<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\Invoice;
use Hideyo\Backend\Models\InvoiceRule;
use Hideyo\Backend\Models\InvoiceAddress;
use Hideyo\Backend\Models\InvoiceSendingMethod;
use Hideyo\Backend\Models\InvoicePaymentMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hideyo\Repositories\OrderRepositoryInterface;
use Hideyo\Repositories\ClientRepositoryInterface;
use Hideyo\Repositories\InvoiceAddressRepositoryInterface;
use Hideyo\Repositories\SendingMethodRepositoryInterface;
use Hideyo\Repositories\PaymentMethodRepositoryInterface;
 
class InvoiceRepository implements InvoiceRepositoryInterface
{

    protected $model;

    public function __construct(
        Invoice $model,
        OrderRepositoryInterface $order,
        ClientRepositoryInterface $client,
        InvoiceAddressRepositoryInterface $invoiceAddress,
        SendingMethodRepositoryInterface $sendingMethod,
        PaymentMethodRepositoryInterface $paymentMethod
    ) {
        $this->model = $model;
        $this->client = $client;
        $this->order = $order;
        $this->invoiceAddress = $invoiceAddress;
        $this->paymentMethod = $paymentMethod;

        $this->sendingMethod = $sendingMethod;
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
            'order_id' => 'required|unique:invoice',
        );

        return $rules;
    }

  
    public function create(array $attributes)
    {
        $attributes['shop_id'] = \Auth::user()->selected_shop_id;
        $attributes['modified_by_user_id'] = \Auth::user()->id;
        $this->model->fill($attributes);
        $this->model->save();

        if (isset($attributes['categories'])) {
            $this->model->categories()->sync($attributes['categories']);
        }
        
        return $this->model;
    }

    public function generateInvoiceFromOrder($orderId)
    {
        
        $order = $this->order->find($orderId);

        if ($order->count()) {
            $attributes = $order->toArray();
            $attributes['order_id'] = $order->id;

            $validator = \Validator::make($attributes, $this->rules());

            if ($validator->fails()) {
                return $validator;
            }


            $this->model->fill($attributes);
            $this->model->save();
        
            if ($this->model->id) {
                if ($order->products) {
                    foreach ($order->products as $product) {
                        $product = $product->toArray();
                        $product['product_id'] = $product['product_id'];

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

                        $products[] = new InvoiceRule($product);
                    }

                    if ($order->orderSendingMethod) {
                        $invoiceRule = array(
                            'type' => 'sending_cost',
                            'title' => $order->orderSendingMethod->title,
                            'tax_rate_id' =>  $order->orderSendingMethod->tax_rate_id,
                            'tax_rate' =>  $order->orderSendingMethod->tax_rate,
                            'amount' =>  1,
                            'price_with_tax' =>  $order->orderSendingMethod->price_with_tax,
                            'price_without_tax' =>  $order->orderSendingMethod->price_without_tax,
                            'total_price_with_tax' =>  $order->orderSendingMethod->price_with_tax,
                            'total_price_without_tax' =>  $order->orderSendingMethod->price_without_tax,
                        );

                        $products[] = new InvoiceRule($invoiceRule);
                    }

                    if ($order->orderPaymentMethod) {
                        $invoiceRule = array(
                            'type' => 'payment_cost',
                            'title' => $order->orderPaymentMethod->title,
                            'tax_rate_id' =>  $order->orderPaymentMethod->tax_rate_id,
                            'tax_rate' =>  $order->orderPaymentMethod->tax_rate,
                            'amount' =>  1,
                            'price_with_tax' =>  $order->orderPaymentMethod->price_with_tax,
                            'price_without_tax' =>  $order->orderPaymentMethod->price_without_tax,
                            'total_price_with_tax' =>  $order->orderPaymentMethod->price_with_tax,
                            'total_price_without_tax' =>  $order->orderPaymentMethod->price_without_tax,
                        );

                        $products[] = new InvoiceRule($invoiceRule);
                    }

                    $this->model->products()->saveMany($products);
                }

                if ($order->orderBillAddress and $order->orderDeliveryAddress) {
                    $deliveryInvoiceAddress = new InvoiceAddress($order->orderBillAddress->toArray());
       
                    $billInvoiceAddress = new InvoiceAddress($order->orderDeliveryAddress->toArray());

                    $this->model->invoiceAddress()->saveMany(array($deliveryInvoiceAddress, $billInvoiceAddress));
     
                    $this->model->fill(array('delivery_invoice_address_id' => $deliveryInvoiceAddress->id, 'bill_invoice_address_id' => $billInvoiceAddress->id));
                    $this->model->save();
                }
            }

            return $this->model;
        }
    }

    public function createByUserAndShop(array $attributes, $shopId)
    {
        $attributes['shop_id'] = $shopId;
        $attributes['client_id'] = $attributes['user_id'];
        $client  = $this->client->selectOneByShopIdAndId($shopId, $attributes['user_id']);

        $this->model->fill($attributes);
        $this->model->save();

        if (isset($attributes['products'])) {
            foreach ($attributes['products'] as $product) {
                $product['product_id'] = $product['id'];
                $products[] = new InvoiceRule($product);
            }

            $this->model->products()->saveMany($products);
        }

        $deliveryInvoiceAddress = new $this->invoiceAddress(new InvoiceAddress());
        $deliveryInvoiceAddress = $deliveryInvoiceAddress->create($client->clientDeliveryAddress->toArray(), $this->model->id);

        $billInvoiceAddress = new $this->invoiceAddress(new InvoiceAddress());
        $billInvoiceAddress = $billInvoiceAddress->create($client->clientBillAddress->toArray(), $this->model->id);

        $this->model->fill(array('delivery_order_address_id' => $deliveryInvoiceAddress->id, 'bill_order_address_id' => $billInvoiceAddress->id));
        $this->model->save();

        if (isset($attributes['sending_method'])) {
            $sendingMethod = $this->sendingMethod->find($attributes['sending_method']);
            $price = $sendingMethod->getPriceDetails();

            $sendingMethodArray = $sendingMethod->toArray();
            $sendingMethodArray['price_with_tax'] = $price['orginal_price_inc_tax'];
            $sendingMethodArray['price_without_tax'] = $price['orginal_price_ex_tax'];
            $sendingMethodArray['tax_rate'] = $price['tax_rate'];
            $sendingMethodArray['sending_method_id'] = $attributes['sending_method'];

            $orderSendingMethod = new InvoiceSendingMethod($sendingMethodArray);
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

            $orderPaymentMethod = new InvoicePaymentMethod($paymentMethodArray);
            $this->model->orderPaymentMethod()->save($orderPaymentMethod);
        }

        return $this->model;
    }

    public function updateById(array $attributes, $id)
    {
        $this->model = $this->find($id);
        $attributes['shop_id'] = \Auth::user()->selected_shop_id;
        $attributes['modified_by_user_id'] = \Auth::user()->id;
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

    public function selectAllByAllProductsAndProductCategoryId($productCategoryId)
    {
        return $this->model->select('extra_field.*')->leftJoin('product_category_related_extra_field', 'extra_field.id', '=', 'product_category_related_extra_field.extra_field_id')->where('all_products', '=', 1)->orWhere('product_category_related_extra_field.product_category_id', '=', $productCategoryId)->get();
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', \Auth::user()->selected_shop_id)->get();
    }
    
    public function find($id)
    {
        return $this->model->find($id);
    }
}
