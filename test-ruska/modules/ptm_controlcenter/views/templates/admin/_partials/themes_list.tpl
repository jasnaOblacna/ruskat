{if isset($themes) && $themes}
<form class="defaultForm form-horizontal">
<div id="controlcenter_fieldset_theme" class="panel">
	<div class="panel-heading">
		{l s='List of PrestaBuilder themes' mod='ptm_controlcenter'}
	</div>
	<div class="form-wrapper">
	  <div class="form-group">
	    <div id="conf_id_theme_for_shop">
	  	  <div class="col-lg-12">
	  	  	<div class="row">
				{foreach from=$themes item=theme}
				  <div class="col-sm-4 col-lg-3">
				  	<div class="theme-container">
				  		<h4 class="theme-title">{$theme.theme_name|escape:'html'} v{$theme.version|escape:'html'}</h4>
				  		<div class="thumbnail-wrapper">
			  			{if $theme.theme_name|lower != $activated_theme}
				  			<div class="action-wrapper" style="display: none;">
								<div class="action-overlay"></div>
								<div class="action-buttons">
									<div class="btn-group">
										<a href="{$admin_theme_url|escape:'html'}&action=enableTheme&theme_name={$theme.theme_name|lower|escape:'html'}" class="btn btn-default">
											<i class="icon-check"></i> {l s='Use this theme' mod='ptm_controlcenter'}
										</a>
									</div>
								</div>
							</div>
				  		{/if}
				  			<img class="center-block img-thumbnail" src="{$theme.theme_preview|escape:'html'}" alt="{$theme.theme_name|escape:'html'}">
				  		</div>
				  	</div>
				  </div>
				{/foreach}
	  	  	</div>
	  	  </div>
	    </div>
	  </div>
	</div>
</div>
</form>
{/if}