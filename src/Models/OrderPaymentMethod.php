<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPaymentMethod extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'order_payment_method';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['order_id', 'payment_method_id', 'title', 'price_with_tax', 'price_without_tax', 'tax_rate', 'tax_rate_id', 'percent_of_total', 'payment_external'];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function order()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Order');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('Hideyo\Backend\Models\PaymentMethod');
    }

    public function getPriceWithTaxNumberFormat()
    {
        return number_format($this->price_with_tax, 2);
    }

    public function getPriceWithoutTaxNumberFormat()
    {
        return number_format($this->price_without_tax, 2);
    }
}
