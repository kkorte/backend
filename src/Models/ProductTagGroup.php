<?php 

namespace Hideyo\Ecommerce\Backend\Models;

use Hideyo\Ecommerce\Backend\Models\BaseModel;

class ProductTagGroup extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_tag_group';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['tag', 'active', 'shop_id'];

    public function relatedProducts()
    {
        return $this->belongsToMany('Hideyo\Ecommerce\Backend\Models\Product', config()->get('hideyo.db_prefix').'product_tag_group_related_product');
    }
}