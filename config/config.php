<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2016 Intelligent Spark
 *
 * @package Isotope Shipping Module "Zones Advanced"
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


\Isotope\Model\Shipping::registerModelType('zones_advanced', 'IntelligentSpark\Model\Shipping\ZonesAdvanced');

$GLOBALS['ISO_HOOKS']['shippingMethodSubmit'][] = ['IntelligentSpark\Hooks\ShippingUpgrades','shippingMethodSubmit'];
$GLOBALS['ISO_HOOKS']['renderUpgradeOptions'][] = ['IntelligentSpark\Hooks\ShippingUpgrades','renderUpgradeOptions'];