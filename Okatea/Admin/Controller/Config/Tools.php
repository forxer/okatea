<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Config;

use ArrayObject;
use DirectoryIterator;
use Okatea\Admin\Controller;
use Okatea\Tao\Misc\Utilities;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

class Tools extends Controller
{
	protected $aPageData;

	protected $oCacheFiles;

	protected $oPublicCacheFiles;

	protected $aCleanableFiles;

	protected $sBackupFilenameBase;

	protected $sDbBackupFilenameBase;

	protected $aBackupFiles;

	protected $aDbBackupFiles;

	protected $bHtaccessExists;

	protected $bHtaccessDistExists;

	protected $sHtaccessContent;

	protected $bCanUninstall;

	public function page()
	{
		if (! $this->okt->checkPerm('tools'))
		{
			return $this->serve401();
		}

		# locales
		$this->okt->l10n->loadFile($this->okt['locales_dir'] . '/%s/admin/tools');

		# Données de la page
		$this->aPageData = new ArrayObject();

		$this->cacheInit();

		$this->cleanupInit();

		$this->backupInit();

		$this->htaccessInit();

		$this->uninstallInit();

		# -- TRIGGER CORE TOOLS PAGE : adminToolsInit
		$this->okt['triggers']->callTrigger('adminToolsInit', $this->aPageData);

		if (($action = $this->cacheHandleRequest()) !== false)
		{
			return $action;
		}

		if (($action = $this->cleanupHandleRequest()) !== false)
		{
			return $action;
		}

		if (($action = $this->backupHandleRequest()) !== false)
		{
			return $action;
		}

		if (($action = $this->htaccessHandleRequest()) !== false)
		{
			return $action;
		}

		if (($action = $this->uninstallHandleRequest()) !== false)
		{
			return $action;
		}

		# -- TRIGGER CORE TOOLS PAGE : adminToolsHandleRequest
		$this->okt['triggers']->callTrigger('adminToolsHandleRequest', $this->aPageData);

		# start building tabs
		$this->aPageData['tabs'] = new ArrayObject();

		# cache tab
		$this->aPageData['tabs'][10] = array(
			'id' => 'tab-cache',
			'title' => __('c_a_tools_cache'),
			'content' => $this->renderView('Config/Tools/Tabs/Cache', array(
				'aPageData' => $this->aPageData,
				'oCacheFiles' => $this->oCacheFiles,
				'oPublicCacheFiles' => $this->oPublicCacheFiles
			))
		);

		# cleanup tab
		$this->aPageData['tabs'][20] = array(
			'id' => 'tab-cleanup',
			'title' => __('c_a_tools_cleanup'),
			'content' => $this->renderView('Config/Tools/Tabs/Cleanup', array(
				'aPageData' => $this->aPageData,
				'aCleanableFiles' => $this->aCleanableFiles
			))
		);

		# backup tab
		$this->aPageData['tabs'][30] = array(
			'id' => 'tab-backup',
			'title' => __('c_a_tools_backup'),
			'content' => $this->renderView('Config/Tools/Tabs/Backup', array(
				'aPageData' => $this->aPageData,
				'aBackupFiles' => $this->aBackupFiles,
				'aDbBackupFiles' => $this->aDbBackupFiles
			))
		);

		# htaccess tab
		$this->aPageData['tabs'][40] = array(
			'id' => 'tab-htaccess',
			'title' => __('c_a_tools_htaccess'),
			'content' => $this->renderView('Config/Tools/Tabs/Htaccess', array(
				'aPageData' => $this->aPageData,
				'bHtaccessExists' => $this->bHtaccessExists,
				'bHtaccessDistExists' => $this->bHtaccessDistExists,
				'sHtaccessContent' => $this->sHtaccessContent
			))
		);

		# uninstall tab
		if ($this->bCanUninstall)
		{
			$this->aPageData['tabs'][50] = array(
				'id' => 'tab-uninstall',
				'title' => __('c_a_tools_uninstall'),
				'content' => $this->renderView('Config/Tools/Tabs/Uninstall', array(
					'aPageData' => $this->aPageData,
					'bCanUninstall' => $this->bCanUninstall
				))
			);
		}

		# -- TRIGGER CORE TOOLS PAGE : adminToolsBuildTabs
		$this->okt['triggers']->callTrigger('adminToolsBuildTabs', $this->aPageData);

		$this->aPageData['tabs']->ksort();

		return $this->render('Config/Tools/Page', array(
			'aPageData' => $this->aPageData
		));
	}

	protected function cacheInit()
	{
		# liste des fichiers cache
		$this->oCacheFiles = Utilities::getOktCacheFiles();

		# liste des fichiers cache public
		$this->oPublicCacheFiles = Utilities::getOktPublicCacheFiles();
	}

	protected function cleanupInit()
	{
		# liste des fichiers supprimables
		$this->aCleanableFiles = array(

			'.DS_Store',

			'Thumbs.db',
			'ehthumbs.db',
			'Desktop.ini',

			'*.tmp',
			'tmp',
			'bak',
			'*.bak',
			'*.swp',

			'.project',
			'.settings',
			'.metadata',
			'.loadpath',
			'.buildpath',

			'_notes',

			'.svn',
			'_svn',
			'CVS',
			'_darcs',
			'.arch-params',
			'.monotone',
			'.bzr',
			'.git',
			'.hg'
		);
	}

	protected function backupInit()
	{
		# base des nom de fichier de backup
		$this->sBackupFilenameBase = 'okatea-backup';
		$this->sDbBackupFilenameBase = 'db-backup';

		# liste des fichiers de backup
		$this->aBackupFiles = array();
		$this->aDbBackupFiles = array();

		foreach (new DirectoryIterator($this->okt['root_dir']) as $oFileInfo)
		{
			if ($oFileInfo->isDot() || ! $oFileInfo->isFile())
			{
				continue;
			}

			# files backups
			if (preg_match('#(^|/)' . preg_quote($this->sBackupFilenameBase, '#') . '(.*?).zip$#', $oFileInfo->getFilename()))
			{
				$this->aBackupFiles[] = $oFileInfo->getFilename();
			}
			# db backups
			elseif (preg_match('#(^|/)' . preg_quote($this->sDbBackupFilenameBase, '#') . '(.*?).sql$#', $oFileInfo->getFilename()))
			{
				$this->aDbBackupFiles[] = $oFileInfo->getFilename();
			}
		}

		natsort($this->aBackupFiles);
		natsort($this->aDbBackupFiles);
	}

	protected function htaccessInit()
	{
		$this->sHtaccessContent = '';

		$this->bHtaccessExists = false;
		if (file_exists($this->okt['root_dir'] . '/.htaccess'))
		{
			$this->bHtaccessExists = true;
			$this->sHtaccessContent = file_get_contents($this->okt['root_dir'] . '/.htaccess');
		}

		$this->bHtaccessDistExists = false;
		if (file_exists($this->okt['root_dir'] . '/.htaccess.oktDist'))
		{
			$this->bHtaccessDistExists = true;
		}
	}

	protected function uninstallInit()
	{
		$this->bCanUninstall = false;

		if ($this->okt['debug']
			&& $this->okt['env'] === 'dev'
			&& $this->okt->checkPerm('is_superadmin'))
		{
			$this->bCanUninstall = true;
		}
	}

	protected function cacheHandleRequest()
	{
		# Suppression d'un fichier cache
		$sCacheFile = $this->okt['request']->query->get('cache_file');
		if ($sCacheFile)
		{
			$fs = (new Filesystem())->remove($this->okt['cache_dir'] . '/' . $sCacheFile);

			$this->okt['flash']->success(__('c_a_tools_cache_confirm'));

			return $this->redirect($this->generateUrl('config_tools'));
		}

		# Suppression d'un fichier cache public
		$sPublicCacheFile = $this->okt['request']->query->get('public_cache_file');
		if ($sPublicCacheFile)
		{
			$fs = (new Filesystem())->remove($this->okt['public_dir'] . '/cache/' . $sPublicCacheFile);

			$this->okt['flash']->success(__('c_a_tools_cache_confirm'));

			return $this->redirect($this->generateUrl('config_tools'));
		}

		# Suppression des fichiers cache
		if ($this->okt['request']->query->has('all_cache_file'))
		{
			Utilities::deleteOktCacheFiles();

			Utilities::deleteOktPublicCacheFiles();

			$this->okt['flash']->success(__('c_a_tools_cache_confirms'));

			return $this->redirect($this->generateUrl('config_tools'));
		}

		return false;
	}

	protected function cleanupHandleRequest()
	{
		# suppression des fichiers
		$aNeedToDelete = $this->okt['request']->request->get('cleanup');
		if ($aNeedToDelete)
		{
			$aToDelete = array();

			foreach ($aNeedToDelete as $cleanup)
			{
				if (isset($this->aCleanableFiles[$cleanup]))
				{
					$aToDelete[] = $this->aCleanableFiles[$cleanup];
				}
			}

			if (! empty($aToDelete))
			{
				ini_set('memory_limit', - 1);
				set_time_limit(0);

				$finder = (new Finder())->in($this->okt['root_dir'])
					->exclude('/vendor')
					->ignoreVCS(false);

				foreach ($aToDelete as $sToDelete)
				{
					$finder->name($sToDelete);
				}

				$iNumFindedFiles = count($finder);

				if ($iNumFindedFiles > 0)
				{
					$fs = new Filesystem();
					$fs->remove($finder);
				}

				$this->okt['flash']->success(sprintf(__('c_a_tools_cleanup_%s_cleaned'), $iNumFindedFiles));

				return $this->redirect($this->generateUrl('config_tools'));
			}
		}

		return false;
	}

	protected function backupHandleRequest()
	{
		# création d'un fichier de backup
		if ($this->okt['request']->query->has('make_backup'))
		{
			$sFilename = $this->sBackupFilenameBase . '-' . date('Y-m-d-H-i') . '.zip';

			$fp = fopen($this->okt['root_dir'] . '/' . $sFilename, 'wb');
			if ($fp === false)
			{
				$this->okt['flash']->error(__('c_a_tools_backup_unable_write_file'));
			}

			try
			{
				//		@ini_set('memory_limit',-1);
				set_time_limit(0);

				$zip = new \fileZip($fp);

				//$zip->addExclusion('#(^|/).(.*?)_(m|s|sq|t).jpg$#');
				$zip->addExclusion('#(^|/)_notes$#');
				$zip->addExclusion('#(^|/)_old$#');
				$zip->addExclusion('#(^|/)_source$#');
				$zip->addExclusion('#(^|/)_sources$#');
				$zip->addExclusion('#(^|/).svn$#');
				$zip->addExclusion('#(^|/)oktCache$#');
				$zip->addExclusion('#(^|/)stats$#');
				$zip->addExclusion('#(^|/)' . preg_quote($this->sBackupFilenameBase, '#') . '(.*?).zip$#');

				$zip->addDirectory($this->okt['root_dir'], $this->sBackupFilenameBase, true);

				$zip->write();
				fclose($fp);
				$zip->close();

				$this->okt['flash']->success(__('c_a_tools_backup_done'));

				return $this->redirect($this->generateUrl('config_tools'));
			}
			catch (\Exception $e)
			{
				$this->okt['flash']->error($e->getMessage());
			}
		}

		# création d'un fichier de backup de la base de données
		if ($this->okt['request']->query->has('make_db_backup'))
		{
			$return = '';
			$tables = $this->okt->db->getTables();

			foreach ($tables as $table)
			{
				$return .= 'DROP TABLE IF EXISTS ' . $table . ';';

				$row2 = $this->okt->db->fetchRow($this->okt->db->query('SHOW CREATE TABLE ' . $table));
				$return .= "\n\n" . $row2[1] . ";\n\n";

				$result = $this->okt->db->query('SELECT * FROM ' . $table);
				$num_fields = $this->okt->db->numFields($result);

				for ($i = 0; $i < $num_fields; $i ++)
				{
					while ($row = $this->okt->db->fetchRow($result))
					{
						$return .= 'INSERT INTO ' . $table . ' VALUES(';

						for ($j = 0; $j < $num_fields; $j ++)
						{
							if (is_null($row[$j]))
							{
								$return .= 'NULL';
							}
							else
							{
								$row[$j] = addslashes($row[$j]);
								$row[$j] = str_replace("\n", "\\n", $row[$j]);
								$return .= '"' . $row[$j] . '"';
							}

							if ($j < ($num_fields - 1))
							{
								$return .= ', ';
							}
						}

						$return .= ");\n";
					}
				}

				$return .= "\n-- --------------------------------------------------------\n\n";
			}

			$sFilename = $this->sDbBackupFilenameBase . '-' . date('Y-m-d-H-i') . '.sql';

			# save the file
			$fp = fopen($this->okt['root_dir'] . '/' . $sFilename, 'wb');
			fwrite($fp, $return);
			fclose($fp);

			$this->okt['flash']->success(__('c_a_tools_backup_done'));

			return $this->redirect($this->generateUrl('config_tools'));
		}

		# suppression d'un fichier de backup
		$sBackupFileToDelete = $this->okt['request']->query->get('delete_backup_file');
		if ($sBackupFileToDelete && (in_array($sBackupFileToDelete, $this->aBackupFiles) || in_array($sBackupFileToDelete, $this->aDbBackupFiles)))
		{
			@unlink($this->okt['root_dir'] . '/' . $sBackupFileToDelete);

			$this->okt['flash']->success(__('c_a_tools_backup_deleted'));

			return $this->redirect($this->generateUrl('config_tools'));
		}

		# téléchargement d'un fichier de backup
		$sBackupFileToDownload = $this->okt['request']->query->get('dl_backup');
		if ($sBackupFileToDownload && (in_array($sBackupFileToDownload, $this->aBackupFiles) || in_array($sBackupFileToDownload, $this->aDbBackupFiles)))
		{
			Utilities::forceDownload($this->okt['root_dir'] . '/' . $sBackupFileToDownload);
			exit();
		}

		return false;
	}

	protected function htaccessHandleRequest()
	{
		# création du fichier .htaccess
		if ($this->okt['request']->query->has('create_htaccess'))
		{
			if ($this->bHtaccessExists)
			{
				$this->okt['flash']->error(__('c_a_tools_htaccess_allready_exists'));
			}
			elseif (! $this->bHtaccessDistExists)
			{
				$this->okt['flash']->error(__('c_a_tools_htaccess_template_not_exists'));
			}
			else
			{
				file_put_contents($this->okt['root_dir'] . '/.htaccess', file_get_contents($this->okt['root_dir'] . '/.htaccess.oktDist'));

				$this->okt['flash']->success(__('c_a_tools_htaccess_created'));

				return $this->redirect($this->generateUrl('config_tools'));
			}
		}

		# suppression du fichier .htaccess
		if ($this->okt['request']->query->has('delete_htaccess'))
		{
			@unlink($this->okt['root_dir'] . '/.htaccess');

			$this->okt['flash']->success(__('c_a_tools_htaccess_deleted'));

			return $this->redirect($this->generateUrl('config_tools'));
		}

		# modification du fichier .htaccess
		if ($this->okt['request']->request->has('htaccess_form_sent'))
		{
			file_put_contents($this->okt['root_dir'] . '/.htaccess', $this->okt['request']->request->get('p_htaccess_content'));

			$this->okt['flash']->success(__('c_a_tools_htaccess_edited'));

			return $this->redirect($this->generateUrl('config_tools'));
		}

		return false;
	}

	protected function uninstallHandleRequest()
	{
		if ($this->okt['request']->request->has('uninstall') && $this->bCanUninstall)
		{
			# uninstall modules
			foreach ($this->okt->modules->getManager()->getInstalled() as $aModuleInfos)
			{
				$this->okt->modules->getInstaller($aModuleInfos['id'])->doUninstall();
			}

			# uninstall themes
			foreach ($this->okt->themes->getManager()->getInstalled() as $aThemeInfos)
			{
				$this->okt->themes->getInstaller($aThemeInfos['id'])->doUninstall();
			}

			# delete all tables from db
			$rDbTables = $this->okt->db->query('SHOW TABLES LIKE \''.$this->okt->db->prefix.'%\'');

			while ($row = $rDbTables->fetch_row())
			{
				$this->okt->db->execute('DROP TABLE `' . $row[0] . '`');
			}

			# remove db connection file
			if (file_exists($this->okt['config_dir'] . '/connection.php'))
			{
				unlink($this->okt['config_dir'] . '/connection.php');
			}

			# clear all cache files
			Utilities::deleteOktCacheFiles(true);
			Utilities::deleteOktPublicCacheFiles(true);

			# destroy session data
			$this->okt['session']->clear();
			$this->okt['session']->invalidate();

			# prepare redirect to install screen response
			$response = $this->redirect($this->okt['config']->app_path.'install');

			# remove cookies
			foreach ($this->okt['request']->cookies->keys() as $cookie)
			{
				$response->headers->clearCookie($cookie, $this->okt['config']->app_path, $this->okt['request']->getHttpHost());
			}

			return $response;
		}

		return false;
	}
}
