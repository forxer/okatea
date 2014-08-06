<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Config;

use Okatea\Admin\Controller;
use Okatea\Tao\Update as Updater;
use Okatea\Tao\Html\Checklister;
use Okatea\Tao\Html\Escaper;
use Symfony\Component\Finder\Finder;

class Update extends Controller
{

	public function page()
	{
		if (! $this->okt['visitor']->checkPerm('is_superadmin'))
		{
			return $this->serve401();
		}

		# locales
		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/update');

		# mise à jour de la base de données
		if ($this->okt['request']->query->has('update_db'))
		{
			$oChecklist = new Checklister();

			Updater::dbUpdate($oChecklist);

			# log admin
			$this->okt['logAdmin']->warning(array(
				'code' => 21,
				'message' => 'DB CORE'
			));
		}

		$bDigestIsReadable = is_readable($this->okt['digests_path']);

		if (! $bDigestIsReadable && ! $this->okt['request']->query->has('update_db'))
		{
			$this->okt['flashMessages']->error(__('c_a_update_unable_read_digests'));
		}

		$sOkateaVersion = $this->okt->getVersion();

		$updater = new Updater($this->okt['config']->updates['url'], 'okatea', $this->okt['config']->updates['type'], $this->okt['cache_path'] . '/versions');
		$new_v = $updater->check($sOkateaVersion);
		$zip_file = $new_v ? $this->okt['app_path'] . '/' . basename($updater->getFileURL()) : '';

		# Hide "update me" message
		if ($this->okt['request']->query->has('hide_msg'))
		{
			$updater->setNotify(false);

			return $this->redirect($this->generateUrl('home'));
		}

		$sBaseSelfUrl = $this->generateUrl('config_update') . '?do_not_check=' . ($this->okt['request']->query->has('do_not_check') ? '1' : '0');

		$sStep = $this->okt['request']->query->get('step', '');
		$sStep = in_array($sStep, array(
			'check',
			'download',
			'backup',
			'unzip',
			'done'
		)) ? $sStep : '';

		# find backup archives files
		$finder = (new Finder())->files()
			->in($this->okt['app_path'])
			->depth('== 0')
			->name('/^backup-([0-9A-Za-z\.-]+).zip$/');

		$aArchives = array();
		foreach ($finder as $file)
		{
			$aArchives[] = $file->getFilename();
		}

		# Revert or delete backup file
		$b_file = $this->okt['request']->request->get('backup_file');
		if ($b_file && in_array($b_file, $aArchives))
		{
			try
			{
				if ($this->okt['request']->request->has('b_del'))
				{
					if (! @unlink($this->okt['app_path'] . '/' . $b_file))
					{
						throw new \Exception(sprintf(__('c_a_update_unable_delete_file_%s'), Escaper::html($b_file)));
					}

					return $this->redirect($sBaseSelfUrl);
				}

				if ($this->okt['request']->request->has('b_revert'))
				{
					$zip = new \fileUnzip($this->okt['app_path'] . '/' . $b_file);
					$zip->unzipAll($this->okt['app_path'] . '/');
					@unlink($this->okt['app_path'] . '/' . $b_file);

					return $this->redirect($sBaseSelfUrl);
				}
			}
			catch (\Exception $e)
			{
				$this->okt['flashMessages']->error($e->getMessage());
			}
		}

		# Upgrade process
		if ($bDigestIsReadable && $new_v && $sStep)
		{
			try
			{
				$updater->setForcedFiles($this->okt['digests_path']);

				# check integrity
				if (! $this->okt['request']->query->has('do_not_check'))
				{
					$updater->checkIntegrity($this->okt['digests_path'], $this->okt['app_path']);
				}

				# download
				$updater->download($zip_file);

				if (! $updater->checkDownload($zip_file))
				{
					throw new \Exception(sprintf(__('c_a_update_downloaded_archive_corrupted'), 'href="' . $sBaseSelfUrl . '&step=download"'));
				}

				# backup config site separatly
				copy($this->okt['config_path'] . '/conf_site.yaml', $this->okt['config_path'] . '/conf_site.yaml.bak');

				# backup old files
				$updater->backup($zip_file, 'okatea/Okatea/digests', $this->okt['app_path'], $this->okt['digests_path'], $this->okt['app_path'] . '/backup-' . $sOkateaVersion . '.zip');

				# upgrade
				$updater->performUpgrade($zip_file, 'okatea/Okatea/digests', 'okatea', $this->okt['app_path'], $this->okt['digests_path']);

				# log admin
				$this->okt['logAdmin']->critical(array(
					'code' => 21,
					'message' => 'FILES CORE ' . $new_v
				));

				return $this->redirect($this->okt['config']->app_url . 'install/?old_version=' . $sOkateaVersion);
			}
			catch (\Exception $e)
			{
				$sMessage = $e->getMessage();
				if ($e->getCode() == Updater::ERR_FILES_CHANGED)
				{
					$sMessage = __('c_a_update_following_files_modified');
				}
				elseif ($e->getCode() == Updater::ERR_FILES_UNREADABLE)
				{
					$sMessage = sprintf(__('c_a_update_following_files_not_readable'), '<strong>backup-' . $sOkateaVersion . '.zip</strong>');
				}
				elseif ($e->getCode() == Updater::ERR_FILES_UNWRITALBE)
				{
					$sMessage = __('c_a_update_following_files_cannot_be_written');
				}

				if (isset($e->bad_files))
				{
					$sMessage .= '<ul><li><strong>' . implode('</strong></li><li><strong>', $e->bad_files) . '</strong></li></ul>';
				}

				$this->okt['flashMessages']->error(__('c_a_update_error_occurred'));
			}
		}

		return $this->render('Config/Update', array(
			'oChecklist' => isset($oChecklist) ? $oChecklist : null,
			'bDigestIsReadable' => $bDigestIsReadable,
			'sStep' => $sStep,
			'sMessage' => isset($sMessage) ? $sMessage : null,
			'aArchives' => $aArchives,
			'sBaseSelfUrl' => $sBaseSelfUrl
		));
	}
}
