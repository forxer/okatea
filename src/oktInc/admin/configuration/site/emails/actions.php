<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Configuration du site emails (partie traitements)
 *
 * @addtogroup Okatea
 *
 */

# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;

if (!empty($_POST['form_sent']))
{
	$p_email_to = !empty($_POST['p_email_to']) ? $_POST['p_email_to'] : '';
	if (empty($p_email_to)) {
		$okt->error->set(__('c_a_config_please_enter_email_to'));
	}
	elseif (!text::isEmail($p_email_to)) {
		$okt->error->set(sprintf(__('c_c_error_invalid_email'), html::escapeHTML($p_email_to)));
	}

	$p_email_from = !empty($_POST['p_email_from']) ? $_POST['p_email_from'] : '';
	if (empty($p_email_from)) {
		$okt->error->set(__('c_a_config_please_enter_email_from'));
	}
	elseif (!text::isEmail($p_email_from)) {
		$okt->error->set(sprintf(__('c_c_error_invalid_email'), html::escapeHTML($p_email_from)));
	}

	$p_email_name = !empty($_POST['p_email_name']) ? $_POST['p_email_name'] : '';
	$p_email_transport = !empty($_POST['p_email_transport']) ? $_POST['p_email_transport'] : 'mail';

	$p_email_smtp_host = !empty($_POST['p_email_smtp_host']) ? $_POST['p_email_smtp_host'] : '';
	$p_email_smtp_port = !empty($_POST['p_email_smtp_port']) ? intval($_POST['p_email_smtp_port']) : 25;
	$p_email_smtp_username = !empty($_POST['p_email_smtp_username']) ? $_POST['p_email_smtp_username'] : '';
	$p_email_smtp_password = !empty($_POST['p_email_smtp_password']) ? $_POST['p_email_smtp_password'] : '';

	$p_email_sendmail = !empty($_POST['p_email_sendmail']) ? $_POST['p_email_sendmail'] : '';

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'email' => array(
			'to' => $p_email_to,
			'from' => $p_email_from,
			'name' => $p_email_name,
			'transport' => $p_email_transport,
			'smtp' => array(
				'host' => $p_email_smtp_host,
				'port' => (integer)$p_email_smtp_port,
				'username' => $p_email_smtp_username,
				'password' => $p_email_smtp_password
			),
			'sendmail' => $p_email_sendmail
		)
	));
}
