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

{if $options.actionbuttons}
<div id="action-button" class="action-button-container wow fadeIn ptm-module ptm-home">
  {if isset($options.title) && $options.title}
  <h2 class="h2 ptm-title text-uppercase">{$options.title|escape:'htmlall':'UTF-8'}</h2>
  {/if}
  <div class="row">
  {if $options.buttonposition == 1}  
      <div class="col-md-12 col-xs-12 center-position">
          <a class="btn btn-primary{$options.buttonsize|escape:'htmlall':'UTF-8'}" href="{$options.url nofilter}">{$options.actionbuttons.button nofilter}</a>
      </div>
      <div class="col-md-12 col-xs-12">
      {$options.actionbuttons.text nofilter}
      </div>
  {elseif $options.buttonposition == 2}
      <div class="col-md-9 col-xs-12">
      {$options.actionbuttons.text nofilter}
      </div>
      <div class="col-md-3 col-xs-12">
          <a class="btn btn-primary full-width{$options.buttonsize|escape:'htmlall':'UTF-8'}" href="{$options.url nofilter}">{$options.actionbuttons.button nofilter}</a>
      </div>

  {elseif $options.buttonposition == 3}
      <div class="col-md-12 col-xs-12">
      {$options.actionbuttons.text nofilter}
      </div>
      <div class="col-md-12 col-xs-12 center-position">
          <a class="btn btn-primary{$options.buttonsize|escape:'htmlall':'UTF-8'}" href="{$options.url nofilter}">{$options.actionbuttons.button nofilter}</a>
      </div>
  {else}
       <div class="col-md-3 col-xs-12 full-width">
          <a class="btn btn-primary full-width{$options.buttonsize|escape:'htmlall':'UTF-8'}" href="{$options.url nofilter}">{$options.actionbuttons.button nofilter}</a>
      </div>
      <div class="col-md-9 col-xs-12">
      {$options.actionbuttons.text nofilter}
      </div>
  {/if}
  </div>  
</div>
{/if}