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

/**
* PTM_DDCUtils class 
*/
class PTM_DDCUtils
{
	/**
	 * Replace main ps shopping cart module main file
	 * displayTop hook should be replaced by displayNav2
	 */
	public static function fixPsShoppingCartModuleHook($fs, $module_name, $id_shop)
	{
		$ps_shoppingcart_file = _PS_MODULE_DIR_ . 'ps_shoppingcart/ps_shoppingcart.php';
        $ps_shoppingcart_clone = _PS_MODULE_DIR_ . $module_name .'/modules/ps_shoppingcart.txt';
        
        if ($fs->exists($ps_shoppingcart_file) && $fs->exists($ps_shoppingcart_clone)) {
            $fs->remove($ps_shoppingcart_file);
            $fs->copy($ps_shoppingcart_clone, $ps_shoppingcart_file, true);

            // in case the module is already installed, we need to link the displayNav2 hook
            // with shopping cart module so that can be displayed on the right place in FO
            if (Module::isInstalled('ps_shoppingcart')) {
                $id_module = (int)Module::getModuleIdByName('ps_shoppingcart');
                $id_hook   = (int)PTM_DDCUtils::getHookIdByName('displayNav2');

                // persist this new hook
                if (!Db::getInstance()->getValue('SELECT `id_hook` FROM `'._DB_PREFIX_.'hook_module` WHERE `id_module` = '. $id_module .' AND `id_shop` = '. (int)$id_shop .' AND `id_hook` ='. $id_hook)) {
	                Db::getInstance()->execute('
	                INSERT INTO `'._DB_PREFIX_.'hook_module` (`id_module`, `id_shop`, `id_hook`, `position`) VALUES ('. $id_module .', '. (int)$id_shop .', '. $id_hook .', 99)');
                }

                $hook_id = (int)PTM_DDCUtils::getHookIdByName('displayTop');
                // remove displayTop hook relationship
                Db::getInstance()->execute('
                DELETE FROM `'._DB_PREFIX_.'hook_module` WHERE `id_module` = '. $id_module. ' AND `id_hook` = '. $hook_id);
            }
        }
	}

	/**
     * Get hook id by name
     */
    public static function getHookIdByName($hook_name)
    {
        $hook_name = Tools::strtolower($hook_name);
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
            
            $hook_ids[Tools::strtolower($result['name'])] = $result['id_hook'];
            Cache::store($cache_id, $hook_ids);
        } else {
            $hook_ids = Cache::retrieve($cache_id);
        }

        return (isset($hook_ids[$hook_name]) ? $hook_ids[$hook_name] : false);
    }
}