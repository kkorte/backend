<?php
namespace Hideyo\Backend\Repositories;

interface AttributeRepositoryInterface
{
    private function rules($id = false);

    public function create(array $attributes, $attributeGroupId);

    public function updateById(array $attributes, $attributeGroupId, $id);

    public function updateEntity(array $attributes = array());

    public function destroy($id);

    public function selectAll();
    
    public function find($id);

    public function getModel();
}
