/**
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
*/
/* global $, prestashop */

/**
 * This module exposes an extension point in the form of the `showModal` function.
 *
 * If you want to override the way the modal window is displayed, simply define:
 *
 * prestashop.blockcart = prestashop.blockcart || {};
 * prestashop.blockcart.showModal = function myOwnShowModal (modalHTML) {
 *   // your own code
 *   // please not that it is your responsibility to handle closing the modal too
 * };
 *
 * Attention: your "override" JS needs to be included **before** this file.
 * The safest way to do so is to place your "override" inside the theme's main JS file.
 *
 */

$(document).ready(function () {

  $('#_mobile_cart').addClass('blockcart_container');
  prestashop.blockcart = prestashop.blockcart || {};

  var showModal = prestashop.blockcart.showModal || function (modal) {
    var $body = $('.ptm_blockcart_body');
    $body.append(modal);
    $body.one('click', '#blockcart-modal', function (event) {
      if (event.target.id === 'blockcart-modal') {
        $(event.target).remove();
      }
    });
  };

    //send the ajax request to the server
  prestashop.blockcart.remove = function(id_product, id_product_attribute, id_product_customization) {
      var refreshURL = $('.ptm_blockcart').data('refresh-url'),
        requestData = {
          id_product_attribute: id_product_attribute,
          id_product: id_product,
          id_product_customization: id_product_customization,
          action: 'removeProduct'
        };

      $.post(refreshURL, requestData).then(function (resp) {
          $("ul.ptm_blockcart_list li.dropdown_cart_product_"+ id_product).fadeOut('fast', function() {
            $(this).remove();
            // $('.blockcart_container').replaceWith(resp.preview);
            $.post(refreshURL, requestData).then(function (resp) {
              $('.blockcart_container').replaceWith(resp.preview);
              $('.mobile #_desktop_cart').attr("id","_mobile_cart");
              $('.mobile #_mobile_cart').addClass('float-xs-right');
            }).fail(function (resp) {
              prestashop.emit('handleError', {eventType: 'updateShoppingCart', resp: resp});
            });
          });
      }).fail(function (resp) {
          prestashop.emit('handleError', {eventType: 'updateShoppingCart', resp: resp});
      });
  }

    prestashop.on(
      'updateCart',
      function (event) {
        var refreshURL = $('.ptm_blockcart').data('refresh-url');
        var requestData = {};

        if (event && event.reason) {
          requestData = {
            id_product_attribute: event.reason.idProductAttribute,
            id_product: event.reason.idProduct,
            action: event.reason.linkAction
          };
        }

        $.post(refreshURL, requestData).then(function (resp) {
          $('.blockcart_container').replaceWith(resp.preview);
          $('.mobile #_desktop_cart').attr("id","_mobile_cart");
          $('.mobile #_mobile_cart').addClass('float-xs-right');
          if (resp.modal) {
            showModal(resp.modal);
          }
        }).fail(function (resp) {
          prestashop.emit('handleError', {eventType: 'updateShoppingCart', resp: resp});
        });


      }
    );

    // detect touch screens
    var is_touch_enabled = false;
    var cart_block = new HoverWatcher('#header .ptm_blockcart');
    var dropdowncart = new HoverWatcher('#header #dropdowncart_url');

    var firstclick = true;
 
    if ('ontouchstart' in document.documentElement)
      is_touch_enabled = true;

    $(document).on('click', '#header .ptm_blockcart a:first', function(e){

      e.preventDefault();
      e.stopPropagation();

      if(firstclick){
        $('#header .ptm_blockcart_body').css('display', 'none');
      }

      // Simulate hover when browser says device is touch based
      if (is_touch_enabled) {
        if($('#header .ptm_blockcart').hasClass('display_mobile_cart')){
          if ($('#header .ptm_blockcart_body').css('display') == 'none' || firstclick) {
            $("#header .ptm_blockcart_body").dequeue().stop(true, true).slideDown(250);
            firstclick = false;
          } else {
            $("#header .ptm_blockcart_body").dequeue().stop(true, true).slideUp(250);
          }
        }else{
          window.location.href = $(this).attr('href');
        }
      } else{
        window.location.href = $(this).attr('href');
      }
    });

    if (!is_touch_enabled) {
      $(document).on("mouseenter", ".ptm_blockcart", function(e) {
        var products_count = parseInt($(".ptm_cart_qty").data('cart-qty'));
        if (products_count > 0) {
          $(this).find('.ptm_blockcart_body').dequeue().stop(true, true).slideDown(250);
        }
      });

      $(document).on("mouseleave", ".ptm_blockcart", function(e) {
          $(this).find('.ptm_blockcart_body').dequeue().stop(true, true).hide();
      });
    }

    // delete a product from cart
    $(document).off('click', '.ptm_blockcart_list .ajax_cart_block_remove_link').on('click', '.ptm_blockcart_list .ajax_cart_block_remove_link', function(e){
        e.preventDefault();
        // Removing product from the cart
        prestashop.blockcart.remove(parseInt($(this).data('product')), parseInt($(this).data('product-attribute')), parseInt($(this).data('product-customization')));
    });
});

function HoverWatcher(selector)
{
  this.hovering = false;
  var self = this;

  this.isHoveringOver = function(){
    return self.hovering;
  }

  $(selector).hover(function(){
    self.hovering = true;
  }, function(){
    self.hovering = false;
  })
}