<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Builder;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class BuilderTools
{
	/**
	 * Okatea application instance.
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	protected $aVendorCleanupRules = array(
		'alchemy/zippy' => 'docs',
		'dotclear/clearbricks' => '.atoum*',
		'dunglas/php-socialshare' => 'examples spec',
		'doctrine/cache' => '',
		'doctrine/collections' => '',
		'erusev/parsedown' => '',
		'ezyang/htmlpurifier' => 'art benchmarks configdoc docs extras maintenance plugins smoketests',
		'forxer/gravatar' => '',
		'forxer/languages-list' => 'src',
		'guzzle/guzzle' => 'docs phing build.xml',
		'imagine/imagine' => 'docs',
		'ircmaxell/password-compat' => '',
		'jdorn/sql-formatter' => 'examples',
		'leafo/lessphp' => 'docs Makefile package.sh',
		'maximebf/debugbar' => 'demo docs',
		'mobiledetect/mobiledetectlib' => 'examples',
		'monolog/monolog' => 'doc',
		'nesbot/carbon' => 'history.md readme.md',
		'pimple/pimple' => '',
		'psr/log' => 'Test',
		'raveren/kint' => 'scripts',
		'simplepie/simplepie' => 'build compatibility_test demo db.sql',
		'swiftmailer/swiftmailer' => 'CHANGES build* doc docs notes test-suite create_pear_package.php package*',
		'symfony/config/Symfony/Component/Config' => 'Tests',
		'symfony/debug/Symfony/Component/Debug' => 'Tests',
		'symfony/event-dispatcher/Symfony/Component/EventDispatcher' => 'Tests',
		'symfony/filesystem/Symfony/Component/Filesystem' => 'Tests',
		'symfony/finder/Symfony/Component/Finder' => 'Tests',
		'symfony/http-foundation/Symfony/Component/HttpFoundation' => 'Tests',
		'symfony/http-kernel/Symfony/Component/HttpKernel' => 'CHANGELOG* README* Tests',
		'symfony/process/Symfony/Component/Process' => 'Tests',
		'symfony/routing/Symfony/Component/Routing' => 'Tests',
		'symfony/templating/Symfony/Component/Templating' => 'Tests',
		'symfony/yaml/Symfony/Component/Yaml' => 'Tests',
		'umpirsky/country-list' => ''
	);

	public function __construct($okt)
	{
		$this->okt = $okt;

		$this->sTempDir = $this->okt->options->root_dir.'/_tmp';
		$this->sPackageDir = $this->okt->options->root_dir.'/packages';
	}

	public function getTempDir($sDirPath = null)
	{
		if (null === $sDirPath) {
			return $this->sTempDir;
		}

		return str_replace($this->okt->options->get('root_dir'), $this->sTempDir, $sDirPath);
	}

	public function copy()
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

	public function cleanup()
	{
		$aToDelete = array();

		# search files to delete into cache dir
		$sCacheDir = $this->getTempDir($this->okt->options->cache_dir);
		$finder = (new Finder())
			->ignoreVCS(false)
			->ignoreDotFiles(false)
			->in($sCacheDir)
			->notName('.gitkeep')
		;

		foreach ($finder as $files) {
			$aToDelete[] = $files->getRealpath();
		}

		# search files to delete into config dir
		$sConfigDir = $this->getTempDir($this->okt->options->config_dir);
		$finder = (new Finder())
			->ignoreVCS(false)
			->ignoreDotFiles(false)
			->files()
			->in($sConfigDir)
			->notName('__okatea_core.yml')
			->notName('conf_site.yml')
			->notName('connexion.php.in')
		;

		foreach ($finder as $files) {
			$aToDelete[] = $files->getRealpath();
		}

		# search files to delete into logs dir
		$sLogsDir = $this->getTempDir($this->okt->options->logs_dir);
		$finder = (new Finder())
			->ignoreVCS(false)
			->ignoreDotFiles(false)
			->in($sLogsDir)
			->notName('.gitkeep')
		;

		foreach ($finder as $files) {
			$aToDelete[] = $files->getRealpath();
		}

		# search files to delete into oktPublic dir
		$sPublicDir = $this->getTempDir($this->okt->options->public_dir);
		$finder = (new Finder())
			->ignoreVCS(false)
			->ignoreDotFiles(false)
			->in($sPublicDir.'/cache')
			->in($sPublicDir.'/modules')
			->in($sPublicDir.'/themes')
			->notName('index.html')
		;

		foreach ($finder as $files) {
			$aToDelete[] = $files->getRealpath();
		}

		# search files to delete into vendor dir
		$sVendorDir = $this->getTempDir().'/vendor';

		$aCommonRules = array(
			'bin', '.svn', '.git', '.hg', '.gitattributes', '.gitignore',
			'.travis.yml', 'composer.json', 'composer.lock', '.bower.json', '.bowerrc',
			'tests', 'test', 'phpunit*',
			'README*', 'CHANGELOG*', 'CONTRIBUTING*'
		);

		foreach ($this->aVendorCleanupRules as $sPackageDir => $rule)
		{
			if (!file_exists($sVendorDir.'/'.$sPackageDir)) {
				continue;
			}

			$aPatterns = array_merge($aCommonRules, explode(' ', $rule));

			foreach ($aPatterns as $pattern)
			{
				$finder = (new Finder())
					->ignoreVCS(false)
					->ignoreDotFiles(false)
					->in($sVendorDir.'/'.$sPackageDir)
					->name($pattern)
				;

				foreach ($finder as $files) {
					$aToDelete[] = $files->getRealpath();
				}
			}
		}

		# process to deletion
		$fs = new Filesystem();
		foreach ($aToDelete as $file) {
			$fs->remove($aToDelete);
		}
	}

	public function modules()
	{
		$sPackagesDir = $this->sPackageDir.'/modules';

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
			$sModuleId = $module->getFilename();

			$bInRepository = in_array($sModuleId, $this->okt->module('Builder')->config->modules['repository']);
			$bInPackage = in_array($sModuleId, $this->okt->module('Builder')->config->modules['package']);

			if (!$bInRepository && !$bInPackage) {
				$fs->remove($module->getRealpath());
			}
			elseif ($bInRepository && !$bInPackage) {
				$fs->rename($module->getRealpath(), $sPackagesDir.'/'.$sModuleId);
			}
			elseif ($bInRepository && $bInPackage) {
				$fs->mirror($module->getRealpath(), $sPackagesDir.'/'.$sModuleId);
			}
		}
	}

	public function themes()
	{
		$sPackagesDir = $this->sPackageDir.'/themes';

		$fs = new Filesystem();

		$fs->remove($sPackagesDir);
		$fs->mkdir($sPackagesDir);

		$finder = (new Finder())
			->directories()
			->in($this->getTempDir($this->okt->options->themes_dir))
			->depth('== 0')
		;

		foreach ($finder as $theme)
		{
			$sThemeId = $theme->getFilename();

			$bInRepository = in_array($sThemeId, $this->okt->module('Builder')->config->themes['repository']);
			$bInPackage = in_array($sThemeId, $this->okt->module('Builder')->config->themes['package']);

			if (!$bInRepository && !$bInPackage) {
				$fs->remove($theme->getRealpath());
			}
			elseif ($bInRepository && !$bInPackage) {
				$fs->rename($theme->getRealpath(), $sPackagesDir.'/'.$sThemeId);
			}
			elseif ($bInRepository && $bInPackage) {
				$fs->mirror($theme->getRealpath(), $sPackagesDir.'/'.$sThemeId);
			}
		}
	}
}
