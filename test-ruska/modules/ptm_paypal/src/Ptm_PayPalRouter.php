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

/**
* Router class
*/
class Ptm_PayPalRouter
{
    public static function successRouter()
    {
        return array(
            'controller'=> 'success',
            'rule'      => 'ptmpaypal/success',
            'keywords'  => array(),
            'params'    => array(
                'fc'=>'module',
                'module'=>'ptm_paypal',
            )
        );
    }

    public static function paymentRouter()
    {
        return array(
            'controller'=> 'payment',
            'rule'      => 'ptmpaypal/payment',
            'keywords'  => array(),
            'params'    => array(
                'fc'=>'module',
                'module'=>'ptm_paypal',
            )
        );
    }

    public static function cancelRouter()
    {
        return array(
            'controller'=> 'cancel',
            'rule'      => 'ptmpaypal/cancel',
            'keywords'  => array(),
            'params'    => array(
                'fc'=>'module',
                'module'=>'ptm_paypal',
            )
        );
    }

    public static function notifyRouter()
    {
        return array(
            'controller'=> 'notify',
            'rule'      => 'ptmpaypal/notify',
            'keywords'  => array(),
            'params'    => array(
                'fc'=>'module',
                'module'=>'ptm_paypal',
            )
        );
    }
}