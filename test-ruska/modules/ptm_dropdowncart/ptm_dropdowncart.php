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

use PrestaShop\PrestaShop\Adapter\Cart\CartPresenter;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use Symfony\Component\Filesystem\Filesystem;

include_once _PS_MODULE_DIR_ .'ptm_dropdowncart/classes/PTM_DDCUtils.php';
include_once _PS_MODULE_DIR_ .'ptm_dropdowncart/classes/Mobile_Detect.php';

class Ptm_Dropdowncart extends Module implements WidgetInterface
{
    private $moduleManager;

    public function __construct()
    {
        $this->name = 'ptm_dropdowncart';
        $this->tab = 'front_office_features';
        $this->version = '1.4.0';
        $this->author = 'Presta Theme Maker';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Ajax Drop Down Shopping cart');
        $this->description = $this->l('Adds a block containing the customer\'s shopping cart.');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);

        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $this->moduleManager  = $moduleManagerBuilder->build();
        $this->nativeShoppingCartName = 'ps_shoppingcart';
    }

    public function hookDisplayHeader()
    {
        if (Configuration::isCatalogMode()) {
            return;
        }

        if (Configuration::get('PS_BLOCK_CART_AJAX')) {
            $this->context->controller->registerJavascript('modules-ptmdropdowncart', 'modules/'.$this->name.'/views/js/ptm_dropdowncart.js', ['position' => 'bottom', 'priority' => 150]);
        }
        
        $this->context->controller->registerStylesheet('modules-ptmdropdowncart', 'modules/'.$this->name.'/views/css/ptm_dropdowncart.css', ['position' => 'bottom', 'priority' => 150]);
    }

    private function getCartSummaryURL()
    {
        return $this->context->link->getPageLink(
            'cart',
            null,
            $this->context->language->id,
            ['action' => 'show']
        );
    }

    private function getCheckProcessingURL()
    {
        return $this->context->link->getPageLink(
            'order',
            null,
            $this->context->language->id
        );
    }

    public function getWidgetVariables($hookName, array $params)
    {
        $redirect_visitors = Configuration::get('PTM_DDC_REDIRECT_VISITORS');
        $mobile_detect = new Mobile_Detect();
        $shadow =  Configuration::get('PTM_BLOCK_CART_SHADOW');
        $dropdownonphones =  Configuration::get('PTM_BLOCK_CART_PHONE');

        return [
            'cart' => (new CartPresenter)->present(isset($params['cart']) ? $params['cart'] : $this->context->cart),
            'refresh_url' => $this->context->link->getModuleLink('ptm_dropdowncart', 'ajax'),
            'link' => $this->context->link,
            'cart_url' => $this->getCartSummaryURL(),
            'option_url' => ($redirect_visitors == 'cart' ? $this->getCartSummaryURL() : $this->getCheckProcessingURL()),
            'redirect_visitors_opt' => $redirect_visitors,
            'is_mobile' => (int)$mobile_detect->isMobile() ? true : false,
            'get_device' => ($mobile_detect->isMobile() ? ($mobile_detect->isTablet() ? 'table' : 'mobile') : 'computer'),
            'display_shadow' => $shadow,
            'dropdownonphones' => $dropdownonphones
        ];
    }

    public function renderWidget($hookName, array $params)
    {
        if (Configuration::isCatalogMode()) {
            return;
        }

        $this->smarty->assign($this->getWidgetVariables($hookName, $params));
        return $this->fetch('module:ptm_dropdowncart/views/templates/hook/ptm_dropdowncart.tpl');
    }

    public function renderModal(Cart $cart, $id_product, $id_product_attribute)
    {
        $data = (new CartPresenter)->present($cart);
        $product = null;
        foreach ($data['products'] as $p) {
            if ($p['id_product'] == $id_product && $p['id_product_attribute'] == $id_product_attribute) {
                $product = $p;
                break;
            }
        }

        $this->smarty->assign([
            'product'  => $product,
            'cart'     => $data,
            'cart_url' => $this->getCartSummaryURL()
        ]);

        return $this->fetch('module:ptm_dropdowncart/views/templates/hook/modal.tpl');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitBlockCart')) {
            $ajax = Tools::getValue('PS_BLOCK_CART_AJAX');
            if ($ajax != 0 && $ajax != 1) {
                $output .= $this->displayError($this->l('Ajax: Invalid choice.'));
            } else {
                Configuration::updateValue('PS_BLOCK_CART_AJAX', (int)($ajax));
                Configuration::updateValue('PTM_DDC_REDIRECT_VISITORS', Tools::getValue('redirect_visitors'));
                Configuration::updateValue('PTM_BLOCK_CART_PHONE', Tools::getValue('PTM_BLOCK_CART_PHONE'));
                Configuration::updateValue('PTM_BLOCK_CART_SHADOW', Tools::getValue('PTM_BLOCK_CART_SHADOW'));
                $output .= $this->displayConfirmation($this->l('Settings updated.'));
            }

            /*if ((int)Tools::getValue('PS_BLOCK_CART_XSELL_LIMIT') < 0) {
                $output .= $this->displayError($this->l('Please complete the "Products to display" field.'));
            } else {
                Configuration::updateValue('PS_BLOCK_CART_XSELL_LIMIT', (int)(Tools::getValue('PS_BLOCK_CART_XSELL_LIMIT')));
            }*/

            // Configuration::updateValue('PS_BLOCK_CART_SHOW_CROSSSELLING', (int)(Tools::getValue('PS_BLOCK_CART_SHOW_CROSSSELLING')));
        }
        return $output.$this->renderForm();
    }

    public function install()
    {
        if (parent::install()
                && $this->registerHook('displayHeader')
                && $this->registerHook('displayNav2')
                && Configuration::updateValue('PS_BLOCK_CART_AJAX', 1)
                && Configuration::updateValue('PS_BLOCK_CART_XSELL_LIMIT', 6)
                && Configuration::updateValue('PS_BLOCK_CART_SHOW_CROSSSELLING', 0) 
                && Configuration::updateValue('PTM_DDC_REDIRECT_VISITORS', 'order')
                && Configuration::updateValue('PTM_BLOCK_CART_PHONE', 0)
                && Configuration::updateValue('PTM_BLOCK_CART_SHADOW', 1) ) {
            
            // uninstall shopping cart module
            if ($this->moduleManager->isInstalled($this->nativeShoppingCartName)) {
                $this->moduleManager->uninstall($this->nativeShoppingCartName);
            }

            return true;
        }
        return false;
    }

    public function uninstall()
    {
        if (parent::uninstall()) {
            // install shopping cart module
            if (!$this->moduleManager->isInstalled($this->nativeShoppingCartName)) {
                $this->moduleManager->install($this->nativeShoppingCartName);
            }
            // fix hook bug
            $this->fixPsShoppingCartModule();

            return true;
        }
        return false;
    }

    public function enable($force_all = false)
    {
        $this->moduleManager->uninstall($this->nativeShoppingCartName);

        return parent::enable($force_all);
    }

    public function disable($force_all = false)
    {
        // install shopping cart module
        $this->moduleManager->install($this->nativeShoppingCartName);
        // fix hook bug
        $this->fixPsShoppingCartModule();

        return parent::disable($force_all);
    }

    public function renderForm()
    {

        $shadows = array(
            ['id' => '1', 'name' => $this->l('Follow theme settings')],
            ['id' => '2', 'name' => $this->l('No shadow')],
            ['id' => '3', 'name' => $this->l('Light shadow')],
            ['id' => '4', 'name' => $this->l('Medium shadow')],
            ['id' => '5', 'name' => $this->l('Strong shadow')],
        );

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Ajax cart'),
                        'name' => 'PS_BLOCK_CART_AJAX',
                        'is_bool' => true,
                        'desc' => $this->l('Activate Ajax mode for the cart'),
                        'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Enabled')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled')
                                )
                            ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Where to redirect visitors by clicking on the "checkout" button'),
                        'name' => 'redirect_visitors',
                        'options' => array(
                            'query' => [
                                [
                                    'id' => 'order',
                                    'name' => $this->l('Checkout')
                                ],
                                [
                                    'id' => 'cart',
                                    'name' => $this->l('Shopping cart')
                                ]
                            ],
                            'id'   => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display drop down cart also on phones and tablets'),
                        'name' => 'PTM_BLOCK_CART_PHONE',
                        'is_bool' => true,
                        'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Enabled')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled')
                                )
                            ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Display shadow'),
                        'class' => 'fixed-width-sm',
                        'name' => 'PTM_BLOCK_CART_SHADOW',
                        'options' => array(
                            'query' => $shadows,
                            'id'   => 'id',
                            'name' => 'name'
                        ),
                    ),

                    /*array(
                        'type' => 'switch',
                        'label' => $this->l('Show cross-selling'),
                        'name' => 'PS_BLOCK_CART_SHOW_CROSSSELLING',
                        'is_bool' => true,
                        'desc' => $this->l('Activate cross-selling display for the cart.'),
                        'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => 1,
                                    'label' => $this->l('Enabled')
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => 0,
                                    'label' => $this->l('Disabled')
                                )
                            ),
                        ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Products to display in cross-selling'),
                        'name' => 'PS_BLOCK_CART_XSELL_LIMIT',
                        'class' => 'fixed-width-xs',
                        'desc' => $this->l('Define the number of products to be displayed in the cross-selling block.')
                    ),*/
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBlockCart';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab
        .'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'PS_BLOCK_CART_AJAX' => (bool)Tools::getValue('PS_BLOCK_CART_AJAX', Configuration::get('PS_BLOCK_CART_AJAX')),
            'redirect_visitors' => (bool)Tools::getValue('redirect_visitors', Configuration::get('PTM_DDC_REDIRECT_VISITORS')),

            'PTM_BLOCK_CART_PHONE' => (bool)Tools::getValue('PTM_BLOCK_CART_PHONE', Configuration::get('PTM_BLOCK_CART_PHONE')),

            'PTM_BLOCK_CART_SHADOW' => Tools::getValue('PTM_BLOCK_CART_SHADOW', Configuration::get('PTM_BLOCK_CART_SHADOW')),
            // 'PS_BLOCK_CART_SHOW_CROSSSELLING' => (bool)Tools::getValue('PS_BLOCK_CART_SHOW_CROSSSELLING', Configuration::get('PS_BLOCK_CART_SHOW_CROSSSELLING')),
            // 'PS_BLOCK_CART_XSELL_LIMIT' => (int)Tools::getValue('PS_BLOCK_CART_XSELL_LIMIT', Configuration::get('PS_BLOCK_CART_XSELL_LIMIT'))
        );
    }

    public function fixPsShoppingCartModule()
    {
        PTM_DDCUtils::fixPsShoppingCartModuleHook(new Filesystem(), $this->name, $this->context->shop->id);
    }
}
