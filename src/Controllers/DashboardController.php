<?php namespace Hideyo\Ecommerce\Backend\Controllers;

use App\Http\Controllers\Controller;
use Hideyo\Ecommerce\Backend\Repositories\OrderRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\ShopRepositoryInterface;
use Hideyo\Ecommerce\Backend\Repositories\UserRepositoryInterface;
use Auth;

class DashboardController extends Controller
{


    public function __construct(
        OrderRepositoryInterface $order,
        ShopRepositoryInterface $shop,
        UserRepositoryInterface $user
    ) {
        $this->order = $order;
        $this->shop = $shop;
        $this->user = $user;
    }

    /*
    |--------------------------------------------------------------------------
    | Home Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders your application's "dashboard" for users that
    | are authenticated. Of course, you are free to change or remove the
    | controller as you wish. It is just here to get your app started!
    |
    */


    public function index()
    {
        $shop  = Auth::guard('hideyobackend')->user()->shop;
     
        return view('hideyo_backend::dashboard.stats')->with(
            array(

            )
        );
    }
}
