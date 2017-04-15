<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeGroup extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'attribute_group';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['type', 'default_value', 'title', 'filter', 'shop_id', 'modified_by_user_id'];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        
        parent::__construct($attributes);
    }

    public function categories()
    {
        return $this->belongsToMany('ProductCategory', 'product_category_related_extra_field');
    }

    public function attributes()
    {
        return $this->hasMany('Hideyo\Backend\Models\Attribute');
    }
}
