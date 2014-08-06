<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
?>

<?php if (!empty($aUpdatablesThemes)) : ?>
<div id="tab-updates">
	<h3><?php _e('c_a_themes_new_releases_available') ?></h3>

	<table class="common">
		<caption><?php _e('c_a_themes_list_new_versions_available') ?></caption>
		<thead>
			<tr>
				<th scope="col" class="left"><?php _e('c_c_Name') ?></th>
				<th scope="col" class="center"><?php _e('c_a_themes_repository') ?></th>
				<th scope="col" class="center"><?php _e('c_a_themes_version') ?></th>
				<th scope="col" class="small nowrap"><?php _e('Update') ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
	
	foreach ($aUpdatablesThemes as $updatable)
	:
		$td_class = $line_count % 2 == 0 ? 'even' : 'odd';
		$line_count ++;
		?>
		<tr>
				<th scope="row" class="<?php echo $td_class; ?> fake-td">
			<?php echo $view->escape($updatable['name'])?>
			<?php echo !empty($updatable['info']) ? '<br />'.$view->escape($updatable['info']) : ''; ?>
			</th>
				<td class="<?php echo $td_class; ?> center"><?php echo $view->escape($updatable['repository']) ?></td>
				<td class="<?php echo $td_class; ?> center"><?php echo $view->escape($updatable['version']) ?></td>
				<td class="<?php echo $td_class; ?> small nowrap"><a
					href="<?php echo $view->generateAdminUrl('config_themes') ?>?repository=<?php
		echo urlencode($updatable['repository'])?>&amp;theme=<?php echo urlencode($updatable['id']) ?>"
					class="lazy-load"><?php
		_e('c_c_action_Download')?></a></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
<!-- #tab-updates -->
<?php endif; ?>
