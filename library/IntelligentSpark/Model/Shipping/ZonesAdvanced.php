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

namespace IntelligentSpark\Model\Shipping;

use Haste\Units\Mass\Weight;
use Isotope\Interfaces\IsotopeProductCollection as IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Shipping;


/**
 * Class Flat
 *
 * @property string flatCalculation
 */
class ZonesAdvanced extends Shipping
{

    /**
     * Return calculated price for this shipping method
     * @return float
     */
    public function getPrice(IsotopeProductCollection $objCollection = null)
    {
        if (null === $objCollection) {
            $objCollection = Isotope::getCart();
        }

        if($this->or_pricing=='1')
        {
            $fltAltPrice = $objCollection->getSubtotal() / 100 * floatval($this->alternative_price);

            switch($this->alternative_price_logic)
            {
                case '1': //less
                    $fltPrice = ($this->arrData['price']<$fltAltPrice ? $this->arrData['price'] : $fltAltPrice);
                    break;
                case '2':	//greater
                    $fltPrice = ($this->arrData['price']>$fltAltPrice ? $this->arrData['price'] : $fltAltPrice);
                    break;
            }

            return $fltPrice;
        }else{
            return $this->arrData['price'];
        }

        return Isotope::calculatePrice($fltPrice, $this, 'price', $this->arrData['tax_class']);
    }

    /**
     * shipping exempt items should be subtracted from the subtotal
     * @param float
     * @return float
     */
    public function getAdjustedSubTotal($fltSubtotal)
    {

        $arrItems = (TL_MODE=='FE' ? Isotope::getCart()->getItems() : Isotope::getCart()->getDraftOrder()->getItems());

        foreach($arrItems as $objItem)
        {
            $objProduct = $objItem->getProduct();

            if($objProduct->shipping_exempt)
            {
                $fltSubtotal -= ($objProduct->price * $objProduct->quantity_requested);
            }

        }

        return $fltSubtotal;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $this->initializeModules();

        if (empty($this->modules)) {
            $this->blnError = true;

            \System::log('No shipping methods available for cart ID ' . Isotope::getCart()->id, __METHOD__, TL_ERROR);

            /** @var Template|\stdClass $objTemplate */
            $objTemplate           = new Template('mod_message');
            $objTemplate->class    = 'shipping_method';
            $objTemplate->hl       = 'h2';
            $objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['shipping_method'];
            $objTemplate->type     = 'error';
            $objTemplate->message  = $GLOBALS['TL_LANG']['MSC']['noShippingModules'];

            return $objTemplate->parse();
        }

        /** @var \Widget $objWidget */
        $objWidget = new $GLOBALS['TL_FFL']['radio'](
            [
                'id'          => $this->getStepClass(),
                'name'        => $this->getStepClass(),
                'mandatory'   => true,
                'options'     => $this->options,
                'value'       => Isotope::getCart()->shipping_id,
                'storeValues' => true,
                'tableless'   => true,
            ]
        );

        // If there is only one shipping method, mark it as selected by default
        if (count($this->modules) === 1) {
            $objModule        = reset($this->modules);
            $objWidget->value = $objModule->id;
            Isotope::getCart()->setShippingMethod($objModule);
        }

        if (\Input::post('FORM_SUBMIT') == $this->objModule->getFormId()) {
            $objWidget->validate();

            if (!$objWidget->hasErrors()) {
                Isotope::getCart()->setShippingMethod($this->modules[$objWidget->value]);
            }
        }

        if (!Isotope::getCart()->hasShipping() || !isset($this->modules[Isotope::getCart()->shipping_id])) {
            $this->blnError = true;
        }

        /** @var Template|\stdClass $objTemplate */
        $objTemplate                  = new Template('iso_checkout_shipping_method');
        $objTemplate->headline        = $GLOBALS['TL_LANG']['MSC']['shipping_method'];
        $objTemplate->message         = $GLOBALS['TL_LANG']['MSC']['shipping_method_message'];
        $objTemplate->options         = $objWidget->parse();
        $objTemplate->shippingMethods = $this->modules;

        return $objTemplate->parse();
    }

}