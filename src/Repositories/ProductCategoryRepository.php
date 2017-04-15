<?php
namespace Hideyo\Backend\Repositories;


use Hideyo\Backend\Repositories\ProductCategoryImageRepositoryInterface;
use Hideyo\Backend\Repositories\RedirectRepositoryInterface;
use Hideyo\Backend\Repositories\ShopRepositoryInterface;

use Hideyo\Backend\Models\ProductCategory;
use Hideyo\Backend\Models\ProductCategoryImage;
use Image;
use File;
 
class ProductCategoryRepository implements ProductCategoryRepositoryInterface
{

    protected $model;

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
                'meta_title'                 => 'required|between:4,65',
                'meta_description'           => 'required|between:4,160',
            );
        } elseif (isset($attributes['highlight'])) {
            $rules = array();
        } else {
            $rules = array(
                'title'                 => 'required|unique_with:'.config()->get('hideyo.db_prefix').'product_category, shop_id'
            );
            
            if ($id) {
                $rules['title'] =   'required|between:4,65|unique_with:'.config()->get('hideyo.db_prefix').'product_category, shop_id, '.$id.' = id';
            }
        }
        return $rules;
    }

    public function __construct(ProductCategory $model, ProductCategoryImage $imageModel, ShopRepositoryInterface $shop, RedirectRepositoryInterface $redirect)
    {
        $this->model = $model;
        $this->imageModel = $imageModel;
        $this->redirect = $redirect;
        $this->shop = $shop;
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
        $this->model->rebuild();
        return $this->model;
    }


    public function createImage(array $attributes, $productCategoryId)
    {
        $userId = \Auth::guard('hideyobackend')->user()->id;
        $shopId = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $shop = $this->shop->find($shopId);
        $attributes['modified_by_user_id'] = $userId;

        $destinationPath = storage_path() . "/app/files/product_category/".$productCategoryId;
        $attributes['user_id'] = $userId;
        $attributes['product_category_id'] = $productCategoryId;
        $attributes['extension'] = $attributes['file']->getClientOriginalExtension();
        $attributes['size'] = $attributes['file']->getSize();
       
        $rules = array(
            'file'=>'required|image|max:1000',
            'rank' => 'required'
        );

        $validator = \Validator::make($attributes, $rules);

        if ($validator->fails()) {
            return $validator;
        } else {
            $filename =  str_replace(" ", "_", strtolower($attributes['file']->getClientOriginalName()));
            $upload_success = $attributes['file']->move($destinationPath, $filename);

            if ($upload_success) {
                $attributes['file'] = $filename;
                $attributes['path'] = $upload_success->getRealPath();
         
                $this->imageModel->fill($attributes);
                $this->imageModel->save();

                if ($shop->square_thumbnail_sizes) {
                    $sizes = explode(',', $shop->square_thumbnail_sizes);
                    if ($sizes) {
                        foreach ($sizes as $key => $value) {
                            $image = Image::make($upload_success->getRealPath());
                            $explode = explode('x', $value);
                            $image->resize($explode[0], $explode[1]);
                            $image->interlace();

                            if (!File::exists(public_path() . "/files/product_category/".$value."/".$productCategoryId."/")) {
                                File::makeDirectory(public_path() . "/files/product_category/".$value."/".$productCategoryId."/", 0777, true);
                            }
                            $image->save(public_path() . "/files/product_category/".$value."/".$productCategoryId."/".$filename);
                        }
                    }
                }
                
                return $this->imageModel;
            }
        }
    }


    public function refactorAllImagesByShopId($shopId)
    {
        $result = $this->model->get();
        $shop = $this->shop->find($shopId);
        foreach ($result as $productImage) {
            if ($shop->square_thumbnail_sizes) {
                $sizes = explode(',', $shop->square_thumbnail_sizes);
                if ($sizes) {
                    foreach ($sizes as $key => $value) {
                        if (!File::exists(public_path() . "/files/product_category/".$value."/".$productImage->product_category_id."/")) {
                            File::makeDirectory(public_path() . "/files/product_category/".$value."/".$productImage->product_category_id."/", 0777, true);
                        }

                        if (!File::exists(public_path() . "/files/product_category/".$value."/".$productImage->product_category_id."/".$productImage->file)) {
                            if (File::exists(storage_path() ."/app/files/product_category/".$productImage->product_category_id."/".$productImage->file)) {
                                $image = Image::make(storage_path() ."/app/files/product_category/".$productImage->product_category_id."/".$productImage->file);
                                $explode = explode('x', $value);
                                $image->fit($explode[0], $explode[1]);
                            
                                $image->interlace();

                                $image->save(public_path() . "/files/product_category/".$value."/".$productImage->product_category_id."/".$productImage->file);
                            }
                        }
                    }
                }
            }
        }
    }



    public function updateById(array $attributes, $id)
    {
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules($id, $attributes));

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        $this->model = $this->find($id);


        $oldTitle = $this->model->title;
        $oldSlug = $this->model->slug;

        $this->model->rebuild();
        $result = $this->updateEntity($attributes);


        // if (isset($attributes['title']) AND $oldTitle != $attributes['title']) {
        //     $url = $result->shop->url.route('product-category', ['slug' => $oldSlug], null);
           
        //     if ($result->active) {
        //         $this->redirect->destroyByUrl($url);
        //     }

        //     $newUrl = $result->shop->url.route('product-category', ['slug' => $result->slug], null);
        //     $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $newUrl, 'shop_id' => $result->shop_id));
        // }

        // if (!$result->active) {
        //     $url = $result->shop->url.route('product-category', ['slug' => $result->slug], null);
        //     $productCategoryUrl = $result->shop->url;
        //     $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $productCategoryUrl, 'shop_id' => $result->shop_id));
        // }




        return $result;
    }

    public function updateEntity(array $attributes = array())
    {
        if (!isset($attributes['parent_id'])) {
            $attributes['parent_id'] = null;
        }

        if (count($attributes) > 0) {
            $this->model->fill($attributes);
            

            if (isset($attributes['highlightProducts'])) {
                $this->model->productCategoryHighlightProduct()->sync($attributes['highlightProducts']);
            }
            
            $this->model->save();
        }

        return $this->model;
    }


    public function updateImageById(array $attributes, $productCategoryId, $id)
    {
        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        $this->model = $this->findImage($id);
        return $this->updateImageEntity($attributes);
    }

    public function updateImageEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->model->fill($attributes);
            $this->model->save();
        }

        return $this->model;
    }



    public function destroy($id)
    {
        $this->model = $this->find($id);

        // $url = $this->model->shop->url.route('hideyo.product-category', ['slug' => $this->model->slug], null);
        // $newUrl = $this->model->shop->url;
        // $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $newUrl, 'shop_id' => $this->model->shop_id));

        if ($this->model->productCategoryImages()->count()) {
            foreach ($this->model->productCategoryImages()->get() as $image) {
                $this->productCategoryImage->destroy($image->id);
            }
        }

        $directory = storage_path() . "/app/files/product_category/".$this->model->id;
        \File::deleteDirectory($directory);
        \File::deleteDirectory(public_path() . "/files/product_category/".$this->model->id);

        if ($this->model->children()->count()) {
            foreach ($this->model->children()->get() as $child) {
                $child->makeRoot();
                $child->parent_id = null;
                $child->save();
            }
        }

        return $this->model->delete();
    }

    public function destroyImage($id)
    {
        $this->imageModel = $this->findImage($id);
        $filename = storage_path() ."/app/files/product_category/".$this->imageModel->product_category_id."/".$this->imageModel->file;
        $shopId = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $shop = $this->shop->find($shopId);

        if (\File::exists($filename)) {
            \File::delete($filename);
            if ($shop->square_thumbnail_sizes) {
                $sizes = explode(',', $shop->square_thumbnail_sizes);
                if ($sizes) {
                    foreach ($sizes as $key => $value) {
                        \File::delete(public_path() . "/files/product_category/".$value."/".$this->imageModel->product_category_id."/".$this->imageModel->file);
                    }
                }
            }
        }

        return $this->imageModel->delete();
    }


    public function rebuild()
    {
        return $this->model->rebuild(true);
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->orderBy('title', 'asc')->get();
    }


    public function selectAllProductPullDown()
    {
        return $this->model->whereNull('redirect_product_category_id')->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->orderBy('title', 'asc')->get();
    }

    public function ajaxSearchByTitle($query)
    {
        return $this->model->where('title', 'LIKE', '%'.$query.'%')->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->orderBy('title', 'asc')->get();
    }

    function selectAllByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->orderBy('title', 'asc')->get();
    }

    function selectAllActiveByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->where('active', '=', 1)->orderBy('title', 'asc')->get();
    }

    function selectAllByShopIdAndRoot($shopId)
    {
         return $this->model->roots()->where('shop_id', '=', $shopId)->where('active', '=', 1)->orderBy('title', 'asc')->get();
    }

    function selectOneByShopIdAndSlug($shopId, $slug, $imageTag = false)
    {

        $result = $this->model->
        with(array('products' => function ($query) {
            $query->where('active', '=', 1)->with(
                array('productImages' => function ($query) {
                    $query->orderBy('rank', 'asc');
                })
            );
        },
        'productCategoryImages' => function ($query) use ($imageTag) {
            if ($imageTag) {
                $query->where('tag', '=', $imageTag);
            } $query->orderBy('rank', 'asc');
        }, 'refProductCategory'))
        ->where('product_category.shop_id', '=', $shopId)
        ->where('product_category.slug', '=', $slug)
        ->where('active', '=', 1)
        ->get()
        ->first();

        if ($result) {
            if ($result->isRoot()) {
                $result['is_root'] = true;
            }

            if ($result->isLeaf()) {
                $result['is_leaf'] = true;
            } else {
                $result['is_leaf'] = false;
                $result['children_product_categories'] = $result->children()->get();
            }

            return $result;
        } else {
            return false;
        }
    }

    function selectOneByShopIdAndId($shopId, $id, $imageTag = false)
    {
        $result = $this->model->
        with(array('products' => function ($query) {
            $query->where('active', '=', 1)->with(
                array('productImages' => function ($query) {
                    $query->orderBy('rank', 'asc');
                })
            );
        },
        'productCategoryImages' => function ($query) use ($imageTag) {
            if ($imageTag) {
                $query->where('tag', '=', $imageTag);
            } $query->orderBy('rank', 'asc');
        }, 'refProductCategory'))
        ->where('product_category.shop_id', '=', $shopId)
        ->where('product_category.id', '=', $id)
        ->where('active', '=', 1)
        ->get()
        ->first();

        if ($result) {
            if ($result->isRoot()) {
                $result['is_root'] = true;
            }

            if ($result->isLeaf()) {
                $result['is_leaf'] = true;
            } else {
                $result['is_leaf'] = false;
                $result['children_product_categories'] = $result->children()->get();
            }

            return $result;
        } else {
            return false;
        }
    }

    function selectCategoriesByParentId($shopId, $parentId, $imageTag = false)
    {
        $result = $this->model->where('id', '=', $parentId)->first()->children()
        ->with(
            array('productCategoryImages' => function ($query) use ($imageTag) {
                if ($imageTag) {
                    $query->where('tag', '=', $imageTag);
                }
                    $query->orderBy('rank', 'asc');
            }
            )
        )
        ->where('product_category.shop_id', '=', $shopId)
        ->where('product_category.parent_id', '=', $parentId)
        ->where('active', '=', '1')->get();

        if ($result->count()) {
            return $result;
        } else {
            return false;
        }
    }

    function selectRootCategories($shopId, $imageTag)
    {

        $result = $this->model
        ->with(
            array('productCategoryImages' => function ($query) use ($imageTag) {
                if ($imageTag) {
                    $query->where('tag', '=', $imageTag);
                }
                    $query->orderBy('rank', 'asc');
            }
            )
        )
            ->where('product_category.shop_id', '=', $shopId)
            ->whereNull('product_category.parent_id')
            ->get();

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    
    public function find($id)
    {
        return $this->model->find($id);
    }

    public function entireTreeStructure($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->get()->toHierarchy();
    }


    public function changeActive($productCategoryId)
    {

        $this->model = $this->find($productCategoryId);

        if ($this->model) {
            if ($this->model->active) {
                $active = 0;
            } else {
                $active = 1;
            }

            $attributes = array(
                'active' => $active
            );

            $this->model->fill($attributes);

            


            // if (!$this->model->active) {
            //     $url = $this->model->shop->url.route('product-category', ['slug' => $this->model->slug], null);
            //     $productCategoryUrl = $this->model->shop->url;
            //     $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $productCategoryUrl, 'shop_id' => $this->model->shop_id));
            // } else {
            //     $url = $this->model->shop->url.route('product-category', ['slug' => $this->model->slug], null);
            //     $this->redirect->destroyByUrl($url);
            // }


            return $this->model->save();
        }

        return false;
    }


    public function getModel()
    {
        return $this->model;
    }

    public function findImage($id)
    {
        return $this->imageModel->find($id);
    }

    public function getImageModel()
    {
        return $this->imageModel;
    }

    
}
