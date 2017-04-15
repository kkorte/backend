<?php
namespace Hideyo\Backend\Repositories;

interface NewsRepositoryInterface
{

    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();


    
    public function find($id);

    
    public function getModel();

}
