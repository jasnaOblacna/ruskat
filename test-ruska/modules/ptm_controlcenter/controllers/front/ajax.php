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

class Ptm_ControlcenterAjaxModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        ob_end_clean();
        header('Content-Type: application/json');
        $action = Tools::getValue('action');

        if (($action && $action == 'checkUpdatesList')
            && (int)Tools::getValue('ajax')) {
            if (PTM_CCUtils::canUpdateModulesList()) {
                echo Tools::jsonEncode($this->module->getModulesListUpdates());
                Configuration::updateValue('PTM_CONTROLCENTER_LAST_MODS_UPDATED', strtotime('-60 minutes'));
                die();
            } else {
                if ($cached_mods = Configuration::get('PTM_CONTROLCENTER_CACHED_MODS')) {
                    echo Tools::jsonEncode(unserialize($cached_mods));
                    die();
                }
            }
        }
        echo Tools::jsonEncode(array()); die();
    }
}
