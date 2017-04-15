<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\ProductTagGroup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
 
class ProductTagGroupRepository implements ProductTagGroupRepositoryInterface
{

    protected $model;

    public function __construct(ProductTagGroup $model)
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
            'tag' => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id'

        );
        
        if ($id) {
            $rules['tag'] =   'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id, '.$id.' = id';
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

        if (isset($attributes['products'])) {
            $this->model->relatedProducts()->sync($attributes['products']);
        }
   
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
        
            if (isset($attributes['products'])) {
                $this->model->relatedProducts()->sync($attributes['products']);
            }
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
        $result = $this->model->with(array('relatedPaymentMethods' => function ($query) {
            $query->where('active', '=', 1);
        }))->where('shop_id', '=', $shopId)->where('active', '=', 1)->where('id', '=', $id)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }



    function selectAllByTagAndShopId($shopId, $tag)
    {
        $result = $this->model->with(array('relatedProducts' => function ($query) {
            $query->with(array('productCategory', 'productImages' => function ($query) {
                $query->orderBy('rank', 'asc');
            }))->where('active', '=', 1);
        }))->where('shop_id', '=', $shopId)->where('tag', '=', $tag)->get();
        if ($result->count()) {
            return $result->first()->relatedProducts;
        } else {
            return false;
        }
    }
    

    function selectOneById($id)
    {
        $result = $this->model->with(array('relatedPaymentMethods'))->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->where('active', '=', 1)->where('id', '=', $id)->get();
        
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
