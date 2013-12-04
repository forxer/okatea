<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page de log de l'administration pour les superadmin
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Locales
l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.logadmin');

# Filtres
$okt->logAdmin->filtersStart();


/* Traitements
----------------------------------------------------------*/

# Suppression automatique des logs
$okt->logAdmin->deleteLogsDate($okt->config->log_admin['ttl_months']);

# Suppression manuelle des logs
if (!empty($_GET['truncate']) && $okt->user->is_superadmin)
{
	$okt->logAdmin->deleteLogs();

	$okt->page->flashMessages->addSuccess(__('c_a_config_logadmin_truncated'));

	http::redirect('configuration.php?action=logadmin');
}

# Ré-initialisation filtres
if (!empty($_GET['init_filters']))
{
	$okt->logAdmin->filters->initFilters();
	http::redirect('configuration.php?action=logadmin');
}


/* Affichage
----------------------------------------------------------*/

# Initialisation des filtres
$aParams = array();
$okt->logAdmin->filters->setLogsParams($aParams);

# Création des filtres
$okt->logAdmin->filters->getFilters();

# Initialisation de la pagination
$oPager = new adminPager($okt->logAdmin->filters->params->page, $okt->logAdmin->getLogs($aParams,true), $okt->logAdmin->filters->params->nb_per_page);
$iNumPages = $oPager->getNbPages();
$okt->logAdmin->filters->normalizePage($iNumPages);
$aParams['limit'] = (($okt->logAdmin->filters->params->page-1)*$okt->logAdmin->filters->params->nb_per_page).','.$okt->logAdmin->filters->params->nb_per_page;

# Récupération des logs
$rsLogAdmin = $okt->logAdmin->getLogs($aParams);


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
			title: '".html::escapeJS(__('c_a_config_logadmin_display_filters'))."',
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
$aLogAdminCodes = Okatea\Core\LogAdmin::getCodes();

# Infos page
$okt->page->addGlobalTitle(__('c_a_config_logadmin_title'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php if ($rsLogAdmin->isEmpty()) : ?>
<p><em><?php _e('c_a_config_logadmin_no_log') ?></em></p>

<?php else : ?>

<?php echo $okt->page->getButtonSet('logsBtSt'); ?>

<?php # formulaire des filtres ?>
<form action="configuration.php?action=logadmin" method="get" id="filters-form">
	<fieldset>
		<legend><?php _e('c_a_config_logadmin_display_filters')?></legend>

		<?php echo $okt->logAdmin->filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><?php echo form::hidden('action','logadmin') ?>
		<input type="submit" name="<?php echo $okt->logAdmin->filters->getFilterSubmitName() ?>" value="<?php _e('c_c_action_display')?>" />
		<a href="configuration.php?action=logadmin&amp;init_filters=1"><?php _e('c_c_reset_filters')?></a>
		</p>
	</fieldset>
</form>

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
		<td><?php echo Okatea\Core\LogAdmin::getHtmlType($rsLogAdmin->type) ?></td>
		<td><?php echo $rsLogAdmin->user_id ?> - <?php echo $rsLogAdmin->username ?></td>
		<td><?php echo $rsLogAdmin->ip ?></td>
		<td><?php echo dt::dt2str(__('%A, %B %d, %Y, %H:%M'), $rsLogAdmin->date) ?></td>
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

<?php if ($okt->user->is_superadmin) : ?>
<p><a href="configuration.php?action=logadmin&amp;truncate=1" class="icon cross" onclick="return window.confirm('<?php
echo html::escapeJS(__('c_a_config_logadmin_confirm_truncate')) ?>')"><?php _e('c_a_config_logadmin_truncate') ?></a></p>
<?php endif; ?>


<div class="checklistlegend">
	<p><?php _e('c_c_checklist_legend') ?></p>
	<ul>
	<?php foreach ($aLogAdminCodes as $iCode=>$sCode) : ?>
		<li><?php echo $iCode.' : '.$sCode?></li>
	<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
