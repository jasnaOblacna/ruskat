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

{if isset($orderProducts) && count($orderProducts) > 0}
	<h2>{l s='Customers who bought this product also bought:' mod='ptm_dropdowncart'}</h2>
	<a id="blockcart_scroll_left" class="blockcart_scroll_left{if count($orderProducts) < 5} hidden{/if}" title="{l s='Previous' mod='ptm_dropdowncart'}" rel="nofollow">{l s='Previous' mod='ptm_dropdowncart'}</a>
	<div id="blockcart_list">
		<ul {if count($orderProducts) > 4}style="width: {math equation="width * nbImages" width=58 nbImages=$orderProducts|@count}px"{/if}>
			{foreach from=$orderProducts item='orderProduct' name=orderProduct}
			<li>
				<a href="{$orderProduct.link}" title="{$orderProduct.name|htmlspecialchars}" class="lnk_img"><img src="{$orderProduct.image}" alt="{$orderProduct.name|htmlspecialchars}" /></a>
				<p class="product_name"><a href="{$orderProduct.link}" title="{$orderProduct.name|htmlspecialchars}">{$orderProduct.name|truncate:15:'...'|escape:'html':'UTF-8'}</a></p>
				{if $orderProduct.show_price == 1 AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
					<span class="price_display">
						<span class="price">{convertPrice price=$orderProduct.displayed_price}</span>
					</span>
				{else}
					<br />
				{/if}
			</li>
			{/foreach}
		</ul>
	</div>
	<a id="blockcart_scroll_right" class="blockcart_scroll_right{if count($orderProducts) < 5} hidden{/if}" title="{l s='Next' mod='ptm_dropdowncart'}" rel="nofollow">{l s='Next' mod='ptm_dropdowncart'}</a>
{/if}
