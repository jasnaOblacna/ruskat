

{block name='header'}
  {block name='header_nav'}
    <nav class="header-nav">
      <div class="container">
        <div class="row">
        <div class="col-md-12 text-xs-right hidden-sm-down">
            {hook h='displayNav1'}
          </div>
          <div class="hidden-md-up text-xs-center mobile">
            {hook h='displayNav2'}
            <div class="float-xs-left" id="menu-icon">
              <i class="fa fa-navicon"></i>
            </div>
            <div class="float-xs-right" id="_mobile_cart"></div>
            <div class="float-xs-right" id="_mobile_user_info"></div>
            <div class="top-logo" id="_mobile_logo"></div>
            <div class="clearfix"></div>
          </div>
        </div>
      </div>
    </nav>
  {/block}

  {block name='header_top'}
  <div class="header-top">
    <div class="header-top-wrapper">
      <div class="container">
         <div class="row">
        <div class="col-md-12 hidden-sm-down text-xs-left" id="_desktop_logo">
          <a href="{$urls.base_url}">
            <img data-desktop="{$shop.logo}" data-mobile="" class="logo img-responsive" src="{$shop.logo}" alt="{$shop.name}">
          </a>
            </div>
          </div>
        <div id="mobile_top_menu_wrapper" class="row hidden-md-up" style="display:none;">
          <div class="js-top-menu mobile" id="_mobile_top_menu"></div>
          <div class="js-top-menu-bottom">
            <div id="_mobile_currency_selector"></div>
            <div id="_mobile_language_selector"></div>
            <div id="_mobile_contact_link"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
    {hook h='displayNavFullWidth'}
  {/block}
{/block}

