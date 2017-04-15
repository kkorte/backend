<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\SendingPaymentMethodRelated;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
 
class SendingPaymentMethodRelatedRepository implements SendingPaymentMethodRelatedRepositoryInterface
{

    protected $model;

    public function __construct(SendingPaymentMethodRelated $model)
    {
        $this->model = $model;
    }
  
    public function create(array $attributes)
    {
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
            
        $this->model->fill($attributes);
        $this->model->save();

        if (isset($attributes['payment_methods'])) {
            $this->model->relatedPaymentMethods()->sync($attributes['payment_methods']);
        }
   
        return $this->model;
    }

    public function updateById(array $attributes, $id)
    {
        $this->model = $this->find($id);
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        return $this->updateEntity($attributes);
    }

    private function updateEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->model->fill($attributes);
            if (isset($attributes['payment_methods'])) {
                $this->model->relatedPaymentMethods()->sync($attributes['payment_methods']);
            }

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
        return $this->model->leftJoin('sending_method', 'sending_payment_method_related.sending_method_id', '=', 'sending_method.id')->leftJoin('payment_method', 'sending_payment_method_related.payment_method_id', '=', 'payment_method.id')->where('sending_method.shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->where('payment_method.shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }

    function selectAllActiveByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->where('active', '=', 1)->get();
    }

    function selectOneByShopIdAndId($shopId, $id)
    {
        $result = $this->model->with(array('relatedPaymentMethods'))->where('shop_id', '=', $shopId)->where('active', '=', 1)->where('id', '=', $id)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }

    function selectOneByShopIdAndPaymentMethodIdAndSendingMethodId($shopId, $paymentMethodId, $sendingMethodId)
    {
       

        $result = $this->model
        ->with(array('sendingMethod'
            => function ($query) use ($shopId) {
                $query->where('shop_id', '=', $shopId);
            }
        ))
        ->with(array('paymentMethod'
            => function ($query) use ($shopId) {
                $query->where('shop_id', '=', $shopId);
            }
        ))
            ->where('sending_method_id', '=', $sendingMethodId)
            ->where('payment_method_id', '=', $paymentMethodId)->get();
       
        if ($result->isEmpty()) {
            return false;
        }
            return $result->first();
    }

    function selectOneByPaymentMethodIdAndSendingMethodIdAdmin($sendingPaymentMethodId, $paymentMethodId)
    {
         $shopId = \Auth::guard('hideyobackend')->user()->selected_shop_id;

        $result = $this->model->with(array('sendingMethod' => function ($query) use ($shopId) {
            $query->where('shop_id', '=', $shopId);
        }))->with(array('paymentMethod' => function ($query) use ($shopId) {
            $query->where('shop_id', '=', $shopId);
        }))->where('sending_method_id', '=', $sendingPaymentMethodId)->where('payment_method_id', '=', $paymentMethodId)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }

    
    function selectOneByPaymentMethodIdAndSendingMethodId($sendingPaymentMethodId, $paymentMethodId)
    {
         $shopId = \Auth::guard('web')->user()->selected_shop_id;

        $result = $this->model->with(array('sendingMethod' => function ($query) use ($shopId) {
            $query->where('shop_id', '=', $shopId);
        }))->with(array('paymentMethod' => function ($query) use ($shopId) {
            $query->where('shop_id', '=', $shopId);
        }))->where('sending_method_id', '=', $sendingPaymentMethodId)->where('payment_method_id', '=', $paymentMethodId)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }
        
    public function find($id)
    {
        return $this->model->find($id);
    }
}
