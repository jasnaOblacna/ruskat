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

{if isset($faqs.list) && $faqs.list|count}
<div id="faqs" class="faqs-container wow fadeIn ptm-module ptm-home ptm-card">
  {if isset($faqs.titleOpt) && $faqs.titleOpt}
    <h2 class="h2 ptm-title text-uppercase">{$faqs.titleOpt|escape:'htmlall':'UTF-8'}</h2>
  {/if}
  <div class="row">
    {if $faqs.perLine == 1}
    <div id="ptm_faqs_fullwidth" class="col-md-12">
        {foreach from=$faqs.list item=faq}
          {include file='./_partials/accordin_row.tpl' faq=$faq}
        {/foreach}
    </div>
    {else}
      {foreach from=$faqs.list item=parent_faqs name=faqs}
      <div id="ptm_faqs_accord_{if $smarty.foreach.faqs.iteration == 1}left_side{elseif $smarty.foreach.faqs.iteration == 2}right_side{/if}" class="col-md-6">
        <div class="">
            {foreach from=$parent_faqs item=faq}
              {include file='./_partials/accordin_row.tpl' faq=$faq}
            {/foreach}
        </div>
      </div>
      {/foreach}
    {/if}
  </div>
  {if isset($faqs.can_load_more) && $faqs.can_load_more}
  <div class="clearfix">
    <a id="load_more_faqs" class="col-md-12 text-xs-center" href="javascript:void();">{l s='Load more questions' mod='ptm_faqs'}</a>
  </div>
  {/if}
</div>
{/if}
<script>
  var ptm_loaded_faqs = "{$faqs.loaded_faqs|intval}", ptm_faqs_ajax_url = "{$faqs.ajax_url|escape:'htmlall':'UTF-8'}", ptm_faqs_per_line = "{$faqs.perLine|intval}";
</script>