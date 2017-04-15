<?php 

namespace Hideyo\Backend\Models;

use Hideyo\Backend\Models\BaseModel;

class ClientAddress extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'client_address';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['company', 'gender', 'initials', 'firstname', 'lastname', 'street', 'housenumber', 'housenumber_suffix', 'zipcode', 'city', 'country', 'phone', 'client_id', 'modified_by_user_id'];



    public function client()
    {
        return $this->belongsTo('Client');
    }

    public function clientDeliveryAddress()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Client', 'id', 'delivery_client_address_id');
    }

    public function clientBillAddress()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Client', 'id', 'bill_client_address_id');
    }
}
