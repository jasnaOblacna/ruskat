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

if (!defined('_PS_VERSION_')) {
    exit;
}
use PrestaShop\PrestaShop\Core\Foundation\Database\EntityManager;

class Ptm_AddToCartButton extends Module
{
    protected $entity_manager;

    public function __construct(EntityManager $entity_manager)
    {
        $this->name = 'ptm_addtocartbutton';
        $this->tab = 'front_office_features';
        $this->version = '1.2.0';
        $this->author = 'Presta Theme Maker';
        $this->bootstrap = true;
        $this->need_instance = 0;

        parent::__construct();
        
        $this->entity_manager = $entity_manager;

        $this->displayName = $this->l('Add to Cart Button');
        $this->description = $this->l('A module that will add “Add to cart” functionality to all product boxes.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        if (parent::install() 
            && $this->registerHook('displayProductPriceBlock') 
            && $this->registerHook('displayHeader')
        ) {
            return true;
        }
        return false;
    }

    public function uninstall()
    {
        /* Deletes Module */
        if (parent::uninstall()) {
            return true;
        }
        return false;
    }

    public function hookDisplayProductPriceBlock($params)
    {
        if (!isset($params['product']) || !isset($params['type'])) {
            return;
        }

        $hook_type = $params['type'];

        if ($hook_type == 'before_price') {
            $product = $params['product'];

            if (!is_array($product)) {
                return;
            }

            $this->context->smarty->assign([
                'hasQty' => (int)$product['quantity'],
            ]);

            if (count($product['attributes'])) {
                $this->context->smarty->assign([
                    'product_url' => $product['url'],
                    'hasAttribute'=> true
                ]);
            } else {
                $this->context->smarty->assign([
                    'id_product' => (int)$product['id_product'], 
                    'hasAttribute'=> false,
                    'cart_url' => $this->context->link->getPageLink('cart', true)
                ]);
            }

            return $this->display(__FILE__, 'addtocartbutton.tpl');
        }
    }

    public function hookdisplayHeader($params)
    {
        $this->context->controller->registerStylesheet('modules-ptm-addtocartbutton', 'modules/'.$this->name.'/views/css/ptm_addtocartbutton_frt.css', ['media' => 'all', 'priority' => 150]);
        $this->context->controller->registerJavascript('modules-ptm-addtocartbutton', 'modules/'.$this->name.'/views/js/ptm_addtocartbutton_frt.js', ['position' => 'bottom', 'priority' => 150]);
    }
}
