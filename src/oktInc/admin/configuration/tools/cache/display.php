<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil gestion du cache (partie affichage)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;

?>

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
		<th class="<?php echo $sTdClass ?> fake-td"><?php echo html::escapeHTML($sFile) ?></th>
		<td class="<?php echo $sTdClass ?>"><?php echo $okt->config->app_path.OKT_INC_DIR.'/'.OKT_CACHE_DIR ?></td>
		<td class="<?php echo $sTdClass ?>"><?php echo dt::str('%A %d %B %Y %H:%M',filemtime(OKT_CACHE_PATH.'/'.$sFile)) ?></td>
		<td class="<?php echo $sTdClass ?> small nowrap">
			<ul class="actions">
				<li>
					<a href="configuration.php?action=tools&amp;cache_file=<?php echo html::escapeHTML($sFile) ?>"
					onclick="return window.confirm('<?php echo html::escapeJS(__('c_a_tools_cache_confirm_delete')) ?>')"
					title="<?php printf(__('c_c_action_Delete_%s'),html::escapeHTML($sFile)) ?>"
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
		<th class="<?php echo $sTdClass ?> fake-td"><?php echo html::escapeHTML($sFile) ?></th>
		<td class="<?php echo $sTdClass ?>"><?php echo $okt->config->app_path.OKT_PUBLIC_DIR.'/cache'?></td>
		<td class="<?php echo $sTdClass ?>"><?php echo dt::str('%A %d %B %Y %H:%M',filemtime(OKT_PUBLIC_PATH.'/cache/'.$sFile)) ?></td>
		<td class="<?php echo $sTdClass ?> small nowrap">
			<ul class="actions">
				<li>
					<a href="configuration.php?action=tools&amp;public_cache_file=<?php echo html::escapeHTML($sFile) ?>"
					onclick="return window.confirm('<?php echo html::escapeJS(__('c_a_tools_cache_confirm_delete')) ?>')"
					title="<?php printf(__('c_c_action_Delete_%s'),html::escapeHTML($sFile)) ?>"
					class="icon delete"><?php _e('c_c_action_Delete') ?></a>
				</li>
			</ul>
		</td>
	</tr>
	<?php $iCountLine++;
	endforeach; ?>
	</tbody>
</table>

<p><a href="configuration.php?action=tools&amp;all_cache_file=1"
onclick="return window.confirm('<?php echo html::escapeJS(__('c_a_tools_cache_confirm_delete_all')) ?>')"
class="icon cross"><?php _e('c_a_tools_cache_delete_all') ?></a></p>
