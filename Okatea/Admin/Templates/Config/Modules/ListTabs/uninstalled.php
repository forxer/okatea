<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Extensions\Modules\Collection as ModulesCollection;
use Okatea\Tao\Misc\Utilities;

?>

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
				<?php if (file_exists($okt->options->get('modules_dir').'/'.$id.'/Install/Assets/module_icon.png')) : ?>
					<img src="<?php echo Utilities::base64EncodeImage($okt->options->get('modules_dir').'/'.$id.'/Install/Assets/module_icon.png', 'image/png'); ?>" width="32" height="32" alt="" />
				<?php else: ?>
					<img src="<?php echo $okt->options->public_url ?>/img/admin/module.png" width="32" height="32" alt="" />
				<?php endif; ?>
			</td>
			<td class="<?php echo $td_class; ?>">
				<p class="title"><?php _e($module['name']) ?></p>
				<p><?php _e($module['desc']) ?></p>
			</td>
			<td class="<?php echo $td_class; ?> center">
				<p><?php echo $module['version'] ?></p>
			</td>
			<td class="<?php echo $td_class ?> small">
				<ul class="actions">
					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?install=<?php echo $id ?>"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Install_%s'),__($module['name']))) ?>"
					class="icon plugin_add"><?php _e('c_c_action_Install') ?></a></li>

					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?download=<?php echo $id ?>"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Download_%s'),__($module['name']))) ?>"
					class="icon package_go"><?php _e('c_c_action_Download') ?></a></li>

					<li><a href="<?php echo $view->generateUrl('config_modules') ?>?delete=<?php echo $id ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_modules_delete_module_confirm')) ?>')"
					title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Delete_%s'),__($module['name']))) ?>"
					class="icon delete"><?php _e('c_c_action_Delete') ?></a></li>
				</ul>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</div><!-- #tab-uninstalled -->
