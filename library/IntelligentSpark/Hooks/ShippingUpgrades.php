<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace IntelligentSpark\Hooks;

use Isotope\Isotope;
use Isotope\Template;

class ShippingUpgrades {

    /**
     * @param $objCheckoutStep
     * @return void
     */
    public function shippingMethodSubmit($objCheckoutStep) {
        //\Input::post('shipping'));

    }

    /**
     * @param $objCheckoutStep
     * @param $objShippingModule
     * @return string;
     */
    public function getShippingUpgrades($objCheckoutStep,$arrModules) {

        $objTemplate = new Template('iso_checkout_step_shipping_upgrades');

        $objTemplate->modules = $arrModules;

        $arrUpgrades = array();

        foreach($arrModules as $module) {
            $arrUpgrade = array();
            $arrUpgrade['options'] = deserialize($module->upgrade_options,true);

            $arrUpgrades[] = $arrUpgrade;
        }

        $objTemplate->upgrades = $arrUpgrades;

        return array();
    }

    public function preCheckout($objOrder, $objModule) {
        $objOrder->delivery_date = Isotope::getCart()->delivery_date;
        $objOrder->save();
        \System::log("delivery date as saved: ".$objOrder->delivery_date,__METHOD__,TL_GENERAL);
        return true;
    }
}