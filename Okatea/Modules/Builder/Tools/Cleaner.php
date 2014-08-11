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

class Cleaner extends BaseTools
{

	protected $aToRemove;

	protected $aCommonRules = array(
		'bin',
		'.svn',
		'.git',
		'.hg',
		'.gitattributes',
		'.gitignore',
		'.gitmodules',
		'.travis.yml',
		'composer.json',
		'composer.lock',
		'grunt.js',
		'Gruntfile.js',
		'bower.json',
		'.bower.json',
		'.bowerrc',
		'package.json',
		'*.jquery.json',
		'tests',
		'test',
		'phpunit*',
		'readme*',
		'README*',
		'changelog*',
		'CHANGELOG*',
		'UPGRADING*',
		'CONTRIBUTING*'
	);

	protected $aComponentsRules = array(
		'codemirror' => '',
		'ghostdown' => '*.html',
		'gmap3' => '',
		'html5shiv' => '',
		'jquery' => 'src',
		'jquery-color' => '.jshintrc',
		'jquery-cookie' => '',
		'jquery-cycle2' => 'src core',
		'jquery-mousewheel' => 'ChangeLog.md',
		'jquery-stringtoslug' => 'samples.html',
		'jquery-ui' => '',
		'jquery-validation' => 'build src',
		'lightbox2' => 'releases sass index.html',
		'normalize-css' => '',
		'passfield' => '.idea lib build.sh release-notes.md',
		'plupload' => 'examples',
		'roundabout' => '',
		'select2' => '',
		'spectrum' => 'index.html',
		'world-flags-sprite' => ''
	);

	protected $aVendorRules = array(
		'doctrine/cache' => '',
		'dunglas/php-socialshare' => 'examples spec',
		'erusev/parsedown' => '',
		'ezyang/htmlpurifier' => 'art benchmarks configdoc docs extras maintenance plugins smoketests',
		'forxer/archiver' => '',
		'forxer/gravatar' => '',
		'forxer/languages-list' => 'src',
		'fzaninotto/faker' => '',
		'guzzlehttp/guzzle' => 'docs',
		'guzzlehttp/streams' => '',
		'imagine/imagine' => 'docs',
		'ircmaxell/password-compat' => '',
		'jdorn/sql-formatter' => 'examples',
		//	'leafo/lessphp' => 'docs Makefile package.sh',
		//	'maximebf/debugbar' => 'demo docs',
		'mobiledetect/mobiledetectlib' => 'examples',
		'monolog/monolog' => 'doc',
		'nesbot/carbon' => 'history.md readme.md',
		'patchwork/utf8' => '',
		//	'pimple/pimple' => '',
		'psr/log' => 'Test',
		'raveren/kint' => 'scripts',
		'simplepie/simplepie' => 'build compatibility_test demo db.sql',
		'swiftmailer/swiftmailer' => 'CHANGES build* doc docs notes test-suite create_pear_package.php package*',
		'symfony/config/Symfony/Component/Config' => 'Tests',
		'symfony/debug/Symfony/Component/Debug' => 'Tests',
		'symfony/filesystem/Symfony/Component/Filesystem' => 'Tests',
		'symfony/finder/Symfony/Component/Finder' => 'Tests',
		'symfony/http-foundation/Symfony/Component/HttpFoundation' => 'Tests',
		'symfony/process/Symfony/Component/Process' => 'Tests',
		'symfony/routing/Symfony/Component/Routing' => 'Tests',
		'symfony/templating/Symfony/Component/Templating' => 'Tests',
		'symfony/yaml/Symfony/Component/Yaml' => 'Tests'
	);

	public function __construct($okt)
	{
		parent::__construct($okt);
	}

	public function process()
	{
		$this->setToRemove();
		$this->remove();
	}

	public function setToRemove()
	{
		$this->aToRemove = [];

		$this->cache(false);
		$this->config(false);
		$this->logs(false);
		$this->publics(false);
		$this->components(false);
		$this->vendor(false);
	}

	public function getToRemove()
	{
		$this->setToRemove();
		return $this->aToRemove;
	}

	public function cache($bProcess = true)
	{
		if ($bProcess)
		{
			$this->aToRemove = [];
		}

		$finder = (new Finder())->ignoreVCS(false)
			->ignoreDotFiles(false)
			->in($this->getTempDir($this->okt['cache_path']))
			->notName('.gitkeep');

		foreach ($finder as $files)
		{
			$this->aToRemove[] = $files->getRealpath();
		}

		if ($bProcess)
		{
			$this->remove();
		}
	}

	public function config($bProcess = true)
	{
		if ($bProcess)
		{
			$this->aToRemove = [];
		}

		$finder = (new Finder())->ignoreVCS(false)
			->ignoreDotFiles(false)
			->files()
			->in($this->getTempDir($this->okt['config_path']))
			->notName('__okatea_core.yml')
			->notName('conf_site.yml')
			->notName('connection.dist.php');

		foreach ($finder as $files)
		{
			$this->aToRemove[] = $files->getRealpath();
		}

		if ($bProcess)
		{
			$this->remove();
		}
	}

	public function logs($bProcess = true)
	{
		if ($bProcess)
		{
			$this->aToRemove = [];
		}

		$finder = (new Finder())->ignoreVCS(false)
			->ignoreDotFiles(false)
			->in($this->getTempDir($this->okt['logs_path']))
			->notName('.gitkeep');

		foreach ($finder as $files)
		{
			$this->aToRemove[] = $files->getRealpath();
		}

		if ($bProcess)
		{
			$this->remove();
		}
	}

	public function publics($bProcess = true)
	{
		if ($bProcess)
		{
			$this->aToRemove = [];
		}

		$sPublicDir = $this->getTempDir($this->okt['public_path']);
		$finder = (new Finder())->ignoreVCS(false)
			->ignoreDotFiles(false)
			->in($sPublicDir . '/cache')
			->in($sPublicDir . '/modules')
			->in($sPublicDir . '/themes')
			->notName('index.html');

		foreach ($finder as $files)
		{
			$this->aToRemove[] = $files->getRealpath();
		}

		if ($bProcess)
		{
			$this->remove();
		}
	}

	public function components($bProcess = true)
	{
		if ($bProcess)
		{
			$this->aToRemove = [];
		}

		$sComponentsDir = $this->getTempDir($this->okt['public_path']) . '/components';

		foreach ($this->aComponentsRules as $sPackageDir => $rule)
		{
			if (!file_exists($sComponentsDir . '/' . $sPackageDir))
			{
				continue;
			}

			$aPatterns = array_merge($this->aCommonRules, explode(' ', $rule));

			foreach ($aPatterns as $pattern)
			{
				$finder = (new Finder())->ignoreVCS(false)
					->ignoreDotFiles(false)
					->in($sComponentsDir . '/' . $sPackageDir)
					->name($pattern);

				foreach ($finder as $files)
				{
					$this->aToRemove[] = $files->getRealpath();
				}
			}
		}

		if ($bProcess)
		{
			$this->remove();
		}
	}

	public function vendor($bProcess = true)
	{
		if ($bProcess)
		{
			$this->aToRemove = [];
		}

		$sVendorDir = $this->getTempDir() . '/vendor';

		foreach ($this->aVendorRules as $sPackageDir => $rule)
		{
			if (!file_exists($sVendorDir . '/' . $sPackageDir))
			{
				continue;
			}

			$aPatterns = array_merge($this->aCommonRules, explode(' ', $rule));

			foreach ($aPatterns as $pattern)
			{
				$finder = (new Finder())->ignoreVCS(false)
					->ignoreDotFiles(false)
					->in($sVendorDir . '/' . $sPackageDir)
					->name($pattern);

				foreach ($finder as $files)
				{
					$this->aToRemove[] = $files->getRealpath();
				}
			}
		}

		if ($bProcess)
		{
			$this->remove();
		}
	}

	protected function remove()
	{
		$fs = new Filesystem();
		foreach ($this->aToRemove as $file)
		{
			$fs->remove($this->aToRemove);
		}
	}
}
