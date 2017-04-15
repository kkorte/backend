<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_image';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['product_id', 'product_variation_id', 'file', 'extension', 'size', 'path', 'rank', 'tag', 'shop_id', 'modified_by_user_id',];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function product()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Product');
    }

    public function relatedProductAttributes()
    {
        return $this->belongsToMany('Hideyo\Backend\Models\ProductAttribute', 
            config()->get('hideyo.db_prefix').'product_attribute_image', 
            'product_image_id', 
            'product_attribute_id');
    }

    public function relatedAttributes()
    {
        return $this->belongsToMany('Hideyo\Backend\Models\Attribute', 
            config()->get('hideyo.db_prefix').'product_image_attribute', 
            'product_image_id', 
            'attribute_id');
    }
}
