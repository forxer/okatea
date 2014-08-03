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
		
		$finder = (new Finder())->ignoreVCS(false)
			->ignoreDotFiles(false)
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
			->notPath('vendor');
		
		foreach ($finder as $file)
		{
			$this->sDigests .= md5_file($file->getRealpath()) . ' .' . $file->getRelativePathname() . "\n";
		}
		
		file_put_contents($this->getTempDir($this->okt['digests_path']), $this->sDigests);
	}
}
