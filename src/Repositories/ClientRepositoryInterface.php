<?php
namespace Hideyo\Ecommerce\Backend\Repositories;

interface ClientRepositoryInterface
{
    public function create(array $attributes);

    public function updateById(array $attributes, $id);
    
    public function selectAll();

    public function selectAllByBillClientAddress();
    
    public function find($id);

    public function validateLogin($email, $password, $shopId);

    public function validateRegister(array $attributes, $shopId);

    public function register(array $attributes, $shopId);

    public function selectOneByShopIdAndId($shopId, $id);

    public function selectOneById($id);

    public function setBillOrDeliveryAddress($shopId, $clientId, $addressId, $type);

    public function confirm($code, $email, $shopId);

    public function getConfirmationCodeByEmail($email, $shopId);

    public function validateConfirmationCodeByConfirmationCodeAndEmail($confirmationCode, $email, $shopId);

    public function resetPasswordByConfirmationCodeAndEmail(array $attributes, $shopId);

    public function activate($id);

    public function deactivate($id);
}
