<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeCombination extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_attribute_combination';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['product_attribute_id', 'attribute_id',  'modified_by_user_id'];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function attribute()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Attribute');
    }

    public function productAttribute()
    {
        return $this->belongsTo('Hideyo\Backend\Models\ProductAttribute');
    }
}
