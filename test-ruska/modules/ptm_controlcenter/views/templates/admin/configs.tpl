<script> 
var selectedTheme = "{$selected_theme_name}", controller_ajax_url = "{$controller_ajax_url}";
$(document).ready(function() {
	// hook second bootstrap class
	{literal}var tabs_wrapper = $('#content .bootstrap').eq(1), ptm_cc_tabs = [
		{'id': 1, 'title': "{/literal}{l s='Add a new theme' mod='ptm_controlcenter'}{literal}"},
		{'id': 2, 'title': "{/literal}{l s='Update a theme and modules' mod='ptm_controlcenter'}{literal}"},
		{'id': 3, 'title': "{/literal}{l s='Settings' mod='ptm_controlcenter'}{literal}"}{/literal}
	], appended_tabs = '<div class="page-head-tabs">', get_current_tab = "{if isset($current_tab) && $current_tab}{$current_tab}{else}1{/if}";
	// add extra classes
	tabs_wrapper.find('.page-head').addClass('with-tabs ptm_control_center_tabs');
	// display current tab content
	$('#ptm_cc_tab_'+ get_current_tab).removeClass('ptm_cc_hidden');

	// iterate through tabs and set current one
	for (var i = 0; i < ptm_cc_tabs.length; i++) {
		var tab = ptm_cc_tabs[i], add_class = (parseInt(get_current_tab) == parseInt(tab.id) ? 'current' : '');
		appended_tabs += '<a class="ptm_cc_tab '+ add_class +'" href="{$ptm_cc_url}&current_tab='+ tab.id +'">'+ tab.title +'</a>';
	}
	appended_tabs += '<div>';
	$('.ptm_control_center_tabs').append(appended_tabs);

	// select gieven theme in querystring 
	if (get_current_tab == 2 && selectedTheme != "") {
		$("select#theme_name>option[value='"+ selectedTheme +"']").prop('selected', true);
	}

	// add dropdown list in themes list when hover over
	$('div.thumbnail-wrapper').hover(function() {
		var w = $(this).parent('div').outerWidth(true);
		var h = $(this).parent('div').outerHeight(true);
		$(this).children('.action-wrapper').css('width', w+'px');
		$(this).children('.action-wrapper').css('height', h+'px');
		$(this).children('.action-wrapper').show();
	  }, function() {
		$('.thumbnail-wrapper .action-wrapper').hide();
	});

	// update modulesl list
	{literal}setTimeout(function() {
		$.post(controller_ajax_url, {ajax: 1,action: 'checkUpdatesList'}, function(res) {
				if (res && res.length > 0) {
					var get_status = true, outdated_mods = [];
					for (var i = 0; i < res.length; i++) {
						$('.btn_ptm_cc_'+ res[i].name).text(res[i].message);
						if (res[i].status == false) {
							get_status = false;
							outdated_mods.push(res[i].name);
						}
					}

					if (get_status == true) {
						$('#ptm_cc_success_msg').removeClass('ptm_cc_hidden');
					} else {
						for (var m = 0; m < outdated_mods.length; m++) {
							$('#ptm_cc_errors_msg .outdated_modules_list').append('<li>'+ outdated_mods[m] +'</li>');
						}
						$('#ptm_cc_errors_msg').removeClass('ptm_cc_hidden');
					}
				}
			}, 'json');
	}, 3000);{/literal}
	
});
</script>