<?php 

namespace Hideyo\Backend\Models;

use LaravelBook\Ardent\Ardent;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ProductSubProductCategory extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_sub_product_category';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['category_id', 'product_id'];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function shop()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Shop');
    }

    public function product()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Product');
    }

    public function productCategory()
    {
        return $this->belongsTo('Hideyo\Backend\Models\ProductCategory');
    }
}
