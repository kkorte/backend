<?php namespace Hideyo\Backend\Controllers;

/**
 * ProductController
 *
 * This is the controller of the product weight types of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;


use Hideyo\Backend\Repositories\ProductCombinationRepositoryInterface;
use Hideyo\Backend\Repositories\ProductRepositoryInterface;
use Hideyo\Backend\Repositories\ExtraFieldRepositoryInterface;
use Hideyo\Backend\Repositories\AttributeGroupRepositoryInterface;
use Hideyo\Backend\Repositories\TaxRateRepositoryInterface;

use \Request;
use \Notification;
use \Redirect;
use \Response;

class ProductCombinationController extends Controller
{
    public function __construct(
        ProductCombinationRepositoryInterface $productCombination,
        ProductRepositoryInterface $product,
        AttributeGroupRepositoryInterface $attributeGroup,
        TaxRateRepositoryInterface $taxRate
    ) {
        $this->productCombination = $productCombination;
        $this->product = $product;

        $this->attributeGroup = $attributeGroup;
        $this->taxRate = $taxRate;
    }

    public function index($productId)
    {
        $product = $this->product->find($productId);
        if (Request::wantsJson()) {


            $query = $this->productCombination->getModel()->select(
                ['id', 'tax_rate_id', 'amount', 'price', 'product_id', 'reference_code',
                'default_on']
            )->where('product_id', '=', $productId);

            $datatables = \Datatables::of($query)->addColumn('action', function ($query) use ($productId) {
                $deleteLink = \Form::deleteajax(url()->route('hideyo.product.combination.destroy', array('productId' => $productId, 'id' => $query->id)), 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $links = '<a href="'.url()->route('hideyo.product.combination.edit', array('productId' => $productId, 'id' => $query->id)).'" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  '.$deleteLink;
            
                return $links;
            })

            ->addColumn('amount', function ($query) {
                return '<input type="text" class="change-amount-product-attribute" value="'.$query->amount.'" data-url="/admin/product/'.$query->product_id.'/product-combination/change-amount-attribute/'.$query->id.'">';
            })

            ->addColumn('price', function ($query) {
                if ($query->price) {
                    if (isset($query->taxRate->rate)) {
                        $taxRate = $query->taxRate->rate;
                        $price_inc = (($query->taxRate->rate / 100) * $query->price) + $query->price;
                        $tax_value = $price_inc - $query->price;
                    } else {
                        $taxRate = 0;
                        $price_inc = 0;
                        $tax_value = 0;
                    }

                    $discount_price_inc = false;
                    $discount_price_ex = false;
                    $discountTaxRate = 0;
                    if ($query->discount_value) {
                        if ($query->discount_type == 'amount') {
                            $discount_price_inc = $price_inc - $query->discount_value;
                            $discount_price_ex = $discount_price_inc / 1.21;
                        } elseif ($query->discount_type == 'percent') {
                            $tax = ($query->discount_value / 100) * $price_inc;
                            $discount_price_inc = $price_inc - $tax;
                            $discount_price_ex = $discount_price_inc / 1.21;
                        }
                        $discountTaxRate = $discount_price_inc - $discount_price_ex;
                        $discount_price_inc = $discount_price_inc;
                        $discount_price_ex = $discount_price_ex;
                    }


                    $output = array(
                        'orginal_price_ex_tax'  => $query->price,
                        'orginal_price_ex_tax_number_format'  => number_format($query->price, 2, '.', ''),
                        'orginal_price_inc_tax' => $price_inc,
                        'orginal_price_inc_tax_number_format' => number_format($price_inc, 2, '.', ''),
                        'tax_rate' => $taxRate,
                        'tax_value' => $tax_value,
                        'currency' => 'EU',
                        'discount_price_inc' => $discount_price_inc,
                        'discount_price_inc_number_format' => number_format($discount_price_inc, 2, '.', ''),
                        'discount_price_ex' => $discount_price_ex,
                        'discount_price_ex_number_format' => number_format($discount_price_ex, 2, '.', ''),
                        'discount_tax_value' => $discountTaxRate,
                        'discount_value' => $query->discount_value,
                        'amount' => $query->amount
                        );

                    $result =  '&euro; '.$output['orginal_price_ex_tax_number_format'].' / &euro; '.$output['orginal_price_inc_tax_number_format'];


                    if ($query->discount_value) {
                        $result .= '<br/> discount: yes';
                    }
                }

                return $result;
            })




            ->addColumn('combinations', function ($query) use ($productId) {
                $items = array();
                foreach ($query->combinations as $row) {
                    $items[] = $row->attribute->attributeGroup->title.': '.$row->attribute->value;
                }
       
                return implode(', ', $items);
            });

            return $datatables->make(true);




        } else {
            return view('hideyo_backend::product-combination.index')->with(array('product' => $product, 'attributeGroups' => $this->attributeGroup->selectAll()->pluck('title', 'id')));
        }
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
            return view('hideyo_backend::product-combination.create')->with(array('taxRates' => $this->taxRate->selectAll()->pluck('title', 'id'), 'product' => $product, 'attributeGroups' => $this->attributeGroup->selectAll()->pluck('title', 'id')));
        }
    }

    public function changeAmount($productId, $id, $amount)
    {
        $result = $this->productCombination->changeAmount($id, $amount);

        return Response::json($result);
    }

    public function store($productId)
    {
        $result  = $this->productCombination->create(Request::all(), $productId);
 
        if (isset($result->id)) {
            Notification::success('The product extra fields are updated.');
            return Redirect::route('hideyo.product.combination.index', $productId);
        }

        if ($result) {
            foreach ($result->errors()->all() as $error) {
                \Notification::error($error);
            }
        } else {
            \Notification::error('combination already exist');
        }
        
        return \Redirect::back()->withInput();
    }

    public function edit($productId, $id)
    {
        $product = $this->product->find($productId);
        $productCombination = $this->productCombination->find($id);
        $selectedAttributes = array();
        $attributes = array();
        foreach ($productCombination->combinations as $row) {
            $selectedAttributes[] = $row->attribute->id;
            $attributes[$row->attribute->id]['group_id'] = $row->attribute->attributeGroup->id;
            $attributes[$row->attribute->id]['value'] = $row->attribute->value;
        }

        if (Request::wantsJson()) {
            $input = Request::all();
            $attributeGroup = $this->attributeGroup->find($input['attribute_group_id']);
            if ($attributeGroup->count()) {
                if ($attributeGroup->attributes()) {
                    return Response::json($attributeGroup->attributes);
                }
            }
        } else {
            return view('hideyo_backend::product-combination.edit')->with(array('taxRates' => $this->taxRate->selectAll()->pluck('title', 'id'), 'selectedAttributes' => $selectedAttributes, 'attributes' => $attributes, 'productCombination' => $productCombination, 'product' => $product, 'attributeGroups' => $this->attributeGroup->selectAll()->pluck('title', 'id')));
        }
    }

    public function update($productId, $id)
    {

        $result  = $this->productCombination->updateById(Request::all(), $productId, $id);

        if (!$result->id) {
            return Redirect::back()->withInput()->withErrors($result->errors()->all());
        }
        
        Notification::success('The product combination is updated.');
        return Redirect::route('hideyo.product.combination.index', $productId);
    }

    public function destroy($productId, $id)
    {
        $result  = $this->productCombination->destroy($id);

        if ($result) {
            Notification::success('The product combination is deleted.');
            return Redirect::route('hideyo.product.combination.index', $productId);
        }
    }
}
