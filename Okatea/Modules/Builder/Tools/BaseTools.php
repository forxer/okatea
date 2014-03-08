<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Builder\Tools;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class BaseTools
{
	/**
	 * Okatea application instance.
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	public function __construct($okt)
	{
		$this->okt = $okt;

		$this->sTempDir = $this->okt->options->root_dir.'/_tmp';
		$this->sPackageDir = $this->okt->options->root_dir.'/packages';
	}

	public function getTempDir($sDirPath = null)
	{
		if (null === $sDirPath) {
			return $this->sTempDir;
		}

		return str_replace($this->okt->options->get('root_dir'), $this->sTempDir, $sDirPath);
	}

	public function getCopier()
	{
		return new Copier($this->okt);
	}

	public function getCleaner()
	{
		return new Cleaner($this->okt);
	}

	public function getModules()
	{
		return new Modules($this->okt);
	}

	public function themes()
	{
		$sPackagesDir = $this->sPackageDir.'/themes';

		$fs = new Filesystem();

		$fs->remove($sPackagesDir);
		$fs->mkdir($sPackagesDir);

		$finder = (new Finder())
			->directories()
			->in($this->getTempDir($this->okt->options->themes_dir))
			->depth('== 0')
		;

		foreach ($finder as $theme)
		{
			if (!file_exists($theme->getRealpath().'/_define.php')) {
				continue;
			}

			$sThemeId = $theme->getFilename();

			$bInRepository = in_array($sThemeId, $this->okt->module('Builder')->config->themes['repository']);
			$bInPackage = in_array($sThemeId, $this->okt->module('Builder')->config->themes['package']);

			if (!$bInRepository && !$bInPackage)
			{
				$fs->remove($theme->getRealpath());
				continue;
			}
			elseif ($bInRepository)
			{
				if ($bInPackage) {
					$fs->mirror($theme->getRealpath(), $sPackagesDir.'/'.$sThemeId);
				}
				else {
					$fs->rename($theme->getRealpath(), $sPackagesDir.'/'.$sThemeId);
				}
			}
		}
	}
}
