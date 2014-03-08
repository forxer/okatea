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
	public function __construct($okt)
	{
		parent::__construct($okt);
	}

	public function process()
	{
		$sPackagesDir = $this->sPackageDir.'/modules';

		$aRepositoryInfos = array();

		$fs = new Filesystem();

		$fs->remove($sPackagesDir);
		$fs->mkdir($sPackagesDir);

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
					$fs->mirror($module->getRealpath(), $sPackagesDir.'/'.$sModuleId);
				}
				else {
					$fs->rename($module->getRealpath(), $sPackagesDir.'/'.$sModuleId);
				}

				$aRepositoryInfos[$sModuleId] = array(
					'id' => $sModuleId
				);
			}
		}

	}
}
