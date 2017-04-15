<?php 

namespace Hideyo\Backend\Models;

use Hideyo\Backend\Models\BaseModel;
use Carbon\Carbon;

class SendingMethod extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'sending_method';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['total_price_discount_type', 'total_price_discount_value', 'total_price_discount_start_date', 'total_price_discount_end_date', 'active', 'price', 'no_price_from', 'minimal_weight', 'maximal_weight', 'title', 'shop_id', 'tax_rate_id', 'modified_by_user_id'];

    public function relatedPaymentMethods()
    {
        return $this->belongsToMany('Hideyo\Backend\Models\PaymentMethod', config()->get('hideyo.db_prefix').'sending_payment_method_related');
    }

    public function getPriceDetails()
    {
        $taxRate = 0;
        $price_inc = 0;

        if (isset($this->taxRate->rate)) {
            $taxRate = $this->taxRate->rate;
            $price_inc = (($this->taxRate->rate / 100) * $this->price) + $this->price;
        }

        return array(
            'orginal_price_ex_tax' => $this->price,
            'orginal_price_inc_tax' => $price_inc,
            'orginal_price_ex_tax_number_format' => number_format($this->price, 2, '.', ''),
            'orginal_price_inc_tax_number_format' => number_format($price_inc, 2, '.', ''),
            'tax_rate' => $taxRate,
            'tax_value' => $price_inc - $this->price,
            'tax_value_number_format' => number_format(($price_inc - $this->price), 2, '.', ''),
            'currency' => $this->shop->currency_code,

        );
    }

    public function taxRate()
    {
        return $this->belongsTo('Hideyo\Backend\Models\TaxRate');
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
        if ($value) {
            $date = explode('/', $value);
            $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
            $this->attributes['total_price_discount_end_date'] = $value;
        } else {
            $this->attributes['total_price_discount_end_date'] = null;
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
