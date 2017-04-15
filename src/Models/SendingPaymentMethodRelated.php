<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class SendingPaymentMethodRelated extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'sending_payment_method_related';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['email_order_subject', 'email_order_body', 'pdf_text', 'payment_text', 'payment_confirmed_text'];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function sendingMethod()
    {
        return $this->belongsTo('Hideyo\Backend\Models\SendingMethod');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('Hideyo\Backend\Models\PaymentMethod');
    }
}