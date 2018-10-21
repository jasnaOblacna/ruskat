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
<div id="content-wrapper" class="">
  <div class="row">
    <div class="col-md-12 m-b-2">
      <section class="checkout-step">
      <h1 class="step-title h3">
        {if isset($title) && $title}
          {$title|escape:'htmlall':'UTF-8'}
        {else}
          {l s='Thank you for your purchase!' mod='ptm_paypal'}
        {/if}
      </h1>

      <div class="content m-y-1">
        <p>
          {if isset($content) && $content}
            {$content|escape:'htmlall':'UTF-8'}
          {else}
            {l s='We have successfully received your payment and your order is now being processed.' mod='ptm_paypal'}
          {/if}
        </p>
        {*
        <!--a class="btn btn-primary" href="{$order_url}">
          {if isset($text_btn) && $text_btn}
            {$text_btn}
          {else}
            {l s='Go to my orders' mod='ptm_paypal'}
          {/if}
        </a-->
        *}
      </div>
      </section>
    </div>
  </div>
</div>
{/block}