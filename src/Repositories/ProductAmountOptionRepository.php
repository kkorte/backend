<?php
namespace Hideyo\Ecommerce\Backend\Repositories;
 
use Hideyo\Ecommerce\Backend\Models\ProductAmountOption;
use Auth;
 
class ProductAmountOptionRepository implements ProductAmountOptionRepositoryInterface
{

    protected $model;

    public function __construct(ProductAmountOption $model, ProductRepositoryInterface $product)
    {
        $this->model = $model;
        $this->product = $product;
    }

    /**
     * The validation rules for the model.
     *
     * @param  integer  $AmountOptionId id attribute model    
     * @return array
     */
    private function rules($AmountOptionId = false)
    {
        $rules = array(
            'amount' => 'required'

        );
        
        if ($AmountOptionId) {
            $rules['amount'] =   'required';
        }

        return $rules;
    }

    public function create(array $attributes, $productId)
    {
        $product = $this->product->find($productId);
        $attributes['shop_id'] = Auth::guard('hideyobackend')->user()->selected_shop_id;
        $attributes['product_id'] = $product->id;
        $validator = \Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
            
        $this->model->fill($attributes);
        $this->model->save();
   
        return $this->model;
    }

    public function updateById(array $attributes, $productId, $AmountOptionId)
    {
        $this->model = $this->find($AmountOptionId);
        $attributes['product_id'] = $productId;
        $attributes['shop_id'] = Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules($AmountOptionId));

        if ($validator->fails()) {
            return $validator;
        }
        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
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

    public function destroy($AmountOptionId)
    {
        $this->model = $this->find($AmountOptionId);
        $this->model->save();

        return $this->model->delete();
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }

    function selectOneById($AmountOptionId)
    {
        $result = $this->model->with(array('relatedPaymentMethods'))->where('shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id)->where('active', '=', 1)->where('id', '=', $AmountOptionId)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }

    function selectAllActiveByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->where('active', '=', 1)->get();
    }

    function selectOneByShopIdAndId($shopId, $AmountOptionId)
    {
        $result = $this->model->with(array('relatedPaymentMethods' => function ($query) {
            $query->where('active', '=', 1);
        }))->where('shop_id', '=', $shopId)->where('active', '=', 1)->where('id', '=', $AmountOptionId)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }
    
    public function find($AmountOptionId)
    {
        return $this->model->find($AmountOptionId);
    }

    public function getModel()
    {
        return $this->model;
    }
    
}
