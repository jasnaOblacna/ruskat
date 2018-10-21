{* NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from AZELAB
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the AZELAB is strictly forbidden.
 * In order to obtain a license, please contact us: info@azelab.com
 * ...........................................................................
 * @package    Tabbed Featured Categories / Subcategories on Home
 * @copyright  Copyright (c) 2014 AZELAB (http://www.azelab.com)
 * @author     AZELAB
 * @license    Commercial license
 * Support by mail  :  support@azelab.com
*}

{if $page_name == "index"}
<!-- MODULE tabbedcathome -->
<section id="tabbedcathome-tab" class="tabbedcathome tab-pane block">
	{if $cat_title}
	<h4 class="title_block">{l s='Featured Categories' mod='tabbedcathome'}</h4>
	{/if}
	{if isset($maincategories) AND $maincategories}
		{if !$cat_tab}
		<ul id="filterCat" class="nav nav-tabs clearfix"> 
			{if $cat_all}
			<li{if $cat_default == 0} class="active"{/if}><a href="#cat" data-filter="*" class="homefeatured">{l s='All Categories' mod='tabbedcathome'}</a></li>
			{/if}
			{foreach from=$maincategories item=maincategory key=i name=isotopeCatMain}
			{if !empty($maincategory)}
			<li{if ($cat_default == $maincategory.id_category) || (!$cat_all && $cat_default == 0 && $i == 0)} class="active"{/if}><a href="#cat" data-filter=".cat{$maincategory.id_category|intval}" class="homefeatured">{$maincategory.name|escape:'htmlall':'UTF-8'}</a></li>
			{/if}
			{/foreach}
		</ul>
		{/if}

		{if isset($subcategories) AND $subcategories}
		<div class="block_content">
			<ul id="isotopeCategories" class="grid row">
			{foreach from=$subcategories item=subcategory key=p name=isotopeCatSub}
			{if !empty($subcategory)}
				<li class="isotopeCat cat{$subcategory->id_parent|intval} col-xs-12 {if $col_nr == 5}col-sm-25{else}col-sm-{12/$col_nr|intval}{/if} clearfix hidestart">
					<a href="{$link->getCategoryLink($subcategory->id, $subcategory->link_rewrite)|escape:'htmlall':'UTF-8'}" title="{$subcategory->name|escape:'htmlall':'UTF-8'}" class="category_image clearfix">
						<img src="{$link->getCatImageLink($subcategory->link_rewrite, $subcategory->id, $cat_img)|escape:'html'}" alt="{$subcategory->name|escape:'htmlall':'UTF-8'}" />
					</a>
					<h5 class="s_title_block">
						<a href="{$link->getCategoryLink($subcategory->id, $subcategory->link_rewrite)|escape:'htmlall':'UTF-8'}" title="{$subcategory->name|escape:'htmlall':'UTF-8'}">{$subcategory->name|truncate:25:'...'|escape:'htmlall':'UTF-8'}</a>
					</h5>
					{if $cat_desc}
					<p class="category_desc">
						{$subcategory->description|truncate:$cat_desc_length:'...':TRUE|escape:'htmlall':'UTF-8'|strip_tags:'UTF-8'}
					</p>
					{/if}
				</li>
				{/if}
			{/foreach}
			</ul>
		</div>
		{/if}
	{else}
		<p class="col-xs-12">{l s='No Categories' mod='tabbedcathome'}</p>
	{/if}
</section>
<!-- /MODULE tabbedcathome -->
{/if}