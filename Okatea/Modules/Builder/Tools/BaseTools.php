<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Builder\Tools;

use Symfony\Component\Filesystem\Filesystem;

class BaseTools
{

	/**
	 * Okatea application instance.
	 *
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	protected $sTempDir;

	protected $sPackageDir;

	public function __construct($okt)
	{
		$this->okt = $okt;
		
		$this->sTempDir = $this->okt->options->root_dir . '/_tmp';
		$this->sPackageDir = $this->okt->options->root_dir . '/packages';
	}

	public function getTempDir($sDirPath = null)
	{
		if (null === $sDirPath)
		{
			return $this->sTempDir;
		}
		
		return str_replace($this->okt->options->get('root_dir'), $this->sTempDir, $sDirPath);
	}

	public function removeTempDir()
	{
		$fs = new Filesystem();
		$fs->remove($this->sTempDir);
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

	public function getThemes()
	{
		return new Themes($this->okt);
	}

	public function getDigests()
	{
		return new Digests($this->okt);
	}

	public function getPackages()
	{
		return new Packages($this->okt);
	}
}
