<?php
/**
 * Configuration du site emails (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

if (!empty($_POST['form_sent']))
{
	$p_courriel_address = !empty($_POST['p_courriel_address']) ? $_POST['p_courriel_address'] : '';
	if ($p_courriel_address != '' && !text::isEmail($p_courriel_address)) {
		$okt->error->set(sprintf(__('c_c_error_invalid_email'),html::escapeHTML($p_courriel_address)));
	}

	$p_courriel_name = !empty($_POST['p_courriel_name']) ? $_POST['p_courriel_name'] : '';
	$p_courriel_transport = !empty($_POST['p_courriel_transport']) ? $_POST['p_courriel_transport'] : 'mail';

	$p_courriel_theme = isset($_POST['p_courriel_theme']) ? $_POST['p_courriel_theme'] : 0;

	$p_courriel_smtp_host = !empty($_POST['p_courriel_smtp_host']) ? $_POST['p_courriel_smtp_host'] : '';
	$p_courriel_smtp_port = !empty($_POST['p_courriel_smtp_port']) ? intval($_POST['p_courriel_smtp_port']) : 25;
	$p_courriel_smtp_username = !empty($_POST['p_courriel_smtp_username']) ? $_POST['p_courriel_smtp_username'] : '';
	$p_courriel_smtp_password = !empty($_POST['p_courriel_smtp_password']) ? $_POST['p_courriel_smtp_password'] : '';

	$p_courriel_sendmail = !empty($_POST['p_courriel_sendmail']) ? $_POST['p_courriel_sendmail'] : '';

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'courriel_address' 	 => $p_courriel_address,
		'courriel_name' 	 => $p_courriel_name,
		'courriel_transport' => $p_courriel_transport,
		'courriel_theme'     => $p_courriel_theme,
		'courriel_smtp' 	 => array(
			'host' 				=> $p_courriel_smtp_host,
			'port' 				=> (integer)$p_courriel_smtp_port,
			'username' 			=> $p_courriel_smtp_username,
			'password' 			=> $p_courriel_smtp_password
		),
		'courriel_sendmail'  => $p_courriel_sendmail
	));
}
