<?php 

namespace Hideyo\Backend\Models;

use Hideyo\Backend\Models\BaseModel;

class ProductRelatedProduct extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_related_product';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['product_id', 'related_product_id'];

    public function product()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Product', 'product_id');
    }

    public function relatedProduct()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Product', 'related_product_id');
    }
}