<?php
/*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2017 PrestaShop SA
*  @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Upgrade the Ps_Customtext module to V3.0.0
 *
 * @param Ps_Customtext $module
 * @return bool
 */
function upgrade_module_1_4_0($module)
{
    $return = true;

    $data = getOldData();

    /** Delete the column id_shop from aboutus table */
    $return &= Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'aboutus` DROP `id_shop`');

    // /** Add the column id_shop and define as primary key in the table aboutus_lang */
    $return &= Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'aboutus_lang` ADD `id_shop` INT(10) UNSIGNED NOT NULL');
    $return &= Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'aboutus_lang` DROP PRIMARY KEY, ADD PRIMARY KEY(`id_aboutus`, `id_lang`, `id_shop`)');

    /** Create the aboutus_shop table */
    $return &= Db::getInstance()->execute('
                CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'aboutus_shop` (
                `id_aboutus` INT(10) UNSIGNED NOT NULL,
                `id_shop` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`id_aboutus`, `id_shop`)
                ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;'
    );

    /** Register the hook responsible for adding custom text when adding a new store */
    $return &= $module->registerHook('actionShopDataDuplication');

    /** Truncate all DB table */
    $return &= Db::getInstance()->execute('TRUNCATE `' . _DB_PREFIX_ . 'aboutus`');
    $return &= Db::getInstance()->execute('TRUNCATE `' . _DB_PREFIX_ . 'aboutus_shop`');
    $return &= Db::getInstance()->execute('TRUNCATE `' . _DB_PREFIX_ . 'aboutus_lang`');

    /** Reset DB data */
    $return &= insertData($data);

    return $return;
}

/**
 * Retrieves the old data of CustomText
 *
 * @return array
 */
function getOldData()
{
    $data = array();
    $texts = Db::getInstance()->executeS('SELECT i.`id_shop`, il.`id_lang`, il.`text`, il.`title` FROM `' . _DB_PREFIX_ . 'aboutus` i
    INNER JOIN `' . _DB_PREFIX_ . 'aboutus_lang` il ON il.`id_aboutus` = i.`id_aboutus`'
    );

    if (is_array($texts) && !empty($texts)) {
        $i = 0;
        foreach ($texts as $text) {
            $data[(int)$text['id_shop']]['text'][(int)$text['id_lang']] = $text['text'];
            $data[(int)$text['id_shop']]['title'][(int)$text['id_lang']] = $text['title'];
            $i++;
        }
    }
    return $data;
}

/**
 * Inserting the old AboutUs data
 *
 * @param $text
 * @return bool
 */
function insertData($texts)
{
    $return = true;

    if (is_array($texts) && !empty($texts)) {
        $shopsIds = Shop::getShops(true, null, true);
        $aboutUss = array_intersect_key($texts, $shopsIds);

        $resetAboutus = reset($aboutUss);

        $info = new AboutUs();
        $info->text = $resetAboutus['text'];
        $info->title = $resetAboutus['title'];
        $return &= $info->add();

        if (sizeof($aboutUss) > 1) {
            foreach ($aboutUss as $key => $text) {
                Shop::setContext(Shop::CONTEXT_SHOP, (int) $key);
                $info->text = $text['text'];
                $info->title = $text['title'];
                $return &= $info->save();
            }
        }
    }

    return $return;
}
