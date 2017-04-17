<?php 

namespace Hideyo\Ecommerce\Backend\Models;

use Hideyo\Ecommerce\Backend\Models\BaseModel;
use Cviebrock\EloquentSluggable\Sluggable;

class Shop extends BaseModel
{

    use Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'shop';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['active', 'email', 'wholesale', 'currency_code', 'title', 'url', 'description', 'meta_title', 'meta_description', 'meta_keywords', 'logo_file_name', 'logo_file_path'];

    protected $sluggable = array(
        'build_from'        => 'title',
        'save_to'           => 'slug',
        'on_update'         => true,
    );

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

    public function shops()
    {
        return $this->hasMany('Hideyo\Ecommerce\Backend\Models\Shop');
    }
    
    public function categories()
    {
        return $this->hasMany('Hideyo\Ecommerce\Backend\Models\ProductCategory');
    }

    public function products()
    {
        return $this->hasMany('Hideyo\Ecommerce\Backend\Models\Product');
    }

    public function setSquareThumbnailSizesAttribute($value = null)
    {
        $values  = explode(',', $value);

        $newValues = serialize($values);

        $this->attributes['square_thumbnail_sizes'] = $newValues;
    }

    public function getSquareThumbnailSizesAttribute($value = null)
    {
        if ($value) {
            $values = unserialize($value);

            $newValues  = implode(',', $values);

            return $newValues;
        }
    }

    public function setWidescreenThumbnailSizesAttribute($value = null)
    {
        $values  = explode(',', $value);

        $newValues = serialize($values);

        $this->attributes['widescreen_thumbnail_sizes'] = $newValues;
    }

    public function getWidescreenThumbnailSizesAttribute($value = null)
    {
        if ($value) {
            $values = unserialize($value);

            $newValues  = implode(',', $values);

            return $newValues;
        }
    }
}
