<?php
namespace Hideyo\Backend\Repositories;
 
use UserLog;
 
class UserLogRepository implements UserLogRepositoryInterface
{

    protected $model;

    public function __construct(UserLog $model)
    {
        $this->model = $model;
    }
  
    public function create($type, $message, $user_id)
    {
        $this->model->message = $message;
        $this->model->type = $type;
        $this->model->user_id = $user_id;
        $this->model->save();
        
        return $this->model;
    }

    public function updateById(array $attributes, $id)
    {
        $this->model = $this->find($id);
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

    public function destroy($id)
    {
        $this->model = $this->find($id);
        $this->model->save();

        return $this->model->delete();
    }

    public function selectAll()
    {
        return $this->model->all();
    }
    
    public function find($id)
    {
        return $this->model->find($id);
    }
}
