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

{if $options.testimonials}
  <div id="testimonials" class="testimonials-container wow fadeIn ptm-module ptm-home">
    {if isset($options.titleOpt) && $options.titleOpt}
    <h2 class="h2 ptm-title text-uppercase">{$options.titleOpt|escape:'htmlall':'UTF-8'}</h2>
    {/if}
    <div class="row">
    <ul class="testimonial_slider" style="visibility: hidden;">
      {foreach from=$options.testimonials item=testimonials}
        <li>
          {foreach from=$testimonials item=testimonial}
           <div class="{$options.classes|escape:'htmlall':'UTF-8'}"> 
             <div class="col-md-4"> 
              {if $testimonial.image}
                <img alt="{$testimonial.name|escape:'htmlall':'UTF-8'}" class="testimonial_img img-circle" src="{$options.img_path|escape:'htmlall':'UTF-8'}{$testimonial.image|escape:'htmlall':'UTF-8'}" title="{$testimonial.name|escape:'htmlall':'UTF-8'}" />
              {else}
                <img alt="{$testimonial.name|escape:'htmlall':'UTF-8'}" class="testimonial_img img-circle" src="{$options.img_path|escape:'htmlall':'UTF-8'}dummy.png" title="{$testimonial.name|escape:'htmlall':'UTF-8'}" />
              {/if}
             </div>
             <div class="col-md-8">
              <div class="message">
                <q>{$testimonial.message nofilter}</q>
              </div>
              <div class="company-name">
                <strong>{$testimonial.name|escape:'htmlall':'UTF-8'}</strong> 
                {if $testimonial.company_name}
                  , {if $testimonial.url|escape:'htmlall':'UTF-8'}
                  <a href="{$testimonial.url|escape:'htmlall':'UTF-8'}" target="_blank" title="{$testimonial.company_name|escape:'htmlall':'UTF-8'}">
                  {/if}
                    <small>{$testimonial.company_name|escape:'htmlall':'UTF-8'}</small>
                  {if $testimonial.url}
                  </a>
                  {/if}
                {/if}
              </div>
             </div>
           </div>
          {/foreach}
        </li>
      {/foreach}
    </ul>
    </div>
  </div>
{/if}
