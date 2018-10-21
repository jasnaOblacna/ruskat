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

$(document).ready(function() {
    $('.header').find('.ptm_cart_qty').html('(0)');
    $('.header').find('.cart-products-count').html('(0)');
    $('.blockcart').removeClass('active');
    $('.blockcart').addClass('inactive');
    $('.blockcart .header a').contents().unwrap();
});