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

use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopeShipping;
use Isotope\Isotope;
use Isotope\Model\Shipping;


/**
 * Class Flat
 *
 * @property string flatCalculation
 */
class ZonesAdvanced extends Shipping implements IsotopeShipping
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

    /*public function calculateShippingRate($intPid, $fltCartSubTotal)
    {
        $objRates = $this->Database->prepare("SELECT * FROM tl_iso_shipping WHERE pid=?")
            ->execute($intPid);

        if($objRates->numRows < 1)
        {
            return 0;
        }

        $arrData = $objRates->fetchAllAssoc();

        //get the basic rate - calculate it based on group '0' first, which is the default, then any group NOT 0.
        foreach($arrData as $row)
        {
            //determine value ranges
            if((float)$row['minimum_total']>0 && $fltCartSubTotal>=(float)$row['minimum_total'])
            {
                if($fltCartSubTotal<=(float)$row['maximum_total'] || $row['maximum_total']==0)
                {
                    $fltRate = $row['rate'];
                }
            }
            elseif((float)$row['maximum_total']>0 && $fltCartSubTotal<=(float)$row['maximum_total'])
            {
                if($fltCartSubTotal>=(float)$row['minimum_total'])
                {
                    $fltRate = $row['rate'];
                }
            }

        }

        return $fltRate;

    }*/

    /**
     * shipping exempt items should be subtracted from the subtotal
     * @param float
     * @return float
     */
    public function getAdjustedSubTotal($fltSubtotal)
    {

        $arrProducts = (TL_MODE=='FE' ? $this->Isotope->Cart->getProducts() : $this->Isotope->Order->getProducts());

        foreach($arrProducts as $objProduct)
        {
            if($objProduct->shipping_exempt)
            {
                $fltSubtotal -= ($objProduct->price * $objProduct->quantity_requested);
            }

        }

        return $fltSubtotal;
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

    /**
     * Get the checkout surcharge for this shipping method
     */
    public function getSurcharge($objCollection)
    {
        return $this->Isotope->calculateSurcharge(
            $this->price,
            ($GLOBALS['TL_LANG']['MSC']['shippingLabel'] . ' (' . $this->label . ')'.(intval($this->price)==0 ? ' - FREE!' : '')),
            $this->arrData['tax_class'],
            $objCollection->getProducts(),
            $this);
    }
}