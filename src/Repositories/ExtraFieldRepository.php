<?php
namespace Hideyo\Backend\Repositories;

use Hideyo\Backend\Models\ExtraField;
use Hideyo\Backend\Models\ExtraFieldDefaultValue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExtraFieldRepository implements ExtraFieldRepositoryInterface
{

    protected $model;

    public function __construct(
        ExtraField $model, 
        ExtraFieldDefaultValue $modelValue)
    {
        $this->model = $model;
        $this->modelValue = $modelValue;
    }

    /**
     * The validation rules for the model.
     *
     * @param  integer  $id id attribute model    
     * @return array
     */
    private function rules($id = false)
    {
        $rules = array(
            'title' => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id'
        );
        
        if ($id) {
            $rules['title'] =   'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id, '.$id.' = id';
        }

        return $rules;
    }

    private function rulesValue($id = false)
    {
        if ($id) {
            return [
                'value' => 'required|unique_with:'.$this->modelValue->getTable().',extra_field_id,'.$id,
            ];
        } else {
            return [
                'value' => 'required|unique_with:'.$this->modelValue->getTable().',extra_field_id'
            ];
        }
    }


    public function create(array $attributes)
    {
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }
        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        $this->model->fill($attributes);
        $this->model->save();

        if (isset($attributes['categories'])) {
            $this->model->categories()->sync($attributes['categories']);
        }
        
        return $this->model;
    }

    public function createValue(array $attributes, $extraFieldId)
    {
        $attributes['extra_field_id'] = $extraFieldId;
        $validator = \Validator::make($attributes, $this->rulesValue());

        if ($validator->fails()) {
            return $validator;
        } else {
            $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
            $this->modelValue->fill($attributes);
            $this->modelValue->save();
            return $this->modelValue;
        }
    }


    public function updateById(array $attributes, $id)
    {
        $this->model = $this->find($id);
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules($id));

        if ($validator->fails()) {
            return $validator;
        }
        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        return $this->updateEntity($attributes);
    }

    public function updateEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->model->fill($attributes);


            if (isset($attributes['categories'])) {
                $this->model->categories()->sync($attributes['categories']);
            } else {
                $this->model->categories()->sync(array());
            }

            $this->model->save();
        }

        return $this->model;
    }


    public function updateValueById(array $attributes, $extraFieldId, $id)
    {
        $attributes['extra_field_id'] = $extraFieldId;
        $validator = \Validator::make($attributes, $this->rulesValue($id));

        if ($validator->fails()) {
            return $validator;
        } else {
            $this->modelValue = $this->findValue($id);
            $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
            return $this->updateValueEntity($attributes);
        }
    }

    public function updateValueEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->modelValue->fill($attributes);
            $this->modelValue->save();
        }

        return $this->modelValue;
    }


    public function destroy($id)
    {
        $this->model = $this->find($id);
        $this->model->save();

        return $this->model->delete();
    }

    public function destroyValue($id)
    {
        $this->modelValue = $this->findValue($id);
        $this->modelValue->save();

        return $this->modelValue->delete();
    }


    public function selectAll()
    {
        return $this->model->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }
    
    public function find($id)
    {
        return $this->model->find($id);
    }

    public function findValue($id)
    {
        return $this->modelValue->find($id);
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getValueModel()
    {
        return $this->modelValue;
    }

    public function selectAllByAllProductsAndProductCategoryId($productCategoryId)
    {
        return $this->model->select(config()->get('hideyo.db_prefix').'extra_field.*')
        ->leftJoin(config()->get('hideyo.db_prefix').'product_category_related_extra_field', config()->get('hideyo.db_prefix').'extra_field.id', '=', config()->get('hideyo.db_prefix').'product_category_related_extra_field.extra_field_id')
        
        ->where(function ($query) use ($productCategoryId) {

            $query->where('all_products', '=', 1)
            ->orWhereHas('categories', function ($query) use ($productCategoryId) {

                $query->where('product_category_id', '=', $productCategoryId);
            });
        })

        ->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }

    
}
