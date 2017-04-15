<?php namespace Hideyo\Backend\Controllers;

/**
 * ProductController
 *
 * This is the controller of the product weight types of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;

use Dutchbridge\Datatable\ProductAmountOptionDatatable;
use Hideyo\Backend\Repositories\ProductAmountOptionRepositoryInterface;
use Hideyo\Backend\Repositories\ProductRepositoryInterface;
use Hideyo\Backend\Repositories\ExtraFieldRepositoryInterface;
use Hideyo\Backend\Repositories\AttributeGroupRepositoryInterface;
use Hideyo\Backend\Repositories\TaxRateRepositoryInterface;

use Request;
use Notification;
use Redirect;
use Response;
use Datatables;
use Form;

class ProductAmountOptionController extends Controller
{
    public function __construct(
        ProductAmountOptionRepositoryInterface $productAmountOption,
        ProductRepositoryInterface $product,
        AttributeGroupRepositoryInterface $attributeGroup,
        TaxRateRepositoryInterface $taxRate
    ) {
        $this->productAmountOption = $productAmountOption;
        $this->product = $product;
        $this->attributeGroup = $attributeGroup;
        $this->taxRate = $taxRate;
    }

    public function index($productId)
    {   
        $datatable =  new ProductAmountOptionDatatable();
        $product = $this->product->find($productId);
        if (Request::wantsJson()) {

            $query = $this->productAmountOption->getModel()->select(
                ['id', 'amount','default_on']
            )->where('product_id', '=', $productId);
            
            $datatables = Datatables::of($query)->addColumn('action', function ($query) use ($productId) {
                $deleteLink = Form::deleteajax('/admin/product/'.$productId.'/product-amount-option/'. $query->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $link = '<a href="/admin/product/'.$productId.'/product-amount-option/'.$query->id.'/edit" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  '.$deleteLink;
                
                return $link;
            });

            return $datatables->make(true);
        }
        
        return view('hideyo_backend::product-amount-option.index')->with(array('product' => $product, 'attributeGroups' => $this->attributeGroup->selectAll()->pluck('title', 'id')));
    }

    public function create($productId)
    {
        $product = $this->product->find($productId);

        if (Request::wantsJson()) {
            $input = Request::all();
            $attributeGroup = $this->attributeGroup->find($input['attribute_group_id']);
            if ($attributeGroup->count()) {
                if ($attributeGroup->attributes()) {
                    return Response::json($attributeGroup->attributes);
                }
            }
        } else {
            return view('hideyo_backend::product-amount-option.create')->with(array('taxRates' => $this->taxRate->selectAll()->pluck('title', 'id'), 'product' => $product, 'attributeGroups' => $this->attributeGroup->selectAll()->pluck('title', 'id')));
        }
    }

    public function store($productId)
    {

        $result  = $this->productAmountOption->create(Request::all(), $productId);
 
        if (isset($result->id)) {
            Notification::success('The product amount option is updated.');
            return redirect()->route('admin.product.{productId}.product-amount-option.index', $productId);
        }

        if ($result) {
            foreach ($result->errors()->all() as $error) {
                Notification::error($error);
            }
        } else {
            Notification::error('amount option already exist');
        }
        
        return redirect()->back()->withInput();
    }

    public function edit($productId, $id)
    {
        $product = $this->product->find($productId);
        $productAmountOption = $this->productAmountOption->find($id);
        $selectedAttributes = array();
        $attributes = array();

          return view('hideyo_backend::product-amount-option.edit')->with(array('taxRates' => $this->taxRate->selectAll()->pluck('title', 'id'), 'selectedAttributes' => $selectedAttributes, 'attributes' => $attributes, 'productAmountOption' => $productAmountOption, 'product' => $product, 'attributeGroups' => $this->attributeGroup->selectAll()->pluck('title', 'id')));
    }

    public function update($productId, $id)
    {

        $result  = $this->productAmountOption->updateById(Request::all(), $productId, $id);

        if (!$result->id) {
            return redirect()->back()->withInput()->withErrors($result->errors()->all());
        }
        
        Notification::success('The product amount option is updated.');
        return redirect()->route('admin.product.{productId}.product-amount-option.index', $productId);
    }

    public function destroy($productId, $id)
    {
        $result  = $this->productAmountOption->destroy($id);

        if ($result) {
            Notification::success('The product amount option is deleted.');
            return redirect()->route('admin.product.{productId}.product-amount-option.index', $productId);
        }
    }
}
