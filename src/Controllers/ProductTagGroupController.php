<?php namespace Hideyo\Ecommerce\Backend\Controllers;


/**
 * ProductTagGroupController
 *
 * This is the controller of the product tag groups of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Ecommerce\Backend\Repositories\ProductTagGroupRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\ProductRepositoryInterface;

use Request;
use Notification;

class ProductTagGroupController extends Controller
{
    public function __construct(
        ProductTagGroupRepositoryInterface $productTagGroup,
        ProductRepositoryInterface $product
    ) {
        $this->product = $product;
        $this->productTagGroup = $productTagGroup;
    }

    public function index()
    {
        if (Request::wantsJson()) {

            $query = $this->productTagGroup->getModel()
            ->select(['id','tag'])
            ->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id);
            
            $datatables = \Datatables::of($query)->addColumn('action', function ($query) {
                $deleteLink = \Form::deleteajax(url()->route('hideyo.product-tag-group.destroy', $query->id), 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $links = '<a href="'.url()->route('hideyo.product-tag-group.edit', $query->id).'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  '.$deleteLink;
            
                return $links;
            });

            return $datatables->make(true);


        }
        
        return view('hideyo_backend::product_tag_group.index')->with('productTagGroup', $this->productTagGroup->selectAll());
    }

    public function create()
    {
        return view('hideyo_backend::product_tag_group.create')->with(array(
            'products' => $this->product->selectAll()->pluck('title', 'id')
        ));
    }

    public function store()
    {
        $result  = $this->productTagGroup->create(\Request::all());

        if (isset($result->id)) {
            Notification::success('The product group tag was inserted.');
            return redirect()->route('hideyo.product-tag-group.index');
        }
        
        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        
        return redirect()->back()->withInput();
    }

    public function edit($productTagGroupId)
    {
        return view('hideyo_backend::product_tag_group.edit')->with(array(
            'products' => $this->product->selectAll()->pluck('title', 'id'),
            'productTagGroup' => $this->productTagGroup->find($productTagGroupId)
            ));
    }

    public function update($productTagGroupId)
    {
        $result  = $this->productTagGroup->updateById(\Request::all(), $productTagGroupId);

        if (isset($result->id)) {
            Notification::success('The product group tag was updated.');
            return redirect()->route('hideyo.product-tag-group.index');
        }
        
        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }
        
        return redirect()->back()->withInput();
    }

    public function destroy($productTagGroupId)
    {
        $result  = $this->productTagGroup->destroy($productTagGroupId);

        if ($result) {
            Notification::success('The product group tag was deleted.');
            return redirect()->route('hideyo.product-tag-group.index');
        }
    }
}
