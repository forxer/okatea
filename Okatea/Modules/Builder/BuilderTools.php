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

	}

	public function copy()
	{
		$fs = new Filesystem();

		$sTempDir = $this->okt->options->root_dir.'/_tmp';

		$fs->mkdir($sTempDir);

		$fs->mirror($this->okt->options->root_dir.'/admin', $sTempDir.'/admin');
		$fs->mirror($this->okt->options->root_dir.'/install', $sTempDir.'/install');
		$fs->mirror($this->okt->options->root_dir.'/Okatea', $sTempDir.'/Okatea');
		$fs->mirror($this->okt->options->root_dir.'/oktPublic', $sTempDir.'/oktPublic');
		$fs->mirror($this->okt->options->root_dir.'/vendor', $sTempDir.'/vendor');

		$fs->copy($this->okt->options->root_dir.'/.htaccess.oktDist', $sTempDir.'/.htaccess.oktDist');
		$fs->copy($this->okt->options->root_dir.'/LICENSE', $sTempDir.'/LICENSE');
		$fs->copy($this->okt->options->root_dir.'/okatea.php', $sTempDir.'/okatea.php');
		$fs->copy($this->okt->options->root_dir.'/oktOptions.php', $sTempDir.'/oktOptions.php');

		# clean up vendor dir
		$sVendorDir = $sTempDir.'/vendor';

		$aToDelete = array();

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
					->name($pattern);

				foreach ($finder as $files) {
					$aToDelete[] = $files->getRealpath();
				}
			}
		}

		foreach ($aToDelete as $file) {
			$fs->remove($aToDelete);
		}
	}
}
