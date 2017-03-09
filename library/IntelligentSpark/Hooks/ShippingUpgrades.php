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
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollectionSurcharge\Shipping;
use Isotope\Template;

class ShippingUpgrades {

    /**
     * @param $objCheckoutStep
     * @return void
     */
    public function shippingMethodSubmit($arrModules,$intModuleId) {

        if(\Input::post('previousStep'))
            return;

        $varValue = current(\Input::post('shipping_upgrade'));

        //reset value;
        if($varValue=='') {
            Isotope::getCart()->shipping_upgrade = null;
            return;
        }

        //this really is a very quick fix.  Re-design this!
        foreach($arrModules as $module) {
            if (strlen($module->upgrade_options) == 0)
                continue;

            $arrUpgrades = array();

            $arrUpgrades = deserialize($module->upgrade_options, true);

            $strSurchargeLabel = '';

            foreach($arrUpgrades as $upgrade) {
                if($upgrade['value']==$varValue && (int)$varValue>0) {
                    $arrUpgrade['value'] = $varValue;
                    $arrUpgrade['label'] = $upgrade['label'];
                    Isotope::getCart()->shipping_upgrade = $arrUpgrade;
                    return;
                }
            }

            return;
        }


    }

    /**
     * @param $objCheckoutStep
     * @param $objShippingModule
     * @return string;
     */
    public function getShippingUpgrades($arrModules,$intModuleId) {

        $objTemplate = new Template('iso_checkout_step_shipping_upgrades');

        $arrUpgradeGroups = array();

        foreach($arrModules as $module) {
            if(strlen($module->upgrade_options)==0)
                continue;

            $arrUpgrades = array();
            $blnSet = false;    //indicator whether upgrade option is set or not

            $arrUpgrades = deserialize($module->upgrade_options,true);

            foreach($arrUpgrades as $i=>$upgrade) {

                //default setting
                $arrUpgrades[$i]['checked'] = false;

                //set option value
                if($upgrade['value']==Isotope::getCart()->shipping_upgrade['value']) {

                    $arrUpgrades[$i]['checked'] = true;
                    $blnSet = true;
                }
            }

            $objTemplate->module_id = $intModuleId;
            $objTemplate->options = $arrUpgrades;
            $objTemplate->blnSet = $blnSet;

            $arrUpgradeGroups[$intModuleId] = $objTemplate->parse();
        }

        return $arrUpgradeGroups;
    }

    public function findSurchargesForCollection($objCollection) {

        if(!Isotope::getCart()->shipping_upgrade)
            return array();

        $arrShippingUpgrade = Isotope::getCart()->shipping_upgrade;

        $objSurcharge = new \Isotope\Model\ProductCollectionSurcharge\Shipping;
        $objSurcharge->label = $arrShippingUpgrade['label'];
        $objSurcharge->price = $arrShippingUpgrade['value'];
        $objSurcharge->total_price = $arrShippingUpgrade['value'];
        $objSurcharge->tax_free_total_price = $arrShippingUpgrade['value'];
        $objSurcharge->tax_class = false;
        $objSurcharge->before_tax = false;
        $objSurcharge->addToTotal = true;

        $objSurcharge->save();

        return array($objSurcharge);
    }

    public function preCheckout($objOrder, $objModule) {
        $objOrder->delivery_date = Isotope::getCart()->delivery_date;
        $objOrder->save();
        \System::log("delivery date as saved: ".$objOrder->delivery_date,__METHOD__,TL_GENERAL);
        return true;
    }

    /**
     * Build a product collection surcharge for given class type
     *
     * @param string                         $strClass
     * @param string                         $strLabel
     * @param IsotopePayment|IsotopeShipping $objSource
     * @param IsotopeProductCollection       $objCollection
     *
     * @return ProductCollectionSurcharge
     */

    protected function buildSurcharge($strClass, $strLabel, $intPrice, $objCollection)
    {
        $intTaxClass = false;

        /** @var \Isotope\Model\ProductCollectionSurcharge $objSurcharge */
        $objSurcharge = new $strClass();
        $objSurcharge->label = $strLabel;
        $objSurcharge->price = $intPrice;
        $objSurcharge->total_price = $intPrice;
        $objSurcharge->tax_free_total_price = $intPrice;
        $objSurcharge->tax_class = false;
        $objSurcharge->before_tax = false;
        $objSurcharge->addToTotal = true;

        $objSurcharge->save();
    }
}