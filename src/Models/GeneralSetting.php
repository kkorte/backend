<?php 

namespace Hideyo\Backend\Models;

use Hideyo\Backend\Models\BaseModel;

class GeneralSetting extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'general_setting';

	protected $fillable = ['id', 'name', 'value', 'text_value', 'shop_id'];
}
