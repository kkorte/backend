<?php
/**
 *
 */

namespace Hideyo\Services;

class AssembleOrder
{

    protected $_products = array();
    protected $_product_total_ex_tax = 0;
    protected $_product_total_inc_tax = 0;
    protected $_product_total_tax_value = 0;
    protected $_sending_method = array();
    protected $_sending_method_id = 0;
    protected $_client_id = null;
    protected $_client_bill_address_id = null;
    protected $_client_delivery_address_id = null;
    protected $_sending_method_cost_ex_tax = 0;
    protected $_sending_method_cost_inc_tax = 0;
    protected $_payment_methods = array();
    protected $_payment_method = array();
    protected $_payment_method_id = 0;
    protected $_payment_method_cost_ex_tax = 0;
    protected $_payment_method_cost_inc_tax = 0;
    protected $_totals = array();
    protected $_total_ex_tax = 0;
    protected $_total_inc_tax = 0;

    /**
     * [getInstance description]
     * @return [type] [description]
     */
    public static function getInstance()
    {
        $shopId = \Auth::user()->selected_shop_id;
        $nsWebshop = \Session::get('assemble-order-'.$shopId);

        if (!$nsWebshop) {
            \Session::put('assemble-order-'.$shopId, new self);
            $nsWebshop = \Session::get('assemble-order-'.$shopId);
        }

        return \Session::get('assemble-order-'.$shopId);
    }

    /**
     * [destroyInstance description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public static function destroyInstance()
    {
        $shopId = \Auth::user()->selected_shop_id;
        \Session::forget('assemble-order'.$shopId);
    }


    public function addClient($clientId)
    {
         $this->_client_id = $clientId;
         $this->_client_bill_address_id = null;
         $this->_client_delivery_address_id = null;
    }

    public function addClientBillAddress($addressId)
    {
         $this->_client_bill_address_id = $addressId;
    }

    public function addClientDeliveryAddress($addressId)
    {
         $this->_client_delivery_address_id = $addressId;
    }

    /**
     * [add description]
     * @param [type] $product [description]
     * @param [type] $amount  [description]
     */
    public function add($product, $amount)
    {
        $product_id = $product['id'];

        if (isset($product['product_combination_id'])) {
            $product_id = $product['id'].'-'.$product['product_combination_id'];
        }

        if (isset($this->_products[$product_id])) {
            $this->_products[$product_id]['amount'] = $this->_products[$product_id]['cart']['count'] + $amount;
            $this->_products[$product_id]['cart']['count'] = $this->_products[$product_id]['cart']['count'] + $amount;
        } else {
            $this->_products[$product_id] = $product;
            $this->_products[$product_id]['id'] = $product_id;
            $this->_products[$product_id]['product_id'] = $product['id'];
            $this->_products[$product_id]['amount'] = $amount;
            $this->_products[$product_id]['cart']['count'] = $amount;
        }

        if (isset($product['product_combination_id'])) {
            $this->_products[$product_id]['product_combination_id'] = $product['product_combination_id'];
        }

        if (isset($product['product_combination_title'])) {
            $this->_products[$product_id]['product_combination_title'] = $product['product_combination_title'];
        }

        if (isset($product['combinations'])) {
            $this->_products[$product_id]['combinations'] = $product['combinations'];
        }

        $this->_products[$product_id]['cart']['price_details'] = $product['price_details'];

        return array('result' => true, 'product' => $this->getProduct($product_id), 'pricetotal' => $this->getTotalincTax(), 'producttotal' => count($this->_products));
    }


    public function change($product, $amount)
    {
        $product_id = $product['id'];

        if ($amount == 0) {
            unset($this->_products[$product_id]);
        } else {
            $this->_products[$product_id] = $product;
            $this->_products[$product_id]['amount'] = $amount;
            $this->_products[$product_id]['cart']['count'] = $amount;
            $this->_products[$product_id]['cart']['price_details'] = $product['price_details'];
        }

        return $this;
    }


    /**
     * [remove product from cart session]
     * @param  [type] $product [product array]
     * @return [type]          [true or false]
     */
    public function remove($product)
    {
        $product_id = $product['id'];

        if (isset($this->_products[$product_id])) {
            unset($this->_products[$product_id]);
            return true;
        }

        return false;
    }

    public function removeProductAttribute($product, $productCombinationId)
    {


        $product_id = $product['id'].'-'.$productCombinationId;

        if (isset($this->_products[$product_id])) {
            unset($this->_products[$product_id]);
            return true;
        }

        return false;
    }

    public function updateSendingMethod($sendingMethod)
    {
        $this->_payment_methods = array();
        $this->_payment_method_cost_ex_tax = 0;
        $this->_payment_method_cost_inc_tax = 0;

        if (isset($sendingMethod['id'])) {
            $this->_sending_method = $sendingMethod;
            $this->_sending_method_id = $sendingMethod['id'];
            if (isset($sendingMethod['related_payment_methods_list'])) {
                $this->_payment_methods = $sendingMethod['related_payment_methods_list'];
            }

            $this->_sending_method_cost_ex_tax = $sendingMethod['price_details']['orginal_price_ex_tax'];
            $this->_sending_method_cost_inc_tax = $sendingMethod['price_details']['orginal_price_inc_tax'];
        } else {
            $this->_sending_method = array();
            $this->_sending_method_id = 0;
            $this->_payment_methods = array();
            $this->_sending_method_cost_ex_tax = 0;
            $this->_sending_method_cost_inc_tax = 0;
            $this->_payment_method = array();
            $this->_payment_method_id = 0;
        }
    }

    public function updatePaymentMethod($paymentMethod)
    {
        if (isset($paymentMethod['id'])) {
            $this->_payment_method = $paymentMethod;
            $this->_payment_method_id = $paymentMethod['id'];
            if (isset($paymentMethod['related_payment_methods_list'])) {
                $this->_payment_methods = $paymentMethod['related_payment_methods_list'];
            }

            $this->_payment_method_cost_ex_tax = $paymentMethod['price_details']['orginal_price_ex_tax'];
            $this->_payment_method_cost_inc_tax = $paymentMethod['price_details']['orginal_price_inc_tax'];
        } else {
            $this->_payment_method = array();
            $this->_payment_method_id = 0;
            $this->_payment_methods = array();
            $this->_payment_method_cost_ex_tax = 0;
            $this->_payment_method_cost_inc_tax = 0;
        }
    }

    public function paymentMethod()
    {
        return $this->_payment_method;
    }

    public function sendingMethod()
    {
        return $this->_sending_method;
    }

    public function updateAmount($product, $amount)
    {
        $product_id = $product['id'];

        if ($amount == 0) {
            unset($this->_products[$product_id]);
            return true;
        } else {
            if (isset($this->_products[$product_id])) {
                $this->_products[$product_id] = $product;
                $this->_products[$product_id]['cart']['count'] = $amount;
            }

            $this->_products[$product_id]['cart']['price_details'] = $product['price_details'];
            return $this->_products[$product_id];
        }
    }

    public function changeProductCombination($oldProduct, $newProduct)
    {

        if (isset($this->_products[$oldProduct['id']])) {
            $count = $this->_products[$oldProduct['id']]['cart']['count'];

            unset($this->_products[$oldProduct['id']]);

            $this->add($newProduct, $count);
            return array('result' => true, 'product' => $this->getProduct($newProduct['id']), 'pricetotal' => $this->getTotalincTax(), 'producttotal' => count($this->_products));
        }
    }


    

    public function getProduct($productId)
    {
        if (isset($this->_products[$productId])) {
            return $this->_products[$productId];
        } else {
            return false;
        }
    }

    public function products()
    {
        return $this->_products;
    }

    public function paymentMethods()
    {
        return $this->_payment_methods;
    }

    public function totals()
    {

        if ($this->_products) {
            $this->_totals['sub_total_inc_tax'] = number_format($this->_product_total_inc_tax, 2, '.', '');
            $this->_totals['sub_total_ex_tax'] = number_format($this->_product_total_ex_tax, 2, '.', '');
            $this->_totals['sub_total_tax_value'] = number_format($this->_product_total_tax_value, 2, '.', '');
            $this->_totals['sending_method'] = $this->_sending_method;
            $this->_totals['sending_method_id'] = $this->_sending_method_id;
            $this->_totals['client_id'] = $this->_client_id;
            $this->_totals['client_bill_address_id'] = $this->_client_bill_address_id;
            $this->_totals['client_delivery_address_id'] = $this->_client_delivery_address_id;
            $this->_totals['sending_method_cost_ex_tax'] = $this->_sending_method_cost_ex_tax;
            $this->_totals['sending_method_cost_inc_tax'] = $this->_sending_method_cost_inc_tax;

            $this->_totals['payment_method'] = $this->_payment_method;
            $this->_totals['payment_method_id'] = $this->_payment_method_id;
            $this->_totals['payment_method_cost_ex_tax'] = $this->_payment_method_cost_ex_tax;
            $this->_totals['payment_method_cost_inc_tax'] = $this->_payment_method_cost_inc_tax;
            $this->_totals['total_ex_tax'] = $this->_total_ex_tax;
            $this->_totals['total_inc_tax'] = $this->_total_inc_tax;
            $this->_totals['total_tax'] = number_format($this->_total_inc_tax - $this->_total_ex_tax, 2, '.', '');
            $this->_totals['producttotal'] = count($this->_products);
        } else {
            $this->_totals = array();
        }
        return $this->_totals;
    }

    protected function reset()
    {
        $this->_product_total_ex_tax = 0;
        $this->_product_total_inc_tax = 0;
        $this->_product_total_tax_value = 0;
    }

    public function summary()
    {

        $this->reset();

        if ($this->_products) {
            foreach ($this->_products as $product) {
                $this->_products[$product['id']]['cart']['tax_type_id'] = 2;

                if ($product['cart']['price_details']['discount_price_inc']) {
                    $this->_products[$product['id']]['cart']['total_price_ex_tax'] = number_format($product['cart']['count'] * $product['cart']['price_details']['discount_price_ex'], 2, '.', '');
                    $this->_products[$product['id']]['cart']['total_price_inc_tax'] = number_format($product['cart']['count'] * $product['cart']['price_details']['discount_price_inc'], 2, '.', '');
                    $this->_products[$product['id']]['total_price_with_tax'] = number_format($product['cart']['count'] * $product['cart']['price_details']['discount_price_inc'], 2, '.', '');
                    $this->_products[$product['id']]['total_price_without_tax'] = number_format($product['cart']['count'] * $product['cart']['price_details']['discount_price_ex'], 2, '.', '');
                    $this->_products[$product['id']]['price_with_tax'] = $product['cart']['price_details']['discount_price_inc'];
                    $this->_products[$product['id']]['price_without_tax'] = $product['cart']['price_details']['discount_price_ex'];
                } else {
                    $this->_products[$product['id']]['cart']['total_price_ex_tax'] = number_format($product['cart']['count'] * $product['cart']['price_details']['orginal_price_ex_tax'], 2, '.', '');
                    $this->_products[$product['id']]['cart']['total_price_inc_tax'] = number_format($product['cart']['count'] * $product['cart']['price_details']['orginal_price_inc_tax'], 2, '.', '');
                    $this->_products[$product['id']]['total_price_with_tax'] = number_format($product['cart']['count'] * $product['cart']['price_details']['orginal_price_inc_tax'], 2, '.', '');
                    $this->_products[$product['id']]['total_price_without_tax'] = number_format($product['cart']['count'] * $product['cart']['price_details']['orginal_price_ex_tax'], 2, '.', '');
                    $this->_products[$product['id']]['price_with_tax'] = $product['cart']['price_details']['orginal_price_inc_tax'];
                    $this->_products[$product['id']]['price_without_tax'] = $product['cart']['price_details']['orginal_price_ex_tax'];
                }
                
                $this->_products[$product['id']]['tax_rate'] = $product['cart']['price_details']['tax_rate'];
                $this->_products[$product['id']]['cart']['tax_rate'] = $product['cart']['price_details']['tax_rate'];
                $this->_products[$product['id']]['cart']['tax_value'] = number_format($product['cart']['count'] * $product['cart']['price_details']['tax_value'], 2, '.', '');
                $this->addProductTotalTaxValue($this->_products[$product['id']]['cart']['tax_value']);
                $this->addProductTotalExTax($this->_products[$product['id']]['cart']['total_price_ex_tax']);
                $this->addProductTotalIncTax($this->_products[$product['id']]['cart']['total_price_inc_tax']);
            }
            $this->getTotalexTax();
            $this->getTotalincTax();
            return $this;
        } else {
            unset($this->_product_total_ex_tax);
            unset($this->_product_total_inc_tax);

            return false;
        }
    }

    public function addProductTotalTaxValue($total)
    {
        return $this->_product_total_tax_value += $total;
    }

    public function addProductTotalExTax($total)
    {
        return $this->_product_total_ex_tax += $total;
    }

    public function addProductTotalIncTax($total)
    {
        return $this->_product_total_inc_tax += $total;
    }

    public function getTotalexTax()
    {
        $this->_total_ex_tax = $this->_product_total_ex_tax;
        $this->_total_ex_tax += $this->_sending_method_cost_ex_tax;
        $this->_total_ex_tax += $this->_payment_method_cost_ex_tax;

        $this->_total_ex_tax = number_format($this->_total_ex_tax, 2, '.', '');
        ;
        return $this;
    }

    public function getTotalincTax()
    {
        $this->_total_inc_tax = $this->_product_total_inc_tax;
        $this->_total_inc_tax += $this->_sending_method_cost_inc_tax;
        $this->_total_inc_tax += $this->_payment_method_cost_inc_tax;

        $this->_total_inc_tax = number_format($this->_total_inc_tax, 2, '.', '');
        ;
        return $this;
    }
}
