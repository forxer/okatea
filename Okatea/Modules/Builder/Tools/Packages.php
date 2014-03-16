<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Builder\Tools;

use Forxer\Archiver\Archiver;
use Okatea\Tao\HttpClient;
use Symfony\Component\Filesystem\Filesystem;

class Packages extends BaseTools
{
	public function __construct($okt)
	{
		parent::__construct($okt);

		$this->sPackagesDir = $this->sPackageDir.'/packages';

		$this->sRepositoryUrl = $this->okt->module('Builder')->config->repository_url.'/packages';

		$this->sPackageFilename = $this->okt->options->software_name.'_'.$this->okt->getVersion().'.zip';
	}

	public function process()
	{
		$fs = new Filesystem();

		$fs->remove($this->sPackagesDir);
		$fs->mkdir($this->sPackagesDir);

		$archiver = (new Archiver)
			->make($this->sPackagesDir.'/'.$this->sPackageFilename)
			->add($this->sTempDir)
			->close()
		;

		$aRepositoryInfos = array();

		try
		{
			$response = (new Client())->get($this->sRepositoryUrl.'/index.json', ['exceptions' => false]);

			if (200 == $response->getStatusCode()) {
				$aRepositoryInfos = $response->json();
			}
		}
		catch (Exception $e) {
		}

		$sReleaseType = $this->okt->session->get('release_type');

		$aRepositoryInfos[$sReleaseType] = array(
			'type' 		=> $sReleaseType,
			'version' 	=> $this->okt->getVersion(),
			'href' 		=> $this->sRepositoryUrl.'/'.$this->sPackageFilename,
			'checksum' 	=> md5_file($this->sPackagesDir.'/'.$this->sPackageFilename),
			'info' 		=> date('Y-m-d')
		);

		file_put_contents($this->sPackagesDir.'/index.json', json_encode($aRepositoryInfos, JSON_PRETTY_PRINT));
	}
}
