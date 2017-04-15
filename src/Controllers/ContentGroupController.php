<?php namespace Hideyo\Backend\Controllers;

/**
 * CouponController
 *
 * This is the controller of the content groups of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Backend\Repositories\ContentRepositoryInterface;

use Illuminate\Http\Request;
use Notification;
use Datatables;
use Form;

class ContentGroupController extends Controller
{
    public function __construct(
        Request $request,
        ContentRepositoryInterface $content
    ) {
        $this->request = $request;
        $this->content = $content;
    }

    public function index()
    {
        if ($this->request->wantsJson()) {

            $query = $this->content->getGroupModel()
            ->select(['id', 'title'])
            ->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id);

            $datatables = Datatables::of($query)
            ->addColumn('action', function ($query) {
                $delete = Form::deleteajax(url()->route('hideyo.content-group.destroy', $query->id), 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $link = '<a href="'.url()->route('hideyo.content-group.edit', $query->id).'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  '.$delete;
            
                return $link;
            });

            return $datatables->make(true);
        }
        
        return view('hideyo_backend::content_group.index')->with('contentGroup', $this->content->selectAll());
    }

    public function create()
    {
        return view('hideyo_backend::content_group.create')->with(array());
    }

    public function store()
    {
        $result  = $this->content->createGroup($this->request->all());

        if (isset($result->id)) {
            Notification::success('The content was inserted.');
            return redirect()->route('hideyo.content-group.index');
        }
        
        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        
        return redirect()->back()->withInput();
    }

    public function edit($id)
    {
        return view('hideyo_backend::content_group.edit')->with(array('contentGroup' => $this->content->findGroup($id)));
    }

    public function editSeo($id)
    {
        return view('hideyo_backend::content_group.edit_seo')->with(array('contentGroup' => $this->content->find($id)));
    }

    public function update($contentGroupId)
    {
        $result  = $this->content->updateGroupById($this->request->all(), $contentGroupId);

        if (isset($result->id)) {
            if ($this->request->get('seo')) {
                Notification::success('ContentGroup seo was updated.');
                return redirect()->route('hideyo.content-group.edit_seo', $contentGroupId);
            } elseif ($this->request->get('content-combination')) {
                Notification::success('ContentGroup combination leading attribute group was updated.');
                return redirect()->route('hideyo.content-group.{contentId}.content-combination.index', $contentGroupId);
            } else {
                Notification::success('ContentGroup was updated.');
                return redirect()->route('hideyo.content-group.index');
            }
        }

        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }        
       
        return redirect()->back()->withInput();
    }

    /**
     * Remove the specified resource from storage
     * @param  int  $id
     * @return Redirect
     */
    public function destroy($id)
    {
        $result  = $this->content->destroyGroup($id);

        if ($result) {
            Notification::success('The content was deleted.');
            return redirect()->route('hideyo.content-group.index');
        }
    }
}
