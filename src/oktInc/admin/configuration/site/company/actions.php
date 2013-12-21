<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Configuration du site société (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;

if (!empty($_POST['form_sent']))
{
	$p_company_name = !empty($_POST['p_company_name']) ? $_POST['p_company_name'] : '';
	$p_company_com_name = !empty($_POST['p_company_com_name']) ? $_POST['p_company_com_name'] : '';
	$p_company_siret = !empty($_POST['p_company_siret']) ? $_POST['p_company_siret'] : '';

	$p_schedule = !empty($_POST['p_schedule']) && is_array($_POST['p_schedule']) ? $_POST['p_schedule'] : array();

	$p_leader_name = !empty($_POST['p_leader_name']) ? $_POST['p_leader_name'] : '';
	$p_leader_firstname = !empty($_POST['p_leader_firstname']) ? $_POST['p_leader_firstname'] : '';

	$p_address_street = !empty($_POST['p_address_street']) ? $_POST['p_address_street'] : '';
	$p_address_street_2 = !empty($_POST['p_address_street']) ? $_POST['p_address_street_2'] : '';
	$p_address_code = !empty($_POST['p_address_code']) ? $_POST['p_address_code'] : '';
	$p_address_city = !empty($_POST['p_address_city']) ? $_POST['p_address_city'] : '';
	$p_address_country = !empty($_POST['p_address_country']) ? $_POST['p_address_country'] : '';
	$p_address_tel = !empty($_POST['p_address_tel']) ? $_POST['p_address_tel'] : '';
	$p_address_mobile = !empty($_POST['p_address_mobile']) ? $_POST['p_address_mobile'] : '';
	$p_address_fax = !empty($_POST['p_address_fax']) ? $_POST['p_address_fax'] : '';

	$p_gps_lat = !empty($_POST['p_gps_lat']) ? $_POST['p_gps_lat'] : '';
	$p_gps_long = !empty($_POST['p_gps_long']) ? $_POST['p_gps_long'] : '';

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'company' 			 => array(
			'name' 				=> $p_company_name,
			'com_name' 			=> $p_company_com_name,
			'siret' 			=> $p_company_siret
		),
		'schedule' 			 => $p_schedule,
		'leader' 			 => array(
			'name' 				=> $p_leader_name,
			'firstname' 		=> $p_leader_firstname
		),
		'address' 			 => array(
			'street' 			=> $p_address_street,
			'street_2' 			=> $p_address_street_2,
			'code' 				=> $p_address_code,
			'city' 				=> $p_address_city,
			'country'			=> $p_address_country,
			'tel'				=> $p_address_tel,
			'mobile'			=> $p_address_mobile,
			'fax'				=> $p_address_fax
		),
		'gps' 			 => array(
			'lat' 			=> $p_gps_lat,
			'long' 			=> $p_gps_long,
		)
	));
}
