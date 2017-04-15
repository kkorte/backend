<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class ExtraFieldRelatedProductCategory extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'extra_field_related_product_category';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['extra_field_id', 'product_category_id'];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function extraField()
    {
        return $this->belongsTo('Hideyo\Backend\Models\ExtraField', 'extra_field_id');
    }

    public function relatedProductCategory()
    {
        return $this->belongsTo('Hideyo\Backend\Models\ProductCategory', 'product_category_id');
    }
}
