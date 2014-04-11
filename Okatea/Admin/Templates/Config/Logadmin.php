<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\LogAdmin;
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Dates;

$view->extend('layout');

# button set
$okt->page->setButtonset('logsBtSt',array(
	'id' => 'logs-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_display_filters'),
			'url' 			=> '#',
			'ui-icon' 		=> 'search',
			'active' 		=> $okt->logAdmin->filters->params->show_filters,
			'id'			=> 'filter-control',
			'class'			=> 'button-toggleable'
		)
	)
));

# Filters control
if ($okt->logAdmin->oConfig->admin_filters_style == 'slide')
{
	# Slide down
	$okt->page->filterControl($okt->logAdmin->filters->params->show_filters);
}
elseif ($okt->logAdmin->oConfig->admin_filters_style == 'dialog')
{
	# Display a UI dialog box
	$okt->page->js->addReady("
		$('#filters-form').dialog({
			title: '".$view->escapeJs(__('c_a_config_logadmin_display_filters'))."',
			autoOpen: false,
			modal: true,
			width: 600,
			height: 350
		});

		$('#filter-control').click(function(){
			$('.datepicker').datepicker('disable');
			$('#filters-form').dialog('open');
			$('.datepicker').datepicker('enable');
			return false;
		});
	");
}

# Datepicker
$okt->page->datePicker();

# Tableau des codes de logs
$aLogAdminCodes = LogAdmin::getCodes();

# Infos page
$okt->page->addGlobalTitle(__('c_a_config_logadmin_title'));

?>

<?php echo $okt->page->getButtonSet('logsBtSt'); ?>

<?php # formulaire des filtres ?>
<form action="<?php $view->generateUrl('config_logadmin') ?>" method="get" id="filters-form">
	<fieldset>
		<legend><?php _e('c_a_config_logadmin_display_filters')?></legend>

		<?php echo $okt->logAdmin->filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><input type="submit" name="<?php echo $okt->logAdmin->filters->getFilterSubmitName() ?>" value="<?php _e('c_c_action_display')?>" />
		<a href="<?php $view->generateUrl('config_logadmin') ?>?init_filters=1"><?php _e('c_c_reset_filters')?></a></p>
	</fieldset>
</form>

<?php if ($rsLogAdmin->isEmpty()) : ?>
<p><em><?php _e('c_a_config_logadmin_no_log') ?></em></p>

<?php else : ?>

<table id="log-admin-list" class="common">
	<caption>Liste des log admin</caption>
	<thead><tr>
		<th scope="col"><?php _e('c_a_config_logadmin_th_type') ?></th>
		<th scope="col"><?php _e('c_a_config_logadmin_th_user') ?></th>
		<th scope="col"><?php _e('c_a_config_logadmin_th_ip') ?></th>
		<th scope="col"><?php _e('c_a_config_logadmin_th_date') ?></th>
		<th scope="col"><?php _e('c_a_config_logadmin_th_component') ?></th>
		<th scope="col" colspan="2"><?php _e('c_a_config_logadmin_th_action') ?></th>
	</tr></thead>
	<tbody>
	<?php while($rsLogAdmin->fetch()) :?>
	<tr class="type_<?php echo $rsLogAdmin->type ?>">
		<td><?php echo LogAdmin::getHtmlType($rsLogAdmin->type) ?></td>
		<td><?php echo $rsLogAdmin->user_id ?> - <?php echo $rsLogAdmin->username ?></td>
		<td><?php echo $rsLogAdmin->ip ?></td>
		<td><?php echo Dates::full($rsLogAdmin->date, true) ?></td>
		<td><?php echo $rsLogAdmin->component ?></td>
		<td><?php echo $rsLogAdmin->code.' - '.$aLogAdminCodes[$rsLogAdmin->code] ?></td>
		<td><?php echo $rsLogAdmin->message ?></td>
	</tr>
	<?php endwhile; ?>
	</tbody>
</table>

<?php if ($iNumPages > 1) : ?>
<ul class="pagination"><?php echo $oPager->getLinks(); ?></ul>
<?php endif; ?>


<p><a href="<?php $view->generateUrl('config_logadmin') ?>?truncate=1" class="icon cross" onclick="return window.confirm('<?php
echo $view->escapeJs(__('c_a_config_logadmin_confirm_truncate')) ?>')"><?php _e('c_a_config_logadmin_truncate') ?></a></p>


<div class="checklistlegend">
	<p><?php _e('c_c_checklist_legend') ?></p>
	<ul>
	<?php foreach ($aLogAdminCodes as $iCode=>$sCode) : ?>
		<li><?php echo $iCode.' : '.$sCode?></li>
	<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>
