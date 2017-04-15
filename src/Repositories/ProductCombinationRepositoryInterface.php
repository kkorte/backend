<?php
namespace Hideyo\Backend\Repositories;

interface ProductCombinationRepositoryInterface
{
    public function create(array $attributes, $productParentId);
    
    public function updateById(array $attributes, $productId, $id);
    
    public function destroy($id);

    public function selectAllByProductId($productId);

    public function selectAllByShopIdAndProductId($shopId, $productId);

    public function selectAll();

    public function selectOneByShopIdAndSlug($shopId, $slug);

    public function selectOneByShopIdAndId($shopId, $id);

    public function find($id);

    public function getModel();

    public function reduceAmounts($products);
}
