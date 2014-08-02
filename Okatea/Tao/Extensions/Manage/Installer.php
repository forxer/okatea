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
use Okatea\Tao\Html\Checklister;
use Okatea\Tao\Extensions\Extension;
use Okatea\Tao\Extensions\Manager;
use Okatea\Tao\Extensions\Manage\Component\AssetsFiles;
use Okatea\Tao\Extensions\Manage\Component\Comparator;
use Okatea\Tao\Extensions\Manage\Component\ConfigFiles;
use Okatea\Tao\Extensions\Manage\Component\RoutesFiles;
use Okatea\Tao\Extensions\Manage\Component\TemplatesFiles;
use Okatea\Tao\Extensions\Manage\Component\UploadsFiles;
use Okatea\Tao\Users\Groups;

class Installer extends Extension
{

	/**
	 * A checklist utility.
	 *
	 * @var Okatea\Tao\Html\CheckList
	 */
	public $checklist;

	/**
	 * Assets files utility.
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\AssetsFiles
	 */
	protected $assetsFiles;

	/**
	 * Files comparator utility.
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\Comparator
	 */
	protected $comparator;

	/**
	 * Configuration files utility.
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\ConfigFiles
	 */
	protected $configFiles;

	/**
	 * Public routes files utility.
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\RoutesFiles
	 */
	protected $routesFiles;

	/**
	 * Admin routes files utility.
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\RoutesFiles
	 */
	protected $routesAdminFiles;

	/**
	 * Templates files utility.
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\TemplatesFiles
	 */
	protected $templatesFiles;

	/**
	 * Upload files utility.
	 *
	 * @var Okatea\Tao\Extensions\Manage\Component\UploadsFiles
	 */
	protected $uploadsFiles;

	/**
	 * Extensions manager instance.
	 *
	 * @var Okatea\Tao\Extensions\Manage
	 */
	protected $manager;

	/**
	 * Constructor.
	 *
	 * @param object $okt
	 *        	Okatea application instance.
	 * @param string $sExtensionsPath
	 *        	The extensions directory path to load.
	 * @param string $sExtensionId        	
	 * @return void
	 */
	public function __construct($okt, $sExtensionsPath, $sExtensionId)
	{
		parent::__construct($okt, $sExtensionsPath);
		
		$this->checklist = new Checklister();
		
		# get extension infos from define file
		$this->setInfo('id', $sExtensionId);
		$this->setInfosFromDefineFile();
	}

	/**
	 * Perform install.
	 *
	 * @return void
	 */
	public function doInstall()
	{
		if (! $this->preInstall())
		{
			$this->checklist->addItem('install_aborted', false, 'Install aborted...', 'Install aborted...');
			
			return false;
		}
		
		# opérations communes à l'installation et la mise à jour
		$this->commonInstallUpdate('install');
		
		# ajout d'éventuelles données par défaut à la base de données
		$this->doInstallDefaultData();
		
		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this, 'install'))
		{
			$this->install();
		}
		
		# ajout de l'extension à la base de données
		$this->checklist->addItem('add_extension_to_db', $this->getManager()
			->addExtension($this->id(), $this->version(), $this->name(), $this->desc(), $this->author(), $this->priority(), 0), 'Add extension to database', 'Cannot add extension to database');
		
		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this, 'installEnd'))
		{
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
		if (method_exists($this, 'update'))
		{
			$this->update();
		}
		
		# modification dans la base de données
		$this->checklist->addItem('update_extension_in_db', $this->getManager()
			->updateExtension($this->id(), $this->version(), $this->name(), $this->desc(), $this->author(), $this->priority()), 'Update extension into database', 'Cannot update extension into database');
		
		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this, 'updateEnd'))
		{
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
		if (method_exists($this, 'uninstall'))
		{
			$this->uninstall();
		}
		
		# désinstallation de la base de données
		$this->loadDbFile($this->root() . '/Install/db-uninstall.xml');
		
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
		$this->checklist->addItem('remove_extension_from_db', $this->getManager()
			->deleteExtension($this->id()), 'Remove extension from database', 'Cannot remove extension from database');
		
		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this, 'uninstallEnd'))
		{
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
		if (method_exists($this, 'truncate'))
		{
			$this->truncate();
		}
		
		# vidange de la base de données
		$this->loadDbFile($this->root() . '/Install/db-truncate.xml');
		
		# suppression des fichiers d'upload
		$this->getUploadsFiles()->delete();
		
		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this, 'truncateEnd'))
		{
			$this->truncateEnd();
		}
	}

	/**
	 * Perform install test set.
	 *
	 * @return void
	 */
	public function doInstallTestSet()
	{
		# ajout d'éventuelles données à la base de données
		$this->loadDbFile($this->root() . '/Install/TestSet/db-data.xml');
		
		# copie des éventuels fichiers upload
		$this->getUploadsFiles()->process();
		
		if (method_exists($this, 'installTestSet'))
		{
			$this->installTestSet();
		}
	}

	/**
	 * Perform install default data.
	 *
	 * @return void
	 */
	public function doInstallDefaultData()
	{
		# ajout d'éventuelles données à la base de données
		$this->loadDbFile($this->root() . '/Install/db-data.xml');
		
		if (method_exists($this, 'installDefaultData'))
		{
			$this->installDefaultData();
		}
	}

	public function forceReplaceTpl()
	{
		$this->getTemplatesFiles()->process();
	}

	public function forceReplaceAssets()
	{
		$this->getAssetsFiles()->process();
	}

	/**
	 * Installing the tables in the database from a file.
	 *
	 * @param string $sDbFilename        	
	 * @param string $sProcess
	 *        	Install or update process.
	 */
	protected function loadDbFile($sDbFilename, $sProcess = null)
	{
		if (file_exists($sDbFilename))
		{
			$xsql = new XmlSql($this->db, file_get_contents($sDbFilename), $this->checklist, $sProcess);
			$xsql->replace('{{PREFIX}}', $this->okt->db->prefix);
			$xsql->execute();
		}
	}

	/**
	 * Test prerequisites to install an extension.
	 *
	 * @return boolean
	 */
	protected function preInstall()
	{
		return true;
	}

	/**
	 * Common actions to installing and updating extensions.
	 *
	 * @param string $sProcess
	 *        	Install or update process.
	 * @return void
	 */
	protected function commonInstallUpdate($sProcess)
	{
		# installation/mise à jour de la base de données
		$this->loadDbFile($this->root() . '/Install/db-install.xml', $sProcess);
		
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
	protected function setDefaultAdminPerms($aDefaultPerms = array())
	{
		$query = 'SELECT perms FROM ' . $this->db->prefix . 'core_users_groups ' . 'WHERE group_id=' . Groups::ADMIN;
		
		$rsPerms = $this->db->select($query);
		
		$aCurrentPerms = array();
		if (! $rsPerms->isEmpty())
		{
			$aCurrentPerms = json_decode($rsPerms->perms);
		}
		
		$aNewPerms = array_merge($aCurrentPerms, $aDefaultPerms);
		$aNewPerms = json_encode($aNewPerms);
		
		$query = 'UPDATE ' . $this->db->prefix . 'core_users_groups SET ' . 'perms=\'' . $this->db->escapeStr($aNewPerms) . '\' ' . 'WHERE group_id=' . Groups::ADMIN;
		
		$this->db->execute($query);
	}

	protected function getAssetsFiles()
	{
		if (null === $this->assetsFiles)
		{
			$this->assetsFiles = new AssetsFiles($this->okt, $this, $this->okt['public_dir'] . '/extensions/%s');
		}
		
		return $this->assetsFiles;
	}

	protected function getComparator()
	{
		if (null === $this->comparator)
		{
			$this->comparator = new Comparator($this->okt, $this);
		}
		
		return $this->comparator;
	}

	protected function getConfigFiles()
	{
		if (null === $this->configFiles)
		{
			$this->configFiles = new ConfigFiles($this->okt, $this);
		}
		
		return $this->configFiles;
	}

	protected function getRoutesFiles()
	{
		if (null === $this->routesFiles)
		{
			$this->routesFiles = new RoutesFiles($this->okt, $this);
			$this->routesFiles->setRoutesDirectory('Routes');
		}
		
		return $this->routesFiles;
	}

	protected function getRoutesAdminFiles()
	{
		if (null === $this->routesAdminFiles)
		{
			$this->routesAdminFiles = new RoutesFiles($this->okt, $this);
			$this->routesAdminFiles->setRoutesDirectory('RoutesAdmin');
		}
		
		return $this->routesAdminFiles;
	}

	protected function getTemplatesFiles()
	{
		if (null === $this->templatesFiles)
		{
			$this->templatesFiles = new TemplatesFiles($this->okt, $this);
		}
		
		return $this->templatesFiles;
	}

	protected function getUploadsFiles()
	{
		if (null === $this->uploadsFiles)
		{
			$this->uploadsFiles = new UploadsFiles($this->okt, $this);
		}
		
		return $this->uploadsFiles;
	}

	/**
	 * Return manager instance.
	 *
	 * @return \Okatea\Tao\Extensions\Manager
	 */
	protected function getManager()
	{
		if (null === $this->manager)
		{
			return ($this->manager = new Manager($this->okt, $this->sExtensionsPath));
		}
		
		return $this->manager;
	}
}
