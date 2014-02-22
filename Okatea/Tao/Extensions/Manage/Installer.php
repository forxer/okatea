<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Extensions\Manage;

use Okatea\Tao\Database\XmlSql;
use Okatea\Tao\Diff\Engine as DiffEngine;
use Okatea\Tao\Diff\Renderer\Html\SideBySide as DiffRenderer;
use Okatea\Tao\Html\CheckList;
use Okatea\Tao\Extensions\Extension;
use Okatea\Tao\Extensions\Manage\Component\AssetsFiles;
use Okatea\Tao\Extensions\Manage\Component\Comparator;
use Okatea\Tao\Extensions\Manage\Component\ConfigFiles;
use Okatea\Tao\Extensions\Manage\Component\RoutesFiles;
use Okatea\Tao\Extensions\Manage\Component\TemplatesFiles;
use Okatea\Tao\Extensions\Manage\Component\UploadsFiles;
use Okatea\Tao\Themes\Collection as ThemesCollection;
use Okatea\Tao\Users\Groups;

/**
 * Installation d'une extension Okatea.
 *
 */
class Installer extends Extension
{
	/**
	 * Une checklist
	 * @var object
	 */
	public $checklist;

	/**
	 * Reserved modules ID
	 * @var array
	 */
	private static $aReservedIds = array(
		'autoloader', 'debug', 'debugBar', 'cacheConfig', 'config', 'db', 'error',
		'languages', 'l10n', 'logAdmin', 'modules', 'navigation',
		'page', 'request', 'requestContext', 'response', 'router', 'adminRouter',
		'session', 'theme', 'theme_id', 'tpl', 'triggers', 'user',
		'htmlpurifier', 'permsStack', 'aTplDirectories'
	);

	/**
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\AssetsFiles
	 */
	protected $assetsFiles;

	/**
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\Comparator
	 */
	protected $comparator;

	/**
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\ConfigFiles
	 */
	protected $configFiles;

	/**
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\RoutesFiles
	 */
	protected $routesFiles;

	/**
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\RoutesFiles
	 */
	protected $routesAdminFiles;

	/**
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\TemplatesFiles
	 */
	protected $templatesFiles;

	/**
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\UploadsFiles
	 */
	protected $uploadsFiles;

	/**
	 * Constructeur
	 *
	 * @param core $this->okt
	 * @param string $id
	 * @return void
	 */
	public function __construct($okt, $path, $id)
	{
		parent::__construct($okt, $path);

		$this->checklist = new CheckList();

		# get infos from define file
		$this->setInfo('id', $id);
		$this->setInfosFromDefineFile();
	}

	/**
	 * Perform install.
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
		if (method_exists($this, 'install')) {
			$this->install();
		}

		# ajout à la base de données
		$this->checklist->addItem(
			'add_extension_to_db',
			$this->okt->modules->addModule($this->id(), $this->version(), $this->name(), $this->desc(), $this->author(), $this->priority(), 0),
			'Add extension to database',
			'Cannot add extension to database'
		);

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this, 'installEnd')) {
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
		if (method_exists($this, 'update')) {
			$this->update();
		}

		# modification dans la base de données
		$this->checklist->addItem(
			'update_extension_in_db',
			$this->okt->modules->updModule($this->id(), $this->version(), $this->name(), $this->desc(), $this->author(), $this->priority()),
			'Update extension into database',
			'Cannot update extension into database'
		);

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this, 'updateEnd')) {
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
		$this->loadDbFile($this->root().'/Install/db-uninstall.xml');

		# suppression des fichiers templates
		$this->getTemplatesFiles()->delete();

		# suppression des fichiers d'upload
		$this->getUploadsFiles()->delete();

		# suppression des fichiers assets
		$this->getAssetsFiles()->delete();

		# suppression des fichiers de config
		$this->getConfigFiles()->delete();

		# suppression des fichiers de routes
		$this->getRoutesFiles()->delete();

		# suppression des fichiers des routes admin
		$this->getRoutesAdminFiles()->delete();

		# suppression de la base de données
		$this->checklist->addItem(
			'remove_extension_from_db',
			$this->okt->modules->deleteModule($this->id()),
			'Remove extension from database',
			'Cannot remove extension from database'
		);

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this, 'uninstallEnd')) {
			$this->uninstallEnd();
		}
	}

	/**
	 * Perform empty extension.
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
		$this->loadDbFile($this->root().'/Install/db-truncate.xml');

		# suppression des fichiers d'upload
		$this->getUploadsFiles()->delete();

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
		$this->loadDbFile($this->root().'/Install/test_set/db-data.xml');

		# copie des éventuels fichiers upload
		$this->getUploadsFiles()->process();

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
		$this->loadDbFile($this->root().'/Install/db-data.xml');

		if (method_exists($this,'installDefaultData')) {
			$this->installDefaultData();
		}
	}

	public function compareFiles()
	{
		# compare templates
		$this->getComparator()->folder($this->root().'/Install/tpl/', $this->okt->options->get('themes_dir').'/default/templates/');

		foreach (ThemesCollection::getThemes() as $sThemeId=>$sTheme)
		{
			if ($sThemeId == 'default') {
				continue;
			}

			$this->getComparator()->folder($this->root().'/Install/tpl/', $this->okt->options->get('themes_dir').'/'.$sThemeId.'/templates/', true);
		}

		# compare assets
		$this->getComparator()->folder($this->root().'/Install/assets/', $this->okt->options->get('public_dir').'/modules/'.$this->id().'/');
	}

	/**
	 * Installation de la base de données depuis un fichier
	 *
	 * @param string $db_file
	 */
	protected function loadDbFile($db_file, $process=null)
	{
		if (file_exists($db_file))
		{
			$xsql = new XmlSql($this->db, file_get_contents($db_file), $this->checklist, $process);
			$xsql->replace('{{PREFIX}}',$this->okt->db->prefix);
			$xsql->execute();
		}
	}

	/**
	 * Cette fonction test les pré-requis pour installer une extension.
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

			$sClassName = 'Okatea\\Modules\\'.$this->id().'\\Module';

			$this->checklist->addItem(
				'module_class',
				class_exists($sClassName),
				'Module handler class "'.$sClassName.'" exists',
				'Module handler class "'.$sClassName.'" doesn\'t exists'
			);

			$this->checklist->addItem(
				'module_class_valide',
				is_subclass_of($sClassName, '\\Okatea\\Tao\\Modules\\Module'),
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
		$this->loadDbFile($this->root().'/Install/db-install.xml',$process);

		# copie des éventuels fichiers templates
		$this->getTemplatesFiles()->process();

		# copie des éventuels fichiers assets
		$this->getAssetsFiles()->process();

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
		'WHERE group_id='.Groups::ADMIN;

		$rsPerms = $this->db->select($query);

		$aCurrentPerms = array();
		if (!$rsPerms->isEmpty()) {
			$aCurrentPerms = json_decode($rsPerms->perms);
		}

		$aNewPerms = array_merge($aCurrentPerms,$aDefaultPerms);
		$aNewPerms = json_encode($aNewPerms);

		$query =
		'UPDATE '.$this->db->prefix.'core_users_groups SET '.
		'perms=\''.$this->db->escapeStr($aNewPerms).'\' '.
		'WHERE group_id='.Groups::ADMIN;

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

	protected function getTemplatesFiles()
	{
		if (null === $this->templatesFiles) {
			$this->templatesFiles = new TemplatesFiles($this->okt, $this);
		}

		return $this->templatesFiles;
	}

	protected function getUploadsFiles()
	{
		if (null === $this->uploadsFiles) {
			$this->uploadsFiles = new UploadsFiles($this->okt, $this);
		}

		return $this->uploadsFiles;
	}
}
