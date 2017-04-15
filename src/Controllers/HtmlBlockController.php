<?php namespace Hideyo\Backend\Controllers;

/**
 * CouponController
 *
 * This is the controller of the htmlBlocks of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Backend\Repositories\HtmlBlockRepositoryInterface;

use Illuminate\Http\Request;
use Notification;
use Datatables;
use Form;

class HtmlBlockController extends Controller
{
    public function __construct(
        Request $request,
        HtmlBlockRepositoryInterface $htmlBlock
    ) {
        $this->htmlBlock = $htmlBlock;
        $this->request = $request;
    }

    public function index()
    {
        if ($this->request->wantsJson()) {

            $query = $this->htmlBlock->getModel()->select(
                ['id', 'active',
                'title', 'image_file_name', 'position']
            )->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id);
            
            $datatables = Datatables::of($query)

            ->addColumn('active', function ($query) {
                if ($query->active) {
                    return '<a href="#" class="change-active" data-url="/admin/html-block/change-active/'.$query->id.'"><span class="glyphicon glyphicon-ok icon-green"></span></a>';
                } else {
                    return '<a href="#" class="change-active" data-url="/admin/html-block/change-active/'.$query->id.'"><span class="glyphicon glyphicon-remove icon-red"></span></a>';
                }
            })
            ->addColumn('image', function ($query) {
                if ($query->image_file_name) {
                    return '<img src="/files/html_block/'.$query->id.'/'.$query->image_file_name.'" width="200px" />';
                }
            })
            ->addColumn('action', function ($query) {
                $delete = Form::deleteajax(url()->route('hideyo.html-block.destroy', $query->id), 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                
                $copy = '<a href="/admin/html-block/'.$query->id.'/copy" class="btn btn-default btn-sm btn-info"><i class="entypo-pencil"></i>Copy</a>';

                $link = '<a href="'.url()->route('hideyo.html-block.edit', $query->id).'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a> '.$copy.' '.$delete;
            
                return $link;
            });

            return $datatables->make(true);

        }
        
        return view('hideyo_backend::html-block.index')->with('htmlBlock', $this->htmlBlock->selectAll());
    }

    public function create()
    {
        return view('hideyo_backend::html-block.create')->with(array());
    }

    public function copy($htmlBlockId)
    {
        $htmlBlock = $this->htmlBlock->find($htmlBlockId);

        return view('hideyo_backend::html-block.copy')->with(
            array(
            'htmlBlock' => $htmlBlock
            )
        );
    }
    
    public function storeCopy($htmlBlockId)
    {
        $htmlBlock = $this->htmlBlock->find($htmlBlockId);
        $result  = $this->htmlBlock->createCopy($this->request->all(), $htmlBlockId);

        if (isset($result->id)) {
            Notification::success('The htmlBlock copy is inserted.');
            return redirect()->route('hideyo.html-block.index');
        }

        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        
        return redirect()->back()->withInput();
    }

    public function store()
    {
        $result  = $this->htmlBlock->create($this->request->all());

        if (isset($result->id)) {
            Notification::success('The html block was inserted.');
            return redirect()->route('hideyo.html-block.index');
        }
        
        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        
        return redirect()->back()->withInput();
    }

    public function changeActive($htmlBlockId)
    {
        $result = $this->htmlBlock->changeActive($htmlBlockId);
        return response()->json($result);
    }

    public function edit($id)
    {
        return view('hideyo_backend::html-block.edit')->with(array('htmlBlock' => $this->htmlBlock->find($id)));
    }

    public function editSeo($id)
    {
        return view('hideyo_backend::html-block.edit_seo')->with(array('htmlBlock' => $this->htmlBlock->find($id)));
    }

    public function update($htmlBlockId)
    {
        $result  = $this->htmlBlock->updateById($this->request->all(), $htmlBlockId);

        if (isset($result->id)) {
            if ($this->request->get('seo')) {
                Notification::success('HtmlBlock seo was updated.');
                return redirect()->route('hideyo.html-block.edit_seo', $htmlBlockId);
            } elseif ($this->request->get('htmlBlock-combination')) {
                Notification::success('HtmlBlock combination leading attribute group was updated.');
                return redirect()->route('hideyo.html-block.{htmlBlockId}.htmlBlock-combination.index', $htmlBlockId);
            } else {
                Notification::success('HtmlBlock was updated.');
                return redirect()->route('hideyo.html-block.edit', $htmlBlockId);
            }
        }

        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        
        return redirect()->back()->withInput();
    }

    public function destroy($id)
    {
        $result  = $this->htmlBlock->destroy($id);

        if ($result) {
            Notification::success('The html block was deleted.');
            return redirect()->route('hideyo.html-block.index');
        }
    }
}
