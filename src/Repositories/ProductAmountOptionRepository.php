<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\ProductAmountOption;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
 
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
     * @param  integer  $id id attribute model    
     * @return array
     */
    private function rules($id = false)
    {
        $rules = array(
            'amount' => 'required'

        );
        
        if ($id) {
            $rules['amount'] =   'required';
        }

        return $rules;
    }

  
    public function create(array $attributes, $productId)
    {
        $product = $this->product->find($productId);
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
              $attributes['product_id'] = $product->id;
        $validator = \Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
            
        $this->model->fill($attributes);
        $this->model->save();
   
        return $this->model;
    }

    public function updateById(array $attributes, $productId, $id)
    {
        $this->model = $this->find($id);
                $attributes['product_id'] = $productId;
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules($id));

        if ($validator->fails()) {
            return $validator;
        }
        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
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

    public function destroy($id)
    {
        $this->model = $this->find($id);
        $this->model->save();

        return $this->model->delete();
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
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
    
    public function find($id)
    {
        return $this->model->find($id);
    }


    public function getModel()
    {
        return $this->model;
    }
    
}
