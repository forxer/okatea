<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page de mise à jour du système
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


# limit this please
ini_set('max_execution_time', 0);

# locales
l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.update');


/* Initialisations
----------------------------------------------------------*/

# mise à jour de la base de données
if (!empty($_GET['update_db']))
{
	$oChecklist = new checkList();

	Okatea\Core\Update::dbUpdate($oChecklist);

	# log admin
	$okt->logAdmin->warning(array(
		'code' => 21,
		'message' => 'DB CORE'
	));
}

if (!defined('OKT_BACKUP_PATH')) {
	define('OKT_BACKUP_PATH',OKT_ROOT_PATH);
}

$digest_is_readable = is_readable(OKT_DIGESTS);

if (!$digest_is_readable && empty($_GET['update_db'])) {
	$okt->error->set(__('c_a_update_unable_read_digests'));
}

$okatea_version = util::getVersion();

$updater = new Okatea\Core\Update($okt->config->update_url, 'okatea', $okt->config->update_type, OKT_CACHE_PATH.'/versions');
$new_v = $updater->check($okatea_version);
$zip_file = $new_v ? OKT_BACKUP_PATH.'/'.basename($updater->getFileURL()) : '';


/* Traitements
----------------------------------------------------------*/

# Hide "update me" message
if (!empty($_GET['hide_msg'])) {
	$updater->setNotify(false);
	http::redirect('index.php');
}

$p_url = 'configuration.php?action=update&do_not_check='.(!empty($_GET['do_not_check']) ? '1' : '0');

$step = isset($_GET['step']) ? $_GET['step'] : '';
$step = in_array($step,array('check', 'download', 'backup', 'unzip', 'done')) ? $step : '';

$archives = array();
foreach (files::scanDir(OKT_BACKUP_PATH) as $v)
{
	if (preg_match('/backup-([0-9A-Za-z\.-]+).zip/',$v)) {
		$archives[] = $v;
	}
}

# Revert or delete backup file
if (!empty($_POST['backup_file']) && in_array($_POST['backup_file'],$archives))
{
	$b_file = $_POST['backup_file'];

	try
	{
		if (!empty($_POST['b_del']))
		{
			if (!@unlink(OKT_BACKUP_PATH.'/'.$b_file)) {
				throw new Exception(sprintf(__('c_a_update_unable_delete_file_%s'),html::escapeHTML($b_file)));
			}

			http::redirect($p_url);
		}

		if (!empty($_POST['b_revert']))
		{
			$zip = new fileUnzip(OKT_BACKUP_PATH.'/'.$b_file);
			$zip->unzipAll(OKT_BACKUP_PATH.'/');
			@unlink(OKT_BACKUP_PATH.'/'.$b_file);

			http::redirect($p_url);
		}
	}
	catch (Exception $e)
	{
		$okt->error->set($e->getMessage());
	}
}

# Upgrade process
if ($digest_is_readable && $new_v && $step)
{
	try
	{
		$updater->setForcedFiles('oktInc/digests');

		# check integrity
		if (empty($_GET['do_not_check'])) {
			$updater->checkIntegrity(OKT_ROOT_PATH.'/oktInc/digests', OKT_ROOT_PATH);
		}

		# download
		$updater->download($zip_file);

		if (!$updater->checkDownload($zip_file))
		{
			throw new Exception(
				sprintf(__('c_a_update_downloaded_archive_corrupted'), 'href="'.$p_url.'&step=download"')
			);
		}

		# backup config site separatly
		copy(OKT_CONFIG_PATH.'/conf_site.yaml', OKT_CONFIG_PATH.'/conf_site.yaml.bak');

		# backup old files
		$updater->backup(
			$zip_file, 'okatea/oktInc/digests',
			OKT_ROOT_PATH, OKT_ROOT_PATH.'/oktInc/digests',
			OKT_BACKUP_PATH.'/backup-'.$okatea_version.'.zip'
		);

		# upgrade
		$updater->performUpgrade(
			$zip_file, 'okatea/oktInc/digests', 'okatea',
			OKT_ROOT_PATH, OKT_ROOT_PATH.'/oktInc/digests'
		);

		# update config for i18n
		if (version_compare($okatea_version, '1.0', '<'))
		{
			$aNewConf = array();

			if (!is_array($okt->config->title)) {
				$aNewConf['title'] = array('fr' => $okt->config->title);
			}

			if (!is_array($okt->config->desc)) {
				$aNewConf['desc'] = array('fr' => $okt->config->desc);
			}

			if (!is_array($okt->config->title_tag)) {
				$aNewConf['title_tag'] = array('fr' => $okt->config->title_tag);
			}

			if (!is_array($okt->config->meta_description)) {
				$aNewConf['meta_description'] = array('fr' => $okt->config->meta_description);
			}

			if (!is_array($okt->config->meta_keywords)) {
				$aNewConf['meta_keywords'] = array('fr' => $okt->config->meta_keywords);
			}

			if (!empty($aNewConf)) {
				$okt->config->write($aNewConf);
			}
		}

		# Merge config
		//$okt->config->merge();

		# Vidange du cache
		//util::deleteOktCacheFiles();

		# log admin
		$okt->logAdmin->critical(array(
			'code' => 21,
			'message' => 'FILES CORE '.$new_v
		));

		http::redirect($okt->config->app_path.'install/?old_version='.$okatea_version);
	}
	catch (Exception $e)
	{
		$msg = $e->getMessage();
		if ($e->getCode() == Okatea\Core\Update::ERR_FILES_CHANGED)
		{
			$msg = __('c_a_update_following_files_modified');
		}
		elseif ($e->getCode() == Okatea\Core\Update::ERR_FILES_UNREADABLE)
		{
			$msg = sprintf(__('c_a_update_following_files_not_readable'),
			'<strong>backup-'.$okatea_version.'.zip</strong>');
		}
		elseif ($e->getCode() == Okatea\Core\Update::ERR_FILES_UNWRITALBE)
		{
			$msg = __('c_a_update_following_files_cannot_be_written');
		}

		if (isset($e->bad_files))
		{
			$msg .= '<ul><li><strong>'.
			implode('</strong></li><li><strong>',$e->bad_files).
			'</strong></li></ul>';
		}

		$okt->error->set(__('c_a_update_error_occurred'));
	}
}


/* Affichage
----------------------------------------------------------*/

# infos page
$okt->page->addGlobalTitle(__('c_a_update_okatea_update'));


$okt->page->loader('.lazy-load');

# En-tête
require OKT_ADMIN_HEADER_FILE;

if (!empty($msg))
{
	echo '<div class="error_box ui-corner-all">'.$msg.'</div>';
}
elseif (empty($_GET['update_db']))
{
	if (!$digest_is_readable) {
		echo '<p><span class="icon error"></span>'.__('c_a_update_digest_file_not_readable').'</p>';
	}

	echo '<p><a href="'.$p_url.'&amp;update_db=1" class="icon database_refresh">'.
	__('c_a_update_database').'</a></p>';
}

if (!empty($_GET['update_db']))
{
	echo $oChecklist->getHTML();

	if ($oChecklist->checkAll())
	{
		echo '<p>'.__('c_a_update_database_successful').' '.
		'<a href="'.$p_url.'">'.__('c_a_update_complete_update').'</a></p>';
	}
	else  {
		echo '<p><span class="icon error"></span> '.
		__('c_a_update_database_blocking_errors_occurred').'</p>';
	}
}
elseif ($digest_is_readable && !$step)
{
	if (empty($new_v))
	{
		echo '<p><strong>'.__('c_a_update_no_newer_version_available').'</strong></p>';
	}
	else
	{
		echo
		'<p class="static-msg">'.sprintf(__('c_a_update_okatea_%s_available'),$new_v).'</p>'.

		'<p>'.__('c_a_update_to_upgrade_instructions').'</p>'.
		'<form action="configuration.php" method="get">'.
		'<p><label for="do_not_check">'.form::checkbox('do_not_check',1,false).__('c_a_update_do_not_check_file_integrity').'</label></p>'.
		'<p><input type="hidden" name="step" value="check" />'.
		'<input type="hidden" name="action" value="update" />'.
		'<input type="submit" class="lazy-load" value="'.__('c_a_update_action').'" /></p>'.
		'</form>';
	}

	if (!empty($archives))
	{
		echo
		'<h3>'.__('c_a_update_backup_files').'</h3>'.
		'<p>'.__('c_a_update_backup_instructions').'</p>';

		echo '<form action="configuration.php" method="post">';

		foreach ($archives as $v) {
			echo
			'<p><label class="classic">'.form::radio(array('backup_file'),html::escapeHTML($v)).' '.
			html::escapeHTML($v).'</label></p>';
		}

		echo
		'<p><strong>'.__('c_a_update_backup_warning').'</strong> '.
		sprintf(__('c_a_update_should_not_revert_prior_%s'),end($archives)).
		'</p>'.
		'<p><input type="submit" name="b_del" value="'.__('c_a_update_delete_selected_file').'" /> '.
		'<input type="submit" name="b_revert" class="lazy-load" value="'.__('c_a_update_revert_selected_file').'" />'.
		'<input type="hidden" name="action" value="update" />'.
		adminPage::formtoken().'</p>'.
		'</form>';
	}
}
elseif ($step == 'done' && !$okt->error->hasError())
{
	echo
	'<p class="message">'.__('c_a_update_congratulations').
	' <strong><a href="'.$p_url.'">'.__('c_a_update_finish').'</a></strong>'.
	'</p>';
}


# Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
