
<div class="footer-before-container">
  <div class="footer-before-container-wrapper">
      <div class="container">
  <div class="row">
    {block name='hook_footer_before'}
      {hook h='displayFooterBefore'}
    {/block}
  </div>
</div>
    </div>
</div>
<div class="footer-container">
  <div class="footer-container-wrapper">
  <div class="container">
    <div class="row" id="footermodules">
      {block name='hook_footer'}
        {hook h='displayFooter'}
      {/block}
    </div>
    <div class="row">
      {block name='hook_footer_after'}
        {hook h='displayFooterAfter'}
      {/block}
    </div>
    <div class="row">
      <div class="col-md-12" id="copyright">
        <p>
          {block name='copyright_link'}
            {include file='./copyright.tpl'}
          {/block}
        </p>
      </div>
    </div>
  </div>
</div>
</div>

