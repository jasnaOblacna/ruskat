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

{if $posts}
<div id="blog" class="powerful-blog-container wow fadeInUp ptm-module ptm-home clearfix">
	{if isset($blog_title) && $blog_title}
		<h2 class="h2 ptm-title ptm-side-padding-title text-uppercase">{$blog_title}</h2>
	{/if}
	{foreach from=$posts item=post}
	{assign var=params value=['slug' => $post.link_rewrite]}
		{if $post_layout == 1}
			<div class="col-sm-12 post-listview">
				<div class="col-sm-5 post-img-container">
					<a href="{$link->getModuleLink('ptm_powerfulblog', 'single', $params)}"><img alt="{$post.title}" src="{$path}/views/img/uploads/{$post.image}" /></a>
				</div>
				<div class="col-sm-7">
					<h3 class="post-title">
						<a href="{$link->getModuleLink('ptm_powerfulblog', 'single', $params)}">{$post.title}</a>
					</h3>
					<div class="post-attrs">
						{if isset($is_allowed_date) && ($is_allowed_date == 1)}
						<span class="post-publishedat"><i class="fa fa-calendar"></i> {$post.published_at}</span> 
						{/if}
						{if isset($is_allowed_to_comment) && ($is_allowed_to_comment == 1 || $is_allowed_to_comment == 2)}
						<span class="post-comment"><i class="fa fa-comment"></i> {$post.comments}</span>
						{/if}
					</div>
					<div class="post-description">
						{$post.excerpt nofilter}
					</div>
				</div>
			</div>
		{else}
			<div class="{if $post_layout == 2}col-md-6{elseif $post_layout == 3}col-md-4{else}col-md-3 title-sm{/if} post-gridview">
				<div class="col-md-12">
					<h3 class="post-title">
						<a href="{$link->getModuleLink('ptm_powerfulblog', 'single', $params)}">{$post.title}</a>
					</h3>
					<div class="post-attrs">
						{if isset($is_allowed_date) && ($is_allowed_date == 1)}
						<span class="post-publishedat"><i class="fa fa-calendar"></i> {$post.published_at}</span> 
						{/if}
						{if isset($is_allowed_to_comment) && ($is_allowed_to_comment == 1 || $is_allowed_to_comment == 2)}
						<span class="post-comment"><i class="fa fa-comment"></i> {$post.comments}</span>
						{/if}
					</div>
				</div>
				<div class="col-md-12 post-img-container">
					<a href="{$link->getModuleLink('ptm_powerfulblog', 'single', $params)}"><img alt="{$post.title}" src="{$path}/views/img/uploads/{$post.image}" /></a>
				</div>
				<div class="col-md-12">
					<div class="post-description">
						{$post.excerpt nofilter} <a href="{$link->getModuleLink('ptm_powerfulblog', 'single', $params)}">{l s='Read more...' mod='ptm_powerfulblog'}</a>
					</div>
				</div>
			</div>
			{if $post_layout == 2 and $post@iteration is div by 2}
			    <div class="clearfix"></div>
			{/if}
			{if $post_layout == 3 and $post@iteration is div by 3}
			    <div class="clearfix"></div>
			{/if}
			{if $post_layout == 4 and $post@iteration is div by 4}
			    <div class="clearfix"></div>
			{/if}
		{/if}
	{/foreach}
	<div class="col-md-12 text-xs-center">
		<a href="{$link->getModuleLink('ptm_powerfulblog', 'blog')}">{l s='Read more articles' mod='ptm_powerfulblog'}</a>
	</div>
	<div class="clear"></div>
</div>
{/if}