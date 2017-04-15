<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductAmountOption extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_amount_option';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['product_id',  'amount', 'discount_type', 'discount_value', 'discount_start_date', 'discount_end_date', 'modified_by_user_id'];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function setDiscountValueAttribute($value)
    {
        $this->attributes['discount_value'] = null;

        if ($value) {
            $this->attributes['discount_value'] = $value;
        }
    }

    public function setDiscountStartDateAttribute($value)
    {
        $this->attributes['discount_start_date'] = null;

        if ($value) {
            $date = explode('/', $value);

            $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
            $this->attributes['discount_start_date'] = $value;
        }
    }

    public function getDiscountStartDateAttribute($value)
    {
        if ($value) {
            $date = explode('-', $value);
            return $date[2].'/'.$date[1].'/'.$date[0];
        }
        
        return null;
    }

    public function setDiscountEndDateAttribute($value)
    {
        $this->attributes['discount_end_date'] = null;

        if ($value) {
            $date = explode('/', $value);
            $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
            $this->attributes['discount_end_date'] = $value;
        }
    }

    public function getDiscountEndDateAttribute($value)
    {
        if ($value) {
            $date = explode('-', $value);
            return $date[2].'/'.$date[1].'/'.$date[0];
        }
        
        return null;
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = null;

        if ($value) {
            $this->attributes['amount'] = (int) $value;
        }
    }

    public function product()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Product');
    }
}
