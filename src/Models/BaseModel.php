<?php 

namespace Hideyo\Backend\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    public function __construct(array $attributes = array())
    {
        $this->table = config()->get('hideyo.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    protected function getExistingSlugs($slug)
    {
        $config = $this->getSluggableConfig();
        $saveTo = $config['save_to'];
        $includeTrashed = $config['include_trashed'];

        $instance = new static;

        $query = $instance->where($saveTo, 'LIKE', $slug . '%');

        // @overriden - changed this to scope unique slugs per user
        $query = $query->where('shop_id', $this->shop_id);

        // include trashed models if required
        if ($includeTrashed && $this->usesSoftDeleting()) {
            $query = $query->withTrashed();
        }

        // get a list of all matching slugs
        $list = $query->pluck($saveTo, $this->getKeyName())->toArray();

        // Laravel 5.0/5.1 check
        return $list instanceof Collection ? $list->all() : $list;
    }
    

}