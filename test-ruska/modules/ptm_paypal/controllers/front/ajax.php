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

class Ptm_PaypalAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
        $status = false;
        $message = '';

        // remove paypal logo
        if (Tools::getValue('action') == 'removeLogo'
            &&  Configuration::get('PTM_PAYPAL_TOKEN') == Tools::getValue('token')) {
            $status  = $this->module->removeLogo();
            $message = $this->module->l('Paypal logo has been removed!');
        }

        ob_end_clean();
        header('Content-Type: application/json');
        die(json_encode(array(
            'status'  => $status,
            'message' => $message
        )));
    }
}
