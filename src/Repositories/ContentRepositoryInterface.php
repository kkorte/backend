<?php
namespace Hideyo\Ecommerce\Backend\Repositories;

interface ContentRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();

    public function selectAllActiveByShopId($shopId);

    public function selectOneByShopIdAndId($shopId, $id);
    
    public function selectOneById($id);
    
    public function find($id);
}
