<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil gestion MySQL (partie affichage)
 *
 * @addtogroup Okatea
 *
 */

use Tao\Utils as util;


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


$rs = $okt->db->select('SELECT VERSION() AS db_version');
$db_version = $rs->db_version;
unset($rs);

if ($table) {
	$table_infos = $okt->db->select('SHOW FULL COLUMNS FROM `'.$okt->db->escapeStr($table).'`');
}

$db_infos = $okt->db->select('SHOW TABLE STATUS FROM `'.$okt->db->escapeStr(OKT_DB_NAME).'`');

$num_tables = $num_rows = $db_size = $db_pertes = 0;
while ($db_infos->fetch())
{
	$num_tables++;
	$num_rows += $db_infos->rows;
	$db_size += $db_infos->data_length + $db_infos->index_length;
	$db_pertes += $db_infos->data_free;
}

?>

<h3><?php _e('c_a_infos_mysql_title') ?></h3>
<ul>
	<li><?php _e('c_a_infos_mysql_version') ?> <?php echo $db_version ?></li>
	<li><?php _e('c_a_infos_mysql_tables') ?> <?php echo $num_tables ?></li>
	<li><?php _e('c_a_infos_mysql_rows') ?> <?php echo $num_rows ?></li>
	<li><?php _e('c_a_infos_mysql_size') ?> <?php echo util::l10nFileSize($db_size) ?></li>
</ul>

<?php if ($table) : ?>
<h4><?php printf(__('c_a_infos_mysql_table_info_%s'),html::escapeHTML($table)) ?></h4>

<p><a href="configuration.php?action=infos" class="icon arrow_undo"><?php _e('c_c_action_Go_back') ?></a></p>

<table class="common">
	<thead>
	<tr>
		<th scope="col"><?php _e('c_a_infos_mysql_th_field') ?></th>
		<th scope="col"><?php _e('c_a_infos_mysql_th_type') ?></th>
		<th scope="col"><?php _e('c_a_infos_mysql_th_collation') ?></th>
		<th scope="col"><?php _e('c_a_infos_mysql_th_null') ?></th>
		<th scope="col"><?php _e('c_a_infos_mysql_th_default') ?></th>
		<th scope="col"><?php _e('c_a_infos_mysql_th_extra') ?></th>
	</tr>
	</thead>
	<tbody>
	<?php $count_line = 0;
	while ($table_infos->fetch()) :
		$td_class = $count_line%2 == 0 ? 'even' : 'odd'; ?>
	<tr>
		<th scope="row" class="<?php echo $td_class ?> fake-td"><?php echo $table_infos->field ?></th>
		<td class="<?php echo $td_class ?>"><?php echo $table_infos->type ?></td>
		<td class="<?php echo $td_class ?>"><?php echo $table_infos->collation ?></td>
		<td class="<?php echo $td_class ?>"><?php echo $table_infos->null ?></td>
		<td class="<?php echo $td_class ?>"><?php echo $table_infos->default ?></td>
		<td class="<?php echo $td_class ?>"><?php echo $table_infos->extra ?></td>
	</tr>
	<?php $count_line++;
	endwhile; ?>
	</tbody>
</table>
<?php else :?>
<h4><?php _e('c_a_infos_mysql_tables_info') ?></h4>

<table class="common">
	<thead>
	<tr>
		<th scope="col"><?php _e('c_a_infos_mysql_th_name') ?></th>
		<th scope="col"><?php _e('c_a_infos_mysql_th_engine') ?></th>
		<th scope="col"><?php _e('c_a_infos_mysql_th_collation') ?></th>
		<th scope="col"><?php _e('c_a_infos_mysql_th_update_time') ?></th>
		<th scope="col"><?php _e('c_a_infos_mysql_th_rows') ?></th>
		<th scope="col"><?php _e('c_a_infos_mysql_th_size') ?></th>
		<th scope="col"><?php _e('c_a_infos_mysql_th_data_free') ?></th>
		<th scope="col">&nbsp;</th>
	</tr>
	</thead>
	<tbody>
	<?php $count_line = 0;
	while ($db_infos->fetch()) :
		$td_class = $count_line%2 == 0 ? 'even' : 'odd'; ?>
	<tr>
		<th scope="row" class="<?php echo $td_class ?> fake-td"><a href="configuration.php?action=infos&amp;table=<?php echo $db_infos->name ?>"><?php echo $db_infos->name ?></a></th>
		<td class="<?php echo $td_class ?>"><?php echo $db_infos->engine ?></td>
		<td class="<?php echo $td_class ?>"><?php echo $db_infos->collation ?></td>
		<td class="<?php echo $td_class ?>"><?php echo $db_infos->update_time ?></td>
		<td class="<?php echo $td_class ?>"><?php echo $db_infos->rows ?></td>
		<td class="<?php echo $td_class ?>"><?php echo util::l10nFileSize($db_infos->data_length + $db_infos->index_length) ?></td>
		<td class="<?php echo $td_class ?>"><?php echo util::l10nFileSize($db_infos->data_free) ?></td>
		<td class="<?php echo $td_class ?>">
			<ul class="actions">
				<li>
					<a href="configuration.php?action=infos&amp;optimize=<?php echo $db_infos->name ?>"
					title="<?php printf(__('c_a_infos_mysql_optimize_%s'),$db_infos->name) ?>"
					class="icon database_refresh"><?php _e('c_a_infos_mysql_optimize') ?></a>
				</li>
				<li>
					<a href="configuration.php?action=infos&amp;truncate=<?php echo $db_infos->name ?>"
					onclick="return window.confirm('<?php echo html::escapeJS(__('c_a_infos_mysql_confirm_empty')) ?>')"
					title="<?php printf(__('c_a_infos_mysql_empty_%s'),$db_infos->name) ?>"
					class="icon database_lightning"><?php _e('c_a_infos_mysql_empty') ?></a>
				</li>
				<li>
					<a href="configuration.php?action=infos&amp;drop=<?php echo $db_infos->name ?>"
					onclick="return window.confirm('<?php echo html::escapeJS(__('c_a_infos_mysql_confirm_delete')) ?>')"
					title="<?php printf(__('c_c_action_Delete_%s'), $db_infos->name) ?>"
					class="icon database_delete"><?php _e('c_c_action_Delete') ?></a>
				</li>
			</ul>
		</td>
	</tr>
	<?php $count_line++;
	endwhile; ?>
	</tbody>
	<tfoot>
	<tr>
		<td colspan="4">&nbsp;</td>
		<td><?php echo $num_rows ?></td>
		<td><?php echo util::l10nFileSize($db_size) ?></td>
		<td><?php echo util::l10nFileSize($db_pertes) ?></td>
		<td>&nbsp;</td>
	</tr>
	</tfoot>
</table>
<?php endif; ?>
