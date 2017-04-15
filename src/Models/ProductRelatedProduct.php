<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRelatedProduct extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_related_product';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['product_id', 'related_product_id'];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function product()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Product', 'product_id');
    }

    public function relatedProduct()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Product', 'related_product_id');
    }
}
