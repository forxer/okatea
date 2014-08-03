<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Builder\Tools;

use Symfony\Component\Filesystem\Filesystem;

class Copier extends BaseTools
{

	public function __construct($okt)
	{
		parent::__construct($okt);
	}

	public function process()
	{
		$fs = new Filesystem();
		
		$fs->remove($this->getTempDir());
		$fs->mkdir($this->getTempDir());
		
		$fs->mirror($this->okt['app_path'] . '/admin', $this->getTempDir() . '/admin');
		$fs->mirror($this->okt['app_path'] . '/install', $this->getTempDir() . '/install');
		$fs->mirror($this->okt['app_path'] . '/Okatea', $this->getTempDir() . '/Okatea');
		$fs->mirror($this->okt['app_path'] . '/oktPublic', $this->getTempDir() . '/oktPublic');
		$fs->mirror($this->okt['app_path'] . '/vendor', $this->getTempDir() . '/vendor');
		
		$fs->copy($this->okt['app_path'] . '/.htaccess.oktDist', $this->getTempDir() . '/.htaccess.oktDist');
		$fs->copy($this->okt['app_path'] . '/LICENSE', $this->getTempDir() . '/LICENSE');
		$fs->copy($this->okt['app_path'] . '/okatea.php', $this->getTempDir() . '/okatea.php');
		$fs->copy($this->okt['app_path'] . '/oktOptions.php', $this->getTempDir() . '/oktOptions.php');
	}
}
