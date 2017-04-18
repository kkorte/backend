<?php
namespace Hideyo\Ecommerce\Backend\Repositories;
 
use Hideyo\Ecommerce\Backend\Models\FaqItem;
use Hideyo\Ecommerce\Backend\Models\FaqItemGroup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
 
class FaqItemRepository implements FaqItemRepositoryInterface
{

    protected $model;

    public function __construct(FaqItem $model, FaqItemGroup $modelFaqItemGroup)
    {
        $this->model = $model;
        $this->modelFaqItemGroup = $modelFaqItemGroup;
    }

    /**
     * The validation rules for the model.
     *
     * @param  integer  $faqItemId id attribute model    
     * @return array
     */
    private function rules($faqItemId = false, $attributes = false)
    {
        if (isset($attributes['seo'])) {
            $rules = array(
                'meta_title'                 => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id'
            );
        } else {
            $rules = array(
                'question'                 => 'required|between:4,65|unique:'.$this->model->getTable().''
            );
            
            if ($faqItemId) {
                $rules['question'] =   'required|between:4,65|unique:'.$this->model->getTable().',question,'.$faqItemId;
            }
        }

        return $rules;
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

        if (isset($attributes['payment_methods'])) {
            $this->model->relatedPaymentMethods()->sync($attributes['payment_methods']);
        }
   
        return $this->model;
    }

    public function updateById(array $attributes, $faqItemId)
    {
        $validator = \Validator::make($attributes, $this->rules($faqItemId, $attributes));

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = \Auth::guard('hideyobackend')->user()->id;
        $this->model = $this->find($faqItemId);
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

    public function destroy($faqItemId)
    {
        $this->model = $this->find($faqItemId);
        $this->model->save();

        return $this->model->delete();
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }

    public function selectAllGroups()
    {
        return $this->modelFaqItemGroup->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->get();
    }

    function selectOneById($faqItemId)
    {
        $result = $this->model->with(array('relatedPaymentMethods'))->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id)->where('active', '=', 1)->where('id', '=', $faqItemId)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }

    function selectAllActiveByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->get();
    }
    
    public function find($faqItemId)
    {
        return $this->model->find($faqItemId);
    }

    public function getModel()
    {
        return $this->model;
    }
    
}
