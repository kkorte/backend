<?php
namespace Hideyo\Backend\Repositories;

interface ProductCategoryRepositoryInterface
{

    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();

    public function ajaxSearchByTitle($query);

    public function selectAllByShopId($shopId);

    public function selectAllActiveByShopId($shopId);
    
    public function selectAllByShopIdAndRoot($shopId);

    public function selectOneByShopIdAndSlug($shopId, $slug, $imageTag = false);

    public function selectCategoriesByParentId($shopId, $parentId, $imageTag = false);

    public function selectRootCategories($shopId, $imageTag);
    
    public function find($id);

    public function entireTreeStructure($shopId);

    public function rebuild();
}
