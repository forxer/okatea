<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Builder\Tools;

use Symfony\Component\Finder\Finder;

class Digests extends BaseTools
{
	protected $sDigests;

	public function __construct($okt)
	{
		parent::__construct($okt);
	}

	public function process()
	{
		$this->sDigests = '';

		$finder = (new Finder())
			->files()
			->in($this->sTempDir)
			->notName('.htaccess.oktDist')
			->notName('conf_site.yml')
			->notPath('install')
			->notName('digests')
			->notPath('Okatea/cache')
			->notPath('Okatea/Modules')
			->notPath('Okatea/Themes')
			->notPath('oktPublic')
			->notPath('vendor')
		;

		foreach ($finder as $file) {
			$this->sDigests .= md5_file($file->getRealpath()).' .'.$file->getRelativePathname()."\n";
		}

		/*
		$this->getDigestStr(
			$this->sTempDir,
			array('.htaccess.oktDist','conf_site.yml'),
			array('/install', '/Okatea/cache', '/Okatea/Modules', '/Okatea/Themes', '/oktPublic', 'vendor')
		);

		$this->getDigestStr($this->sTempDir.'/Okatea/Themes/DefaultTheme');
		*/

		file_put_contents($this->getTempDir($this->okt->options->digests), $this->sDigests);
	}

	protected function getDigestStr($dir, $aExcludeFiles = array(), $aExcludeDirs = array())
	{
		$D = dir($dir);

		$path = str_replace($this->sTempDir, '', $dir);

		while (($e = $D->read()) !== false)
		{
			if ($e == '.' || $e == '..'
				|| is_file($dir.'/'.$e) && in_array($e, $aExcludeFiles)
				|| is_dir($dir.'/'.$e) && in_array($path, $aExcludeDirs)) {
				continue;
			}

			if (is_dir($dir.'/'.$e)) {
				$this->getDigestStr($dir.'/'.$e, $aExcludeFiles, $aExcludeDirs);
			}
			elseif (is_file($dir.'/'.$e)) {
				$this->sDigests .= md5_file($dir.'/'.$e).' .'.$path.'/'.$e."\n";
			}
		}
	}

}
