<?php namespace Hideyo\Ecommerce\Backend\Controllers;

/**
 * InvoiceController
 *
 * This is the controller of the invoices of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Ecommerce\Backend\Repositories\InvoiceRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\TaxRateRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\PaymentMethodRepositoryInterface;

use \Request;
use \Notification;
use \Redirect;

class InvoiceController extends Controller
{
    public function __construct(
        InvoiceRepositoryInterface $invoice,
        TaxRateRepositoryInterface $taxRate,
        PaymentMethodRepositoryInterface $paymentMethod
    ) {
        $this->taxRate = $taxRate;
        $this->invoice = $invoice;
        $this->paymentMethod = $paymentMethod;
    }

    public function index()
    {

        if (Request::wantsJson()) {

            $invoice = $this->invoice->getModel->select(
                [
                
                'id', 'generated_custom_invoice_id', 'order_id',
                'price_with_tax']
            )->with(array('Order'))->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id);
            
            
            $datatables = \Datatables::of($invoice)

            ->addColumn('price_with_tax', function ($order) {
                $money = '&euro; '.$order->price_with_tax;
                return $money;
            })



            ->addColumn('action', function ($invoice) {
                $deleteLink = \Form::deleteajax('/invoice/'. $invoice->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $download = '<a href="/invoice/'.$invoice->id.'/download" class="btn btn-default btn-sm btn-info"><i class="entypo-pencil"></i>Download</a>  ';
                $links = '<a href="/invoice/'.$invoice->id.'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Show</a>  '.$download;
            
                return $links;
            });

            return $datatables->make(true);


        } else {
            return view('hideyo_backend::invoice.index')->with('invoice', $this->invoice->selectAll());
        }
    }

    public function show($id)
    {
        return view('hideyo_backend::invoice.show')->with('invoice', $this->invoice->find($id));
    }

    public function download($id)
    {
        $invoice = $this->invoice->find($id);
        $pdf = \PDF::loadView('invoice.pdf', array('invoice' => $invoice));
        return $pdf->download('invoice-'.$invoice->generated_custom_invoice_id.'.pdf');
    }


    public function create()
    {
        return view('hideyo_backend::invoice.create')->with(array(
            'taxRates' => $this->taxRate->selectAll()->pluck('title', 'id'),
            'paymentMethods' => $this->paymentMethod->selectAll()->pluck('title', 'id')
        ));
    }

    public function store()
    {
        $result  = $this->invoice->create(Request::all());

        if (isset($result->id)) {
            \Notification::success('The invoice was inserted.');
            return \Redirect::route('sending-method.index');
        }
        
        \Notification::error($result->errors()->all());
        return \Redirect::back()->withInput();
    }

    public function edit($id)
    {
        return view('hideyo_backend::invoice.edit')->with(array(
            'taxRates' => $this->taxRate->selectAll()->pluck('title', 'id'),
            'invoice' => $this->invoice->find($id),
            'paymentMethods' => $this->paymentMethod->selectAll()->pluck('title', 'id'),
        ));
    }

    public function update($id)
    {
        $result  = $this->invoice->updateById(Request::all(), $id);

        if (isset($result->id)) {
            \Notification::success('The invoice was updated.');
            return \Redirect::route('sending-method.index');
        }
        
        \Notification::error($result->errors()->all());
        return \Redirect::back()->withInput();
    }

    public function destroy($id)
    {
        $result  = $this->invoice->destroy($id);

        if ($result) {
            Notification::success('The invoice was deleted.');
            return Redirect::route('sending-method.index');
        }
    }
}
