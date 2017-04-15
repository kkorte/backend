<?php namespace Hideyo\Backend\Controllers;

/**
 * ProductController
 *
 * This is the controller of the product weight types of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;

use Hideyo\Backend\Repositories\ProductCategoryRepositoryInterface;

use Illuminate\Http\Request;
use Notification;
use Datatables;
use Form;

class ProductCategoryImageController extends Controller
{
    public function __construct(ProductCategoryRepositoryInterface $productCategory)
    {
        $this->productCategory = $productCategory;
    }

    public function index(Request $request, $productCategoryId)
    {
        $productCategory = $this->productCategory->find($productCategoryId);
        if ($request->wantsJson()) {

            $image = $this->productCategory->getImageModel()->select(
                ['id','file', 'product_category_id']
            )->where('product_category_id', '=', $productCategoryId);
            
            $datatables = Datatables::of($image)

            ->addColumn('thumb', function ($image) use ($productCategoryId) {
                return '<img src="/files/product_category/100x100/'.$image->product_category_id.'/'.$image->file.'"  />';
            })
            ->addColumn('action', function ($image) use ($productCategoryId) {
                $deleteLink = Form::deleteajax('/admin/product-category/'.$productCategoryId.'/images/'. $image->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $link = '<a href="/admin/product-category/'.$productCategoryId.'/images/'.$image->id.'/edit" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  '.$deleteLink;

                return $link;
            });

            return $datatables->make(true);
        


        }
        
        return view('hideyo_backend::product_category_image.index')->with(array('productCategory' => $productCategory));
    }

    public function create($productCategoryId)
    {
        $productCategory = $this->productCategory->find($productCategoryId);

        return view('hideyo_backend::product_category_image.create')->with(array('productCategory' => $productCategory));
    }

    public function store(Request $request, $productCategoryId)
    {
        $result  = $this->productCategory->createImage($request->all(), $productCategoryId);
 
        if (isset($result->id)) {
            Notification::success('The category image was inserted.');
            return redirect()->route('hideyo.product-category.{productCategoryId}.images.index', $productCategoryId);
        } else {
            foreach ($result->errors()->all() as $error) {
                Notification::error($error);
            }
            return redirect()->back()->withInput()->withErrors($result);
        }
    }

    public function edit(Request $request, $productCategoryId, $id)
    {
        $productCategory = $this->productCategory->find($productCategoryId);
        return view('hideyo_backend::product_category_image.edit')->with(array('productCategoryImage' => $this->productCategory->findImage($id), 'productCategory' => $productCategory));
    }

    public function update(Request $request, $productCategoryId, $id)
    {
        $result  = $this->productCategory->updateImageById($request->all(), $productCategoryId, $id);

        if (isset($result->id)) {
            Notification::success('The category image was updated.');
            return redirect()->route('hideyo.product-category.{productCategoryId}.images.index', $productCategoryId);
        } else {
            foreach ($result->errors()->all() as $error) {
                Notification::error($error);
            }
            return redirect()->back()->withInput()->withErrors($result);
        }
    }

    public function destroy($productCategoryId, $id)
    {
        $result  = $this->productCategory->destroyImage($id);

        if ($result) {
            Notification::success('The file was deleted.');
            return redirect()->route('hideyo.product-category.{productCategoryId}.images.index', $productCategoryId);
        }
    }
}
