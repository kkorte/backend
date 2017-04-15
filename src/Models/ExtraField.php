<?php 

namespace Hideyo\Backend\Models;

use Hideyo\Backend\Models\BaseModel;

class ExtraField extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'extra_field';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['type', 'default_value', 'title', 'all_products', 'filterable', 'product_category_id', 'shop_id', 'modified_by_user_id'];



    public function categories()
    {
        return $this->belongsToMany('Hideyo\Backend\Models\ProductCategory', config()->get('hideyo.db_prefix').'extra_field_related_product_category');
    }

    public function productCategory()
    {
        return $this->belongsTo('Hideyo\Backend\Models\ProductCategory');
    }

    public function values()
    {
        return $this->hasMany('Hideyo\Backend\Models\ExtraFieldDefaultValue');
    }
}
