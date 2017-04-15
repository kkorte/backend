<?php
namespace Hideyo\Backend\Repositories;

use Hideyo\Backend\Models\Redirect;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use File;
use Image;

class RedirectRepository implements RedirectRepositoryInterface
{

    protected $model;

    public function __construct(Redirect $model)
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
        $rules = array(
            'url' => 'required|unique_with:'.$this->model->getTable().', shop_id'
        );
        
        if ($id) {
            $rules['url'] = 'required|unique_with:'.$this->model->getTable().', shop_id, '.$id.' = id';
        }

        return $rules;
    }

  
    public function create(array $attributes)
    {
        $attributes['modified_by_user_id'] = null;
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }
        $this->model->fill($attributes);
        $this->model->save();
        
        return $this->model;
    }


    public function importCsv($results, $shopId)
    {
        foreach ($results as $row) {

            $attributes = $row->toArray();
            $attributes['shop_id'] = $shopId;
            $attributes['active'] = 0;
     
            $validator = \Validator::make($attributes, $this->rules());

            if (!$validator->fails()) {
                $redirect = new Redirect;

                $redirect->fill($attributes);
                $redirect->save();
            } else {

                $result = $this->model->where('url', '=', $attributes['url'])->get()->first();
                if ($result) {
                    $attributes['active'] = 0;
                    if($attributes['redirect_url']) {
                        $attributes['active'] = 1;
                    } 
                    $this->model = $this->find($result->id);
                    $this->updateEntity($attributes);
                }
            }
        }

        return true;
    }


    public function updateClicks($url)
    {
        $result = $this->model->where('url', '=', $url)->get()->first();
        if ($result) {
            $this->model = $this->find($result->id);
            return $this->updateEntity(array('clicks' => $result->clicks + 1));
        }
    }


    public function updateById(array $attributes, $id)
    {
        $attributes['shop_id'] = \Auth::guard('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules($id));

        if ($validator->fails()) {
            return $validator;
        }


        $this->model = $this->find($id);
        return $this->updateEntity($attributes);
    }

    public function updateEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            if (isset($attributes['logo'])) {
                \File::delete($this->model->logo_file_path);
                $destinationPath = storage_path() . "/app/files/".$this->model->id."/logo/";

                $filename =  str_replace(" ", "_", strtolower($attributes['logo']->getClientOriginalName()));
                $upload_success = $attributes['logo']->move($destinationPath, $filename);

                $attributes['logo_file_name'] = $filename;
                $attributes['logo_file_path'] = $upload_success->getRealPath();
            }


            $this->model->fill($attributes);
            $this->model->save();

            if (File::exists($this->model->logo_file_path)) {
                if (!File::exists(public_path() . "/files/".$this->model->id."/logo/")) {
                    File::makeDirectory(public_path() . "/files/".$this->model->id."/logo/", 0777, true);
                }

                if (!File::exists(public_path() . "/files/".$this->model->id."/logo/".$this->model->logo_file_name)) {
                    $image = Image::make($this->model->logo_file_path);
                    $image->interlace();
                    $image->save(public_path() . "/files/".$this->model->id."/logo/".$this->model->logo_file_name);
                }
            }
        }

        return $this->model;
    }

    public function destroy($id)
    {
        $this->model = $this->find($id);
        return $this->model->delete();
    }

    public function destroyByUrl($url)
    {
        $result = $this->model->where('url', '=', $url)->delete();
        return $result;
    }


    public function selectAll()
    {
        return $this->model->where('shop_id', '=', \Auth::guard('hideyobackend')->user()->selected_shop_id);
    }

    public function selectNewRedirects()
    {
        return \DB::table('number')
        ->leftJoin('user_number', 'number.id', '=', 'user_number.number_id')
        ->whereNull('user_number.number_id')
        ->select('number.id', 'number.number')
        ->get();
    }

    public function checkApiToken($token, $title)
    {
        $shop = $this->model->where('title', '=', $title)->get();

        if ($shop->isEmpty() == 1) {
            $headers = array('WWW-Authenticate' => 'Basic');
            return new Response('Invalid credentials.', 401, $headers);
        } else {
            return;
        }
    }

    public function checkByCompanyIdAndUrl($companyId, $shopUrl)
    {
        $result = $this->model->where('company_id', '=', $companyId)->where('url', '=', $shopUrl)->get()->first();

        if (isset($result->id)) {
            return $result->id;
        } else {
            return false;
        }
    }

    public function findByUrlAndActive($url)
    {
        $result = $this->model->where('url', '=', $url)->whereNotNull('redirect_url')->where('active', '=', 1)->get()->first();
        return $result;
    }

    public function findByUrl($url)
    {
        $result = $this->model->where('url', '=', $url)->get()->first();
        return $result;
    }
    


    public function findByCompanyIdAndUrl($companyId, $shopUrl)
    {
        $result = $this->model->where('company_id', '=', $companyId)->where('url', '=', $shopUrl)->get()->first();
        return $result;
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
