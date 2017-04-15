<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\Attribute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
 
class AttributeRepository implements AttributeRepositoryInterface
{
    protected $model;

    public function __construct(Attribute $model)
    {
        $this->model = $model;
    }
  
    /**
     * The validation rules for the model.
     *
     * @param  integer  $id id attribute model    
     * @return array
     */
    private function rules($id = false)
    {
        if ($id) {
            return [
                'value' => 'required|unique_with:'.$this->model->getTable().',attribute_group_id,'.$id,
            ];
        } else {
            return [
                'value' => 'required|unique_with:'.$this->model->getTable().',attribute_group_id'
            ];
        }
    }

    public function create(array $attributes, $attributeGroupId)
    {
        $attributes['attribute_group_id'] = $attributeGroupId;
        $validator = Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        } else {
            $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
            $this->model->fill($attributes);
            $this->model->save();
            return $this->model;
        }
    }

    public function updateById(array $attributes, $attributeGroupId, $id)
    {
        $attributes['attribute_group_id'] = $attributeGroupId;
        $validator = Validator::make($attributes, $this->rules($id));

        if ($validator->fails()) {
            return $validator;
        } else {
            $this->model = $this->find($id);
            $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
            return $this->updateEntity($attributes);
        }
    }

    private function updateEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->model->fill($attributes);
            $this->model->save();
        }

        return $this->model;
    }

    public function destroy($id)
    {
        $this->model = $this->find($id);
        $this->model->save();

        return $this->model->delete();
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }
    
    public function find($id)
    {
        return $this->model->find($id);
    }

    public function getModel()
    {
        return $this->model;
    } 
}