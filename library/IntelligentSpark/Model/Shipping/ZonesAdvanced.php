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
use Isotope\Interfaces\IsotopeShipping;
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
     * Returns the ID of this shipping method.
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Return boolean flag if the shipping method is available
     * @return  bool
     */
    public function isAvailable() {
        return true;
    }

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
            $fltAltPrice = $objCollection->subTotal - ($objCollection->subTotal / (1 + (floatval($this->alternative_price) / 100)));
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
        $objTemplate->shippingMethods11  = $this->modules;

        return $objTemplate->parse();
    }

    /**
     * Return information or advanced features in the backend.
     * Use this function to present advanced features or basic shipping information for an order in the backend.
     * @param integer
     * @return string
     */
    public function backendInterface($orderId) {

    }

    /**
     * Return the checkout review information.
     *
     * Use this to return custom checkout information about this shipping module.
     * Example: Information about tracking codes.
     * @return string
     */
    public function checkoutReview() {

    }

    /**
     * Get the checkout surcharge for this shipping method
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return Shipping|null
     */
    public function getSurcharge(IsotopeProductCollection $objCollection) {

    }

    /**
     * Initialize the module options DCA in backend
     *
     * @access public
     * @return string
     */
    public function moduleOptionsLoad()
    {
        $GLOBALS['TL_DCA']['tl_iso_shipping']['palettes']['default'] = '{general_legend},name,description;{config_legend},rate,minimum_total,maximum_total';
    }


    /**
     * List module options in backend
     *
     * @access public
     * @return string
     */
    public function moduleOptionsList($row)
    {
        return '
<div class="cte_type ' . $key . '"><strong>' . $row['name'] . '</strong></div>
<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h52' : '') . ' block">
'. $GLOBALS['TL_LANG']['tl_iso_shipping']['option_type'][0] . ': ' . $GLOBALS['TL_LANG']['tl_iso_shipping']['types'][$row['option_type']] . '<br><br>' . $row['rate'] .' for '. $row['upper_limit'] . ' based on ' . $row['dest_country'] .', '. $row['dest_region'] . ', ' . $row['dest_zip'] . '</div>' . "\n";
    }

    public function getShippingOptions(&$objModule)
    {
        $arrOptions = deserialize($this->upgrade_options,true);

        if(count($arrOptions))
        {
            $objTemplate = new IsotopeTemplate('iso_checkout_shipping_options');
            $objTemplate->module_id = $this->id;
            $objTemplate->options = $arrOptions;

            return $objTemplate->parse();
        }

        return '';
    }


}