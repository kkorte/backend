<?php namespace Hideyo\Backend\Controllers;

/**
 * ProductWeightTypeController
 *
 * This is the controller of the product weight types of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Backend\Repositories\OrderRepositoryInterface;
use Hideyo\Backend\Repositories\ProductRepositoryInterface;
use Hideyo\Backend\Repositories\SendingMethodRepositoryInterface;
use Hideyo\Backend\Repositories\SendingPaymentMethodRelatedRepositoryInterface;
use Hideyo\Backend\Repositories\PaymentMethodRepositoryInterface;
use Hideyo\Backend\Repositories\ClientAddressRepositoryInterface;
use Hideyo\Backend\Repositories\ClientRepositoryInterface;
use Hideyo\Backend\Repositories\OrderStatusRepositoryInterface;
use Dutchbridge\Services\AssembleOrder;

use Carbon\Carbon;
use \Request;
use \Notification;
use \Redirect;
use \Log;
use \App\ProductAttribute;
use \App\Events\OrderChangeStatus;
use \Event;
use \Response;
use \View;
use Excel;

class OrderController extends Controller
{
    public function __construct(
        OrderRepositoryInterface $order,
        ProductRepositoryInterface $product,
        PaymentMethodRepositoryInterface $paymentMethod,
        SendingMethodRepositoryInterface $sendingMethod,
        SendingPaymentMethodRelatedRepositoryInterface $sendingPaymentMethodRelated,
        ClientAddressRepositoryInterface $clientAddress,
        OrderStatusRepositoryInterface $orderStatus,
        ClientRepositoryInterface $client
    ) {
        $this->order = $order;
        $this->product = $product;

        $this->sendingMethod = $sendingMethod;
        $this->paymentMethod = $paymentMethod;
        $this->clientAddress = $clientAddress;
        $this->client = $client;
        $this->orderStatus = $orderStatus;
        $this->sendingPaymentMethodRelated = $sendingPaymentMethodRelated;
    }

    public function index()
    {
        $shop  = \Auth::guard('hideyobackend')->user()->shop;
        $now = Carbon::now();

        $revenueThisMonth = null;

        if ($shop->wholesale) {
            if (Request::wantsJson()) {

                $order = $this->order->getModel()->select(
                    ['order.id',
                    'order.created_at',
                    'order.generated_custom_order_id',
                    'order.order_status_id',
                    'order.client_id',
                    'order.delivery_order_address_id',
                    'order.bill_order_address_id',
                    'order.price_with_tax']
                )->with(array('orderStatus', 'orderPaymentMethod', 'orderSendingMethod', 'products', 'client', 'orderBillAddress', 'orderDeliveryAddress'))->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)



                ->leftJoin(config()->get('hideyo.db_prefix').'order_address', 'order.bill_order_address_id', '=', 'order_address.id');
                
                
                $datatables = \Datatables::of($order)

                ->addColumn('created_at', function ($order) {
                    return date('d F H:i', strtotime($order->created_at));
                })

                ->addColumn('status', function ($order) {
                    if ($order->orderStatus) {
                        if ($order->orderStatus->color) {
                            return '<span style="background-color:'.$order->orderStatus->color.'; padding: 10px; line-height:30px; text-align:center; color:white;">'.$order->orderStatus->title.'</span>';
                        }
                        return $order->orderStatus->title;
                    }
                })

                ->addColumn('company', function ($order) {
                    if ($order->client) {
                        return $order->client->company;
                    }
                })

                ->filterColumn('company', function ($query, $keyword) {

                    $query->where(
                        function ($query) use ($keyword) {
                            $query->whereRaw("order_address.company like ?", ["%{$keyword}%"]);
                            ;
                        }
                    );
                })

                ->filterColumn('client', function ($query, $keyword) {

                    $query->where(
                        function ($query) use ($keyword) {
                            $query->whereRaw("order_address.firstname like ?", ["%{$keyword}%"]);
                            $query->orWhereRaw("order_address.lastname like ?", ["%{$keyword}%"]);
                            ;
                        }
                    );
                })
                ->addColumn('client', function ($order) {
                    if ($order->client) {
                        if ($order->orderBillAddress) {
                            return '<a href="/admin/client/'.$order->client_id.'/order">'.$order->orderBillAddress->firstname.' '.$order->orderBillAddress->lastname.' ('.$order->client->orders->count() .')</a>';
                        }
                    }
                })
                ->addColumn('products', function ($order) {
                    if ($order->products) {
                        return $order->products->count();
                    }
                })
                ->addColumn('price_with_tax', function ($order) {
                    $money = '&euro; '.$order->getPriceWithTaxNumberFormat();
                    return $money;
                })


                ->addColumn('paymentMethod', function ($order) {
                    if ($order->orderPaymentMethod) {
                        return $order->orderPaymentMethod->title;
                    }
                })
                ->addColumn('sendingMethod', function ($order) {
                    if ($order->orderSendingMethod) {
                        return $order->orderSendingMethod->title;
                    }
                })
                ->addColumn('action', function ($order) {
                    $deleteLink = \Form::deleteajax('/admin/order/'. $order->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                    $download = '<a href="/admin/order/'.$order->id.'/download" class="btn btn-default btn-sm btn-info"><i class="entypo-pencil"></i>Download</a>  ';
                    $label = "";
                    if($order->orderLabel()->count()) {
                        $label = '<a href="/admin/order/'.$order->id.'/download-label" class="btn btn-default btn-sm btn-info"><i class="entypo-pencil"></i>Label</a>  ';
                    }      
             
                    $links = '<a href="/admin/order/'.$order->id.'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Show</a>  '.$download.' '.$label;
                




                    return $links;
                });

                return $datatables->make(true);


            } else {
                return view('hideyo_backend::order.index-wholesale')->with(array('revenueThisMonth' => $revenueThisMonth, 'order' => $this->order->selectAll()));
            }
        } else {

            if (Request::wantsJson()) {

                $order = $this->order->getModel()
                    ->from(config()->get('hideyo.db_prefix').'order as order')
                    ->select(
                    [

                    
                    'order.id',
                    'order.created_at',
                    'order.generated_custom_order_id',
                    'order.order_status_id',
                    'order.client_id',
                    'order.delivery_order_address_id',
                    'order.bill_order_address_id',
                    'order.price_with_tax']
                )->with(array('orderStatus', 'orderPaymentMethod', 'orderSendingMethod', 'products', 'client', 'orderBillAddress', 'orderDeliveryAddress'))->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)



                ->leftJoin(config()->get('hideyo.db_prefix').'order_address', 'order.bill_order_address_id', '=', config()->get('hideyo.db_prefix').'order_address.id');
                
                
                $datatables = \Datatables::of($order)

                ->addColumn('generated_custom_order_id', function ($order) {
                    return $order->generated_custom_order_id;
                })

                ->addColumn('created_at', function ($order) {
                    return date('d F H:i', strtotime($order->created_at));
                })

                ->addColumn('status', function ($order) {
                    if ($order->orderStatus) {
                        if ($order->orderStatus->color) {
                            return '<a href="/admin/order/'.$order->id.'" style="text-decoration:none;"><span style="background-color:'.$order->orderStatus->color.'; padding: 10px; line-height:30px; text-align:center; color:white;">'.$order->orderStatus->title.'</span></a>';
                        }
                        return $order->orderStatus->title;
                    }
                })

                ->filterColumn('client', function ($query, $keyword) {

                    $query->where(
                        function ($query) use ($keyword) {
                            $query->whereRaw("order_address.firstname like ?", ["%{$keyword}%"]);
                            $query->orWhereRaw("order_address.lastname like ?", ["%{$keyword}%"]);
                            ;
                        }
                    );
                })



                ->addColumn('client', function ($order) {
                    if ($order->client) {
                        if ($order->orderBillAddress) {
                            return '<a href="/admin/client/'.$order->client_id.'/order">'.$order->orderBillAddress->firstname.' '.$order->orderBillAddress->lastname.' ('.$order->client->orders->count() .')</a>';
                        }
                    }
                })
                ->addColumn('products', function ($order) {
                    if ($order->products) {
                        return $order->products->count();
                    }
                })
                ->addColumn('price_with_tax', function ($order) {
                    $money = '&euro; '.$order->getPriceWithTaxNumberFormat();
                    return $money;
                })


                ->addColumn('paymentMethod', function ($order) {
                    if ($order->orderPaymentMethod) {
                        return $order->orderPaymentMethod->title;
                    }
                })
                ->addColumn('sendingMethod', function ($order) {
                    if ($order->orderSendingMethod) {
                        return $order->orderSendingMethod->title;
                    }
                })
                ->addColumn('action', function ($order) {
                    $deleteLink = \Form::deleteajax('/admin/order/'. $order->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                    $download = '<a href="/admin/order/'.$order->id.'/download" class="btn btn-default btn-sm btn-info"><i class="entypo-pencil"></i>Download</a>  ';
                
                    $label = "";
                    if($order->orderLabel()->count()) {
                        $label = '<a href="/admin/order/'.$order->id.'/download-label" class="btn btn-default btn-sm btn-info"><i class="entypo-pencil"></i>Label</a>  ';
                    }      
             
                    
                    $links = '<a href="/admin/order/'.$order->id.'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Show</a>  '.$download.' '.$label;
                
                    return $links;
                });

                return $datatables->make(true);


            } else {
                return view('hideyo_backend::order.index')->with(array('revenueThisMonth' => $revenueThisMonth, 'order' => $this->order->selectAll()));
            }
        }
    }

    public function getPrintOrders()
    {

        $orders = Request::session()->get('print_orders');

        return View::make('admin.order.print-orders')->with(array('orders' => $orders));
    }

    public function getPrint()
    {
        return view('hideyo_backend::order.print')->with(array('orderStatuses' => $this->orderStatus->selectAll()->pluck('title', 'id')));
    }
    
    public function postDownloadPrint()
    {
        $data = Request::all();

        if ($data and $data['order']) {

            if($data['type'] == 'one-pdf') {
                $pdfHtml = "";
                $countOrders = count($data['order']);
                $i = 0;
                foreach ($data['order'] as $key => $val) {
                    $i++;

                    $order = $this->order->find($val);
                    $text = $this->sendingPaymentMethodRelated->selectOneByPaymentMethodIdAndSendingMethodIdAdmin($order->orderSendingMethod->sending_method_id, $order->orderPaymentMethod->payment_method_id);
                    
                    $pdfText = "";
                    if ($text) {
                        $pdfText = $this->replaceTags($text->pdf_text, $order);
                    }

                    if ($order->shop->wholesale) {
                        $pdfHtml .= View::make('admin.order.bodypdf-wholesale', array('order' => $order, 'pdfText' => $pdfText))->render();
                    } else {
                        $pdfHtml .= View::make('admin.order.bodypdf', array('order' => $order, 'pdfText' => $pdfText))->render();
                    }


                    if ($i != $countOrders) {
                        $pdfHtml .= '<div style="page-break-before: always;"></div>';
                    }
                }


                    $pdfHtmlBody = View::make('admin.order.multiplepdfbody', array('body' => $pdfHtml))->render();



                $pdf = \PDF::loadHTML($pdfHtmlBody);

                return $pdf->download('order-'.$order->generated_custom_order_id.'.pdf');
            } elseif($data['type'] == 'product-list') {
                $products = $this->order->productsByOrderIds($data['order']);

                if($products) {


                    Excel::create('products', function ($excel) use ($products) {

                        $excel->sheet('Products', function ($sheet) use ($products) {
                            $newArray = array();
                            foreach ($products as $key => $row) {
                    
  
                                $newArray[$row->title] = array(
                                    'total' => $row->total_amount,
                                    'title' => $row->title,
                                    'reference_code' => $row->reference_code,
                                    'price_with_tax' => $row->price_with_tax,
                                    'price_without_tax' => $row->price_without_tax
                                    
             
                                );


                 
                            }

                                ksort($newArray);
                            $sheet->fromArray($newArray);
                        });
                    })->download('xls');

                }
            }
        }
    }

    public function postPrint()
    {

        $data = Request::all();

            $orders = $this->order->selectAllByShopIdAndStatusId($data['order_status_id'], $data['start_date'], $data['end_date']);

        if ($orders) {
            Request::session()->put('print_orders', $orders->toArray());
            return Response::json(array('orders' => $orders->toArray() ));
        } else {
            Request::session()->destroy('print_orders');
            return Response::json(false);
        }
    }


    public function show($id)
    {
        $order = $this->order->find($id);

        return view('hideyo_backend::order.show')->with(array('order' => $order, 'orderStatuses' => $this->orderStatus->selectAll()->pluck('title', 'id')));
    }

    public function updateStatus($orderId)
    {
        $orderStatusId = Request::get('order_status_id');
        if ($orderStatusId) {
            $result = $this->order->updateStatus($orderId, $orderStatusId);
            Event::fire(new OrderChangeStatus($result));
            \Notification::success('The status was updated to '.$result->OrderStatus->title);
        }
        return Redirect::route('admin.order.show', $orderId);
    }

    public function download($id)
    {
        $order = $this->order->find($id);
        $text = $this->sendingPaymentMethodRelated->selectOneByPaymentMethodIdAndSendingMethodIdAdmin($order->orderSendingMethod->sending_method_id, $order->orderPaymentMethod->payment_method_id);
        
        $pdfText = "";
        if ($text) {
            $pdfText = $this->replaceTags($text->pdf_text, $order);
        }
        $pdf = \PDF::loadview('hideyo_backend::order.pdf', array('order' => $order, 'pdfText' => $pdfText));

        return $pdf->download('order-'.$order->generated_custom_order_id.'.pdf');
    }


    public function downloadLabel($id)
    {
        $order = $this->order->find($id);
        if($order->orderLabel()->count()) {

  header("Content-type: application/octet-stream");
  header("Content-disposition: attachment;filename=label.pdf");

  echo $order->orderLabel->data;

  
        }

    }


    public function replaceTags($content, $order)
    {

        $replace = array(
            'orderId' => $order->generated_custom_order_id,
            'orderCreated' => $order->created_at,
            'orderTotalPriceWithTax' => $order->getPriceWithTaxNumberFormat(),
            'orderTotalPriceWithoutTax' => $order->getPriceWithoutTaxNumberFormat(),
            'clientEmail' => $order->client->email,
            'clientFirstname' => $order->orderBillAddress->firstname,
            'clientLastname' => $order->orderBillAddress->lastname,
            'clientCompany' => $order->orderBillAddress->company,
            'clientDeliveryFirstname' => $order->orderDeliveryAddress->firstname,
            'clientDeliveryLastname' => $order->orderDeliveryAddress->lastname,
            'clientDeliveryStreet' => $order->orderDeliveryAddress->street,
            'clientDeliveryHousenumber' => $order->orderDeliveryAddress->housenumber,
            'clientDeliveryHousenumberSuffix' => $order->orderDeliveryAddress->housenumber_suffix,
            'clientDeliveryZipcode' => $order->orderDeliveryAddress->zipcode,
            'clientDeliveryCity' => $order->orderDeliveryAddress->city,
            'clientDeliveryCountry' => $order->orderDeliveryAddress->country,
            'clientDeliveryCompany' => $order->orderDeliveryAddress->company,


        );
        foreach ($replace as $key => $val) {
            $content = str_replace("[" . $key . "]", $val, $content);
        }

        return $content;
    }

    public function create()
    {
        $ajaxProducts = $this->product->selectAllWithCombinations();
    
        $clientAdresses = $this->client->selectAllByBillClientAddress();
        $ajaxClientAdresses = array();
        foreach ($clientAdresses as $row) {
            $ajaxClientAdresses[$row->client_id] = $row->firstname.' '.$row->lastname;
            if ($row->company) {
                $ajaxClientAdresses[$row->client_id] = $ajaxClientAdresses[$row->client_id].' ('.$row->company.')';
            }
        }

        $products = $this->assembleOrder->products();

        $sendingMethodsList = $this->sendingMethod->selectAll();
        $paymentMethodsList = $this->paymentMethod->selectAll();

        $summary = $this->assembleOrder->summary();

        $products = "";
        $totals = "";
        $clientAddresses = array();
        if ($summary) {
            $totals = $summary->totals();

            if ($totals['client_id']) {
                $client = $this->client->find($totals['client_id']);
                foreach ($client->clientAddress as $row) {
                    $clientAddresses[$row->id] = $row;
                }
            }

            $products = $summary->products();
            $paymentMethodsList = $summary->paymentMethods();
        }

        return view('hideyo_backend::order.create')->with(
            array(
                'orderAssembleSession' => $this->assembleOrder,
                'products' => $products,
                'ajaxProducts' => $ajaxProducts,
                'ajaxClientAdresses' => $ajaxClientAdresses,
                'sendingMethodsList' => $sendingMethodsList,
                'paymentMethodsList' => $paymentMethodsList,
                'totals' => $totals,
                'clientAddresses' => $clientAddresses,
                'orderStatuses' => $this->orderStatus->selectAll()->pluck('title', 'id')
                )
        );
    }


    public function deleteProduct($productId)
    {
        $explode = explode('-', $productId);

        $product = $this->product->selectOneById($explode[0]);

        if (isset($explode[1])) {
            $productAttributeId = $explode[1];
            $productCombination = ProductAttribute::where('id', '=', $productAttributeId)->first();

            if ($productCombination) {
                $result = $this->assembleOrder->removeProductAttribute($product, $productCombination->id);
            }
        } elseif ($product->id) {
            $result = $this->assembleOrder->remove($product);
        }

        return Redirect::back();
    }

    public function changeProductCombination($productId, $newProductId)
    {


        $explode = explode('-', $productId);

        $product = $this->product->selectOneById($explode[0]);
        $productCombination = false;
        if (isset($explode[1])) {
            $productAttributeId = $explode[1];
            $productCombination = ProductAttribute::where('id', '=', $productAttributeId)->first();
        }

        if ($product->attributes()->count()) {
            $attributes = $product->attributes()->with(array('combinations' => function ($query) {
                $query->with(array('attribute'));
            }))->get();
            $attributesArray = array();
            foreach ($attributes as $row) {
                foreach ($row->combinations as $combination) {
                    $attributesArray[$row->product_id.'-'.$row->id][] = $combination->attribute->value;
                }

                $attributesArray[$row->product_id.'-'.$row->id] = implode(',', $attributesArray[$row->product_id.'-'.$row->id]);
            }
        }


        if ($product->id) {
            $productArray = $product->toArray();
                    
            $productArray['price_details'] = $product->getPriceDetails();
            if (isset($attributesArray)) {
                $productArray['combinations'] = $attributesArray;
            }
            
            if ($productCombination) {
                $productArray['id'] = $productArray['id'].'-'.$productCombination->id;
                $productArray['product_id'] = $product->id;
                if ($productCombination->price) {
                    $productArray['price_details'] = $productCombination->getPriceDetails();
                }
                $productArray['product_combination_title'] = array();
                foreach ($productCombination->combinations as $combination) {
                    $productArray['product_combination_title'][$combination->attribute->attributeGroup->title] = $combination->attribute->value;
                }
            }
        }



        $explode = explode('-', $newProductId);

        $newProduct = $this->product->selectOneById($explode[0]);
        $newProductCombination = false;
        if (isset($explode[1])) {
            $productAttributeId = $explode[1];
            $newProductCombination = ProductAttribute::where('id', '=', $productAttributeId)->first();
        }

        if ($newProduct->attributes()->count()) {
            $attributes = $newProduct->attributes()->with(array('combinations' => function ($query) {
                $query->with(array('attribute'));
            }))->get();
            $attributesArray = array();
            foreach ($attributes as $row) {
                foreach ($row->combinations as $combination) {
                    $attributesArray[$row->product_id.'-'.$row->id][] = $combination->attribute->value;
                }

                $attributesArray[$row->product_id.'-'.$row->id] = implode(',', $attributesArray[$row->product_id.'-'.$row->id]);
            }
        }


        if ($newProduct->id) {
            $newProductArray = $newProduct->toArray();
                    
            $newProductArray['price_details'] = $newProduct->getPriceDetails();
            if (isset($attributesArray)) {
                $newProductArray['combinations'] = $attributesArray;
            }
            
            if ($newProductCombination) {
                $newProductArray['id'] = $newProductArray['id'].'-'.$newProductCombination->id;
                $newProductArray['product_id'] = $newProduct->id;
                if ($newProductCombination->price) {
                    $newProductArray['price_details'] = $newProductCombination->getPriceDetails();
                }
                $newProductArray['product_combination_title'] = array();
                foreach ($newProductCombination->combinations as $combination) {
                    $newProductArray['product_combination_title'][$combination->attribute->attributeGroup->title] = $combination->attribute->value;
                }
            }
        }


        $this->assembleOrder->changeProductCombination($productArray, $newProductArray);

        $summary = $this->assembleOrder->summary();
        if ($summary) {
                 return \Response::json(array('oldproductid' => $productId, 'product' => $summary->getProduct($newProductArray['id']), 'totals' => $summary->totals()));
        } else {
               return \Response::json(array('product' =>false, 'totals' => false));
        }
    }



    public function updateAmountProduct($productId, $amount)
    {

        $explode = explode('-', $productId);

        $product = $this->product->selectOneById($explode[0]);
        $productCombination = false;
        if (isset($explode[1])) {
            $productAttributeId = $explode[1];
            $productCombination = ProductAttribute::where('id', '=', $productAttributeId)->first();
        }

        if ($product->attributes()->count()) {
            $attributes = $product->attributes()->with(array('combinations' => function ($query) {
                $query->with(array('attribute'));
            }))->get();
            $attributesArray = array();
            foreach ($attributes as $row) {
                foreach ($row->combinations as $combination) {
                    $attributesArray[$row->product_id.'-'.$row->id][] = $combination->attribute->value;
                }

                $attributesArray[$row->product_id.'-'.$row->id] = implode(',', $attributesArray[$row->product_id.'-'.$row->id]);
            }
        }


        if ($product->id) {
            $productArray = $product->toArray();
                    
            $productArray['price_details'] = $product->getPriceDetails();
            if (isset($attributesArray)) {
                $productArray['combinations'] = $attributesArray;
            }
            
            if ($productCombination) {
                $productArray['id'] = $productArray['id'].'-'.$productCombination->id;
                $productArray['product_id'] = $product->id;
                if ($productCombination->price) {
                    $productArray['price_details'] = $productCombination->getPriceDetails();
                }
                $productArray['product_combination_title'] = array();
                foreach ($productCombination->combinations as $combination) {
                    $productArray['product_combination_title'][$combination->attribute->attributeGroup->title] = $combination->attribute->value;
                }
            }

            $this->assembleOrder->updateAmount($productArray, $amount);
        }

        $summary = $this->assembleOrder->summary();
        if ($summary) {
                 return \Response::json(array('product' => $summary->getProduct($productId), 'totals' => $summary->totals()));
        } else {
               return \Response::json(array('product' =>false, 'totals' => false));
        }
    }

    public function updateSendingMethod($sendingMethodId)
    {
        $sendingMethod = $this->sendingMethod->selectOneById($sendingMethodId);
        $sendingMethodArray = array();
        if (isset($sendingMethod->id)) {
            $sendingMethodArray = $sendingMethod->toArray();
            $sendingMethodArray['price_details'] = $sendingMethod->getPriceDetails();
            $sendingMethodArray['related_payment_methods_list'] = $sendingMethod->relatedPaymentMethods->pluck('title', 'id');
        }

        $this->assembleOrder->updateSendingMethod($sendingMethodArray);
        $summary = $this->assembleOrder->summary();

        return \Response::json(array('sending_method' => $summary->sendingMethod(), 'totals' => $summary->totals()));
    }

    public function updatePaymentMethod($paymentMethodId)
    {
        $paymentMethod = $this->paymentMethod->selectOneById($paymentMethodId);

        $paymentMethodArray = array();
        if (isset($paymentMethod->id)) {
            $paymentMethodArray = $paymentMethod->toArray();
            $paymentMethodArray['price_details'] = $paymentMethod->getPriceDetails();
        }

        $this->assembleOrder->updatePaymentMethod($paymentMethodArray);
        $summary = $this->assembleOrder->summary();

        return \Response::json(array('payment_method' => $summary->paymentMethod(), 'totals' => $summary->totals()));
    }

    public function addClient()
    {
        $input = Request::get('add-client');
        $this->assembleOrder->addClient($input);
        return Redirect::back();
    }

    public function updateClientBillAddress($addressId)
    {
        $this->assembleOrder->addClientBillAddress($addressId);
        $summary = $this->assembleOrder->summary();
        return \Response::json(array('totals' => $summary->totals()));
    }

    public function updateClientDeliveryAddress($addressId)
    {
        $this->assembleOrder->addClientDeliveryAddress($addressId);
        $summary = $this->assembleOrder->summary();
        return \Response::json(array('totals' => $summary->totals()));
    }

    public function addProduct()
    {
        $products = Request::all();

        if (isset($products['add-products'])) {
            foreach ($products['add-products'] as $key => $value) {
                $explode = explode('-', $value);

                $product = $this->product->selectOneById($explode[0]);

                $productCombination = false;
                if ($product->attributes()->count()) {
                    $attributes = $product->attributes()->with(array('combinations' => function ($query) {
                        $query->with(array('attribute'));
                    }))->get();
                    $attributesArray = array();
                    foreach ($attributes as $row) {
                        foreach ($row->combinations as $combination) {
                            $attributesArray[$row->product_id.'-'.$row->id][] = $combination->attribute->value;
                        }

                        $attributesArray[$row->product_id.'-'.$row->id] = implode(',', $attributesArray[$row->product_id.'-'.$row->id]);
                    }

                    if ($explode[1]) {
                        $productCombination = ProductAttribute::where('id', '=', $explode[1])->first();
                    }
                }

                if ($product->id) {
                    $productArray = $product->toArray();
                    $productArray['price_details'] = $product->getPriceDetails();
                    
                    if (isset($attributesArray)) {
                        $productArray['combinations'] = $attributesArray;
                    }
                    
                    if ($productCombination) {
                        $productArray['product_combination_title'] = array();
                        foreach ($productCombination->combinations as $combination) {
                            $productArray['product_combination_title'][$combination->attribute->attributeGroup->title] = $combination->attribute->value;
                        }
            
                        $productArray['product_combination_id'] = $productCombination->id;
                        if ($productCombination->price) {
                            $productArray['price_details'] = $productCombination->getPriceDetails();
                        }

                        if ($productCombination->reference_code) {
                            $productArray['reference_code'] = $productCombination->reference_code;
                        }
                    }

                    $result = $this->assembleOrder->add($productArray, 1);
                    $summary = $this->assembleOrder->summary();
                    $result['summary'] = $summary->totals();
                }
            }
        }

        return Redirect::back();
    }
    

    public function store()
    {
        $summary = $this->assembleOrder->summary();
        if (!$summary) {
            return Redirect::back()->withInput()->withErrors(array('no products'));
        }

        $totals = $summary->totals();

        if (empty($totals['client_bill_address_id']) or empty($totals['client_delivery_address_id'])) {
            return Redirect::back()->withInput()->withErrors(array('select delivery and bill address'));
        }
 
        if (empty($totals['sending_method']) or empty($totals['payment_method'])) {
            return Redirect::back()->withInput()->withErrors(array('select send & payment method'));
        }

        $attributes = Request::all();
        if (empty($attributes['order_status_id'])) {
            return Redirect::back()->withInput()->withErrors(array('select order status id'));
        }


        $data = array(
            'client_id' => $totals['client_id'],
            'client_bill_address_id' => $totals['client_bill_address_id'],
            'client_delivery_address_id' => $totals['client_delivery_address_id'],
            'price_with_tax' => $totals['total_inc_tax'],
            'price_without_tax' => $totals['total_ex_tax'],
            'sending_method' => $totals['sending_method_id'],
            'payment_method' => $totals['payment_method_id'],
            'products' => $summary->products(),
            'order_status_id' => $attributes['order_status_id']
        );

        $orderInsertAttempt = $this->order->createByAdmin($data);

        $this->assembleOrder->destroyInstance();

        return Redirect::route('admin.order.index');
    }

    public function edit($id)
    {
        return view('hideyo_backend::order.edit')->with(array('order' => $this->order->find($id)));
    }

    public function update($id)
    {
        $result  = $this->order->updateById(Request::all(), $id);

        if ($result->errors()->all()) {
            return Redirect::back()->withInput()->withErrors($result->errors()->all());
        } else {
            Log::info('Tax '.$result->name.' updated');
            //Notification::success('The order was updated.');
            return Redirect::route('admin.order.index');
        }
    }
}
