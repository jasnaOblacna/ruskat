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

if (!defined('_PS_VERSION_')) {
    exit;
}

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\PrestaShop\Core\Addon\Theme\Theme;
use PrestaShop\PrestaShop\Core\Addon\Theme\ThemeManagerBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;
use PrestaShop\PrestaShop\Core\Module\HookConfigurator;
use PrestaShop\PrestaShop\Core\Module\HookRepository;
use PrestaShop\PrestaShop\Adapter\Hook\HookInformationProvider;

// include utils class 
include_once dirname(__FILE__) .'/classes/PTM_CCUtils.php';

class Ptm_Controlcenter extends Module
{
    private $fileSystem;
    private $moduleManager;
    private $hookConfigurator;
    private $_themeName;
    protected $_errors;
    private static $skipped_directories = ["uploads", "translations"];
    private static $hook_admin_controllers = [];
    private $_html = '';
    private $_main_dir;
    private $_sandbox;
    protected $_sandbox_dir;

    public function __construct(
        Filesystem $file_system, 
        Finder $finder)
    {
        $this->name = 'ptm_controlcenter';
        $this->tab = 'administration';
        $this->version = '2.2.0';
        $this->author = 'PrestaBuilder';
        $this->need_instance = 0;
        $this->secure_key = Tools::encrypt($this->name);
        $this->bootstrap  = true;
        $this->controllers = array('ajax');

        parent::__construct();

        $this->displayName = $this->l('Control center');
        $this->description = $this->l('Easily install a new theme from PrestaBuilder, or update your current theme (and PrestaBuilder modules) without overriding your current hooks.');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

        $this->_main_dir      = realpath(dirname(__FILE__)) .'/';
        $this->_sandbox       = '_sandbox';
        $this->fileSystem     = $file_system;
        $moduleManagerBuilder = ModuleManagerBuilder::getInstance();
        $this->moduleManager  = $moduleManagerBuilder->build();
        $this->finder       = $finder;
        
        if ((int)Tools::getValue('ajax') && (Tools::getValue('action') && Tools::getValue('action') == 'checkUpdatesList')) {
            $this->themeManager = null;
        } else {
            if (isset($this->context->employee) && !empty($this->context->employee)) {
                $this->themeManager = (new ThemeManagerBuilder($this->context, Db::getInstance()))->build();
            } else {
                $this->themeManager = null;
            }
        }
        $this->hookConfigurator = new HookConfigurator(new HookRepository(new HookInformationProvider(), $this->context->shop, Db::getInstance()));
    }

    /**
     * @see Module::install()
     */
    public function install()
    {
        if (parent::install() 
            && $this->registerHook('displayBackOfficeHeader') 
            && $this->registerHook('displayHeader') 
            && $this->registerHook('displayBackOfficeFooter')
            && $this->registerHook('actionAdminThemesControllerUpdate_optionsAfter')) {
            Configuration::updateValue('PTM_CONTROLCENTER_QUICKLINKS', 1);
            Configuration::updateValue('PTM_CONTROLCENTER_LAST_MODS_UPDATED', strtotime('-2 hours'));
            Configuration::updateValue('PTM_CONTROLCENTER_LASTUPDATED', time());

            PTM_CCUtils::createSidebarTabs();
        }
            return true;
        return false;
    }
    
    /**
     * @see Module::uninstall()
     */
    public function uninstall()
    {
        if (parent::uninstall())
            return true;
        return false;
    }

    public function getContent()
    {
        $this->_errors = [];
        // add top tabs to the configuration page
        $this->_html = $this->_addConfigTabs();
        $redirectLink = $this->context->link->getAdminLink('AdminModules', true).'&configure='. $this->name;

        if (Tools::isSubmit('submitSettings')) {
            $display_links = (int)Tools::getValue('PTM_CONTROLCENTER_QUICKLINKS_settings');
            Configuration::updateValue('PTM_CONTROLCENTER_QUICKLINKS', $display_links);
            $ptm_cc_notif = 1;

            if ($display_links == 1) {
                PTM_CCUtils::createSidebarTabs();
            } else {
                PTM_CCUtils::deleteTabsByModuleName();
                $ptm_cc_notif = 2;
            }

            Tools::redirectAdmin($redirectLink . '&current_tab=3&ptm_cc_notif='. $ptm_cc_notif);
        } elseif (Tools::isSubmit('submitAddNewTheme')) {
            if (!Tools::getValue('add_theme_zip')) {
                $this->_errors[] = $this->displayError($this->l('You must select a theme.'));
            } else {
                $this->_validateProcessThemeFile();
            }

            if (count($this->_errors)) {
                foreach ($this->_errors as $error) {
                    $this->_html .= $this->displayError($error);
                }
            } else {
                if ((int)Tools::getValue('update_assets_upt_m')) {
                    $this->_updateAssets($this->_themeName);
                } else {
                    // remove assets
                    $this->_removeThemeAssets($this->_themeName);
                }

                if ((int)Tools::getValue('update_modules_upt_m')) {
                    $this->upgradePTMModules($this->_themeName);
                }

                $this->_html .= $this->displayConfirmation($this->l(sprintf('Your theme "%s" has been successfully uploaded', $this->_themeName)));
            }
        } elseif (Tools::isSubmit('submitUpdateThemeAndModules')) {
            if (!Tools::getValue('add_theme_zip')) {
                $this->_html .= $this->displayError($this->l('You must select a theme.'));
            } else {
                $this->_themeName = Tools::getValue('theme_name');
                $without_theme_opt = false;
                
                if ((int)Tools::getValue('update_theme_upt_m')) {
                    $this->_validateProcessThemeFile(true);
                } else {
                    $this->_validateProcessThemeFile(true, true);
                    $without_theme_opt = true;
                }

                if (count($this->_errors)) {
                    foreach ($this->_errors as $error) {
                        $this->_html .= $this->displayError($error);
                    }
                } else {
                    if ((int)Tools::getValue('update_assets_upt_m')) {
                        $this->_updateAssets($this->_themeName);
                    } else {
                        // remove assets
                        $this->_removeThemeAssets($this->_themeName);
                    }

                    if ((int)Tools::getValue('update_hooks_upt_m')) {
                        $hooks = PTM_CCUtils::parseThemeYmlFile($this->_themeName, 'global_settings.hooks.modules_to_hook');
                        $modulesToEnable = PTM_CCUtils::parseThemeYmlFile($this->_themeName, 'global_settings.modules.to_enable');

                        //enable modules defined in the config file and disable the rest
                        $this->_enableAndDisableModules($modulesToEnable);
                        $this->hookConfigurator->setHooksConfiguration($hooks);
                    }

                    if ((int)Tools::getValue('update_modules_upt_m')) {
                        $this->upgradePTMModules($this->_themeName);
                    }
                    
                    // remove temporary theme folder
                    if ($without_theme_opt) {
                        $this->fileSystem->remove(_PS_ALL_THEMES_DIR_ . $this->_themeName);
                        $this->_html .= $this->displayConfirmation($this->l(sprintf('Your theme assets have been successfully updated')));
                    } else {
                        $this->_html .= $this->displayConfirmation($this->l(sprintf('Your theme "%s" has been successfully updated', $this->_themeName)));
                    }

                    // update last updated theme
                    Configuration::updateValue('PTM_CONTROLCENTER_LASTUPDATED', time());

                    // Clear cache
                    Tools::clearAllCache();
                    Media::clearCache();
                    Tools::generateIndex();
                }
            }
        }
        $this->_themeName = null;

        if ($current_tab = (int)Tools::getValue('current_tab')) {
            if ($current_tab == 1) {
                $this->context->smarty->assign([
                    'render_form' => $this->addNewThemeForm(),
                    'show_themes_list' => true,
                    'show_modules_list' => true,
                    'modules_list' => PTM_CCUtils::getPTMModules(),
                    'themes_list' => PTM_CCUtils::getPTMListOfThemesWithPreviews(),
                ]);
            } elseif ($current_tab == 2) {
                $this->context->smarty->assign([
                    'render_form' => $this->updateThemeAndModulesForm(),
                    'show_themes_list' => true,
                    'show_modules_list' => true,
                    'themes_list' => PTM_CCUtils::getPTMListOfThemesWithPreviews(),
                    'modules_list' => PTM_CCUtils::getPTMModules(),
                ]);
            } else {
                $this->context->smarty->assign([
                    'render_form' => $this->settingsForm(),
                    'show_themes_list' => false,
                    'show_modules_list' => false
                ]);
            }
        } else {
            $current_tab = 1;
            $this->context->smarty->assign([
                'render_form' => $this->addNewThemeForm(),
                'show_themes_list' => true,
                'show_modules_list' => false,
                'themes_list' => PTM_CCUtils::getPTMListOfThemesWithPreviews(),
            ]);
        }

        $this->context->smarty->assign([
            'base_url'=> Tools::getCurrentUrlProtocolPrefix().Tools::getShopDomain() . __PS_BASE_URI__,
            'current_tab' => $current_tab,
            'activated_theme' => Tools::strtolower($this->context->shop->theme_name),
            'admin_theme_url' => $this->context->link->getAdminLink('AdminThemes')
        ]);

        if ($notif = Tools::getValue('ptm_cc_notif')) {
            switch ($notif) {
                case 1:
                    $this->_html .= $this->displayConfirmation($this->l('Quick links have been successfully created in the sidebar'));
                    break;
                case 2:
                    $this->_html .= $this->displayConfirmation($this->l('Quick links have been successfully removed in the sidebar'));
                    break;
            }
        }

        $this->_html .= $this->fetch($this->local_path .'views/templates/admin/configuration.tpl');
        return $this->_html;
    }

    /**
     * Validate and process theme file
     */
    private function _validateProcessThemeFile($is_update = false, $without_theme = false)
    {
        $filename  = Tools::getValue('add_theme_zip');
        $themePath = _PS_ALL_THEMES_DIR_ . $filename;

        if (!isset($filename) || Tools::isEmpty($filename)) {
            $this->_errors[] = $this->l('Please provide a valid theme');
            return false;
        }

        if ($this->fileSystem->exists($themePath)) {
            $this->fileSystem->remove($themePath);
        }

        $destination = $this->_processUploadFile($themePath);
    
        if (!Tools::isEmpty($destination)) {
            if ((filter_var($destination, FILTER_VALIDATE_URL))) {
                $destination = Tools::createFileFromUrl($destination);
            }

            if (preg_match('/\.zip$/', $destination)) {
                $this->_installFromZip($destination, $is_update, $without_theme);
            }
        }
        // remove the zip file
        $this->fileSystem->remove($destination);
    }

    private function _processUploadFile($dest)
    {
        if (!count($_FILES['add_theme_zip'])) {
            $this->_errors[] = $this->l('No file found.');
            return false;
        }

        switch ($_FILES['add_theme_zip']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->_errors[] = $this->l('The uploaded file is too large.');
                return false;
            default:
                $this->_errors[] = $this->l('Unknown error.');
                return false;
        }

        $tmp_name = $_FILES['add_theme_zip']['tmp_name'];
        $mimeType = false;
        $goodMimeType = false;

        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME);
            $mimeType = @finfo_file($finfo, $tmp_name);
            @finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mimeType = @mime_content_type($tmp_name);
        } elseif (function_exists('exec')) {
            $mimeType = trim(@exec('file -b --mime-type '.escapeshellarg($tmp_name)));
            if (!$mimeType) {
                $mimeType = trim(@exec('file --mime '.escapeshellarg($tmp_name)));
            }
            if (!$mimeType) {
                $mimeType = trim(@exec('file -bi '.escapeshellarg($tmp_name)));
            }
        }

        if (!empty($mimeType)) {
            preg_match('#application/zip#', $mimeType, $matches);
            if (!empty($matches)) {
                $goodMimeType = true;
            }
        }

        if (false === $goodMimeType) {
            $this->_errors[] = $this->l('Invalid file format.');
            return false;
        }

        $name = $_FILES['add_theme_zip']['name'];
        if (!Validate::isFileName($name)) {
            $dest = _PS_ALL_THEMES_DIR_.sha1_file($_FILES['add_theme_zip']['tmp_name']).'.zip';
        }

        if (!move_uploaded_file($_FILES['add_theme_zip']['tmp_name'], $dest)) {
            $this->_errors[] = $this->l('Failed to move theme file.');
            return false;
        }

        return $dest;
    }

    private function _installFromZip($source, $is_update = false, $without_theme = false)
    {
        $sandboxPath = $this->_getSandboxPath();
        Tools::ZipExtract($source, $sandboxPath);

        // dump new yml file if the theme name is not the same as the old one
        if ($is_update && !$without_theme) {
            PTM_CCUtils::dumpYmlFile($this->_themeName, $sandboxPath);
        }

        $theme_data = (new Parser())->parse(file_get_contents($sandboxPath.'/config/theme.yml'));
        $theme_data['directory'] = $sandboxPath;

        $theme = new Theme($theme_data);
        if (!PTM_CCUtils::isPTMTheme('', $sandboxPath .'/')) {
            $this->fileSystem->remove($sandboxPath);
            $this->_errors[] = $this->l('This theme is not a valid PTM theme.');
            return false;
        }

        if (!$this->_isValidTheme($theme)) {
            $this->fileSystem->remove($sandboxPath);
            $this->_errors[] = $this->l('This theme is not valid for PrestaShop 1.7');
            return false;
        }

        if ($without_theme) {
            $this->_themeName = 'tmp_'. $theme->getName();
        } else {
            $this->_themeName = $theme->getName();
        }
        $themePath = _PS_ALL_THEMES_DIR_ . $this->_themeName;

        if ($this->fileSystem->exists($themePath)) {
            $this->fileSystem->remove($themePath);
        }

        $this->fileSystem->mkdir($themePath);
        $this->mirror($sandboxPath, $themePath);
        $this->fileSystem->remove($sandboxPath);
    }

    public function addNewThemeForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Add a new theme'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'file',
                        'label' => $this->l('Theme zip'),
                        'name' => 'add_theme_zip'
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'update_modules',
                        'values' => [
                            'query' => [
                                [
                                    'id' => 'upt_m',
                                    'name' => $this->l('Add / update PrestaBuilder modules'),
                                    'val' => 1
                                ]
                            ],
                            'id' => 'id',
                            'name' => 'name'
                        ]
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'update_assets',
                        'values' => [
                            'query' => [
                                [
                                    'id' => 'upt_m',
                                    'name' => $this->l('Override the logo, the image slider, the banner (if defined in the editor)'),
                                    'val' => 1
                                ]
                            ],
                            'id' => 'id',
                            'name' => 'name'
                        ]
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Add'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitAddNewTheme';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name .'&current_tab=1';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->tpl_vars = array(
            'fields_value' => [
                'add_theme_zip' => '',
                'update_modules_upt_m' => 1,
                'update_assets_upt_m' => 0,
            ],
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function updateThemeAndModulesForm()
    {
        $current_theme = Tools::strtolower($this->context->shop->theme_name);
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Update a theme and modules'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Choose a theme to update'),
                        'name' => 'theme_name',
                        'options' => [
                            'query' => PTM_CCUtils::getPTMThemesList($current_theme),
                            'id'   => 'id',
                            'name' => 'name'
                        ]
                    ),
                    array(
                        'type' => 'file',
                        'label' => $this->l('Theme zip'),
                        'name' => 'add_theme_zip'
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'update_theme',
                        'values' => [
                            'query' => [
                                [
                                    'id' => 'upt_m',
                                    'name' => $this->l('Update the theme'),
                                    'val' => 1
                                ]
                            ],
                            'id'  => 'id',
                            'name' => 'name'
                        ]
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'update_modules',
                        'values' => [
                            'query' => [
                                [
                                    'id' => 'upt_m',
                                    'name' => $this->l('Add / update PrestaBuilder modules'),
                                    'val' => 1
                                ]
                            ],
                            'id' => 'id',
                            'name' => 'name'
                        ]
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'update_hooks',
                        'values' => [
                            'query' => [
                                [
                                    'id' => 'upt_m',
                                    'name' => $this->l('Update hooks as defined in the theme config').'<br /><i>'.$this->l('Check only if you have *not* installed any other 3rd party modules. This option will reset your theme to the exact state you created in the editor and will deactivate any other 3rd party modules.').'</i>',
                                    'val' => 1
                                ]
                            ],
                            'id' => 'id',
                            'name' => 'name'
                        ]
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'update_assets',
                        'values' => [
                            'query' => [
                                [
                                    'id' => 'upt_m',
                                    'name' => $this->l('Override the logo, the image slider, the banner (if defined in the editor)').'<br /><i>'.$this->l('If you don\'t want to change your logo, image slider and banner, keep this unchecked').'</i>',
                                    'val' => 1
                                ]
                            ],
                            'id' => 'id',
                            'name' => 'name'
                        ]
                    ),
                    
                ),
                'submit' => array(
                    'title' => $this->l('Update'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitUpdateThemeAndModules';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name .'&current_tab=2';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->tpl_vars = array(
            'fields_value' => [
                'theme_name' => '',
                'add_theme_zip' => '',
                'update_theme_upt_m' => 1,
                'update_modules_upt_m' => 1,
                'update_hooks_upt_m' => 0,
                'update_assets_upt_m' => 0,
            ],
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function settingsForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'checkbox',
                        'name' => 'PTM_CONTROLCENTER_QUICKLINKS',
                        'values' => [
                            'query' => [
                                [
                                    'id' => 'settings',
                                    'name' => $this->l('Display quick links of PrestaBuilder Control Center in the sidebar'),
                                    'val' => 1
                                ]
                            ],
                            'id' => 'id',
                            'name' => 'name'
                        ]
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->module = $this;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitSettings';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name .'&current_tab=3';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->tpl_vars = array(
            'fields_value' => [
                'PTM_CONTROLCENTER_QUICKLINKS_settings' => (int)Configuration::get('PTM_CONTROLCENTER_QUICKLINKS')
            ],
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        $theme_name = Tools::strtolower($this->context->shop->theme_name);

        if (PTM_CCUtils::isPTMTheme($theme_name)
            && (Tools::getValue('controller')
                && Tools::getValue('controller') == 'AdminThemes')) {
            $this->_updateAssets($theme_name);
            $this->upgradePTMModules($theme_name);
            // remove assets
            $this->_removeThemeAssets($theme_name);
        }

        $configure = Tools::getValue('configure');
        if (!$configure || ($configure && $configure != $this->name)) {
            return false;
        }

        $this->context->controller->addCSS($this->_path .'views/css/ptm_cc_bk.css');
    }

    public function hookDisplayBackOfficeFooter($params)
    {
        if (Tools::getValue('controller') && Tools::getValue('controller') == 'AdminThemes') {
            $theme_name = Tools::strtolower($this->context->shop->theme_name);
            $this->context->smarty->assign([
                'ptm_cc_url'  => $this->context->link->getAdminLink('AdminModules', true). '&configure='. $this->name,
                'is_ptm_theme'=> (int)PTM_CCUtils::isPTMTheme($theme_name),
                'theme_name' => $theme_name,
                'ptm_themes' => implode(',', PTM_CCUtils::getPTMThemes())
            ]);

            return $this->fetch($this->local_path .'views/templates/admin/theme_configs.tpl');
        }
    }

    public function hookDisplayHeader($params)
    {
        $this->context->smarty->assign([
            'ptm_controlcenter_lastupdated' => Configuration::get('PTM_CONTROLCENTER_LASTUPDATED')
        ]);
        //dump(Configuration::get('PTM_CONTROLCENTER_LASTUPDATED'));
        return $this->fetch($this->local_path .'views/templates/hook/custom_head_css.tpl');
    }

    private function _addConfigTabs()
    {
        if (!($current_tab = (int)Tools::getValue('current_tab'))) {
            $current_tab = 1;
        }
        $this->context->smarty->assign([
            'current_tab' => $current_tab,
            'ptm_cc_url'  => $this->context->link->getAdminLink('AdminModules', true). '&configure='. $this->name,
            'current_theme_name' => Tools::strtolower($this->context->shop->theme_name),
            'selected_theme_name' => Tools::getValue('theme_name'),
            'controller_ajax_url' => $this->context->link->getModuleLink($this->name, 'ajax')
        ]);

        return $this->fetch($this->local_path .'views/templates/admin/configs.tpl');
    }

    /**
     * Start copying and persisting assets in the db
     */
    private function _updateAssets($theme_name)
    {
        $themeImgPath = _PS_ALL_THEMES_DIR_ . $theme_name . '/assets/img/';

        # persist/copy image slider
        if ($this->moduleManager->isInstalled('ps_imageslider')) {
            $sliderImagesPath = _PS_MODULE_DIR_ . 'ps_imageslider/images/';
            $sliderImage      = null;

            if ($this->fileSystem->exists($themeImgPath . 'slider.jpg')) {
                $sliderImage = 'slider.jpg';
            } elseif ($this->fileSystem->exists($themeImgPath . 'slider.jpeg')) {
                $sliderImage = 'slider.jpeg';
            }elseif ($this->fileSystem->exists($themeImgPath . 'slider.png')) {
                $sliderImage = 'slider.png';
            }
            
            $copied = false;    
            if ($sliderImage) {
                $sliderExt = pathinfo($sliderImage, PATHINFO_EXTENSION);
                $newSliderImageName = Tools::hash(time()) .'.'. $sliderExt;
                $this->fileSystem->copy($themeImgPath . $sliderImage, $sliderImagesPath . $newSliderImageName, true);
                $copied = true;     
            }

            # install image slider
            if ($sliderImage && $copied) {
                // remove the image from theme
                $this->fileSystem->remove($themeImgPath . $sliderImage);
                // persist new slider in db
                $this->persistImageSlider($newSliderImageName);
            }
        }

        # persist/copy banner module
        if ($this->moduleManager->isInstalled('ps_banner')) {
            $bannerImagePath = _PS_MODULE_DIR_ . 'ps_banner/img/';
            $bannerImage     = null;

            if ($this->fileSystem->exists($themeImgPath . 'banner.jpg')) {
                $bannerImage = 'banner.jpg';
            } elseif ($this->fileSystem->exists($themeImgPath . 'banner.jpeg')) {
                $bannerImage = 'banner.jpeg';
            }elseif ($this->fileSystem->exists($themeImgPath . 'banner.png')) {
                $bannerImage = 'banner.png';
            }

            $copied = false;    
            if ($bannerImage) {
                $bannerExt = pathinfo($bannerImage, PATHINFO_EXTENSION);
                $newBannerImageName = Tools::hash(time()) .'.'. $bannerExt;
                $this->fileSystem->copy($themeImgPath . $bannerImage, $bannerImagePath . $newBannerImageName, true);
                $copied = true;     
            }

            if ($bannerImage && $copied) {
                // remove the image from theme
                $this->fileSystem->remove($themeImgPath . $bannerImage);
                // update the banner image in db
                $this->updateBannerImage($newBannerImageName);
            }
        }


         # persist/copy paymenticons module
        if ($this->moduleManager->isInstalled('ptm_paymenticons')) {
            $paymenticonsImagePath = _PS_MODULE_DIR_ . 'ptm_paymenticons/views/img/uploads/';
            $paymenticonsImage     = null;

            if ($this->fileSystem->exists($themeImgPath . 'paymenticons.jpg')) {
                $paymenticonsImage = 'paymenticons.jpg';
            } elseif ($this->fileSystem->exists($themeImgPath . 'paymenticons.jpeg')) {
                $paymenticonsImage = 'paymenticons.jpeg';
            }elseif ($this->fileSystem->exists($themeImgPath . 'paymenticons.png')) {
                $paymenticonsImage = 'paymenticons.png';
            }

            $copied = false;    
            if ($paymenticonsImage) {
                $paymenticonsExt = pathinfo($paymenticonsImage, PATHINFO_EXTENSION);
                $newPaymentIconsImageName = Tools::hash(time()) .'.'. $paymenticonsExt;
                $this->fileSystem->copy($themeImgPath . $paymenticonsImage, $paymenticonsImagePath . $newPaymentIconsImageName, true);
                $copied = true;
            }

            if ($paymenticonsImage && $copied) {
                // remove the image from theme
                $this->fileSystem->remove($themeImgPath . $paymenticonsImage);
                // update the paymenticons image in db
                $this->updatePaymentIconsImage($newPaymentIconsImageName);
            }
        }

        // theme logos
        $this->createLogos($themeImgPath);

        // change the favicon
        $this->updateFavicon($themeImgPath);

        // social media
        if ($this->moduleManager->isInstalled('ps_socialfollow')) {
            if (false === PTM_CCUtils::checkSocialMediaLinksExisting()) {
                PTM_CCUtils::persistDefaultSocilaMediaLinks();
            }
        }
    }

    protected function persistImageSlider($sliderImage)
    {
        // this file must be existing
        if (!file_exists(_PS_MODULE_DIR_.'ps_imageslider/Ps_HomeSlide.php')) {
            return false;
        }

        include_once(_PS_MODULE_DIR_.'ps_imageslider/Ps_HomeSlide.php');

        // deactivate all sliders
        Db::getInstance()->execute('
                UPDATE `'._DB_PREFIX_.'homeslider_slides` SET `active` = 0');

        // persist new customized slider
        $languages       = Language::getLanguages(false);
        $slide           = new Ps_HomeSlide();
        $slide->position = 1;
        $slide->active   = 1;
        foreach ($languages as $language) {
            $slide->title[$language['id_lang']]  = 'Marvelous';
            $slide->description[$language['id_lang']] = '<h2>EXCEPTEUR OCCAECAT</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin tristique in tortor et dignissim.</p>';
            $slide->legend[$language['id_lang']] = 'lorem-ipsum';
            $slide->url[$language['id_lang']]    = Tools::getCurrentUrlProtocolPrefix().Tools::getShopDomain() . __PS_BASE_URI__;
            $slide->image[$language['id_lang']]  = $sliderImage;
        }
        $slide->add();
    }

    protected function updateBannerImage($bannerImage)
    {
        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            // remove old ones
            $banner = Configuration::get('BANNER_IMG', $lang['id_lang']);
            if ($banner && $this->fileSystem->exists(_PS_MODULE_DIR_ . 'ps_banner/img/'. $banner)) {
                $this->fileSystem->remove(_PS_MODULE_DIR_ . 'ps_banner/img/'. $banner);
            }

            $values['BANNER_IMG'][$lang['id_lang']] = $bannerImage;
            Configuration::updateValue('BANNER_IMG', $values['BANNER_IMG']);
        }
    }


    protected function updatePaymentIconsImage($paymenticonsImage)
    {
        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            // remove old ones
            $paymenticons = Configuration::get('PTM_PAYMENTICONS_IMG', $lang['id_lang']);
            if ($paymenticons && $this->fileSystem->exists(_PS_MODULE_DIR_ . 'ptm_paymenticons/views/img/uploads/'. $paymenticons)) {
                $this->fileSystem->remove(_PS_MODULE_DIR_ . 'ptm_paymenticons/views/img/uploads/'. $paymenticons);
            }

            $values['PTM_PAYMENTICONS_IMG'][$lang['id_lang']] = $paymenticonsImage;
            Configuration::updateValue('PTM_PAYMENTICONS_IMG', $values['PTM_PAYMENTICONS_IMG']);
        }
    }

    private function createLogos($theme_path)
    {
        $logoImage  = null;
        $copied     = false;

        if ($this->fileSystem->exists($theme_path . 'logo.jpg')) {
            $logoImage = 'logo.jpg';
        } elseif ($this->fileSystem->exists($theme_path . 'logo.jpeg')) {
            $logoImage = 'logo.jpeg';
        } elseif ($this->fileSystem->exists($theme_path . 'logo.png')) {
            $logoImage = 'logo.png';
        }

        if ($logoImage) {

            $logoExt     = pathinfo($logoImage, PATHINFO_EXTENSION);
            $newLogoName = Tools::hash(time()) .'.'. $logoExt;
            $newMailName = Tools::hash(time()) .'mail.'. $logoExt;
            $newInvoiceName = Tools::hash(time()) .'invoice.'. $logoExt;
            $id_shop     = $this->context->shop->id;

            // copy eshop logo
            $this->fileSystem->copy($theme_path . $logoImage, _PS_IMG_DIR_ . $newLogoName, true);
            // copy invoice logo
            $this->fileSystem->copy($theme_path . $logoImage, _PS_IMG_DIR_ . $newMailName, true);
            // copy email logo
            $this->fileSystem->copy($theme_path . $logoImage, _PS_IMG_DIR_ . $newInvoiceName, true);

            // update db configurations
            Configuration::updateValue('PS_LOGO', $newLogoName, false, null, $id_shop);
            Configuration::updateValue('PS_LOGO_MAIL', $newMailName, false, null, $id_shop);
            Configuration::updateValue('PS_LOGO_INVOICE', $newInvoiceName, false, null, $id_shop);

            $copied = true;   
        }
     
        if ($copied) {
            // remove the logo
            $this->fileSystem->remove($theme_path . $logoImage);
        }
    }

    /**
     * Upgrade PTM modules
     */
    public function upgradePTMModules($theme)
    {
        $module_names = array();
        // update dependency modules
        $this->updateModulesFiles($theme, $module_names);

        if (!count($module_names)) {
            return false;
        }

        foreach ($module_names as $name) {
            if (!Validate::isModuleName($name) || !PTM_CCUtils::isPTMModule($name)) {
                continue;
            }
            $isInstalled = true;
            // if not installed, then install it
            if (!$this->moduleManager->isInstalled($name)) {
                $isInstalled = false;
                $this->moduleManager->install($name);
            }
            // start upgrading the module
            $this->upgradeModule($name);
            
            if (!$isInstalled) {
                $this->moduleManager->uninstall($name);
            }
        }
    }

    /**
     * This will update the existing module files to the new ones
     */
    public function updateModulesFiles($theme, &$module_names)
    {
        $theme_module_folder = _PS_ALL_THEMES_DIR_ . $theme .'/dependencies/modules';

        if (!$this->fileSystem->exists($theme_module_folder)) {
            return false;
        }

        $modules_to_iterate = PTM_CCUtils::scanDepsModulesFolder($theme_module_folder);

        if (!count($modules_to_iterate)) {
            return false;
        }

        foreach ($modules_to_iterate as $module) {
            $module_name = Tools::strtolower($module['module_name']);
            // check if the module is already on the disk and if the version is newer, if not skip
            if ($this->fileSystem->exists(_PS_MODULE_DIR_ . $module_name)) {
                if (!PTM_CCUtils::isPTMModuleVersionNew($module_name, $module['module_path'])) {
                    continue;
                }
            }

            $this->mirror($module['module_path'], _PS_MODULE_DIR_ . $module_name, ['override' => true]);
            $module_names[] = $module_name;
        }

        $this->fileSystem->remove(_PS_ALL_THEMES_DIR_ . $theme .'/dependencies');

        return true;
    }

    /**
     * Upgrade Single Module
     */
    public function upgradeModule($name)
    {
        // init legacy instance and prepare extra properties for interne verification
        $legacy_instance = \Module::getInstanceByName($name);
        $legacy_instance->installed = true;
        $legacy_instance->database_version = Db::getInstance()->getValue('SELECT `version` FROM `'._DB_PREFIX_.'module` WHERE `name` = "'. pSQL($name) .'"');
        $legacy_instance->interest = 0;

        if (\ModuleCore::initUpgradeModule($legacy_instance)) {
            $legacy_instance->runUpgradeModule();
            // update module new version
            \ModuleCore::upgradeModuleVersion($name, $legacy_instance->version);

            return (!count($legacy_instance->getErrors()));
        } elseif (\ModuleCore::getUpgradeStatus($name)) {
            return true;
        }

        return false;
    }

    /**
     * Update the favicon
     */
    public function updateFavicon($theme_path)
    {
        $favicon = null;
        $copied  = false;

        if ($this->fileSystem->exists($theme_path . 'favicon.png')) {
            $favicon = 'favicon.png';
            if ($this->fileSystem->exists(_PS_IMG_DIR_ . 'favicon.png')) {
                $this->fileSystem->remove(_PS_IMG_DIR_ . 'favicon.png');
            }
        } elseif ($this->fileSystem->exists($theme_path . 'favicon.ico')) {
            $favicon = 'favicon.ico';
            // remove the old icon from PS img folder
            if ($this->fileSystem->exists(_PS_IMG_DIR_ . 'favicon.ico')) {
                $this->fileSystem->remove(_PS_IMG_DIR_ . 'favicon.ico');
            } 
        }

        if (null === $favicon) {
            return;
        }

        $id_shop = $this->context->shop->id;
        // copy favicon
        $this->fileSystem->copy($theme_path . $favicon, _PS_IMG_DIR_ . $favicon, true);
        // update db configurations
        Configuration::updateValue('PS_FAVICON', $favicon, false, null, $id_shop);
        // remove the favicon from the theme
        $this->fileSystem->remove($theme_path . $favicon);
    }

    public function mirror($originDir, $targetDir, $options = array())
    {
        $targetDir = rtrim($targetDir, '/\\');
        $originDir = rtrim($originDir, '/\\');
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($originDir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);

        if (!$this->fileSystem->exists($targetDir)) {
            $this->fileSystem->mkdir($targetDir);
        }

        foreach ($iterator as $file) {
            $target = str_replace($originDir, $targetDir, $file->getPathname());

            if ($this->isForbiddenDir($target)) {
                continue;
            }

            if (is_link($file)) {
                $this->fileSystem->symlink($file->getLinkTarget(), $target);
            } elseif (is_dir($file)) {
                $this->fileSystem->mkdir($target);
            } elseif (is_file($file)) {
                $this->fileSystem->copy($file, $target, isset($options['override']) ? $options['override'] : false);
            } else {
                $this->_errors[] = $this->l(sprintf('Unable to guess "%s" file type.', $file));
            }
        }
    }

    /**
     * check if the given directory is forbiden to check
     */
    private function isForbiddenDir($dir)
    {
        $forbiden = false;

        foreach (self::$skipped_directories as $skip_dir) {
            if (false !== strpos($dir, $skip_dir)) {
                $forbiden = true;
                break;
            }
        }

        return $forbiden;
    }

    private function _getSandboxPath()
    {
        if (!isset($this->_sandbox_dir)) {
            $main_dir = $this->_main_dir . $this->_sandbox;

            if ($this->fileSystem->exists($main_dir)) {
                $this->fileSystem->remove($main_dir);
            }

            $this->fileSystem->mkdir($main_dir, 0755);

            $this->_sandbox_dir = $main_dir;
        }

        return $this->_sandbox_dir;
    }

    private function _isValidTheme(Theme $theme)
    {
        return $this->hasRequiredFiles($theme)
            && $this->hasRequiredProperties($theme);
    }

    public function getModulesListUpdates()
    {
        $existence_mods = PTM_CCUtils::getModulesNameAndVersion();
        $external_mods  = PTM_CCUtils::getModulesListFromExternal();
        $updated_mods   = [];

        foreach ($existence_mods as $module_name => $module_version) {
            if (array_key_exists($module_name, $external_mods)) {
                $mod_message = $this->l('Up to date');
                $status = true;
                
                if ($module_version !== $external_mods[$module_name]
                    && version_compare($module_version, $external_mods[$module_name], '<')) {
                    $status      = false;
                    $mod_message = $this->l('Ready for upgrade');
                }

                $updated_mods[] = ['message' => $mod_message, 'name' => $module_name, 'status' => $status];
            } else {
                $updated_mods[] = ['message' => $this->l('Not available'), 'name' => $module_name, 'status' => true];
            }
        }

        if (PTM_CCUtils::canUpdateModulesList()) {
            Configuration::updateValue('PTM_CONTROLCENTER_CACHED_MODS', serialize($updated_mods));
        }

        return $updated_mods;
    }

    private function hasRequiredProperties(Theme $theme)
    {
        $themeName = $theme->getName();

        foreach (PTM_CCUtils::getRequiredProperties() as $prop) {
            if (!$theme->has($prop)) {
                if (!array_key_exists($themeName, $this->_errors)) {
                    $this->_errors[$themeName] = array();
                }

                $this->_errors[$themeName] = $this->l(sprintf('An error occurred. Some information are missing.'));
            }
        }

        return !array_key_exists($themeName, $this->_errors);
    }

    private function hasRequiredFiles(Theme $theme)
    {
        $themeName = $theme->getName();
        $parentDir = realpath($theme->getDirectory().'/../'.$theme->get('parent')).'/';
        $parentFile = false;

        foreach (PTM_CCUtils::getRequiredFiles() as $file) {
            $childFile = $theme->getDirectory().$file;
            if ($theme->get('parent')) {
                $parentFile = $parentDir.$file;
            }

            if (!file_exists($childFile) && !file_exists($parentFile)) {
                if (!array_key_exists($themeName, $this->_errors)) {
                    $this->_errors[$themeName] = array();
                }

                $this->_errors[$themeName] = $this->l(sprintf('An error occurred. Some files are missing.'));
            }
        }

        return !array_key_exists($themeName, $this->_errors);
    }

    private function _removeThemeAssets($theme_name)
    {
        $themeImgPath = _PS_ALL_THEMES_DIR_ . $theme_name . '/assets/img/';
        $assetsToRemove = ['logo.png','logo.jpg','slider.jpg','slider.jpeg','slider.png','banner.jpg','banner.jpeg','banner.png'];

        foreach ($assetsToRemove as $_asset) {
            if ($this->fileSystem->exists($themeImgPath.$_asset)) {
                $this->fileSystem->remove($themeImgPath.$_asset);
            }
        }

        return true;
    }

    private function _enableAndDisableModules($modulesToEnable){

        $modulesToDisable  = PTM_CCUtils::getModulesListFromExternal();

        foreach ($modulesToEnable as $module) {

            if($module == 'welcome')
                continue;

            unset($modulesToDisable[$module]);

            if (!$this->moduleManager->isInstalled($module)) {
                $this->moduleManager->install($module);
            }

            if (!$this->moduleManager->isEnabled($module)) {
                $this->moduleManager->enable($module);
            }
        }

        foreach ($modulesToDisable as $key => $value) {
            if ($this->moduleManager->isEnabled($key)) {
                $this->moduleManager->disable($key);
            }
        }

    }

}
