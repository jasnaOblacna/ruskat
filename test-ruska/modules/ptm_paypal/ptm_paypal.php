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

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

require_once dirname(__FILE__) .'/src/Ptm_PaypalRepository.php';
require_once dirname(__FILE__) .'/src/IPN_Verifier.php';
require_once dirname(__FILE__) .'/src/PaypalButtonManager.php';
require_once dirname(__FILE__) .'/src/Ptm_PayPalRouter.php';
require_once dirname(__FILE__) .'/src/PTMPLogger.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ptm_Paypal extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();
    public $repository;
    public $ptm_upgraded = true;
    public $checkName;
    public $address;
    public $extra_mail_vars;
    private $_uploads_path;
    // const DEBUG_MODE = true;

    /** Sandbox URL */
    const PAYPAL_SANBOX_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    /** Producttion URL */
    const PAYPAL_LIVE_URL = 'https://www.paypal.com/cgi-bin/webscr';
    
    public function __construct()
    {
        $this->name = 'ptm_paypal';
        $this->tab = 'payments_gateways';
        $this->version = '2.1.0';
        $this->author = 'PrestaBuilder';
        $this->controllers = array('payment', 'success', 'notify', 'cancel');

        $this->checkName = 'YASPBEL';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Payments by Paypal');
        $this->description = $this->l('This module allows you to accept payments by PayPal.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete these details?');
        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
        $this->setPtmPaypalFrontTemplates();
        // initiate repository
        $this->repository = new Ptm_PaypalRepository(Db::getInstance());
        $this->_uploads_path = $this->local_path .'views/uploads/';
        $this->module_key = '7b8d3d80540e6d86dea376cf24c6a5bd';
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('moduleRoutes')
            && $this->registerHook('displayBackOfficeHeader')
            && $this->repository->createTables()
            && Configuration::updateValue('PTM_PAYPAL_EMAIL', 'your_paypal_email@')
            && Configuration::updateValue('PTM_PAYPAL_DISPLAY_PAGE', '')
            && Configuration::updateValue('PTM_PAYPAL_MODE', 0)
            && Configuration::updateValue('PTM_PAYPAL_IMMEDIATE_REDIRECTION', 1)
            && Configuration::updateValue('PTM_PAYPAL_SHIPPING_ADDRESS', 1)
            && Configuration::updateValue('PTM_PAYPAL_TOKEN', Tools::passwdGen(36))
            && Configuration::updateValue('PTM_PAYPAL_LOGGING_SYS', 0)
        ;
    }

    public function uninstall()
    {
        return parent::uninstall()
        ;
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        if (!Tools::getValue('configure') || Tools::getValue('configure') != $this->name) {
            return;
        }

        $this->context->controller->addCSS($this->_path.'views/css/ptm_bk.css');
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path.'views/js/ptm_paypal_bk.js');
    }

    private function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
           /* if (!Tools::getValue('PTM_PAYPAL_USERNAME')) {
                $this->_postErrors[] = $this->l('The "username" field is required.');
            } elseif (!Tools::getValue('PTM_PAYPAL_PSWD')) {
                $this->_postErrors[] = $this->l('The "password" field is required.');
            } elseif (!Tools::getValue('PTM_PAYPAL_SIG')) {
                $this->_postErrors[] = $this->l('The "signature" field is required.');
            } else*/
            // check merchant email
            if (!Tools::getValue('PTM_PAYPAL_EMAIL')
                || !Validate::isEmail(Tools::getValue('PTM_PAYPAL_EMAIL'))) {
                $this->_postErrors[] = $this->l('The "PayPal address" field is required and must be a valid email.');
            }
            // check payment logo
            if (isset($_FILES['PTM_PAYPAL_PAYMENT_LOGO']['tmp_name'])
                && !Tools::isEmpty($_FILES['PTM_PAYPAL_PAYMENT_LOGO']['tmp_name'])) {
                if ($_error = ImageManager::validateUpload($_FILES['PTM_PAYPAL_PAYMENT_LOGO'])) {
                    $this->_postErrors[] = $_error;
                }
            }
        }
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            /*Configuration::updateValue('PTM_PAYPAL_USERNAME', Tools::getValue('PTM_PAYPAL_USERNAME'));
            Configuration::updateValue('PTM_PAYPAL_PSWD', Tools::getValue('PTM_PAYPAL_PSWD'));
            Configuration::updateValue('PTM_PAYPAL_SIG', Tools::getValue('PTM_PAYPAL_SIG'));*/
            Configuration::updateValue('PTM_PAYPAL_DISPLAY_PAGE', Tools::getValue('PTM_PAYPAL_DISPLAY_PAGE'));
            Configuration::updateValue('PTM_PAYPAL_MODE', (int)Tools::getValue('PTM_PAYPAL_MODE'));
            Configuration::updateValue('PTM_PAYPAL_IMMEDIATE_REDIRECTION', (int)Tools::getValue('PTM_PAYPAL_IMMEDIATE_REDIRECTION'));
            Configuration::updateValue('PTM_PAYPAL_SHIPPING_ADDRESS', Tools::getValue('PTM_PAYPAL_SHIPPING_ADDRESS'));
            Configuration::updateValue('PTM_PAYPAL_EMAIL', Tools::getValue('PTM_PAYPAL_EMAIL'));
            Configuration::updateValue('PTM_PAYPAL_LOGGING_SYS', (int)Tools::getValue('PTM_PAYPAL_LOGGING_SYS'));

            /* Uploads paypal payment logo */
            $logo = 'PTM_PAYPAL_PAYMENT_LOGO';
            if (isset($_FILES[$logo]) && isset($_FILES[$logo]['tmp_name'])
                && !empty($_FILES[$logo]['tmp_name'])
            ) {
                $img_type  = pathinfo($_FILES[$logo]['name'], PATHINFO_EXTENSION);
                $salt      = sha1(microtime());
                $logoName  = $salt .'.'. $img_type;
                $logoPath  = $this->_uploads_path . $logoName;

                if (!move_uploaded_file($_FILES[$logo]['tmp_name'], $logoPath)) {
                    $this->_html .= $this->displayError($this->l('Paypal payment logo could not be uploaded.'));
                } else {
                    $old_logo = Configuration::get('PTM_PAYPAL_PAYMENT_LOGO');
                    if (Configuration::updateValue('PTM_PAYPAL_PAYMENT_LOGO', $logoName)) {
                        @unlink($this->_uploads_path . $old_logo);
                    }
                }
            }
        }

        if (Tools::isSubmit('welcomeBtnSubmit')) {
            // welcome page settings
            $langs = Language::getLanguages(false);
            $wp = array();
            foreach ($langs as $lang) {
                // prepare data
                // $wp['PTM_PAYPAL_WP_BTN'][$lang['id_lang']] = Tools::getValue('PTM_PAYPAL_WP_BTN_'. $lang['id_lang']);
                $wp['PTM_PAYPAL_WP_TITLE'][$lang['id_lang']] = Tools::getValue('PTM_PAYPAL_WP_TITLE_'. $lang['id_lang']);
                $wp['PTM_PAYPAL_WP_CONTENT'][$lang['id_lang']] = Tools::getValue('PTM_PAYPAL_WP_CONTENT_'. $lang['id_lang']);
                // update data
                // Configuration::updateValue('PTM_PAYPAL_WP_BTN', $wp['PTM_PAYPAL_WP_BTN']);
                Configuration::updateValue('PTM_PAYPAL_WP_CONTENT', $wp['PTM_PAYPAL_WP_CONTENT']);
                Configuration::updateValue('PTM_PAYPAL_WP_TITLE', $wp['PTM_PAYPAL_WP_TITLE']);
            }
        }

        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    public function getContent()
    {
        $this->_html = '';

        if (Tools::isSubmit('btnSubmit') || Tools::isSubmit('welcomeBtnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        }

        $this->_html .= $this->renderForm() . $this->renderWelcomePageForm();

        return $this->_html;
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        $newOption = new PaymentOption();

        if ($paypal_logo = Configuration::get('PTM_PAYPAL_PAYMENT_LOGO')) {
            $newOption->setLogo($this->_path .'views/uploads/'. $paypal_logo);
        }

        $newOption->setCallToActionText($this->l('Pay by PayPal'))
                ->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), true))
                ->setAdditionalInformation($this->fetch('module:ptm_paypal/views/templates/front/payment_infos.tpl'));

        return array($newOption);
    }

    public function hookDisplayHeader($params)
    {
        $php_self = $this->context->controller->php_self;
        if ($php_self && $php_self == 'ptm_paypal_payment') {
            $this->context->controller->registerJavascript('modules-ptm-paypal', 'modules/'.$this->name.'/views/js/ptm_paypal.js', array('position' => 'bottom', 'priority' => 190));
            $this->context->controller->registerStylesheet('modules-ptm-paypal', 'modules/'.$this->name.'/views/css/paypal.css', array('position' => 'bottom', 'priority' => 150));
        }

        if ($php_self && $php_self == 'ptm_paypal_success') {
            $this->context->controller->registerJavascript('modules-ptm-paypal-success', 'modules/'.$this->name.'/views/js/ptm_paypal_success.js', array('position' => 'bottom', 'priority' => 200));
        }
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('PayPal Configuration'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('PayPal email address'),
                        'name' => 'PTM_PAYPAL_EMAIL',
                        'required' => true
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('Username'),
                        'name' => 'PTM_PAYPAL_USERNAME',
                        'required' => false
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('Password'),
                        'name' => 'PTM_PAYPAL_PSWD',
                        'required' => false
                    ),
                    array(
                        'type' => 'hidden',
                        'label' => $this->l('Signature'),
                        'name' => 'PTM_PAYPAL_SIG',
                        'required' => false
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live account (YES) / Sandbox (NO)'),
                        'name' => 'PTM_PAYPAL_MODE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Redirect user to Paypal immediately'),
                        'desc' => $this->l('If "NO" is selected, the user will see notification about the redirection first'),
                        'name' => 'PTM_PAYPAL_IMMEDIATE_REDIRECTION',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Send shipping address also to Paypal'),
                        'desc' => $this->l('Even if "NO" is selected, your eshop will save the shipping address'),
                        'name' => 'PTM_PAYPAL_SHIPPING_ADDRESS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Turn on/off logging'),
                        'name' => 'PTM_PAYPAL_LOGGING_SYS',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No')
                            )
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Payment pages'),
                        'name' => 'PTM_PAYPAL_DISPLAY_PAGE',
                        'desc' => $this->l('Optional: you can define the name of your payment page (if you have created one on your PayPal)')
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Paypal payment logo'),
                        'name' => 'PTM_PAYPAL_PAYMENT_LOGO',
                        'form_group_class' => 'paypal_logo_wrapper',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
        );
        $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'display_paypal_logo');
        $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'ptm_paypal_ajax_url');
        $fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'ptm_paypal_gen_token');

        $this->fields_form = array();

        return $helper->generateForm(array($fields_form));
    }

    public function renderWelcomePageForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Thank Page Configuration'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Title'),
                        'name' => 'PTM_PAYPAL_WP_TITLE',
                        'lang' => true
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->l('Content'),
                        'name' => 'PTM_PAYPAL_WP_CONTENT',
                        'autoload_rte' => true,
                        'lang' => true
                    ),
                    /*array(
                        'type' => 'hidden',
                        'label' => $this->l('Button text (leading to the list of all orders)'),
                        'name' => 'PTM_PAYPAL_WP_BTN',
                        'lang' => true
                    ),*/
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            )
        );

        $language   = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper     = new HelperForm();
        $helper->show_toolbar = false;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'welcomeBtnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->default_form_language = $language->id;
        $helper->tpl_vars = array(
            'language' => array(
                'id_lang' => $language->id,
                'iso_code' => $language->iso_code
            ),
            'fields_value' => $this->getWPConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        $this->fields_form = array();

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        $paypal_logo = Configuration::get('PTM_PAYPAL_PAYMENT_LOGO');
        return array(
            'PTM_PAYPAL_USERNAME' => Tools::getValue('PTM_PAYPAL_USERNAME', Configuration::get('PTM_PAYPAL_USERNAME')),
            'PTM_PAYPAL_PSWD' => Tools::getValue('PTM_PAYPAL_PSWD', Configuration::get('PTM_PAYPAL_PSWD')),
            'PTM_PAYPAL_SIG' => Tools::getValue('PTM_PAYPAL_SIG', Configuration::get('PTM_PAYPAL_SIG')),
            'PTM_PAYPAL_MODE' => Tools::getValue('PTM_PAYPAL_MODE', (int)Configuration::get('PTM_PAYPAL_MODE')),
            'PTM_PAYPAL_IMMEDIATE_REDIRECTION' => Tools::getValue('PTM_PAYPAL_IMMEDIATE_REDIRECTION', (int)Configuration::get('PTM_PAYPAL_IMMEDIATE_REDIRECTION')),
            'PTM_PAYPAL_SHIPPING_ADDRESS' => Tools::getValue('PTM_PAYPAL_SHIPPING_ADDRESS', Configuration::get('PTM_PAYPAL_SHIPPING_ADDRESS')),
            'PTM_PAYPAL_DISPLAY_PAGE' => Tools::getValue('PTM_PAYPAL_DISPLAY_PAGE', Configuration::get('PTM_PAYPAL_DISPLAY_PAGE')),
            'PTM_PAYPAL_EMAIL' => Tools::getValue('PTM_PAYPAL_EMAIL', Configuration::get('PTM_PAYPAL_EMAIL')),
            'PTM_PAYPAL_PAYMENT_LOGO' => '',
            'PTM_PAYPAL_LOGGING_SYS' => Tools::getValue('PTM_PAYPAL_LOGGING_SYS', (int)Configuration::get('PTM_PAYPAL_LOGGING_SYS')),
            'display_paypal_logo' => ($paypal_logo ? $this->_path .'views/uploads/'. $paypal_logo : ''),
            'ptm_paypal_ajax_url' => $this->context->link->getModuleLink('ptm_paypal', 'ajax'),
            'ptm_paypal_gen_token' => Configuration::get('PTM_PAYPAL_TOKEN')
        );
    }

    public function getWPConfigFieldsValues()
    {
        $langs = Language::getLanguages(false);
        $data  = array();

        foreach ($langs as $lang) {
            // $data['PTM_PAYPAL_WP_BTN'][$lang['id_lang']] = '';
            $data['PTM_PAYPAL_WP_TITLE'][$lang['id_lang']] = Tools::getValue('PTM_PAYPAL_WP_TITLE_'. $lang['id_lang'], Configuration::get('PTM_PAYPAL_WP_TITLE', $lang['id_lang']));
            $data['PTM_PAYPAL_WP_CONTENT'][$lang['id_lang']] = Tools::getValue('PTM_PAYPAL_WP_CONTENT_'. $lang['id_lang'], Configuration::get('PTM_PAYPAL_WP_CONTENT', $lang['id_lang']));
        }

        return $data;
    }

    public function hookModuleRoutes($params)
    {
        $routes = array();

        $routes['module-ptm_paypal-success'] = Ptm_PayPalRouter::successRouter();
        $routes['module-ptm_paypal-payment'] = Ptm_PayPalRouter::paymentRouter();
        $routes['module-ptm_paypal-cancel']  = Ptm_PayPalRouter::cancelRouter();
        $routes['module-ptm_paypal-notify']  = Ptm_PayPalRouter::notifyRouter();

        return $routes;
    }

    private function setPtmPaypalFrontTemplates()
    {
        $tpl_dirs = Context::getContext()->smarty->getTemplateDir();
        $tpl_dirs[] = $this->local_path . 'views/templates/front/';
        Context::getContext()->smarty->setTemplateDir($tpl_dirs);
    }

    /**
     * Remove paypal logo
     */
    public function removeLogo()
    {
        if ($logo = Configuration::get('PTM_PAYPAL_PAYMENT_LOGO')) {
            Configuration::updateValue('PTM_PAYPAL_PAYMENT_LOGO', '');
            @unlink($this->_uploads_path . $logo);

            return true;
        }

        return false;
    }
}
