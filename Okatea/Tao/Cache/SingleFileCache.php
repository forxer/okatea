<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Cache;

use Doctrine\Common\Cache\CacheProvider;

/**
 * Single file cache driver.
 */
class SingleFileCache extends CacheProvider
{

	/**
	 * Single cache file path.
	 *
	 * @var string $file
	 */
	protected $file;

	/**
	 * Data to cache.
	 *
	 * @var array $data
	 */
	protected $data;

	/**
	 * Construct the file cache
	 *
	 * @param string $file
	 *        	- the location where the cache file will be stored
	 * @param array $aData
	 */
	public function __construct($file, array $aData = [])
	{
		$this->file = $file;

		if (! file_exists($this->file))
		{
			$this->data = $aData;
			$this->writeData();
		}
		else
		{
			$this->data = $this->loadData(true);
		}
	}

	/**
	 * Load file data
	 *
	 * @param boolean $bForce
	 * @return array
	 */
	protected function loadData($bForce = false)
	{
		if (is_array($this->data) && ! $bForce)
		{
			return $this->data;
		}

		if (($json = file_get_contents($this->file)) === false)
		{
			return null;
		}

		return json_decode($json, true);
	}

	/**
	 * Write file data
	 *
	 * @return boolean
	 */
	protected function writeData()
	{
		return file_put_contents($this->file, json_encode($this->data));
	}

	/**
	 * @ERROR!!!
	 */
	public function getIds()
	{
		return array_keys($this->data);
	}

	/**
	 * @ERROR!!!
	 */
	protected function doFetch($id)
	{
		if (isset($this->data[$id]))
		{
			return $this->data[$id];
		}

		return false;
	}

	/**
	 * @ERROR!!!
	 */
	protected function doContains($id)
	{
		return isset($this->data[$id]);
	}

	/**
	 * @ERROR!!!
	 */
	protected function doSave($id, $data, $lifeTime = 0)
	{
		$this->data[$id] = $data;

		$this->writeData();

		return true;
	}

	/**
	 * @ERROR!!!
	 */
	protected function doDelete($id)
	{
		unset($this->data[$id]);

		$this->writeData();

		return true;
	}

	/**
	 * @ERROR!!!
	 */
	protected function doFlush()
	{
		$this->data = [];

		return true;
	}

	/**
	 * @ERROR!!!
	 */
	protected function doGetStats()
	{
		return null;
	}
}
