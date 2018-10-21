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

{if isset($faq) && $faq|count}
<div class="accordion_in card-color{if $faq.open == 1} acc_active card-inner-color{/if}">
  <div class="acc_head"><i class="fa fa-{if $faq.open == 1}minus{else}plus{/if}-circle" aria-hidden="true"></i>{$faq.question|escape:'htmlall':'UTF-8'}</div>
  <div class="acc_content">
    {$faq.answer nofilter}
  </div>
</div>
{/if}