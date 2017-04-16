<?php namespace Hideyo\Backend\Controllers;

/**
 * SendingPaymentMethodRelatedController
 *
 * This is the controller of the sending payment method related of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */
use App\Http\Controllers\Controller;
use Hideyo\Backend\Repositories\SendingPaymentMethodRelatedRepositoryInterface;
use Hideyo\Backend\Repositories\TaxRateRepositoryInterface;
use Hideyo\Backend\Repositories\PaymentMethodRepositoryInterface;
use DB;
use Request;
use Datatables;
use Notification;

class SendingPaymentMethodRelatedController extends Controller
{
    public function __construct(SendingPaymentMethodRelatedRepositoryInterface $sendingPaymentMethodRelated)
    {
        $this->sendingPaymentMethodRelated = $sendingPaymentMethodRelated;
    }

    public function index()
    {
        if (Request::wantsJson()) {

            $query = DB::table(config()->get('hideyo.db_prefix').'sending_payment_method_related')->join(config()->get('hideyo.db_prefix').'sending_method', config()->get('hideyo.db_prefix').'sending_payment_method_related.sending_method_id', '=', config()->get('hideyo.db_prefix').'sending_method.id')->join(config()->get('hideyo.db_prefix').'payment_method', config()->get('hideyo.db_prefix').'sending_payment_method_related.payment_method_id', '=', config()->get('hideyo.db_prefix').'payment_method.id')
                ->select([config()->get('hideyo.db_prefix').'payment_method.title as payment_method_title', 
                    config()->get('hideyo.db_prefix').'sending_method.title as sending_method_title', 
                    config()->get('hideyo.db_prefix').'sending_payment_method_related.*'])
               ->where(config()->get('hideyo.db_prefix').'sending_method.shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id);
            $datatables = Datatables::of($query)

            ->addColumn('payment_method', function ($query) {
                  return $query->payment_method_title;
            })
            ->addColumn('sending_method', function ($query) {
                  return $query->sending_method_title;
            })

            ->addColumn('pdf_text', function ($query) {
                
                $result = '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>';

                if ($query->pdf_text) {
                    $result = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
                }

                return $result;
            })
            ->addColumn('payment_text', function ($query) {
                
                $result = '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>';

                if ($query->payment_text) {
                    $result = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
                }

                return $result;
            })
            ->addColumn('payment_confirmed_text', function ($query) {
                
                $result = '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>';

                if ($query->payment_confirmed_text) {
                    $result = '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>';
                }

                return $result;
            })
            



            ->addColumn('action', function ($query) {
                $links = '<a href="'.url()->route('hideyo.sending-payment-method-related.edit', $query->id).'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>';
            
                return $links;
            });

            return $datatables->make(true);


        } else {
            return view('hideyo_backend::sending_payment_method_related.index');
        }
    }

    public function edit($sendingPaymentRelatedId)
    {
        return view('hideyo_backend::sending_payment_method_related.edit')->with(array(
            'sendingPaymentMethodRelated' => $this->sendingPaymentMethodRelated->find($sendingPaymentRelatedId)
            ));
    }

    public function update($sendingPaymentRelatedId)
    {
        $result  = $this->sendingPaymentMethodRelated->updateById(Request::all(), $sendingPaymentRelatedId);

        if (isset($result->id)) {
            Notification::success('The order template was updated.');
            return redirect()->route('hideyo.sending-payment-method-related.index');
        }
        
        Notification::error($result->errors()->all());
        return redirect()->back()->withInput();
    }
}
