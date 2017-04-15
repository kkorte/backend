<?php namespace Hideyo\Backend\Controllers;

/**
 * CouponController
 *
 * This is the controller of the newss of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Backend\Repositories\NewsRepositoryInterface;

use Request;
use Notification;

class NewsGroupController extends Controller
{
    public function __construct(
        NewsRepositoryInterface $news
    ) {
        $this->news = $news;
    }

    public function index()
    {
        if (Request::wantsJson()) {

            $query = $this->news->getGroupModel()->select(
                [
                
                'id',
                'title']
            )->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id);

            $datatables = \Datatables::of($query)
            ->addColumn('action', function ($query) {
                $delete = \Form::deleteajax(url()->route('hideyo.news-group.destroy', $query->id), 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $link = '<a href="'.url()->route('hideyo.news-group.edit', $query->id).'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  '.$delete;
            
                return $link;
            });


            return $datatables->make(true);

        } else {
            return view('hideyo_backend::news_group.index')->with('newsGroup', $this->news->selectAll());
        }
    }

    public function create()
    {
        return view('hideyo_backend::news_group.create')->with(array());
    }

    public function store()
    {
        $result  = $this->news->createGroup(\Request::all());

        if (isset($result->id)) {
            Notification::success('The news was inserted.');
            return redirect()->route('hideyo.news-group.index');
        }
        
        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        
        return redirect()->back()->withInput();
    }

    public function edit($id)
    {
        return view('hideyo_backend::news_group.edit')->with(array('newsGroup' => $this->news->findGroup($id)));
    }

    public function update($newsGroupId)
    {
        $result  = $this->news->updateGroupById(Request::all(), $newsGroupId);

        if (isset($result->id)) {
            if (Request::get('seo')) {
                Notification::success('NewsGroup seo was updated.');
                return redirect()->route('hideyo.news-group.edit_seo', $newsGroupId);
            } elseif (Request::get('news-combination')) {
                Notification::success('NewsGroup combination leading attribute group was updated.');
                return redirect()->route('hideyo.news-group.{newsId}.news-combination.index', $newsGroupId);
            } else {
                Notification::success('NewsGroup was updated.');
                return redirect()->route('hideyo.news-group.edit', $newsGroupId);
            }
        }

        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }        
       
        return redirect()->back()->withInput();
    }

    public function destroy($id)
    {
        $result  = $this->news->destroyGroup($id);

        if ($result) {
            Notification::success('The news group was deleted.');
            return redirect()->route('hideyo.news-group.index');
        }
    }
}
