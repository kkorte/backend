<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\Product;
use Hideyo\Backend\Models\ProductImage;
use Image;
use File;

use Hideyo\Backend\Repositories\ProductImageRepositoryInterface;
use Hideyo\Backend\Repositories\RedirectRepositoryInterface;
use Hideyo\Backend\Repositories\ShopRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{

    protected $model;

    protected $guard = 'admin';


    public function __construct(Product $model, ProductImage $modelImage, ShopRepositoryInterface $shop, RedirectRepositoryInterface $redirect)
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
    private function rules($id = false, $attributes = false)
    {
        if (isset($attributes['seo'])) {
            $rules = array(
                'meta_title'                 => 'required|between:4,65',
                'meta_description'           => 'required|between:4,160',
            );
        } elseif (isset($attributes['product-combination'])) {
            $rules = array();
        } elseif (isset($attributes['price'])) {
            $rules = array(
                'discount_start_date' => 'date_format:d/m/Y',
                'discount_end_date' => 'date_format:d/m/Y'
            );
        } else {
            $rules = array(
                'title'                 => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id',
                'amount'                => 'integer|required',
                'product_category_id'   => 'required|integer',
                'tax_rate_id'           => 'integer',
                'reference_code'      => 'required'
            );
            
            if ($id) {
                $rules['title'] =   'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id, '.$id.' = id';
            }
        }


        return $rules;
    }

    public function createCopy(array $attributes, $productId)
    {

        $product =  $this->find($productId);
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }
   
        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        if (empty($attributes['discount_value'])) {
            $attributes['discount_value'] = null;
        }

        
        $this->model->fill($attributes);

        $this->model->save();

        if (isset($attributes['subcategories'])) {
            $this->model->subcategories()->sync($attributes['subcategories']);
        }
                
        return $this->model;
    }

  
    public function create(array $attributes)
    {
        
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }
   
        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        if (empty($attributes['discount_value'])) {
            $attributes['discount_value'] = null;
        }

        
        $this->model->fill($attributes);
        $this->model->save();
        if (isset($attributes['subcategories'])) {
            $this->model->subcategories()->sync($attributes['subcategories']);
        }
        
        $this->model->addAllToIndex();

        return $this->model;
    }

    public function createImage(array $attributes, $productId)
    {
        $userId = \Auth::guard('hideyobackend')->user()->id;
        $shopId = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $shop = $this->shop->find($shopId);
        $attributes['modified_by_user_id'] = $userId;

        $destinationPath = storage_path() . "/app/files/product/".$productId;
        $attributes['user_id'] = $userId;
        $attributes['product_id'] = $productId;

        $rules = array(
            'file'=>'required|image|max:1000',
            'rank' => 'required'
        );

        foreach ($attributes['files'] as $file) {

            $attributes['file'] = $file;
            $validator = \Validator::make($attributes, $rules);

            if ($validator->fails()) {
                return $validator;
            } else {
                $attributes['extension'] = $file->getClientOriginalExtension();
                $attributes['size'] = $file->getSize();
                $filename = str_replace(" ", "_", strtolower($file->getClientOriginalName()));
                $upload_success = $file->move($destinationPath, $filename);

                if ($upload_success) {
                    $attributes['file'] = $filename;
                    $attributes['path'] = $upload_success->getRealPath();
                    $file = new ProductImage;
                    $file->fill($attributes);
                    $file->save();
                    if ($shop->square_thumbnail_sizes) {
                        $sizes = explode(',', $shop->square_thumbnail_sizes);
                        if ($sizes) {
                            foreach ($sizes as $key => $value) {
                                $image = Image::make($upload_success->getRealPath());
                                $explode = explode('x', $value);

                                if ($image->width() >= $explode[0] and $image->height() >= $explode[1]) {
                                    $image->resize($explode[0], $explode[1]);
                                }
    

                                if (!File::exists(public_path() . "/files/product/".$value."/".$productId."/")) {
                                    File::makeDirectory(public_path() . "/files/product/".$value."/".$productId."/", 0777, true);
                                }

                                $image->interlace();

                                $image->save(public_path() . "/files/product/".$value."/".$productId."/".$filename);
                            }
                        }
                    }
                }
            }
        }

        if (isset($attributes['productAttributes'])) {
            $file->relatedProductAttributes()->sync($attributes['productAttributes']);
        }

        if (isset($attributes['attributes'])) {
            $this->model->relatedAttributes()->sync($attributes['attributes']);
        }

        $file->save();


        return $file;
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
                        if (!File::exists(public_path() . "/files/product/".$value."/".$productImage->product_id."/")) {
                            File::makeDirectory(public_path() . "/files/product/".$value."/".$productImage->product_id."/", 0777, true);
                        }

                        if (!File::exists(public_path() . "/files/product/".$value."/".$productImage->product_id."/".$productImage->file)) {
                            if (File::exists(storage_path() ."/app/files/product/".$productImage->product_id."/".$productImage->file)) {
                                $image = Image::make(storage_path() ."/app/files/product/".$productImage->product_id."/".$productImage->file);
                                $explode = explode('x', $value);
                                $image->fit($explode[0], $explode[1]);
                            
                                $image->interlace();

                                $image->save(public_path() . "/files/product/".$value."/".$productImage->product_id."/".$productImage->file);
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
        $oldProductCategoryId = $this->model->product_category_id;
        $oldProductCategorySlug = $this->model->productCategory->slug;
        $result = $this->updateEntity($attributes);

        if (isset($attributes['title']) and isset($attributes['product_category_id'])) {
            if (($oldTitle != $attributes['title']) or ($oldProductCategoryId != $attributes['product_category_id'])) {
                $url = $result->shop->url.route('product.item', ['productId' => $result->id, 'productSlug' => $oldSlug, 'categorySlug' => $oldProductCategorySlug], null);
                if ($result->active) {
                    $this->redirect->destroyByUrl($url);
                }
                
                $newUrl = $result->shop->url.route('product.item', ['productId' => $result->id, 'productSlug' => $result->slug, 'categorySlug' => $result->productCategory->slug], null);
                $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $newUrl, 'shop_id' => $result->shop_id));
            }
        }

        if (!$result->active) {
            $url = $result->shop->url.route('product.item', ['productId' => $result->id, 'productSlug' => $result->slug, 'categorySlug' => $result->productCategory->slug], null);
            $productCategoryUrl = $result->shop->url.route('product-category', ['slug' => $result->productCategory->slug], null);
            $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $productCategoryUrl, 'shop_id' => $result->shop_id));
        }





        return $result;
    }

    private function updateEntity(array $attributes = array())
    {

        if (empty($attributes['leading_atrribute_group_id'])) {
            $attributes['leading_atrribute_group_id'] = null;
        }

        if (empty($attributes['discount_value'])) {
            $attributes['discount_value'] = null;
        }
        
        if (count($attributes) > 0) {
            $this->model->fill($attributes);

            if (isset($attributes['subcategories'])) {
                $this->model->subcategories()->sync($attributes['subcategories']);
            } else {
                $this->model->subcategories()->sync(array());
            }
            $this->model->save();
        }
        
        $this->model->addAllToIndex();

        return $this->model;
    }


    public function updateImageById(array $attributes, $productId, $id)
    {
        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        $this->modelImage = $this->findImage($id);
        return $this->updateImageEntity($attributes);
    }

    public function updateImageEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->modelImage->fill($attributes);

            if (isset($attributes['productAttributes'])) {
                $this->modelImage->relatedProductAttributes()->sync($attributes['productAttributes']);
            } else {
                $this->modelImage->relatedProductAttributes()->sync(array());
            }

            if (isset($attributes['attributes'])) {
                $this->modelImage->relatedAttributes()->sync($attributes['attributes']);
            } else {
                $this->modelImage->relatedAttributes()->sync(array());
            }

            $this->modelImage->save();
        }

        return $this->modelImage;
    }


    public function destroy($id)
    {
        $this->model = $this->find($id);

        if ($this->model->productCategory) {
            $url = $this->model->shop->url.route('product.item', ['productId' => $this->model->id, 'productSlug' => $this->model->slug, 'categorySlug' => $this->model->productCategory->slug], null);
            $productCategoryUrl = $this->model->shop->url.route('product-category', ['slug' => $this->model->productCategory->slug], null);
            $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $productCategoryUrl, 'shop_id' => $this->model->shop_id));
        }


        if ($this->model->productImages()->count()) {
            foreach ($this->model->productImages()->get() as $image) {
                $this->productImage->destroy($image->id);
            }
        }


        $directory = storage_path() . "/app/files/product/".$this->model->id;
        \File::deleteDirectory($directory);

        $shopId = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        \File::deleteDirectory(public_path() . "/files/product/".$this->model->id);
        $this->model->addAllToIndex();
        return $this->model->delete();
    }


    public function destroyImage($id)
    {
        $this->modelImage = $this->findImage($id);
        $filename = $this->modelImage->path;
        $shopId = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $shop = $this->shop->find($shopId);
        
        if (\File::exists($filename)) {
            \File::delete($filename);


            if ($shop->square_thumbnail_sizes) {
                $sizes = explode(',', $shop->square_thumbnail_sizes);
                if ($sizes) {
                    foreach ($sizes as $key => $value) {
                        \File::delete(public_path() . "/files/product/".$value."/".$this->modelImage->product_id."/".$this->modelImage->file);
                    }
                }
            }
        }

        return $this->modelImage->delete();
    }


    public function selectByLimitAndOrderBy($shopId, $limit, $orderBy)
    {
        return $this->model->with(array('productCategory', 'relatedProducts', 'productImages' => function ($query) {
            $query->orderBy('rank', 'asc');
        }))->where('shop_id', '=', $shopId)->where('active', '=', 1)->limit($limit)->orderBy('id', $orderBy)->get();
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }

    function selectAllByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->get();
    }
    

    public function selectAllExport()
    {
        return $this->model->with(array('productImages' => function ($query) {
            $query->orderBy('rank', 'asc');
        }))->where('active', '=', 1)->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }



    public function selectAllWithCombinations()
    {
        $result = $this->model->with(array('attributes'))->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();

        $newResult = array();
        foreach ($result as $product) {
            if ($product->attributes->count()) {
                foreach ($product->attributes as $attribute) {
                    $attributesArray = array();
                    foreach ($attribute->combinations as $combination) {
                        $attributesArray[] = $combination->attribute->value;
                    }

                    $newResult[$product->id.'-'.$attribute->id] = $product->title.' - '.implode(', ', $attributesArray);
                }
            } else {
                $newResult[$product->id] = $product->title;
            }
        }

        return $newResult;
    }


    public function selectAllByProductParentId($productParentId)
    {
        return $this->model->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->where('product_parent_id', '=', $productParentId)->get();
    }


    function selectOneByShopIdAndSlug($shopId, $slug)
    {

           return $this->model->with(
               array(
                'attributeGroup',
                'attributes' => function ($query) {
                    $query->with(
                        array(
                            'combinations' => function ($query) {
                                $query->with(
                                    array(
                                        'productAttribute',
                                        'attribute' => function ($query) {
                                            $query->with(
                                                array(
                                                    'attributeGroup'
                                                    )
                                            );
                                        }
                                        )
                                );
                            }
                            )
                    )->orderBy('default_on', 'desc');
                },
                'extraFields' => function ($query) {
                    $query->where('value', '!=', '')
                    ->orWhereNotNull('extra_field_default_value_id')
                    ->with(
                        array('extraField', 'extraFieldDefaultValue')
                    )
                    ->orderBy('id', 'desc');
                },
                'relatedProducts' => function ($query) {
                    $query->with(array('productImages' => function ($query) {
                        $query->orderBy('rank', 'asc');
                    }, 'productCategory'));
                },
                'productImages' => function ($query) {
                    $query->orderBy(
                        'rank',
                        'asc'
                    )->with(array('relatedProductAttributes',
                    'relatedAttributes'));
                })
           )->where('shop_id', '=', $shopId)->whereNotNull('product_category_id')->where('active', '=', 1)->where('slug', '=', $slug)->get()->first();
    }


    function selectAllByShopIdAndProductCategoryId($shopId, $productCategoryId, $filters = false)
    {

        $result = $this->model
        ->with(array('subcategories', 'extraFields' => function ($query) {
            $query->with('extraField')->orderBy('id', 'desc');
        }, 'taxRate', 'productCategory',  'relatedProducts' => function ($query) {
            $query->with('productImages')->orderBy('rank', 'asc');
        }, 'productImages' => function ($query) {
            $query->orderBy('rank', 'asc');
        }))
        ->where('shop_id', '=', $shopId)
        ->where('active', '=', 1)
                ->whereNotNull('product.product_category_id')
        ->where(function ($query) use ($productCategoryId) {
            $query->where('product_category_id', '=', $productCategoryId);
            $query->orWhereHas('subcategories', function ($query) use ($productCategoryId) {
                $query->where('product_category_id', '=', $productCategoryId);
            });
        });

        if ($filters) {
            if (isset($filters['filter']['product_attribute'])) {
                $keys = array();
                foreach ($filters['filter']['product_attribute'] as $key => $row) {
                    if ($keys) {
                        $keys = array_merge($row);
                    } else {
                        $keys = $row;
                    }
                }

                $result->whereHas('attributes', function ($query) use ($filters, $keys) {

                    $query->whereHas('combinations', function ($query) use ($filters, $keys) {

                        $query->whereIn('attribute_id', $keys);
                    });
                });
            }


            if (isset($filters['filter']['extra_field'])) {
                $fieldKeys = array();
                foreach ($filters['filter']['extra_field'] as $key => $row) {
                    if ($fieldKeys) {
                        $fieldKeys = array_merge($row);
                    } else {
                        $fieldKeys = $row;
                    }
                }

                $result->whereHas('extraFields', function ($query) use ($filters, $fieldKeys) {
                        $query->whereIn('extra_field_default_value_id', $fieldKeys);
                        $query->orWhereIn('value', $fieldKeys);
                });
            }
        }
        $result->orderBy(\DB::raw('product.rank = 0, product.rank'), 'ASC');

        return $result->get();
    }


    function selectAllByShopIdAndProductCategoryIdDataLayer($shopId, $productCategoryId, $filters = false)
    {

        $result = $this->model
        ->select('product.title as name', 'product.reference_code as reference_code', 'product_category.title as category', 'brand.title as brand', 'product.rank as position')
        ->leftJoin('product_category', 'product_category.id', '=', 'product.product_category_id')
         ->leftJoin('brand', 'brand.id', '=', 'product.brand_id')
        ->where('product.shop_id', '=', $shopId)
        ->where('product.active', '=', 1)
        ->whereNotNull('product.product_category_id')
        ->where(function ($query) use ($productCategoryId) {
            $query->where('product_category_id', '=', $productCategoryId);
            $query->orWhereHas('subcategories', function ($query) use ($productCategoryId) {
                $query->where('product_category_id', '=', $productCategoryId);
            });
        });

        if ($filters) {
            if (isset($filters['filter']['product_attribute'])) {
                $keys = array();
                foreach ($filters['filter']['product_attribute'] as $key => $row) {
                    if ($keys) {
                        $keys = array_merge($row);
                    } else {
                        $keys = $row;
                    }
                }

                $result->whereHas('attributes', function ($query) use ($filters, $keys) {

                    $query->whereHas('combinations', function ($query) use ($filters, $keys) {

                        $query->whereIn('attribute_id', $keys);
                    });
                });
            }


            if (isset($filters['filter']['extra_field'])) {
                $fieldKeys = array();
                foreach ($filters['filter']['extra_field'] as $key => $row) {
                    if ($fieldKeys) {
                        $fieldKeys = array_merge($row);
                    } else {
                        $fieldKeys = $row;
                    }
                }

                $result->whereHas('extraFields', function ($query) use ($filters, $fieldKeys) {
                        $query->whereIn('extra_field_default_value_id', $fieldKeys);
                        $query->orWhereIn('value', $fieldKeys);
                });
            }
        }
        $result->orderBy(\DB::raw('position = 0, position'), 'ASC');

        return $result->get();
    }


    function selectAllByShopIdAndDiscountPromotion($shopId)
    {

           return $this->model
           ->with(array('subcategories', 'extraFields' => function ($query) {
            $query->with('extraField')->orderBy('id', 'desc');
           }, 'taxRate', 'productCategory',  'relatedProducts' => function ($query) {
            $query->with('productImages')->orderBy('rank', 'asc');
           }, 'productImages' => function ($query) {
            $query->orderBy('rank', 'asc');
           }))
           ->where('shop_id', '=', $shopId)
           ->where('active', '=', 1)
           ->whereNotNull('product_category_id')
           ->where(function ($query) {
                $query->WhereNotNull('discount_value');
                $query->where('discount_promotion', '=', '1');
           })
            ->orderBy(\DB::raw('product.rank = 0, product.rank'), 'ASC')
            ->get();
    }

    function selectAllNewItemsByShopId($shopId, $limit)
    {

           return $this->model
           ->with(array('subcategories', 'extraFields' => function ($query) {
            $query->with('extraField')->orderBy('id', 'desc');
           }, 'taxRate', 'productCategory',  'relatedProducts' => function ($query) {
            $query->with('productImages')->orderBy('rank', 'asc');
           }, 'productImages' => function ($query) {
            $query->orderBy('rank', 'asc');
           }))
           ->where('shop_id', '=', $shopId)
           ->where('active', '=', 1)
           ->whereNotNull('product_category_id')
           ->orderBy('created_at', 'DESC')
           ->limit($limit)
           ->get();
    }

    function selectAllByShopIdAndBrandId($shopId, $brandId)
    {

           return $this->model
           ->with(array('subcategories', 'extraFields' => function ($query) {
            $query->with('extraField')->orderBy('id', 'desc');
           }, 'taxRate', 'productCategory',  'relatedProducts' => function ($query) {
            $query->with('productImages')->orderBy('rank', 'asc');
           }, 'productImages' => function ($query) {
            $query->orderBy('rank', 'asc');
           }))
           ->where('shop_id', '=', $shopId)
           ->where('active', '=', 1)
           ->whereNotNull('product_category_id')
           ->where(function ($query) use ($brandId) {
                $query->where('brand_id', '=', $brandId);
                $query->orWhereHas('subcategories', function ($query) use ($brandId) {
                    $query->where('brand_id', '=', $brandId);
                });
           })
            ->whereNotNull('product_category_id')
            ->orderBy(\DB::raw('product.rank = 0, product.rank'), 'ASC')
            ->get();
    }


    function selectAllByShopIdFrontend($shopId)
    {

           return $this->model
           ->with(array('subcategories', 'extraFields' => function ($query) {
            $query->with('extraField')->orderBy('id', 'desc');
           }, 'taxRate', 'productCategory',  'relatedProducts' => function ($query) {
            $query->with('productImages')->orderBy('rank', 'asc');
           }, 'productImages' => function ($query) {
            $query->orderBy('rank', 'asc');
           }))
           ->where('shop_id', '=', $shopId)
           ->where('active', '=', 1)
           ->whereNotNull('product_category_id')
            ->whereNotNull('product_category_id')
            ->orderBy(\DB::raw('product.rank = 0, product.rank'), 'ASC')
            ->get();
    }



    function selectOneById($id)
    {

        $result = $this->model->with(array('productCategory', 'relatedProducts', 'productImages' => function ($query) {
            $query->orderBy('rank', 'asc');
        }))->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->where('active', '=', 1)->where('id', '=', $id)->get()->first();
        return $result;
    }

    function selectOneByShopIdAndId($shopId, $id)
    {

           return $this->model->with(
               array(
                'attributeGroup',
                'attributes' => function ($query) {
                    $query->with(
                        array(
                            'combinations' => function ($query) {
                                $query->with(
                                    array(
                                        'productAttribute',
                                        'attribute' => function ($query) {
                                            $query->with(
                                                array(
                                                    'attributeGroup'
                                                    )
                                            );
                                        }
                                        )
                                );
                            }
                            )
                    )->orderBy('default_on', 'desc');
                },
                'extraFields' => function ($query) {
                    $query->where(
                        'value',
                        '!=',
                        ''
                    )->orWhereNotNull('extra_field_default_value_id')->with(array('extraField',
                    'extraFieldDefaultValue'))->orderBy(
                        'id',
                        'desc'
                    );
                },
                'relatedProducts' => function ($query) {
                    $query->with(
                        'productImages',
                        'productCategory'
                    )->orderBy(
                        'rank',
                        'asc'
                    );
                },
                'productImages' => function ($query) {
                    $query->orderBy(
                        'rank',
                        'asc'
                    )->with(array('relatedProductAttributes',
                    'relatedAttributes'));
                })
           )->where('shop_id', '=', $shopId)->where('active', '=', 1)->whereNotNull('product_category_id')->where('id', '=', $id)->get()->first();
    }


    function selectOneByIdAndAttributeId($shopId, $id, $attributeId)
    {

           return $this->model
           ->with(
               array(
                'attributeGroup',
                'attributes' => function ($query) use ($attributeId) {
                    $query->with(
                        array(
                            'combinations' => function ($query) {
                                $query->with(
                                    array(
                                        'productAttribute',
                                        'attribute' => function ($query) {
                                            $query->with(
                                                array(
                                                    'attributeGroup'
                                                    )
                                            );
                                        }
                                        )
                                );
                            }
                            )
                    )->orderBy('default_on', 'desc');
                },
                'extraFields' => function ($query) {
                    $query->where('value', '!=', '')



                    ->orWhereNotNull('extra_field_default_value_id')
                    ->with(
                        array('extraField', 'extraFieldDefaultValue')
                    )
                    ->orderBy('id', 'desc');
                },
                    'taxRate',
                    'productCategory',
                    'relatedProducts' => function ($query) {
                        $query->with('productImages')->orderBy('rank', 'asc');
                    },
                    'productImages' => function ($query) {
                        $query->orderBy('rank', 'asc');
                    }
                )
           )




           ->where('active', '=', 1)
           ->whereNotNull('product_category_id')
           ->where('shop_id', '=', $shopId)
           ->where('id', '=', $id)->get()->first();
    }


    public function increaseAmounts($products)
    {
        if ($products->count()) {
            foreach ($products as $product) {
                if ($product->product_id) {
                    $this->model = $this->find($product->product_id);
                    if ($this->model) {
                        $attributes = array(
                            'title' => $this->model->title,
                            'amount' => $this->model->amount + $product->amount
                        );
                    }
                }

                $this->model->fill($attributes);

    

                $this->model->save();
            }
        }
    }



    public function reduceAmounts($products)
    {
        if ($products->count()) {
            foreach ($products as $product) {
                if ($product->product_id) {
                    $this->model = $this->find($product->product_id);
                    if ($this->model) {
                        $attributes = array(
                            'title' => $this->model->title,
                            'amount' => $this->model->amount - $product->amount
                        );
                    }
                }

                $this->model->fill($attributes);

    

                $this->model->save();
            }
        }
    }

    public function changeActive($productId)
    {

        $this->model = $this->find($productId);

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



            if (!$this->model->active) {
                $url = $this->model->shop->url.route('product.item', ['productId' => $this->model->id, 'productSlug' => $this->model->slug, 'categorySlug' => $this->model->productCategory->slug], null);
                $productCategoryUrl = $this->model->shop->url.route('product-category', ['slug' => $this->model->productCategory->slug], null);
                $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $productCategoryUrl, 'shop_id' => $this->model->shop_id));
            } else {
                $url = $this->model->shop->url.route('product.item', ['productId' => $this->model->id, 'productSlug' => $this->model->slug, 'categorySlug' => $this->model->productCategory->slug], null);
                $this->redirect->destroyByUrl($url);
            }


            return $this->model->save();
        }

        return false;
    }


    public function changeAmount($productId, $amount)
    {

        $this->model = $this->find($productId);

        if ($this->model) {
            $attributes = array(
                'amount' => $amount
            );

            $this->model->fill($attributes);



            return $this->model->save();
        }

        return false;
    }


    public function changeRank($productId, $rank)
    {

        $this->model = $this->find($productId);

        if ($this->model) {
            $attributes = array(
                'rank' => $rank
            );

            $this->model->fill($attributes);



            return $this->model->save();
        }

        return false;
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

    public function getImageModel()
    {
        return $this->modelImage;
    }

}
