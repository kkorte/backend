<?php
namespace Hideyo\Ecommerce\Backend\Repositories;
 
use Hideyo\Ecommerce\Backend\Models\Order;
use Hideyo\Ecommerce\Backend\Models\OrderProduct;
use Hideyo\Ecommerce\Backend\Repositories\ClientRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\OrderAddressRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\SendingMethodRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\PaymentMethodRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\ClientAddressRepositoryInterface;
use DB;
use Carbon\Carbon;
use Auth;
 
class OrderRepository implements OrderRepositoryInterface
{

    protected $model;

    public function __construct(
        Order $model,
        OrderProduct $modelOrderProduct,
        ClientRepositoryInterface $client,
        ClientAddressRepositoryInterface $clientAddress,
        OrderAddressRepositoryInterface $orderAddress,
        SendingMethodRepositoryInterface $sendingMethod,
        PaymentMethodRepositoryInterface $paymentMethod
    ) {
        $this->model = $model;
        $this->modelOrderProduct = $modelOrderProduct;
        $this->client = $client;
        $this->orderAddress = $orderAddress;
        $this->clientAddress = $clientAddress;
        $this->paymentMethod = $paymentMethod;
        $this->sendingMethod = $sendingMethod;
    }
  
    public function create(array $attributes)
    {
        $attributes['shop_id'] = Auth::guard('hideyobackend')->user()->selected_shop_id;
        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
        $this->model->fill($attributes);
        $this->model->save();

        if (isset($attributes['categories'])) {
            $this->model->categories()->sync($attributes['categories']);
        }
        
        return $this->model;
    }

    public function updateStatus($id, $orderStatusId)
    {
        $this->model = $this->find($id);

        $attributes['order_status_id'] = $orderStatusId;
        if (count($attributes) > 0) {
            $this->model->fill($attributes);
            $this->model->save();
        }

        return $this->model;
    }

    public function updateById(array $attributes, $id)
    {
        $this->model = $this->find($id);
        if (Auth::guard('hideyobackend')->check()) {
            $attributes['shop_id'] = Auth::guard('hideyobackend')->user()->selected_shop_id;
            $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
        }

        return $this->updateEntity($attributes);
    }

    private function updateEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->model->fill($attributes);

        
            if (isset($attributes['categories'])) {
                $this->model->categories()->sync($attributes['categories']);
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

    public function selectAllByShopIdAndStatusId($orderStatusId, $startDate = false, $endDate = false, $shopId = false)
    {
        $query = $this->model
        ->where('shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id)
        ->where('order_status_id', '=', $orderStatusId);

        if ($startDate) {
            $dt = Carbon::createFromFormat('d/m/Y', $startDate);

            $query->where('created_at', '>=', $dt->toDateString('Y-m-d'));
        }


        if ($endDate) {
            $dt = Carbon::createFromFormat('d/m/Y', $endDate);

            $query->where('created_at', '<=', $dt->toDateString('Y-m-d'));
        }


        $query->orderBy('created_at', 'ASC');

        return $query->get();
    }

    public function selectAllByAllProductsAndProductCategoryId($productCategoryId)
    {
        return $this->model->select('extra_field.*')->leftJoin('product_category_related_extra_field', 'extra_field.id', '=', 'product_category_related_extra_field.extra_field_id')->where('all_products', '=', 1)->orWhere('product_category_related_extra_field.product_category_id', '=', $productCategoryId)->get();
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }


    public function orderProductsByClientId($clientId, $shopId)
    {

        return $this->modelOrderProduct->with(array('product'))->whereHas('Order', function ($query) use ($clientId, $shopId) {
            $query->where('client_id', '=', $clientId)->where('shop_id', '=', $shopId);
        });
    }


    public function selectAllByCompany()
    {
        return $this->model->leftJoin('shop', 'order.shop_id', '=', 'shop.id')->get();
    }
    
    public function find($id)
    {
        return $this->model->find($id);
    }

    public function productsByOrderIds(array $orderIds) {


        $result = DB::table('order_product')
        ->select(DB::raw('DISTINCT(CONCAT_WS(\' - \',order_product.title, IFNULL(order_product.product_attribute_title, \'\'))) as title, order_product.product_attribute_title, order_product.reference_code, order_product.price_with_tax, order_product.price_without_tax,  SUM(order_product.amount) as total_amount'))
        ->whereIn('order_product.order_id', $orderIds)
        ->whereNotNull('order_product.product_attribute_title')
        ->groupBy('order_product.title', 'order_product.product_attribute_title')
        ->get();


        $result2 = DB::table('order_product')
        ->select(DB::raw('DISTINCT(order_product.title) as title, order_product.product_attribute_title, order_product.reference_code, order_product.price_with_tax, order_product.price_without_tax,  SUM(order_product.amount) as total_amount'))
        ->whereIn('order_product.order_id', $orderIds)
        ->whereNull('order_product.product_attribute_title')
        ->groupBy('order_product.title', 'order_product.product_attribute_title')
        ->get();

        $result = array_merge($result, $result2);
        return $result;
    }


    public function getModel()
    {
        return $this->model;
    }
    
}
