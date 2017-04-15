<?php namespace Hideyo\Backend\Controllers;

/**
 * ProductController
 *
 * This is the controller of the product weight types of the shop
 * @author Matthijs Neijenhuijs <matthijs@hideyo.io>
 * @version 0.1
 */

use App\Http\Controllers\Controller;
use Hideyo\Backend\Repositories\NewsRepositoryInterface;

use Illuminate\Http\Request;
use Notification;

class NewsImageController extends Controller
{
    public function __construct(Request $request, NewsRepositoryInterface $news)
    {
        $this->news = $news;
        $this->request = $request;
    }

    public function index($newsId)
    {
        $news = $this->news->find($newsId);
        if ($this->request->wantsJson()) {

            $image = $this->news->getImageModel()->select(
                [
                
                'id',
                'file', 'news_id']
            )->where('news_id', '=', $newsId);
            
            $datatables = \Datatables::of($image)

            ->addColumn('thumb', function ($image) use ($newsId) {


                return '<img src="/files/news/100x100/'.$image->news_id.'/'.$image->file.'"  />';
            })


            ->addColumn('action', function ($image) use ($newsId) {
                $delete = \Form::deleteajax('/admin/news/'.$newsId.'/images/'. $image->id, 'Delete', '', array('class'=>'btn btn-default btn-sm btn-danger'));
                $link = '<a href="/admin/news/'.$newsId.'/images/'.$image->id.'/edit" class="btn btn-default btn-sm btn-success"><i class="entypo-pencil"></i>Edit</a>  '.$delete;

                return $link;
            });

            return $datatables->make(true);


        } else {
            return view('hideyo_backend::news_image.index')->with(array('news' => $news));
        }
    }

    public function create($newsId)
    {
        $news = $this->news->find($newsId);
        return view('hideyo_backend::news_image.create')->with(array('news' => $news));
    }

    public function store($newsId)
    {

        $result  = $this->newsImage->create($this->request->all(), $newsId);
 
        if (isset($result->id)) {
            Notification::success('The news image was inserted.');
            return redirect()->route('hideyo.news.{newsId}.images.index', $newsId);
        } else {
            foreach ($result->errors()->all() as $error) {
                \Notification::error($error);
            }
            return redirect()->back()->withInput()->withErrors($result);
        }
    }

    public function edit($newsId, $id)
    {
        $news = $this->news->find($newsId);
        return view('hideyo_backend::news_image.edit')->with(array('newsImage' => $this->news->findImage($id), 'news' => $news));
    }

    public function update($newsId, $id)
    {
        $result  = $this->news->updateImageById($this->request->all(), $newsId, $id);

        if (isset($result->id)) {
            Notification::success('The news image was updated.');
            return redirect()->route('hideyo.news.{newsId}.images.index', $newsId);
        } else {
            foreach ($result->errors()->all() as $error) {
                \Notification::error($error);
            }
            return redirect()->back()->withInput()->withErrors($result);
        }
    }

    public function destroy($newsId, $id)
    {
        $result  = $this->news->destroyImage($id);

        if ($result) {
            Notification::success('The file was deleted.');
            return redirect()->route('hideyo.news.{newsId}.images.index', $newsId);
        }
    }
}
