<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2016 Intelligent Spark
 *
 * @package    Isotope Custom Step "Delivery Date"
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_iso_shipping
 */
$GLOBALS['TL_DCA']['tl_iso_shipping']['palettes']['__selector__'][] = 'or_pricing';

//old
#$GLOBALS['TL_DCA']['tl_iso_shipping']['palettes']['zones_advanced'] = '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},price,or_pricing,tax_class;{config_legend},upgrade_options,countries,subdivisions,postalCodes,minimum_total,maximum_total,product_types;{expert_legend:hide},guests,protected;{enabled_legend},enabled';

//new
$GLOBALS['TL_DCA']['tl_iso_shipping']['palettes']['zones_advanced'] = '{title_legend},name,label,type;{note_legend:hide},note;{price_legend},price,or_pricing,tax_class,flatCalculation;{config_legend},upgrade_options,countries,subdivisions,postalCodes,quantity_mode,minimum_quantity,maximum_quantity,minimum_total,maximum_total,minimum_weight,maximum_weight,product_types,product_types_condition,config_ids;{expert_legend:hide},guests,protected;{enabled_legend},enabled';

$GLOBALS['TL_DCA']['tl_iso_shipping']['subpalettes']['or_pricing'] = 'alternative_price,alternative_price_logic';

$GLOBALS['TL_DCA']['tl_iso_shipping']['fields']['or_pricing'] = array
(
    'label'			=> &$GLOBALS['TL_LANG']['tl_iso_shipping']['or_pricing'],
    'inputType'		=> 'checkbox',
    'eval'			=> array('submitOnChange'=>true,'tl_class'=>'w100 clr'),
    'sql'           => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_iso_shipping']['fields']['alternative_price'] = array
(
    'label'			=> &$GLOBALS['TL_LANG']['tl_iso_shipping']['alternative_price'],
    'inputType'		=> 'text',
    'eval'			=> array('mandatory'=>true,'tl_class'=>'w100'),
    'sql'           => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_iso_shipping']['fields']['alternative_price_logic'] = array
(
    'label'			=> &$GLOBALS['TL_LANG']['tl_iso_shipping']['alternative_price_logic'],
    'inputType'		=> 'select',
    'options'		=> array(1,2),
    'eval'			=> array('mandatory'=>true),
    'reference'		=> &$GLOBALS['TL_LANG']['tl_iso_shipping']['alternative_price_logic_options'],
    'sql'           => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_iso_shipping']['fields']['upgrade_options'] = array
(
    'label'			=> &$GLOBALS['TL_LANG']['tl_iso_shipping']['upgrade_options'],
    'inputType'		=> 'optionWizard',
    'sql'           => "mediumblob NULL"
);