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

{extends file="helpers/form/form.tpl"}

{block name="input"}
    {if $input.type == 'category_choices'}
		<select id="categories" name="PTM_PFC_CATEGORY_ID">
			{$options_choice}
		</select>
	{else}
		{$smarty.block.parent}
		<script type="application/javascript">
        	$(document).ready(function() {
        		$('.translatable-field.lang-1').css('display','block');
        	});
        </script>
    {/if}
{/block}