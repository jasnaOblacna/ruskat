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

{if isset($hasAttribute) && $hasAttribute}
<a class="btn btn-primary ptm-addtocartbutton-link ptm_hidden{if !$hasQty} disabled{/if}" href="{if isset($hasQty) && $hasQty}{$product_url}{else}#{/if}">{l s='Add to cart' mod='ptm_addtocartbutton'}</a>
{else}
<form action="{if isset($hasQty) && $hasQty}{$cart_url}{/if}" method="post">
	{if isset($hasQty) && $hasQty}
    <input type="hidden" name="token" value="{$static_token}">
  	<input name="id_product" value="{$id_product}" type="hidden" />
  	<input name="id_customization" value="0" type="hidden" />
	{/if}
	<button class="btn btn-primary ptm-addtocartbutton-link ptm_hidden{if isset($hasQty) && $hasQty} add-to-cart{else} disabled{/if}" data-button-action="add-to-cart">{l s='Add to cart' mod='ptm_addtocartbutton'}</button>
</form>
{/if}