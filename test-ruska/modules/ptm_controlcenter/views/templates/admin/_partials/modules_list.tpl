{if isset($modules) && $modules}
<div class="row">
    <div class="col-lg-12">
    	<div class="ptm_cc_hidden" id="ptm_cc_errors_msg">
    		<div class="alert alert-info">{l s='These modules from PrestaBuilder are outdated and a newer version is available:' mod='ptm_controlcenter'}
    		<ul class="outdated_modules_list"></ul>
    		{l s='Please visit %prestathememaker%, redownload your theme from your account page for free, and update your current theme with only " Update modules " checked' sprintf=['%prestathememaker%' => '<a href="https://prestabuilder.com" target="_blank">PrestaBuilder</a>'] mod='ptm_controlcenter'} 
    		</div>
    	</div>
    	<div class="ptm_cc_hidden" id="ptm_cc_success_msg">
    		<div class="alert alert-success">{l s='All your modules from %prestathememaker% are up-to-date' sprintf=['%prestathememaker%' => '<a href="https://prestabuilder.com" target="_blank">PrestaBuilder</a>'] mod='ptm_controlcenter'}</div>
    	</div>
	    <div class="clearfix"></div>
    </div>
    <div class="col-lg-12 col-lg-offset-0">
    	<div class="module-short-list">
			<span class="module-search-result-wording">{$modules|count} {l s='installed PrestaBuilder modules' mod='ptm_controlcenter'}</span>
           	<span class="help-box" data-toggle="popover"
                data-title="{l s='Installed modules' mod='ptm_controlcenter'}"
                data-content="{l s='These are all the modules you\'ve purchased from PrestaBuilder' mod='ptm_controlcenter'}">
           	</span>
       </div>
    </div>
    <div class="col-lg-12 col-lg-offset-0">
        <div id="modules-list-container-all" class="row modules-list">
    	{foreach from=$modules item=module}
    		<div class="ptm-cc-module-item module-item module-item-list col-md-12" data-mod-name="{$module->name|escape:'html':'UTF-8'}" data-mod-version="{$module->version|escape:'html':'UTF-8'}">
    			<div class="container-fluid">
    				<div class="module-item-wrapper-list row">
    					<div class="module-logo-thumb-list col-sm-12 col-md-12 col-lg-1 text-sm-center">
					        <img src="{$base_url|escape:'html':'UTF-8'}modules/{$module->name|escape:'html':'UTF-8'}/logo.png" class="text-md-center" alt="{$module->displayName|escape:'html':'UTF-8'}" />
					    </div>
					    <div class="col-sm-12 col-md-10 col-lg-11">
					    	<h3 class="text-ellipsis module-name-list" data-toggle="tooltip" data-placement="top" title="" data-original-title="{$module->displayName|escape:'html':'UTF-8'}">
		                      {$module->displayName|escape:'html':'UTF-8'}
		                  	</h3>
					    </div>
					    <div class="col-sm-12 col-md-2">
					    	<span class="text-ellipsis xsmall">
				                v{$module->version|escape:'html':'UTF-8'} - {l s='by' mod='ptm_controlcenter'} <b>{$module->author|escape:'html':'UTF-8'}</b>
				          	</span>
					    </div>
					    <div class="col-sm-12 col-md-8 col-lg-6">
					    	{$module->description}
					    </div>
					    <div class="col-sm-12 col-md-12 col-lg-3 text-md-right">
					    	<div class="pull-right btn-group">
					    		<a class="btn btn-primary-reverse btn-primary-outline light-button module_action_menu_configure btn_ptm_cc_{$module->name|escape:'html':'UTF-8'}" href="javascript:;" data-confirm_modal="module-modal-confirm-ptm_controlcenter-configure">{l s='Checking...' mod='ptm_controlcenter'}</a>
					    	</div>
					    </div>
    				</div>
    			</div>
    		</div>
    	{/foreach}
        </div>
	</div>
</div>
{/if}