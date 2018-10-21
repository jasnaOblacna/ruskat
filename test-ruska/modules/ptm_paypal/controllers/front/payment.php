<?php
/**
* 2016 - 2017 PrestaBuilder
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future.
*
*  @author    PrestaBuilder <prestabuilder@gmail.com>
*  @copyright 2016 - 2017 PrestaBuilder
*  @license   Do not distribute this module without permission from the author
*/

class Ptm_PaypalPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    public function init()
    {
        parent::init();

        $this->php_self = 'ptm_paypal_payment';
        $this->context  = Context::getContext();

        // redirect to home page
        if (!$this->module->active) {
            Tools::redirect($this->context->link->getPageLink('index.php'));
        }
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        $id_invoice = ($cart->id_address_invoice ? $cart->id_address_invoice : $cart->id_address_delivery);
        $address_invoice = new Address((int)$id_invoice);
        $lang_iso_code = $this->context->language->iso_code;
        $currency_code = $this->context->currency->iso_code;
        $return_url = $this->context->link->getModuleLink($this->module->name, 'success');
        $cancel_return = $this->context->link->getModuleLink($this->module->name, 'cancel');
        $notify_url = $this->context->link->getModuleLink($this->module->name, 'notify');
        $shipping_amt = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        $discount_amt = $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);

        $this->context->smarty->assign(array(
            'id_cart' => $cart->id,
            'id_shop' =>  $this->context->shop->id,
            'customer' => new Customer((int)$cart->id_customer),
            'address_invoice' => $address_invoice,
            'state' => ($address_invoice->id_state ? State::getNameById($address_invoice->id_state) : ''),
            'nbProducts' => $cart->nbProducts(),
            'total'     => $cart->getOrderTotal(true, Cart::BOTH),
            'iso_code'   => $lang_iso_code,
            'currency_code' => $currency_code,
            'products_amt' => $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS),
            'shipping_amt' => $shipping_amt,
            'discount_amt' => $discount_amt,
            'products' => $cart->getProducts(),
            'notify_url' => $notify_url,
            'success_url' => $return_url,
            'cancel_url' => $cancel_return,
            'paypal_url' => (Configuration::get('PTM_PAYPAL_MODE') == 1 ? Ptm_Paypal::PAYPAL_LIVE_URL : Ptm_Paypal::PAYPAL_SANBOX_URL),
            'merchant_email' => Configuration::get('PTM_PAYPAL_EMAIL'),
            'page_style' => Configuration::get('PTM_PAYPAL_DISPLAY_PAGE'),
            'immediate' => Configuration::get('PTM_PAYPAL_IMMEDIATE_REDIRECTION'),
            'shipping' => Configuration::get('PTM_PAYPAL_SHIPPING_ADDRESS'),
        ));

        $this->setTemplate('payment_execution.tpl');
    }
}
