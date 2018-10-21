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

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Ptm_Paymenticons extends Module implements WidgetInterface
{
    private $templateFile;

	public function __construct()
	{
		$this->name = 'ptm_paymenticons';
		$this->version = '1.2.0';
		$this->author = 'Presta Theme Maker';
		$this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Payment Icons in the Footer');
        $this->description = $this->l('Display payment icons in the footer.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:ptm_paymenticons/views/templates/hook/ptm_paymenticons.tpl';
    }

    public function install()
    {
        return (parent::install() &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayHeader') &&
            $this->registerHook('displayFooterAfter') &&
            $this->registerHook('actionObjectLanguageAddAfter') &&
            $this->installFixtures());
    }

    public function hookActionObjectLanguageAddAfter($params)
    {
        return $this->installFixture((int)$params['object']->id, Configuration::get('PTM_PAYMENTICONS_IMG', (int)Configuration::get('PS_LANG_DEFAULT')));
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        if ((Tools::getValue('configure') && Tools::getValue('configure') == $this->name)) {
            $this->context->controller->addCSS($this->_path.'views/css/ptm_bk.css');
        }
    }

    public function hookdisplayHeader($params)
    {
        $this->context->controller->registerStylesheet('modules-ptmpaymenticons', 'modules/'.$this->name.'/views/css/paymenticons_main.css', ['position' => 'bottom', 'priority' => 150]);
    }

    protected function installFixtures()
    {
        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $this->installFixture((int)$lang['id_lang'], 'payments.png');
        }

        return true;
    }

    protected function installFixture($id_lang, $image = null)
    {
        $values['PTM_PAYMENTICONS_IMG'][(int)$id_lang] = $image;

        Configuration::updateValue('PTM_PAYMENTICONS_IMG', $values['PTM_PAYMENTICONS_IMG']);
    }

    public function uninstall()
    {
        Configuration::deleteByName('PTM_PAYMENTICONS_IMG');

        return parent::uninstall();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitStoreConf')) {
            $languages = Language::getLanguages(false);
            $values = array();
            $update_images_values = false;

            foreach ($languages as $lang) {
                if (isset($_FILES['PTM_PAYMENTICONS_IMG_'.$lang['id_lang']])
                    && isset($_FILES['PTM_PAYMENTICONS_IMG_'.$lang['id_lang']]['tmp_name'])
                    && !empty($_FILES['PTM_PAYMENTICONS_IMG_'.$lang['id_lang']]['tmp_name'])) {
                    if ($error = ImageManager::validateUpload($_FILES['PTM_PAYMENTICONS_IMG_'.$lang['id_lang']], 4000000)) {
                        return $error;
                    } else {
                        $ext = substr($_FILES['PTM_PAYMENTICONS_IMG_'.$lang['id_lang']]['name'], strrpos($_FILES['PTM_PAYMENTICONS_IMG_'.$lang['id_lang']]['name'], '.') + 1);
                        $file_name = md5($_FILES['PTM_PAYMENTICONS_IMG_'.$lang['id_lang']]['name']).'.'.$ext;

                        if (!move_uploaded_file($_FILES['PTM_PAYMENTICONS_IMG_'.$lang['id_lang']]['tmp_name'], dirname(__FILE__).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$file_name)) {
                            return $this->displayError($this->trans('An error occurred while attempting to upload the file.', array(), 'Admin.Notifications.Error'));
                        } else {
                            if (Configuration::hasContext('PTM_PAYMENTICONS_IMG', $lang['id_lang'], Shop::getContext())
                                && Configuration::get('PTM_PAYMENTICONS_IMG', $lang['id_lang']) != $file_name) {
                                @unlink(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'views'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'uploads' . DIRECTORY_SEPARATOR . Configuration::get('PTM_PAYMENTICONS_IMG', $lang['id_lang']));
                            }

                            $values['PTM_PAYMENTICONS_IMG'][$lang['id_lang']] = $file_name;
                        }
                    }

                    $update_images_values = true;
                }
            }

            if ($update_images_values) {
                Configuration::updateValue('PTM_PAYMENTICONS_IMG', $values['PTM_PAYMENTICONS_IMG']);
            }

            $this->_clearCache($this->templateFile);

            return $this->displayConfirmation($this->trans('The settings have been updated.', array(), 'Admin.Notifications.Success'));
        }

        return '';
    }

    public function getContent()
    {
        return $this->postProcess().$this->renderForm();
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->trans('Settings', array(), 'Admin.Global'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'file_lang',
                        'label' => $this->l('Payment icons'),
                        'name' => 'PTM_PAYMENTICONS_IMG',
                        'desc' => $this->l('Upload an image for your payment icons in the footer. The recommended dimensions are around 300 x 35px.'),
                        'lang' => true,
                    )
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions')
                )
            ),
        );

        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitStoreConf';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'uri' => $this->getPathUri(),
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        $languages = Language::getLanguages(false);
        $fields = array();

        foreach ($languages as $lang) {
            $fields['PTM_PAYMENTICONS_IMG'][$lang['id_lang']] = Tools::getValue('PTM_PAYMENTICONS_IMG_'.$lang['id_lang'], Configuration::get('PTM_PAYMENTICONS_IMG', $lang['id_lang']));
        }

        return $fields;
    }

    public function renderWidget($hookName, array $params)
    {
        if (!$this->isCached($this->templateFile, $this->getCacheId('ptm_paymenticons'))) {
            $this->smarty->assign($this->getWidgetVariables($hookName, $params));
        }

        return $this->fetch($this->templateFile, $this->getCacheId('ptm_paymenticons'));
    }

    public function getWidgetVariables($hookName, array $params)
    {
        $imgname = Configuration::get('PTM_PAYMENTICONS_IMG', $this->context->language->id);

        if ($imgname && file_exists(_PS_MODULE_DIR_.$this->name.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.$imgname)) {
            $this->smarty->assign('paymenticons_img', $this->context->link->protocol_content . Tools::getMediaServer($imgname) . $this->_path . 'views/img/uploads/' . $imgname);
        }
    }
}
