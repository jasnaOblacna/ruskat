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
*  @copyright 2016 - 2018 Prestabuilder
*  @license   Do not distribute this module without permission from the author
*}
{if $instagram_hook == 'displayHome'}
<div id="instagram" class="instagram-container ptm-module ptm-home">
{else}
<div id="instagram-footer" class="instagram-container ptm-module ptm-footer">
{/if}
	<div class="row">
		<h2 class="h2 ptm-title text-uppercase">
			{if isset($block_title) && $block_title}
				{$block_title|escape:'htmlall':'UTF-8'}
			{else}
				{l s='Instagram' mod='pb_instagram'}
			{/if}
		</h2>
		<div id="ptm-instagram_media" class="col-md-12 col-sm-12">
			{if isset($medias) && $medias|count}
				{foreach from=$medias item=get_medias}
				  <div class="row">
					{foreach from=$get_medias item=media}
					  {if $media->type == 'image' || $media->type == 'carousel'}
						<div class="instagram_photo_block col-xs-4 {if $number_of_pictures_perline == 4}col-md-3{elseif $number_of_pictures_perline == 6}col-md-2{elseif $number_of_pictures_perline == 12}col-md-1{/if}">
							<a class="{if isset($link_type) && $link_type == 2}instagram_popup{/if}" href="{if isset($link_type) && $link_type == 1}{$media->link}{else}{$media->images->standard_resolution->url}{/if}"{if isset($link_type) && $link_type == 1} target="_blank"{/if}>
								<img alt="Instagram" class="instagram_img_{$number_of_pictures_perline}" src="{$media->images->thumbnail->url}" />
							</a>
						</div>
					  {/if}
					{/foreach}
				  </div>
				{/foreach}
			{else}
				<div class="row">
					<div class="instagram_photo_block col-xs-6 col-md-3" title="" data-tlite="">
						<a class="" href="" target="_blank">
							<img alt="Instagram" class="instagram_img_4" src="{$img_path|escape:'htmlall':'UTF-8'}instagram.jpg" title="" data-tlite="">
						</a>
					</div>
				  	<div class="instagram_photo_block col-xs-6 col-md-3" title="" data-tlite="">
						<a class="" href="" target="_blank">
							<img alt="Instagram" class="instagram_img_4" src="{$img_path|escape:'htmlall':'UTF-8'}instagram.jpg" title="" data-tlite="">
						</a>
					</div>
				  	<div class="instagram_photo_block col-xs-6 col-md-3">
						<a class="" href="" target="_blank">
							<img alt="Instagram" class="instagram_img_4" src="{$img_path|escape:'htmlall':'UTF-8'}instagram.jpg">
						</a>
					</div>
				  	<div class="instagram_photo_block col-xs-6 col-md-3">
						<a class="" href="" target="_blank">
							<img alt="Instagram" class="instagram_img_4" src="{$img_path|escape:'htmlall':'UTF-8'}instagram.jpg" title="" data-tlite="">
						</a>
					</div>
			  	</div>
				<div class="row" title="" data-tlite="">
					<div class="instagram_photo_block col-xs-6 col-md-3" title="" data-tlite="">
						<a class="" href="" target="_blank">
							<img alt="Instagram" class="instagram_img_4" src="{$img_path|escape:'htmlall':'UTF-8'}instagram.jpg" title="" data-tlite="">
						</a>
					</div>
				  	<div class="instagram_photo_block col-xs-6 col-md-3" title="" data-tlite="">
						<a class="" href="" target="_blank">
							<img alt="Instagram" class="instagram_img_4" src="{$img_path|escape:'htmlall':'UTF-8'}instagram.jpg" title="" data-tlite="">
						</a>
					</div>
				  	<div class="instagram_photo_block col-xs-6 col-md-3">
						<a class="" href="" target="_blank">
							<img alt="Instagram" class="instagram_img_4" src="{$img_path|escape:'htmlall':'UTF-8'}instagram.jpg" title="" data-tlite="">
						</a>
					</div>
				  	<div class="instagram_photo_block col-xs-6 col-md-3">
						<a class="" href="" target="_blank">
							<img alt="Instagram" class="instagram_img_4" src="{$img_path|escape:'htmlall':'UTF-8'}instagram.jpg" title="" data-tlite="">
						</a>
					</div>
			  </div>
		{/if}
		</div>
	</div>
</div>