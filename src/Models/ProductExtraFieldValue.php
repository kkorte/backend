<?php 

namespace Hideyo\Ecommerce\Backend\Models;

use Hideyo\Ecommerce\Backend\Models\BaseModel;

class ProductExtraFieldValue extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_extra_field_value';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['value', 'product_id', 'extra_field_id', 'extra_field_default_value_id', 'shop_id', 'modified_by_user_id'];

    public function extraField()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Backend\Models\ExtraField');
    }
    public function extraFieldDefaultValue()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Backend\Models\ExtraFieldDefaultValue');
    }

    public function product()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Backend\Models\Product');
    }
}
