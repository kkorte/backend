<?php namespace Hideyo\Backend\Controllers;

use App\Http\Controllers\Controller;

/**
 * ClientAddressController
 *
 * This is the controller for the shop clients orders
 * @author Matthijs Neijenhuijs <matthijs@Dutchbridge.nl>
 * @version 1.0
 */

use Hideyo\Backend\Repositories\ClientAddressRepositoryInterface;
use Hideyo\Backend\Repositories\ClientRepositoryInterface;
use Hideyo\Backend\Repositories\OrderRepositoryInterface;

use Illuminate\Http\Request;

class ClientOrderController extends Controller
{
    public function __construct(Request $request, ClientAddressRepositoryInterface $clientAddress, ClientRepositoryInterface $client, OrderRepositoryInterface $order)
    {
        $this->clientAddress = $clientAddress;
        $this->client = $client;
        $this->order = $order;
        $this->request = $request;
    }

    public function index($clientId)
    {
        $client = $this->client->find($clientId);
        if ($this->request->wantsJson()) {

            $order = $this->order->getModel()->select(
                [
                
                'id', 'created_at', 'generated_custom_order_id', 'order_status_id', 'client_id', 'delivery_order_address_id', 'bill_order_address_id',
                'price_with_tax']
            )->with(array('orderStatus', 'orderPaymentMethod', 'orderSendingMethod', 'products', 'client', 'orderBillAddress', 'orderDeliveryAddress'))->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->where('client_id', '=', $clientId);
            
            
            $datatables = \Datatables::of($order)

            ->addColumn('status', function ($order) {
                if ($order->orderStatus) {
                     return $order->orderStatus->title;
                }
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

            ->addColumn('client', function ($order) {
                if ($order->client) {
                    if ($order->orderBillAddress) {
                        return $order->orderBillAddress->firstname.' '.$order->orderBillAddress->lastname;
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
                $delete = \Form::deleteajax('/admin/order/'. $order->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $download = '<a href="/admin/order/'.$order->id.'/download" class="btn btn-default btn-sm btn-info"><i class="entypo-pencil"></i>Download</a>  ';
                $link = '<a href="/admin/order/'.$order->id.'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Show</a>  '.$download;
            
                return $link;
            });

            return $datatables->make(true);


            $order = Order::select(
                [
                
                'id', 'order_status_id',
                'price_with_tax']
            )->with(array('orderStatus'))->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->where('client_id', '=', $clientId);
            
            
            $datatables = \Datatables::of($order)

            ->addColumn('status', function ($order) {
                if ($order->orderStatus) {
                     return $order->orderStatus->title;
                }
            })
            ->addColumn('email', function ($order) {
                if ($order->client) {
                     return $order->client->email;
                }
            })

            ->addColumn('action', function ($order) {
                $delete = \Form::deleteajax('/admin/order/'. $order->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $download = '<a href="/admin/order/'.$order->id.'/download" class="btn btn-default btn-sm btn-info"><i class="entypo-pencil"></i>Download</a>  ';
                $link = '<a href="/admin/order/'.$order->id.'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Show</a>  '.$download.$delete;
            
                return $link;
            });

            return $datatables->make(true);


        } else {
            return view('hideyo_backend::client_order.index')->with(array('client' => $client));
        }
    }   
}
