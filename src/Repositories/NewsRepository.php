<?php
namespace Hideyo\Ecommerce\Backend\Repositories;
 
use Hideyo\Ecommerce\Backend\Models\News;
use Hideyo\Ecommerce\Backend\Models\NewsImage;
use Hideyo\Ecommerce\Backend\Models\NewsGroup;
use Carbon\Carbon;
use Image;
use File;
use Hideyo\Ecommerce\Backend\Repositories\ShopRepositoryInterface;
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
    private function rules($newsId = false, $attributes = false)
    {
        if (isset($attributes['seo'])) {
            $rules = array(
                'meta_title'                 => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id'
            );
        } else {
            $rules = array(
                'title'                 => 'required|between:4,65|unique:'.$this->model->getTable().''
            );
            
            if ($newsId) {
                $rules['title'] =   'required|between:4,65|unique:'.$this->model->getTable().',title,'.$newsId;
            }
        }

        return $rules;
    }

    private function rulesGroup($newsGroupId = false, $attributes = false)
    {
        if (isset($attributes['seo'])) {
            $rules = array(
                'meta_title'                 => 'required|between:4,65|unique_with:'.$this->modelGroup->getTable().', shop_id'
            );
        } else {
            $rules = array(
                'title'                 => 'required|between:4,65|unique:'.$this->modelGroup->getTable()
            );
            
            if ($newsGroupId) {
                $rules['title'] =   'required|between:4,65|unique:'.$this->modelGroup->getTable().',title,'.$newsGroupId;
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
        } 
        
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
                    foreach ($sizes as $keyImage => $valueImage) {
                        $image = Image::make($upload_success->getRealPath());
                        $explode = explode('x', $valueImage);
                        $image->resize($explode[0], $explode[1]);
                        $image->interlace();

                        if (!File::exists(public_path() . "/files/news/".$valueImage."/".$newsId."/")) {
                            File::makeDirectory(public_path() . "/files/news/".$valueImage."/".$newsId."/", 0777, true);
                        }
                        $image->save(public_path() . "/files/news/".$valueImage."/".$newsId."/".$filename);
                    }
                }
            }
            
            return $this->modelImage;
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
                    foreach ($sizes as $keyImage => $valueImage) {
                        if (!File::exists(public_path() . "/files/news/".$valueImage."/".$productImage->news_id."/")) {
                            File::makeDirectory(public_path() . "/files/news/".$valueImage."/".$productImage->news_id."/", 0777, true);
                        }

                        if (!File::exists(public_path() . "/files/news/".$valueImage."/".$productImage->news_id."/".$productImage->file)) {
                            if (File::exists(storage_path() ."/app/files/news/".$productImage->news_id."/".$productImage->file)) {
                                $image = Image::make(storage_path() ."/app/files/news/".$productImage->news_id."/".$productImage->file);
                                $explode = explode('x', $valueImage);
                                $image->fit($explode[0], $explode[1]);
                            
                                $image->interlace();

                                $image->save(public_path() . "/files/news/".$valueImage."/".$productImage->news_id."/".$productImage->file);
                            }
                        }
                    }
                }
            }
        }
    }


    public function updateById(array $attributes, $newsId)
    {
        $validator = Validator::make($attributes, $this->rules($newsId, $attributes));

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
        $this->model = $this->find($newsId);
        return $this->updateEntity($attributes);
    }

    private function updateEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->model->fill($attributes);
            $this->model->save();
        }

        return $this->model;
    }


    public function updateImageById(array $attributes, $newsId, $newsImageId)
    {
        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
        $this->modelImage = $this->findImage($newsImageId);
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


    public function updateGroupById(array $attributes, $newsGroupId)
    {
        $validator = Validator::make($attributes, $this->rulesGroup($newsGroupId, $attributes));

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
        $this->modelGroup = $this->findGroup($newsGroupId);
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

    public function destroy($newsId)
    {
        $this->model = $this->find($newsId);

        if ($this->model->newsImages->count()) {
            foreach ($this->model->newsImages as $image) {
                $this->newsImage->destroy($image->id);
            }
        }

        $directory = app_path() . "/storage/files/news/".$this->model->id;
        File::deleteDirectory($directory);

        return $this->model->delete();
    }

    public function destroyImage($newsImageId)
    {
        $this->modelImage = $this->findImage($newsImageId);
        $filename = storage_path() ."/app/files/news/".$this->modelImage->news_id."/".$this->modelImage->file;
        $shopId = Auth::guard('hideyobackend')->user()->selected_shop_id;
        $shop = $this->shop->find($shopId);

        if (File::exists($filename)) {
            File::delete($filename);
            if ($shop->square_thumbnail_sizes) {
                $sizes = explode(',', $shop->square_thumbnail_sizes);
                if ($sizes) {
                    foreach ($sizes as $keyImage => $valueImage) {
                        File::delete(public_path() . "/files/news/".$valueImage."/".$this->modelImage->news_id."/".$this->modelImage->file);
                    }
                }
            }
        }

        return $this->modelImage->delete();
    }

    public function destroyGroup($newsGroupId)
    {
        $this->modelGroup = $this->findGroup($newsGroupId);
        $this->modelGroup->save();

        return $this->modelGroup->delete();
    }

    public function selectAll()
    {
        return $this->model->get();
    }

    public function selectAllGroups()
    {
       return $this->model->where('shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
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