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

{if isset($layouts) && count($layouts)}
  <div id="multibanner" class="multibanner-container wow fadeInUp ">
    <div class="row">
    <ul class="">
      {foreach from=$layouts item=layout}
        {if isset($layout.banners) && count($layout.banners)}
        <li>
          {foreach from=$layout.banners item=banner name=banners}
            <div class="{strip}
            {if $smarty.foreach.banners.total == 2}col-md-6
            {elseif $smarty.foreach.banners.total == 3}col-md-4
            {else}col-md-12{/if}
            {/strip} px-{$layout.paddingx} py-{$layout.paddingy}">
              <a href="{$banner.url|escape:'htmlall':'UTF-8'}" target="{$banner.target|escape:'htmlall':'UTF-8'}" title="{$banner.title|escape:'htmlall':'UTF-8'}" class="{if isset($hover_effect) && ($hover_effect == 1)}fulltolight{elseif isset($hover_effect) && ($hover_effect == 2)}lighttofull{elseif isset($hover_effect) && ($hover_effect == 3)}shadow{elseif isset($hover_effect) && ($hover_effect == 4)}removeshadow{else}{/if}">
              {if $banner.image}
                <img alt="{l s='Banner' mod='pb_multibanner'}" class="mb_banner_img img-fluid" src="{$img_path|escape:'htmlall':'UTF-8'}{$banner.image|escape:'htmlall':'UTF-8'}" title="{$banner.title|escape:'htmlall':'UTF-8'}" />
              {else}
                {$banner.title|escape:'htmlall':'UTF-8'}
              {/if}
              </a>
           </div>
          {/foreach}
        </li>
        {/if}
      {/foreach}
    </ul>
    </div>
  </div>
{/if}
