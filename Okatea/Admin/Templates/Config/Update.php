<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

# infos page
$okt->page->addGlobalTitle(__('c_a_update_okatea_update'));

$okt->page->loader('.lazy-load');

if (!empty($sMessage))
{
	echo '<div class="errors_box ui-corner-all">' . $sMessage . '</div>';
}
elseif (!$okt['request']->query->has('update_db'))
{
	if (!$bDigestIsReadable)
	{
		echo '<p><span class="icon error"></span>' . __('c_a_update_digest_file_not_readable') . '</p>';
	}
	
	echo '<p><a href="' . $sBaseSelfUrl . '&amp;update_db=1" class="icon database_refresh">' . __('c_a_update_database') . '</a></p>';
}

if ($okt['request']->query->has('update_db'))
{
	echo $oChecklist->getHTML();
	
	if ($oChecklist->checkAll())
	{
		echo '<p>' . __('c_a_update_database_successful') . ' ' . '<a href="' . $sBaseSelfUrl . '">' . __('c_a_update_complete_update') . '</a></p>';
	}
	else
	{
		echo '<p><span class="icon error"></span> ' . __('c_a_update_database_blocking_errors_occurred') . '</p>';
	}
}
elseif ($bDigestIsReadable && !$sStep)
{
	if (empty($new_v))
	{
		echo '<p><strong>' . __('c_a_update_no_newer_version_available') . '</strong></p>';
	}
	else
	{
		echo '<p class="static-msg">' . sprintf(__('c_a_update_okatea_%s_available'), $new_v) . '</p>' . 

		'<p>' . __('c_a_update_to_upgrade_instructions') . '</p>' . '<form action="configuration.php" method="get">' . '<p><label for="do_not_check">' . form::checkbox('do_not_check', 1, false) . __('c_a_update_do_not_check_file_integrity') . '</label></p>' . '<p><input type="hidden" name="step" value="check" />' . '<input type="hidden" name="action" value="update" />' . '<input type="submit" class="lazy-load" value="' . __('c_a_update_action') . '" /></p>' . '</form>';
	}
	
	if (!empty($aArchives))
	{
		echo '<h3>' . __('c_a_update_backup_files') . '</h3>' . '<p>' . __('c_a_update_backup_instructions') . '</p>';
		
		echo '<form action="' . $view->generateAdminUrl('config_update') . '" method="post">';
		
		foreach ($aArchives as $v)
		{
			echo '<p><label class="classic">' . form::radio(array(
				'backup_file'
			), $view->escape($v)) . ' ' . $view->escape($v) . '</label></p>';
		}
		
		echo '<p><strong>' . __('c_a_update_backup_warning') . '</strong> ' . sprintf(__('c_a_update_should_not_revert_prior_%s'), end($aArchives)) . '</p>' . '<p><input type="submit" name="b_del" value="' . __('c_a_update_delete_selected_file') . '" /> ' . '<input type="submit" name="b_revert" class="lazy-load" value="' . __('c_a_update_revert_selected_file') . '" />' . $okt->page->formtoken() . '</p>' . '</form>';
	}
}
elseif ($sStep == 'done' && !$okt->error->hasError())
{
	echo '<p class="message">' . __('c_a_update_congratulations') . ' <strong><a href="' . $sBaseSelfUrl . '">' . __('c_a_update_finish') . '</a></strong>' . '</p>';
}
