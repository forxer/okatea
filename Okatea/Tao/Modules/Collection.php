<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Modules;

use Okatea\Tao\Database\Recordset;
use Okatea\Tao\HttpClient;

/**
 * Gestion des modules Okatea.
 *
 */
class Collection
{
	/**
	 * Le chemin du répertoir des modules
	 * @var string
	 */
	public $path;

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
	 * Le nom de la table modules
	 * @var string
	 */
	protected $t_extensions;

	/**
	 * La liste des modules installés
	 * @var array
	 */
	protected $list = array();

	/**
	 * La liste complète des modules (y compris non installés)
	 * @var array
	 */
	protected $complete_list = array();

	/**
	 * L'objet gestionnaire de cache
	 * @var object
	 */
	protected $cache;

	/**
	 * L'identifiant du cache des modules
	 * @var string
	 */
	protected $cache_id;

	/**
	 * L'identifiant du cache des dépots
	 * @var string
	 */
	protected $cache_repo_id;

	/**
	 * L'identifiant du module éventuellement actif
	 * @var string
	 */
	protected $sActiveModule;

	/**
	 * Constructeur.
	 *
	 * @param	object	$okt		Okatea application instance.
	 * @param	string 	$path		Le chemin du répertoire des modules à charger.
	 * @return void
	 */
	public function __construct($okt, $path)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		$this->cache = $okt->cacheConfig;
		$this->cache_id = 'modules';
		$this->cache_repo_id = 'modules_repositories';

		$this->t_extensions = $okt->db->prefix.'core_extensions';

		$this->path = $path;
	}

	/**
	 * Charge les modules disponibles.
	 *
	 * @param	string	ns			Le namespace à prendre en compte (null)
	 * @return void
	 */
	public function loadModules($ns = null)
	{
		if (!$this->cache->contains($this->cache_id)) {
			$this->generateCacheList();
		}

		$aModulesList = $this->cache->fetch($this->cache_id);

		foreach ($aModulesList as $sModuleId=>$module_infos)
		{
			$sModuleClass = 'Okatea\\Modules\\'.$sModuleId.'\\Module';

			$this->list[$sModuleId] = new $sModuleClass($this->okt);

			$this->list[$sModuleId]->setInfos($module_infos);
		}

		foreach ($aModulesList as $sModuleId=>$module_infos)
		{
			$this->list[$sModuleId]->init();
			$this->list[$sModuleId]->initNs($ns);
		}
	}

	/**
	 * Construit la liste des modules et la met en cache.
	 *
	 */
	public function generateCacheList()
	{
		$aModulesList = array();

		$rsModules = $this->getModulesFromDB(array('status'=>1));

		while ($rsModules->fetch())
		{
			$aModulesList[$rsModules->f('id')] = array(
				'id' 			=> $rsModules->f('id'),
				'root'			=> $this->path.'/'.$rsModules->f('id'),
				'name'			=> $rsModules->f('name'),
				'version'		=> $rsModules->f('version'),
				'desc'			=> $rsModules->f('description'),
				'author'		=> $rsModules->f('author'),
				'status'		=> $rsModules->f('status')
			);
		}

		return $this->cache->save($this->cache_id, $aModulesList);
	}

	/**
	 * Retourne la liste des modules actifs.
	 *
	 * @return array
	 */
	public function getListModules()
	{
		return $this->list;
	}

	/**
	 * Ré-initialise la liste des modules actifs.
	 *
	 * @return void
	 */
	public function resetModulesList()
	{
		$this->list = array();
	}

	/**
	 * Indique si un module donné existe dans la liste des modules actifs.
	 *
	 * @param string $sModuleId
	 * @return boolean
	 */
	public function loaded($sModuleId)
	{
		return isset($this->list[$sModuleId]);
	}

	/**
	 * Retourne la liste complète des modules.
	 *
	 * @param string $sModuleId
	 */
	public function getCompleteList($sModuleId = null)
	{
		if ($sModuleId && isset($this->complete_list[$sModuleId])) {
			return $this->complete_list[$sModuleId];
		}

		return $this->complete_list;
	}

	/**
	 * Ré-initialise la liste complète des modules.
	 *
	 * @return void
	 */
	public function resetCompleteList()
	{
		$this->complete_list = array();
	}

	/**
	 * Retourne l'instance d'un module handler donné.
	 *
	 * @param string $sModuleId
	 * @throws Exception
	 */
	public function getModuleObject($sModuleId)
	{
		if (!$this->isLoaded($sModuleId)) {
			throw new \Exception(__('The module specified ('.$sModuleId.') does not appear to be a valid installed module.'));
		}

		return $this->list[$sModuleId];
	}

	public function __get($sModuleId)
	{
		return $this->getModuleObject($sModuleId);
	}

	public function requireDefine($dir,$id)
	{
		if (file_exists($dir.'/_define.php'))
		{
			$this->id = $id;
			require $dir.'/_define.php';
			$this->id = null;
		}
	}

	/**
	 * Retrouve la liste des modules à partir du système de fichiers.
	 *
	 * @return array
	 */
	public function getModulesFromFileSystem()
	{
		if (!is_dir($this->path) || !is_readable($this->path)) {
			return false;
		}

		if (($d = dir($this->path)) === false) {
			return false;
		}

		while (($entry = $d->read()) !== false)
		{
			$full_entry = $this->path.'/'.$entry;

			if ($entry != '.' && $entry != '..' && $entry != '.svn' &&
				is_dir($full_entry) && file_exists($full_entry.'/_define.php'))
			{
				$this->id = $entry;
				$this->mroot = $full_entry;

				require $full_entry.'/_define.php';

				$this->id = null;
				$this->mroot = null;
			}
		}
		$d->close();

		return $this->complete_list;
	}

	/**
	 * Cette fonction est utilisée dans les fichiers _define.php
	 * des modules pour qu'ils soient pris en compte par le système.
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
	 * @param array $aParams 			Le tableau de paramètres
	 * @return void
	 */
	public function registerModule(array $aParams=array())
	{
		if ($this->id)
		{
			$this->complete_list[$this->id] = array(
				'id' 			=> $this->id,
				'root' 			=> $this->mroot,
				'name' 			=> (!empty($aParams['name']) ? $aParams['name'] : $this->id),
				'desc' 			=> (!empty($aParams['desc']) ? $aParams['desc'] : null),
				'version' 		=> (!empty($aParams['version']) ? $aParams['version'] : null),
				'author' 		=> (!empty($aParams['author']) ? $aParams['author'] : null),
				'priority' 		=> (!empty($aParams['priority']) ? (integer)$aParams['priority'] : 1000),
				'updatable' 	=> (!empty($aParams['updatable']) ? (boolean)$aParams['updatable'] : true)
			);
		}
	}

	/**
	 * Retourne la liste des modules dans la base de données selon des paramètres.
	 *
	 * @param array $params Liste des paramètres
	 * @return object recordset
	 */
	public function getModulesFromDB($params=array())
	{
		$reqPlus = 'WHERE type=\'module\' ';

		if (!empty($params['mod_id'])) {
			$reqPlus .= 'AND id=\''.$this->db->escapeStr($params['mod_id']).'\' ';
		}

		if (!empty($params['status'])) {
			$reqPlus .= 'AND status='.(integer)$params['status'].' ';
		}

		$strReq =
		'SELECT id, name, description, author, '.
		'version, priority, updatable, status '.
		'FROM '.$this->t_extensions.' '.
		$reqPlus.
		'ORDER BY priority ASC, id ASC ';

		if (($rs = $this->db->select($strReq)) === false) {
			return new Recordset(array());
		}

		return $rs;
	}

	/**
	 * Retourne la liste des modules installés.
	 *
	 * @return array
	 */
	public function getInstalledModules()
	{
		$rsInstalledModules = $this->getModulesFromDB();

		$aInstalledModules = array();

		while ($rsInstalledModules->fetch())
		{
			$aInstalledModules[$rsInstalledModules->id] = array(
				'id' 			=> $rsInstalledModules->id,
				'root' 			=> $this->path.'/'.$rsInstalledModules->id.'/',
				'name' 			=> $rsInstalledModules->name,
				'name_l10n' 	=> __($rsInstalledModules->name),
				'desc' 			=> $rsInstalledModules->description,
				'desc_l10n' 	=> __($rsInstalledModules->description),
				'author' 		=> $rsInstalledModules->author,
				'version' 		=> $rsInstalledModules->version,
				'priority' 		=> $rsInstalledModules->priority,
				'status' 		=> $rsInstalledModules->status,
				'updatable' 	=> $rsInstalledModules->updatable
			);
		}

		return $aInstalledModules;
	}

	/**
	 * Retourne les informations d'un module donné.
	 *
	 * @param string $sModuleId
	 * @return recordset
	 */
	public function getModule($sModuleId)
	{
		return $this->getModulesFromDB(array('mod_id'=>$sModuleId));
	}

	/**
	 * Ajout d'un module à la base de données.
	 *
	 * @param string $id
	 * @param string $version
	 * @param string $name
	 * @param string $desc
	 * @param string $author
	 * @param integer $priority
	 * @param integer $status
	 * @return booolean
	 */
	public function addModule($id, $version, $name = '', $desc = '', $author = '', $priority = 1000, $status = 0)
	{
		$query =
		'INSERT INTO '.$this->t_extensions.' ('.
			'id, name, description, author, '.
			'version, priority, status, type'.
		') VALUES ('.
			'\''.$this->db->escapeStr($id).'\', '.
			'\''.$this->db->escapeStr($name).'\', '.
			'\''.$this->db->escapeStr($desc).'\', '.
			'\''.$this->db->escapeStr($author).'\', '.
			'\''.$this->db->escapeStr($version).'\', '.
			(integer)$priority.', '.
			(integer)$status.', '.
			'\'module\''.
		') ';

		if ($this->db->execute($query) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Modification d'un module.
	 *
	 * @param string $id
	 * @param string $version
	 * @param string $name
	 * @param string $desc
	 * @param string $author
	 * @param integer $priority
	 * @param integer $status
	 * @return boolean
	 */
	public function updModule($id, $version, $name = '', $desc = '', $author = '', $priority = 1000, $status = null)
	{
		$query =
		'UPDATE '.$this->t_extensions.' SET '.
			'name=\''.$this->db->escapeStr($name).'\', '.
			'description=\''.$this->db->escapeStr($desc).'\', '.
			'author=\''.$this->db->escapeStr($author).'\', '.
			'version=\''.$this->db->escapeStr($version).'\', '.
			'priority='.(integer)$priority.', '.
			'status='.($status === null ? 'status' : (integer)$status).' '.
		'WHERE id=\''.$this->db->escapeStr($id).'\' ';

		if ($this->db->execute($query) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Activation d'un module.
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function enableModule($id)
	{
		$query =
		'UPDATE '.$this->t_extensions.' SET '.
			'status=1 '.
		'WHERE id=\''.$this->db->escapeStr($id).'\' ';

		if ($this->db->execute($query) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Désactivation d'un module.
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function disableModule($id)
	{
		$query =
		'UPDATE '.$this->t_extensions.' SET '.
			'status=0 '.
		'WHERE id=\''.$this->db->escapeStr($id).'\' ';

		if ($this->db->execute($query) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Suppression d'un module.
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function deleteModule($id)
	{
		$query =
		'DELETE FROM '.$this->t_extensions.' '.
		'WHERE id=\''.$this->db->escapeStr($id).'\' ';

		if ($this->db->execute($query) === false) {
			return false;
		}

		$this->db->optimize($this->t_extensions);

		return true;
	}

	/**
	 * Install a module from a zip file.
	 *
	 * @param string $zip_file
	 * @param Collection $modules
	 */
	public static function installPackage($zip_file, $modules)
	{
		$zip = new \fileUnzip($zip_file);
		$zip->getList(false,'#(^|/)(__MACOSX|\.svn|\.DS_Store|Thumbs\.db)(/|$)#');

		$zip_root_dir = $zip->getRootDir();

		if ($zip_root_dir !== false)
		{
			$target = dirname($zip_file);
			$destination = $target.'/'.$zip_root_dir;
			$define = $zip_root_dir.'/_define.php';
			$has_define = $zip->hasFile($define);
		}
		else {
			$target = dirname($zip_file).'/'.preg_replace('/\.([^.]+)$/','',basename($zip_file));
			$destination = $target;
			$define = '_define.php';
			$has_define = $zip->hasFile($define);
		}

		if ($zip->isEmpty())
		{
			$zip->close();
			unlink($zip_file);
			throw new \Exception(__('Empty module zip file.'));
		}

		if (!$has_define)
		{
			$zip->close();
			unlink($zip_file);
			throw new \Exception(__('The zip file does not appear to be a valid module.'));
		}

		$ret_code = 1;

		if (is_dir($destination))
		{
			copy($target.'/_define.php', $target.'/_define.php.bak');

			# test for update
			$sandbox = clone $modules;
			$zip->unzip($define, $target.'/_define.php');

			$sandbox->resetCompleteList();
			$sandbox->requireDefine($target,basename($destination));
			unlink($target.'/_define.php');
			$new_modules = $sandbox->getCompleteList();
			$old_modules = $modules->getModulesFromFileSystem();

			$modules->disableModule(basename($destination));
			$modules->generateCacheList();

			if (!empty($new_modules))
			{
				$tmp = array_keys($new_modules);
				$id = $tmp[0];
				$cur_module = $old_modules[$id];
				if (!empty($cur_module) && $new_modules[$id]['version'] != $cur_module['version'])
				{
					# delete old module
					if (!\files::deltree($destination)) {
						throw new \Exception(__('An error occurred during module deletion.'));
					}
					$ret_code = 2;
				}
				else
				{
					$zip->close();
					unlink($zip_file);

					if (file_exists($target.'/_define.php.bak')) {
						rename($target.'/_define.php.bak', $target.'/_define.php');
					}

					throw new \Exception(sprintf(__('Unable to upgrade "%s". (same version)'),basename($destination)));
				}
			}
			else
			{
				$zip->close();
				unlink($zip_file);

				if (file_exists($target.'/_define.php.bak')) {
					rename($target.'/_define.php.bak', $target.'/_define.php');
				}

				throw new \Exception(sprintf(__('Unable to read new _define.php file')));
			}
		}

		$zip->unzipAll($target);
		$zip->close();
		unlink($zip_file);

		return $ret_code;
	}

	/**
	 * Recherche et utilisation d'une classe d'installation d'un module donné.
	 *
	 * @param string $sModuleId
	 * @return string
	 */
	public function getInstallClass($sModuleId)
	{
		$return = '\\Okatea\Tao\\Modules\\Manage\\Process';

		if (file_exists($this->path.'/'.$sModuleId.'/install/module_install.php'))
		{
			require_once $this->path.'/'.$sModuleId.'/install/module_install.php';

			$class_install = 'moduleInstall_'.$sModuleId;

			if (class_exists($class_install,false) && is_subclass_of($class_install, '\\Okatea\Tao\\Modules\\Manage\\Process')) {
				$return = $class_install;
			}
		}

		return $return;
	}

	/**
	 * Retourne les informations concernant les dépôts de modules.
	 *
	 * @param array $aRepositories
	 * @return array
	 */
	public function getRepositoriesInfos($aRepositories=array())
	{
		if (!$this->cache->contains($this->cache_repo_id)) {
			$this->saveRepositoriesInfosCache($aRepositories);
		}

		return $this->cache->fetch($this->cache_repo_id);
	}

	/**
	 * Enregistre les infos des dépôts dans le cache.
	 *
	 * @param array $aRepositories
	 * @return boolean
	 */
	protected function saveRepositoriesInfosCache($aRepositories)
	{
		return $this->cache->save($this->cache_repo_id, $this->readRepositoriesInfos($aRepositories));
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
		foreach ($aRepositories as $repository_id=>$repository_url)
		{
			if (($infos = $this->getRepositoryInfos($repository_url)) !== false) {
				$aModulesRepositories[$repository_id] = $infos;
			}
		}

		return $aModulesRepositories;
	}

	/**
	 * Retourne les informations d'un dépôt de modules donné.
	 *
	 * @param array $repository_url
	 * @return array
	 */
	protected function getRepositoryInfos($repository_url)
	{
		$repository_url = str_replace('%VERSION%', $this->okt->getVersion(), $repository_url);

		if (filter_var($repository_url, FILTER_VALIDATE_URL) === false) {
			return false;
		}

		$client = new HttpClient();
		$response = $client->get($repository_url)->send();

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
			$xml = new \SimpleXMLElement($str,LIBXML_NOERROR);

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


	/* Méthodes utilitaires.
	----------------------------------------------------------*/

	/**
	 * Fonction de "pluralisation" des modules.
	 *
	 * @param integer $count
	 * @return string
	 */
	public static function pluralizeModuleCount($count)
	{
		if ($count == 1) {
			return __('c_a_modules_one_module');
		}
		elseif ($count > 1) {
			return sprintf(__('c_a_modules_%s_modules'),$count);
		}

		return __('c_a_modules_no_module');
	}

	/**
	 * Tri les modules par ordre alphabétique.
	 *
	 * @param array $aModules
	 * @return void
	 */
	public static function sortModules(array &$aModules)
	{
		uasort($aModules, 'self::sortModulesListCallable');
	}

	/**
	 * Fonction de callback de tri des modules.
	 *
	 * @param string $a
	 * @param string $b
	 * @return number
	 */
	protected static function sortModulesListCallable($a,$b)
	{
		return strcasecmp($a['name_l10n'],$b['name_l10n']);
	}
}
