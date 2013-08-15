<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Liste des pré-requis
 *
 * @addtogroup Okatea
 *
 */


$requirements = array();


/* Groupes de pré-requis
----------------------------------------------------------*/

$requirements[0] = array(
	'group_id' 		=> 'php',
	'group_title' 	=> __('pr_php'),
	'requirements'	=> array()
);

$requirements[1] = array(
	'group_id' 		=> 'files',
	'group_title' 	=> __('pr_dirs_and_files'),
	'requirements'	=> array()
);


/* Détails des pré-requis "PHP"
----------------------------------------------------------*/

# Vérification de la version PHP
$requirements[0]['requirements'][] = array(
	'id' 		=> 'php_version',
	'test' 		=> version_compare(PHP_VERSION,'5.2.0','>='),
	'msg_ok'	=> sprintf(__('pr_php_version_ok'),PHP_VERSION),
	'msg_ko'	=> sprintf(__('pr_php_version_ko'),PHP_VERSION)
);

# Vérification de la présence des fonctions MySQL
$requirements[0]['requirements'][] = array(
	'id' 		=> 'mysql',
	'test' 		=> function_exists('mysql_connect'),
	'msg_ok'	=> __('pr_mysql_ok'),
	'msg_ko'	=> __('pr_mysql_ko')
);

# Vérification de la présence des fonctions MySQL
$requirements[0]['requirements'][] = array(
	'id' 		=> 'curl',
	'test' 		=> function_exists('curl_init'),
	'msg_ok'	=> __('pr_curl_ok'),
	'msg_ko'	=> __('pr_curl_ko')
);

# Vérification de la présence du module XML
$requirements[0]['requirements'][] = array(
	'id' 		=> 'xml',
	'test' 		=> function_exists('xml_parser_create'),
	'msg_ok'	=> __('pr_xml_ok'),
	'msg_ko'	=> __('pr_xml_ko')
);


# Vérification de la présence du module simplexml
$requirements[0]['requirements'][] = array(
	'id' 		=> 'simplexml',
	'test' 		=> function_exists('simplexml_load_string') ? TRUE : NULL,
	'msg_ok'	=> __('pr_simplexml_ok'),
	'msg_ko'	=> __('pr_simplexml_ko')
);

# Vérification de la présence du module mb_string
$requirements[0]['requirements'][] = array(
	'id' 		=> 'mb_string',
	'test' 		=> function_exists('mb_detect_encoding'),
	'msg_ok'	=> __('pr_mb_string_ok'),
	'msg_ko'	=> __('pr_mb_string_ko')
);

# Vérification de la présence du module mb_string
$requirements[0]['requirements'][] = array(
	'id' 		=> 'json_encode',
	'test' 		=> function_exists('json_encode'),
	'msg_ok'	=> __('pr_json_encode_ok'),
	'msg_ko'	=> __('pr_json_encode_ko')
);

# Vérification de la présence du module iconv
$requirements[0]['requirements'][] = array(
	'id' 		=> 'iconv',
	'test' 		=> function_exists('iconv') ? TRUE : NULL,
	'msg_ok'	=> __('pr_iconv_ok'),
	'msg_ko'	=> __('pr_iconv_ko')
);

# Vérification de la prise en charge d'UTF-8 par le moteur PCRE
$pcre_str = base64_decode('w6nDqMOgw6o=');
$requirements[0]['requirements'][] = array(
	'id' 		=> 'PCRE',
	'test' 		=> @preg_match('/'.$pcre_str.'/u', $pcre_str),
	'msg_ok'	=> __('pr_pcre_ok'),
	'msg_ko'	=> __('pr_pcre_ko')
);

# Vérification de la présence du module SPL
$requirements[0]['requirements'][] = array(
	'id' 		=> 'SPL',
	'test' 		=> function_exists('spl_classes') ? TRUE : NULL,
	'msg_ok'	=> __('pr_spl_ok'),
	'msg_ko'	=> __('pr_spl_ko')
);

# Vérification de la présence de GD2
$requirements[0]['requirements'][] = array(
	'id' 		=> 'GD 2',
	'test' 		=> function_exists('imagegd2') ? TRUE : NULL,
	'msg_ok'	=> __('pr_gd2_ok'),
	'msg_ko'	=> __('pr_gd2_ko')
);


/* Détails des pré-requis "files"
----------------------------------------------------------*/

# Vérification des droits sur /oktConf
$requirements[1]['requirements'][] = array(
	'id' 		=> 'oktConf',
	'test' 		=> is_writable(OKT_CONFIG_PATH),
	'msg_ok' 	=> sprintf(__('pr_oktconf_ok'),OKT_CONFIG_DIR),
	'msg_ko'	=> sprintf(__('pr_oktconf_ko'),OKT_CONFIG_DIR)
);

# Vérification des droits sur /oktConf/conf_site.yaml
$requirements[1]['requirements'][] = array(
	'id' 		=> 'conf_site',
	'test' 		=> is_writable(OKT_CONFIG_PATH.'/conf_site.yaml'),
	'msg_ok' 	=> sprintf(__('pr_conf_site_ok'),OKT_CONFIG_DIR),
	'msg_ko'	=> sprintf(__('pr_conf_site_ko'),OKT_CONFIG_DIR)
);

# Vérification des droits sur /oktCache
$requirements[1]['requirements'][] = array(
	'id' 		=> 'oktCache',
	'test' 		=> is_writable(OKT_CACHE_PATH) ? TRUE : NULL,
	'msg_ok' 	=> sprintf(__('pr_oktcache_ok'),OKT_CACHE_DIR),
	'msg_ko'	=> sprintf(__('pr_oktcache_ko'),OKT_CACHE_DIR)
);

# Vérification des droits sur /oktLog
$requirements[1]['requirements'][] = array(
	'id' 		=> 'oktLog',
	'test' 		=> is_writable(OKT_LOG_PATH) ? TRUE : NULL,
	'msg_ok' 	=> sprintf(__('pr_oktlog_ok'),OKT_LOG_DIR),
	'msg_ko'	=> sprintf(__('pr_oktlog_ko'),OKT_LOG_DIR)
);

# Vérification des droits sur /oktModules
$requirements[1]['requirements'][] = array(
	'id' 		=> 'oktModules',
	'test' 		=> is_writable(OKT_MODULES_PATH) ? TRUE : NULL,
	'msg_ok' 	=> sprintf(__('pr_oktmodules_ok'),OKT_MODULES_DIR),
	'msg_ko'	=> sprintf(__('pr_oktmodules_ko'),OKT_MODULES_DIR)
);

# Vérification des droits sur /oktPublic
$requirements[1]['requirements'][] = array(
	'id' 		=> 'oktPublic',
	'test' 		=> is_writable(OKT_PUBLIC_PATH) ? TRUE : NULL,
	'msg_ok' 	=> sprintf(__('pr_oktpublic_ok'),OKT_PUBLIC_DIR),
	'msg_ko'	=> sprintf(__('pr_oktpublic_ko'),OKT_PUBLIC_DIR)
);

# Vérification des droits sur /oktThemes
$requirements[1]['requirements'][] = array(
	'id' 		=> 'oktThemes',
	'test' 		=> is_writable(OKT_THEMES_PATH) ? TRUE : NULL,
	'msg_ok' 	=> sprintf(__('pr_oktthemes_ok'),OKT_THEMES_DIR),
	'msg_ko'	=> sprintf(__('pr_oktthemes_ko'),OKT_THEMES_DIR)
);

