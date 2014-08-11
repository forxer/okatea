<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Extensions\Modules\Collection as ModulesCollection;

$okt->page->dialog([], '.changelog_link');

# Display a UI dialog box for each module
foreach ($aInstalledModules as $aModule)
{
	if (file_exists($aModule['root'] . '/CHANGELOG'))
	{
		$okt->page->openLinkInDialog('#' . $aModule['id'] . '_changelog_link', array(
			'title' => $view->escapeJs($aModule['name_l10n'] . " CHANGELOG"),
			'width' => 730,
			'height' => 500
		));
	}
}

?>

<div id="tab-installed">
	<h3><?php _e('c_a_modules_installed_modules') ?> (<?php echo ModulesCollection::pluralizeModuleCount(count($aInstalledModules)); ?>)</h3>

	<?php if (empty($aInstalledModules)) : ?>
		<p><?php _e('c_a_modules_no_modules_installed') ?></p>
	<?php else : ?>

	<table class="common">
		<caption><?php _e('c_a_modules_list_installed_modules') ?></caption>
		<thead>
			<tr>
				<th scope="col" class="left" colspan="2"><?php _e('c_a_modules_name') ?></th>
				<th scope="col" class="center"><?php _e('c_a_modules_version') ?></th>
				<th scope="col"><?php _e('c_a_modules_data') ?></th>
				<th scope="col"><?php _e('c_a_modules_tools') ?></th>
				<th scope="col"><?php _e('c_a_modules_actions') ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$line_count = 0;
		
		foreach ($aInstalledModules as $aModule)
		:
			
			# odd/even
			$td_class = $line_count % 2 == 0 ? 'even' : 'odd';
			$line_count ++;
			
			# disabled ?
			if (!$aModule['status'])
			{
				$td_class .= ' disabled';
			}
			
			# title
			$module_title = $aModule['name_l10n'];
			if ($aModule['status'] && $okt['adminRouter']->routeExists($aModule['id'] . '_index'))
			{
				$module_title = '<a href="' . $okt['adminRouter']->generate($aModule['id'] . '_index') . '">' . $module_title . '</a>';
			}
			
			# links
			$module_links = [];
			if (file_exists($aModule['root'] . '/CHANGELOG'))
			{
				$module_links[] = '<a href="' . $view->generateAdminUrl('config_modules') . '?show_changelog=' . $aModule['id'] . '"' . ' id="' . $aModule['id'] . '_changelog_link">' . __('c_a_modules_changelog') . '</a>';
			}
			
			if ($okt['adminRouter']->routeExists($aModule['id'] . '_display'))
			{
				$module_links[] = '<a href="' . $okt['adminRouter']->generate($aModule['id'] . '_display') . '">' . __('c_a_modules_display') . '</a>';
			}
			if ($okt['adminRouter']->routeExists($aModule['id'] . '_config'))
			{
				$module_links[] = '<a href="' . $okt['adminRouter']->generate($aModule['id'] . '_config') . '">' . __('c_a_modules_config') . '</a>';
			}
			?>
		<tr>
				<td class="<?php echo $td_class ?> small">
					<p>
				<?php if ($aModule['status']): ?>
					<a href="module.php?m=<?php echo $aModule['id'] ?>">
				<?php endif; ?>
					<?php if (file_exists($okt['public_path'].'/modules/'.$aModule['id'].'/module_icon.png')) : ?>
					<img
							src="<?php echo $okt['public_url'].'/modules/'.$aModule['id'] ?>/module_icon.png"
							width="32" height="32" alt="" />
					<?php else: ?>
					<img
							src="<?php echo $okt['public_url'] ?>/img/admin/module.png"
							width="32" height="32" alt="" />
					<?php endif; ?>
				<?php if ($aModule['status']): ?>
					</a>
				<?php endif; ?>
				</p>
				</td>
				<td class="<?php echo $td_class ?>">
					<p class="title"><?php echo $module_title ?></p>
					<p><?php echo $aModule['desc_l10n'] ?></p>

				<?php if (!empty($module_links)) : ?>
				<p><?php echo implode(' - ',$module_links) ?></p>
				<?php endif; ?>
			</td>
				<td class="<?php echo $td_class ?> center">
					<p>
				<?php echo $aModule['version']?>
				<?php if (version_compare($aAllModules[$aModule['id']]['version'], $aModule['version'], '>')) : ?>
				<br /> <a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?update=<?php echo $aModule['id']; ?>"
							class="icon plugin_error">Mettre à jour à la version <?php echo $aAllModules[$aModule['id']]['version'] ?></a>
				<?php endif; ?>
				</p>
				</td>
				<td class="<?php echo $td_class ?> nowrap">
					<ul class="actions">
					<?php if (is_file($aModule['root'].'/Install/db-data.xml')) : ?>
					<li><a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?defaultdata=<?php echo $aModule['id']; ?>"
							onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_install_default_data_module_confirm')) ?>')"
							class="lazy-load icon database_add"><?php _e('c_a_modules_install_default_data') ?></a></li>
					<?php endif; ?>

					<?php if (is_dir($aModule['root'].'/Install/TestSet')) : ?>
					<li><a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?testset=<?php echo $aModule['id']; ?>"
							onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_install_test_set_module_confirm')) ?>')"
							class="lazy-load icon package"><?php _e('c_a_modules_install_test_set') ?></a></li>
					<?php endif; ?>

					<?php if (is_file($aModule['root'].'/Install/db-truncate.xml')) : ?>
					<li><a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?empty=<?php echo $aModule['id']; ?>"
							onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_empty_module_confirm')) ?>')"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_a_modules_empty_module_%s'),$aModule['name_l10n'])) ?>"
							class="icon package_delete"><?php _e('c_a_modules_empty_module') ?></a></li>
					<?php endif; ?>
				</ul>
				</td>
				<td class="<?php echo $td_class ?> nowrap">
					<ul class="actions">
					<?php if (is_dir($aModule['root'].'/Install/Templates')) : ?>
					<li><a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?templates=<?php echo $aModule['id']; ?>"
							onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_replace_templates_files_confirm')) ?>')"
							class="icon layout"><?php _e('c_a_modules_replace_templates_files') ?></a></li>
					<?php endif; ?>

					<?php if (is_dir($aModule['root'].'/Install/Assets')) : ?>
					<li><a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?assets=<?php echo $aModule['id']; ?>"
							onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_replace_assets_files_confirm')) ?>')"
							class="icon folder_page"><?php _e('c_a_modules_replace_assets_files') ?></a></li>
					<?php endif; ?>

					<?php if (is_dir($aModule['root'].'/Install/public')) : ?>
					<li><a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?public=<?php echo $aModule['id']; ?>"
							onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_replace_public_files_confirm')) ?>')"
							class="icon script"><?php _e('c_a_modules_replace_public_files') ?></a></li>
					<?php endif; ?>

					<li><a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?compare=<?php echo $aModule['id']; ?>"
							class="icon page_copy"><?php _e('c_a_modules_compare_files') ?></a></li>
					</ul>
				</td>
				<td class="<?php echo $td_class ?> small">
					<ul class="actions">
						<li><a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?download=<?php echo $aModule['id']; ?>"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Download_%s'),$aModule['name_l10n'])) ?>"
							class="icon package_go"><?php _e('c_c_action_Download') ?></a></li>
						<li>
						<?php if (!$aModule['status']) : ?>
						<a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?enable=<?php echo $aModule['id']; ?>"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Enable_%s'),$aModule['name_l10n'])) ?>"
							class="icon plugin_disabled"><?php _e('c_c_action_Enable') ?></a>
						<?php else : ?>
						<a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?disable=<?php echo $aModule['id']; ?>"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Disable_%s'),$aModule['name_l10n'])) ?>"
							class="icon plugin"><?php _e('c_c_action_Disable') ?></a>
						<?php endif; ?>
					</li>
						<li><a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?reinstall=<?php echo $aModule['id']; ?>"
							onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_reinstall_module_confirm')) ?>')"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Re-install_%s'),$aModule['name_l10n'])) ?>"
							class="icon plugin_go"><?php _e('c_c_action_Re-install') ?></a></li>
						<li><a
							href="<?php echo $view->generateAdminUrl('config_modules') ?>?uninstall=<?php echo $aModule['id']; ?>"
							onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_remove_module_confirm')) ?>')"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Uninstall_%s'),$aModule['name_l10n'])) ?>"
							class="icon plugin_delete"><?php _e('c_c_action_Uninstall') ?></a>
						</li>
					</ul>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div>
<!-- #tab-installed -->
