<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Extensions;

use Okatea\Tao\Database\Recordset;

class Collection
{
	/**
	 * Okatea application instance.
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The database manager instance.
	 * @var object
	 */
	protected $db;

	/**
	 * The errors manager instance.
	 * @var object
	 */
	protected $error;

	/**
	 * The name of the extensions table.
	 * @var string
	 */
	protected $t_extensions;

	/**
	 * The directory path extensions.
	 * @var string
	 */
	protected $path;

	/**
	 * Cache manager object.
	 * @var object
	 */
	protected $cache;

	/**
	 * Extensions cache identifier.
	 * @var string
	 */
	protected $sCacheId;

	/**
	 * Extensions class name pattern.
	 * @var string
	 */
	protected $sExtensionClassPatern;

	/**
	 * List of loaded extensions.
	 * @var array
	 */
	protected $aLoaded;

	protected $manager;

	/**
	 * Constructor.
	 *
	 * @param object $okt Okatea application instance.
	 * @param string $sPath The extensions directory path to load.
	 * @return void
	 */
	public function __construct($okt, $sPath)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		$this->cache = $okt->cacheConfig;

		$this->t_extensions = $okt->db->prefix.'core_extensions';

		$this->path = $sPath;
	}

	/**
	 * Return an instance of Okatea\Tao\Extensions\Manage
	 *
	 * @return \Okatea\Tao\Extensions\Manage
	 */
	public function manage()
	{
		if (null === $this->manager) {
			return ($this->manager = new Manage($this->okt, $this->path));
		}

		return $this->manager;
	}

	/**
	 * Load available extensions.
	 *
	 * @param string $ns The namespace to consider (null)
	 * @return void
	 */
	public function load($ns = null)
	{
		if (!$this->cache->contains($this->sCacheId)) {
			$this->generateCacheList();
		}

		$aList = $this->cache->fetch($this->sCacheId);

		# first pass to instanciate extensions
		foreach ($aList as $sExtensionId => $aExtensionInfos)
		{
			$sExtensionClass = sprintf($this->sExtensionClassPatern, $sExtensionId);

			$this->aLoaded[$sExtensionId] = new $sExtensionClass($this->okt, $this->path);

			$this->aLoaded[$sExtensionId]->setInfos($aExtensionInfos);
		}

		# second pass to initialize extensions so they can interact each others
		foreach ($aList as $sExtensionId => $aExtensionInfos)
		{
			$this->aLoaded[$sExtensionId]->init();
			$this->aLoaded[$sExtensionId]->initNs($ns);
		}
	}

	/**
	 * Returns the list of loaded extensions.
	 *
	 * @return array
	 */
	public function getLoaded()
	{
		return $this->aLoaded;
	}

	/**
	 * Resets the list of loaded extensions.
	 *
	 * @return void
	 */
	public function resetsLoaded()
	{
		$this->aLoaded = array();
	}

	/**
	 * Indicates whether a given extension is in the list of loaded extensions.
	 *
	 * @param string $sModuleId
	 * @return boolean
	 */
	public function isLoaded($sExtensionId)
	{
		return isset($this->aLoaded[$sExtensionId]);
	}

	/**
	 * Returns the instance of a given extension.
	 *
	 * @param string $sExtensionId
	 * @throws Exception
	 * @return object Okatea\Tao\Extensions\Extension
	 */
	public function getInstance($sExtensionId)
	{
		if (!isset($this->aLoaded[$sExtensionId])) {
			throw new \Exception(__('The extension specified ('.$sExtensionId.') does not appear to be a valid installed extension.'));
		}

		return $this->aLoaded[$sExtensionId];
	}

	/**
	 * Caches the list of extensions to load.
	 *
	 * @return boolean
	 */
	public function generateCacheList()
	{
		$aLoaded = array();

		$rsExtensions = $this->getFromDatabase(array(
			'status' => 1
		));

		while ($rsExtensions->fetch())
		{
			$aLoaded[$rsExtensions->f('id')] = array(
				'id' 		=> $rsExtensions->f('id'),
				'root'		=> $this->path.'/'.$rsExtensions->f('id'),
				'name'		=> $rsExtensions->f('name'),
				'version'	=> $rsExtensions->f('version'),
				'desc'		=> $rsExtensions->f('description'),
				'author'	=> $rsExtensions->f('author'),
				'status'	=> $rsExtensions->f('status')
			);
		}

		return $this->cache->save($this->sCacheId, $aLoaded);
	}

	/**
	 * Returns a list of extensions registered in the database.
	 *
	 * @param array $aParams
	 * @return object Recordset
	 */
	public function getFromDatabase(array $aParams = array())
	{
		$reqPlus = 'WHERE 1 ';

		if (!empty($aParams['id'])) {
			$reqPlus .= 'AND id=\''.$this->db->escapeStr($aParams['id']).'\' ';
		}

		if (!empty($aParams['status'])) {
			$reqPlus .= 'AND status='.(integer)$aParams['status'].' ';
		}

		if (!empty($aParams['type'])) {
			$reqPlus .= 'AND type=\''.$this->db->escapeStr($aParams['type']).'\' ';
		}

		$strReq =
		'SELECT id, name, description, author, version, priority, updatable, status, type '.
		'FROM '.$this->t_extensions.' '.
		$reqPlus.
		'ORDER BY priority ASC, id ASC ';

		if (($rs = $this->db->select($strReq)) === false) {
			return new Recordset(array());
		}

		return $rs;
	}

	/**
	 * Sort an array of extensions alphabetically.
	 *
	 * @param array $aExtensions
	 * @return void
	 */
	public static function sort(array &$aExtensions)
	{
		uasort($aExtensions, function($a, $b){
			return strcasecmp($a['name_l10n'], $b['name_l10n']);
		});
	}

	/**
	 * Return repositories data about a list of given repositories.
	 *
	 * @param array $aRepositories
	 * @return array
	 */
	public function getRepositoriesData(array $aRepositories = array())
	{
		return (new Repositories($this->okt))->getData($aRepositories);
	}
}
