<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Core;

use Tao\Html\CheckList;

class Requirements
{
	/**
	 * L'objet core.
	 * @var object oktCore
	 */
	protected $okt;

	/**
	 * L'objet core.
	 * @var object Tao\Core\Application
	 */
	protected $aRequirements = array();

	public function __construct($okt, $sLanguage = null)
	{
		$this->okt = $okt;

		if (null === $sLanguage)
		{
			if (isset($this->okt->user)) {
				$sLanguage = $this->okt->user->language;
			}
			else {
				$sLanguage = 'en';
			}
		}

		# vérification des pré-requis
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$sLanguage.'/pre-requisites');


		/* Groupes de pré-requis
		----------------------------------------------------------*/

		$this->aRequirements[0] = array(
			'group_id' 		=> 'php',
			'group_title' 	=> __('pr_php'),
			'requirements'	=> array()
		);

		$this->aRequirements[1] = array(
			'group_id' 		=> 'files',
			'group_title' 	=> __('pr_dirs_and_files'),
			'requirements'	=> array()
		);


		/* Détails des pré-requis "PHP"
		----------------------------------------------------------*/

		# Vérification de la version PHP
		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'php_version',
			'test' 		=> version_compare(PHP_VERSION,'5.4.0','>='),
			'msg_ok'	=> sprintf(__('pr_php_version_ok'),PHP_VERSION),
			'msg_ko'	=> sprintf(__('pr_php_version_ko'),PHP_VERSION)
		);

		# Vérification de la présence du module SPL
		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'SPL',
			'test' 		=> function_exists('spl_classes'),
			'msg_ok'	=> __('pr_spl_ok'),
			'msg_ko'	=> __('pr_spl_ko')
		);

		# Vérification de la présence des fonctions MySQLi
		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'mysqli',
			'test' 		=> function_exists('mysqli_connect'),
			'msg_ok'	=> __('pr_mysqli_ok'),
			'msg_ko'	=> __('pr_mysqli_ko')
		);

		# Vérification de la présence des fonctions MySQL
		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'curl',
			'test' 		=> function_exists('curl_init'),
			'msg_ok'	=> __('pr_curl_ok'),
			'msg_ko'	=> __('pr_curl_ko')
		);

		# Vérification de la présence du module XML
		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'xml',
			'test' 		=> function_exists('xml_parser_create'),
			'msg_ok'	=> __('pr_xml_ok'),
			'msg_ko'	=> __('pr_xml_ko')
		);

		# Vérification de la présence du module mb_string
		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'mb_string',
			'test' 		=> function_exists('mb_detect_encoding'),
			'msg_ok'	=> __('pr_mb_string_ok'),
			'msg_ko'	=> __('pr_mb_string_ko')
		);

		# Vérification de la présence des fonctions json_*
		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'json_encode',
			'test' 		=> function_exists('json_encode'),
			'msg_ok'	=> __('pr_json_encode_ok'),
			'msg_ko'	=> __('pr_json_encode_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'json_decode',
			'test' 		=> function_exists('json_decode'),
			'msg_ok'	=> __('pr_json_decode_ok'),
			'msg_ko'	=> __('pr_json_decode_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'pcre',
			'test' 		=> $this->oktPcreSupportTest(),
			'msg_ok'	=> __('pr_pcre_ok'),
			'msg_ko'	=> __('pr_pcre_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'crypt',
			'test' 		=> $this->oktCryptSupportTest(),
			'msg_ok'	=> __('pr_crypt_ok'),
			'msg_ko'	=> __('pr_crypt_ko')
		);

		# Vérification de la présence du module simplexml
		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'simplexml',
			'test' 		=> function_exists('simplexml_load_string') ? true : null,
			'msg_ok'	=> __('pr_simplexml_ok'),
			'msg_ko'	=> __('pr_simplexml_ko')
		);

		# Vérification de la présence du module iconv
		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'iconv',
			'test' 		=> function_exists('iconv') ? true : null,
			'msg_ok'	=> __('pr_iconv_ok'),
			'msg_ko'	=> __('pr_iconv_ko')
		);

		# Vérification de la présence de GD2
		$this->aRequirements[0]['requirements'][] = array(
			'id' 		=> 'GD 2',
			'test' 		=> function_exists('imagegd2') ? true : null,
			'msg_ok'	=> __('pr_gd2_ok'),
			'msg_ko'	=> __('pr_gd2_ko')
		);


		/* Détails des pré-requis "files"
		----------------------------------------------------------*/

		# Vérification des droits sur /oktConf
		$this->aRequirements[1]['requirements'][] = array(
			'id' 		=> 'oktConf',
			'test' 		=> is_writable($this->okt->options->get('config_dir')),
			'msg_ok' 	=> sprintf(__('pr_oktconf_ok'), $this->okt->options->get('config_dir')),
			'msg_ko'	=> sprintf(__('pr_oktconf_ko'), $this->okt->options->get('config_dir'))
		);

		# Vérification des droits sur /oktConf/conf_site.yaml
		$this->aRequirements[1]['requirements'][] = array(
			'id' 		=> 'conf_site',
			'test' 		=> is_writable($this->okt->options->get('config_dir').'/conf_site.yml'),
			'msg_ok' 	=> sprintf(__('pr_conf_site_ok'), $this->okt->options->get('config_dir').'/conf_site.yml'),
			'msg_ko'	=> sprintf(__('pr_conf_site_ko'), $this->okt->options->get('config_dir').'/conf_site.yml')
		);

		# Vérification des droits sur /oktCache
		$this->aRequirements[1]['requirements'][] = array(
			'id' 		=> 'oktCache',
			'test' 		=> is_writable($this->okt->options->get('cache_dir')) ? true : null,
			'msg_ok' 	=> sprintf(__('pr_oktcache_ok'), $this->okt->options->get('cache_dir')),
			'msg_ko'	=> sprintf(__('pr_oktcache_ko'), $this->okt->options->get('cache_dir'))
		);

		# Vérification des droits sur /oktLog
		$this->aRequirements[1]['requirements'][] = array(
			'id' 		=> 'oktLog',
			'test' 		=> is_writable($this->okt->options->get('logs_dir')) ? true : null,
			'msg_ok' 	=> sprintf(__('pr_oktlog_ok'), $this->okt->options->get('logs_dir')),
			'msg_ko'	=> sprintf(__('pr_oktlog_ko'), $this->okt->options->get('logs_dir'))
		);

		# Vérification des droits sur /oktModules
		$this->aRequirements[1]['requirements'][] = array(
			'id' 		=> 'oktModules',
			'test' 		=> is_writable($this->okt->options->get('modules_dir')) ? true : null,
			'msg_ok' 	=> sprintf(__('pr_oktmodules_ok'), $this->okt->options->get('modules_dir')),
			'msg_ko'	=> sprintf(__('pr_oktmodules_ko'), $this->okt->options->get('modules_dir'))
		);

		# Vérification des droits sur /oktPublic
		$this->aRequirements[1]['requirements'][] = array(
			'id' 		=> 'oktPublic',
			'test' 		=> is_writable($this->okt->options->get('public_dir')) ? true : null,
			'msg_ok' 	=> sprintf(__('pr_oktpublic_ok'), $this->okt->options->get('public_dir')),
			'msg_ko'	=> sprintf(__('pr_oktpublic_ko'), $this->okt->options->get('public_dir'))
		);

		# Vérification des droits sur /oktThemes
		$this->aRequirements[1]['requirements'][] = array(
			'id' 		=> 'oktThemes',
			'test' 		=> is_writable($this->okt->options->get('themes_dir')) ? true : null,
			'msg_ok' 	=> sprintf(__('pr_oktthemes_ok'), $this->okt->options->get('themes_dir')),
			'msg_ko'	=> sprintf(__('pr_oktthemes_ko'), $this->okt->options->get('themes_dir'))
		);
	}

	public function getRequirements()
	{
		return $this->aRequirements;
	}

	public function getResultsFromHtmlCheckList()
	{
		$this->putInHtmlCheckList();

		$aResults = array(
			'bCheckAll' => true,
			'bCheckWarning' => true
		);

		foreach ($this->aRequirements as $i => $group)
		{
			$aResults['bCheckAll'] = $aResults['bCheckAll'] && $this->aRequirements[$i]['check_'.$group['group_id']]->checkAll();
			$aResults['bCheckWarning'] = $aResults['bCheckWarning'] && !$this->aRequirements[$i]['check_'.$group['group_id']]->checkWarnings();
		}

		return $aResults;
	}

	protected function putInHtmlCheckList()
	{
		foreach ($this->aRequirements as $i => $group)
		{
			$this->aRequirements[$i]['check_'.$group['group_id']] = new CheckList();

			foreach ($group['requirements'] as $requirement)
			{
				$this->aRequirements[$i]['check_'.$group['group_id']]->addItem(
					$requirement['id'],
					$requirement['test'],
					$requirement['msg_ok'],
					$requirement['msg_ko']
				);
			}
		}
	}

	# Vérification de la prise en charge d'UTF-8 par le moteur PCRE
	protected function oktPcreSupportTest()
	{
		$pcre_str = base64_decode('w6nDqMOgw6o=');
		return @preg_match('/'.$pcre_str.'/u', $pcre_str);
	}

	# Vérification du support pour la fonction crypt
	protected function oktCryptSupportTest()
	{
		$hash = '$2y$04$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
		$test = crypt("password", $hash);
		return $test == $hash;
	}
}