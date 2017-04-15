<?php namespace Hideyo\Backend\Controllers;
/**
 * ProductWeightTypeController
 *
 * This is the controller of the product weight types of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Backend\Repositories\ExtraFieldRepositoryInterface;

use Request;
use Notification;
use Datatables;
use Form;

class ExtraFieldDefaultValueController extends Controller
{
    public function __construct(ExtraFieldRepositoryInterface $extraField)
    {
        $this->extraField = $extraField;
    }

    public function index($extraFieldId)
    {
        if (Request::wantsJson()) {

            $query = $this->extraField->getValueModel()->select(
                [
                
                'id',
                'value']
            )->where('extra_field_id', '=', $extraFieldId);
            
            $datatables = Datatables::of($query)->addColumn('action', function ($query) use ($extraFieldId) {
                $deleteLink = Form::deleteajax(url()->route('hideyo.extra-field-values.destroy', array('ExtraFieldId' => $extraFieldId, 'id' => $query->id)), 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $link = ' <a href="'.url()->route('hideyo.extra-field-values.edit', array('ExtraFieldId' => $extraFieldId, 'id' => $query->id)).'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a> 
                '.$deleteLink;
            
                return $link;
            });

            return $datatables->make(true);

        } else {
            return view('hideyo_backend::extra-field-default-value.index')->with('extraField', $this->extraField->find($extraFieldId));
        }
    }

    public function create($extraFieldId)
    {
        return view('hideyo_backend::extra-field-default-value.create')->with(array('extraField' =>  $this->extraField->find($extraFieldId)));
    }

    public function store($extraFieldId)
    {
        $result  = $this->extraField->createValue(Request::all(), $extraFieldId);

        if (isset($result->id)) {
            Notification::success('The extra field was inserted.');
            return redirect()->route('hideyo.extra-field-values.index', $extraFieldId);
        } else {
            foreach ($result->errors()->all() as $error) {
                Notification::error($error);
            }
        }

        return redirect()->back()->withInput();
    }

    public function edit($extraFieldId, $id)
    {
        return view('hideyo_backend::extra-field-default-value.edit')->with(array('extraFieldDefaultValue' => $this->extraField->findValue($id)));
    }

    public function update($extraFieldId, $id)
    {
        $result  = $this->extraField->updateValueById(Request::all(), $extraFieldId, $id);

        if (isset($result->id)) {
            Notification::success('The extra field was updated.');
            return redirect()->route('hideyo.extra-field-values.index', $extraFieldId);
        } else {
            foreach ($result->errors()->all() as $error) {
                Notification::error($error);
            }
        }

        return redirect()->back()->withInput();
    }

    public function destroy($extraFieldId, $id)
    {
        $result  = $this->extraField->destroyValue($id);

        if ($result) {
            Notification::success('Extra field was deleted.');
            return Redirect::route('hideyo.extra-field-values.index', $extraFieldId);
        }
    }
}
