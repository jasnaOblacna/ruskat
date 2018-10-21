{**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

{block name='block_social'}
  <div class="block-social col-lg-4 col-md-12 col-sm-12">
    <ul>
      {foreach from=$social_links item='social_link'}
      	{if $social_link.class == 'facebook'}
	        {$icon = 'fa-facebook'}
	    {elseif $social_link.class == 'twitter'}
	        {$icon = 'fa-twitter'}
	    {elseif $social_link.class == 'youtube'}
	        {$icon = 'fa-youtube'}
	    {elseif $social_link.class == 'rss'}
	        {$icon = 'fa-rss'}
	    {elseif $social_link.class == 'googleplus'}
	        {$icon = 'fa-google-plus'}
	    {elseif $social_link.class == 'pinterest'}
	        {$icon = 'fa-pinterest-p'}
	    {elseif $social_link.class == 'vimeo'}
	        {$icon = 'fa-vimeo'}
	    {elseif $social_link.class == 'instagram'}
	        {$icon = 'fa-instagram'}
	    {else}
	        {$icon = 'fa-share'}
	    {/if}

        <li class="{$social_link.class} ptm_social_icons hvr-shrink"><a href="{$social_link.url}" target="_blank"><i class="fa {$icon}"></i></a></li>
      {/foreach}
    </ul>
  </div>
{/block}
