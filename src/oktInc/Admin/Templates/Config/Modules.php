<?php

use Tao\Forms\Statics\FormElements as form;
use Tao\Modules\Collection as ModulesCollection;
use Tao\Misc\Utilities;

$view->extend('layout');

# Infos page par défaut
$okt->page->addGlobalTitle(__('Modules'));

$okt->page->dialog(array(), '.changelog_link');

# Display a UI dialog box for each module
foreach ($aInstalledModules as $aModule)
{
	if (file_exists($aModule['root'].'/CHANGELOG'))
	{
		$okt->page->openLinkInDialog('#'.$aModule['id'].'_changelog_link',array(
			'title' => $view->escapeJs($aModule['name_l10n']." CHANGELOG"),
			'width' => 730,
			'height' => 500
		));
	}
}

# Toggle
$okt->page->toggleWithLegend('add_module_zip_title','add_module_zip_content',array('cookie'=>'oktAdminAddModuleZip'));
$okt->page->toggleWithLegend('add_module_repo_title','add_module_repo_content',array('cookie'=>'oktAdminAddModuleRepo'));

# Tabs
$okt->page->tabs();

# Loader
$okt->page->loader('.lazy-load');

?>

<div id="tabered">
	<ul>
		<li><a href="#tab-installed"><span><?php _e('c_a_modules_installed_modules') ?></span></a></li>
		<li><a href="#tab-uninstalled"><span><?php _e('c_a_modules_uninstalled_modules') ?></span></a></li>
		<li><a href="#tab-add"><span><?php _e('c_a_modules_add_module') ?></span></a></li>
		<?php # des modules à mettre à jour ?
		if (!empty($aUpdatablesModules)) : ?>
		<li><a href="#tab-updates"><span><?php _e('c_a_modules_new_releases') ?></span></a></li>
		<?php endif; ?>
	</ul>

<div id="tab-installed">
	<h3><?php _e('c_a_modules_installed_modules') ?> (<?php echo ModulesCollection::pluralizeModuleCount(count($aInstalledModules)); ?>)</h3>

	<?php if (empty($aInstalledModules)) : ?>
		<p><?php _e('c_a_modules_no_modules_installed') ?></p>
	<?php else : ?>

	<table class="common">
		<caption><?php _e('c_a_modules_list_installed_modules') ?></caption>
		<thead><tr>
			<th scope="col" class="left" colspan="2"><?php _e('c_a_modules_name') ?></th>
			<th scope="col" class="center"><?php _e('c_a_modules_version') ?></th>
			<th scope="col"><?php _e('c_a_modules_data') ?></th>
			<th scope="col"><?php _e('c_a_modules_tools') ?></th>
			<th scope="col"><?php _e('c_a_modules_actions') ?></th>
		</tr></thead>
		<tbody>
		<?php
		$line_count = 0;

		foreach ($aInstalledModules as $aModule) :

			# odd/even
			$td_class = $line_count%2 == 0 ? 'even' : 'odd';
			$line_count++;

			# disabled ?
			if (!$aModule['status']) {
				$td_class .= ' disabled';
			}

			# title
			$module_title = '<h4 class="title">'.$aModule['name_l10n'].'</h4>';
			if ($aModule['status'] && $okt->adminRouter->routeExists($aModule['id'].'_index')) {
				$module_title = '<a href="'.$okt->adminRouter->generate($aModule['id'].'_index').'">'.$module_title.'</a>';
			}

			# links
			$module_links = array();
			if (file_exists($aModule['root'].'/CHANGELOG'))
			{
				$module_links[] = '<a href="'.$view->generateUrl('config_modules').'?show_changelog='.$aModule['id'].'"'.
				' id="'.$aModule['id'].'_changelog_link">'.__('c_a_modules_changelog').'</a>';
			}

			if ($okt->adminRouter->routeExists($aModule['id'].'_display')) {
				$module_links[] = '<a href="'.$okt->adminRouter->generate($aModule['id'].'_display').'">'.__('c_a_modules_display').'</a>';
			}
			if ($okt->adminRouter->routeExists($aModule['id'].'_config')) {
				$module_links[] = '<a href="'.$okt->adminRouter->generate($aModule['id'].'_config').'">'.__('c_a_modules_config').'</a>';
			}
		?>
		<tr>
			<td class="<?php echo $td_class ?> small">
				<p>
				<?php if ($aModule['status']): ?>
					<a href="module.php?m=<?php echo $aModule['id'] ?>">
				<?php endif; ?>
					<?php if (file_exists($okt->options->get('public_dir').'/modules/'.$aModule['id'].'/module_icon.png')) : ?>
					<img src="<?php echo $okt->options->public_url.'/modules/'.$aModule['id'] ?>/module_icon.png" width="32" height="32" alt="" />
					<?php else: ?>
					<img src="<?php echo $okt->options->public_url ?>/img/admin/module.png" width="32" height="32" alt="" />
					<?php endif; ?>
				<?php if ($aModule['status']): ?>
					</a>
				<?php endif; ?>
				</p>
			</td>
			<td class="<?php echo $td_class ?>">
				<h4 class="title"><?php echo $module_title ?></h4>
				<p><?php echo $aModule['desc_l10n'] ?></p>

				<?php if (!empty($module_links)) : ?>
				<p><?php echo implode(' - ',$module_links) ?></p>
				<?php endif; ?>
			</td>
			<td class="<?php echo $td_class ?> center">
				<p>
				<?php echo $aModule['version'] ?>
				<?php if (version_compare($aAllModules[$aModule['id']]['version'], $aModule['version'], '>')) : ?>
				<br /><a href="<?php echo $view->generateUrl('config_modules') ?>?update=<?php echo $aModule['id']; ?>"
				class="icon plugin_error">Mettre à jour à la version <?php echo $aAllModules[$aModule['id']]['version'] ?></a>
				<?php endif; ?>
				</p>
			</td>
			<td class="<?php echo $td_class ?> nowrap">
				<ul class="actions">
					<?php if (file_exists($aModule['root'].'/install/db-data.xml')) : ?>
					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?defaultdata=<?php echo $aModule['id']; ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_install_default_data_module_confirm')) ?>')"
					class="lazy-load icon database_add"><?php _e('c_a_modules_install_default_data') ?></a></li>
					<?php endif; ?>

					<?php if (file_exists($aModule['root'].'/install/test_set/')) : ?>
					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?testset=<?php echo $aModule['id']; ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_install_test_set_module_confirm')) ?>')"
					class="lazy-load icon package"><?php _e('c_a_modules_install_test_set') ?></a></li>
					<?php endif; ?>

					<?php if (file_exists($aModule['root'].'/install/db-truncate.xml')) : ?>
					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?empty=<?php echo $aModule['id']; ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_empty_module_confirm')) ?>')"
					title="<?php printf(__('c_a_modules_empty_module_%s'),$aModule['name_l10n']) ?>"
					class="icon package_delete"><?php _e('c_a_modules_empty_module') ?></a></li>
					<?php endif; ?>
				</ul>
			</td>
			<td class="<?php echo $td_class ?> nowrap">
				<ul class="actions">
					<?php if (file_exists($aModule['root'].'/install/tpl/')) : ?>
					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?templates=<?php echo $aModule['id']; ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_replace_templates_files_confirm')) ?>')"
					class="icon layout"><?php _e('c_a_modules_replace_templates_files') ?></a></li>
					<?php endif; ?>

					<?php if (file_exists($aModule['root'].'/install/assets/')) : ?>
					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?common=<?php echo $aModule['id']; ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_replace_common_files_confirm')) ?>')"
					class="icon folder_page"><?php _e('c_a_modules_replace_common_files') ?></a></li>
					<?php endif; ?>

					<?php if (file_exists($aModule['root'].'/install/public/')) : ?>
					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?public=<?php echo $aModule['id']; ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_replace_public_files_confirm')) ?>')"
					class="icon script"><?php _e('c_a_modules_replace_public_files') ?></a></li>
					<?php endif; ?>

					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?compare=<?php echo $aModule['id']; ?>"
					class="icon page_copy"><?php _e('c_a_modules_compare_files') ?></a></li>
				</ul>
			</td>
			<td class="<?php echo $td_class ?> small">
				<ul class="actions">
					<li>
						<a href="<?php echo $view->generateUrl('config_modules') ?>?download=<?php echo $aModule['id']; ?>"
						title="<?php printf(__('c_c_action_Download_%s'),$aModule['name_l10n']) ?>"
						class="icon package_go"><?php _e('c_c_action_Download') ?></a>
					</li>
					<li>
						<?php if (!$aModule['status']) : ?>
						<a href="<?php echo $view->generateUrl('config_modules') ?>?enable=<?php echo $aModule['id']; ?>"
						title="<?php printf(__('c_c_action_Enable_%s'),$aModule['name_l10n']) ?>"
						class="icon plugin_disabled"><?php _e('c_c_action_Enable') ?></a>
						<?php else : ?>
						<a href="<?php echo $view->generateUrl('config_modules') ?>?disable=<?php echo $aModule['id']; ?>"
						title="<?php printf(__('c_c_action_Disable_%s'),$aModule['name_l10n']) ?>"
						class="icon plugin"><?php _e('c_c_action_Disable') ?></a>
						<?php endif; ?>
					</li>
					<li>
						<a href="<?php echo $view->generateUrl('config_modules') ?>?reinstall=<?php echo $aModule['id']; ?>"
						onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_reinstall_module_confirm')) ?>')"
						title="<?php printf(__('c_c_action_Re-install_%s'),$aModule['name_l10n']) ?>"
						class="icon plugin_go"><?php _e('c_c_action_Re-install') ?></a>
					</li>
					<li>
						<a href="<?php echo $view->generateUrl('config_modules') ?>?uninstall=<?php echo $aModule['id']; ?>"
						onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_remove_module_confirm')) ?>')"
						title="<?php printf(__('c_c_action_Uninstall_%s'),$aModule['name_l10n']) ?>"
						class="icon plugin_delete"><?php _e('c_c_action_Uninstall') ?></a>
					</li>
				</ul>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div><!-- #tab-installed -->

<div id="tab-uninstalled">
	<h3><?php echo __('c_a_modules_uninstalled_modules').' ('.ModulesCollection::pluralizeModuleCount(count($aUninstalledModules)).')' ?></h3>

	<?php if (empty($aUninstalledModules)) : ?>
		<p><?php _e('c_a_modules_no_modules_uninstalled') ?></p>
	<?php else : ?>

	<table class="common">
		<caption><?php _e('c_a_modules_uninstalled_list_modules') ?></caption>
		<thead><tr>
			<th scope="col" class="left" colspan="2"><?php _e('c_c_Name') ?></th>
			<th scope="col" class="center"><?php _e('c_a_modules_version') ?></th>
			<th scope="col"><?php _e('c_a_modules_actions') ?></th>
		</tr></thead>
		<tbody>
		<?php
		$line_count = 0;

		foreach ($aUninstalledModules as $id=>$module) :
			$td_class = $line_count%2 == 0 ? 'even' : 'odd';
			$line_count++;
		?>
		<tr>
			<td class="<?php echo $td_class; ?> small">
				<?php if (file_exists($okt->options->get('modules_dir').'/'.$id.'/install/assets/module_icon.png')) : ?>
					<img src="<?php echo Utilities::base64EncodeImage($okt->options->get('modules_dir').'/'.$id.'/install/assets/module_icon.png', 'png'); ?>" width="32" height="32" alt="" />
				<?php else: ?>
					<img src="<?php echo $okt->options->public_url ?>/img/admin/module.png" width="32" height="32" alt="" />
				<?php endif; ?>
			</td>
			<td class="<?php echo $td_class; ?>">
				<h4 class="title"><?php _e($module['name']) ?></h4>
				<p><?php _e($module['desc']) ?></p>
			</td>
			<td class="<?php echo $td_class; ?> center">
				<?php echo $module['version'] ?>
			</td>
			<td class="<?php echo $td_class ?> small">
				<ul class="actions">
					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?install=<?php echo $id ?>"
					title="<?php printf(__('c_c_action_Install_%s'),__($module['name'])) ?>"
					class="icon plugin_add"><?php _e('c_c_action_Install') ?></a></li>

					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?download=<?php echo $id ?>"
					title="<?php printf(__('c_c_action_Download_%s'),__($module['name'])) ?>"
					class="icon package_go"><?php _e('c_c_action_Download') ?></a></li>

					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?delete=<?php echo $id ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_delete_module_confirm')) ?>')"
					title="<?php printf(__('c_c_action_Delete_%s'),__($module['name'])) ?>"
					class="icon delete"><?php _e('c_c_action_Delete') ?></a></li>
				</ul>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div><!-- #tab-uninstalled -->

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
	<?php if (!$okt->config->modules_repositories_enabled) : ?>
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

<?php # des modules à mettre à jour ?
if (!empty($aUpdatablesModules)) : ?>
<div id="tab-updates">
	<h3><?php _e('c_a_modules_new_releases_available') ?></h3>

	<table class="common">
		<caption><?php _e('c_a_modules_list_new_versions_available') ?></caption>
		<thead><tr>
			<th scope="col" class="left"><?php _e('c_c_Name') ?></th>
			<th scope="col" class="center"><?php _e('c_a_modules_repository') ?></th>
			<th scope="col" class="center"><?php _e('c_a_modules_version') ?></th>
			<th scope="col" class="small nowrap"><?php _e('Update') ?></th>
		</tr></thead>
		<tbody>
		<?php foreach ($aUpdatablesModules as $updatable) :
			$td_class = $line_count%2 == 0 ? 'even' : 'odd';
			$line_count++; ?>
		<tr>
			<th scope="row" class="<?php echo $td_class; ?> fake-td">
			<?php echo $view->escape($updatable['name']) ?>
			<?php echo !empty($updatable['info']) ? '<br />'.$view->escape($updatable['info']) : ''; ?>
			</th>
			<td class="<?php echo $td_class; ?> center"><?php echo $view->escape($updatable['repository']) ?></td>
			<td class="<?php echo $td_class; ?> center"><?php echo $view->escape($updatable['version']) ?></td>
			<td class="<?php echo $td_class; ?> small nowrap"><a href="<?php echo $view->generateUrl('config_modules') ?>?repository=<?php
			echo urlencode($updatable['repository']) ?>&amp;module=<?php echo urlencode($updatable['id']) ?>" class="lazy-load"><?php
			_e('c_c_action_Download') ?></a></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div><!-- #tab-updates -->
<?php endif; ?>

</div><!-- #tabered -->
