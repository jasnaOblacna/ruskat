/**
* 2016 - 2017 PrestaBuilder
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
*  @copyright 2016 - 2017 PrestaBuilder
*  @license   Do not distribute this module without permission from the author
*/

$(document).ready(function() {
	// display paypal logo image
	var paypal_logo = $("#display_paypal_logo").val();
	if (typeof paypal_logo !== 'undefined' && paypal_logo != '') {
		var imageContainer = '<div class="col-md-12 clear"><label class="control-label col-lg-3"></label><div class="col-lg-9 paypal_logo_container"><a id="removePaypalLogoBtn" class="paypal_logo_link" href="javascript:;">x</a><img src="'+ paypal_logo +'" /></div>';
		$(".paypal_logo_wrapper").append(imageContainer);

		$('#removePaypalLogoBtn').on('click', function(e) {
			e.preventDefault();
			$.ajax({
				url: $('#ptm_paypal_ajax_url').val(),
		        type: "POST",
		        data: {'action': 'removeLogo', token: $('#ptm_paypal_gen_token').val()},
				dataType: 'json',
          		success: function (data) {
          			console.log(data);
          			if (data.status == true) {
          				$('.paypal_logo_container').fadeOut('slow', function() {
          					$(this).remove();
          				});
          			}
          		}
			});
		});
	}
});