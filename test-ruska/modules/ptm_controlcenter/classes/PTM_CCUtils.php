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

use Symfony\Component\Yaml\Yaml;
use Shudrum\Component\ArrayFinder\ArrayFinder;

/**
* PTM_CCUtils class 
*/
class PTM_CCUtils
{
	public static $module_name = 'ptm_controlcenter';
    private static $_modules_external_url = 'https://prestabuilder.com/wp-content/generator/modulesversion.php';
    private static $_tabs = ['adminptmcontrolcenter', 'adminptmcontrolcenteraddtheme', 'adminptmcontrolcenterupdatethememodules'];
    private static $_cached_installed_modules = [];

	/**
	 * Set default social media links
	 */
	public static function getDefaultSocialMediaLinks()
	{
		return [
			'FACEBOOK' 		=> 'https://www.facebook.com/prestabuilder',
			'TWITTER' 		=> 'https://www.facebook.com/prestabuilder',
			'RSS' 			=> 'https://www.facebook.com/prestabuilder',
			'YOUTUBE' 		=> 'https://www.facebook.com/prestabuilder',
			'GOOGLE_PLUS' 	=> 'https://www.facebook.com/prestabuilder',
			'PINTEREST' 	=> 'https://www.facebook.com/prestabuilder',
			'VIMEO' 		=> 'https://www.facebook.com/prestabuilder',
			'INSTAGRAM' 	=> 'https://www.facebook.com/prestabuilder',
		];
	}

	/**
	 * Check if there is at least one social media link in db
	 */
	public static function checkSocialMediaLinksExisting()
	{
		$exists = false;
		foreach (self::getDefaultSocialMediaLinks() as $socialmedia => $link) {
			if (Configuration::get('BLOCKSOCIAL_'. $socialmedia)) {
				$exists = true;
				break;
			}
		}

		return $exists;
	}

	/**
	 * Persist defualt social media links
	 */
	public static function persistDefaultSocilaMediaLinks()
	{
		$success = true;

		foreach (self::getDefaultSocialMediaLinks() as $socialmedia => $link) {
			$success &= Configuration::updateValue('BLOCKSOCIAL_'. $socialmedia, $link);
		}

		return $success;
	}

	/**
	 * Replace main ps shopping cart module main file
	 * displayTop hook should be replaced by displayNav2
	 */
	public static function fixPsShoppingCartModuleHook(Filesystem $fs, $module_name, $id_shop)
	{
		$ps_shoppingcart_file = _PS_MODULE_DIR_ . 'ps_shoppingcart/ps_shoppingcart.php';
        $ps_shoppingcart_clone = _PS_MODULE_DIR_ . $module_name .'/modules/ps_shoppingcart.txt';
        
        if ($fs->exists($ps_shoppingcart_file) && $fs->exists($ps_shoppingcart_clone)) {
            $fs->remove($ps_shoppingcart_file);
            $fs->copy($ps_shoppingcart_clone, $ps_shoppingcart_file, true);
            $fs->remove($ps_shoppingcart_clone);

            // in case the module is already installed, we need to link the displayNav2 hook
            // with shopping cart module so that can be displayed on the right place in FO
            if (Module::isInstalled('ps_shoppingcart')) {
                $id_module = (int)Module::getModuleIdByName('ps_shoppingcart');
                $id_hook   = (int)PTM_CCUtils::getHookIdByName('displayNav2');
               
                // persist this new hook
               	if (!Db::getInstance()->getValue('SELECT `id_hook` FROM `'._DB_PREFIX_.'hook_module` WHERE `id_module` = '. $id_module .' AND `id_shop` = '. $id_shop .' AND `id_hook` ='. $id_hook)) {
	                Db::getInstance()->execute('
	                INSERT INTO `'._DB_PREFIX_.'hook_module` (`id_module`, `id_shop`, `id_hook`, `position`) VALUES ('. $id_module .', '. $id_shop .', '. $id_hook .', 99)');
                }

                $hook_id = (int)PTM_CCUtils::getHookIdByName('displayTop');
                // remove displayTop hook relationship
                Db::getInstance()->execute('
                DELETE FROM `'._DB_PREFIX_.'hook_module` WHERE `id_module` = '. $id_module
                . ' AND `id_hook` = '. $hook_id);
            }
        }
	}

	/**
     * Get hook id by name
     */
    public static function getHookIdByName($hook_name)
    {
        $hook_name = strtolower($hook_name);
        if (!Validate::isHookName($hook_name)) {
            return false;
        }

        $cache_id = 'hook_idsbyname';
        if (!Cache::isStored($cache_id)) {
            // Get all hook ID by name and alias
            $hook_ids = array();
            $db = Db::getInstance();
            $result = $db->getRow('
            SELECT `id_hook`, `name`
            FROM `'._DB_PREFIX_.'hook` 
            WHERE `name` = "'. pSQL($hook_name) .'"', false);
            
            $hook_ids[strtolower($result['name'])] = $result['id_hook'];
            Cache::store($cache_id, $hook_ids);
        } else {
            $hook_ids = Cache::retrieve($cache_id);
        }

        return (isset($hook_ids[$hook_name]) ? $hook_ids[$hook_name] : false);
    }

    /**
     * Create tabs
     */
    public static function createSidebarTabs()
    {
    	// check if tabs are already created
    	if ((int)Tab::getIdFromClassName('AdminPTMControlCenter')) {
    		return false;
    	}

    	$_tabbed = true;
    	$langs   = Language::getLanguages(false);
    	// create tabs
        $parentTab             = new Tab();
        $parentTab->active     = 1;
        $parentTab->class_name = "AdminPTMControlCenter";
        $parentTab->id_parent  = 0;
        $parentTab->module     = self::$module_name;
        
        foreach ($langs as $lang) {
            $parentTab->name[$lang['id_lang']] = "PB CONTROL CENTER";
        }
        
        $_tabbed &= $parentTab->add();

        // add new theme tab
        $addThemeTab             = new Tab();
        $addThemeTab->active     = 1;
        $addThemeTab->class_name = "AdminPTMControlCenterAddTheme";
        $addThemeTab->id_parent  = (int)Tab::getIdFromClassName('AdminPTMControlCenter');
        $addThemeTab->module     = self::$module_name;

        foreach ($langs as $lang) {
            $addThemeTab->name[$lang['id_lang']] = "Add new PB theme";
        }

        $_tabbed &= $addThemeTab->add();

        // update theme and modules tab
        $updateThemeTab             = new Tab();
        $updateThemeTab->active     = 1;
        $updateThemeTab->class_name = "AdminPTMControlCenterUpdateThemeModules";
        $updateThemeTab->id_parent = (int)Tab::getIdFromClassName('AdminPTMControlCenter');
        $updateThemeTab->module    = self::$module_name;
        
        foreach ($langs as $lang) {
            $updateThemeTab->name[$lang['id_lang']] = "Update PB theme/modules";
        }

        $_tabbed &= $updateThemeTab->add();

        return $_tabbed;
    }

    /**
     * Remove tabs by module name
     */
    public static function deleteTabsByModuleName()
    {
    	// check if tabs are already created
    	if (!(int)Tab::getIdFromClassName('AdminPTMControlCenter')) {
    		return false;
    	}

        $res = true;
        $sql = 'SELECT `id_tab` FROM `'._DB_PREFIX_. 'tab` WHERE `module` = "'. pSQL(self::$module_name) .'"';
        $ids = Db::getInstance()->executeS($sql);
        $_ids = [];

        foreach ($ids as $_id) {
            $_ids[] = (int)$_id['id_tab'];
        }

        $res &= Db::getInstance()->execute('
            DELETE FROM `'._DB_PREFIX_. 'tab_lang` WHERE `id_tab` IN ('. implode(', ', $_ids) .')');

        $res &= Db::getInstance()->execute('
                DELETE FROM `'._DB_PREFIX_. 'tab` WHERE `module` = "'. pSQL(self::$module_name) .'"');

        // delete tab permissions
        foreach (self::$_tabs as $_tab) {
            $slug = 'ROLE_MOD_TAB_'.strtoupper($_tab);

            foreach (array('CREATE', 'READ', 'UPDATE', 'DELETE') as $action) {
                $res &= Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'authorization_role` WHERE `slug` = "'.$slug.'_'.$action.'"');
            }
        }

        return $res;
    }

    /**
     * Theme must contain these properties within its yml file
     */
    public static function getRequiredProperties()
    {
        return [
            'name',
            'display_name',
            'version',
            'author.name',
            'meta.compatibility.from',
            'meta.available_layouts',
            'global_settings.image_types.cart_default',
            'global_settings.image_types.small_default',
            'global_settings.image_types.medium_default',
            'global_settings.image_types.large_default',
            'global_settings.image_types.home_default',
            'global_settings.image_types.category_default',
            'theme_settings.default_layout',
        ];
    }

    /**
     * Theme must have these files to process the uploading
     */
    public static function getRequiredFiles()
    {
        return [
            'preview.png',
            'config/theme.yml',
            // 'assets/js/theme.js',
            'assets/css/theme.css',
            'assets/css/custommaker.min.css',
            // Templates
            'templates/_partials/form-fields.tpl',
            'templates/catalog/product.tpl',
            'templates/catalog/listing/product-list.tpl',
            'templates/checkout/cart.tpl',
            'templates/checkout/checkout.tpl',
            'templates/cms/category.tpl',
            'templates/cms/page.tpl',
            'templates/customer/address.tpl',
            'templates/customer/addresses.tpl',
            'templates/customer/guest-tracking.tpl',
            'templates/customer/guest-login.tpl',
            'templates/customer/history.tpl',
            'templates/customer/identity.tpl',
            'templates/index.tpl',
            'templates/customer/my-account.tpl',
            'templates/checkout/order-confirmation.tpl',
            'templates/customer/order-detail.tpl',
            'templates/customer/order-follow.tpl',
            'templates/customer/order-return.tpl',
            'templates/customer/order-slip.tpl',
            'templates/errors/404.tpl',
            'templates/errors/forbidden.tpl',
            'templates/checkout/cart-empty.tpl',
            // 'templates/cms/sitemap.tpl',
            'templates/cms/stores.tpl',
            'templates/customer/authentication.tpl',
            'templates/customer/registration.tpl',
            'templates/contact.tpl',
        ];
    }

    /**
     * Get PrestaBuilder themes 
     *
     * @return array
     */
    public static function getPTMThemes()
    {
        $checkThemes = Tools::scandir(_PS_ALL_THEMES_DIR_, '');
        $getPrestaThemesMaker = [];

        // get available PrestaBuilder
        foreach ($checkThemes as $is_theme) {
            $theme = Tools::strtolower($is_theme);
            if (is_dir(_PS_ALL_THEMES_DIR_ . $theme) 
                && !in_array($theme, ['.', '..', 'cache']) 
                && Validate::isThemeName($theme) 
                && self::isPTMTheme($theme)) {
                $getPrestaThemesMaker[] = $theme;
            }
        }

        return $getPrestaThemesMaker;
    }

    /**
     * Get PrestaBuilder modules 
     *
     * @return array
     */
    public static function getPTMModules($only_names = false)
    {
        $getInstalledModules = self::getPtmInstalledModuled();
        $getPTMModules = [];

        if (count($getInstalledModules)) {
            if (isset(self::$_cached_installed_modules[$only_names])) {
                return self::$_cached_installed_modules[$only_names];
            } else {
                foreach ($getInstalledModules as $_mod) {
                    $name = Tools::strtolower($_mod['name']);
                    if (is_dir(_PS_MODULE_DIR_ . $name)  
                        && Validate::isModuleName($name) 
                        && self::isPTMModule($name)) {
                        $get_module = Module::getInstanceByName($name);
                        if ( false === $only_names ) {
                            $getPTMModules[] = $get_module;
                        } else {
                            $getPTMModules[$get_module->name] = $get_module->version;
                        }
                    }
                }

                if (!count(self::$_cached_installed_modules)) {
                    self::$_cached_installed_modules[$only_names] = $getPTMModules;
                }
            }
        }

        return $getPTMModules;
    }

    /**
     * Get only name and version of each module
     *
     * @return array
     */
    public static function getModulesNameAndVersion()
    {
        return self::getPTMModules(true);
    }

    /**
     * Check if the given theme name is PTM theme
     */
    public static function isPTMTheme($theme, $theme_path = _PS_ALL_THEMES_DIR_)
    {
        if (empty($theme)) {
            $theme_path = rtrim($theme_path, '/');
        }

        $config_file = $theme_path . $theme .'/config/theme.yml';
        $css_file    = $theme_path . $theme . '/assets/css/custommaker.min.css';
        $ptmAuthor = str_replace(' ', '', Tools::strtolower(self::parseThemeYmlFile($theme, 'author.name', $theme_path)));

        return file_exists($css_file) && file_exists($config_file) 
            && ($ptmAuthor && ($ptmAuthor === 'prestathememaker' || $ptmAuthor === 'prestabuilder'));
    }

    /**
     * Check if the given module name is a PTM module
     */
    public static function isPTMModule($module, $path = _PS_MODULE_DIR_)
    {
        $moduleConfig = $path . $module .'/config.xml';

        if (file_exists($moduleConfig)) {
            $parseFile  = self::parseXmlFile($moduleConfig);
            $author     = Tools::strtolower(str_replace(' ', '', Tools::htmlentitiesDecodeUTF8($parseFile->author)));

            return preg_match('/^ptm\_/', $module) && ($author === 'prestathememaker' || $author === 'prestabuilder');
        }

        return preg_match('/^ptm\_/', $module);
    }

    /**
     * Scan dependencies modules folder of a theme
     */
    public static function scanDepsModulesFolder($path)
    {
        $scanPath   = Tools::scandir($path, '');
        $getModules = [];

        foreach ($scanPath as $module) {
            $module      = Tools::strtolower($module);
            $module_path = rtrim($path, '/') .'/';
            
            if (!is_dir($module_path) || in_array($module_path, ['.', '..']) 
                || !self::isPTMModule($module, $module_path)) {
                continue;
            }
            
            $getModules[] = [
                'module_path' => $module_path . $module,
                'module_name' => $module
            ];
        }

        return $getModules;
    }

    /**
     * Parse theme YML file
     */
    public static function parseThemeYmlFile($theme, $data, $parent_path = _PS_ALL_THEMES_DIR_)
    {
        if (empty($theme)) {
            $parent_path = rtrim($parent_path, '/');
        }

        $config_theme = $parent_path . $theme .'/config/theme.yml';

        if (!file_exists($config_theme)) {
            return false;
        }

        $themeAttributes = Yaml::parse(file_get_contents($config_theme));
        $attributes      = new ArrayFinder($themeAttributes);
        
        return $attributes->get($data, []);
    }

    /**
     * Parse XML file
     */
    public static function parseXmlFile($filename_path)
    {
        $parseXML = @simplexml_load_file($filename_path);

        return $parseXML;
    }

    /**
     * Get list of PTM themes with preview image
     */
    public static function getPTMListOfThemesWithPreviews()
    {
        $ptm_themes = [];

        if (count(self::getPTMThemes())) {
            foreach (self::getPTMThemes() as $theme) {
                $theme = Tools::strtolower($theme);
                $ptm_themes[] = [
                    'theme_name'    => Tools::ucfirst($theme),
                    'theme_preview' => Tools::getCurrentUrlProtocolPrefix().Tools::getShopDomain() . __PS_BASE_URI__ .'themes/'. $theme .'/preview.png?rand='.rand(),
                    'version' => self::parseThemeYmlFile($theme, 'version')
                ];
            }
        }
    
        return $ptm_themes;
    }

    /**
     * Get list of themes to be displayed in a dropdown list
     */
    public static function getPTMThemesList($current_theme)
    {
        $ptm_themes = [];

        if (count(self::getPTMThemes())) {
            foreach (self::getPTMThemes() as $theme) {
                $theme = Tools::strtolower($theme);
                $ptm_themes[] = [
                    'id'   => $theme,
                    'name' => ($current_theme == $theme ? Tools::ucfirst($theme) .' (active)' : Tools::ucfirst($theme))
                ];
            }
        }

        return $ptm_themes;
    }

    /**
     * Dump to yml file
     */
    public static function dumpYmlFile($new_theme, $dir_path)
    {
        $dir_path     = rtrim($dir_path, '/') . '/';
        $old_theme    = self::parseThemeYmlFile('', 'name', $dir_path);
        // if the theme's name is the same we don't need to dump  the file
        if ($old_theme == $new_theme) {
            return true;
        }

        $data['name'] = $new_theme;
        $data['display_name'] = self::parseThemeYmlFile('', 'display_name', $dir_path);
        $data['version'] = self::parseThemeYmlFile('', 'version', $dir_path);
        
        $theme_key = self::parseThemeYmlFile('', 'theme_key', $dir_path);
        if ($theme_key) {
            $data['theme_key'] = $theme_key;
        }

        $data['author'] = self::parseThemeYmlFile('', 'author', $dir_path);
        $data['meta'] = self::parseThemeYmlFile('', 'meta', $dir_path);
        $data['assets'] = self::parseThemeYmlFile('', 'assets', $dir_path);
       
        $dependencies = self::parseThemeYmlFile('','dependencies', $dir_path);
        if ($dependencies) {
            $data['dependencies'] = $dependencies;
        }
       
        $data['global_settings'] = self::parseThemeYmlFile('', 'global_settings', $dir_path);
        $data['theme_settings'] = self::parseThemeYmlFile('', 'theme_settings', $dir_path);

        // rename old yml file
        if (!rename($dir_path .'config/theme.yml', $dir_path .'config/_theme.yml')) {
            throw new Exception("We can not rename the yml file", 1);
        }

        $get_yml = Yaml::dump($data, 2, 2);

        return self::writeToFile($get_yml, $dir_path .'config/theme.yml');
    }

    public static function writeToFile($data, $path)
    {
        $handler = fopen($path, "w") or die("Unable to open file!");
        fwrite($handler, $data);
        fclose($handler);

        return true;
    }

    /**
     * Get list of modules versions from external
     */
    public static function getModulesListFromExternal()
    {
        $get_modules = [];

        $modules = json_decode(@file_get_contents(self::$_modules_external_url), true);
        $_modules = $modules['modules'];

        if (isset($_modules) && count($_modules)) {
            foreach ($_modules as $data) {
                $get_modules[Tools::strtolower($data['name'])] = $data['version'];
            }
        }

        return $get_modules;
    }

    /**
     * Get installed ptm modules list 
     */
    public static function getPtmInstalledModuled()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT * FROM `'. _DB_PREFIX_ .'module` WHERE `name` LIKE "ptm_%"');
    }

    /**
     * Check last updated modules
     */
    public static function canUpdateModulesList()
    {
        $getLastUpdated = Configuration::get('PTM_CONTROLCENTER_LAST_MODS_UPDATED');
        $createDateObj = new DateTime('now');
        $createDateObj->setTimeStamp($getLastUpdated);
        $createDateObj->modify('+1 hour');

        return ($createDateObj->getTimeStamp() < time());
    }

    /**
     * Verify if the new uploaded module version is newer
     */
    public static function isPTMModuleVersionNew($module, $new_module_path)
    {
        $getNewModuleConfig = rtrim($new_module_path, '/') .'/config.xml';

        if (file_exists($getNewModuleConfig)) {
            $parseFile  = self::parseXmlFile($getNewModuleConfig);

            if (!$parseFile || !isset($parseFile->version) || Tools::isEmpty($parseFile->version)) {
                return false;
            }

            $_module  = Module::getInstanceByName($module);
            $_version = Tools::strtolower(str_replace(' ', '', Tools::htmlentitiesDecodeUTF8($parseFile->version)));

            return Tools::version_compare($_version, $_module->version, '>');
        }

        return false;
    }
}