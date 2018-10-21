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

{if isset($textservices.services)}
{assign var=whichline value=0 scope="global"}
  <div id="texts-and-icons" class="textsandicons-container wow fadeIn ptm-module ptm-home">
    {if isset($textservices.titleOpt) && $textservices.titleOpt}
    <h2 class="h2 ptm-title text-uppercase">{$textservices.titleOpt|escape:'htmlall':'UTF-8'}</h2>
    {/if}
      {foreach from=$textservices.services item=services}
      <div class="row">
      {foreach from=$services item=text}
        <div class="{$textservices.perline|escape:'htmlall':'UTF-8'}">
          <div class="text-center">
            {if $text.icon}
              <div class="icon"><i class="fa {$text.icon|escape:'htmlall':'UTF-8'}"></i></div>
            {/if}
            <h4 class="">
              {$text.title|escape:'htmlall':'UTF-8'}
            </h4>
            {if $text.description}
            <p>{$text.description nofilter}</p>
            {/if}
          </div>
        </div>
      {/foreach}
      </div>
      {/foreach}
  </div>
{/if}
