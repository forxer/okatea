<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Extensions\Themes\Collection as ThemesCollection;
use Okatea\Tao\Misc\Utilities;

?>

<div id="tab-uninstalled">
	<h3><?php echo __('c_a_themes_uninstalled_themes').' ('.ThemesCollection::pluralizeThemeCount(count($aUninstalledThemes)).')' ?></h3>

	<?php if (empty($aUninstalledThemes)) : ?>
		<p><?php _e('c_a_themes_no_themes_uninstalled') ?></p>
	<?php else : ?>

	<table class="common">
		<caption><?php _e('c_a_themes_uninstalled_list_themes') ?></caption>
		<thead>
			<tr>
				<th scope="col" class="left" colspan="2"><?php _e('c_c_Name') ?></th>
				<th scope="col" class="center"><?php _e('c_a_themes_version') ?></th>
				<th scope="col"><?php _e('c_a_themes_actions') ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
		$line_count = 0;
		
		foreach ($aUninstalledThemes as $id => $aTheme)
		:
			$td_class = $line_count % 2 == 0 ? 'even' : 'odd';
			$line_count ++;
			?>
		<tr>
				<td class="<?php echo $td_class ?> small">
				<?php if ($aTheme['icon']) : ?>
				<p>
						<img
							src="<?php echo  Utilities::base64EncodeImage($aTheme['root'].'/Install/Assets/'.$aTheme['icon']) ?>"
							alt="" width="64" height="64">
					</p>
				<?php else : ?>
				<div class="no-icon">
						<em>n/a</em>
					</div>
				<?php endif; ?>

			</td>
				<td class="<?php echo $td_class; ?>">
					<p class="title"><?php _e($aTheme['name']) ?></p>
					<p><?php _e($aTheme['desc']) ?></p>
				</td>
				<td class="<?php echo $td_class; ?> center">
					<p><?php echo $aTheme['version'] ?></p>
				</td>
				<td class="<?php echo $td_class ?> small">
					<ul class="actions">
						<li><a
							href="<?php echo $view->generateAdminUrl('config_themes') ?>?install=<?php echo $id ?>"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Install_%s'),__($aTheme['name']))) ?>"
							class="icon picture_add"><?php _e('c_c_action_Install') ?></a></li>

						<li><a
							href="<?php echo $view->generateAdminUrl('config_themes') ?>?download=<?php echo $id ?>"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Download_%s'),__($aTheme['name']))) ?>"
							class="icon package_go"><?php _e('c_c_action_Download') ?></a></li>

						<li><a
							href="<?php echo $view->generateAdminUrl('config_themes') ?>?delete=<?php echo $id ?>"
							onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_themes_delete_theme_confirm')) ?>')"
							title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Delete_%s'),__($aTheme['name']))) ?>"
							class="icon delete"><?php _e('c_c_action_Delete') ?></a></li>
					</ul>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div>
<!-- #tab-uninstalled -->
