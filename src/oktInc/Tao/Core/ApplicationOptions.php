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

	public function __construct(array $aOptions = array())
	{
		$this->setDefaultOptions();

		if (!empty($aOptions)) {
			$this->setOptions($aOptions);
		}
	}

	public function setDefaultOptions()
	{
		$this->aOptions = array(
			'inc_dir' => OKT_ROOT_PATH.'/oktInc',
			'cache_dir' => OKT_ROOT_PATH.'/oktInc/cache',
			'config_dir' => OKT_ROOT_PATH.'/oktInc/config',
			'module_dir' => OKT_ROOT_PATH.'/oktModules',
			'public_dir' => OKT_ROOT_PATH.'/oktPublic',
			'upload_dir' => OKT_ROOT_PATH.'/oktPublic/upload',
			'themes_dir' => OKT_ROOT_PATH.'/oktThemes',
		);
	}

	public function setOptions(array $aOptions = array())
	{
		$this->$aOptions = $aOptions + $this->$aOptions;
	}

	public function getOptions()
	{
		return $this->$aOptions;
	}

	public function setOption($sKey, $mValue)
	{
		$this->$aOptions[$sKey] = $mValue;
	}

	public function getOption($sKey)
	{
		if (isset($this->$aOptions[$sKey])) {
			return $this->$aOptions[$sKey];
		}
	}

	public function __get($sKey)
	{
		return $this->getOption($sKey);
	}

	public function __set($sKey, $mValue)
	{
		return $this->setOptions($sKey, $mValue);
	}

	public function __isset($sKey)
	{
		return isset($this->$aOptions[$sKey]);
	}

	public function __unset($sKey)
	{
		if (isset($this->$aOptions[$sKey])) {
			unset($this->$aOptions[$sKey]);
		}
	}
}
