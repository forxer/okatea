<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\L10n\DateTime;
use Okatea\Tao\Logger\LogAdmin;
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

# button set
$okt->page->setButtonset('logsBtSt', array(
	'id' => 'logs-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_c_display_filters'),
			'url' => '#',
			'ui-icon' => 'search',
			'active' => $okt['logAdmin']->filters->params->show_filters,
			'id' => 'filter-control',
			'class' => 'button-toggleable'
		)
	)
));

# Filters control
if ($okt['logAdmin']->oConfig->admin_filters_style == 'slide')
{
	# Slide down
	$okt->page->filterControl($okt['logAdmin']->filters->params->show_filters);
}
elseif ($okt['logAdmin']->oConfig->admin_filters_style == 'dialog')
{
	# Display a UI dialog box
	$okt->page->js->addReady("
		$('#filters-form').dialog({
			title: '" . $view->escapeJs(__('c_a_config_logadmin_display_filters')) . "',
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
<form action="<?php $view->generateAdminUrl('config_logadmin') ?>"
	method="get" id="filters-form">
	<fieldset>
		<legend><?php _e('c_a_config_logadmin_display_filters')?></legend>

		<?php echo $okt['logAdmin']->filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p>
			<input type="submit"
				name="<?php echo $okt['logAdmin']->filters->getFilterSubmitName() ?>"
				value="<?php _e('c_c_action_display')?>" /> <a
				href="<?php $view->generateAdminUrl('config_logadmin') ?>?init_filters=1"><?php
				_e('c_c_reset_filters')?></a>
		</p>
	</fieldset>
</form>

<?php if (empty($aLogAdmin)) : ?>
<p>
	<em><?php _e('c_a_config_logadmin_no_log') ?></em>
</p>

<?php else : ?>

<table id="log-admin-list" class="common">
	<caption>Liste des log admin</caption>
	<thead>
		<tr>
			<th scope="col"><?php _e('c_a_config_logadmin_th_type') ?></th>
			<th scope="col"><?php _e('c_a_config_logadmin_th_user') ?></th>
			<th scope="col"><?php _e('c_a_config_logadmin_th_ip') ?></th>
			<th scope="col"><?php _e('c_a_config_logadmin_th_date') ?></th>
			<th scope="col"><?php _e('c_a_config_logadmin_th_component') ?></th>
			<th scope="col" colspan="2"><?php _e('c_a_config_logadmin_th_action') ?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($aLogAdmin as $aLog) :?>
	<tr class="type_<?php echo $aLog['type'] ?>">
			<td><?php echo LogAdmin::getHtmlType($aLog['type']) ?></td>
			<td><?php echo $aLog['user_id'] ?> - <?php echo $aLog['username'] ?></td>
			<td><?php echo $aLog['ip'] ?></td>
			<td><?php echo DateTime::full($aLog['date']) ?></td>
			<td><?php echo $aLog['component'] ?></td>
			<td><?php echo $aLog['code'].' - '.$aLogAdminCodes[$aLog['code']] ?></td>
			<td><?php echo $aLog['message'] ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php if ($iNumPages > 1) : ?>
<ul class="pagination"><?php echo $oPager->getLinks(); ?></ul>
<?php endif; ?>


<p>
	<a href="<?php $view->generateAdminUrl('config_logadmin') ?>?truncate=1"
		class="icon cross"
		onclick="return window.confirm('<?php
	echo $view->escapeJs(__('c_a_config_logadmin_confirm_truncate'))?>')"><?php _e('c_a_config_logadmin_truncate') ?></a>
</p>


<div class="checklistlegend">
	<p><?php _e('c_c_checklist_legend') ?></p>
	<ul>
	<?php foreach ($aLogAdminCodes as $iCode=>$sCode) : ?>
		<li><?php echo $iCode.' : '.$sCode?></li>
	<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>
