<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class BrandImage extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'brand_image';

    protected $guarded =  array('file');

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['brand_id', 'file', 'extension', 'size', 'path', 'rank', 'tag', 'modified_by_user_id',];

    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        parent::__construct($attributes);
    }
}
