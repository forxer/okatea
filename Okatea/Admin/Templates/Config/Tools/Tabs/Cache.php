<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\L10n\DateTime;

?>

<h3><?php _e('c_a_tools_cache_title') ?></h3>

<table class="common">
	<thead>
		<tr>
			<th scope="col"><?php _e('c_a_tools_cache_filename') ?></th>
			<th scope="col"><?php _e('c_a_tools_cache_dirname') ?></th>
			<th scope="col"><?php _e('c_a_tools_cache_last_modification') ?></th>
			<th scope="col"><?php _e('c_c_Actions') ?></th>
		</tr>
	</thead>
	<tbody>
	<?php

	$iCountLine = 0;
	foreach ($oCacheFiles as $oFileInfo) :
		$sTdClass = $iCountLine % 2 == 0 ? 'even' : 'odd';
	?>
	<tr>
			<th class="<?php echo $sTdClass ?> fake-td"><?php echo $view->escape($oFileInfo->getRelativePathname()) ?></th>
			<td class="<?php echo $sTdClass ?>"><?php echo $okt['app_url'].basename($okt['okt_path']).'/'.basename($okt['cache_path']) ?></td>
			<td class="<?php echo $sTdClass ?>"><?php echo DateTime::full($oFileInfo->getMTime()) ?></td>
			<td class="<?php echo $sTdClass ?> small nowrap">
				<ul class="actions">
					<li><a
						href="<?php echo $view->generateUrl('config_tools') ?>?cache_file=<?php echo $view->escape($oFileInfo->getRelativePathname()) ?>"
						onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_tools_cache_confirm_delete')) ?>')"
						title="<?php printf(__('c_c_action_Delete_%s'), $view->escape($oFileInfo->getRelativePathname())) ?>"
						class="icon delete"><?php _e('c_c_action_Delete') ?></a></li>
				</ul>
			</td>
		</tr>
	<?php
		$iCountLine ++;
	endforeach; ?>

	<?php foreach ($oPublicCacheFiles as $oFileInfo) :
		$sTdClass = $iCountLine % 2 == 0 ? 'even' : 'odd';
	?>
	<tr>
			<th class="<?php echo $sTdClass ?> fake-td"><?php echo $view->escape($oFileInfo->getRelativePathname()) ?></th>
			<td class="<?php echo $sTdClass ?>"><?php echo $okt['app_url'].basename($okt['public_path']).'/cache'?></td>
			<td class="<?php echo $sTdClass ?>"><?php echo DateTime::full($oFileInfo->getMTime()) ?></td>
			<td class="<?php echo $sTdClass ?> small nowrap">
				<ul class="actions">
					<li><a
						href="<?php echo $view->generateUrl('config_tools') ?>?public_cache_file=<?php echo $view->escape($oFileInfo->getRelativePathname()) ?>"
						onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_tools_cache_confirm_delete')) ?>')"
						title="<?php printf(__('c_c_action_Delete_%s'),$view->escape($oFileInfo->getRelativePathname())) ?>"
						class="icon delete"><?php _e('c_c_action_Delete') ?></a></li>
				</ul>
			</td>
		</tr>
	<?php
		$iCountLine ++;
	endforeach; ?>
	</tbody>
</table>

<p>
	<a
		href="<?php echo $view->generateUrl('config_tools') ?>?all_cache_file=1"
		onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_tools_cache_confirm_delete_all')) ?>')"
		class="icon cross"><?php _e('c_a_tools_cache_delete_all') ?></a>
</p>
