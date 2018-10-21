{*
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
*}

<div id="_desktop_cart" class="blockcart_container{if isset($is_mobile) && $is_mobile} pull-xs-right{/if}">
  <div class="ptm_blockcart blockcart cart-preview {if isset($display_shadow) && $display_shadow == 2}noshadow{elseif isset($display_shadow) && $display_shadow == 3}lightshadow{elseif isset($display_shadow) && $display_shadow == 4}mediumshadow{elseif isset($display_shadow) && $display_shadow == 5}strongshadow{/if}" data-refresh-url="{$refresh_url}">
    <div class="header">
      {if $cart.products_count > 0} 
      <a id="dropdowncart_url" rel="nofollow" href="{$cart_url}">
      {/if}
        <i class="material-icons shopping-cart">shopping_cart</i>
        <span class="hidden-sm-down">{l s='Cart' mod='ptm_dropdowncart'}</span>
        <span class="ptm_cart_qty" data-cart-qty="{$cart.products_count}">({$cart.products_count})</span>
      {if $cart.products_count > 0}
      </a>
      {/if}
    </div>
    <div class="ptm_blockcart_body {if $display_shadow == 2}noshadow{elseif $display_shadow == 3}lightshadow{elseif $display_shadow == 4}mediumshadow{elseif $display_shadow == 5}strongshadow{/if} ptm_blockcart_default">
      <ul class="ptm_blockcart_list">
        {foreach from=$cart.products item=product}
          <li class="dropdown_cart_product_{$product.id_product}">{include 'module:ptm_dropdowncart/views/templates/hook/ptm_dropdowncart-product-line.tpl' product=$product}</li>
          <li class="cart_divider">&nbsp;</li>
        {/foreach}
      </ul>
      <div class="cart-total">
        <div class="cart-prices-line first-line{if $cart.subtotals.shipping.amount <= 0} ptm_unvisible{/if}">
            <span class="label shippig">{$cart.subtotals.shipping.label}</span>
            <span class="value price cart_block_shipping_cost">{$cart.subtotals.shipping.value}</span>            
        </div>
        <div class="cart-prices-line{if $cart.subtotals.tax.amount > 0} first-line{else} last-line{/if}">
          <span class="label">{$cart.totals.total.label} {$cart.labels.tax_short}</span>
          <span class="value price cart_block_total">{$cart.totals.total.value}</span>
        </div>
        <div class="cart-prices-line last-line{if $cart.subtotals.tax.amount <= 0} ptm_unvisible{/if}">
            <span class="label taxes">{$cart.subtotals.tax.label}</span>
            <span class="value price cart_block_tax_cost">{$cart.subtotals.tax.value}</span>
        </div>
      </div>
      <div class="checkout-link">
        <a class="button_order_cart btn btn-default btn btn-primary" href="{$option_url}">
          {if $redirect_visitors_opt == 'cart'}
              {l s='Shopping cart' mod='ptm_dropdowncart'} <i class="material-icons">&#xE163;</i>
          {else}
              {l s='Checkout' mod='ptm_dropdowncart'} <i class="material-icons">&#xE163;</i>
          {/if}
        </a>
      </div>
    </div>
  </div>
</div>