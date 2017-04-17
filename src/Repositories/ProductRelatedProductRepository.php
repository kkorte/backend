<?php
namespace Hideyo\Ecommerce\Backend\Repositories;
 
use Hideyo\Ecommerce\Backend\Models\ProductRelatedProduct;
use Hideyo\Ecommerce\Backend\Models\Product;
use Hideyo\Ecommerce\Backend\Repositories\ProductRepositoryInterface;
 
class ProductRelatedProductRepository implements ProductRelatedProductRepositoryInterface
{

    protected $model;

    public function __construct(ProductRelatedProduct $model, ProductRepositoryInterface $product)
    {
        $this->model = $model;
        $this->product = $product;
    }
  
    public function create(array $attributes, $productParentId)
    {
        $parentProduct = $this->product->find($productParentId);
   
        if (isset($attributes['products'])) {
            $parentProduct->relatedProducts()->attach($attributes['products']);
        }

        return $parentProduct->save();
    }

    private function updateEntity(array $attributes = array())
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
        $filename = $this->model->path;

        if (\File::exists($filename)) {
            \File::delete($filename);
        }

        return $this->model->delete();
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }

    function selectAllByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->get();
    }

    function selectAllByProductId($productId)
    {
         return $this->model->where('product_id', '=', $productId)->get();
    }
    
    public function find($id)
    {
        return $this->model->find($id);
    }

    public function getModel()
    {
        return $this->model;
    }
    
}
