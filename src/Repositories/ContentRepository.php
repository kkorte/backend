<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\Content;
use Hideyo\Backend\Models\ContentImage;
use Hideyo\Backend\Models\ContentGroup;
use Image;
use File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hideyo\Backend\Repositories\ShopRepositoryInterface;
use Validator;
 
class ContentRepository implements ContentRepositoryInterface
{

    protected $model;

    public function __construct(Content $model, ContentImage $modelImage, ContentGroup $modelGroup, ShopRepositoryInterface $shop)
    {
        $this->model = $model;
        $this->modelImage = $modelImage;
        $this->modelGroup = $modelGroup;
        $this->shop = $shop;
    }

    /**
     * The validation rules for the model.
     *
     * @param  integer  $id id attribute model    
     * @return array
     */
    private function rules($id = false, $attributes = false)
    {
        if (isset($attributes['seo'])) {
            $rules = array(
                'meta_title'                 => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id'
            );
        } else {
            $rules = array(
                'title'                 => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id'
            );
            
            if ($id) {
                $rules['title'] =   'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id, '.$id.' = id';
            }
        }

        return $rules;
    }


    /**
     * The validation rules for the modelGroup.
     *
     * @return array
     */
    private function rulesGroup($id = false, $attributes = false)
    {
        if (isset($attributes['seo'])) {
            $rules = array(
                'meta_title'                 => 'required|between:4,65|unique_with:'.$this->modelGroup->getTable().', shop_id'
            );
        } else {
            $rules = array(
                'title'                 => 'required|between:4,65|unique:'.$this->modelGroup->getTable().''
            );
            
            if ($id) {
                $rules['title'] =   'required|between:4,65|unique:'.$this->modelGroup->getTable().',title,'.$id;
            }
        }

        return $rules;
    }

  
    public function create(array $attributes)
    {
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
            
        $this->model->fill($attributes);
        $this->model->save();

        if (isset($attributes['payment_methods'])) {
            $this->model->relatedPaymentMethods()->sync($attributes['payment_methods']);
        }
   
        return $this->model;
    }

    public function createGroup(array $attributes)
    {
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rulesGroup());

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
            
        $this->modelGroup->fill($attributes);
        $this->modelGroup->save();
   
        return $this->modelGroup;
    }

    public function createImage(array $attributes, $contentId)
    {
        $userId = \Auth::guard('hideyobackend')->user()->id;
        $shopId = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $shop = $this->shop->find($shopId);

       
        $rules = array(
            'file'=>'required|image|max:1000',
            'rank' => 'required'
        );

        $validator = Validator::make($attributes, $rules);

        if ($validator->fails()) {
            return $validator;
        } else {

            $attributes['modified_by_user_id'] = $userId;

            $destinationPath = storage_path() . "/app/files/content/".$contentId;
            $attributes['user_id'] = $userId;
            $attributes['content_id'] = $contentId;
            $attributes['extension'] = $attributes['file']->getClientOriginalExtension();
            $attributes['size'] = $attributes['file']->getSize();
            
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

                            if (!File::exists(public_path() . "/files/content/".$value."/".$contentId."/")) {
                                File::makeDirectory(public_path() . "/files/content/".$value."/".$contentId."/", 0777, true);
                            }
                            $image->save(public_path() . "/files/content/".$value."/".$contentId."/".$filename);
                        }
                    }
                }
                
                return $this->modelImage;
            }
        }
    }

    public function updateById(array $attributes, $id)
    {
        $validator = Validator::make($attributes, $this->rules($id, $attributes));

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        $this->model = $this->find($id);
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


    public function updateGroupById(array $attributes, $id)
    {
        $validator = Validator::make($attributes, $this->rulesGroup($id, $attributes));

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        $this->modelGroup = $this->findGroup($id);
        return $this->updateGroupEntity($attributes);
    }

    private function updateGroupEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->modelGroup->fill($attributes);
            $this->modelGroup->save();
        }

        return $this->modelGroup;
    }

    public function updateImageById(array $attributes, $contentId, $id)
    {
        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        $this->modelImage = $this->find($id);
        return $this->updateImageEntity($attributes);
    }

    private function updateImageEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->modelImage->fill($attributes);
            $this->modelImage->save();
        }

        return $this->modelImage;
    }

    public function destroy($id)
    {
        $this->model = $this->find($id);
        $this->model->save();

        if ($this->model->contentImages()->count()) {
            foreach ($this->model->contentImages()->get() as $image) {
                $this->contentImage->destroy($image->id);
            }
        }

        $directory = app_path() . "/storage/files/".$this->model->shop_id."/content/".$this->model->id;
        \File::deleteDirectory($directory);


        return $this->model->delete();
    }

    public function destroyImage($id)
    {
        $this->modelImage = $this->findImage($id);
        $filename = storage_path() ."/app/files/content/".$this->modelImage->content_id."/".$this->modelImage->file;
        $shopId = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $shop = $this->shop->find($shopId);

        if (\File::exists($filename)) {
            \File::delete($filename);
            if ($shop->square_thumbnail_sizes) {
                $sizes = explode(',', $shop->square_thumbnail_sizes);
                if ($sizes) {
                    foreach ($sizes as $key => $value) {
                        \File::delete(public_path() . "/files/content/".$value."/".$this->modelImage->content_id."/".$this->modelImage->file);
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


    public function selectAll()
    {
        return $this->model->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }

    public function selectGroupAll()
    {
        return $this->modelGroup->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }


    function selectOneById($id)
    {
        $result = $this->model->with(array('relatedPaymentMethods'))->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->where('active', '=', 1)->where('id', '=', $id)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }

    function selectAllActiveByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->where('active', '=', 1)->get();
    }

    function selectOneByShopIdAndId($shopId, $id)
    {
        $result = $this->model->with(array('relatedPaymentMethods' => function ($query) {
            $query->where('active', '=', 1);
        }))->where('shop_id', '=', $shopId)->where('active', '=', 1)->where('id', '=', $id)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }



    function selectOneByShopIdAndSlug($shopId, $slug)
    {
        $result = $this->model->where('shop_id', '=', $shopId)->where('slug', '=', $slug)->where('active', '=', 1)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
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
