<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\OrderStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
 
class OrderStatusRepository implements OrderStatusRepositoryInterface
{

    protected $model;

    public function __construct(OrderStatus $model)
    {
        $this->model = $model;
    }

    /**
     * The validation rules for the model.
     *
     * @param  integer  $id id attribute model    
     * @return array
     */  
    public function rules($id = false)
    {
        $rules = array(
            'title' => 'required|between:4,65|unique_with:payment_method, shop_id'

        );
        
        if ($id) {
            $rules['title'] =   'required|between:4,65|unique_with:payment_method, shop_id, '.$id.' = id';
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

        $this->model->fill($attributes);
        $this->model->save();
        return $this->model;
    }

    public function updateById(array $attributes, $id)
    {
        $validator = \Validator::make($attributes, $this->rules($id));

        if ($validator->fails()) {
            return $validator;
        }
        
        $this->model = $this->find($id);
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
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
    
    public function find($id)
    {
        return $this->model->find($id);
    }

    public function getModel()
    {
        return $this->model;
    }
    
}
