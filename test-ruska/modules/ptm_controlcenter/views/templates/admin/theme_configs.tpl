<script>
var addthemeStr = "{l s='Add new PTM theme' mod='ptm_controlcenter'}";
var updatethemeStr = "{l s='Update this theme' mod='ptm_controlcenter'}";
var isPtmTheme = "{$is_ptm_theme}", ptm_themes = "{$ptm_themes}";
$(document).ready(function() {
	var ul = $('#toolbar-nav'), theme_form_container = $('#js_theme_form_container');

	ul.prepend('<li><a class="toolbar_btn pointer" href="{$ptm_cc_url}&current_tab=1"><i class="process-icon-new"></i><div>'+ addthemeStr +'</div></a></li>');

	if (parseInt(isPtmTheme) == 1) {
		theme_form_container.prepend('<div class="col-sm-2 pull-right"><a class="btn btn-default" href="{$ptm_cc_url}&current_tab=2&theme_name={$theme_name}"><i class="icon icon-edit"></i>'+ updatethemeStr +'</a></div>');
	}

	if (ptm_themes != "") {
		// convert it to array
		var themestoArray = ptm_themes.split(',');
		// iterate through the list of installed themes and determin which is PTM theme
		$('#conf_id_theme_for_shop h4.theme-title').each(function() {
			var theme_name = $(this).text().trim();
			if (jQuery.inArray(theme_name, themestoArray) > -1) {
				$(this).parent().find('.dropdown-menu').prepend('<li><a href="{$ptm_cc_url}&current_tab=2&theme_name='+ theme_name +'" title="'+ updatethemeStr +'" class="edit"><i class="icon-edit"></i> '+ updatethemeStr +'</a></li>');
			}
		});
	}
});
</script>