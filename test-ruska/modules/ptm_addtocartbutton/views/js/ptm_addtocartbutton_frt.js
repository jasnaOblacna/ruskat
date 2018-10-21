$(document).ready(function() {

	function activateButtons(){
		$(".product-miniature .thumbnail-container").on({
		    mouseenter: function () {
		       $(this).find(".ptm-addtocartbutton-link").removeClass("ptm_hidden");
		    },
		    mouseleave:function () {
		       $(this).find(".ptm-addtocartbutton-link").addClass("ptm_hidden");
		    }
		});
	}

	activateButtons();

	$("body").on('DOMSubtreeModified', "#main", function() {
	    activateButtons();
	});

});