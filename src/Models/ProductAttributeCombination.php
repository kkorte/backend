<?php 

namespace Hideyo\Ecommerce\Backend\Models;

use Hideyo\Ecommerce\Backend\Models\BaseModel;

class ProductAttributeCombination extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_attribute_combination';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['product_attribute_id', 'attribute_id',  'modified_by_user_id'];

    public function attribute()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Backend\Models\Attribute');
    }

    public function productAttribute()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Backend\Models\ProductAttribute');
    }
}
