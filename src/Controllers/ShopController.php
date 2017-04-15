<?php namespace Hideyo\Backend\Controllers;

/**
 * CouponController
 *
 * This is the controller of the sending methods of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Backend\Repositories\ShopRepositoryInterface;
use Illuminate\Http\Request;
use Notification;
use Datatables;
use Form;

class ShopController extends Controller
{
    public function __construct(
        Request $request, 
        ShopRepositoryInterface $shop)
    {
        $this->shop = $shop;
        $this->request = $request;
    }

    public function index()
    {
        if ($this->request->wantsJson()) {

            $query = $this->shop->getModel()
            ->select(['id', 'title', 'logo_file_name']);
            $datatables = Datatables::of($query)

            ->addColumn('action', function ($query) {
                $delete = Form::deleteajax(url()->route('hideyo.shop.destroy', $query->id), 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $link = '<a href="'.url()->route('hideyo.shop.edit', $query->id).'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  '.$delete;
                return $link;
            })

            ->addColumn('image', function ($query) {
                if ($query->logo_file_name) {
                    return '<img src="http://shop.brulo.nl/files/'.$query->id.'/logo/'.$query->logo_file_name.'"  />';
                }
            });

            return $datatables->make(true);
        } else {
            return view('hideyo_backend::shop.index')->with('shop', $this->shop->selectAll());
        }
    }

    public function create()
    {
        return view('hideyo_backend::shop.create');
    }

    public function store()
    {
        $result  = $this->shop->create($this->request->all());

        if (isset($result->id)) {
            Notification::success('The shop was inserted.');
            return redirect()->route('hideyo.shop.index');
        }
        
        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        
        return redirect()->back()->withInput();
    }

    public function edit($id)
    {
        return view('hideyo_backend::shop.edit')->with(array('shop' => $this->shop->find($id)));
    }

    public function update($id)
    {
        $result  = $this->shop->updateById($this->request->all(), $id);

        if (isset($result->id)) {
            Notification::success('The shop was updated.');
            return redirect()->route('hideyo.shop.index');
        }
        
        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        
        return redirect()->back()->withInput();
    }

    public function destroy($id)
    {
        $result  = $this->shop->destroy($id);

        if ($result) {
            Notification::success('The shop was deleted.');
            return redirect()->route('hideyo.shop.index');
        }
    }
}
