<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;

use Cviebrock\EloquentSluggable\Sluggable;

class Brand extends Model
{
    use Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'brand';

    protected $scoped = array('shop_id');

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['active',  'title', 'short_description', 'description', 'rank', 'meta_title', 'meta_description', 'meta_keywords', 'shop_id', 'modified_by_user_id'];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table; 
        parent::__construct($attributes);
    }

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function beforeValidate()
    {
        $this->sluggify();
    }

    public function products()
    {
        return $this->hasMany('Hideyo\Backend\Models\Product');
    }

    public function brandImages()
    {
        return $this->hasMany('Hideyo\Backend\Models\BrandImage');
    }

    public function shop()
    {
        return $this->belongsTo('Hideyo\Backend\Models\Shop');
    }
}
