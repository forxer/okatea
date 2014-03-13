<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Builder\Tools;

use Forxer\Archiver\Archiver;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Extensions extends BaseTools
{
	protected $aRepositoryInfos;

	protected $aConfig;

	public function __construct($okt)
	{
		parent::__construct($okt);

		$this->aRepositoryInfos = array();
	}

	public function process()
	{
		$fs = new Filesystem();

		$fs->remove($this->sPackagesDir);
		$fs->mkdir($this->sPackagesDir);

		$finder = (new Finder())
			->directories()
			->in($this->sTempDir)
			->depth('== 0')
		;

		foreach ($finder as $extension)
		{
			$sExtensionId = $extension->getFilename();
			$sExtensionPath = $extension->getRealpath();

			if (!file_exists($sExtensionPath.'/_define.php')) {
				$fs->remove($sExtensionPath);
				continue;
			}

			$bInRepository = in_array($sExtensionId, $this->aConfig['repository']);
			$bInPackage = in_array($sExtensionId, $this->aConfig['package']);

			if (!$bInRepository && !$bInPackage)
			{
				$fs->remove($sExtensionPath);
				continue;
			}
			elseif ($bInRepository)
			{
				$archiver = (new Archiver)
					->make($this->sPackagesDir.'/'.$sExtensionId.'.zip')
					->add($sExtensionPath)
					->close()
				;

				$aExtensionInfos = require $sExtensionPath.'/_define.php';

				$this->aRepositoryInfos[$sExtensionId] = array_merge(
					array(
						'id' => $sExtensionId,
						'url' => $this->aConfig['repository_url'].'/'.$this->okt->getVersion().'/'.$sExtensionId.'.zip'
					),
					$aExtensionInfos
				);

				if (!$bInPackage) {
					$fs->remove($sExtensionPath);
				}
			}
		}

		file_put_contents(
			$this->sPackagesDir.'/index.json',
			json_encode($this->aRepositoryInfos, JSON_PRETTY_PRINT)
		);
	}
}
