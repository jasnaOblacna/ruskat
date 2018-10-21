<?php
/**
 * This source file is subject to a commercial license from AZELAB
 *
 * @author    AZELAB
 * @copyright Copyright (c) 2014 AZELAB (http://www.azelab.com)
 * @license   Commercial license
 * Support by mail:  support@azelab.com
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Tabbedcathome extends Module
{
    const PAGE_HOME = 1;

    const PAGE_PRODUCT = 3;

    protected $templateFile;

    protected $pageList = array();

    public function __construct()
    {
        $this->name = 'tabbedcathome';
        $this->tab = 'front_office_features';
        $this->version = '3.0.0';
        $this->author = 'azelab';
        $this->module_key = '8ff7e7505c427061a6954fa413556573';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Tabbed Featured Categories Subcategories on Home');
        $this->description = $this->l('Displays Categories, Subcategories in the middle of your homepage.');
        if ($this->is17()) {
            $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        }
        $this->templateFile = 'module:tabbedcathome/views/templates/hook/tabbedcathome.tpl';
        $this->pageList = array(
            self::PAGE_HOME => $this->l('Home page'),
            self::PAGE_PRODUCT => $this->l('Product page'),
        );
    }

    public function install()
    {
        $image_types = ImageType::getImagesTypes('categories');
        if (!parent::install()
            || !$this->registerHooks()
            || !Configuration::updateValue('TABBED_TITLE', 1)
            || !Configuration::updateValue('TABBED_ALL', 0)
            || !Configuration::updateValue('TABBED_CAT_ALL', 1)
            || !Configuration::updateValue('TABBED_CAT_ALL_HIDE', 1)
            || !Configuration::updateValue('TABBED_CAT_DEFAULT', 0)
            || !Configuration::updateValue('TABBED_SUBSUBCAT', 0)
            || !Configuration::updateValue('TABBED_CAT_DESC', 0)
            || !Configuration::updateValue('TABBED_DESC_LNG', 100)
            || !Configuration::updateValue('TABBED_CAT_NBR', 4)
            || !Configuration::updateValue('TABBED_IMG', isset($image_types[0])? $image_types[0]['id_image_type'] : null)
            || !Configuration::updateValue('TABBED_CAT_HIDDEN', 0)
            || !Configuration::updateValue('TABBED_SUBCAT_HIDDEN', 0)
            || !Configuration::updateValue('TABBED_SHOW_ON_PAGES', self::PAGE_HOME)) {
            return false;
        }
        $this->clearCache();
        if ($this->is17()) {
            $hook_id = Hook::getIdByName('displayHome');
            $this->updatePosition($hook_id, 0, 2);
        } else {
            $hook_id = Hook::getIdByName('displayTopColumn');
            $this->updatePosition($hook_id, 0, 3);
        }
        return true;
    }
    
    protected function registerHooks()
    {
        if ($this->is16()) {
            if (!$this->registerHook('displayTopColumn')
                || !$this->registerHook('categoryAddition')
                || !$this->registerHook('categoryUpdate')
                || !$this->registerHook('categoryDeletion')
                || !$this->registerHook('displayFooterProduct')
                || !$this->registerHook('header')) {
                return false;
            }
            return true;
        } else {
            if (!$this->registerHook('displayHome')
                || !$this->registerHook('categoryAddition')
                || !$this->registerHook('categoryUpdate')
                || !$this->registerHook('categoryDeletion')
                || !$this->registerHook('displayFooterProduct')
                || !$this->registerHook('header')) {
                return false;
            }
            return true;
        }
    }

    public function uninstall()
    {
        if (!parent::uninstall()
            || !Configuration::deleteByName('TABBED_TITLE')
            || !Configuration::deleteByName('TABBED_ALL')
            || !Configuration::deleteByName('TABBED_CAT_ALL')
            || !Configuration::deleteByName('TABBED_CAT_ALL_HIDE')
            || !Configuration::deleteByName('TABBED_CAT_DEFAULT')
            || !Configuration::deleteByName('TABBED_SUBSUBCAT')
            || !Configuration::deleteByName('TABBED_CAT_DESC')
            || !Configuration::deleteByName('TABBED_DESC_LNG')
            || !Configuration::deleteByName('TABBED_CAT_NBR')
            || !Configuration::deleteByName('TABBED_IMG')
            || !Configuration::deleteByName('TABBED_CAT_HIDDEN')
            || !Configuration::deleteByName('TABBED_SUBCAT_HIDDEN')
            || !Configuration::deleteByName('TABBED_SHOW_ON_PAGES')) {
            return false;
        }
        $this->clearCache();

        return true;
    }

    private function clearCache()
    {
        parent::_clearCache('tabbedcathome.tpl');
    }

    public function hookCategoryAddition()
    {
        $this->clearCache();
    }

    public function hookCategoryUpdate()
    {
        $this->clearCache();
    }

    public function hookCategoryDeletion()
    {
        $this->clearCache();
    }

    public function hookHeader()
    {
        $this->context->controller->addCSS(($this->_path).'views/css/tabbedcathome.css', 'all');
        $this->context->controller->addJS($this->_path.'views/js/isotope.pkgd.min.js');
        $this->context->controller->addJS($this->_path.'views/js/tabbedcathome.js');
    }

    public function hookdisplayTopColumn()
    {
        $controllerId = $this->context->controller->php_self;
        if ($this->isDisplayOnHome() && $controllerId == 'index') {
            return $this->displayTabbedCats();
        } else {
            return null;
        }
    }

    public function hookdisplayHome($params)
    {
        if (!$this->isDisplayOnHome()) {
            return null;
        }
        return $this->hookdisplayTopColumn($params);
    }

    public function hookdisplayHomeTab()
    {
        return $this->display(__FILE__, 'tab.tpl', $this->getCacheId('tabbedcathome-tab'));
    }

    public function hookdisplayHomeTabContent($params)
    {
        return $this->displayTabbedCats('tab_content.tpl');
    }
    
    public function hookdisplayFooterProduct($params)
    {
        if (!$this->isDisplayOnProduct()) {
            return null;
        }
        return $this->displayTabbedCats();
    }
    
    public function displayTabbedCats($view = 'tabbedcathome.tpl')
    {
        if (!$this->isCached($view, $this->getCacheId('tabbedcathome'))) {
            $id_lang = (int)Context::getContext()->language->id;
            $main_categories = Category::getHomeCategories($id_lang);

            $cat_hide = explode(',', Configuration::get('TABBED_CAT_HIDDEN'));
            $subcat_hide = explode(',', Configuration::get('TABBED_SUBCAT_HIDDEN'));

            $showmaincategories = array();
            $subcategories = array();

            foreach ($main_categories as $maincategory) {
                if (!in_array($maincategory['id_category'], $cat_hide)) {
                    $showmaincategories[] = $maincategory;
                }
            }

            $main_categories = $showmaincategories;

            if (!Configuration::get('TABBED_CAT_ALL_HIDE')) {
                foreach ($main_categories as $maincategory) {
                    if (!empty($cat_hide) && !in_array($maincategory['id_category'], $cat_hide)) {
                        $subcategories[] = new Category($maincategory['id_category'], $id_lang);
                    }
                }
            }

            foreach ($main_categories as $maincategory) {
                $sub_categories = Category::getChildren($maincategory['id_category'], $id_lang);
                if (!empty($sub_categories)) {
                    foreach ($sub_categories as $subcategory) {
                        if (!empty($subcat_hide) && !in_array($subcategory['id_category'], $subcat_hide)) {
                            $subcategories[] = new Category($subcategory['id_category'], $id_lang);

                            if (Configuration::get('TABBED_SUBSUBCAT')) {
                                $subsub_categories = Category::getChildren($subcategory['id_category'], $id_lang);
                                if (!empty($subsub_categories)) {
                                    foreach ($subsub_categories as $subsubcategory) {
                                        if (!empty($subcat_hide) && !in_array($subsubcategory['id_category'], $subcat_hide)) {
                                            $subsubcat = new Category($subsubcategory['id_category'], $id_lang);
                                            $subsubcat->id_parent = $maincategory['id_category'];
                                            $subcategories[] = $subsubcat;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $image_types = ImageType::getImagesTypes('categories');
            foreach ($image_types as $image_type) {
                if ($image_type['id_image_type'] == Configuration::get('TABBED_IMG')) {
                    $imagetype = $image_type['name'];
                }
            }

            $this->smarty->assign(array(
                        'col_nr' => Configuration::get('TABBED_CAT_NBR'),
                        'cat_img' => $imagetype,
                        'cat_title' => Configuration::get('TABBED_TITLE'),
                        'cat_tab' => Configuration::get('TABBED_ALL'),
                        'cat_all' => Configuration::get('TABBED_CAT_ALL'),
                        'cat_default' => Configuration::get('TABBED_CAT_DEFAULT'),
                        'cat_desc_length' => Configuration::get('TABBED_DESC_LNG'),
                        'cat_desc' => Configuration::get('TABBED_CAT_DESC'),
                        'maincategories' => $main_categories,
                        'subcategories' => $subcategories,
                ));
        }
        return $this->display(__FILE__, $view);
    }

    public function getContent()
    {
        $output_html = '';
        $errors = array();
        if (Tools::isSubmit('submitTabbedCatories')) {
            $cat_title = (int) Tools::getValue('TABBED_TITLE');
            $cat_tab = (int) Tools::getValue('TABBED_ALL');
            $cat_all = (int) Tools::getValue('TABBED_CAT_ALL');
            $cat_all_hide = (int) Tools::getValue('TABBED_CAT_ALL_HIDE');
            $cat_default = (int) Tools::getValue('TABBED_CAT_DEFAULT');
            $cat_subsubcat = (int) Tools::getValue('TABBED_SUBSUBCAT');
            $cat_desc = (int) Tools::getValue('TABBED_CAT_DESC');
            $cat_desc_length = (int) Tools::getValue('TABBED_DESC_LNG');
            $cat_nbr = (int) Tools::getValue('TABBED_CAT_NBR');
            $cat_img = (string) Tools::getValue('TABBED_IMG');
            if ($cat_hide = Tools::getValue('TABBED_CAT_HIDDEN')) {
                $cat_hide = implode(',', $cat_hide);
            }
            if ($subcat_hide = Tools::getValue('TABBED_SUBCAT_HIDDEN')) {
                $subcat_hide = implode(',', $subcat_hide);
            }
            if ($show_on_pages = Tools::getValue('TABBED_SHOW_ON_PAGES')) {
                $show_on_pages = implode(',', $show_on_pages);
            }

            if ($cat_nbr == 0 || !Validate::isUnsignedInt($cat_nbr)) {
                $errors[] = $this->l('There is an invalid number of columns number.');
            }

            if ($cat_desc_length == 0 || !Validate::isUnsignedInt($cat_desc_length)) {
                $errors[] = $this->l('There is an invalid number for subcategory description length.');
            }

            if (empty($errors)) {
                Configuration::updateValue('TABBED_TITLE', $cat_title);
                Configuration::updateValue('TABBED_ALL', $cat_tab);
                Configuration::updateValue('TABBED_CAT_ALL', $cat_all);
                Configuration::updateValue('TABBED_CAT_ALL_HIDE', $cat_all_hide);
                Configuration::updateValue('TABBED_CAT_DEFAULT', $cat_default);
                Configuration::updateValue('TABBED_SUBSUBCAT', $cat_subsubcat);
                Configuration::updateValue('TABBED_CAT_DESC', $cat_desc);
                Configuration::updateValue('TABBED_DESC_LNG', $cat_desc_length);
                Configuration::updateValue('TABBED_CAT_NBR', $cat_nbr);
                Configuration::updateValue('TABBED_IMG', $cat_img);
                Configuration::updateValue('TABBED_CAT_HIDDEN', $cat_hide);
                Configuration::updateValue('TABBED_SUBCAT_HIDDEN', $subcat_hide);
                Configuration::updateValue('TABBED_SHOW_ON_PAGES', $show_on_pages);

                $this->clearCache();
            }
            if (isset($errors) && count($errors)) {
                $output_html .= $this->displayError(implode('<br />', $errors));
            } else {
                $output_html .= $this->displayConfirmation($this->l('Your settings have been saved.'));
                $this->clearCache();
            }
        }

        return $output_html.$this->renderForm();
    }

    public function renderForm()
    {
        $id_lang = (int) Context::getContext()->language->id;
        $root_category = Category::getRootCategory($id_lang);
        $main_categories = Category::getChildren($root_category->id_category, $id_lang);
        $cat_hide = explode(',', Configuration::get('TABBED_CAT_HIDDEN'));
        $default_categories = $main_categories;
        array_unshift($default_categories, array('id_category' => 0, 'name' => $this->l('All Categories')));

        $subcategories = array();
        foreach ($main_categories as $category) {
            foreach (Category::getChildren($category['id_category'], $id_lang) as $subcat) {
                $subcategories[] = $subcat;
            }
        }
        $subcat_hide = explode(',', Configuration::get('TABBED_SUBCAT_HIDDEN'));
        $show_on_pages = explode(',', Configuration::get('TABBED_SHOW_ON_PAGES'));
        $image_types = ImageType::getImagesTypes('categories');
        
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                                        array(
                        'type' => 'tabbed_show_on_pages',
                        'label' => $this->l('Select pages to show module'),
                        'name' => 'TABBED_SHOW_ON_PAGES[]',
                        'desc' => $this->l('Press CRTL to make a multiple selection'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show module title'),
                        'name' => 'TABBED_TITLE',
                        'values' => array(
                                    array(
                                        'id' => 'tab_title_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled'),
                                    ),
                                    array(
                                        'id' => 'tab_title_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled'),
                                    ),
                                ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show All Categories without tabs'),
                        'name' => 'TABBED_ALL',
                        'class' => 'tab_all',
                        'values' => array(
                                    array(
                                        'id' => 'tab_all_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled'),
                                    ),
                                    array(
                                        'id' => 'tab_all_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled'),
                                    ),
                                ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show All Categories tab'),
                        'name' => 'TABBED_CAT_ALL',
                        'class' => 'tab_cat_all',
                        'values' => array(
                                    array(
                                        'id' => 'tab_cat_all_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled'),
                                    ),
                                    array(
                                        'id' => 'tab_cat_all_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled'),
                                    ),
                                ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Choose default active tab'),
                        'name' => 'TABBED_CAT_DEFAULT',
                        'class' => 'tabcategories_default',
                        'options' => array(
                            'query' => $default_categories,
                            'id' => 'id_category',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Hide main categories under All Categories'),
                        'name' => 'TABBED_CAT_ALL_HIDE',
                        'values' => array(
                                    array(
                                        'id' => 'tab_cat_all_hide_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled'),
                                    ),
                                    array(
                                        'id' => 'tab_cat_all_hide_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled'),
                                    ),
                                ),
                    ),
                    array(
                        'type' => 'tabbed_cat_hidden',
                        'label' => $this->l('Select main categories to hide'),
                        'name' => 'TABBED_CAT_HIDDEN[]',
                        'desc' => $this->l('Press CRTL to make a multiple selection'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Display sub-subcategories'),
                        'name' => 'TABBED_SUBSUBCAT',
                        'values' => array(
                                    array(
                                        'id' => 'tab_subsubcat_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled'),
                                    ),
                                    array(
                                        'id' => 'tab_subsubcat_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled'),
                                    ),
                                ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Show subcategory description'),
                        'name' => 'TABBED_CAT_DESC',
                        'values' => array(
                                    array(
                                        'id' => 'tab_cat_desc_on',
                                        'value' => 1,
                                        'label' => $this->l('Enabled'),
                                    ),
                                    array(
                                        'id' => 'tab_cat_desc_off',
                                        'value' => 0,
                                        'label' => $this->l('Disabled'),
                                    ),
                                ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Subcategory description length'),
                        'name' => 'TABBED_DESC_LNG',
                        'size' => 3,
                        'class' => 'fixed-width-xs',
                        'desc' => $this->l('Default is 100'),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Subcategory columns'),
                        'name' => 'TABBED_CAT_NBR',
                        'desc' => $this->l('Default is 4'),
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 2,
                                    'name' => '2',
                                ),
                                array(
                                    'id' => 3,
                                    'name' => '3',
                                ),
                                array(
                                    'id' => 4,
                                    'name' => '4',
                                ),
                                array(
                                    'id' => 5,
                                    'name' => '5',
                                ),
                                array(
                                    'id' => 6,
                                    'name' => '6',
                                ),
                            ),
                            'id' => 'id',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Choose image type'),
                        'name' => 'TABBED_IMG',
                        'desc' => $this->l('Image type can be added or updated at')
                                .' <a href="'.$this->context->link->getAdminLink('AdminImages')
                                .'" target="_blank">'.$this->l('Preferences-> Images').'</a>',
                        'options' => array(
                            'query' => $image_types,
                            'id' => 'id_image_type',
                            'name' => 'name',
                        ),
                    ),
                    array(
                        'type' => 'tabbed_subcat_hidden',
                        'label' => $this->l('Select subcategories to hide'),
                        'name' => 'TABBED_SUBCAT_HIDDEN[]',
                        'desc' => $this->l('Press CRTL to make a multiple selection'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTabbedCatories';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
                                    .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'main_categories' => $main_categories,
            'cat_hide' => $cat_hide,
            'subcategories' => $subcategories,
            'subcat_hide' => $subcat_hide,
                        'pages' => $this->pageList,
                        'show_on_pages' => $show_on_pages,
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'TABBED_TITLE' => Tools::getValue('TABBED_TITLE', Configuration::get('TABBED_TITLE')),
            'TABBED_ALL' => Tools::getValue('TABBED_ALL', Configuration::get('TABBED_ALL')),
            'TABBED_CAT_ALL' => Tools::getValue('TABBED_CAT_ALL', Configuration::get('TABBED_CAT_ALL')),
            'TABBED_CAT_ALL_HIDE' => Tools::getValue('TABBED_CAT_ALL_HIDE', Configuration::get('TABBED_CAT_ALL_HIDE')),
            'TABBED_CAT_DEFAULT' => Tools::getValue('TABBED_CAT_DEFAULT', Configuration::get('TABBED_CAT_DEFAULT')),
            'TABBED_SUBSUBCAT' => Tools::getValue('TABBED_SUBSUBCAT', Configuration::get('TABBED_SUBSUBCAT')),
            'TABBED_CAT_DESC' => Tools::getValue('TABBED_CAT_DESC', Configuration::get('TABBED_CAT_DESC')),
            'TABBED_DESC_LNG' => Tools::getValue('TABBED_DESC_LNG', Configuration::get('TABBED_DESC_LNG')),
            'TABBED_CAT_NBR' => Tools::getValue('TABBED_CAT_NBR', Configuration::get('TABBED_CAT_NBR')),
            'TABBED_IMG' => Tools::getValue('TABBED_IMG', Configuration::get('TABBED_IMG')),
            'TABBED_CAT_HIDDEN[]' => Tools::getValue('TABBED_CAT_HIDDEN', Configuration::get('TABBED_CAT_HIDDEN')),
            'TABBED_SUBCAT_HIDDEN[]' => Tools::getValue('TABBED_SUBCAT_HIDDEN', Configuration::get('TABBED_SUBCAT_HIDDEN')),
            'TABBED_SHOW_ON_PAGES[]' => Tools::getValue('TABBED_SHOW_ON_PAGES', Configuration::get('TABBED_SHOW_ON_PAGES')),
        );
    }
    
    public function is16()
    {
        if ((version_compare(_PS_VERSION_, '1.6.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.7.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function is17()
    {
        if ((version_compare(_PS_VERSION_, '1.7.0', '>=') === true)
                && (version_compare(_PS_VERSION_, '1.8.0', '<') === true)) {
            return true;
        }
        return false;
    }
    
    public function getPath()
    {
        return $this->_path;
    }
    
    public function getModuleBaseUrl()
    {
        return Tools::getShopDomainSsl(true, true).__PS_BASE_URI__ . 'modules/' . $this->name . '/';
    }
    
    public function isDisplayOnHome()
    {
        $param = Configuration::get('TABBED_SHOW_ON_PAGES');
        $value = explode(',', $param);
        return in_array(self::PAGE_HOME, $value);
    }
    
    public function isDisplayOnProduct()
    {
        $param = Configuration::get('TABBED_SHOW_ON_PAGES');
        $value = explode(',', $param);
        return in_array(self::PAGE_PRODUCT, $value);
    }
}
