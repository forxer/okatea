<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao;

class ApplicationOptions
{
	protected $aOptions = [];

	public function __construct(array $aOptions = [])
	{
		$this->setOptions($aOptions);
	}

	public function setOptions(array $aOptions = [])
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
		if (isset($this->aOptions[$sKey]))
		{
			return $this->aOptions[$sKey];
		}
	}

	public function has($sKey)
	{
		return isset($this->aOptions[$sKey]);
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
		if (isset($this->aOptions[$sKey]))
		{
			unset($this->aOptions[$sKey]);
		}
	}
}
