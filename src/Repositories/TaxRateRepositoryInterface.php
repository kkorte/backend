<?php
namespace Hideyo\Ecommerce\Backend\Repositories;

interface TaxRateRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $taxRateId);
    
    public function selectAll();
    
    public function find($taxRateId);
}
