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

class Themes extends BaseTools
{
	protected $sPackagesDir;

	protected $aRepositoryInfos;

	public function __construct($okt)
	{
		parent::__construct($okt);

		$this->sPackagesDir = $this->sPackageDir.'/themes/'.$this->okt->getVersion();

		$this->aRepositoryInfos = array();
	}

	public function process()
	{
		$fs = new Filesystem();

		$fs->remove($this->sPackagesDir);
		$fs->mkdir($this->sPackagesDir);

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
					$fs->mirror($theme->getRealpath(), $this->sPackagesDir.'/'.$sThemeId);
				}
				else {
					$fs->rename($theme->getRealpath(), $this->sPackagesDir.'/'.$sThemeId);
				}

				$aThemeInfos = require $this->sPackagesDir.'/'.$sThemeId.'/_define.php';

				$this->aRepositoryInfos[$sThemeId] = array_merge(
					array(
						'id' => $sThemeId,
						'url' => ''
					),
					$aThemeInfos
				);
			}
		}

		file_put_contents(
			$this->sPackagesDir.'/themes.json',
			json_encode($this->aRepositoryInfos, JSON_PRETTY_PRINT)
		);
	}
}
