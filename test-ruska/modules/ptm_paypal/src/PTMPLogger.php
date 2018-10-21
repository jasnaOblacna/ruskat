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

class PTMPLogger
{
    public static function log($data)
    {
        $file = dirname(__FILE__) .'/../logs/'. date('Y-m-d') .'.log';
        return @file_put_contents($file, (is_array($data) ? print_r($data, true) : $data) . "\n", FILE_APPEND);
    }
}