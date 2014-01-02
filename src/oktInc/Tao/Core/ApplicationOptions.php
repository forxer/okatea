<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Core;

class ApplicationOptions
{
	protected $aOptions;

	protected $sRootPath;

	public function __construct($sRootPath, array $aOptions = array())
	{
		$this->sRootPath = $sRootPath;

		$this->setDefaultOptions();

		if (!empty($aOptions)) {
			$this->setOptions($aOptions);
		}
	}

	public function getRootPath()
	{
		return $this->sRootPath;
	}

	protected function setDefaultOptions()
	{
		$this->aOptions = array(

			'inc_dir' 			=> $this->sRootPath.'/oktInc',
			'cache_dir' 		=> $this->sRootPath.'/oktInc/cache',
			'config_dir' 		=> $this->sRootPath.'/oktInc/config',
			'locales_dir' 		=> $this->sRootPath.'/oktInc/locales',
			'modules_dir' 		=> $this->sRootPath.'/oktModules',
			'public_dir' 		=> $this->sRootPath.'/oktPublic',
			'upload_dir' 		=> $this->sRootPath.'/oktPublic/upload',
			'themes_dir' 		=> $this->sRootPath.'/oktThemes',

			'cookie_auth_name' 	=> 'otk_auth',
			'cookie_auth_from' 	=> 'otk_auth_from',
			'cookie_language' 	=> 'otk_language',

			'digests' 			=> $this->sRootPath.'/oktInc/digests',
			'csrf_token_name' 	=> 'okt_csrf_token'
		);
	}

	public function setOptions(array $aOptions = array())
	{
		$this->aOptions = $aOptions + $this->aOptions;
	}

	public function all()
	{
		return $this->aOptions;
	}

	public function set($sKey, $mValue)
	{
		$this->aOptions[$sKey] = $mValue;
	}

	public function get($sKey)
	{
		if (isset($this->aOptions[$sKey])) {
			return $this->aOptions[$sKey];
		}
	}

	public function __get($sKey)
	{
		return $this->get($sKey);
	}

	public function __set($sKey, $mValue)
	{
		return $this->set($sKey, $mValue);
	}

	public function __isset($sKey)
	{
		return isset($this->aOptions[$sKey]);
	}

	public function __unset($sKey)
	{
		if (isset($this->aOptions[$sKey])) {
			unset($this->aOptions[$sKey]);
		}
	}
}
