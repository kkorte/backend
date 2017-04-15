<?php namespace Hideyo\Backend\Controllers;

/**
 * CouponController
 *
 * This is the controller of the faqs of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Backend\Repositories\FaqItemRepositoryInterface;

use Illuminate\Http\Request;
use Notification;
use Datatables;
use Form;

class FaqItemController extends Controller
{
    public function __construct(
        Request $request, 
        FaqItemRepositoryInterface $faq
    ) {
        $this->request = $request;
        $this->faq = $faq;
    }

    public function index()
    {
        if ($this->request->wantsJson()) {

            $query = $this->faq->getModel()->select(
                [
                
                'faq_item.id', 'faq_item.faq_item_group_id',
                'faq_item.question', 'faq_item.answer', 'faq_item_group.title as grouptitle']
            )
            ->with(array('faqItemGroup'))
            ->leftJoin('faq_item_group', 'faq_item_group.id', '=', 'faq_item.faq_item_group_id')
            ->where('faq_item.shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id);

            $datatables = Datatables::of($query)
            ->addColumn('faqitemgroup', function ($query) {
                return $query->grouptitle;
            })
            ->addColumn('action', function ($query) {
                $deleteLink = Form::deleteajax('/admin/faq/'. $query->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $link = '<a href="/admin/faq/'.$query->id.'/edit" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  '.$deleteLink;
            
                return $link;
            });

            return $datatables->make(true);

        } else {
            return view('hideyo_backend::faq-item.index')->with('faq', $this->faq->selectAll());
        }
    }

    public function create()
    {
        $groups = $this->faq->selectAllGroups()->pluck('title', 'id')->toArray();
        return view('hideyo_backend::faq-item.create')->with(array('groups' => $groups));
    }

    public function store()
    {
        $result  = $this->faq->create($this->request->all());

        if (isset($result->id)) {
            Notification::success('The faq was inserted.');
            return redirect()->route('hideyo.faq.index');
        }
        
        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        
        return redirect()->back()->withInput();
    }

    public function edit($id)
    {

        $groups = $this->faq->selectAllGroups()->pluck('title', 'id')->toArray();
        return view('hideyo_backend::faq-item.edit')->with(array('faq' => $this->faq->find($id), 'groups' => $groups));
    }

    public function editSeo($id)
    {
        return view('hideyo_backend::faq-item.edit_seo')->with(array('faq' => $this->faq->find($id)));
    }

    public function update($faqId)
    {
        $result  = $this->faq->updateById($this->request->all(), $faqId);

        if (isset($result->id)) {
            if ($this->request->get('seo')) {
                Notification::success('FaqItem seo was updated.');
                return redirect()->route('hideyo.faq.edit_seo', $faqId);
            } elseif ($this->request->get('faq-combination')) {
                Notification::success('FaqItem combination leading attribute group was updated.');
                return redirect()->route('hideyo.faq.{faqId}.faq-combination.index', $faqId);
            } else {
                Notification::success('FaqItem was updated.');
                return redirect()->route('hideyo.faq.edit', $faqId);
            }
        }

        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        
       
        return redirect()->back()->withInput();
    }

    public function destroy($id)
    {
        $result  = $this->faq->destroy($id);

        if ($result) {
            Notification::success('The faq was deleted.');
            return redirect()->route('hideyo.faq.index');
        }
    }
}
