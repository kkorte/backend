<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceAddress extends Model
{
    protected $table = 'invoice_address';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['company', 'gender', 'initials', 'firstname', 'lastname', 'street', 'housenumber', 'housenumber_suffix', 'zipcode', 'city', 'country', 'invoice_id', 'modified_by_user_id'];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function invoice()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Invoice');
    }
}
