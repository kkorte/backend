<?php
namespace Hideyo\Backend\Repositories;

interface ShopRepositoryInterface
{

    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();
    
    public function selectNewShops();
    
    public function find($id);

    public function checkApiToken($token, $title);

    public function checkByCompanyIdAndUrl($companyId, $shopUrl);

    public function checkByUrl($shopUrl);

    public function findByCompanyIdAndUrl($companyId, $shopUrl);
}
