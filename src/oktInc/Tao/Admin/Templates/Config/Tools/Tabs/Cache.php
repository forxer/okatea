
<h3><?php _e('c_a_tools_cache_title') ?></h3>

<table class="common">
	<thead><tr>
		<th scope="col"><?php _e('c_a_tools_cache_filename') ?></th>
		<th scope="col"><?php _e('c_a_tools_cache_dirname') ?></th>
		<th scope="col"><?php _e('c_a_tools_cache_last_modification') ?></th>
		<th scope="col"><?php _e('c_c_Actions') ?></th>
	</tr></thead>
	<tbody>
	<?php $iCountLine = 0;
	foreach ($aCacheFiles as $sFile) :
		$sTdClass = $iCountLine%2 == 0 ? 'even' : 'odd'; ?>
	<tr>
		<th class="<?php echo $sTdClass ?> fake-td"><?php echo $view->escape($sFile) ?></th>
		<td class="<?php echo $sTdClass ?>"><?php echo $okt->config->app_path.basename($okt->options->get('inc_dir')).'/'.basename($okt->options->get('cache_dir')) ?></td>
		<td class="<?php echo $sTdClass ?>"><?php echo dt::str('%A %d %B %Y %H:%M',filemtime($okt->options->get('cache_dir').'/'.$sFile)) ?></td>
		<td class="<?php echo $sTdClass ?> small nowrap">
			<ul class="actions">
				<li>
					<a href="<?php echo $view->generateUrl('config_tools') ?>?cache_file=<?php echo $view->escape($sFile) ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_tools_cache_confirm_delete')) ?>')"
					title="<?php printf(__('c_c_action_Delete_%s'), $view->escape($sFile)) ?>"
					class="icon delete"><?php _e('c_c_action_Delete') ?></a>
				</li>
			</ul>
		</td>
	</tr>
	<?php $iCountLine++;
	endforeach; ?>

	<?php foreach ($aPublicCacheFiles as $sFile) :
		$sTdClass = $iCountLine%2 == 0 ? 'even' : 'odd'; ?>
	<tr>
		<th class="<?php echo $sTdClass ?> fake-td"><?php echo $view->escape($sFile) ?></th>
		<td class="<?php echo $sTdClass ?>"><?php echo $okt->config->app_path.basename($okt->options->get('public_dir')).'/cache'?></td>
		<td class="<?php echo $sTdClass ?>"><?php echo dt::str('%A %d %B %Y %H:%M',filemtime($okt->options->public_dir.'/cache/'.$sFile)) ?></td>
		<td class="<?php echo $sTdClass ?> small nowrap">
			<ul class="actions">
				<li>
					<a href="<?php echo $view->generateUrl('config_tools') ?>?public_cache_file=<?php echo $view->escape($sFile) ?>"
					onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_tools_cache_confirm_delete')) ?>')"
					title="<?php printf(__('c_c_action_Delete_%s'),$view->escape($sFile)) ?>"
					class="icon delete"><?php _e('c_c_action_Delete') ?></a>
				</li>
			</ul>
		</td>
	</tr>
	<?php $iCountLine++;
	endforeach; ?>
	</tbody>
</table>

<p><a href="<?php echo $view->generateUrl('config_tools') ?>?all_cache_file=1"
onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_tools_cache_confirm_delete_all')) ?>')"
class="icon cross"><?php _e('c_a_tools_cache_delete_all') ?></a></p>
