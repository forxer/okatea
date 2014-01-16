<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Modules\Manage;

use Okatea\Tao\Core\Authentification;
use Okatea\Tao\Database\XmlSql;
use Okatea\Tao\Diff\Engine as DiffEngine;
use Okatea\Tao\Diff\Renderer\Html\SideBySide as DiffRenderer;
use Okatea\Tao\Html\CheckList;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Modules\Module;
use Okatea\Tao\Modules\Manage\Component\AssetsFiles\AssetsFiles;
use Okatea\Tao\Modules\Manage\Component\Comparator\Comparator;
use Okatea\Tao\Modules\Manage\Component\ConfigFiles\ConfigFiles;
use Okatea\Tao\Modules\Manage\Component\RoutesFiles\RoutesFiles;
use Okatea\Tao\Themes\Collection as ThemesCollection;

/**
 * Installation d'un module Okatea.
 *
 */
class Process extends Module
{
	/**
	 * Une checklist
	 * @var object
	 */
	public $checklist;

	private static $aReservedIds = array(
		'autoloader', 'debug', 'cache', 'config', 'db', 'error',
		'languages', 'l10n', 'logAdmin', 'modules', 'navigation',
		'page', 'request', 'requestContext', 'response', 'router',
		'session', 'theme', 'theme_id', 'tpl', 'triggers', 'user',
		'htmlpurifier', 'permsStack', 'aTplDirectories'
	);

	/**
	 *
	 * @var Okatea\Tao\Modules\Manage\Component\AssetsFiles\AssetsFiles
	 */
	protected $assetsFiles;

	/**
	 *
	 * @var Okatea\Tao\Modules\Manage\Component\ConfigFiles\ConfigFiles
	 */
	protected $configFiles;

	/**
	 *
	 * @var Okatea\Tao\Modules\Manage\Component\RoutesFiles\RoutesFiles
	 */
	protected $routesFiles;

	/**
	 *
	 * @var Okatea\Tao\Modules\Manage\Component\RoutesFiles\RoutesFiles
	 */
	protected $routesAdminFiles;

	/**
	 *
	 * @var Okatea\Tao\Modules\Manage\Component\Comparator\Comparator
	 */
	protected $comparator;

	/**
	 * Constructeur
	 *
	 * @param core $this->okt
	 * @param string $id
	 * @return void
	 */
	public function __construct($okt, $modules_path, $id)
	{
		parent::__construct($okt, $modules_path);

		$this->checklist = new CheckList();

		# get infos from define file
		$this->setInfo('id', $id);
		$this->setInfosFromDefineFile();
	}

	/**
	 * Perform module install.
	 *
	 * @return void
	 */
	public function doInstall()
	{
		if (!$this->preInstall())
		{
			$this->checklist->addItem(
				'install_aborted',
				false,
				'Install aborted...',
				'Install aborted...'
			);

			return false;
		}

		# opérations communes à l'installation et la mise à jour
		$this->commonInstallUpdate('install');

		# ajout d'éventuelles données par défaut à la base de données
		$this->doInstallDefaultData();

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'install')) {
			$this->install();
		}

		# ajout du module à la base de données
		$this->checklist->addItem(
			'add_module_to_db',
			$this->okt->modules->addModule($this->id(),$this->version(),$this->name(),$this->desc(),$this->author(),$this->priority(),0),
			'Add module to database',
			'Cannot add module to database'
		);

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'installEnd')) {
			$this->installEnd();
		}
	}

	/**
	 * Perform update
	 *
	 * @return void
	 */
	public function doUpdate()
	{
		# opérations communes à l'installation et la mise à jour
		$this->commonInstallUpdate('update');

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'update')) {
			$this->update();
		}

		# ajout du module à la base de données
		$this->checklist->addItem(
			'update_module_in_db',
			$this->okt->modules->updModule($this->id(),$this->version(),$this->name(),$this->desc(),$this->author(),$this->priority()),
			'Update module into database',
			'Cannot update module into database'
		);

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'updateEnd')) {
			$this->updateEnd();
		}
	}

	/**
	 * Perform uninstall.
	 *
	 * @return void
	 */
	public function doUninstall()
	{
		if (method_exists($this,'uninstall')) {
			$this->uninstall();
		}

		# désinstallation de la base de données
		$this->loadDbFile($this->root().'/install/db-uninstall.xml');

		# suppression des fichiers templates
		if (is_dir($this->root().'/install/tpl'))
		{
			$d = dir($this->root().'/install/tpl');
			while (false !== ($entry = $d->read()))
			{
				if (!is_dir($entry) && $entry != '.' && $entry != '..' && $entry != '.svn') {
					$this->uninstallTplFile($this->root().'/install/tpl/'.$entry);
				}
			}
			$d->close();
		}

		# suppression des fichiers public
		if (is_dir($this->root().'/install/public'))
		{
			$d = dir($this->root().'/install/public');
			while (false !== ($entry = $d->read()))
			{
				if (!is_dir($entry) && $entry != '.' && $entry != '..' && $entry != '.svn') {
					$this->uninstallPublicFile($this->root().'/install/public/'.$entry);
				}
			}
			$d->close();
		}

		# suppression des fichiers d'upload
		$sUploadDir = $this->okt->options->get('upload_dir').'/'.$this->id().'/';
		if (file_exists($sUploadDir))
		{
			$this->checklist->addItem(
				'remove_upload_dir',
				\files::deltree($sUploadDir),
				'Remove upload dir',
				'Cannot remove upload dir'
			);
		}

		# suppression des fichiers assets
		$this->getAssetsFiles()->delete();

		# suppression des fichiers de config
		$this->getConfigFiles()->delete();

		# suppression des fichiers de routes
		$this->getRoutesFiles()->delete();

		# suppression des fichiers des routes admin
		$this->getRoutesAdminFiles()->delete();

		# suppression du module de la base de données
		$this->checklist->addItem(
			'remove_module_from_db',
			$this->okt->modules->deleteModule($this->id()),
			'Remove module from database',
			'Cannot remove module from database'
		);

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'uninstallEnd')) {
			$this->uninstallEnd();
		}
	}

	/**
	 * Perform empty module.
	 *
	 * @return void
	 */
	public function doEmpty()
	{
		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'truncate')) {
			$this->truncate();
		}

		# vidange de la base de données
		$this->loadDbFile($this->root().'/install/db-truncate.xml');

		# suppression des fichiers d'upload
		$sUploadDir = $this->okt->options->get('upload_dir').'/'.$this->id().'/';
		if (file_exists($sUploadDir))
		{
			$this->checklist->addItem(
				'remove_upload_dir',
				\files::deltree($sUploadDir),
				'Remove upload dir',
				'Cannot remove upload dir'
			);
		}

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'truncateEnd')) {
			$this->truncateEnd();
		}
	}


	/* Méthodes d'installation des jeux de test
	----------------------------------------------------------*/

	/**
	 * Perform install test set
	 *
	 * @return void
	 */
	public function doInstallTestSet()
	{
		# ajout d'éventuelles données à la base de données
		$this->loadDbFile($this->root().'/install/test_set/db-data.xml');

		# copie des éventuels fichiers upload
		if (is_dir($this->root().'/install/test_set/upload/'))
		{
			$this->checklist->addItem(
				'upload_dir',
				$this->forceReplaceUploads(),
				'Create upload dir',
				'Cannot create upload dir'
			);
		}

		if (method_exists($this,'installTestSet')) {
			$this->installTestSet();
		}
	}

	/**
	 * Perform install default data
	 *
	 * @return void
	 */
	public function doInstallDefaultData()
	{
		# ajout d'éventuelles données à la base de données
		$this->loadDbFile($this->root().'/install/db-data.xml');

		if (method_exists($this,'installDefaultData')) {
			$this->installDefaultData();
		}
	}

	public function compareFiles()
	{
		# compare templates
		$this->getComparator()->folder($this->root().'/install/tpl/', $this->okt->options->get('themes_dir').'/default/templates/');

		foreach (ThemesCollection::getThemes() as $sThemeId=>$sTheme)
		{
			if ($sThemeId == 'default') {
				continue;
			}

			$this->getComparator()->folder($this->root().'/install/tpl/', $this->okt->options->get('themes_dir').'/'.$sThemeId.'/templates/', true);
		}

		# compare assets
		$this->getComparator()->folder($this->root().'/install/assets/', $this->okt->options->get('public_dir').'/modules/'.$this->id().'/');
	}

	/**
	 * Force le remplacement des fichiers de template
	 *
	 */
	public function forceReplaceTpl()
	{
		return $this->forceReplaceFiles(
			$this->root().'/install/tpl/',
			$this->okt->options->get('themes_dir').'/default/templates/'
		);
	}

	/**
	 * Force le remplacement des fichiers actifs
	 *
	 */
	public function forceReplaceAssets()
	{
		return $this->getAssetsFiles()->process();
	}

	/**
	 * Force le remplacement des fichiers d'upload
	 *
	 */
	public function forceReplaceUploads()
	{
		return $this->forceReplaceFiles(
			$this->root().'/install/test_set/upload/',
			$this->okt->options->get('upload_dir').'/'.$this->id().'/'
		);
	}

	/**
	 * Désinstallation d'un fichier de template
	 *
	 * @param string $file
	 * @return void
	 */
	protected function uninstallTplFile($file)
	{
		static $count;

		if (empty($count)) {
			$count = 1;
		}

		$filename = basename($file);

		# suppression du fichier .bak de façon silencieuse
		if (file_exists($this->okt->options->get('themes_dir').'/default/templates/'.$filename.'.bak')) {
			@unlink($this->okt->options->get('themes_dir').'/default/templates/'.$filename.'.bak');
		}

		# si le fichier existe on le supprime
		if (file_exists($this->okt->options->get('themes_dir').'/default/templates/'.$filename))
		{
			$this->checklist->addItem(
				'tpl_file_'.$count,
				(is_dir($this->okt->options->get('themes_dir').'/default/templates/'.$filename) ? \files::deltree($this->okt->options->get('themes_dir').'/default/templates/'.$filename) : unlink($this->okt->options->get('themes_dir').'/default/templates/'.$filename)),
				'Remove template file '.$filename,
				'Cannot remove template file '.$filename
			);
		}
		else {
			$this->checklist->addItem(
				'tpl_file_'.$count,
				null,
				'Template file '.$filename.' doesn\'t exists',
				'Template file '.$filename.' doesn\'t exists'
			);
		}

		$count++;
	}

	/**
	 * Force le remplacement des fichiers de façon récursive dans les fichiers
	 *
	 */
	protected function forceReplaceFiles($sSourceDir, $sDestDir, $aLockedFiles=array())
	{
		if (!is_dir($sSourceDir)) {
			return false;
		}

		$aSources = \files::getDirList($sSourceDir);
		$aDests = array();

		foreach ($aSources['files'] as $file) {
			$aDests[] = str_replace($sSourceDir,'',$file);
		}

		if (!is_dir($sDestDir)) {
			\files::makeDir($sDestDir,true);
		}

		foreach ($aDests as $file)
		{
			$parent_dir = dirname($sDestDir.$file);

			if (!is_dir($parent_dir)) {
				\files::makeDir($parent_dir,true);
			}

			if (in_array($sDestDir.$file, $aLockedFiles)) {
				continue;
			}

			if (file_exists($sDestDir.$file))
			{
				if (file_exists($sDestDir.$file.'.bak')) {
					unlink($sDestDir.$file.'.bak');
				}

				rename($sDestDir.$file, $sDestDir.$file.'.bak');
			}

			copy($sSourceDir.$file, $sDestDir.$file);
		}

		return true;
	}

	protected function copyTplFiles()
	{
		if (is_dir($this->root().'/install/tpl/'))
		{
			$this->checklist->addItem(
				'tpl_dir',
				$this->forceReplaceTpl(),
				'Create templates files',
				'Cannot create templates files'
			);
		}
	}

	protected function copyAssetsFiles()
	{
		if (is_dir($this->root().'/install/assets/'))
		{
			$this->checklist->addItem(
				'assets',
				$this->forceReplaceAssets(),
				'Create assets files',
				'Cannot create assets files'
			);
		}
	}

	/**
	 * Installation de la base de données depuis un fichier
	 *
	 * @param string $db_file
	 */
	protected function loadDbFile($db_file, $process=null)
	{
		static $count;

		if (empty($count)) {
			$count = 1;
		}

		if (file_exists($db_file))
		{
			$xsql = new XmlSql($this->db, file_get_contents($db_file), $this->checklist, $process);
			$xsql->replace('{{PREFIX}}',$this->okt->db->prefix);
			$xsql->execute();
		}
		else
		{
			$this->checklist->addItem(
				'db_file_'.$count,
				null,
				'DB file '.$db_file.' doesn\'t exists',
				'DB file '.$db_file.' doesn\'t exists'
			);
		}

		$count++;
	}

	/**
	 * Cette fonction test les pré-requis pour installer un module.
	 *
	 * @return boolean
	 */
	protected function preInstall()
	{
		# identifiant non-réservé ?
		$this->checklist->addItem(
			'module_id_not_reserved',
			!in_array($this->id(), self::$aReservedIds),
			'Module id not reserved',
			'Module id can not be one of:'.implode('", "', self::$aReservedIds)
		);

		# présence du fichier /Module.php
		$this->checklist->addItem(
			'module_file',
			file_exists($this->root().'/Module.php'),
			'Module handler file exists',
			'Module handler file doesn\'t exists'
		);

		# existence de la class module_<id_module>
		if ($this->checklist->checkItem('module_file'))
		{
			include $this->root().'/Module.php';

			$sClassName = 'Okatea\\Module\\'.$this->id().'\\Module';

			$this->checklist->addItem(
				'module_class',
				class_exists($sClassName),
				'Module handler class "'.$sClassName.'" exists',
				'Module handler class "'.$sClassName.'" doesn\'t exists'
			);

			$this->checklist->addItem(
				'module_class_valide',
				is_subclass_of($sClassName,'\\Okatea\Tao\\Modules\\Module'),
				'Module handler class "'.$sClassName.'" is a valid module class',
				'Module handler class "'.$sClassName.'" is not a valid module class'
			);
		}

		return
			$this->checklist->checkItem('module_file')
			&& $this->checklist->checkItem('module_class')
			&& $this->checklist->checkItem('module_class_valide');
	}

	/**
	 * Action communes à l'installation et la mise à jour.
	 *
	 */
	protected function commonInstallUpdate($process)
	{
		# installation/mise à jour de la base de données
		$this->loadDbFile($this->root().'/install/db-install.xml',$process);

		# copie des éventuels fichiers templates
		$this->copyTplFiles();

		# copie des éventuels fichiers assets
		$this->copyAssetsFiles();

		# copie des éventuels fichiers de configurations
		$this->getConfigFiles()->process();

		# copie des éventuels fichiers de routes
		$this->getRoutesFiles()->process();

		# copie des éventuels fichiers de routes admin
		$this->getRoutesAdminFiles()->process();
	}

	/**
	 * Ajout de permission par défaut au groupe admin.
	 *
	 * @param array $aDefaultPerms
	 */
	protected function setDefaultAdminPerms($aDefaultPerms=array())
	{
		$query =
		'SELECT perms FROM '.$this->db->prefix.'core_users_groups '.
		'WHERE group_id='.(integer)Authentification::admin_group_id;

		$rsPerms = $this->db->select($query);

		$aCurrentPerms = array();
		if (!$rsPerms->isEmpty()) {
			$aCurrentPerms = unserialize($rsPerms->perms);
		}

		$aNewPerms = array_merge($aCurrentPerms,$aDefaultPerms);
		$aNewPerms = serialize($aNewPerms);

		$query =
		'UPDATE '.$this->db->prefix.'core_users_groups SET '.
		'perms=\''.$this->db->escapeStr($aNewPerms).'\' '.
		'WHERE group_id='.(integer)Authentification::admin_group_id;

		$this->db->execute($query);
	}

	protected function getAssetsFiles()
	{
		if (null === $this->assetsFiles) {
			$this->assetsFiles = new AssetsFiles($this->okt, $this);
		}

		return $this->assetsFiles;
	}

	protected function getComparator()
	{
		if (null === $this->comparator) {
			$this->comparator = new Comparator($this->okt, $this);
		}

		return $this->comparator;
	}

	protected function getConfigFiles()
	{
		if (null === $this->configFiles) {
			$this->configFiles = new ConfigFiles($this->okt, $this);
		}

		return $this->configFiles;
	}

	protected function getRoutesFiles()
	{
		if (null === $this->routesFiles) {
			$this->routesFiles = new RoutesFiles($this->okt, $this);
			$this->routesFiles->setRoutesDirectory('routes');
		}

		return $this->routesFiles;
	}

	protected function getRoutesAdminFiles()
	{
		if (null === $this->routesAdminFiles) {
			$this->routesAdminFiles = new RoutesFiles($this->okt, $this);
			$this->routesAdminFiles->setRoutesDirectory('routes_admin');
		}

		return $this->routesAdminFiles;
	}
}
