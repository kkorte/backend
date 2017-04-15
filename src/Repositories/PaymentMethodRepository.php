<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\PaymentMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
 
class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{

    protected $model;

    public function __construct(PaymentMethod $model)
    {
        $this->model = $model;
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
            'title' => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id',
            'price' => 'nullable'

        );
        
        if ($id) {
            $rules['title'] =   'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id, '.$id.' = id';
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

    public function updateById(array $attributes, $id)
    {
        $this->model = $this->find($id);
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules($id));

        if ($validator->fails()) {
            return $validator;
        }
        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;


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

    function selectAllActiveByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->where('active', '=', 1)->get();
    }

    function selectOneByShopIdAndId($shopId, $id)
    {
        $result = $this->model->where('shop_id', '=', $shopId)->where('active', '=', 1)->where('id', '=', $id)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }

    function selectOneById($id)
    {
        $result = $this->model->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->where('active', '=', 1)->where('id', '=', $id)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }
    
    public function find($id)
    {
        return $this->model->find($id);
    }

    public function getModel() {
        return $this->model;
    }
}
