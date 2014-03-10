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

class Modules extends BaseTools
{
	protected $sPackagesDir;

	protected $aRepositoryInfos;

	public function __construct($okt)
	{
		parent::__construct($okt);

		$this->sPackagesDir = $this->sPackageDir.'/modules'.$this->okt->getVersion();

		$this->aRepositoryInfos = array();
	}

	public function process()
	{
		$fs = new Filesystem();

		$fs->remove($this->sPackagesDir);
		$fs->mkdir($this->sPackagesDir);

		$finder = (new Finder())
			->directories()
			->in($this->getTempDir($this->okt->options->modules_dir))
			->depth('== 0')
		;

		foreach ($finder as $module)
		{
			if (!file_exists($module->getRealpath().'/_define.php')) {
				continue;
			}

			$sModuleId = $module->getFilename();

			$bInRepository = in_array($sModuleId, $this->okt->module('Builder')->config->modules['repository']);
			$bInPackage = in_array($sModuleId, $this->okt->module('Builder')->config->modules['package']);

			if (!$bInRepository && !$bInPackage)
			{
				$fs->remove($module->getRealpath());
				continue;
			}
			elseif ($bInRepository)
			{
				if ($bInPackage) {
					$fs->mirror($module->getRealpath(), $this->sPackagesDir.'/'.$sModuleId);
				}
				else {
					$fs->rename($module->getRealpath(), $this->sPackagesDir.'/'.$sModuleId);
				}

				$aModuleInfos = require $this->sPackagesDir.'/'.$sModuleId.'/_define.php';

				$this->aRepositoryInfos[$sThemeId] = array_merge(
					array(
						'id' => $sModuleId,
						'url' => ''
					),
					$aModuleInfos
				);
			}
		}

		file_put_contents(
			$this->sPackagesDir.'/modules.json',
			json_encode($this->aRepositoryInfos, JSON_PRETTY_PRINT)
		);
	}
}
