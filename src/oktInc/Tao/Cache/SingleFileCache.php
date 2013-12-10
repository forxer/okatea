<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Cache;

use Doctrine\Common\Cache\CacheProvider;

/**
 * Single file cache driver.
 */
class SingleFileCache extends CacheProvider
{
	/**
	 * @var string $file
	 */
	protected $file;

	/**
	 * @var array $data
	 */
	protected $data = null;

	/**
	 * Construct the file cache
	 *
	 * @param string $file - the location where the cache file will be stored
	 * @param array $aData
	 */
	public function __construct($file, $aData=array())
	{
		$this->file = $file;

		if (!file_exists($this->file))
		{
			$this->data = $aData;
			$this->writeData();
		}
		else {
			$this->data = $this->loadData(true);
		}
	}

	/**
	 * Load file data
	 *
	 * @param boolean $bForce
	 * @return array
	 */
	protected function loadData($bForce=false)
	{
		if (is_array($this->data) && !$bForce) {
			return $this->data;
		}

		if (($json = file_get_contents($this->file)) === false) {
			return null;
		}

		return json_decode($json,true);
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
	 * {@inheritdoc}
	 */
	public function getIds()
	{
		return array_keys($this->data);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFetch($id)
	{
		if (isset($this->data[$id])) {
			return $this->data[$id];
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doContains($id)
	{
		return isset($this->data[$id]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doSave($id, $data, $lifeTime = 0)
	{
		$this->data[$id] = $data;

		$this->writeData();

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doDelete($id)
	{
		unset($this->data[$id]);

		$this->writeData();

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doFlush()
	{
		$this->data = array();

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function doGetStats()
	{
		return null;
	}

}
