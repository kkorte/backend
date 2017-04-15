<?php namespace Hideyo\Backend\Controllers;

/**
 * OrderStatusEmailTemplateController
 *
 * This is the controller of the content weight types of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Backend\Repositories\OrderStatusEmailTemplateRepositoryInterface;
use Hideyo\Backend\Repositories\SendingPaymentMethodRelatedRepositoryInterface;

use Illuminate\Http\Request;
use Notification;

class OrderStatusEmailTemplateController extends Controller
{
    public function __construct(
        Request $request,
        OrderStatusEmailTemplateRepositoryInterface $orderHtmlTemplate,
        SendingPaymentMethodRelatedRepositoryInterface $sendingPaymentMethodRelatedInterface
    ) {
        $this->request = $request;
        $this->orderHtmlTemplate = $orderHtmlTemplate;
        $this->sendingPaymentMethodRelatedInterface = $sendingPaymentMethodRelatedInterface;
    }

    public function index()
    {
        if ($this->request->wantsJson()) {

            $query = $this->orderHtmlTemplate->getModel()->select(
                ['id', 'title', 'subject']
            )->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id);
            
            $datatables = \Datatables::of($query)
            ->addColumn('action', function ($query) {
                $delete = \Form::deleteajax('/admin/order-status-email-template/'. $query->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $link = '<a href="/admin/order-status-email-template/'.$query->id.'/edit" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  '.$delete;
            
                return $link;
            });

            return $datatables->make(true);


        } else {
            return view('hideyo_backend::order-status-email-template.index')->with(array('orderHtmlTemplate' =>  $this->orderHtmlTemplate->selectAll()));
        }
    }

    public function create()
    {
        return view('hideyo_backend::order-status-email-template.create')->with(array());
    }

    public function store()
    {
        $result  = $this->orderHtmlTemplate->create($this->request->all());

        if (isset($result->id)) {
            Notification::success('The template was inserted.');
            return redirect()->route('hideyo.order-status-email-template.index');
        }
            
        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }

        return redirect()->back()->withInput();
    }

    public function edit($id)
    {
        return view('hideyo_backend::order-status-email-template.edit')->with(array('orderHtmlTemplate' => $this->orderHtmlTemplate->find($id)));
    }

    public function showAjaxTemplate($id)
    {
        return response()->json($this->orderHtmlTemplate->find($id));
    }

    public function update($id)
    {
        $result  = $this->orderHtmlTemplate->updateById($this->request->all(), $id);

        if (isset($result->id)) {
            Notification::success('template was updated.');
            return redirect()->route('hideyo.order-status-email-template.index');
        }

        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        
        return redirect()->back()->withInput()->withErrors($result->errors()->all());
    }

    public function destroy($id)
    {
        $result  = $this->orderHtmlTemplate->destroy($id);

        if ($result) {
            Notification::success('template was deleted.');
            return redirect()->route('hideyo.order-status-email-template.index');
        }
    }
}
