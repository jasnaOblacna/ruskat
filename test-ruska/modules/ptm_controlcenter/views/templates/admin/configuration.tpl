<div id="ptm_cc_tab_{$current_tab}" class="ptm_cc_panel">
	{$render_form}

	{if isset($show_themes_list) && $show_themes_list}
		{include file='./_partials/themes_list.tpl' themes=$themes_list}
	{/if}
	{if isset($show_modules_list) && $show_modules_list}
	<div class="panel">
		{include file='./_partials/modules_list.tpl' modules=$modules_list}
	</div>
	{/if}
</div>