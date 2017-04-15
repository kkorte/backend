<?php namespace App\Http\Controllers;

/**
 * CouponController
 *
 * This is the controller of the sending methods of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Backend\Repositories\ProductRepositoryInterface;
use Hideyo\Backend\Repositories\TaxRateRepositoryInterface;
use Hideyo\Backend\Repositories\PaymentMethodRepositoryInterface;

use \Request;
use \Notification;
use \Redirect;

class InventoryController extends Controller
{
    public function __construct(
        ProductRepositoryInterface $inventory,
        TaxRateRepositoryInterface $taxRate,
        PaymentMethodRepositoryInterface $paymentMethod
    ) {
        $this->taxRate = $taxRate;
        $this->inventory = $inventory;
        $this->paymentMethod = $paymentMethod;
    }

    public function index()
    {

        if (Request::wantsJson()) {


           $product = $this->inventory->getModel()->select(
                [
                
                'product.id', 'product.shop_id', 'amount', 'price',
                'product.active', 'product_category_id',
                'product.title', 'product_category.title as categorytitle', 'product.meta_title', 'product.meta_description']
            )->with(array('productCategory', 'productImages'))

            ->leftJoin('product_category', 'product_category.id', '=', 'product.product_category_id')

            ->where('product.shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->orderBy('amount');
            




            $datatables = \Datatables::of($product)

            ->filterColumn('active', function ($query, $keyword) {
                $query->whereRaw("product.active like ?", ["%{$keyword}%"]);
                ;
            })

            ->filterColumn('title', function ($query, $keyword) {
                $query->whereRaw("product.title like ?", ["%{$keyword}%"]);
                ;
            })

            ->filterColumn('categorytitle', function ($query, $keyword) {
                $query->whereRaw("product_category.title like ?", ["%{$keyword}%"]);
                ;
            })

            ->addColumn('image', function ($product) {
                if ($product->productImages->count()) {
                    return '<img src="http://shop.brulo.nl/files/'.$product->shop_id.'/product_image/100x100/'.$product->id.'/'.$product->productImages->first()->file.'"  />';
                }
            })
            ->addColumn('price', function ($product) {
                if ($product->price) {
                     return '&euro; '. number_format($product->price, 2);
                }
            })

            ->addColumn('combinations', function ($product) {
                if ($product->attributes) {
                    $newArray = array();
                    foreach ($product->attributes as $attribute) {
                        $combi = array();
                        foreach ($attribute->combinations as $combination) {
                            $combi[] = $combination->attribute->value;
                        }

                        $newArray[] =  implode(' | ', $combi).' | amount: '.$attribute->amount;
                         # code...
                    }

                    return implode('</br>', $newArray);
                }
            })



            ->addColumn('categorytitle', function ($product) {
                return $product->categorytitle;
            })
            ->addColumn('seo', function ($productCategory) {
                if ($productCategory->meta_title && $productCategory->meta_description) {
                    return '<i class="fa fa-check"></i>';
                }
            })
            ->addColumn('action', function ($product) {
                $delete = \Form::deleteajax('/product/'. $product->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                 $copy = '<a href="/admin/product/'.$product->id.'/copy" class="btn btn-default btn-sm btn-info"><i class="entypo-pencil"></i>Copy</a>';
            
                $link = '<a href="/admin/product/'.$product->id.'/edit" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  ';
            
                return $link;
            });

            return $datatables->make(true);

        } else {
            return \View::make('inventory.index')->with('inventory', $this->inventory->selectAll());
        }
    }
}
