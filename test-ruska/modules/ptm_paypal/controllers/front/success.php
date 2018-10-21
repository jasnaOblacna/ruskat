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

class Ptm_PaypalSuccessModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    public function init()
    {
        parent::init();
        CartRule::cleanCache();

        $this->php_self = 'ptm_paypal_success';
        $this->context  = Context::getContext();

        // redirect to home page
        if (!$this->module->active) {
            Tools::redirect($this->context->link->getPageLink('index.php'));
        }
    }

    public function postProcess()
    {
        $this->context->smarty->assign(array(
            'title' => Configuration::get('PTM_PAYPAL_WP_TITLE', $this->context->language->id),
            'content' => Configuration::get('PTM_PAYPAL_WP_CONTENT', $this->context->language->id),
            'text_btn' => Configuration::get('PTM_PAYPAL_WP_BTN', $this->context->language->id),
            'order_url' => $this->context->link->getPageLink('history', true, null)
        ));

        $this->setTemplate('payment_success.tpl');
    }
}
