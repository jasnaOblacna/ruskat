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

class Ptm_PaypalNotifyModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $context = Context::getContext();
        $custom = explode('#', Tools::getValue('custom'));
        $cart = new Cart((int)$custom[0]);
        $context->cart = $cart;
        $can_log = (int)Configuration::get('PTM_PAYPAL_LOGGING_SYS');

        if ($can_log) {
            $_POST['ps_controller'] = 'Notify controller';
            PTMPLogger::log($_POST);
        }

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            if ($can_log) {
                PTMPLogger::log('no id_customer: '. $cart->id_customer.', no id_address_invoice'.$cart->id_address_invoice.', no id_address_delivery'.$cart->id_address_delivery .' -> Notify controller');
            }

            return;
        }

        // Does order already exists ?
        if ($context->cart->OrderExists() == true) {
	        if ($can_log) {
                PTMPLogger::log('cart already exists -> Notify controller');
            }

	        return;
        }

        // check amounts, if not the same, stop processing the payment
        if ((float)Tools::getValue('mc_gross') != (float)$cart->getOrderTotal(true, Cart::BOTH)) {
            header("HTTP/1.1 400 Bad Request");
            if ($can_log) {
                PTMPLogger::log("Gross amount from paypal: ". (float)Tools::getValue('mc_gross') ." is different than total cart amount in this eshop: ". (float)$cart->getOrderTotal(true, Cart::BOTH) .' -> Notify controller');
            }

            return;
        }

        // verify IPN
        $data = Tools::file_get_contents('php://input');
        $use_sandbox = (Configuration::get('PTM_PAYPAL_MODE') == 1 ? false : true);
        $response = IPN_Verifier::verifyNotification($data, $use_sandbox);

        if ($response) {
            if ($can_log) {
                PTMPLogger::log("Good verified res: ". $response .', gross: '. Tools::getValue('mc_gross') .' -> Notify controller');
            }

	        $customer = new Customer((int)$cart->id_customer);
	        
	        if (!Validate::isLoadedObject($customer)) {
	            return;
	        }

	        $paypal_products = array('express' => 'PayPal Express Checkout', 'standard' => 'PayPal Standard', 'advanced' => 'PayPal Payments Advanced',  'payflow_pro' => 'PayPal PayFlow Pro');
	        $payment_type = (isset($paypal_products[Tools::getValue('payment_type')]) ? $paypal_products[Tools::getValue('payment_type')] : $paypal_products['standard']);

	        $currency = (Tools::getValue('mc_currency') ? new Currency((int)Currency::getIdByIsoCode(Tools::getValue('mc_currency'), (int)$custom[1])) : $context->currency);
	        $total    = (float)Tools::getValue('mc_gross');
	        $mailVars = array();
	        $mode     = (Tools::getValue('test_ipn') ? 'Test (Sandbox)' : 'Live');

	        // insert new data into ptm_paypal table
	        $this->module->repository->add(Tools::getValue('txn_id'), $payment_type, Tools::getValue('payment_date'), (float)Tools::getValue('mc_gross'), Tools::getValue('mc_currency'), (float)Tools::getValue('mc_fee'), Tools::getValue('protection_eligibility'), Tools::getValue('address_status'), Tools::getValue('payer_id'), Tools::getValue('payer_status'), Tools::getValue('payer_email'), Tools::getValue('ipn_track_id'), Tools::getValue('verify_sign'), $mode, (int)$cart->id);

	        $message = "Add order: \n\r\tid_cart: ".(int)$cart->id."\n\r\tpayment_status: ".Configuration::get('PS_OS_PAYMENT')."\n\r\ttotal: ". $total."\n\r\tmodule: ".$this->module->displayName."\n\r\tcurrency id: ".(int)$currency->id;
	        if ($can_log) {
                PTMPLogger::log($message .' -> Notify controller');
            }
	        $this->module->validateOrder((int)$cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, null, $mailVars, (int)$currency->id, false, $customer->secure_key);
        } else {
            if ($can_log) {
                PTMPLogger::log("Problem with response: ". $response .' -> Notify controller');
            }
        }

        // Reply with an empty 200 response to indicate to paypal the IPN was received correctly.
		header("HTTP/1.1 200 OK");
    }

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $this->setTemplate('payment_notify.tpl');
    }
}
