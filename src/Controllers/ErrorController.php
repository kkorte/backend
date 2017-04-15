<?php namespace Hideyo\Backend\Controllers;

use App\Http\Controllers\Controller;

use Hideyo\Backend\Repositories\ExceptionRepositoryInterface;

use Illuminate\Http\Request;
use Notification;

class ErrorController extends Controller
{

    public function __construct(Request $request, ExceptionRepositoryInterface $error)
    {
        $this->request = $request;
        $this->error = $error;
    }

    public function index()
    {
        if ($this->request->wantsJson()) {

            $query = $this->error->getModel()->select(
                [
                
                'id',
                'class', 'file' , 'status_code', 'line', 'message', 'url', 'method']
            );
            
            $datatables = \Datatables::of($query)->addColumn('action', function ($query) {
                $delete = \Form::deleteajax('/admin/general-setting/'. $query->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $link = '<a href="/admin/general-setting/'.$query->id.'/edit" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  '.$delete;
            
                return $link;
            });

            return $datatables->make(true);

        } else {
            return view('hideyo_backend::error.index')->with('error', $this->error->selectAll());
        }
    }
}
