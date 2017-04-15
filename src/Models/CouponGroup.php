<?php 

namespace Hideyo\Backend\Models;

use Hideyo\Backend\Models\BaseModel;
use Cviebrock\EloquentSluggable\Sluggable;

class CouponGroup extends BaseModel
{
    use Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'coupon_group';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['id', 'title', 'shop_id'];




    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function coupon()
    {
        return $this->hasMany('Hideyo\Backend\Models\Coupon');
    }
}
