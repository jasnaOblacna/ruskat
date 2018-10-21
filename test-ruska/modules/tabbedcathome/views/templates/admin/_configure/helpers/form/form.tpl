{* NOTICE OF LICENSE
 *
 * This source file is subject to a commercial license from AZELAB
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the AZELAB is strictly forbidden.
 * 
 * ...........................................................................
*}

{extends file="helpers/form/form.tpl"}

{block name="input"}
	{if isset($input.type)}
		{if $input.type == 'tabbed_cat_hidden'}

			<p><select name="TABBED_CAT_HIDDEN[]" class="tabcategorieshide fixed-width-xl" multiple size="10">
					{foreach from=$main_categories item=maincategory}
						<option value="{$maincategory.id_category|intval}" {if (in_array($maincategory.id_category, $cat_hide))}selected=="selected" {/if}>{$maincategory.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
			</select></p>
			<p><input class="btn btn-info reset_cat" type="button" value="{l s='Unselect all' mod='tabbedcathome'}" /></p>
		{elseif $input.type == 'tabbed_subcat_hidden'}

			<p><select name="TABBED_SUBCAT_HIDDEN[]" class="tabsubcategorieshide fixed-width-xl" multiple size="10">
					{foreach from=$subcategories item=subcategory}
						<option value="{$subcategory.id_category|intval}" {if (in_array($subcategory.id_category, $subcat_hide))}selected=="selected" {/if}>{$subcategory.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
			</select></p>
			<p><input class="btn btn-info reset_subcat" type="button" value="{l s='Unselect all' mod='tabbedcathome'}" /></p>
		{elseif $input.type == 'tabbed_show_on_pages'}
                        <p><select name="TABBED_SHOW_ON_PAGES[]" class="tabshowonpages fixed-width-xl" multiple size="10">
					{foreach from=$pages item=page key=id}
						<option value="{$id|intval}" {if (in_array($id, $show_on_pages))}selected=="selected" {/if}>{$page|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
			</select></p>
			<p><input class="btn btn-info reset_show_on_pages" type="button" value="{l s='Unselect all' mod='tabbedcathome'}" /></p>
                {else}
			{$smarty.block.parent}
		{/if}
	{/if}
{/block}

{block name="script"}
$(window).load(function() {	
	if($('#TABBED_CAT_ALL_on').prop('checked') == false){
		$('.tabcategories_default option[value=0]').hide();
		$('.tabcategories_default option').removeAttr('selected');
		/*$('.tabcategories_default option:visible').first().attr('selected', 'selected');*/
	}
	$('#TABBED_CAT_ALL_on').change(function () {
		$('.tabcategories_default option[value=0]').show();
	});
	$('#TABBED_CAT_ALL_off').change(function () {
		$('.tabcategories_default option[value=0]').hide();
		$('.tabcategories_default option').removeAttr('selected');
		/*$('.tabcategories_default option:visible').first().attr('selected', 'selected');*/
	});

	$('.tabcategorieshide').change(function () {
		$('.tabcategorieshide option').each(function (){
			if ($(this).attr('selected')) {
			$('.tabcategories_default option[value='+$(this).attr('value')+']').hide();
			}else {
			$('.tabcategories_default option[value='+$(this).attr('value')+']').show();
			}
		});
		$('.tabcategories_default option').removeAttr('selected');
		/*$('.tabcategories_default option:visible').first().attr('selected', 'selected');*/
	});

	$('.reset_cat').click(function(){
		$('.tabcategorieshide option').each(function (){
			$(this).removeAttr('selected');
			$('.tabcategorieshide option').each(function (){
				$('.tabcategories_default option[value='+$(this).attr('value')+']').show();
			});
		});
	});
	$('.reset_subcat').click(function(){
		$('.tabsubcategorieshide option').each(function (){
			$(this).removeAttr('selected');
		});
	});
        $('.reset_show_on_pages').click(function(){
		$('.tabshowonpages option').each(function (){
			$(this).removeAttr('selected');
		});
	});
});	
{/block}
