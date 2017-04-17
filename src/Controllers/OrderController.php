<?php namespace Hideyo\Ecommerce\Backend\Controllers;

/**
 * OrderController
 *
 * This is the controller of the product weight types of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Ecommerce\Backend\Repositories\OrderRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\ProductRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\SendingMethodRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\SendingPaymentMethodRelatedRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\PaymentMethodRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\ClientAddressRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\ClientRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\OrderStatusRepositoryInterface;
use Dutchbridge\Services\AssembleOrder;

use Carbon\Carbon;
use Request;
use Notification;
use App\ProductAttribute;
use App\Events\OrderChangeStatus;
use Event;
use Response;
use Excel;
use Auth;
use PDF;

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
        $shop  = Auth::guard('hideyobackend')->user()->shop;
        $now = Carbon::now();

        $revenueThisMonth = null;

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
            )->with(array('orderStatus', 'orderPaymentMethod', 'orderSendingMethod', 'products', 'client', 'orderBillAddress', 'orderDeliveryAddress'))->where('shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id)



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
        }
        
        return view('hideyo_backend::order.index')->with(array('revenueThisMonth' => $revenueThisMonth, 'order' => $this->order->selectAll())); 
    }

    public function getPrintOrders()
    {
        $orders = Request::session()->get('print_orders');
        return view('admin.order.print-orders')->with(array('orders' => $orders));
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
                    
                    $pdfHtml .= view('admin.order.bodypdf', array('order' => $order, 'pdfText' => $pdfText))->render();
                   
                    if ($order->shop->wholesale) {
                        $pdfHtml .= view('admin.order.bodypdf-wholesale', array('order' => $order, 'pdfText' => $pdfText))->render();
                    }

                    if ($i != $countOrders) {
                        $pdfHtml .= '<div style="page-break-before: always;"></div>';
                    }
                }

                $pdfHtmlBody = view('admin.order.multiplepdfbody', array('body' => $pdfHtml))->render();
                $pdf = PDF::loadHTML($pdfHtmlBody);

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
        }

        Request::session()->destroy('print_orders');
        return Response::json(false);
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

        return redirect()->route('admin.order.show', $orderId);
    }

    public function download($id)
    {
        $order = $this->order->find($id);
        $text = $this->sendingPaymentMethodRelated->selectOneByPaymentMethodIdAndSendingMethodIdAdmin($order->orderSendingMethod->sending_method_id, $order->orderPaymentMethod->payment_method_id);
        
        $pdfText = "";
        if ($text) {
            $pdfText = $this->replaceTags($text->pdf_text, $order);
        }
        $pdf = PDF::loadview('hideyo_backend::order.pdf', array('order' => $order, 'pdfText' => $pdfText));

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

    public function edit($id)
    {
        return view('hideyo_backend::order.edit')->with(array('order' => $this->order->find($id)));
    }

    public function update($id)
    {
        $result  = $this->order->updateById(Request::all(), $id);

        if ($result->errors()->all()) {
            return redirect()->back()->withInput()->withErrors($result->errors()->all());
        } else {
            Notification::success('The order was updated.');
            return redirect()->route('admin.order.index');
        }
    }
}
