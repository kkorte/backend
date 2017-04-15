<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\Brand;
use Hideyo\Backend\Models\BrandImage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hideyo\Backend\Repositories\RedirectRepositoryInterface;
use Image;
use File;
use Hideyo\Backend\Repositories\ShopRepositoryInterface;
 
class BrandRepository implements BrandRepositoryInterface
{
    protected $model;

    public function __construct(Brand $model, BrandImage $modelImage, RedirectRepositoryInterface $redirect, ShopRepositoryInterface $shop)
    {
        $this->model = $model;
        $this->modelImage = $modelImage;
        $this->redirect = $redirect;
        $this->shop = $shop;
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
                'title'                 => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id',
            );
            
            if ($id) {
                $rules['title'] =   'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id, '.$id.' = id';
            }
        }

        return $rules;
    }  
  
    public function create(array $attributes)
    {
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
            
        $this->model->fill($attributes);
        $this->model->save();
   
        return $this->model;
    }


    public function createImage(array $attributes, $brandId)
    {
        $userId = \Auth::guard('hideyobackend')->user()->id;
        $shopId = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $shop = $this->shop->find($shopId);
       
        $rules = array(
            'file'=>'required|image|max:1000',
            'rank' => 'required'
        );

        $validator = \Validator::make($attributes, $rules);

        if ($validator->fails()) {
            return $validator;
        } else {

            $attributes['modified_by_user_id'] = $userId;

            $destinationPath = storage_path() . "/app/files/brand/".$brandId;
            $attributes['user_id'] = $userId;
            $attributes['brand_id'] = $brandId;
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

                            if (!File::exists(public_path() . "/files/brand/".$value."/".$brandId."/")) {
                                File::makeDirectory(public_path() . "/files/brand/".$value."/".$brandId."/", 0777, true);
                            }
                            $image->save(public_path() . "/files/brand/".$value."/".$brandId."/".$filename);
                        }
                    }
                }
                
                return $this->modelImage;
            }
        }
    }

    public function updateById(array $attributes, $id)
    {
        $validator = \Validator::make($attributes, $this->rules($id, $attributes));
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        $this->model = $this->find($id);


        $oldTitle = $this->model->title;
        $oldSlug = $this->model->slug;

        $result = $this->updateEntity($attributes);

        return $result;
    }

    public function updateEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->model->fill($attributes);
            $this->model->save();
        }

        return $this->model;
    }

    public function updateImageById(array $attributes, $brandId, $id)
    {
        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
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

    public function destroy($id)
    {
        $this->model = $this->find($id);

        // $url = $this->model->shop->url.route('brand.item', ['slug' => $this->model->slug], null);
        // $newUrl = $this->model->shop->url.route('brand.overview', array(), null);
        // $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $newUrl, 'shop_id' => $this->model->shop_id));

        $this->model->save();

        return $this->model->delete();
    }

    public function destroyImage($id)
    {
        $this->modelImage = $this->findImage($id);
        $filename = storage_path() ."/app/files/brand/".$this->modelImage->brand_id."/".$this->modelImage->file;
        $shopId = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $shop = $this->shop->find($shopId);

        if (\File::exists($filename)) {
            \File::delete($filename);
            if ($shop->square_thumbnail_sizes) {
                $sizes = explode(',', $shop->square_thumbnail_sizes);
                if ($sizes) {
                    foreach ($sizes as $key => $value) {
                        \File::delete(public_path() . "/files/brand/".$value."/".$this->modelImage->brand_id."/".$this->modelImage->file);
                    }
                }
            }
        }

        return $this->modelImage->delete();
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
                        if (!File::exists(public_path() . "/files/brand/".$value."/".$productImage->brand_id."/")) {
                            File::makeDirectory(public_path() . "/files/brand/".$value."/".$productImage->brand_id."/", 0777, true);
                        }

                        if (!File::exists(public_path() . "/files/brand/".$value."/".$productImage->brand_id."/".$productImage->file)) {
                            if (File::exists(storage_path() ."/app/files/brand/".$productImage->brand_id."/".$productImage->file)) {
                                $image = Image::make(storage_path() ."/app/files/brand/".$productImage->brand_id."/".$productImage->file);
                                $explode = explode('x', $value);
                                $image->fit($explode[0], $explode[1]);
                            
                                $image->interlace();

                                $image->save(public_path() . "/files/brand/".$value."/".$productImage->brand_id."/".$productImage->file);
                            }
                        }
                    }
                }
            }
        }
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->orderBy('title', 'asc')->get();
    }
    
    public function find($id)
    {
        return $this->model->find($id);
    }

    public function getModel()
    {
        return $this->model;
    }

    public function findImage($id)
    {
        return $this->modelImage->find($id);
    }

    public function getModelImage()
    {
        return $this->modelImage;
    }    
}