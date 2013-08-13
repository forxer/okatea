<?php
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

	$p_log_admin_ttl_months = !empty($_POST['p_log_admin_ttl_months']) ? $_POST['p_log_admin_ttl_months'] : 3;

	$p_news_feed_enabled = !empty($_POST['p_news_feed_enabled']) ? true : false;
	$p_news_feed_url = !empty($_POST['p_news_feed_url']) ? $_POST['p_news_feed_url'] : '';

	$p_slug_type = !empty($_POST['p_slug_type']) ? $_POST['p_slug_type'] : 'ascii';

	$aPageData['aNewConf'] = array_merge($aPageData['aNewConf'], array(
		'public_maintenance_mode' => $p_public_maintenance_mode,
		'admin_maintenance_mode' => $p_admin_maintenance_mode,

		'htmlpurifier_disabled' => (boolean)$p_htmlpurifier_disabled,

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
