<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao;

use Okatea\Tao\Html\Checklister;

class Requirements
{
	/**
	 * Okatea application instance.
	 *
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The prerequisites list.
	 *
	 * @var array
	 */
	protected $aRequirements = array();

	public function __construct($okt, $sLanguage = null)
	{
		$this->okt = $okt;

		$this->okt->l10n->loadFile($this->okt->options->locales_dir . '/%s/pre-requisites', $sLanguage);

		/* Groups
		----------------------------------------------------------*/

		$this->aRequirements[0] = array(
			'group_id' => 'php',
			'group_title' => __('pr_php'),
			'requirements' => array()
		);

		$this->aRequirements[1] = array(
			'group_id' => 'files',
			'group_title' => __('pr_dirs_and_files'),
			'requirements' => array()
		);

		/* PHP requirements
		----------------------------------------------------------*/

		$sPhpVersionRequired = require $this->okt->options->okt_dir . '/php_version_required.php';

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'php_version',
			'test' => version_compare(PHP_VERSION, $sPhpVersionRequired, '>='),
			'msg_ok' => sprintf(__('pr_php_version_ok'), PHP_VERSION),
			'msg_ko' => sprintf(__('pr_php_version_ko'), PHP_VERSION, $sPhpVersionRequired)
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'mysqli',
			'test' => function_exists('mysqli_connect'),
			'msg_ok' => __('pr_mysqli_ok'),
			'msg_ko' => __('pr_mysqli_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'curl',
			'test' => function_exists('curl_init'),
			'msg_ok' => __('pr_curl_ok'),
			'msg_ko' => __('pr_curl_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'json_encode',
			'test' => function_exists('json_encode'),
			'msg_ok' => __('pr_json_encode_ok'),
			'msg_ko' => __('pr_json_encode_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'json_decode',
			'test' => function_exists('json_decode'),
			'msg_ok' => __('pr_json_decode_ok'),
			'msg_ko' => __('pr_json_decode_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'intl',
			'test' => extension_loaded('intl'),
			'msg_ok' => __('pr_intl_ok'),
			'msg_ko' => __('pr_intl_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'pcre',
			'test' => $this->oktPcreSupportTest(),
			'msg_ok' => __('pr_pcre_ok'),
			'msg_ko' => __('pr_pcre_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'crypt',
			'test' => $this->oktCryptSupportTest(),
			'msg_ok' => __('pr_crypt_ok'),
			'msg_ko' => __('pr_crypt_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'fileinfo',
			'test' => extension_loaded('fileinfo') ? true : null,
			'msg_ok' => __('pr_fileinfo_ok'),
			'msg_ko' => __('pr_fileinfo_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'xml',
			'test' => extension_loaded('xml') ? true : null,
			'msg_ok' => __('pr_xml_ok'),
			'msg_ko' => __('pr_xml_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'simplexml',
			'test' => extension_loaded('simplexml') ? true : null,
			'msg_ok' => __('pr_simplexml_ok'),
			'msg_ko' => __('pr_simplexml_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'mb_string',
			'test' => extension_loaded('mbstring') ? true : null,
			'msg_ok' => __('pr_mb_string_ok'),
			'msg_ko' => __('pr_mb_string_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'iconv',
			'test' => extension_loaded('iconv') ? true : null,
			'msg_ok' => __('pr_iconv_ok'),
			'msg_ko' => __('pr_iconv_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'GD 2',
			'test' => extension_loaded('gd') ? true : null,
			'msg_ok' => __('pr_gd2_ok'),
			'msg_ko' => __('pr_gd2_ko')
		);

		$this->aRequirements[0]['requirements'][] = array(
			'id' => 'zip',
			'test' => extension_loaded('zip') ? true : null,
			'msg_ok' => __('pr_zip_ok'),
			'msg_ko' => __('pr_zip_ko')
		);

		/* Filesystem requirements
		----------------------------------------------------------*/

		$this->aRequirements[1]['requirements'][] = array(
			'id' => 'oktConf',
			'test' => is_writable($this->okt->options->get('config_dir')),
			'msg_ok' => sprintf(__('pr_okatea_conf_ok'), $this->okt->options->get('config_dir')),
			'msg_ko' => sprintf(__('pr_okatea_conf_ko'), $this->okt->options->get('config_dir'))
		);

		$this->aRequirements[1]['requirements'][] = array(
			'id' => 'conf_site',
			'test' => is_writable($this->okt->options->get('config_dir') . '/conf_site.yml'),
			'msg_ok' => sprintf(__('pr_conf_site_ok'), $this->okt->options->get('config_dir') . '/conf_site.yml'),
			'msg_ko' => sprintf(__('pr_conf_site_ko'), $this->okt->options->get('config_dir') . '/conf_site.yml')
		);

		$this->aRequirements[1]['requirements'][] = array(
			'id' => 'oktCache',
			'test' => is_writable($this->okt->options->get('cache_dir')) ? true : null,
			'msg_ok' => sprintf(__('pr_okatea_cache_ok'), $this->okt->options->get('cache_dir')),
			'msg_ko' => sprintf(__('pr_okatea_cache_ko'), $this->okt->options->get('cache_dir'))
		);

		$this->aRequirements[1]['requirements'][] = array(
			'id' => 'oktLog',
			'test' => is_writable($this->okt->options->get('logs_dir')) ? true : null,
			'msg_ok' => sprintf(__('pr_okatea_log_ok'), $this->okt->options->get('logs_dir')),
			'msg_ko' => sprintf(__('pr_okatea_log_ko'), $this->okt->options->get('logs_dir'))
		);

		$this->aRequirements[1]['requirements'][] = array(
			'id' => 'oktModules',
			'test' => is_writable($this->okt->options->get('modules_dir')) ? true : null,
			'msg_ok' => sprintf(__('pr_okatea_modules_ok'), $this->okt->options->get('modules_dir')),
			'msg_ko' => sprintf(__('pr_okatea_modules_ko'), $this->okt->options->get('modules_dir'))
		);

		$this->aRequirements[1]['requirements'][] = array(
			'id' => 'oktPublic',
			'test' => is_writable($this->okt->options->get('public_dir')) ? true : null,
			'msg_ok' => sprintf(__('pr_okatea_public_ok'), $this->okt->options->get('public_dir')),
			'msg_ko' => sprintf(__('pr_okatea_public_ko'), $this->okt->options->get('public_dir'))
		);

		$this->aRequirements[1]['requirements'][] = array(
			'id' => 'oktThemes',
			'test' => is_writable($this->okt->options->get('themes_dir')) ? true : null,
			'msg_ok' => sprintf(__('pr_okatea_themes_ok'), $this->okt->options->get('themes_dir')),
			'msg_ko' => sprintf(__('pr_okatea_themes_ko'), $this->okt->options->get('themes_dir'))
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
			$aResults['bCheckAll'] = $aResults['bCheckAll'] && $this->aRequirements[$i]['check_' . $group['group_id']]->checkAll();
			$aResults['bCheckWarning'] = $aResults['bCheckWarning'] && ! $this->aRequirements[$i]['check_' . $group['group_id']]->checkWarnings();
		}

		return $aResults;
	}

	protected function putInHtmlCheckList()
	{
		foreach ($this->aRequirements as $i => $group)
		{
			$this->aRequirements[$i]['check_' . $group['group_id']] = new Checklister();

			foreach ($group['requirements'] as $requirement)
			{
				$this->aRequirements[$i]['check_' . $group['group_id']]->addItem($requirement['id'], $requirement['test'], $requirement['msg_ok'], $requirement['msg_ko']);
			}
		}
	}

	protected function oktPcreSupportTest()
	{
		$pcre_str = base64_decode('w6nDqMOgw6o=');
		return @preg_match('/' . $pcre_str . '/u', $pcre_str);
	}

	protected function oktCryptSupportTest()
	{
		$hash = '$2y$04$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
		$test = crypt("password", $hash);
		return $test == $hash;
	}
}
