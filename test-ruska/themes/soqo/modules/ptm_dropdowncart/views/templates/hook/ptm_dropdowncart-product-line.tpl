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
{if isset($product.images.0)}
<a class="cart-images" href="{$link->getProductLink($product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}" title="{$product.name|escape:'html':'UTF-8'}"><img src="{$product.images.0.small.url}" alt="{$product.name|escape:'html':'UTF-8'}" width="80" /></a>
{/if}
{*
<span class="product-quantity">{$product.quantity|escape:'html':'UTF-8'}</span>
<span class="product-name"><a class="cart_block_product_name" href="{$link->getProductLink($product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}">{$product.name|escape:'html':'UTF-8'}</a></span>
<span class="product-price">{$product.price}</span>
*}
<div class="cart-info">
    <div class="product-name">
        <span class="quantity-formated">
            <span class="quantity">{$product.quantity}</span>&nbsp;x&nbsp;
        </span>
        <a class="cart_block_product_name" href="{$link->getProductLink($product, $product.link_rewrite, $product.category, null, null, $product.id_shop, $product.id_product_attribute)|escape:'html':'UTF-8'}" title="{$product.name}">{$product.name|truncate:30:'...'|escape:'html':'UTF-8'}</a>
    </div>
    <span class="price">{$product.price}</span>
</div>

<span class="remove_link">
<a  class="remove-from-cart ajax_cart_block_remove_link"
    rel="nofollow"
    href="{$product.remove_from_cart_url}"
    data-link-action="remove-from-cart"
    data-product="{$product.id_product}"
    data-product-attribute="{$product.id_product_attribute}"
    data-product-customization="0"
    title="{l s='remove from cart' mod='ptm_dropdowncart'}"
><i class="fa fa-remove"></i></a>
</span>
{if $product.customizations|count}
    <div class="customizations clearfix col-md-12">
        <ul>
            {foreach from=$product.customizations item="customization"}
                <li>
                    <span class="product-quantity ptm_unvisible">{$customization.quantity}</span>
                    <a href="{$customization.remove_from_cart_url}" title="{l s='remove from cart' mod='ptm_dropdowncart'}" class="remove-from-cart ajax_cart_block_remove_link" data-product-customization="{$customization.id_customization}" data-product="{$product.id_product}" data-product-attribute="{$product.id_product_attribute}" rel="nofollow"><i class="fa fa-remove"></i></a>
                    <ul class="col-md-11 customization_row">
                        {foreach from=$customization.fields item="field"}
                            <li class="col-md-12">
                                <span>{$field.label}</span>
                                {if $field.type == 'text'}
                                    <span>{$field.text}</span>
                                {else if $field.type == 'image'}
                                    <img src="{$field.image.small.url}">
                                {/if}
                            </li>
                        {/foreach}
                    </ul>
                </li>
                {if $customization@index > 1}
                <li class="cart_divider"></li>
                {/if}
            {/foreach}
        </ul>
    </div>
{/if}
