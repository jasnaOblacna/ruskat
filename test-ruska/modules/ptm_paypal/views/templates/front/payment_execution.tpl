{*
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
*}

{extends file='layouts/layout-full-width.tpl'}

{block name='content_wrapper'}
<div id="content-wrapper" class="{if isset($immediate) && $immediate == '1'}immediate{/if}">
  {if isset($immediate) && $immediate == '1'}
  <div id="paypal-glass">

  </div>
  {/if}
  <div class="row{if isset($immediate) && $immediate == '1'} hidden-xs-up{/if}">
    <div class="col-md-12 m-b-2">
      <section id="checkout-personal-information-step" class="checkout-step -reachable -complete">
      <h1 class="step-title h3">{l s='Pay by paypal' mod='ptm_paypal'}</h1>

      <div class="content">
        {l s='Please click on the button to continue your payment on PayPal'  mod='ptm_paypal'}<br /><br />
        <a id="continue_payment_paypal_btn" class="btn btn-primary" href="#">{l s='Continue to Paypal' mod='ptm_paypal'}</a>
      </div>
      </section>
    </div>
  </div>
</div>
<div style="display:none !important;">
  <form id="ptm-paypal-form" action="{$paypal_url|escape:'htmlall':'UTF-8'}" method="post">
    <input type="hidden" name="cmd" value="_cart">
    <input name="upload" value="1" type="hidden">
    <input name="no_note" value="1" type="hidden">
    <input name="rm" value="0" type="hidden">
    <input type="hidden" name="business" value="{$merchant_email|escape:'htmlall':'UTF-8'}">
    <input name="lc" value="{$iso_code|escape:'htmlall':'UTF-8'}" type="hidden">
    <input type="hidden" name="currency_code" value="{$currency_code|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="custom" value="{$id_cart|escape:'htmlall':'UTF-8'}#{$id_shop|escape:'htmlall':'UTF-8'}">
    {if isset($page_style) && $page_style}
    <input type="hidden" name="page_style" value="{$page_style|escape:'htmlall':'UTF-8'}" />
    {/if}
    {if isset($discount_amt) && $discount_amt}
    <input type="hidden" name="discount_amount_cart" value="{$discount_amt|escape:'htmlall':'UTF-8'}" />
    {/if}
    {foreach from=$products item=product key=key name=products}
      <input type="hidden" name="item_name_{$smarty.foreach.products.iteration|escape:'htmlall':'UTF-8'}" value="{$product.name}">
      <input type="hidden" name="quantity_{$smarty.foreach.products.iteration|escape:'htmlall':'UTF-8'}" value="{$product.cart_quantity}" />
      <input type="hidden" name="item_number_{$smarty.foreach.products.iteration|escape:'htmlall':'UTF-8'}" value="{$product.id_product}">
      <input type="hidden" name="amount_{$smarty.foreach.products.iteration|escape:'htmlall':'UTF-8'}" value="{round($product.price_wt, 2)}">

      {if $smarty.foreach.products.iteration eq 1}
      {if $shipping_amt > 0}
      <input type="hidden" name="shipping_1" value="{$shipping_amt|escape:'htmlall':'UTF-8'}">
      {/if}
      {/if}
    {/foreach}

    {if isset($shipping) && $shipping == '1'}
    <input type="hidden" name="first_name" value="{$address_invoice->firstname|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="last_name" value="{$address_invoice->lastname|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="address1" value="{$address_invoice->address1|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="address2" value="{$address_invoice->address2|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="city" value="{$address_invoice->city|escape:'htmlall':'UTF-8'}">
    {if isset($address_invoice->id_state) && $address_invoice->id_state > 0}
    <input type="hidden" name="state" value="{$state|escape:'htmlall':'UTF-8'}">
    {/if}
    <input type="hidden" name="zip" value="{$address_invoice->postcode|escape:'htmlall':'UTF-8'}">
    {/if}
    
    <input type="hidden" name="email" value="{$customer->email|escape:'htmlall':'UTF-8'}">

    <input name="return" value="{$success_url|escape:'htmlall':'UTF-8'}" type="hidden">
    <input name="cancel_return" value="{$cancel_url|escape:'htmlall':'UTF-8'}" type="hidden">
    <input name="notify_url" value="{$notify_url|escape:'htmlall':'UTF-8'}" type="hidden">
  </form>
</div>
{/block}