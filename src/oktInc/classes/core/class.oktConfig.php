<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class oktConfig
 * @ingroup okt_classes_core
 * @brief Gestion des fichiers de configuration.
 *
 */

class oktConfig
{
	/**
	 * Le chemin du fichier source
	 * @var string
	 */
	protected $sourceFile;

	/**
	 * Le TTL du fichier du cache
	 * @var string
	 */
	protected $cache = null;

	/**
	 * Les données
	 * @var array
	 */
	protected $data;

	/**
	 * L'identifiant du cache
	 * @var string
	 */
	protected $cache_id;

	/**
	 * Constructeur. Charge les données.
	 *
	 * @param string $sourceFile
	 * @param string $cacheFile
	 */
	public function __construct($cache, $sourceFile)
	{
		$this->cache = $cache;

		$this->sourceFile = $sourceFile.'.yaml';
		$this->cache_id = basename($sourceFile);

		$this->loadData();
	}

	public function __get($name)
	{
		return $this->getData($name);
	}

	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}

	public function __isset($name)
	{
		return isset($this->data[$name]);
	}

	public function __unset($name)
	{
		unset($this->data[$name]);
	}

	public function get($name=null)
	{
		if ($name === null) {
			return $this->data;
		}

		return $this->getData($name);
	}

	public function getData($name)
	{
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}

		trigger_error('There is no config data for '.$name.' key.', E_USER_NOTICE);

		return null;
	}

	private function loadData()
	{
		if (!$this->cache->contains($this->cache_id)) {
			$this->generateCacheFile();
		}

		$this->data = $this->cache->fetch($this->cache_id);
	}

	private function loadSource()
	{
		try {
			return (array)sfYaml::load($this->sourceFile);
		}
		catch (InvalidArgumentException $e)
		{
			$trace = debug_backtrace();
			trigger_error(
				'Problème lecture configuration : ' . $e->getMessage() .
				' dans ' . $trace[0]['file'] . ' à la ligne ' . $trace[0]['line'],
				E_USER_WARNING);

			return array();
		}
	}

	private function generateCacheFile()
	{
		return $this->cache->save($this->cache_id, $this->loadSource());
	}

	public function write($data)
	{
		$data = array_merge($this->loadSource(), $data);

		file_put_contents($this->sourceFile, sfYaml::dump($data));

		$this->generateCacheFile();
	}

	public function writeCurrent()
	{
		file_put_contents($this->sourceFile, sfYaml::dump($this->data));

		$this->generateCacheFile();
	}

	public function merge()
	{
		$data = array_merge($this->cache->fetch($this->cache_id), $this->loadSource());

		file_put_contents($this->sourceFile, sfYaml::dump($data));

		$this->generateCacheFile();
	}


} # class
