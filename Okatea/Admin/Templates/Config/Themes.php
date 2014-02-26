<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Extensions\Themes\Collection as ThemesCollection;
use Okatea\Tao\Misc\Utilities;

$view->extend('layout');

# Infos page par défaut
$okt->page->addGlobalTitle(__('Themes'));

$okt->page->dialog(array(), '.changelog_link');

$okt->page->dialog(array(), '.notes_link');

# Display a UI dialog box for each theme
foreach ($aInstalledThemes as $aTheme)
{
	if (file_exists($aTheme['root'].'/CHANGELOG'))
	{
		$okt->page->openLinkInDialog('#'.$aTheme['id'].'_changelog_link',array(
			'title' => $view->escapeJs($aTheme['name_l10n']." CHANGELOG"),
			'width' => 730,
			'height' => 500
		));
	}

	if (file_exists($aTheme['root'].'/notes.md'))
	{
		$okt->page->openLinkInDialog('#'.$aTheme['id'].'_notes_link',array(
			'title' => $view->escapeJs($aTheme['name_l10n']." Notes"),
			'width' => 730,
			'height' => 500
		));
	}
}

# Toggle
$okt->page->toggleWithLegend('add_theme_zip_title','add_theme_zip_content',array('cookie'=>'oktAdminAddThemeZip'));
$okt->page->toggleWithLegend('add_theme_repo_title','add_theme_repo_content',array('cookie'=>'oktAdminAddThemeRepo'));

# Tabs
$okt->page->tabs();

# Loader
$okt->page->loader('.lazy-load');

?>

<div id="tabered">
	<ul>
		<li><a href="#tab-installed"><span><?php _e('c_a_themes_installed_themes') ?></span></a></li>
		<li><a href="#tab-uninstalled"><span><?php _e('c_a_themes_uninstalled_themes') ?></span></a></li>
		<li><a href="#tab-add"><span><?php _e('c_a_themes_add_theme') ?></span></a></li>
		<?php # des themes à mettre à jour ?
		if (!empty($aUpdatablesThemes)) : ?>
		<li><a href="#tab-updates"><span><?php _e('c_a_themes_new_releases') ?></span></a></li>
		<?php endif; ?>
	</ul>

<div id="tab-installed">
	<h3><?php _e('c_a_themes_installed_themes') ?> (<?php echo ThemesCollection::pluralizeThemeCount(count($aInstalledThemes)) ?>)</h3>

	<?php if (empty($aInstalledThemes)) : ?>
		<p><?php _e('c_a_themes_no_themes_installed') ?></p>
	<?php else : ?>

	<table class="common">
		<caption><?php _e('c_a_themes_list_installed_themes') ?></caption>
		<thead><tr>
			<th scope="col" class="left"><?php _e('c_a_themes_name') ?></th>
			<th scope="col" class="center"><?php _e('c_a_themes_version') ?></th>
			<th scope="col"><?php _e('c_a_themes_tools') ?></th>
			<th scope="col"><?php _e('c_a_themes_use') ?></th>
			<th scope="col"><?php _e('c_a_themes_actions') ?></th>
		</tr></thead>
		<tbody>
		<?php
		$line_count = 0;

		foreach ($aInstalledThemes as $aTheme) :

			# odd/even
			$td_class = $line_count%2 == 0 ? 'even' : 'odd';
			$line_count++;

			# disabled ?
			if (!$aTheme['status']) {
				$td_class .= ' disabled';
			}

			# links
			$theme_links = array();
			if (file_exists($aTheme['root'].'/CHANGELOG'))
			{
				$theme_links[] = '<a href="'.$view->generateUrl('config_themes').'?show_changelog='.$aTheme['id'].'"'.
				' id="'.$aTheme['id'].'_changelog_link">'.__('c_a_themes_changelog').'</a>';
			}

			if (file_exists($aTheme['root'].'/notes.md'))
			{
				$theme_links[] = '<a href="'.$view->generateUrl('config_themes').'?show_notes='.$aTheme['id'].'"'.
				' id="'.$aTheme['id'].'_notes_link">'.__('c_a_themes_notes').'</a>';
			}

			/*
			if ($okt->adminRouter->routeExists($aTheme['id'].'_display')) {
				$theme_links[] = '<a href="'.$okt->adminRouter->generate($aTheme['id'].'_display').'">'.__('c_a_themes_display').'</a>';
			}
			if ($okt->adminRouter->routeExists($aTheme['id'].'_config')) {
				$theme_links[] = '<a href="'.$okt->adminRouter->generate($aTheme['id'].'_config').'">'.__('c_a_themes_config').'</a>';
			}
			*/
		?>
		<tr>
			<td class="<?php echo $td_class ?>">
				<p class="title"><?php echo $aTheme['name_l10n'] ?></p>
				<p><?php echo $aTheme['desc_l10n'] ?></p>

				<?php if (!empty($theme_links)) : ?>
				<p><?php echo implode(' - ',$theme_links) ?></p>
				<?php endif; ?>
			</td>
			<td class="<?php echo $td_class ?> center">
				<p>
				<?php echo $aTheme['version'] ?>
				<?php if (version_compare($aAllThemes[$aTheme['id']]['version'], $aTheme['version'], '>')) : ?>
				<br /><a href="<?php echo $view->generateUrl('config_themes') ?>?update=<?php echo $aTheme['id']; ?>"
				class="icon plugin_error">Mettre à jour à la version <?php echo $aAllThemes[$aTheme['id']]['version'] ?></a>
				<?php endif; ?>
				</p>
			</td>
			<td class="<?php echo $td_class ?> nowrap">
				<ul class="actions">
					<?php if (file_exists($aTheme['root'].'/Install/assets/')) : ?>
					<li><a href="<?php echo $view->generateUrl('config_themes') ?>?common=<?php echo $aTheme['id']; ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_themes_replace_common_files_confirm')) ?>')"
					class="icon folder_page"><?php _e('c_a_themes_replace_common_files') ?></a></li>
					<?php endif; ?>

					<?php if (file_exists($aTheme['root'].'/Install/public/')) : ?>
					<li><a href="<?php echo $view->generateUrl('config_themes') ?>?public=<?php echo $aTheme['id']; ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_themes_replace_public_files_confirm')) ?>')"
					class="icon script"><?php _e('c_a_themes_replace_public_files') ?></a></li>
					<?php endif; ?>

					<li><a href="<?php echo $view->generateUrl('config_themes') ?>?compare=<?php echo $aTheme['id']; ?>"
					class="icon page_copy"><?php _e('c_a_themes_compare_files') ?></a></li>
				</ul>
			</td>
			<td class="<?php echo $td_class ?> small nowrap">
				<ul class="actions">
					<li>
						<?php if ($aTheme['id'] == $this->okt->config->themes['desktop']) : ?>
						<span class="icon tick"></span><?php _e('c_a_themes_current') ?>
						<?php else : ?>
						<a href="<?php echo $view->generateUrl('config_themes').'?use='.$aTheme['id'] ?>" class="icon cross"><?php _e('c_a_themes_use_desktop') ?></a>
						<?php endif; ?>
					</li>
					<li>
						<?php if ($aTheme['id'] == $this->okt->config->themes['mobile']) : ?>
						<a href="<?php echo $view->generateUrl('config_themes').'?use_mobile='.$aTheme['id'] ?>" class="icon tick"><?php _e('c_a_themes_current_mobile') ?></a>
						<?php else : ?>
						<a href="<?php echo $view->generateUrl('config_themes').'?use_mobile='.$aTheme['id'] ?>" class="icon cross"><?php _e('c_a_themes_use_mobile') ?></a>
						<?php endif; ?>
					</li>
					<li>
						<?php if ($aTheme['id'] == $this->okt->config->themes['tablet']) : ?>
						<a href="<?php echo $view->generateUrl('config_themes').'?use_tablet='.$aTheme['id'] ?>" class="icon tick"><?php _e('c_a_themes_current_tablet') ?></a>
						<?php else : ?>
						<a href="<?php echo $view->generateUrl('config_themes').'?use_tablet='.$aTheme['id'] ?>" class="icon cross"><?php _e('c_a_themes_use_tablet') ?></a>
						<?php endif; ?>
					</li>
				</ul>
			</td>
			<td class="<?php echo $td_class ?> small nowrap">
				<ul class="actions">
					<li>
						<a href="<?php echo $view->generateUrl('config_themes') ?>?download=<?php echo $aTheme['id']; ?>"
						title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Download_%s'),$aTheme['name_l10n'])) ?>"
						class="icon package_go"><?php _e('c_c_action_Download') ?></a>
					</li>
					<li>
					<?php if (ThemesCollection::DEFAULT_THEME === $aTheme['id']) : ?>
						<span class="icon picture"></span><?php _e('c_c_action_Disable') ?>
					<?php else : ?>
						<?php if (!$aTheme['status']) : ?>
						<a href="<?php echo $view->generateUrl('config_themes') ?>?enable=<?php echo $aTheme['id']; ?>"
						title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Enable_%s'),$aTheme['name_l10n'])) ?>"
						class="icon picture_empty"><?php _e('c_c_action_Enable') ?></a>
						<?php else : ?>
						<a href="<?php echo $view->generateUrl('config_themes') ?>?disable=<?php echo $aTheme['id']; ?>"
						title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Disable_%s'),$aTheme['name_l10n'])) ?>"
						class="icon picture"><?php _e('c_c_action_Disable') ?></a>
						<?php endif; ?>
					<?php endif; ?>
					</li>
					<li>
					<?php if (ThemesCollection::DEFAULT_THEME === $aTheme['id']) : ?>
						<span class="icon picture_go"></span><?php _e('c_c_action_Re-install') ?>
					<?php else : ?>
						<a href="<?php echo $view->generateUrl('config_themes') ?>?reinstall=<?php echo $aTheme['id']; ?>"
						onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_themes_reinstall_theme_confirm')) ?>')"
						title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Re-install_%s'),$aTheme['name_l10n'])) ?>"
						class="icon picture_go"><?php _e('c_c_action_Re-install') ?></a>
					<?php endif; ?>
					</li>
					<li>
					<?php if (ThemesCollection::DEFAULT_THEME === $aTheme['id']) : ?>
						<span class="icon picture_delete"></span><?php _e('c_c_action_Uninstall') ?>
					<?php else : ?>
						<a href="<?php echo $view->generateUrl('config_themes') ?>?uninstall=<?php echo $aTheme['id']; ?>"
						onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_themes_remove_theme_confirm')) ?>')"
						title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Uninstall_%s'),$aTheme['name_l10n'])) ?>"
						class="icon picture_delete"><?php _e('c_c_action_Uninstall') ?></a>
					<?php endif; ?>
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
	<h3><?php echo __('c_a_themes_uninstalled_themes').' ('.ThemesCollection::pluralizeThemeCount(count($aUninstalledThemes)).')' ?></h3>

	<?php if (empty($aUninstalledThemes)) : ?>
		<p><?php _e('c_a_themes_no_themes_uninstalled') ?></p>
	<?php else : ?>

	<table class="common">
		<caption><?php _e('c_a_themes_uninstalled_list_themes') ?></caption>
		<thead><tr>
			<th scope="col" class="left"><?php _e('c_c_Name') ?></th>
			<th scope="col" class="center"><?php _e('c_a_themes_version') ?></th>
			<th scope="col"><?php _e('c_a_themes_actions') ?></th>
		</tr></thead>
		<tbody>
		<?php
		$line_count = 0;

		foreach ($aUninstalledThemes as $id=>$theme) :
			$td_class = $line_count%2 == 0 ? 'even' : 'odd';
			$line_count++;
		?>
		<tr>
			<td class="<?php echo $td_class; ?>">
				<p class="title"><?php _e($theme['name']) ?></p>
				<p><?php _e($theme['desc']) ?></p>
			</td>
			<td class="<?php echo $td_class; ?> center">
				<?php echo $theme['version'] ?>
			</td>
			<td class="<?php echo $td_class ?> small">
				<ul class="actions">
					<li><a href="<?php echo $view->generateUrl('config_themes') ?>?install=<?php echo $id ?>"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Install_%s'),__($theme['name']))) ?>"
					class="icon picture_add"><?php _e('c_c_action_Install') ?></a></li>

					<li><a href="<?php echo $view->generateUrl('config_themes') ?>?download=<?php echo $id ?>"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Download_%s'),__($theme['name']))) ?>"
					class="icon package_go"><?php _e('c_c_action_Download') ?></a></li>

					<li><a href="<?php echo $view->generateUrl('config_themes') ?>?delete=<?php echo $id ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_themes_delete_theme_confirm')) ?>')"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Delete_%s'),__($theme['name']))) ?>"
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
	<h3><?php _e('c_a_themes_add_theme') ?></h3>

	<h4 id="add_theme_zip_title"><?php _e('c_a_themes_add_theme_from_zip') ?></h4>

	<div id="add_theme_zip_content" class="two-cols">
		<form class="col" action="<?php echo $view->generateUrl('config_themes') ?>" method="post">
			<fieldset>
				<legend><?php _e('c_a_themes_download_zip_file') ?></legend>
				<p class="field"><label for="pkg_url"><?php _e('c_a_themes_plugin_zip_file_url') ?></label>
				<?php echo form::text('pkg_url', 40, 255) ?></p>
			</fieldset>

			<p><?php echo form::hidden('fetch_pkg', 1) ?>
			<?php echo $okt->page->formtoken() ?>
			<input type="submit" class="lazy-load" value="<?php _e('c_a_themes_download_plugin') ?>" /></p>
		</form>
		<form class="col" action="<?php echo $view->generateUrl('config_themes') ?>" method="post" enctype="multipart/form-data">
			<fieldset>
				<legend><?php _e('c_a_themes_upload_zip_file') ?></legend>
				<p class="field"><label for="pkg_file"><?php _e('c_a_themes_plugin_zip_file') ?></label>
				<?php echo form::file('pkg_file')?></p>
			</fieldset>

			<p><?php echo form::hidden('upload_pkg', 1) ?>
			<?php echo $okt->page->formtoken() ?>
			<input type="submit" class="lazy-load" value="<?php _e('c_a_themes_upload_plugin') ?>" /></p>
		</form>
	</div>

	<h4 id="add_theme_repo_title"><?php _e('c_a_themes_add_theme_from_remote_repository') ?></h4>

	<div id="add_theme_repo_content">
	<?php if (!$okt->config->repositories['themes']['enabled']) : ?>
		<p><?php _e('c_a_themes_repositories_themes_disabled') ?></p>

	<?php elseif (!empty($aThemesRepositories)) : ?>
		<?php foreach($aThemesRepositories as $repo_name=>$themes) : ?>

		<h5><?php echo $view->escape($repo_name).' ('.ThemesCollection::pluralizeThemeCount(count($themes)).')'; ?></h5>

		<table class="common">
			<caption><?php printf('c_a_themes_list_themes_available_%s', $view->escape($repo_name)) ?></caption>
			<thead><tr>
				<th scope="col" class="left"><?php _e('c_c_Name') ?></th>
				<th scope="col" class="center"><?php _e('c_a_themes_version') ?></th>
				<th scope="col" class="small"><?php _e('c_c_action_Add') ?></th>
				<th scope="col" class="small"><?php _e('c_c_action_Download') ?></th>
			</tr></thead>
			<tbody>
			<?php $line_count = 0;
			foreach($themes as $theme) :
				$td_class = $line_count%2 == 0 ? 'even' : 'odd';
				$line_count++; ?>
			<tr>
				<th scope="row" class="<?php echo $td_class; ?> fake-td">
				<?php echo $view->escape($theme['name']) ?>
				<?php echo !empty($theme['info']) ? '<br />'.$view->escape($theme['info']) : ''; ?>
				</th>
				<td class="<?php echo $td_class; ?> center"><?php echo $view->escape($theme['version']) ?></td>
				<td class="<?php echo $td_class; ?> center"><a href="<?php echo $view->generateUrl('config_themes') ?>?repository=<?php echo urlencode($repo_name) ?>&amp;theme=<?php echo urlencode($theme['id']) ?>" class="lazy-load"><?php _e('c_c_action_Add') ?></a></td>
				<td class="<?php echo $td_class; ?> center"><a href="<?php echo $theme['href'] ?>"><?php _e('c_c_action_Download') ?></a></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endforeach; ?>
	<?php else : ?>
		<p><?php _e('c_a_themes_no_repository_themes_defined') ?></p>
	<?php endif; ?>
	</div>

</div><!-- #tab-add -->

<?php # des themes à mettre à jour ?
if (!empty($aUpdatablesThemes)) : ?>
<div id="tab-updates">
	<h3><?php _e('c_a_themes_new_releases_available') ?></h3>

	<table class="common">
		<caption><?php _e('c_a_themes_list_new_versions_available') ?></caption>
		<thead><tr>
			<th scope="col" class="left"><?php _e('c_c_Name') ?></th>
			<th scope="col" class="center"><?php _e('c_a_themes_repository') ?></th>
			<th scope="col" class="center"><?php _e('c_a_themes_version') ?></th>
			<th scope="col" class="small nowrap"><?php _e('Update') ?></th>
		</tr></thead>
		<tbody>
		<?php foreach ($aUpdatablesThemes as $updatable) :
			$td_class = $line_count%2 == 0 ? 'even' : 'odd';
			$line_count++; ?>
		<tr>
			<th scope="row" class="<?php echo $td_class; ?> fake-td">
			<?php echo $view->escape($updatable['name']) ?>
			<?php echo !empty($updatable['info']) ? '<br />'.$view->escape($updatable['info']) : ''; ?>
			</th>
			<td class="<?php echo $td_class; ?> center"><?php echo $view->escape($updatable['repository']) ?></td>
			<td class="<?php echo $td_class; ?> center"><?php echo $view->escape($updatable['version']) ?></td>
			<td class="<?php echo $td_class; ?> small nowrap"><a href="<?php echo $view->generateUrl('config_themes') ?>?repository=<?php
			echo urlencode($updatable['repository']) ?>&amp;theme=<?php echo urlencode($updatable['id']) ?>" class="lazy-load"><?php
			_e('c_c_action_Download') ?></a></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div><!-- #tab-updates -->
<?php endif; ?>

</div><!-- #tabered -->
