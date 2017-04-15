<?php
namespace Hideyo\Backend\Repositories;

interface ProductAmountSeriesRepositoryInterface
{

    public function create(array $attributes, $productId);

    public function updateById(array $attributes, $productId, $id);
    
    public function selectAll();

    public function selectAllActiveByShopId($shopId);

    public function selectOneByShopIdAndId($shopId, $id);
    
    public function selectOneById($id);
    
    public function find($id);
}
