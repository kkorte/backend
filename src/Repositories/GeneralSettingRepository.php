<?php
namespace Hideyo\Ecommerce\Backend\Repositories;
 
use Hideyo\Ecommerce\Backend\Models\GeneralSetting;
use Auth;
use Validator;
 
class GeneralSettingRepository implements GeneralSettingRepositoryInterface
{

    protected $model;

    public function __construct(GeneralSetting $model)
    {
        $this->model = $model;
    }

    /**
     * The validation rules for the model.
     *
     * @param  integer  $settingId id attribute model    
     * @return array
     */
    private function rules($settingId = false)
    {
        $rules = array(
            'name' => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id',
            'value' => 'required'
        );
        
        if ($settingId) {
            $rules['name'] =   $rules['name'].','.$settingId.' = id';
        }

        return $rules;
    }
  
    public function create(array $attributes)
    {
        $attributes['shop_id'] = Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }
        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
        $this->model->fill($attributes);
        $this->model->save();
        
        return $this->model;
    }

    public function updateById(array $attributes, $settingId)
    {
        $this->model = $this->find($settingId);
        $attributes['shop_id'] = Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules($settingId));

        if ($validator->fails()) {
            return $validator;
        }
        $attributes['modified_by_user_id'] = Auth::guard('hideyobackend')->user()->id;
        return $this->updateEntity($attributes);
    }

    private function updateEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->model->fill($attributes);
            $this->model->save();
        }

        return $this->model;
    }

    public function destroy($settingId)
    {
        $this->model = $this->find($settingId);
        $this->model->save();

        return $this->model->delete();
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }
    
    public function find($settingId)
    {
        return $this->model->find($settingId);
    }

    function selectOneByShopIdAndName($shopId, $name)
    {     
        $result = $this->model
        ->where('shop_id', '=', $shopId)->where('name', '=', $name)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }

    public function getModel()
    {
        return $this->model;
    } 
}