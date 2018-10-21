<?php
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

class AdminPTMControlCenterUpdateThemeModulesController extends ModuleAdminController
{
    public function __construct()
    {
        $this->context   = Context::getContext();
        $this->display   = 'view';
        parent::__construct();

        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules'));
        }

        $redirectLink = $this->context->link->getAdminLink('AdminModules', true).'&configure='. $this->module->name .'&current_tab=2';

        Tools::redirectAdmin($redirectLink);
    }
}