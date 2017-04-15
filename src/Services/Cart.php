<?php

namespace Hideyo\Services;

class Cart
{

    /**
     * Service: Cart
     * Note: please keep logic in services or repositories. Put logic not in models
     * @author     Matthijs Neijenhuijs <matthijs@hideyo.io>
     * @copyright  DutchBridge - dont share/steel!
     */

    protected $_products = array();
    protected $_product_total_ex_tax = 0;
    protected $_product_total_inc_tax = 0;
    protected $_product_total_tax_value = 0;
    protected $_sending_method = array();
    protected $_sending_method_id = 0;
    protected $_sending_method_cost_ex_tax = 0;
    protected $_sending_method_cost_inc_tax = 0;
    protected $_payment_methods = array();
    protected $_payment_method = array();
    protected $_payment_method_id = 0;
    protected $_payment_method_cost_ex_tax = 0;
    protected $_payment_method_cost_inc_tax = 0;
    protected $_present = array();
    protected $_coupon = array();
    protected $_coupon_id = 0;
    protected $_coupon_code = null;
    protected $_discount = 0;
    protected $_discount_ex = 0;
    protected $_discount_tax = 0;
    protected $_totals = array();
    protected $_total_ex_tax = 0;
    protected $_total_inc_tax = 0;

    /**
     * [getInstance description]
     * @return [type] [description]
     */
    public static function getInstance()
    {

        $nsWebshop = \Session::get('cart');

        if (!$nsWebshop) {
            \Session::put('cart', new self);
            $nsWebshop = \Session::get('cart');
        }

        return \Session::get('cart');
    }

    /**
     * [destroyInstance description]
     * @param  [type] $type [description]
     * @return [type]       [description]
     */
    public static function destroyInstance()
    {
        \Session::forget('cart');
    }

    public function addPresent($input)
    {
        $this->_present = $input;
        $this->_present['tax_rate'] = 21.0000;
        $this->_present['cost_inc_tax'] = 1.5000;
        $this->_present['cost_ex_tax'] = 1.2397;
        $this->_present['cost_inc_tax_number_format'] = number_format($this->_present['cost_inc_tax'], 2, '.', '');
        $this->_present['cost_ex_tax_number_format'] = number_format($this->_present['cost_ex_tax'], 2, '.', '');
    }

    public function deletePresent()
    {
        $this->_present = array();
    }



    /**
     * [add description]
     * @param [type] $product [description]
     * @param [type] $amount  [description]
     */
    public function add($product, $amount)
    {
        $product_id = $product['id'];
        if($product['amount'] > 0) {

            if (isset($product['product_combination_id'])) {
                $product_id = $product['id'].'-'.$product['product_combination_id'];
            }

            if (isset($this->_products[$product_id])) {
                if ($product['product_amount_series']) {
                    if (in_array($amount, $product['product_amount_series_range'])) {
                        if (($amount + $this->_products[$product_id]['cart']['count']) <= end($product['product_amount_series_range'])) {
                            $this->_products[$product_id]['amount'] = $this->_products[$product_id]['cart']['count'] + $amount;
                            $this->_products[$product_id]['cart']['count'] = $this->_products[$product_id]['cart']['count'] + $amount;
                        } else {
                            $this->_products[$product_id]['amount'] = $amount;
                            $this->_products[$product_id]['cart']['count'] = $amount;
                        }
                    } else {

                        if(($amount + $this->_products[$product_id]['amount']) >= $product['amount']) {

                            $this->_products[$product_id]['amount'] = $product['amount'];
                            $this->_products[$product_id]['cart']['count'] = $product['amount'];

                        } else {
                            $this->_products[$product_id]['amount'] = $amount;
                            $this->_products[$product_id]['cart']['count'] = $amount;
                        }
                    }


                    $this->_products[$product_id]['product_amount_series'] = true;
                    $this->_products[$product_id]['product_amount_series_range'] = $product['product_amount_series_range'];
                } else {

                   if(($amount + $this->_products[$product_id]['amount']) >= $product['amount']) {

                        $this->_products[$product_id]['amount'] = $product['amount'];
                        $this->_products[$product_id]['cart']['count'] = $product['amount'];

                    } else {
                        $this->_products[$product_id]['amount'] = $this->_products[$product_id]['cart']['count'] + $amount;
                        $this->_products[$product_id]['cart']['count'] = $this->_products[$product_id]['cart']['count'] + $amount;
                    }

                    $this->_products[$product_id]['product_amount_series'] = false;
                }
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
            $this->_products[$product_id]['cart']['price_details'] = $product['price_details'];

            return array('result' => true, 'product' => $this->getProduct($product_id), 'pricetotal' => $this->getTotalincTax(), 'producttotal' => count($this->_products));
        }
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

    public function setCoupon($coupon, $inputCode)
    {
        if ($coupon['id']) {
            $this->_coupon = $coupon;
            $this->_coupon_id = $coupon->id;
            $this->_coupon_code = $coupon->code;
        } else {
            $this->_coupon = array();
            $this->_coupon_id = 0;
            $this->_coupon_code = $inputCode;
        }
    }

    public function updateSendingMethod($sendingMethod)
    {
        if (isset($sendingMethod['id'])) {
            $sendingMethod['no_price_from_number_format'] = number_format($sendingMethod['no_price_from'], 2, '.', '');
        
            $this->_sending_method = $sendingMethod;
            $this->_sending_method_id = $sendingMethod['id'];
            if (isset($sendingMethod['related_payment_methods_list'])) {
                $this->_payment_methods = $sendingMethod['related_payment_methods_list'];
            }

            if ($sendingMethod['wholesale']) {
                $freeSending = ( $sendingMethod['no_price_from'] - $this->_product_total_ex_tax);
            } else {
                $freeSending = ( $sendingMethod['no_price_from'] - $this->_product_total_inc_tax);
            }

            if ($freeSending > 0) {
                $this->_sending_method_cost_ex_tax = $sendingMethod['price_details']['orginal_price_ex_tax'];
                $this->_sending_method_cost_inc_tax = $sendingMethod['price_details']['orginal_price_inc_tax'];
            } else {
                $this->_sending_method_cost_ex_tax = 0;
                $this->_sending_method_cost_inc_tax = 0;
            }
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

            if ($paymentMethod['wholesale']) {
                $freeSending = ( $paymentMethod['no_price_from'] - $this->_product_total_ex_tax);
            } else {
                $freeSending = ( $paymentMethod['no_price_from'] - $this->_product_total_inc_tax);
            }


            if ($freeSending > 0) {
                $this->_payment_method_cost_ex_tax = $paymentMethod['price_details']['orginal_price_ex_tax'];
                $this->_payment_method_cost_inc_tax = $paymentMethod['price_details']['orginal_price_inc_tax'];
            } else {
                $this->_payment_method_cost_ex_tax = 0;
                $this->_payment_method_cost_inc_tax = 0;
            }
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

        if ($amount >= 0) {
            $product_id = $product['id'];

            if ($amount == 0) {
                unset($this->_products[$product_id]);
                return true;
            } else {

                if($product['amount'] > 0) {

                    if (isset($this->_products[$product_id])) {
                        $this->_products[$product_id] = $product;
                        
                        if($amount >= $product['amount']) {

                            $this->_products[$product_id]['cart']['count'] = $product['amount'];
                            $this->_products[$product_id]['amount'] = $product['amount'];
                            $this->_products[$product_id]['amount_na'] = true;

                        } else {

                            $this->_products[$product_id]['cart']['count'] = $amount;
                            $this->_products[$product_id]['amount'] = $amount;
                            $this->_products[$product_id]['amount_na'] = false;
                        }
                    } else {
                        return false;
                    }

                    $this->_products[$product_id]['cart']['price_details'] = $product['price_details'];
                    return $this->_products[$product_id];


                } else {
                    unset($this->_products[$product_id]);
                    return true;   
                }


            }
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
            $this->_totals['sub_total_inc_tax'] = $this->_product_total_inc_tax + $this->_discount;
            $this->_totals['sub_total_ex_tax'] = $this->_product_total_ex_tax  + $this->_discount_ex;
            $this->_totals['sub_total_tax_value'] = $this->_product_total_tax_value;


            $this->_totals['sub_total_inc_tax_number_format'] = number_format($this->_product_total_inc_tax + $this->_discount, 2, '.', '');
            $this->_totals['sub_total_ex_tax_number_format'] = number_format($this->_product_total_ex_tax  + $this->_discount_ex, 2, '.', '');
            $this->_totals['sub_total_tax_value_number_format'] = number_format($this->_product_total_tax_value, 2, '.', '');

            $this->_totals['sending_method'] = $this->_sending_method;
            $this->_totals['sending_method_id'] = $this->_sending_method_id;
            $this->_totals['sending_method_cost_ex_tax'] = $this->_sending_method_cost_ex_tax;
            $this->_totals['sending_method_cost_inc_tax'] = $this->_sending_method_cost_inc_tax;

            $this->_totals['sending_method_cost_ex_tax_number_format'] = number_format($this->_sending_method_cost_ex_tax, 2, '.', '');
            $this->_totals['sending_method_cost_inc_tax_number_format'] = number_format($this->_sending_method_cost_inc_tax, 2, '.', '');

            $this->_totals['payment_method'] = $this->_payment_method;
            $this->_totals['payment_method_id'] = $this->_payment_method_id;
            $this->_totals['payment_method_cost_ex_tax'] = $this->_payment_method_cost_ex_tax;
            $this->_totals['payment_method_cost_inc_tax'] = $this->_payment_method_cost_inc_tax;

            $this->_totals['payment_method_cost_ex_tax_number_format'] = number_format($this->_payment_method_cost_ex_tax, 2, '.', '');
            $this->_totals['payment_method_cost_inc_tax_number_format'] = number_format($this->_payment_method_cost_inc_tax, 2, '.', '');


            $this->_totals['coupon'] = $this->_coupon;
            $this->_totals['coupon_id'] = $this->_coupon_id;
            $this->_totals['coupon_code'] = $this->_coupon_code;

            $this->_totals['discount'] = $this->_discount;
            $this->_totals['discount_ex'] = $this->_discount_ex;

            $this->_totals['discount_number_format'] = number_format($this->_discount, 2, '.', '');
            $this->_totals['discount_ex_number_format'] = number_format($this->_discount_ex, 2, '.', '');


            $this->_totals['total_ex_tax'] = $this->_total_ex_tax;
            $this->_totals['total_inc_tax'] = $this->_total_inc_tax;
            $this->_totals['total_ex_tax_number_format'] = number_format($this->_total_ex_tax, 2, '.', '');
            $this->_totals['total_inc_tax_number_format'] = number_format($this->_total_inc_tax, 2, '.', '');

            $this->_totals['total_tax'] = $this->_total_inc_tax - $this->_total_ex_tax;
            $this->_totals['total_tax_number_format'] = number_format($this->_total_inc_tax - $this->_total_ex_tax, 2, '.', '');

            $this->_totals['producttotal'] = count($this->_products);
            $this->_totals['present'] = $this->_present;
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

    public function getDiscount()
    {
        return $this->_discount;
    }

    public function getDiscountEx()
    {
        return $this->_discount_ex;
    }


    public function setDiscount($discount = 0)
    {

        $this->getTotalexTax();
        $this->getTotalincTax();
        $discount = 0;
        $discountEx = 0;

        if ($this->_coupon) {
            if ($this->_coupon['type'] == 'total_price') {
                if ($this->_coupon['discount_way'] == 'total') {
                    $discount += $this->_coupon['value'];
                } elseif ($this->_coupon['discount_way'] == 'percent') {
                    $discountTotal  = (($this->_coupon['value']/100) * $this->_product_total_inc_tax);
                    $discount += $discountTotal;

                    $discountTotalEx  = (($this->_coupon['value']/100) * $this->_product_total_ex_tax);
                    $discountEx += $discountTotalEx;
                }
            } elseif ($this->_coupon['type'] == 'product') {
                if ($this->_coupon['products']) {
                    foreach ($this->_coupon['products'] as $product) {
                        if (isset($this->_products[$product['id']])) {
                            if ($this->_coupon['discount_way'] == 'total') {
                                $discount += $this->_products[$product['id']]['cart']['count'] * $this->_coupon['value'];
                            } else {
                                $discountTotal  = (($this->_coupon['value']/100) * $this->_products[$product['id']]['total_price_with_tax']);
                                $discount += $discountTotal;
                            }
                        }
                    }
                }
            }
        }

        if (isset($this->_sending_method['total_price_discount_value']) and $this->_sending_method['total_price_discount_value']) {
            if ($this->_sending_method['total_price_discount_type'] == 'amount') {
                $discount += $this->_sending_method['total_price_discount_value'];
            } elseif ($this->_sending_method['total_price_discount_type'] == 'percent') {
                $discountTotal  = (($this->_sending_method['total_price_discount_value']/100) * $this->_product_total_inc_tax);
                $discount += $discountTotal;

                $discountTotalEx  = (($this->_sending_method['total_price_discount_value']/100) * $this->_product_total_inc_tax);
                $discountEx += $discountTotalEx;
            }
        }


        if (isset($this->_payment_method['total_price_discount_value']) and $this->_payment_method['total_price_discount_value']) {
            if ($this->_payment_method['total_price_discount_type'] == 'amount') {
                $discount += $this->_payment_method['total_price_discount_value'];
            } elseif ($this->_payment_method['total_price_discount_type'] == 'percent') {
                $discountTotal  = (($this->_payment_method['total_price_discount_value']/100) * $this->_product_total_inc_tax);
                $discount += $discountTotal;

                $discountTotalEx  = (($this->_payment_method['total_price_discount_value']/100) * $this->_product_total_inc_tax);
                $discountEx += $discountTotalEx;
            }
        }


        $this->_discount = $discount;
        $this->_discount_ex = $discountEx;
        return $this;
    }


    public function summary()
    {

        $this->reset();

        if ($this->_products) {
            foreach ($this->_products as $product) {
                if ($product['cart']['price_details']['discount_price_inc'] OR $product['cart']['price_details']['discount_price_ex']) {
                    $this->_products[$product['id']]['cart']['total_price_ex_tax'] = $product['cart']['count'] * $product['cart']['price_details']['discount_price_ex'];
                    $this->_products[$product['id']]['cart']['total_price_inc_tax'] = $product['cart']['count'] * $product['cart']['price_details']['discount_price_inc'];
                    $this->_products[$product['id']]['total_price_with_tax'] = $product['cart']['count'] * $product['cart']['price_details']['discount_price_inc'];
                    $this->_products[$product['id']]['total_price_without_tax'] = $product['cart']['count'] * $product['cart']['price_details']['discount_price_ex'];
                    
                    $this->_products[$product['id']]['original_total_price_with_tax'] = $product['cart']['count'] * $product['cart']['price_details']['orginal_price_inc_tax'];
                    $this->_products[$product['id']]['original_total_price_without_tax'] = $product['cart']['count'] * $product['cart']['price_details']['orginal_price_ex_tax'];
                    

                    $this->_products[$product['id']]['price_with_tax'] = $product['cart']['price_details']['discount_price_inc'];
                    $this->_products[$product['id']]['price_without_tax'] = $product['cart']['price_details']['discount_price_ex'];
                    $this->_products[$product['id']]['tax_rate'] = $product['cart']['price_details']['tax_rate'];
                    $this->_products[$product['id']]['cart']['tax_rate'] = $product['cart']['price_details']['tax_rate'];
                    $this->_products[$product['id']]['cart']['tax_value'] = $product['cart']['count'] * $product['cart']['price_details']['discount_tax_value'];
                } else {
                    $this->_products[$product['id']]['cart']['total_price_ex_tax'] = $product['cart']['count'] * $product['cart']['price_details']['orginal_price_ex_tax'];
                    $this->_products[$product['id']]['cart']['total_price_inc_tax'] = $product['cart']['count'] * $product['cart']['price_details']['orginal_price_inc_tax'];
                    $this->_products[$product['id']]['total_price_with_tax'] = $product['cart']['count'] * $product['cart']['price_details']['orginal_price_inc_tax'];
                    $this->_products[$product['id']]['total_price_without_tax'] = $product['cart']['count'] * $product['cart']['price_details']['orginal_price_ex_tax'];
                    
                    $this->_products[$product['id']]['original_total_price_with_tax'] = $product['cart']['count'] * $product['cart']['price_details']['orginal_price_inc_tax'];
                    $this->_products[$product['id']]['original_total_price_without_tax'] = $product['cart']['count'] * $product['cart']['price_details']['orginal_price_ex_tax'];
                    


                    $this->_products[$product['id']]['price_with_tax'] = $product['cart']['price_details']['orginal_price_inc_tax'];
                    $this->_products[$product['id']]['price_without_tax'] = $product['cart']['price_details']['orginal_price_ex_tax'];
                    $this->_products[$product['id']]['tax_rate'] = $product['cart']['price_details']['tax_rate'];
                    $this->_products[$product['id']]['cart']['tax_rate'] = $product['cart']['price_details']['tax_rate'];
                    $this->_products[$product['id']]['cart']['tax_value'] = $product['cart']['count'] * $product['cart']['price_details']['tax_value'];
                }

                $this->_products[$product['id']]['cart']['total_price_ex_tax_number_format'] = number_format($this->_products[$product['id']]['cart']['total_price_ex_tax'], 2, '.', '');
                $this->_products[$product['id']]['cart']['total_price_inc_tax_number_format'] = number_format($this->_products[$product['id']]['cart']['total_price_inc_tax'], 2, '.', '');

                $this->_products[$product['id']]['cart']['total_discount'] = 0;
                $this->_products[$product['id']]['original_price_with_tax'] = $this->_products[$product['id']]['cart']['price_details']['orginal_price_inc_tax'];
                $this->_products[$product['id']]['original_price_without_tax'] = $this->_products[$product['id']]['cart']['price_details']['orginal_price_ex_tax'];


                $this->addProductTotalTaxValue($this->_products[$product['id']]['cart']['tax_value']);
                $this->addProductTotalExTax($this->_products[$product['id']]['cart']['total_price_ex_tax']);

                $this->addProductTotalIncTax($this->_products[$product['id']]['cart']['total_price_inc_tax']);
            }

            $this->setDiscount();
            $this->updateSendingMethod($this->_sending_method);
            $this->updatePaymentMethod($this->_payment_method);

            if ($this->getDiscount()) {
                if ($this->_coupon) {
                    if ($this->_coupon['type'] == 'total_price') {
                        $percent = $this->getDiscount() / $this->getProductTotalIncTax();
                        $totalDiscountTaxProducts = $percent * $this->getProductTotalTaxValue();
                        $this->_discount_tax = $totalDiscountTaxProducts;

                        foreach ($this->_products as $product) {
                            $this->addProductTotalIncTax(- $percent * $product['total_price_with_tax']);
                            $this->addProductTotalExTax(- $percent * $product['total_price_without_tax']);

                            $this->_products[$product['id']]['price_with_tax'] = $this->_products[$product['id']]['price_with_tax'] - ($percent * $product['price_with_tax']);
                            $this->_products[$product['id']]['price_without_tax'] = $this->_products[$product['id']]['price_without_tax'] - ($percent * $product['price_without_tax']);
                            $this->_products[$product['id']]['cart']['total_discount'] = ($percent * $this->_products[$product['id']]['total_price_with_tax']);

                            $this->_products[$product['id']]['total_price_with_tax'] = $this->_products[$product['id']]['total_price_with_tax'] - ($percent * $product['total_price_with_tax']);
                            $this->_products[$product['id']]['total_price_without_tax'] = $this->_products[$product['id']]['total_price_without_tax'] - ($percent * $product['total_price_without_tax']);
                        }
                    } elseif ($this->_coupon['type'] == 'product') {
                        if ($this->_coupon['discount_way'] == 'percent') {
                            $totalTaxValue = 0;
                            $totalTaxInc = 0;
                            foreach ($this->_coupon['products'] as $product) {
                                if (isset($this->_products[$product['id']])) {
                                    $totalTaxValue += $this->_products[$product['id']]['cart']['tax_value'];
                                    $totalTaxInc += $this->_products[$product['id']]['cart']['total_price_inc_tax'];
                                }
                            }

                            $percent = $this->getDiscount() / $totalTaxInc;
                            $totalDiscountTaxProducts = $percent * $totalTaxValue;
                            $this->_discount_tax = $totalDiscountTaxProducts;

                            foreach ($this->_coupon['products'] as $product) {
                                if (isset($this->_products[$product['id']])) {
                                    $this->_products[$product['id']]['cart']['tax_value'];
                                  //  $this->addProductTotalIncTax(- $percent * $this->_products[$product['id']]['total_price_with_tax']);
                                  //  $this->addProductTotalExTax(- $percent * $this->_products[$product['id']]['total_price_without_tax']);

                                    $this->_products[$product['id']]['price_with_tax'] = $this->_products[$product['id']]['price_with_tax'] - ($percent * $this->_products[$product['id']]['price_with_tax']);
                                    $this->_products[$product['id']]['price_without_tax'] = $this->_products[$product['id']]['price_without_tax'] - ($percent * $this->_products[$product['id']]['price_without_tax']);

                                    $this->_products[$product['id']]['cart']['total_discount'] = ($percent * $this->_products[$product['id']]['total_price_with_tax']);

                                    $this->_products[$product['id']]['total_price_with_tax'] = $this->_products[$product['id']]['total_price_with_tax'] - ($percent * $this->_products[$product['id']]['total_price_with_tax']);
                                    $this->_products[$product['id']]['total_price_without_tax'] = $this->_products[$product['id']]['total_price_without_tax'] - ($percent * $this->_products[$product['id']]['total_price_without_tax']);
                                }
                            }
                        } elseif ($this->_coupon['discount_way'] == 'total') {
                            foreach ($this->_coupon['products'] as $product) {
                                if (isset($this->_products[$product['id']])) {
                                    $this->addProductTotalIncTax(- $this->_coupon['value'] * $this->_products[$product['id']]['cart']['count']);

                                    $this->_products[$product['id']]['price_with_tax'] = $this->_products[$product['id']]['price_with_tax'] - $this->_coupon['value'];
                                    $taxProduct = $this->_products[$product['id']]['price_with_tax'] - ($this->_products[$product['id']]['price_with_tax'] / (($this->_products[$product['id']]['cart']['tax_rate'] / 100) + 1));

                                    $priceWithoutTax = $this->_products[$product['id']]['price_without_tax'];
                                    $priceWithoutTaxDiscount = $this->_products[$product['id']]['price_with_tax'] - $taxProduct;
                                    $this->_products[$product['id']]['price_without_tax'] = $priceWithoutTaxDiscount;

                                    $this->addProductTotalExTax(- (($priceWithoutTax - $this->_products[$product['id']]['price_without_tax'])) * $this->_products[$product['id']]['cart']['count']);

                                    $this->_products[$product['id']]['cart']['total_discount'] = $this->_products[$product['id']]['cart']['count'] * $this->_coupon['value'];

                                    $this->_products[$product['id']]['total_price_with_tax'] -=  $this->_products[$product['id']]['cart']['count'] * $this->_coupon['value'];
                                    $this->_products[$product['id']]['total_price_without_tax'] = $this->_products[$product['id']]['cart']['count'] * $priceWithoutTaxDiscount;
                                }
                            }
                        }
                    }
                } elseif ($this->_sending_method['total_price_discount_value'] or $this->_payment_method['total_price_discount_value']) {
                    $percent = $this->getDiscount() / $this->getProductTotalIncTax();
                    $totalDiscountTaxProducts = $percent * $this->getProductTotalTaxValue();
                    $this->_discount_tax = $totalDiscountTaxProducts;

                    foreach ($this->_products as $product) {
                        $this->addProductTotalIncTax(- $percent * $product['total_price_with_tax']);
                        $this->addProductTotalExTax(- $percent * $product['total_price_without_tax']);

                        $this->_products[$product['id']]['price_with_tax'] = $this->_products[$product['id']]['price_with_tax'] - ($percent * $product['price_with_tax']);
                        $this->_products[$product['id']]['price_without_tax'] = $this->_products[$product['id']]['price_without_tax'] - ($percent * $product['price_without_tax']);
                        $this->_products[$product['id']]['cart']['total_discount'] = ($percent * $this->_products[$product['id']]['total_price_with_tax']);


                        $this->_products[$product['id']]['total_price_with_tax'] = $this->_products[$product['id']]['total_price_with_tax'] - ($percent * $product['total_price_with_tax']);
                        $this->_products[$product['id']]['total_price_without_tax'] = $this->_products[$product['id']]['total_price_without_tax'] - ($percent * $product['total_price_without_tax']);
                    }
                }
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

    public function getProductTotalTaxValue()
    {
        return $this->_product_total_tax_value;
    }


    public function addProductTotalExTax($total)
    {
        return $this->_product_total_ex_tax += $total;
    }

    public function addProductTotalIncTax($total)
    {
        return $this->_product_total_inc_tax += $total;
    }


    public function getProductTotalIncTax()
    {
        return $this->_product_total_inc_tax;
    }

    public function getTotalexTax()
    {
        $this->_total_ex_tax = $this->_product_total_ex_tax;
        $this->_total_ex_tax += $this->_sending_method_cost_ex_tax;
        $this->_total_ex_tax += $this->_payment_method_cost_ex_tax;

        $this->_total_ex_tax = $this->_total_ex_tax;

        if ($this->_present) {
            $this->_total_ex_tax += $this->_present['cost_ex_tax'];
        }
        return $this;
    }

    public function getTotalincTax()
    {
        $this->_total_inc_tax = $this->_product_total_inc_tax;

        $this->_total_inc_tax += $this->_sending_method_cost_inc_tax;
        $this->_total_inc_tax += $this->_payment_method_cost_inc_tax;

        $this->_total_inc_tax = $this->_total_inc_tax;


        if ($this->_present) {
            $this->_total_inc_tax += $this->_present['cost_inc_tax'];
        }

        return $this;
    }
}
