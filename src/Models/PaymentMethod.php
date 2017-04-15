<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PaymentMethod extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'payment_method';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['total_price_discount_type', 'total_price_discount_value', 'total_price_discount_start_date', 'total_price_discount_end_date',  'active', 'percent_of_total', 'price', 'no_price_from', 'payment_external', 'title', 'shop_id', 'tax_rate_id', 'mollie_external_payment_way', 'order_confirmed_order_status_id', 'payment_completed_order_status_id',  'payment_failed_order_status_id', 'mollie_api_key', 'modified_by_user_id'];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function getPriceDetails()
    {

        if (isset($this->taxRate->rate)) {
            $taxRate = $this->taxRate->rate;
            $price_inc = (($this->taxRate->rate / 100) * $this->price) + $this->price;
        } else {
            $taxRate = 0;
            $price_inc = 0;
        }

        return array(
            'orginal_price_ex_tax' => $this->price,
            'orginal_price_inc_tax' => $price_inc,
            'orginal_price_ex_tax_number_format' => number_format($this->price, 2, '.', ''),
            'orginal_price_inc_tax_number_forma' => number_format($price_inc, 2, '.', ''),
            'tax_rate' => $taxRate,
            'tax_value' => $price_inc - $this->price,
            'tax_value_number_format' => number_format(($price_inc - $this->price), 2, '.', ''),
            'currency' => $this->Shop->currency_code,

        );
    }

    public function taxRate()
    {
        return $this->belongsTo('Hideyo\Backend\Models\TaxRate');
    }

    public function orderConfirmedOrderStatus()
    {
        return $this->belongsTo('Hideyo\Backend\Models\OrderStatus', 'order_confirmed_order_status_id');
    }

    public function orderPaymentCompletedOrderStatus()
    {
        return $this->belongsTo('Hideyo\Backend\Models\OrderStatus', 'payment_completed_order_status_id');
    }

    public function orderPaymentFailedOrderStatus()
    {
        return $this->belongsTo('Hideyo\Backend\Models\OrderStatus', 'payment_failed_order_status_id');
    }

    public function shop()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Shop');
    }

    public function setTotalPriceDiscountStartDateAttribute($value)
    {
        $this->attributes['total_price_discount_start_date'] = null;
        
        if ($value) {
            $date = explode('/', $value);
            $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
            $this->attributes['total_price_discount_start_date'] = $value;
        }
    }

    public function getTotalPriceDiscountStartDateAttribute($value)
    {
        if ($value) {
            $date = explode('-', $value);
            return $date[2].'/'.$date[1].'/'.$date[0];
        }
        
        return null;
    }

    public function setTotalPriceDiscountEndDateAttribute($value)
    {
        $this->attributes['total_price_discount_end_date'] = null;

        if ($value) {
            $date = explode('/', $value);
            $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
            $this->attributes['total_price_discount_end_date'] = $value;
        }
    }

    public function getTotalPriceDiscountEndDateAttribute($value)
    {
        if ($value) {
            $date = explode('-', $value);
            return $date[2].'/'.$date[1].'/'.$date[0];
        }
        
        return null;
    }
}
