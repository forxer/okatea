<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin\Controller\Config;

use Tao\Admin\Controller;
use Tao\Core\Update as Updater;
use Tao\Html\CheckList;
use Tao\Misc\Utilities;

class Update extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('is_superadmin')) {
			return $this->serve401();
		}

		# locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$this->okt->user->language.'/admin.update');

		# mise à jour de la base de données
		if ($this->request->query->has('update_db'))
		{
			$oChecklist = new CheckList();

			Updater::dbUpdate($oChecklist);

			# log admin
			$this->okt->logAdmin->warning(array(
				'code' => 21,
				'message' => 'DB CORE'
			));
		}

		$bDigestIsReadable = is_readable($this->okt->options->get('digests'));

		if (!$bDigestIsReadable && !$this->request->query->has('update_db')) {
			$this->okt->error->set(__('c_a_update_unable_read_digests'));
		}

		$sOkateaVersion = Utilities::getVersion();

		$updater = new Updater(
			$this->okt->config->update_url,
			'okatea',
			$this->okt->config->update_type,
			$this->okt->options->get('cache_dir').'/versions'
		);
		$new_v = $updater->check($sOkateaVersion);
		$zip_file = $new_v ? $this->okt->options->getRootPath().'/'.basename($updater->getFileURL()) : '';

		# Hide "update me" message
		if ($this->request->query->has('hide_msg'))
		{
			$updater->setNotify(false);

			return $this->redirect($this->generateUrl('home'));
		}

		$sBaseSelfUrl = $this->generateUrl('config_update').'?do_not_check='.($this->request->query->has('do_not_check') ? '1' : '0');

		$sStep = $this->request->query->get('step', '');
		$sStep = in_array($sStep, array('check', 'download', 'backup', 'unzip', 'done')) ? $sStep : '';

		$aArchives = array();

		foreach (\files::scanDir($this->okt->options->getRootPath()) as $v)
		{
			if (preg_match('/backup-([0-9A-Za-z\.-]+).zip/',$v)) {
				$aArchives[] = $v;
			}
		}

		# Revert or delete backup file
		$b_file = $this->request->request->get('backup_file');
		if ($b_file && in_array($b_file, $aArchives))
		{
			try
			{
				if ($this->request->request->has('b_del'))
				{
					if (!@unlink($this->okt->options->getRootPath().'/'.$b_file)) {
						throw new Exception(sprintf(__('c_a_update_unable_delete_file_%s'),html::escapeHTML($b_file)));
					}

					return $this->redirect($sBaseSelfUrl);
				}

				if ($this->request->request->has('b_revert'))
				{
					$zip = new fileUnzip($this->okt->options->getRootPath().'/'.$b_file);
					$zip->unzipAll($this->okt->options->getRootPath().'/');
					@unlink($this->okt->options->getRootPath().'/'.$b_file);

					return $this->redirect($sBaseSelfUrl);
				}
			}
			catch (Exception $e)
			{
				$this->okt->error->set($e->getMessage());
			}
		}

		# Upgrade process
		if ($bDigestIsReadable && $new_v && $sStep)
		{
			try
			{
				$updater->setForcedFiles('oktInc/digests');

				# check integrity
				if (!$this->request->query->has('do_not_check')) {
					$updater->checkIntegrity($this->okt->options->getRootPath().'/oktInc/digests', $this->okt->options->getRootPath());
				}

				# download
				$updater->download($zip_file);

				if (!$updater->checkDownload($zip_file))
				{
					throw new Exception(
						sprintf(__('c_a_update_downloaded_archive_corrupted'), 'href="'.$sBaseSelfUrl.'&step=download"')
					);
				}

				# backup config site separatly
				copy($this->okt->options->config_dir.'/conf_site.yaml', $this->okt->options->config_dir.'/conf_site.yaml.bak');

				# backup old files
				$updater->backup(
					$zip_file,
					'okatea/oktInc/digests',
					$this->okt->options->getRootPath(),
					$this->okt->options->getRootPath().'/oktInc/digests',
					$this->okt->options->getRootPath().'/backup-'.$sOkateaVersion.'.zip'
				);

				# upgrade
				$updater->performUpgrade(
					$zip_file,
					'okatea/oktInc/digests',
					'okatea',
					$this->okt->options->getRootPath(),
					$this->okt->options->getRootPath().'/oktInc/digests'
				);

				# Merge config
				//$this->okt->config->merge();

				# Vidange du cache
				//Utilities::deleteOktCacheFiles();

				# log admin
				$this->okt->logAdmin->critical(array(
					'code' => 21,
					'message' => 'FILES CORE '.$new_v
				));

				return $this->redirect($this->okt->config->app_path.'install/?old_version='.$sOkateaVersion);
			}
			catch (Exception $e)
			{
				$sMessage = $e->getMessage();
				if ($e->getCode() == Updater::ERR_FILES_CHANGED)
				{
					$sMessage = __('c_a_update_following_files_modified');
				}
				elseif ($e->getCode() == Updater::ERR_FILES_UNREADABLE)
				{
					$sMessage = sprintf(__('c_a_update_following_files_not_readable'), '<strong>backup-'.$sOkateaVersion.'.zip</strong>');
				}
				elseif ($e->getCode() == Updater::ERR_FILES_UNWRITALBE)
				{
					$sMessage = __('c_a_update_following_files_cannot_be_written');
				}

				if (isset($e->bad_files))
				{
					$sMessage .= '<ul><li><strong>'.implode('</strong></li><li><strong>',$e->bad_files).'</strong></li></ul>';
				}

				$this->okt->error->set(__('c_a_update_error_occurred'));
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