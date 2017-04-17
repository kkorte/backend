<?php namespace Hideyo\Ecommerce\Backend\Controllers;


use App\Http\Controllers\Controller;

/**
 * ClientController
 *
 * This is the controller for the shop clients
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */


use Hideyo\Ecommerce\Backend\Repositories\ClientRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\ClientAddressRepositoryInterface;

use Illuminate\Http\Request;
use Notification;
use Mail;
use Excel;
use Auth;
use Form;
use Datatables;

class ClientController extends Controller
{
    public function __construct(
        Request $request, ClientRepositoryInterface $client, 
        ClientAddressRepositoryInterface $clientAddress)
    {
        $this->client           = $client;
        $this->clientAddress    = $clientAddress;
        $this->request          = $request;
    }

    public function index()
    {
        $shop  = Auth::guard('hideyobackend')->user()->shop;

        if ($this->request->wantsJson()) {
            $shop  = Auth::guard('hideyobackend')->user()->shop();
            $clients = $this->client->getModel()->select(
                [
                
                'id', 'confirmed', 'active',
                'email', 'last_login']
            )->where('shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id);
            
            $datatables = \Datatables::of($clients)


            ->addColumn('last_login', function ($clients) {
                return date('d F H:i', strtotime($clients->last_login));
            })

            ->addColumn('action', function ($clients) {
                $deleteLink = \Form::deleteajax(url()->route('hideyo.client.destroy', $clients->id), 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $links = '<a href="'.url()->route('hideyo.client.edit', $clients->id).'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Show</a>  '.$deleteLink;
            
                if (!$clients->active || !$clients->confirmed) {
                    $links .= ' <a href="'.url()->route('hideyo.client.activate', $clients->id).'" class="btn btn-default btn-sm btn-info">activate</a>';
                } else {
                    $links .= ' <a href="'.url()->route('hideyo.client.de-activate', $clients->id).'" class="btn btn-default btn-sm btn-info">block</a>';
                }

                return $links;
            });

            return $datatables->make(true);


        }
        
        return view('hideyo_backend::client.index')->with('client', $this->client->selectAll());    
    }

    public function create()
    {
        return view('hideyo_backend::client.create')->with(array());
    }

    public function getActivate($clientId)
    {

        return view('hideyo_backend::client.activate')->with(array('client' => $this->client->find($clientId), 'addresses' => $this->clientAddress->selectAllByClientId($clientId)->pluck('firstname', 'id')));
    }

    public function getDeActivate($clientId)
    {

        return view('hideyo_backend::client.de-activate')->with(array('client' => $this->client->find($clientId), 'addresses' => $this->clientAddress->selectAllByClientId($clientId)->pluck('firstname', 'id')));
    }


    public function postActivate($clientId)
    {
        $input = $this->request->all();

        $result  = $this->client->activate($clientId);


        $shop  = Auth::guard('hideyobackend')->user()->shop;

        if ($shop->wholesale and $result) {
            if ($input['send_mail']) {
                    Mail::send('frontend.email.activate-mail-wholesale', array('user' => $result->toArray(), 'billAddress' => $result->clientBillAddress->toArray()), function ($message) use ($result) {
                        $message->to($result['email'])->from('info@foodelicious.nl', 'Foodelicious')->subject('Toegang tot groothandel.');
                    });

                    Notification::container('foundation')->success('U heeft zich geregistreerd voor de groothandels bestel website van Foodelicious. U aanvraag zal zo snel mogelijk bekeken worden en na goedkeuring ontvangt u daarover een email.');
            }
        } else {
            if ($input['send_mail']) {
                    Mail::send('frontend.email.activate-mail', array('user' => $result->toArray(), 'billAddress' => $result->clientBillAddress->toArray()), function ($message) use ($result) {
                        $message->to($result['email'])->from('info@foodelicious.nl', 'Foodelicious')->subject('Toegang tot groothandel.');
                    });

                    Notification::container('foundation')->success('Uw account is geactiveerd.');
            }
        }

        Notification::success('The client was activate.');
        return redirect()->route('hideyo.client.index');
    }


    public function postDeActivate($clientId)
    {
        $this->client->deactivate($clientId);
        Notification::success('The client was deactivate.');
        return redirect()->route('hideyo.client.index');
    }

    public function store()
    {
        $result  = $this->client->create($this->request->all());
        
        if (isset($result->id)) {
            Notification::success('The client was inserted.');
            return redirect()->route('hideyo.client.index');
        }
            
        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        return redirect()->back()->withInput();
    }

    public function edit($clientId)
    {
        $addresses = $this->clientAddress->selectAllByClientId($clientId);

        $addressesList = array();

        if ($addresses) {
            foreach ($addresses as $row) {
                $addressesList[$row->id] = $row->street.' '.$row->housenumber;
                if ($row->housenumber_suffix) {
                    $addressesList[$row->id] .= $row->housenumber_suffix;
                }

                $addressesList[$row->id] .= ', '.$row->city;
            }
        }

        return view('hideyo_backend::client.edit')->with(array('client' => $this->client->find($clientId), 'addresses' => $addressesList));
    }

    public function getExport()
    {
        return view('hideyo_backend::client.export')->with(array());
    }

    public function postExport()
    {

        $result  =  $this->client->selectAllExport();
        Excel::create('export', function ($excel) use ($result) {

            $excel->sheet('Clients', function ($sheet) use ($result) {
                $newArray = array();
                foreach ($result as $row) {
                    $firstname = null;
                    if($row->clientBillAddress) {
                        $firstname = $row->clientBillAddress->firstname;
                    }

                    $lastname = null;
                    if($row->clientBillAddress) {
                        $lastname = $row->clientBillAddress->lastname;
                    }

                    $gender = null;
                    if($row->clientBillAddress) {
                        $gender = $row->clientBillAddress->gender;
                    }


                    $newArray[$row->id] = array(
                    'email' => $row->email,
                    'company' => $row->company,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'gender' => $gender


                    );


           
                }

                $sheet->fromArray($newArray);
            });
        })->download('xls');


        Notification::success('The product export is completed.');
        return redirect()->route('hideyo.product.index');
    }



    public function update($clientId)
    {
        $result  = $this->client->updateById($this->request->all(), $clientId);
        $input = $this->request->all();
        if (isset($result->id)) {
            if ($result->active) {
                $shop  = Auth::guard('hideyobackend')->user()->shop;

                if ($shop->wholesale and $result) {
                    if ($input['send_mail']) {
                        Mail::send('frontend.email.activate-mail-wholesale', array('user' => $result->toArray(), 'billAddress' => $result->clientBillAddress->toArray()), function ($message) use ($result) {
                            $message->to($result['email'])->from('info@foodelicious.nl', 'Foodelicious')->subject('Toegang tot groothandel.');
                        });

                        Notification::container('foundation')->success('U heeft zich geregistreerd voor de groothandels bestel website van Foodelicious. U aanvraag zal zo snel mogelijk bekeken worden en na goedkeuring ontvangt u daarover een email.');
                    }
                } else {
                    if ($input['send_mail']) {
                        Mail::send('frontend.email.activate-mail', array('user' => $result->toArray(), 'billAddress' => $result->clientBillAddress->toArray()), function ($message) use ($result) {
                            $message->to($result['email'])->from('info@foodelicious.nl', 'Foodelicious')->subject('Toegang tot groothandel.');
                        });

                        Notification::container('foundation')->success('Uw account is geactiveerd.');
                    }
                }
            }



            Notification::success('The client was updated.');
            return redirect()->route('hideyo.client.index');
        }
        
        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        return redirect()->back()->withInput();
    }

    public function destroy($clientId)
    {
        $result  = $this->client->destroy($clientId);

        if ($result) {
            Notification::success('The client was deleted.');
            return redirect()->route('hideyo.client.index');
        }
    }
}
