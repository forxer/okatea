<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Extensions\Modules\Collection as ModulesCollection;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Toggle
$okt->page->toggleWithLegend('add_module_zip_title', 'add_module_zip_content', array('cookie'=>'oktAdminAddModuleZip'));
$okt->page->toggleWithLegend('add_module_repo_title', 'add_module_repo_content', array('cookie'=>'oktAdminAddModuleRepo'));

?>

<div id="tab-add">
	<h3><?php _e('c_a_modules_add_module') ?></h3>

	<h4 id="add_module_zip_title"><?php _e('c_a_modules_add_module_from_zip') ?></h4>

	<div id="add_module_zip_content" class="two-cols">
		<form class="col" action="<?php echo $view->generateUrl('config_modules') ?>" method="post">
			<fieldset>
				<legend><?php _e('c_a_modules_download_zip_file') ?></legend>
				<p class="field"><label for="pkg_url"><?php _e('c_a_modules_plugin_zip_file_url') ?></label>
				<?php echo form::text('pkg_url', 40, 255) ?></p>
			</fieldset>

			<p><?php echo form::hidden('fetch_pkg', 1) ?>
			<?php echo $okt->page->formtoken() ?>
			<input type="submit" class="lazy-load" value="<?php _e('c_a_modules_download_plugin') ?>" /></p>
		</form>
		<form class="col" action="<?php echo $view->generateUrl('config_modules') ?>" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend><?php _e('c_a_modules_upload_zip_file') ?></legend>
				<p class="field"><label for="pkg_file"><?php _e('c_a_modules_plugin_zip_file') ?></label>
				<?php echo form::file('pkg_file')?></p>
			</fieldset>

			<p><?php echo form::hidden('upload_pkg', 1) ?>
			<?php echo $okt->page->formtoken() ?>
			<input type="submit" class="lazy-load" value="<?php _e('c_a_modules_upload_plugin') ?>" /></p>
		</form>
	</div>

	<h4 id="add_module_repo_title"><?php _e('c_a_modules_add_module_from_remote_repository') ?></h4>

	<div id="add_module_repo_content">
	<?php if (!$okt->config->repositories['modules']['enabled']) : ?>
		<p><?php _e('c_a_modules_repositories_modules_disabled') ?></p>

	<?php elseif (!empty($aModulesRepositories)) : ?>
		<?php foreach($aModulesRepositories as $repo_name=>$modules) : ?>

		<h5><?php echo $view->escape($repo_name).' ('.ModulesCollection::pluralizeModuleCount(count($modules)).')'; ?></h5>

		<table class="common">
			<caption><?php printf('c_a_modules_list_modules_available_%s', $view->escape($repo_name)) ?></caption>
			<thead><tr>
				<th scope="col" class="left"><?php _e('c_c_Name') ?></th>
				<th scope="col" class="center"><?php _e('c_a_modules_version') ?></th>
				<th scope="col" class="small"><?php _e('c_c_action_Add') ?></th>
				<th scope="col" class="small"><?php _e('c_c_action_Download') ?></th>
			</tr></thead>
			<tbody>
			<?php $line_count = 0;
			foreach($modules as $module) :
				$td_class = $line_count%2 == 0 ? 'even' : 'odd';
				$line_count++; ?>
			<tr>
				<th scope="row" class="<?php echo $td_class; ?> fake-td">
				<?php echo $view->escape($module['name']) ?>
				<?php echo !empty($module['info']) ? '<br />'.$view->escape($module['info']) : ''; ?>
				</th>
				<td class="<?php echo $td_class; ?> center"><?php echo $view->escape($module['version']) ?></td>
				<td class="<?php echo $td_class; ?> center"><a href="<?php echo $view->generateUrl('config_modules') ?>?repository=<?php echo urlencode($repo_name) ?>&amp;module=<?php echo urlencode($module['id']) ?>" class="lazy-load"><?php _e('c_c_action_Add') ?></a></td>
				<td class="<?php echo $td_class; ?> center"><a href="<?php echo $module['href'] ?>"><?php _e('c_c_action_Download') ?></a></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endforeach; ?>
	<?php else : ?>
		<p><?php _e('c_a_modules_no_repository_modules_defined') ?></p>
	<?php endif; ?>
	</div>

</div><!-- #tab-add -->
