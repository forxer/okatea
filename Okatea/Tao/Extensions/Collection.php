<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Extensions;

class Collection
{
	/**
	 * Okatea application instance.
	 *
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The type of extensions.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The directory path extensions.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Extensions cache identifier.
	 *
	 * @var string
	 */
	protected $sCacheId;

	/**
	 * Extensions class name pattern.
	 *
	 * @var string
	 */
	protected $sExtensionClassPatern;

	/**
	 * List of loaded extensions.
	 *
	 * @var array
	 */
	protected $aLoaded;

	/**
	 * Base installer class
	 *
	 * @var string
	 */
	protected $sInstallerClass = '\\Okatea\Tao\\Extensions\\Manage\\Installer';

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

		$this->type = 'extension';

		$this->path = $sPath;
	}

	/**
	 * Load available extensions.
	 *
	 * @param string $ns
	 *        	The namespace to consider (null)
	 * @return void
	 */
	public function load($ns = null)
	{
		if (! $this->okt['cacheConfig']->contains($this->sCacheId))
		{
			$this->generateCacheList();
		}

		$aList = $this->okt['cacheConfig']->fetch($this->sCacheId);

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
	public function resetLoaded()
	{
		$this->aLoaded = array();
	}

	/**
	 * Indicates whether a given extension is in the list of loaded extensions.
	 *
	 * @param string $sExtensionId
	 * @return boolean
	 */
	public function isLoaded($sExtensionId)
	{
		return isset($this->aLoaded[$sExtensionId]);
	}

	/**
	 * Indicates whether a given extension is installed.
	 *
	 * @param string $sExtensionId
	 * @return boolean
	 */
	public function isInstalled($sExtensionId)
	{
		$this->aInstalledThemes = $this->getManager()->getInstalled();

		return isset($this->aInstalledThemes[$sExtensionId]);
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
		if (! isset($this->aLoaded[$sExtensionId]))
		{
			throw new \RuntimeException(__('The extension specified (' . $sExtensionId . ') does not appear to be a valid loaded extension.'));
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

		$rsExtensions = $this->getManager()->getFromDatabase(array(
			'status' => 1
		));

		while ($rsExtensions->fetch())
		{
			$aLoaded[$rsExtensions->f('id')] = array(
				'id' => $rsExtensions->f('id'),
				'root' => $this->path . '/' . $rsExtensions->f('id'),
				'name' => $rsExtensions->f('name'),
				'version' => $rsExtensions->f('version'),
				'desc' => $rsExtensions->f('description'),
				'author' => $rsExtensions->f('author'),
				'status' => $rsExtensions->f('status')
			);
		}

		return $this->okt['cacheConfig']->save($this->sCacheId, $aLoaded);
	}

	/**
	 * Sort an array of extensions alphabetically.
	 *
	 * @param array $aExtensions
	 * @return void
	 */
	public static function sort(array &$aExtensions)
	{
		uasort($aExtensions, function ($a, $b)
		{
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
		return (new Repositories($this->okt, $this->sCacheRepositoryId))->getData($aRepositories);
	}

	/**
	 * Return manager instance.
	 *
	 * @return \Okatea\Tao\Extensions\Manager
	 */
	public function getManager()
	{
		return new Manager($this->okt, $this->type, $this->path);
	}

	public function installPackage($zip_file)
	{
		return $this->getManager()->installPackage($zip_file, $this);
	}

	/**
	 * Return installer instance for a given extension.
	 *
	 * @param string $sExtensionId
	 * @return string
	 */
	public function getInstaller($sExtensionId)
	{
		$sInstallerClass = $this->getInstallerClass($sExtensionId);

		return new $sInstallerClass($this->okt, $this->path, $sExtensionId);
	}

	/**
	 * Looking for an install class of a given extension.
	 *
	 * @param string $sExtensionId
	 * @return string
	 */
	public function getInstallerClass($sExtensionId)
	{
		$sInstallerClass = 'Okatea\\Modules\\' . $sExtensionId . '\\Install\\Installer';

		if (class_exists($sInstallerClass) && is_subclass_of($sInstallerClass, $this->sInstallerClass))
		{
			return $sInstallerClass;
		}

		return $this->sInstallerClass;
	}
}
