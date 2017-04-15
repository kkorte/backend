<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\Order;
use Hideyo\Backend\Models\OrderProduct;
use Hideyo\Backend\Models\OrderAddress;
use Hideyo\Backend\Models\OrderSendingMethod;
use Hideyo\Backend\Models\OrderPaymentMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hideyo\Backend\Repositories\ClientRepositoryInterface;
use Hideyo\Backend\Repositories\OrderAddressRepositoryInterface;
use Hideyo\Backend\Repositories\SendingMethodRepositoryInterface;
use Hideyo\Backend\Repositories\PaymentMethodRepositoryInterface;
use Hideyo\Backend\Repositories\ClientAddressRepositoryInterface;
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

    public function createByAdmin(array $attributes)
    {
        $attributes['client_id'] = $attributes['client_id'];
        $attributes['shop_id'] = Auth::guard('hideyobackend')->user()->selected_shop_id;
        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;

        $client  = $this->client->selectOneById($attributes['client_id']);

        $this->model->fill($attributes);
        $this->model->save();

        if (isset($attributes['products'])) {
            foreach ($attributes['products'] as $product) {

                $product['product_id'] = $product['id'];
                if (isset($product['product_id'])) {
                    $product['product_id'] = $product['product_id'];
                }
                
                if (isset($product['product_combination_id'])) {
                    $product['product_attribute_id'] = $product['product_combination_id'];
                    $productCombinationTitleArray = array();
                    if (isset($product['product_combination_title']) and is_array($product['product_combination_title'])) {
                        foreach ($product['product_combination_title'] as $key => $val) {
                            $productCombinationTitle[] = $key.': '.$val;
                        }

                        $product['product_attribute_title'] = implode(', ', $productCombinationTitle);
                    }
                }

                $product['amount'] = $product['cart']['count'];

                $products[] = new OrderProduct($product);
            }

            $this->model->products()->saveMany($products);
        }

        if (isset($attributes['sending_method'])) {
            $sendingMethod = $this->sendingMethod->find($attributes['sending_method']);
            $price = $sendingMethod->getPriceDetails();

            $sendingMethodArray = $sendingMethod->toArray();
            $sendingMethodArray['price_with_tax'] = $price['orginal_price_inc_tax'];
            $sendingMethodArray['price_without_tax'] = $price['orginal_price_ex_tax'];
            $sendingMethodArray['tax_rate'] = $price['tax_rate'];
            $sendingMethodArray['sending_method_id'] = $attributes['sending_method'];

            $orderSendingMethod = new OrderSendingMethod($sendingMethodArray);
            $this->model->orderSendingMethod()->save($orderSendingMethod);
        }

        if (isset($attributes['payment_method'])) {
            $paymentMethod = $this->paymentMethod->find($attributes['payment_method']);
            $price = $paymentMethod->getPriceDetails();

            $paymentMethodArray = $paymentMethod->toArray();
            $paymentMethodArray['price_with_tax'] = $price['orginal_price_inc_tax'];
            $paymentMethodArray['price_without_tax'] = $price['orginal_price_ex_tax'];
            $paymentMethodArray['tax_rate'] = $price['tax_rate'];
            $paymentMethodArray['payment_method_id'] = $attributes['payment_method'];

            $orderPaymentMethod = new OrderPaymentMethod($paymentMethodArray);
            $this->model->orderPaymentMethod()->save($orderPaymentMethod);
        }

        if (isset($attributes['client_bill_address_id']) and isset($attributes['client_delivery_address_id'])) {
            $deliveryOrderAddress = new $this->orderAddress(new OrderAddress());

            $clientAddress = $this->clientAddress->find($attributes['client_bill_address_id']);
    
            $deliveryOrderAddress = $deliveryOrderAddress->create($clientAddress->toArray(), $this->model->id);

            $billOrderAddress = new $this->orderAddress(new OrderAddress());
            $clientAddress = $this->clientAddress->find($attributes['client_delivery_address_id']);
            $billOrderAddress = $billOrderAddress->create($clientAddress->toArray(), $this->model->id);

            $this->model->fill(array('delivery_order_address_id' => $deliveryOrderAddress->id, 'bill_order_address_id' => $billOrderAddress->id));
            $this->model->save();
        }
    }

    public function createByUserAndShop(array $attributes, $shopId, $noAccountUser)
    {

        $attributes['shop_id'] = $shopId;

        $attributes['client_id'] = $attributes['user_id'];
        $client  = $this->client->selectOneByShopIdAndId($shopId, $attributes['user_id']);

        $this->model->fill($attributes);
        $this->model->save();

        if (isset($attributes['products'])) {
            foreach ($attributes['products'] as $product) {
                $product['product_id'] = $product['id'];
                if (isset($product['product_id'])) {
                    $product['product_id'] = $product['product_id'];
                }
    
                if (isset($product['product_combination_id'])) {
                    $product['product_attribute_id'] = $product['product_combination_id'];
                          $productCombinationTitleArray = array();
                    if (isset($product['product_combination_title']) and is_array($product['product_combination_title'])) {
                        $productCombinationTitle = array();
                        foreach ($product['product_combination_title'] as $key => $val) {
                            $productCombinationTitle[] = $key.': '.$val;
                        }

                        $product['product_attribute_title'] = implode(', ', $productCombinationTitle);
                    }
                }

                if (isset($product['cart'])) {
                    $product['amount'] = $product['cart']['count'];
                    $products[] = new OrderProduct($product);
                }
            }

            if (isset($attributes['present'])) {
                $presentProduct = array(
                    'title' => 'cadeauservice',
                    'amount' => 1,
                    'tax_rate' => $attributes['present']['tax_rate'],
                    'price_with_tax' => $attributes['present']['cost_inc_tax'],
                    'total_price_with_tax' => $attributes['present']['cost_inc_tax'],
                    'price_without_tax' => $attributes['present']['cost_ex_tax'],
                    'total_price_without_tax' => $attributes['present']['cost_ex_tax'],
                    'original_price_with_tax' => $attributes['present']['cost_inc_tax'],
                    'original_price_without_tax' => $attributes['present']['cost_ex_tax'],
                    'original_total_price_without_tax' => $attributes['present']['cost_ex_tax'],
                    'original_total_price_with_tax' => $attributes['present']['cost_inc_tax']
                );

                $products[] = new OrderProduct($presentProduct);
            }


            $this->model->products()->saveMany($products);
        }


        if ($client and !$noAccountUser) {
            if ($client->clientBillAddress) {
                $billOrderAddress = new $this->orderAddress(new OrderAddress());
                $billOrderAddress = $billOrderAddress->create($client->clientBillAddress->toArray(), $this->model->id);
            }

            $deliveryOrderAddress = $billOrderAddress;
            if ($client->clientDeliveryAddress) {
                $deliveryOrderAddress = new $this->orderAddress(new OrderAddress());
                $deliveryOrderAddress = $deliveryOrderAddress->create($client->clientDeliveryAddress->toArray(), $this->model->id);
            }

            $this->model->fill(array('delivery_order_address_id' => $deliveryOrderAddress->id, 'bill_order_address_id' => $billOrderAddress->id));
            $this->model->save();
        } elseif ($noAccountUser) {
            $deliveryOrderAddress = new $this->orderAddress(new OrderAddress());

            $deliveryOrderAddress = $deliveryOrderAddress->create($noAccountUser, $this->model->id);
            if (isset($noAccountUser['delivery'])) {
                $deliveryOrderAddress = $deliveryOrderAddress->create($noAccountUser['delivery'], $this->model->id);
            }

            $billOrderAddress = new $this->orderAddress(new OrderAddress());
            $billOrderAddress = $billOrderAddress->create($noAccountUser, $this->model->id);

            $this->model->fill(array('delivery_order_address_id' => $deliveryOrderAddress->id, 'bill_order_address_id' => $billOrderAddress->id));
            $this->model->save();
        }


        if (isset($attributes['sending_method'])) {
            $sendingMethod = $this->sendingMethod->find($attributes['sending_method']);
            $price = $sendingMethod->getPriceDetails();

            $sendingMethodArray = $sendingMethod->toArray();
            $sendingMethodArray['price_with_tax'] = $attributes['sending_method_cost_inc_tax'];
            $sendingMethodArray['price_without_tax'] = $attributes['sending_method_cost_ex_tax'];
            $sendingMethodArray['tax_rate'] = $price['tax_rate'];
            $sendingMethodArray['sending_method_id'] = $attributes['sending_method'];

            $orderSendingMethod = new OrderSendingMethod($sendingMethodArray);
            $this->model->orderSendingMethod()->save($orderSendingMethod);
        }

        if (isset($attributes['payment_method'])) {
            $paymentMethod = $this->paymentMethod->find($attributes['payment_method']);
            $price = $paymentMethod->getPriceDetails();

            $paymentMethodArray = $paymentMethod->toArray();
            $paymentMethodArray['price_with_tax'] = $attributes['payment_method_cost_inc_tax'];
            $paymentMethodArray['price_without_tax'] = $attributes['payment_method_cost_ex_tax'];
            $paymentMethodArray['tax_rate'] = $price['tax_rate'];
            $paymentMethodArray['payment_method_id'] = $attributes['payment_method'];

            $orderPaymentMethod = new OrderPaymentMethod($paymentMethodArray);
            $this->model->orderPaymentMethod()->save($orderPaymentMethod);
        }

        if ($this->model->orderPaymentMethod->paymentMethod->order_confirmed_order_status_id) {
            $this->model->fill(array('order_status_id' => $this->model->orderPaymentMethod->paymentMethod->order_confirmed_order_status_id));
            $this->model->save();
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

    public function updateByIdFrontend(array $attributes, $id)
    {
        $this->model = $this->find($id);
        return $this->updateEntity($attributes);
    }


    public function updateEntity(array $attributes = array())
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

    public function monthlyRevenue($year, $month = false)
    {
  
        $result = DB::table(config()->get('hideyo.db_prefix').'order as order')
        ->select(DB::raw('YEAR(order.created_at) as SalesYear, MONTH(order.created_at) as SalesMonth, COUNT(order.id) as total_orders, SUM(order.price_with_tax) as price_with_tax, DATE_FORMAT(order.created_at, "%M") AS dm'))
        ->leftJoin(config()->get('hideyo.db_prefix').'order_status as order_status', 'order_status.id', '=', 'order.order_status_id')
        ->where(DB::raw('YEAR(order.created_at)'), $year)
        ->where('order.shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id);

        if ($month) {
            $result->where(DB::raw('MONTH(order.created_at)'), $month);
        }

        if ($year >= '2016') {
            $result->where('order_status.count_as_revenue', '=', '1');
        }
        

        $result->groupBy('dm')
        ->orderBy('order.created_at', 'ASC');

        return $result->get();
    }

    public function yearsRevenue()
    {
  
        $result = DB::table(config()->get('hideyo.db_prefix').'order as order')
        ->select(DB::raw('YEAR(order.created_at) as SalesYear, SUM(order.price_with_tax) as price_with_tax, DATE_FORMAT(order.created_at, "%Y") AS year'))
        ->leftJoin(config()->get('hideyo.db_prefix').'order_status as order_status', 'order_status.id', '=', 'order.order_status_id')
        ->where('order.shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id);

 

        $result->where(function ($query) {
            $query->where('order_status.count_as_revenue', '=', '1');
            $query->orWhere('order.created_at', '<=', '2016');
        });


        $result->groupBy('year')
        ->orderBy('order.created_at', 'ASC');

        return $result->get();
    }



    public function browserDetectOrdersInformation($year, $month = false)
    {

        $result = DB::table(config()->get('hideyo.db_prefix').'order as order')
        ->select(DB::raw('MONTH(order.created_at) as SalesMonth, SUM(order.browser_detect LIKE \'%"isMobile";b:1;%\') AS mobile, SUM(order.browser_detect LIKE \'%"isTablet";b:1;%\') AS tablet,  SUM(order.browser_detect LIKE \'%"isDesktop";b:1;%\') AS desktop, DATE_FORMAT(order.created_at, "%M") AS dm'))
        ->leftJoin(config()->get('hideyo.db_prefix').'order_status as order_status', 'order_status.id', '=', 'order.order_status_id')
        ->where(DB::raw('YEAR(order.created_at)'), $year)
        ->where('order.shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id);

        if ($month) {
            $result->where(DB::raw('MONTH(order.created_at)'), $month);
        }

        if ($year >= '2016') {
            $result->where('order_status.count_as_revenue', '=', '1');
        }
        

        $result->orderBy('mobile');
        $result->orderBy('desktop');
        $result->orderBy('tablet');

        $result->groupBy('dm')
        ->orderBy('order.created_at', 'ASC');


        return $result->get();
    }


    public function paymentMethodOrdersInformation($year, $month = false)
    {

        $result = DB::table(config()->get('hideyo.db_prefix').'order as order')
        ->select(DB::raw('DATE_FORMAT(order.created_at, "%Y") AS dm,   order_payment_method.title as paymenttitle, COUNT(*) as count'))
        ->leftJoin(config()->get('hideyo.db_prefix').'order_status as order_status', 'order_status.id', '=', 'order.order_status_id')
        ->leftJoin('order_payment_method', 'order_payment_method.order_id', '=', 'order.id')

        ->where(DB::raw('YEAR(order.created_at)'), $year)
        ->where('order.shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id);

        if ($month) {
            $result->where(DB::raw('MONTH(order.created_at)'), $month);
        }

        if ($year >= '2016') {
            $result->where('order_status.count_as_revenue', '=', '1');
        }
        

  


        $result->groupBy('dm', 'paymenttitle')
        ->orderBy('order.created_at', 'ASC');


        return $result->get();
    }



    public function monthlyRevenueYears()
    {
        return $this->model->
        select(DB::raw('DISTINCT YEAR(created_at) as year'))
        ->where('shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id)
        
        ->get();
    }



    public function updateMolliePaymentId($id, $paymentId)
    {
        $this->model = $this->find($id);

        $attributes['mollie_payment_id'] = $paymentId;
        if (count($attributes) > 0) {
            $this->model->fill($attributes);
            $this->model->save();
        }

        return $this->model;
    }

    public function getModel()
    {
        return $this->model;
    }
    
}
