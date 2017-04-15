<?php namespace Hideyo\Backend\Controllers;

use App\Http\Controllers\Controller;
use Dutchbridge\Datatable\OrderDatatable;
use Dutchbridge\Datatable\OrderWholesaleDatatable;
use Hideyo\Backend\Repositories\OrderRepositoryInterface;
use Hideyo\Backend\Repositories\ShopRepositoryInterface;
use Hideyo\Backend\Repositories\UserRepositoryInterface;

use Request;
use Auth;
use Notification;
use Lava;
use Carbon\Carbon;

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




    public function getStatsPaymentMethodByYear($year)
    {
        $monthlyRevenueYears = $this->order->monthlyRevenueYears();


        $paymentMethods = $this->order->paymentMethodOrdersInformation($year);


        if ($paymentMethods) {
            $finances = Lava::DataTable();
            $newRows = array();
            foreach ($paymentMethods as $paymentMethod) {
                $newRows[$paymentMethod->dm][$paymentMethod->paymenttitle] = $paymentMethod->count;

            }

            $finances->addStringColumn('Title')
                     ->addNumberColumn('total');

            foreach ($newRows as $month => $row) {

                foreach($row as $key => $count) {         
                    $finances->addRow([$key, $count]);
                }           
            }
  
            $revenueChartPaymentMethod = Lava::ColumnChart('PaymentMethodDetect', $finances, [
               
                'titleTextStyle' => [
                   
                    'fontSize' => 14
                ]
            ]);
        }

        return view('hideyo_backend::dashboard.ajax-payment-method-by-year')->with(array('selectedYear' => $year, 'years' => $monthlyRevenueYears, 'revenueChartPaymentMethod' => $revenueChartPaymentMethod));
    }


    public function getStatsRevenueByYear($year)
    {
        $monthlyRevenueYears = $this->order->monthlyRevenueYears();

        $monthlyRevenue = $this->order->monthlyRevenue($year);

        $revenueChart = "";
        if ($monthlyRevenue) {
            $finances = Lava::DataTable();

            $finances->addStringColumn('Month')
                     ->addNumberColumn('Euro\'s');
            foreach ($monthlyRevenue as $month) {
                $finances->addRow([$month->dm, $month->price_with_tax]);
            }
  
            $revenueChart = Lava::ColumnChart('Finances', $finances, [
                'titleTextStyle' => [
                
                    'fontSize' => 14
                ]
            ]);
        }

        return view('hideyo_backend::dashboard.ajax-revenue-by-year')->with(array('selectedYear' => $year, 'years' => $monthlyRevenueYears, 'revenueChart' => $revenueChart));
    }


    public function getStatsOrderAverageByYear($year)
    {
        $monthlyRevenueYears = $this->order->monthlyRevenueYears();

        $monthlyRevenue = $this->order->monthlyRevenue($year);

        $revenueChart = "";
        if ($monthlyRevenue) {
            $finances = Lava::DataTable();

            $finances->addStringColumn('Month')
                     ->addNumberColumn('Euro\'s');
            foreach ($monthlyRevenue as $month) {
                $finances->addRow([$month->dm, $month->price_with_tax / $month->total_orders]);
            }
  
            $revenueChart = Lava::ColumnChart('FinancesAverage', $finances, [
                'titleTextStyle' => [
                
                    'fontSize' => 14
                ]
            ]);
        }

        return view('hideyo_backend::dashboard.ajax-order-average-by-year')->with(array('selectedYear' => $year, 'years' => $monthlyRevenueYears, 'revenueAverageChart' => $revenueChart));
    }




    public function getStatsTotalsByYear($year)
    {
        $monthlyRevenueYears = $this->order->monthlyRevenueYears();

        $monthlyRevenue = $this->order->monthlyRevenue($year);

        $revenueChartTotal = "";
        if ($monthlyRevenue) {
            $totals = Lava::DataTable();

            $totals->addStringColumn('Month')
                     ->addNumberColumn('orders');
            foreach ($monthlyRevenue as $month) {
                $totals->addRow([$month->dm, $month->total_orders]);
            }
  
            $revenueChartTotal = Lava::ColumnChart('totalOrders', $totals, [
               
                'titleTextStyle' => [
                   
                    'fontSize' => 14
                ]
            ]);
        }

        return view('hideyo_backend::dashboard.ajax-total-by-year')->with(array('selectedYear' => $year, 'years' => $monthlyRevenueYears, 'revenueChart' => $revenueChartTotal));
    }



    public function getStatsBrowserByYear($year)
    {
        $monthlyRevenueYears = $this->order->monthlyRevenueYears();

        $isMobile = $this->order->browserDetectOrdersInformation($year);


        $revenueChart = "";
        if ($isMobile) {
            $finances = Lava::DataTable();

            $finances->addStringColumn('Month')
                     ->addNumberColumn('Desktop')
                     ->addNumberColumn('Tablet')
                     ->addNumberColumn('Mobile');
            foreach ($isMobile as $month) {
                $finances->addRow([$month->dm, $month->desktop, $month->tablet, $month->mobile]);
            }
  
            $revenueChart = Lava::ColumnChart('BrowserDetect', $finances, [
                'titleTextStyle' => [
                
                    'fontSize' => 14
                ]
            ]);
        }

        return view('hideyo_backend::dashboard.ajax-browser-by-year')->with(array('selectedYear' => $year, 'years' => $monthlyRevenueYears, 'revenueChart' => $revenueChart));
    }






    public function getShowOrder($orderId)
    {
        $order = $this->order->find($orderId);

        if (Auth::user()) {
            $id = Auth::id();
        }

        $shop = $this->shop->find($order->shop_id);

        $result  = $this->user->updateShopProfileById($shop, $id);
        Notification::success('The shop changed.');
        return \Redirect::route('order.show', array('id' => $orderId));
    }
}
