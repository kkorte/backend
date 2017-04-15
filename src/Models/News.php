<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Carbon\Carbon;

class News extends Model
{
    use Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'news';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['id', 'news_group_id', 'title', 'short_description', 'content', 'published_at', 'meta_title', 'meta_description', 'meta_keywords', 'shop_id'];

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
    
    public function setPublishedAtAttribute($value)
    {
        $this->attributes['published_at'] = null;

        if ($value) {
            $date = explode('/', $value);
            $this->attributes['published_at'] = $value;

            if (isset($date[2])) {
                $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
                $this->attributes['published_at'] = $value;
            }
        }
    }

    public function getPublishedAtAttribute($value)
    {
        if ($value) {
            $date = explode('-', $value);
            return $date[2].'/'.$date[1].'/'.$date[0];
        }
        
        return null;
    }

    public function newsImages()
    {
        return $this->hasMany('Hideyo\Backend\Models\NewsImage');
    }

    public function newsGroup()
    {
        return $this->belongsTo('Hideyo\Backend\Models\NewsGroup');
    }
}
