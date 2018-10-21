<?php
/**
* 2016 - 2018 PrestaBuilder
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
*  @copyright 2016 - 2018 PrestaBuilder
*  @license   Do not distribute this module without permission from the author
*/

class Ptm_DropdowncartAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
    * @see FrontController::initContent()
    */
    public function initContent()
    {
        $modal = null;

        if (Tools::getValue('action') === 'add-to-cart') {
            $modal = $this->module->renderModal(
                $this->context->cart,
                Tools::getValue('id_product'),
                Tools::getValue('id_product_attribute')
            );
        }

        // remove prodcut from cart
        if (Tools::getValue('action') === 'removeProduct') {
            $this->context->cart->deleteProduct(
                (int)Tools::getValue('id_product'),
                (int)Tools::getValue('id_product_attribute'),
                ((int)Tools::getValue('id_product_customization') ? (int)Tools::getValue('id_product_customization') : null)
            );
        }

        ob_end_clean();
        header('Content-Type: application/json');
        die(Tools::jsonEncode([
            'preview' => $this->module->renderWidget(null, ['cart' => $this->context->cart]),
            'modal'   => $modal
        ]));
    }
}
