<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\News;
use Hideyo\Backend\Models\NewsImage;
use Hideyo\Backend\Models\NewsGroup;
use Carbon\Carbon;
use Image;
use File;
use Hideyo\Backend\Repositories\ShopRepositoryInterface;
use Validator;
use Auth;

class NewsRepository implements NewsRepositoryInterface
{

    /**
     * Note: please keep logic in this repository. Put logic not in models,
     * Information about models in Laravel: http://laravel.com/docs/5.1/eloquent
     * @author     Matthijs Neijenhuijs <matthijs@hideyo.io>
     * @copyright  DutchBridge - dont share/steel!
     */

    protected $model;

    public function __construct(News $model, NewsImage $modelImage, NewsGroup $modelGroup, ShopRepositoryInterface $shop)
    {
        $this->model = $model;
        $this->modelImage = $modelImage;
        $this->shop = $shop;
        $this->modelGroup = $modelGroup;
    }
  
    /**
     * The validation rules for the model.
     *
     * @param  integer  $id id attribute model    
     * @return array
     */
    public function rules($id = false, $attributes = false)
    {
        if (isset($attributes['seo'])) {
            $rules = array(
                'meta_title'                 => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id'
            );
        } else {
            $rules = array(
                'title'                 => 'required|between:4,65|unique:'.$this->model->getTable().''
            );
            
            if ($id) {
                $rules['title'] =   'required|between:4,65|unique:'.$this->model->getTable().',title,'.$id;
            }
        }

        return $rules;
    }

    public function rulesGroup($id = false, $attributes = false)
    {
        if (isset($attributes['seo'])) {
            $rules = array(
                'meta_title'                 => 'required|between:4,65|unique_with:'.$this->modelGroup->getTable().', shop_id'
            );
        } else {
            $rules = array(
                'title'                 => 'required|between:4,65|unique:'.$this->modelGroup->getTable()
            );
            
            if ($id) {
                $rules['title'] =   'required|between:4,65|unique:'.$this->modelGroup->getTable().',title,'.$id;
            }
        }

        return $rules;
    }


    public function create(array $attributes)
    {
        $attributes['shop_id'] = Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
        $this->model->fill($attributes);
        $this->model->save();
        
        if (isset($attributes['subcategories'])) {
            $this->model->subcategories()->sync($attributes['subcategories']);
        }
                
        return $this->model;
    }

    public function createImage(array $attributes, $newsId)
    {
        $userId = Auth::guard('hideyobackend')->user()->id;
        $shopId = Auth::guard('hideyobackend')->user()->selected_shop_id;
        $shop = $this->shop->find($shopId);
        $attributes['modified_by_user_id'] = $userId;

        $destinationPath = storage_path() . "/app/files/news/".$newsId;
        $attributes['user_id'] = $userId;
        $attributes['news_id'] = $newsId;
        $attributes['extension'] = $attributes['file']->getClientOriginalExtension();
        $attributes['size'] = $attributes['file']->getSize();
       
        $rules = array(
            'file'=>'required|image|max:1000',
            'rank' => 'required'
        );

        $validator = Validator::make($attributes, $rules);

        if ($validator->fails()) {
            return $validator;
        } else {
            $filename =  str_replace(" ", "_", strtolower($attributes['file']->getClientOriginalName()));
            $upload_success = $attributes['file']->move($destinationPath, $filename);

            if ($upload_success) {
                $attributes['file'] = $filename;
                $attributes['path'] = $upload_success->getRealPath();
         
                $this->modelImage->fill($attributes);
                $this->modelImage->save();

                if ($shop->square_thumbnail_sizes) {
                    $sizes = explode(',', $shop->square_thumbnail_sizes);
                    if ($sizes) {
                        foreach ($sizes as $key => $value) {
                            $image = Image::make($upload_success->getRealPath());
                            $explode = explode('x', $value);
                            $image->resize($explode[0], $explode[1]);
                            $image->interlace();

                            if (!File::exists(public_path() . "/files/news/".$value."/".$newsId."/")) {
                                File::makeDirectory(public_path() . "/files/news/".$value."/".$newsId."/", 0777, true);
                            }
                            $image->save(public_path() . "/files/news/".$value."/".$newsId."/".$filename);
                        }
                    }
                }
                
                return $this->modelImage;
            }
        }
    }


    public function createGroup(array $attributes)
    {
        $attributes['shop_id'] = Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rulesGroup());

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
            
        $this->modelGroup->fill($attributes);
        $this->modelGroup->save();
   
        return $this->modelGroup;
    }



    public function refactorAllImagesByShopId($shopId)
    {
        $result = $this->modelImage->get();
        $shop = $this->shop->find($shopId);
        foreach ($result as $productImage) {
            if ($shop->square_thumbnail_sizes) {
                $sizes = explode(',', $shop->square_thumbnail_sizes);
                if ($sizes) {
                    foreach ($sizes as $key => $value) {
                        if (!File::exists(public_path() . "/files/news/".$value."/".$productImage->news_id."/")) {
                            File::makeDirectory(public_path() . "/files/news/".$value."/".$productImage->news_id."/", 0777, true);
                        }

                        if (!File::exists(public_path() . "/files/news/".$value."/".$productImage->news_id."/".$productImage->file)) {
                            if (File::exists(storage_path() ."/app/files/news/".$productImage->news_id."/".$productImage->file)) {
                                $image = Image::make(storage_path() ."/app/files/news/".$productImage->news_id."/".$productImage->file);
                                $explode = explode('x', $value);
                                $image->fit($explode[0], $explode[1]);
                            
                                $image->interlace();

                                $image->save(public_path() . "/files/news/".$value."/".$productImage->news_id."/".$productImage->file);
                            }
                        }
                    }
                }
            }
        }
    }


    public function updateById(array $attributes, $id)
    {
        $validator = Validator::make($attributes, $this->rules($id, $attributes));

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
        $this->model = $this->find($id);
        return $this->updateEntity($attributes);
    }

    public function updateEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->model->fill($attributes);
            $this->model->save();
        }

        return $this->model;
    }


    public function updateImageById(array $attributes, $newsId, $id)
    {
        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
        $this->modelImage = $this->findImage($id);
        return $this->updateImageEntity($attributes);
    }

    public function updateImageEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->modelImage->fill($attributes);
            $this->modelImage->save();
        }

        return $this->modelImage;
    }


    public function updateGroupById(array $attributes, $id)
    {
        $validator = Validator::make($attributes, $this->rulesGroup($id, $attributes));

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
        $this->modelGroup = $this->findGroup($id);
        return $this->updateGroupEntity($attributes);
    }

    public function updateGroupEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->modelGroup->fill($attributes);
            $this->modelGroup->save();
        }

        return $this->modelGroup;
    }


    public function destroy($id)
    {
        $this->model = $this->find($id);

        if ($this->model->newsImages->count()) {
            foreach ($this->model->newsImages as $image) {
                $this->newsImage->destroy($image->id);
            }
        }

        $directory = app_path() . "/storage/files/news/".$this->model->id;
        File::deleteDirectory($directory);

        return $this->model->delete();
    }




    public function destroyImage($id)
    {
        $this->modelImage = $this->findImage($id);
        $filename = storage_path() ."/app/files/news/".$this->modelImage->news_id."/".$this->modelImage->file;
        $shopId = Auth::guard('hideyobackend')->user()->selected_shop_id;
        $shop = $this->shop->find($shopId);

        if (File::exists($filename)) {
            File::delete($filename);
            if ($shop->square_thumbnail_sizes) {
                $sizes = explode(',', $shop->square_thumbnail_sizes);
                if ($sizes) {
                    foreach ($sizes as $key => $value) {
                        File::delete(public_path() . "/files/news/".$value."/".$this->modelImage->news_id."/".$this->modelImage->file);
                    }
                }
            }
        }

        return $this->modelImage->delete();
    }

    public function destroyGroup($id)
    {
        $this->modelGroup = $this->findGroup($id);
        $this->modelGroup->save();

        return $this->modelGroup->delete();
    }



    public function selectByLimitAndOrderBy($shopId, $limit, $orderBy)
    {

        $dt = Carbon::now('Europe/Amsterdam');


        return $this->model->with(
            array('newsImages' => function ($query) {
                $query->orderBy('rank', 'asc');
            })
        )
            ->limit($limit)
           ->where('shop_id', '=', $shopId)
           ->where('published_at', '<=', $dt->toDateString('Y-m-d'))
            ->orderBy('created_at', $orderBy)->get();
    }

    public function selectAll()
    {
        return $this->model->get();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    public function getModel()
    {
        return $this->model;
    }


    public function findGroup($id)
    {
        return $this->modelGroup->find($id);
    }

    public function getGroupModel()
    {
        return $this->modelGroup;
    }


    public function findImage($id)
    {
        return $this->modelImage->find($id);
    }

    public function getImageModel()
    {
        return $this->modelImage;
    }

}
