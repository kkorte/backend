<?php
namespace Hideyo\Backend\Repositories; 

interface ProductRepositoryInterface
{

    public function create(array $attributes);

    public function createCopy(array $attributes, $productId);


    public function updateById(array $attributes, $id);
    
    public function selectAll();

    function selectAllByShopId($shopId);

    public function selectAllExport();

    public function selectAllByProductParentId($productParentId);
    
    public function find($id);

    public function selectOneByShopIdAndSlug($shopId, $slug);

    public function selectOneByShopIdAndId($shopId, $id);

    public function selectOneById($id);

    public function selectAllByShopIdAndProductCategoryId($shopId, $productCategoryId, $filters);
    
    public function getModel();

    public function reduceAmounts($products);

    public function changeActive($productId);

    public function changeAmount($productId, $amount);


    function selectByLimitAndOrderBy($shopId, $limit, $orderBy);
}
