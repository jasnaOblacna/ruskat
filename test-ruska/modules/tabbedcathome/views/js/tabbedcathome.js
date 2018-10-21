/**
 * This source file is subject to a commercial license from AZELAB
 *
 * @package   Tabbed Featured Categories Subcategories on Home
 * @author    AZELAB
 * @copyright Copyright (c) 2014 AZELAB (http://www.azelab.com)
 * @license   Commercial license
 * Support by mail:  support@azelab.com
*/

$(document).ready(function(){

	$('#filterCat li').each(function(){
		var cat = $(this).find('.homefeatured').attr('data-filter');
		if($(this).hasClass('active')){
			if(cat != '*'){
				$(cat).show();
			}else{
				$('.isotopeCat').show();
				return false;
			}
		}else{
			if(cat != '*'){
				$(cat).hide();
			}
		}
	});
	
});
$(window).load(function() {
	
	$('.isotopeCat').each(function(){
		$(this).removeClass('hidestart');
	});
	
	// init Isotope
	var $container = $('#isotopeCategories').isotope({
		itemSelector: '.isotopeCat',
		filter: $('#filterCat .active .homefeatured').attr('data-filter'),
		layoutMode: "fitRows"
	});

	$('#filterCat').on( 'click', '.homefeatured', function(event) {
		event.preventDefault();
		$container.isotope({ filter: $(this).attr('data-filter') });
	});

	// change active class on buttons
	$('#filterCat').on( 'click', 'li', function() {
		$('#filterCat').find('.active').removeClass('active');
		$(this).addClass('active');
	});
});