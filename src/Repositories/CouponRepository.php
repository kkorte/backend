<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\Coupon;
use Hideyo\Backend\Models\CouponGroup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Validator;
 
class CouponRepository implements CouponRepositoryInterface
{

    protected $model;

    public function __construct(Coupon $model, CouponGroup $couponGroup)
    {
        $this->model = $model;
        $this->modelGroup = $couponGroup;
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
            'title' => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id',
            'code' => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id',
            'product_id' => 'integer',
            'product_category_id' => 'integer'
        );
        
        if ($id) {
            $rules['title'] =   'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id, '.$id.' = id';
            $rules['code'] =   'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id, '.$id.' = id';
        }

        return $rules;
    }

    public function rulesGroup($id = false, $attributes = false)
    {

        $rules = array(
            'title'                 => 'required|between:4,65|unique_with:'.$this->modelGroup->getTable().', shop_id'
        );
        
        if ($id) {
            $rules['title'] =   'required|between:4,65|unique_with:'.$this->modelGroup->getTable().', shop_id, '.$id.' = id';
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
        
        if (isset($attributes['product_categories'])) {
            $this->model->productCategories()->sync($attributes['product_categories']);
        }

        if (isset($attributes['products'])) {
            $this->model->products()->sync($attributes['products']);
        }

        if (isset($attributes['sending_methods'])) {
            $this->model->sendingMethods()->sync($attributes['sending_methods']);
        }

        if (isset($attributes['payment_methods'])) {
            $this->model->paymentMethods()->sync($attributes['payment_methods']);
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


    public function updateById(array $attributes, $id)
    {
        $this->model = $this->find($id);
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;

        $validator = Validator::make($attributes, $this->rules($id));

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

            if (isset($attributes['product_categories'])) {
                $this->model->productCategories()->sync($attributes['product_categories']);
            } else {
                $this->model->productCategories()->sync(array());
            }

            if (isset($attributes['products'])) {
                $this->model->products()->sync($attributes['products']);
            } else {
                $this->model->products()->sync(array());
            }

            if (isset($attributes['sending_methods'])) {
                $this->model->sendingMethods()->sync($attributes['sending_methods']);
            } else {
                $this->model->sendingMethods()->sync(array());
            }

            if (isset($attributes['payment_methods'])) {
                $this->model->paymentMethods()->sync($attributes['payment_methods']);
            } else {
                $this->model->paymentMethods()->sync(array());
            }

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

    public function updateGroupEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->modelGroup->fill($attributes);
            $this->modelGroup->save();
        }

        return $this->modelGroup;
    }


    public function destroy($id)
    {
        $this->model = $this->find($id);
        $this->model->save();

        return $this->model->delete();
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
    

    public function selectAllGroups()
    {
        return $this->modelGroup->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }



    function selectOneByShopIdAndCode($shopId, $code)
    {
        $dt = Carbon::now('Europe/Amsterdam');
        $result = $this->model
        ->where('shop_id', '=', $shopId)
        ->where('active', '=', 1)
        ->where('code', '=', $code)
        // ->where('published_at', '<=', $dt->toDateString('Y-m-d'))
        // ->where('unpublished_at', '>=', $dt->toDateString('Y-m-d'))
        ->with(array('products', 'productCategories', 'sendingMethods', 'paymentMethods'))
        ->get();
        
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

    
}
