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

		$fs->mirror($this->okt->options->root_dir.'/admin', 			$this->getTempDir().'/admin');
		$fs->mirror($this->okt->options->root_dir.'/install', 			$this->getTempDir().'/install');
		$fs->mirror($this->okt->options->root_dir.'/Okatea', 			$this->getTempDir().'/Okatea');
		$fs->mirror($this->okt->options->root_dir.'/oktPublic', 		$this->getTempDir().'/oktPublic');
		$fs->mirror($this->okt->options->root_dir.'/vendor', 			$this->getTempDir().'/vendor');

		$fs->copy($this->okt->options->root_dir.'/.htaccess.oktDist', 	$this->getTempDir().'/.htaccess.oktDist');
		$fs->copy($this->okt->options->root_dir.'/LICENSE', 			$this->getTempDir().'/LICENSE');
		$fs->copy($this->okt->options->root_dir.'/okatea.php', 			$this->getTempDir().'/okatea.php');
		$fs->copy($this->okt->options->root_dir.'/oktOptions.php', 		$this->getTempDir().'/oktOptions.php');
	}
}
