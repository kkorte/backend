<?php namespace Hideyo\Backend\Controllers;


/**
 * ProductController
 *
 * This is the controller of the product weight types of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;


use Hideyo\Backend\Repositories\ProductRelatedProductRepositoryInterface;
use Hideyo\Backend\Repositories\ProductRepositoryInterface;

use Illuminate\Http\Request;
use Notification;

class ProductRelatedProductController extends Controller
{
    public function __construct(Request $request, ProductRelatedProductRepositoryInterface $productRelatedProduct, ProductRepositoryInterface $product)
    {
        $this->productRelatedProduct    = $productRelatedProduct;
        $this->product                  = $product;
        $this->request                  = $request;
    }

    public function index($productId)
    {
           $product = $this->product->find($productId);
        if ($this->request->wantsJson()) {

            $query = $this->productRelatedProduct->getModel()->where('product_id', '=', $productId);
            
            $datatables = \Datatables::of($query)
                ->addColumn('related', function ($query) use ($productId) {
                    return $query->RelatedProduct->title;
                })
                ->addColumn('product', function ($query) use ($productId) {
                    return $query->Product->title;
                })
                ->addColumn('action', function ($query) use ($productId) {
                    $deleteLink = \Form::deleteajax(url()->route('hideyo.product.related-product.destroy', array('productId' => $productId, 'id' => $query->id)), 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                    
                    return $deleteLink;
                });

                return $datatables->make(true);
        } else {
            return view('hideyo_backend::product_related_product.index')->with(array('product' => $product));
        }
    }

    public function create($productId)
    {
        $product = $this->product->find($productId);
        $products = $this->product->selectAll()->pluck('title', 'id');

        return view('hideyo_backend::product_related_product.create')->with(array('products' => $products, 'product' => $product));
    }

    public function store($productId)
    {
        $result  = $this->productRelatedProduct->create($this->request->all(), $productId);
        return redirect()->route('hideyo.product.related-product.index', $productId);
    }

    public function edit($id)
    {
        return view('hideyo_backend::product_related_product.edit')->with(array('productRelatedProduct' => ProductImage::find($id), 'categories' => $this->productRelatedProduct->selectAll()->pluck('title', 'id')));
    }

    public function update($id)
    {
        $result  = $this->productRelatedProduct->updateById($this->generateInput(), $id);

        if (!$result->id) {
            return redirect()->back()->withInput()->withErrors($result->errors()->all());
        } else {
            Notification::success('The related product is updated.');
            return redirect()->route('hideyo.product_related_product.index');
        }
    }

    public function destroy($productId, $id)
    {
        $result  = $this->productRelatedProduct->destroy($id);

        if ($result) {
            Notification::success('The related product is deleted.');
            return redirect()->route('hideyo.product.related-product.index', $productId);
        }
    }
}
