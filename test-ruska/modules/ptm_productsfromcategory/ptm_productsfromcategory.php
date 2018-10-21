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

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
include _PS_MODULE_DIR_ .'ptm_productsfromcategory/src/model/ProductModel.php';

class Ptm_ProductsFromCategory extends Module
{
    protected $spacer_size = '5';
    protected $_html = '';
    protected $_errors = array();

    public function __construct()
    {
        $this->name = 'ptm_productsfromcategory';
        $this->tab = 'front_office_features';
        $this->version = '1.2.1';
        $this->author = 'Presta Theme Maker';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Products from a selected category');
        $this->description = $this->l('Displays products in the central column of your homepage from a selected category');

        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        $langs = Language::getLanguages(false);

        /* Adds Module */
        if(parent::install()
            && Configuration::updateValue('PTM_PFC_PRODUCT_POS', 1)
            && Configuration::updateValue('PTM_PFC_CATEGORY_ID', 2)
            && Configuration::updateValue('PTM_PFC_PRODUCT_LIMIT', 10)
            && $this->registerHook('displayHome')){

            // Main title of the block
            Configuration::updateValue('PTMPRODUCTSFROMCATEGORY_TITLE', 1);
            $block_name = array();
            foreach ($langs as $lang) {
                $block_name['PTMPRODUCTSFROMCATEGORY_TITLE'][$lang['id_lang']] = 'Products from our favorite category';
                Configuration::updateValue('PTMPRODUCTSFROMCATEGORY_TITLE', $block_name['PTMPRODUCTSFROMCATEGORY_TITLE']);
            }

            return true;

        }
        return false;
    }

    /**
     * @see Module::uninstall()
     */
    public function uninstall()
    {
        /* Deletes Module */
        if (parent::uninstall()){
            
            /* Unsets configuration */
            Configuration::deleteByName('PTM_PFC_PRODUCT_POS');
            Configuration::deleteByName('PTM_PFC_CATEGORY_ID');
            Configuration::deleteByName('PTM_PFC_PRODUCT_LIMIT');
            Configuration::deleteByName('PTMPRODUCTSFROMCATEGORY_TITLE');
            
            return true;
        }
        return false;
    }

    public function getContent()
    {

        if (Tools::isSubmit('submitConfig')) {
            if ($this->_postValidation()) {
                $this->_postProcess();
                $this->_html .= $this->renderForm();
            }
        }else{
            $this->_html .= $this->renderForm();
        }

        return $this->_html;
    }

    public function _postProcess()
    {

        $errors = array();
        $langs = Language::getLanguages(false);

        $res = Configuration::updateValue('PTM_PFC_CATEGORY_ID', (int)Tools::getValue('PTM_PFC_CATEGORY_ID'));
        $res &= Configuration::updateValue('PTM_PFC_PRODUCT_POS', (int)Tools::getValue('PTM_PFC_PRODUCT_POS'));
        $res &= Configuration::updateValue('PTM_PFC_PRODUCT_LIMIT', (int)Tools::getValue('PTM_PFC_PRODUCT_LIMIT'));

        $block_name = array();
        foreach ($langs as $lang) {
            $block_name['PTMPRODUCTSFROMCATEGORY_TITLE'][$lang['id_lang']] = Tools::getValue('PTMPRODUCTSFROMCATEGORY_TITLE_'. $lang['id_lang']);
            $res &= Configuration::updateValue('PTMPRODUCTSFROMCATEGORY_TITLE', $block_name['PTMPRODUCTSFROMCATEGORY_TITLE']);
        }

        if (!$res) {
            $this->_html .= $this->displayError('Problem when saving your settings.');
        }else{
            $this->_html .= $this->displayConfirmation($this->l('The settings have been successfully updated.'));
        }
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->getTranslator()->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title of the section'),
                        'lang' => true,
                        'name' => 'PTMPRODUCTSFROMCATEGORY_TITLE',
                    ),
                    array(
                        'type' => 'category_choices',
                        'label' => $this->l('Select category'),
                        'name' => 'PTM_PFC_CATEGORY_ID',
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Number of products'),
                        'name' => 'PTM_PFC_PRODUCT_LIMIT',
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Order'),
                        'name' => 'PTM_PFC_PRODUCT_POS',
                        'values' => array(
                            array(
                                'id' => 'name',
                                'value' => 1,
                                'label' => $this->l('By default')
                            ),
                            array(
                                'id' => 'position',
                                'value' => 0,
                                'label' => $this->l('Random order')
                            ),
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->getTranslator()->trans('Save', array(), 'Admin.Actions'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitConfig';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $selectOptions = $this->makeOptions($this->context->shop->id, $this->context->language->id);
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages'   => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'options_choice' => $selectOptions
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        $langs = Language::getLanguages(false);
        $id_shop_group = Shop::getContextShopGroupID();
        $id_shop = Shop::getContextShopID();

        $data = array(
            'PTM_PFC_PRODUCT_POS' => (int)Tools::getValue('PTM_PFC_PRODUCT_POS', Configuration::get('PTM_PFC_PRODUCT_POS', null, $id_shop_group, $id_shop)),
            'PTM_PFC_CATEGORY_ID' => (int)Tools::getValue('PTM_PFC_CATEGORY_ID', Configuration::get('PTM_PFC_CATEGORY_ID', null, $id_shop_group, $id_shop)),
            'PTM_PFC_PRODUCT_LIMIT' => (int)Tools::getValue('PTM_PFC_PRODUCT_LIMIT', Configuration::get('PTM_PFC_PRODUCT_LIMIT', null, $id_shop_group, $id_shop))
        );

        foreach ($langs as $lang) {
            $data['PTMPRODUCTSFROMCATEGORY_TITLE'][$lang['id_lang']] = Tools::getValue('PTMPRODUCTSFROMCATEGORY_TITLE_'. $lang['id_lang'], Configuration::get('PTMPRODUCTSFROMCATEGORY_TITLE', $lang['id_lang']));
        }

        return $data;
    }

    public function hookDisplayHome($params)
    {
        $this->smarty->assign($this->getWidgetVariables());

        return $this->display(__FILE__, 'views/templates/hook/products_list.tpl');
    }

    public function getWidgetVariables(){

        if (!($id_category = (int)Configuration::get('PTM_PFC_CATEGORY_ID'))) {
            return;
        }

        $context = $this->context;
        $products_position = (int)Configuration::get('PTM_PFC_PRODUCT_POS');
        $products_limit = (int)Configuration::get('PTM_PFC_PRODUCT_LIMIT');
        if(!((int)$products_limit > 0)){
            $products_limit = 10;
        }
        $getProducts = ProductModel::getProducts(ProductModel::getProductsByCategory($context->language->id, true, 0, $products_limit, 'id_product', 'ASC',  $id_category, true, $context), $context->language->id, $products_position, 'id_product', 'ASC', $products_limit);
        $products = array();

        if ($getProducts) {
            $assembler = new ProductAssembler($context);
            $presenterFactory = new ProductPresenterFactory($context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    $context->link
                ),
                $context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $context->getTranslator()
            );

            foreach ($getProducts as $rawProduct) {
                $products[] = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct($rawProduct),
                    $context->language
                );
            }
        }

        $config   = $this->getConfigFieldsValues();
        $titleOpt = $config['PTMPRODUCTSFROMCATEGORY_TITLE'][$context->language->id];

        return array(
            'options' => array(
                'titleOpt'         => $titleOpt
            ),
            'products'       => $products,
            'allProductsLink'  => $context->link->getCategoryLink($id_category),
        );

    }

    public function _postValidation()
    {

        $errors = array();

        
        if (Tools::isEmpty(Tools::getValue('PTMPRODUCTSFROMCATEGORY_TITLE'))){
            $errors[] = $this->l('Invalid values, please check that all entered values are correct');
        }

        if (!Validate::isInt(Tools::getValue('PTM_PFC_CATEGORY_ID'))) {
            $this->_errors[] = $this->displayError($this->l('Category ID is not valid'));
        }

        if (!Validate::isInt(Tools::getValue('PTM_PFC_PRODUCT_LIMIT'))) {
            $this->_errors[] = $this->displayError($this->l('Product limit can be only a number'));
        }

        /* Display errors if needed */
        if (count($errors)) {
            foreach ($errors as $error) {
                $this->_html .= $this->displayError($error);
            }

            return false;
        }

        /* Returns if validation is ok */

        return true;
    }

    private function makeOptions($shop_id, $id_lang, $active = true, $groups = null)
    {
        if (!Validate::isBool($active)) {
            die(Tools::displayError());
        }

        if (isset($groups) && Group::isFeatureActive() && !is_array($groups)) {
            $groups = (array)$groups;
        }

        $cache_id = Tools::strtoupper($this->name). '::Category::generateCategories_'.md5((int)$shop_id.(int)$id_lang.(int)$active.(int)$active
            .(isset($groups) && Group::isFeatureActive() ? implode('', $groups) : ''));

        if (!Cache::isStored($cache_id)) {
            $sql = 'SELECT c.`id_category`, c.`level_depth`, c.`id_parent`, cl.`name`
                FROM `'._DB_PREFIX_.'category` c
                INNER JOIN `'._DB_PREFIX_.'category_shop` category_shop ON (category_shop.`id_category` = c.`id_category` AND category_shop.`id_shop` = "'.(int)$shop_id.'")
                LEFT JOIN `'._DB_PREFIX_.'category_lang` cl ON (c.`id_category` = cl.`id_category` AND cl.`id_shop` = "'.(int)$shop_id.'")
                WHERE 1 '.($id_lang ? 'AND cl.`id_lang` = '.(int)$id_lang : '').'
                '.($active ? ' AND (c.`active` = 1 OR c.`is_root_category` = 1)' : '').'
                '.(isset($groups) && Group::isFeatureActive() ? ' AND cg.`id_group` IN ('.implode(',', $groups).')' : '').'
                '.(!$id_lang || (isset($groups) && Group::isFeatureActive()) ? ' GROUP BY c.`id_category`' : '').'
                ORDER BY c.`level_depth` ASC';
            $result = Db::getInstance()->executeS($sql);

            $categories = array();
            $buff = array();

            foreach ($result as $row) {
                $current = &$buff[$row['id_category']];
                $current = $row;

                if ($row['id_parent'] == 0) {
                    $categories[$row['id_category']] = &$current;
                } else {
                    $buff[$row['id_parent']]['children'][$row['id_category']] = &$current;
                }
            }

            $get_categories_options = '';
            $get_categories_options = $this->combineCategories($categories);

            // return $get_categories_options;
            Cache::store($cache_id, $get_categories_options);
        }

        return Cache::retrieve($cache_id);
    }

    public function combineCategories($categories)
    {
        $html = '';

        foreach ($categories as $category) {
            $this->context->smarty->assign(array(
                'id_category' => (int)$category['id_category'],
                'spacer' => str_repeat('&nbsp;', $this->spacer_size * (int)$category['level_depth']),
                'name' => $category['name'],
                'is_selected' => ((int)$category['id_category'] === (int)Configuration::get('PTM_PFC_CATEGORY_ID')),
            ));
            $html .= $this->context->smarty->fetch($this->local_path .'views/templates/admin/select_option.tpl');

            if (isset($category['children']) && !empty($category['children'])) {
                $html .= $this->combineCategories($category['children']);
            }
        }

        return $html;
    }
}