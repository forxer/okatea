<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Configuration avancée autres (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


if (!empty($_POST['form_sent']))
{
	$p_public_maintenance_mode = !empty($_POST['p_public_maintenance_mode']) ? true : false;
	$p_admin_maintenance_mode = !empty($_POST['p_admin_maintenance_mode']) ? true : false;

	$p_htmlpurifier_disabled = !empty($_POST['p_htmlpurifier_disabled']) ? true : false;

	$p_user_visit_timeout = !empty($_POST['p_user_visit_timeout']) ? $_POST['p_user_visit_timeout'] : 1800;
	$p_user_visit_remember_time = !empty($_POST['p_user_visit_remember_time']) ? $_POST['p_user_visit_remember_time'] : 1209600;

	$p_log_admin_ttl_months = !empty($_POST['p_log_admin_ttl_months']) ? $_POST['p_log_admin_ttl_months'] : 3;

	$p_news_feed_enabled = !empty($_POST['p_news_feed_enabled']) ? true : false;
	$p_news_feed_url = !empty($_POST['p_news_feed_url']) && is_array($_POST['p_news_feed_url']) ? $_POST['p_news_feed_url'] : array();

	$p_slug_type = !empty($_POST['p_slug_type']) ? $_POST['p_slug_type'] : 'ascii';

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'public_maintenance_mode' => $p_public_maintenance_mode,
		'admin_maintenance_mode' => $p_admin_maintenance_mode,

		'htmlpurifier_disabled' => (boolean)$p_htmlpurifier_disabled,

		'user_visit' => array(
			'timeout' => (integer)$p_user_visit_timeout,
			'remember_time' => (integer)$p_user_visit_remember_time
		),

		'log_admin' => array(
			'ttl_months' => (integer)$p_log_admin_ttl_months
		),

		'news_feed' => array(
			'enabled' => (boolean)$p_news_feed_enabled,
			'url' => $p_news_feed_url
		),

		'slug_type' => $p_slug_type
	));
}
