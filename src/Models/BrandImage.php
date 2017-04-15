<?php 

namespace Hideyo\Backend\Models;

use Hideyo\Backend\Models\BaseModel;

class BrandImage extends BaseModel
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


}
