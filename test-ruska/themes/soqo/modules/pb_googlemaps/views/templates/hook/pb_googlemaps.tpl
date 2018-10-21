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
{if $gm_hook == 'displayHome'}
<div id="googlemaps" class="wow fadeInUp googlemaps-container pb-module pb-home container">
{else}
<div id="googlemaps-footer" class="googlemaps-container pb-module pb-home">
{/if}
  <div class="row">
  	{if isset($gm_title) && $gm_title}
        {if $gm_hook == 'displayHome'}
            <h2 class="h2 products-section-title text-uppercase pb-title">{$gm_title|escape:'htmlall':'UTF-8'}</h2>
        {else}
            <h4 class="text-uppercase block-contact-title">{$gm_title|escape:'htmlall':'UTF-8'}</h4>
        {/if}
    {/if}
    <div class="gm-container">
    	{if isset($gm_api_key) && $gm_api_key}
	    	{if $gm_hook == 'displayHome'}
		      <div id="googlemaps-wrapper" style="height:{$gm_height|intval}px;"></div>
		    {else}
	      		<div id="googlemaps-footer-wrapper" style="height:{$gm_height|intval}px;"></div>
		    {/if}
	    {else}
	    	<img src="{$preview_img|escape:'html':'UTF-8'}" alt="{$shop.name|escape:'htmlall':'UTF-8'}" />
	    {/if}
    </div>
  </div>
</div>
{if isset($gm_api_key) && $gm_api_key}
<script>
{literal}
var gm_map, gm_preview = 'googlemaps-footer-wrapper';

if (pb_gm_hook == 'displayHome') {
	gm_preview = 'googlemaps-wrapper';
}

function gmInitMap() {
	gm_map = new google.maps.Map(document.getElementById(gm_preview), {
		center: {lat: {/literal}pb_gm_location_lat{literal}, lng: {/literal}pb_gm_location_lng{literal}},
        zoom: parseInt(pb_gm_zoom_level) || 12,
        zoomControl: {/literal}pb_gm_zooming{literal},
        scaleControl: {/literal}pb_gm_zooming{literal},
        scrollwheel: {/literal}pb_gm_zooming{literal},
        disableDoubleClickZoom: {/literal}!pb_gm_zooming{literal},
	});

	var marker = new google.maps.Marker({
        map: gm_map,
        anchorPoint: new google.maps.Point(0, -29),
        position: new google.maps.LatLng({/literal}pb_gm_location_lat{literal}, {/literal}pb_gm_location_lng{literal})
    });
}{/literal}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={$gm_api_key|escape:'html':'UTF-8'}&callback=gmInitMap" async defer></script>
{/if}