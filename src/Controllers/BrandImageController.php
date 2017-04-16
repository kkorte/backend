<?php namespace Hideyo\Backend\Controllers;
/**
 * ProductController
 *
 * This is the controller of the brand images of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;

use Hideyo\Backend\Repositories\BrandRepositoryInterface;

use Illuminate\Http\Request;
use Notification;
use Form;
use Datatables;

class BrandImageController extends Controller
{
    public function __construct(Request $request, BrandRepositoryInterface $brand)
    {
        $this->brand = $brand;
        $this->request = $request;
    }

    public function index($brandId)
    {
        $brand = $this->brand->find($brandId);
        if ($this->request->wantsJson()) {

            $image = $this->brand->getModelImage()
            ->select(['id','file', 'brand_id'])
            ->where('brand_id', '=', $brandId);
            
            $datatables = Datatables::of($image)

            ->addColumn('thumb', function ($image) use ($brandId) {
                return '<img src="/files/brand/100x100/'.$image->brand_id.'/'.$image->file.'"  />';
            })
            ->addColumn('action', function ($image) use ($brandId) {
                $deleteLink = Form::deleteajax('/admin/brand/'.$brandId.'/images/'. $image->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $links = '<a href="/admin/brand/'.$brandId.'/images/'.$image->id.'/edit" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  '.$deleteLink;
                return $links;
            });

            return $datatables->make(true);
        }
        
        return view('hideyo_backend::brand_image.index')->with(array( 'brand' => $brand));
    }

    public function create($brandId)
    {
        $brand = $this->brand->find($brandId);
        return view('hideyo_backend::brand_image.create')->with(array('brand' => $brand));
    }

    public function store($brandId)
    {
        $result  = $this->brand->createImage($this->request->all(), $brandId);
 
        if (isset($result->id)) {
            Notification::success('The brand image was inserted.');
            return redirect()->route('hideyo.brand.{brandId}.images.index', $brandId);
        } else {
            foreach ($result->errors()->all() as $error) {
                Notification::error($error);
            }
            return redirect()->back()->withInput()->withErrors($result);
        }
    }

    public function edit($brandId, $brandImageId)
    {
        $brand = $this->brand->find($brandId);
        return view('hideyo_backend::brand_image.edit')->with(array('brandImage' => $this->brand->findImage($brandImageId), 'brand' => $brand));
    }

    public function update($brandId, $brandImageId)
    {
        $result  = $this->brand->updateImageById($this->request->all(), $brandId, $brandImageId);

        if (isset($result->id)) {
            Notification::success('The brand image was updated.');
            return redirect()->route('hideyo.brand.{brandId}.images.index', $brandId);
        } else {
            foreach ($result->errors()->all() as $error) {
                Notification::error($error);
            }
            return redirect()->back()->withInput()->withErrors($result);
        }
    }

    public function destroy($brandId, $brandImageId)
    {
        $result  = $this->brand->destroyImage($brandImageId);

        if ($result) {
            Notification::success('The file was deleted.');
            return redirect()->route('hideyo.brand.{brandId}.images.index', $brandId);
        }
    }
}
