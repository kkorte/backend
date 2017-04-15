<?php 

namespace Hideyo\Backend\Models;

use Hideyo\Backend\Models\BaseModel;

class InvoiceAddress extends BaseModel
{
    protected $table = 'invoice_address';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['company', 'gender', 'initials', 'firstname', 'lastname', 'street', 'housenumber', 'housenumber_suffix', 'zipcode', 'city', 'country', 'invoice_id', 'modified_by_user_id'];



    public function invoice()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Invoice');
    }
}
