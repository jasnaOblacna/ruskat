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

{if $options.manufacturers}
  <div id="manufacturers" class="manufacturers-container wow fadeIn ptm-module ptm-home">
    {if isset($options.titleOpt) && $options.titleOpt}
    <h2 class="h2 ptm-title text-uppercase">{$options.titleOpt|escape:'htmlall':'UTF-8'}</h2>
    {/if}
    <div class="row">
    <ul class="manufacturer_slider" style="visibility: hidden;">
      {foreach from=$options.manufacturers item=manufacturers}
        <li>
          {foreach from=$manufacturers item=manufacturer}
           <div class="{$options.class|escape:'htmlall':'UTF-8'}">
              {if $manufacturer.url}
              <a href="{$manufacturer.url|escape:'htmlall':'UTF-8'}" {if isset($options.target) and $options.target == 1}target="_blank" {/if} title="{$manufacturer.name|escape:'htmlall':'UTF-8'}">
              {/if}
                <img alt="{$manufacturer.name|escape:'htmlall':'UTF-8'}" style="width:{$options.imgsizeOpt|escape:'htmlall':'UTF-8'}px" class="manufacturer_img{if $options.blackwhiteOpt == 2} ptm_manufacturer_blackwhite{/if}" src="{$options.img_path|escape:'htmlall':'UTF-8'}{$manufacturer.image|escape:'htmlall':'UTF-8'}" title="{$manufacturer.name|escape:'htmlall':'UTF-8'}" />
              {if $manufacturer.url}
              </a>
              {/if}
           </div>
          {/foreach}
        </li>
      {/foreach}
    </ul>
    </div>
  </div>
{/if}
