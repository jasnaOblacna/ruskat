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

<section id="products-from-category" class="wow fadeIn featured-products clearfix">
	{if isset($options.titleOpt) && $options.titleOpt}
	<h2 class="h2 products-section-title text-uppercase">
		{$options.titleOpt|escape:'htmlall':'UTF-8'}
	</h2>
	{/if}
	<div class="products">
		{foreach from=$products item="product"}
			{include file="catalog/_partials/miniatures/product.tpl" product=$product}
		{/foreach}
	</div>
	<a class="all-product-link float-xs-right h4" href="{$allProductsLink|escape:'html':'UTF-8'}">
		{l s='All products' mod='ptm_productsfromcategory'} 
		<i class="material-icons">&#xE315;</i>
	</a>
</section>