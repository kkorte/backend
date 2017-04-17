<?php
namespace Hideyo\Ecommerce\Backend\Repositories;
 
use Hideyo\Ecommerce\Backend\Models\Shop;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use File;
use Image;
 
class ShopRepository implements ShopRepositoryInterface
{

    protected $model;

    public function __construct(Shop $model)
    {
        $this->model = $model;
    }
  
    public function create(array $attributes)
    {
        $this->model->slug = null;
        $this->model->fill($attributes);
        $this->model->save();
        
        if (isset($attributes['logo'])) {
            $destinationPath = storage_path() . "/app/files/".$this->model->id."/logo/";
            $filename =  str_replace(" ", "_", strtolower($attributes['logo']->getClientOriginalName()));
            $upload_success = $attributes['logo']->move($destinationPath, $filename);

            $attributes['logo_file_name'] = $filename;
            $attributes['logo_file_path'] = $upload_success->getRealPath();
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

    public function updateById(array $attributes, $shopId)
    {
        $this->model = $this->find($shopId);
        return $this->updateEntity($attributes);
    }

    private function updateEntity(array $attributes = array())
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


            $this->model->slug = null;
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

    public function destroy($shopId)
    {
        $this->model = $this->find($shopId);
        $this->model->save();

        return $this->model->delete();
    }


    public function selectAll()
    {
        return $this->model->get();
    }


    public function find($shopId)
    {
        return $this->model->find($shopId);
    }

    public function getModel()
    {
        return $this->model;
    }

}
