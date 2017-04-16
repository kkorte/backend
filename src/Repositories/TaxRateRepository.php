<?php
namespace Hideyo\Backend\Repositories;
 
use Hideyo\Backend\Models\TaxRate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use Auth;
 
class TaxRateRepository implements TaxRateRepositoryInterface
{

    protected $model;

    public function __construct(TaxRate $model)
    {
        $this->model = $model;
    }

    /**
     * The validation rules for the model.
     *
     * @param  integer  $taxRateId id attribute model    
     * @return array
     */
    private function rules($taxRateId = false)
    {
        $rules = array(
            'title' => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id'

        );
        
        if ($taxRateId) {
            $rules['title'] =   'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id, '.$taxRateId.' = id';
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

    public function updateById(array $attributes, $taxRateId)
    {
        $this->model = $this->find($taxRateId);
        $attributes['shop_id'] = Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules($taxRateId));

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

    public function destroy($taxRateId)
    {
        $this->model = $this->find($taxRateId);
        $this->model->save();

        return $this->model->delete();
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }

    public function getModel() {
        return $this->model;
    }

    
    public function find($taxRateId)
    {
        return $this->model->find($taxRateId);
    }
}
