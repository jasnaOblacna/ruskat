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

class AboutUs extends ObjectModel
{
	public $id_aboutus;
	public $title;
	public $text;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'aboutus',
		'primary' => 'id_aboutus',
		'multilang' => true,
		'multilang_shop' => true,
		'fields' => array(
			'id_aboutus' =>			array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'),
			// Lang fields
			'title' =>				array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true),
			'text' =>				array('type' => self::TYPE_HTML, 'lang' => true, 'required' => true),
		)
	);

	static function getAboutUsIdByShop($shopId) 
	{
		$sql = 'SELECT i.`id_aboutus` FROM `' . _DB_PREFIX_ . 'aboutus` i
		LEFT JOIN `' . _DB_PREFIX_ . 'aboutus_shop` ish ON ish.`id_aboutus` = i.`id_aboutus`
		WHERE ish.`id_shop` = ' . (int)$shopId;
		
		if ($result = Db::getInstance()->executeS($sql)) {
			return (int) reset($result)['id_aboutus'];
		}

		return false;
	}

}
