<?php
namespace Hideyo\Ecommerce\Backend\Repositories;

interface LanguageRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();
    
    public function find($id);

    public function getModel();
}
