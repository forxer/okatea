<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Extensions;

use Okatea\Tao\Database\Recordset;
use Okatea\Tao\HttpClient;
use Symfony\Component\Finder\Finder;

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
	 * Repository cache identifier.
	 * @var string
	 */
	protected $sCacheRepositoryId;

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

	/**
	 * List of all extensions in the file system.
	 * @var array
	 */
	protected $aAll;

	/**
	 * Temporary extension identifier.
	 * @var string
	 */
	protected $sTempId;

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
	public function extensionExists($sExtensionId)
	{
		return isset($this->aLoaded[$sExtensionId]);
	}

	/**
	 * Retourne l'instance d'une extension donnée.
	 *
	 * @param string $sExtensionId
	 * @throws Exception
	 */
	public function getInstance($sExtensionId)
	{
		if (!$this->extensionExists($sExtensionId)) {
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

		$rsExtensions = $this->getFromDB(array('status' => 1));

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
	 * Returns a list of extensions from the file system.
	 *
	 * @return array
	 */
	public function getFromFileSystem()
	{
		$finder = (new Finder())
			->files()
			->in($this->path)
			->depth('== 1')
			->name('_define.php');

		foreach ($finder as $file)
		{
			$this->sTempId = $file->getRelativePath();

			require $file->getRealpath();

			$this->id = null;
			$this->mroot = null;
		}

		return $this->aAll;
	}

	/**
	 * Returns a list of all the extensions in the file system.
	 *
	 * @param string $sModuleId
	 */
	public function getAll($sModuleId = null)
	{
		if (null === $this->aAll) {
			$this->getFromFileSystem();
		}

		if ($sModuleId && isset($this->aAll[$sModuleId])) {
			return $this->aAll[$sModuleId];
		}

		return $this->aAll;
	}

	/**
	 * Ré-initialise la liste complète des modules.
	 *
	 * @return void
	 */
	public function resetAll()
	{
		$this->aAll = array();
	}

	/**
	 * Cette fonction est utilisée dans les fichiers _define.php
	 * des extensions pour qu'elles soient prises en compte par le système.
	 *
	 * Cette méthode reçoit en argument un tableau de paramètres,
	 * les paramètres possibles sont les suivants :
	 * 	- name 		Le nom de l'extension
	 * 	- desc 		La description de l'extension
	 * 	- version 	Le numero de version de l'extension
	 * 	- author 	L'auteur de l'extension ('')
	 * 	- priority 	Priorité de l'extension (1000)
	 * 	- updatable	Blocage de mise à jour (true)
	 *
	 * @param array $aParams Le tableau de paramètres
	 * @return void
	 */
	public function register(array $aParams = array())
	{
		if (null !== $this->sTempId)
		{
			$this->aAll[$this->sTempId] = array(
				'id' 			=> $this->sTempId,
				'root'			=> $this->path.'/'.$this->sTempId,
				'name' 			=> (!empty($aParams['name']) 		? $aParams['name'] 					: $this->id),
				'desc' 			=> (!empty($aParams['desc']) 		? $aParams['desc'] 					: null),
				'version' 		=> (!empty($aParams['version']) 	? $aParams['version'] 				: null),
				'author' 		=> (!empty($aParams['author']) 		? $aParams['author'] 				: null),
				'priority' 		=> (!empty($aParams['priority']) 	? (integer)$aParams['priority'] 	: 1000),
				'updatable' 	=> (!empty($aParams['updatable']) 	? (boolean)$aParams['updatable'] 	: true)
			);
		}
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
	 * Returns the list of installed extensions.
	 *
	 * @return array
	 */
	public function getInstalled()
	{
		$rsInstalled = $this->getFromDatabase();

		$aInstalled = array();

		while ($rsInstalled->fetch())
		{
			$aInstalled[$rsInstalled->id] = array(
				'id' 			=> $rsInstalled->id,
				'root' 			=> $this->path.'/'.$rsInstalled->id,
				'name' 			=> $rsInstalled->name,
				'name_l10n' 	=> __($rsInstalled->name),
				'desc' 			=> $rsInstalled->description,
				'desc_l10n' 	=> __($rsInstalled->description),
				'author' 		=> $rsInstalled->author,
				'version' 		=> $rsInstalled->version,
				'priority' 		=> $rsInstalled->priority,
				'status' 		=> $rsInstalled->status,
				'updatable' 	=> $rsInstalled->updatable
			);
		}

		return $aInstalled;
	}




	/**
	 * Retourne les informations concernant les dépôts des extensions.
	 *
	 * @param array $aRepositories
	 * @return array
	 */
	public function getRepositoriesInfos(array $aRepositories = array())
	{
		if (!$this->cache->contains($this->sCacheRepositoryId)) {
			$this->saveRepositoriesInfosCache($aRepositories);
		}

		return $this->cache->fetch($this->sCacheRepositoryId);
	}

	/**
	 * Enregistre les infos des dépôts dans le cache.
	 *
	 * @param array $aRepositories
	 * @return boolean
	 */
	protected function saveRepositoriesInfosCache(array $aRepositories = array())
	{
		return $this->cache->save($this->sCacheRepositoryId, $this->readRepositoriesInfos($aRepositories));
	}

	/**
	 * Lit les informations concernant les dépôts de modules et les retournes.
	 *
	 * @param array $aRepositories
	 * @return array
	 */
	protected function readRepositoriesInfos($aRepositories)
	{
		$aModulesRepositories = array();

		foreach ($aRepositories as $sRepositoryId => $sRepositoryUrl)
		{
			if (($infos = $this->getRepositoryInfos($sRepositoryUrl)) !== false) {
				$aModulesRepositories[$sRepositoryId] = $infos;
			}
		}

		return $aModulesRepositories;
	}

	/**
	 * Retourne les informations d'un dépôt de modules donné.
	 *
	 * @param array $sRepositoryUrl
	 * @return array
	 */
	protected function getRepositoryInfos($sRepositoryUrl)
	{
		$sRepositoryUrl = str_replace('%VERSION%', $this->okt->getVersion(), $sRepositoryUrl);

		if (filter_var($sRepositoryUrl, FILTER_VALIDATE_URL) === false) {
			return false;
		}

		$client = new HttpClient();
		$response = $client->get($sRepositoryUrl)->send();

		if ($response->isSuccessful()) {
			return $this->readRepositoryInfos($response->getBody(true));
		}
		else {
			return false;
		}
	}

	/**
	 * Lit les informations XML d'un dépôt de modules donné et les retournes.
	 *
	 * @param sting $str
	 * @return array
	 */
	protected function readRepositoryInfos($str)
	{
		try
		{
			$xml = new \SimpleXMLElement($str, LIBXML_NOERROR);

			$return = array();
			foreach ($xml->module as $module)
			{
				if (isset($module['id']))
				{
					$return[(string)$module['id']] = array(
						'id' 		=> (string)$module['id'],
						'name' 		=> (string)$module['name'],
						'version' 	=> (string)$module['version'],
						'href' 		=> (string)$module['href'],
						'checksum' 	=> (string)$module['checksum'],
						'info' 		=> (string)$module['info']
					);
				}
			}

			if (empty($return)) {
				return false;
			}

			return $return;
		}
		catch (Exception $e) {
			throw $e;
		}
	}

}
