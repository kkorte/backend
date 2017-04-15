<?php
namespace Hideyo\Backend\Repositories;

interface UserLogRepositoryInterface
{
    public function create($type, $message, $user_id);

    public function updateById(array $attributes, $id);
    
    public function selectAll();
    
    public function find($id);
}
