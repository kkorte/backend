<?php
namespace Hideyo\Backend\Repositories;

interface AttributeGroupRepositoryInterface
{
    private function rules($id = false);

    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function updateEntity(array $attributes = array());

    public function destroy($id);

    public function selectAll();
    
    public function find($id);

    public function getModel();
}
