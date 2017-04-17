<?php 

namespace Hideyo\Ecommerce\Backend\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Baum\Node;

class ProductCategory extends Node
{
    use Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_category';

    protected $scoped = array('shop_id');

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['active', 'parent_id', 'title', 'product_category_highlight_title', 'product_overview_title', 'product_overview_description', 'short_description', 'description', 'meta_title', 'meta_description', 'meta_keywords', 'slug', 'shop_id', 'redirect_product_category_id', 'modified_by_user_id'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;  
        parent::__construct($attributes);
    }

    public function shop()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Backend\Models\Shop');
    }

    public static function boot()
    {
        parent::boot();

        static::moving(function ($node) {
          // YOUR CODE HERE
        });

        static::moved(function ($node) {
          // YOUR CODE HERE
        });
    }

    public function refProductCategory()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Backend\Models\ProductCategory', config()->get('hideyo.db_prefix').'redirect_product_category_id');
    }

    public function productCategoryImages()
    {
        return $this->hasMany('Hideyo\Ecommerce\Backend\Models\ProductCategoryImage');
    }

    public function productCategoryHighlightProduct()
    {
        return $this->belongsToMany('Hideyo\Ecommerce\Backend\Models\Product', config()->get('hideyo.db_prefix').'product_category_highlight_product', 'product_category_id', 'product_id');
    }

    public function productCategoryHighlightProductActive()
    {
        return $this->belongsToMany('Hideyo\Ecommerce\Backend\Models\Product', config()->get('hideyo.db_prefix').'product_category_highlight_product', 'product_category_id', 'product_id')->where('active', '=', 1);
    }

    public function products()
    {
        return $this->hasMany('Hideyo\Ecommerce\Backend\Models\Product');
    }
}
