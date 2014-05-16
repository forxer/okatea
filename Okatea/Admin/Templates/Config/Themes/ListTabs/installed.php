<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Extensions\Themes\Collection as ThemesCollection;

$okt->page->dialog(array(), '.changelog_link');

$okt->page->dialog(array(), '.notes_link');

# Display a UI dialog box for each theme
foreach ($aInstalledThemes as $aTheme)
{
	if (file_exists($aTheme['root'] . '/CHANGELOG'))
	{
		$okt->page->openLinkInDialog('#' . $aTheme['id'] . '_changelog_link', array(
			'title' => $view->escapeJs($aTheme['name_l10n'] . " CHANGELOG"),
			'width' => 730,
			'height' => 500
		));
	}
	
	if (file_exists($aTheme['root'] . '/notes.md'))
	{
		$okt->page->openLinkInDialog('#' . $aTheme['id'] . '_notes_link', array(
			'title' => $view->escapeJs($aTheme['name_l10n'] . " Notes"),
			'width' => 730,
			'height' => 500
		));
	}
}

?>


<div id="tab-installed">
	<h3><?php _e('c_a_themes_installed_themes') ?> (<?php echo ThemesCollection::pluralizeThemeCount(count($aInstalledThemes)) ?>)</h3>

	<?php if (empty($aInstalledThemes)) : ?>
		<p><?php _e('c_a_themes_no_themes_installed') ?></p>
	<?php else : ?>

	<table class="common">
		<caption><?php _e('c_a_themes_list_installed_themes') ?></caption>
		<thead>
			<tr>
				<th scope="col" class="left" colspan="2"><?php _e('c_a_themes_name') ?></th>
				<th scope="col" class="center"><?php _e('c_a_themes_version') ?></th>
				<th scope="col"><?php _e('c_a_themes_tools') ?></th>
				<th scope="col"><?php _e('c_a_themes_use') ?></th>
				<th scope="col"><?php _e('c_a_themes_actions') ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$line_count = 0;
		
		foreach ($aInstalledThemes as $aTheme)
		:
			
			# odd/even
			$td_class = $line_count % 2 == 0 ? 'even' : 'odd';
			$line_count ++;
			
			# disabled ?
			if (! $aTheme['status'])
			{
				$td_class .= ' disabled';
			}
			
			# links
			$theme_links = array();
			if (file_exists($aTheme['root'] . '/CHANGELOG'))
			{
				$theme_links[] = '<a href="' . $view->generateUrl('config_themes') . '?show_changelog=' . $aTheme['id'] . '"' . ' id="' . $aTheme['id'] . '_changelog_link">' . __('c_a_themes_changelog') . '</a>';
			}
			if (file_exists($aTheme['root'] . '/notes.md'))
			{
				$theme_links[] = '<a href="' . $view->generateUrl('config_themes') . '?show_notes=' . $aTheme['id'] . '"' . ' id="' . $aTheme['id'] . '_notes_link">' . __('c_a_themes_notes') . '</a>';
			}
			if ($okt->adminRouter->routeExists($aTheme['id'] . '_display'))
			{
				$theme_links[] = '<a href="' . $okt->adminRouter->generate($aTheme['id'] . '_display') . '">' . __('c_a_themes_display') . '</a>';
			}
			if ($okt->adminRouter->routeExists($aTheme['id'] . '_config'))
			{
				$theme_links[] = '<a href="' . $okt->adminRouter->generate($aTheme['id'] . '_config') . '">' . __('c_a_themes_config') . '</a>';
			}
			?>
		<tr>
				<td class="<?php echo $td_class ?> small">
				<?php if ($aTheme['icon']) : ?>
				<p>
						<img
							src="<?php echo  $okt->options->get('public_url').'/themes/'.$aTheme['id'].'/'.$aTheme['icon'] ?>"
							alt="" width="64" height="64">
					</p>
				<?php else : ?>
				<div class="no-icon">
						<em>n/a</em>
					</div>
				<?php endif; ?>
			</td>
				<td class="<?php echo $td_class ?>">
					<p class="title">
				<?php if ($aTheme['status']) : ?>
				<a
							href="<?php echo $view->generateUrl('config_theme', array('theme_id' => $aTheme['id'])) ?>">
				<?php echo $aTheme['name_l10n'] ?></a>
				<?php else : ?>
				<?php echo $aTheme['name_l10n']?>
				<?php endif; ?>
				</p>
					<p><?php echo $aTheme['desc_l10n'] ?></p>

				<?php if (!empty($theme_links)) : ?>
				<p><?php echo implode(' - ',$theme_links) ?></p>
				<?php endif; ?>
			</td>
				<td class="<?php echo $td_class ?> center">
					<p>
				<?php echo $aTheme['version']?>
				<?php if (version_compare($aAllThemes[$aTheme['id']]['version'], $aTheme['version'], '>')) : ?>
				<br />
						<a
							href="<?php echo $view->generateUrl('config_themes') ?>?update=<?php echo $aTheme['id']; ?>"
							class="icon plugin_error">Mettre à jour à la version <?php echo $aAllThemes[$aTheme['id']]['version'] ?></a>
				<?php endif; ?>
				</p>
				</td>
				<td class="<?php echo $td_class ?> nowrap">
					<ul class="actions">
					<?php if (file_exists($aTheme['root'].'/Install/Assets/')) : ?>
					<li><a
							href="<?php echo $view->generateUrl('config_themes') ?>?common=<?php echo $aTheme['id']; ?>"
							onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_themes_replace_common_files_confirm')) ?>')"
							class="icon folder_page"><?php _e('c_a_themes_replace_common_files') ?></a></li>
					<?php endif; ?>

					<?php if (file_exists($aTheme['root'].'/Install/public/')) : ?>
					<li><a
							href="<?php echo $view->generateUrl('config_themes') ?>?public=<?php echo $aTheme['id']; ?>"
							onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_themes_replace_public_files_confirm')) ?>')"
							class="icon script"><?php _e('c_a_themes_replace_public_files') ?></a></li>
					<?php endif; ?>

					<li><a
							href="<?php echo $view->generateUrl('config_themes') ?>?compare=<?php echo $aTheme['id']; ?>"
							class="icon page_copy"><?php _e('c_a_themes_compare_files') ?></a></li>
					</ul>
				</td>
				<td class="<?php echo $td_class ?> small nowrap">
					<ul class="actions">
						<li>
						<?php if ($aTheme['id'] == $this->okt->config->themes['desktop']) : ?>
						<span class="icon tick"></span><?php _e('c_a_themes_current')?>
						<?php else : ?>
						<a
							href="<?php echo $view->generateUrl('config_themes').'?use='.$aTheme['id'] ?>"
							class="icon cross"><?php _e('c_a_themes_use_desktop') ?></a>
						<?php endif; ?>
					</li>
						<li>
						<?php if ($aTheme['id'] == $this->okt->config->themes['mobile']) : ?>
						<a
							href="<?php echo $view->generateUrl('config_themes').'?use_mobile='.$aTheme['id'] ?>"
							class="icon tick"><?php _e('c_a_themes_current_mobile') ?></a>
						<?php else : ?>
						<a
							href="<?php echo $view->generateUrl('config_themes').'?use_mobile='.$aTheme['id'] ?>"
							class="icon cross"><?php _e('c_a_themes_use_mobile') ?></a>
						<?php endif; ?>
					</li>
						<li>
						<?php if ($aTheme['id'] == $this->okt->config->themes['tablet']) : ?>
						<a
							href="<?php echo $view->generateUrl('config_themes').'?use_tablet='.$aTheme['id'] ?>"
							class="icon tick"><?php _e('c_a_themes_current_tablet') ?></a>
						<?php else : ?>
						<a
							href="<?php echo $view->generateUrl('config_themes').'?use_tablet='.$aTheme['id'] ?>"
							class="icon cross"><?php _e('c_a_themes_use_tablet') ?></a>
						<?php endif; ?>
					</li>
					</ul>
				</td>
				<td class="<?php echo $td_class ?> small nowrap">
					<ul class="actions">
						<li><a
							href="<?php echo $view->generateUrl('config_themes') ?>?download=<?php echo $aTheme['id']; ?>"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Download_%s'),$aTheme['name_l10n'])) ?>"
							class="icon package_go"><?php _e('c_c_action_Download') ?></a></li>
						<li>
					<?php if (ThemesCollection::DEFAULT_THEME === $aTheme['id']) : ?>
						<span class="icon picture"></span><?php _e('c_c_action_Disable')?>
					<?php else : ?>
						<?php if (!$aTheme['status']) : ?>
						<a
							href="<?php echo $view->generateUrl('config_themes') ?>?enable=<?php echo $aTheme['id']; ?>"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Enable_%s'),$aTheme['name_l10n'])) ?>"
							class="icon picture_empty"><?php _e('c_c_action_Enable') ?></a>
						<?php else : ?>
						<a
							href="<?php echo $view->generateUrl('config_themes') ?>?disable=<?php echo $aTheme['id']; ?>"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Disable_%s'),$aTheme['name_l10n'])) ?>"
							class="icon picture"><?php _e('c_c_action_Disable') ?></a>
						<?php endif; ?>
					<?php endif; ?>
					</li>
						<li>
					<?php if (ThemesCollection::DEFAULT_THEME === $aTheme['id']) : ?>
						<span class="icon picture_go"></span><?php _e('c_c_action_Re-install')?>
					<?php else : ?>
						<a
							href="<?php echo $view->generateUrl('config_themes') ?>?reinstall=<?php echo $aTheme['id']; ?>"
							onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_themes_reinstall_theme_confirm')) ?>')"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Re-install_%s'),$aTheme['name_l10n'])) ?>"
							class="icon picture_go"><?php _e('c_c_action_Re-install') ?></a>
					<?php endif; ?>
					</li>
						<li>
					<?php if (ThemesCollection::DEFAULT_THEME === $aTheme['id']) : ?>
						<span class="icon picture_delete"></span><?php _e('c_c_action_Uninstall')?>
					<?php else : ?>
						<a
							href="<?php echo $view->generateUrl('config_themes') ?>?uninstall=<?php echo $aTheme['id']; ?>"
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
</div>
<!-- #tab-installed -->
