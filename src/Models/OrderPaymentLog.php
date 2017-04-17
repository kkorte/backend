<?php 

namespace Hideyo\Ecommerce\Backend\Models;

use Hideyo\Ecommerce\Backend\Models\BaseModel;

class OrderPaymentLog extends BaseModel
{
    protected $table = 'order_payment_log';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['order_id', 'type', 'log'];

    public function order()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Backend\Models\Order');
    }
}