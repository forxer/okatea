<?php
/**
 * @ingroup okt_module_diary
 * @brief
 *
 */

use Tao\Admin\Page;
use Tao\Admin\Pager;
use Tao\Forms\Statics\FormElements as form;


# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# initialisation des filtres
$okt->diary->filtersStart('admin');


/* Traitements
----------------------------------------------------------*/

# Ré-initialisation filtres
if (!empty($_GET['init_filters']))
{
	$okt->diary->filters->initFilters();
	http::redirect('module.php?m=diary&action=index');
}

# Switch statut
if (!empty($_GET['switch_status']))
{
	$okt->diary->switchEventStatus($_GET['switch_status']);
	http::redirect('module.php?m=diary&action=index');
}

# Traitements par lots
if (!empty($_POST['actions']) && !empty($_POST['events']) && is_array($_POST['events']))
{
	$aEventsId = array_map('intval',$_POST['events']);

	if ($_POST['actions'] == 'show')
	{
		foreach ($aEventsId as $iEventId) {
			$okt->diary->setEventStatus($iEventId,1);
		}

		http::redirect('module.php?m=diary&action=index');
	}
	elseif ($_POST['actions'] == 'hide')
	{
		foreach ($aEventsId as $iEventId) {
			$okt->diary->setEventStatus($iEventId,0);
		}

		http::redirect('module.php?m=diary&action=index');
	}
	elseif($_POST['actions'] == 'delete')
	{
		foreach ($aEventsId as $iEventId) {
			$okt->diary->delEvent($iEventId);
		}

		http::redirect('module.php?m=diary&action=index');
	}
}


/* Affichage
----------------------------------------------------------*/

# initialisation des filtres
$aParams = array(
	'visibility' => 2
);
$okt->diary->filters->setParams($aParams);

# création des filtres
$okt->diary->filters->getFilters();

# initialisation de la pagination
$iNumFilteredEvents = $okt->diary->getEvents($aParams,true);

$oPager = new Pager($okt, $okt->diary->filters->params->page, $iNumFilteredEvents, $okt->diary->filters->params->nb_per_page);

$iNumEvents = $oPager->getNbPages();

$okt->diary->filters->normalizePage($iNumEvents);

$aParams['limit'] = (($okt->diary->filters->params->page-1)*$okt->diary->filters->params->nb_per_page).','.$okt->diary->filters->params->nb_per_page;


# récupération de la liste des éléments filtrés
$rsEvents = $okt->diary->getEvents($aParams);


# ajout de boutons
$okt->page->addButton('diaryBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_display_filters'),
	'url' 			=> '#',
	'ui-icon' 		=> 'search',
	'active' 		=> $okt->diary->filters->params->show_filters,
	'id'			=> 'filter-control',
	'class'			=> 'button-toggleable'
));

# bouton vers le module côté public
$okt->page->addButton('diaryBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_Show'),
	'url' 			=> html::escapeHTML(DiaryHelpers::getDiaryUrl()),
	'ui-icon' 		=> 'extlink'
));


# Filters control
if ($okt->diary->config->admin_filters_style == 'slide')
{
	# Slide down
	$okt->page->filterControl($okt->diary->filters->params->show_filters);
}
elseif ($okt->diary->config->admin_filters_style == 'dialog')
{
	# Display a UI dialog box
	$okt->page->js->addReady("
		$('#filters-form').dialog({
			title: '".html::escapeJs(__('m_diary_display_filters'))."',
			autoOpen: false,
			modal: true,
			width: 500,
			height: 280
		});

		$('#filter-control').click(function() {
			$('#filters-form').dialog('open');
		})
	");
}

# tableau de choix d'actions pour le traitement par lot
$aActionsChoices = array(
	__('c_c_action_display') => 'show',
	__('c_c_action_hide') => 'hide',
	__('c_c_action_delete') => 'delete'
);

# checkboxes helper
$okt->page->checkboxHelper('diary-list','checkboxHelper');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('diaryBtSt'); ?>

<?php # formulaire des filtres ?>
<form action="module.php" method="get" id="filters-form">
	<fieldset>
		<legend><?php _e('m_diary_display_filters') ?></legend>

		<?php echo $okt->diary->filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><?php echo form::hidden('m','diary') ?>
		<?php echo form::hidden('action','index') ?>
		<input type="submit" name="<?php echo $okt->diary->filters->getFilterSubmitName() ?>" value="<?php _e('c_c_action_display') ?>" />
		<a href="module.php?m=diary&amp;action=index&amp;init_filters=1"><?php _e('c_c_reset_filters') ?></a>
		</p>
	</fieldset>
</form>

<?php if ($rsEvents->isEmpty()) : ?>
<p><?php _e('m_diary_there_is_no_event') ?></p>
<?php else : ?>

<form action="module.php" method="post" id="diary-list">
<table class="common">
	<caption><?php _e('m_diary_list_of_events') ?></caption>
	<thead><tr>
		<th scope="col"><?php _e('m_diary_title') ?></th>
		<th scope="col"><?php _e('m_diary_date') ?></th>
		<th scope="col"><?php _e('m_diary_date_end') ?></th>
		<th scope="col"><?php _e('m_diary_actions') ?></th>
	</tr></thead>
	<tbody>
	<?php $count_line = 0;
	while ($rsEvents->fetch()) :
		$td_class = $count_line%2 == 0 ? 'even' : 'odd';
		$count_line++;
	?>
	<tr>
		<th class="<?php echo $td_class ?> fake-td">
			<?php echo form::checkbox(array('events[]'),$rsEvents->id) ?>
			<a href="module.php?m=diary&amp;action=edit&amp;event_id=<?php
			echo $rsEvents->id ?>"><?php echo html::escapeHTML($rsEvents->title) ?></a></th>
		<td class="<?php echo $td_class ?>">
			<?php echo dt::dt2str(__('%Y-%m-%d'),$rsEvents->date) ?>
		</td>
		<td class="<?php echo $td_class ?>">
			<?php echo (!empty($rsEvents->date_end) ? dt::dt2str(__('%Y-%m-%d'),$rsEvents->date_end) : '') ?>
		</td>
		<td class="<?php echo $td_class ?> small">
			<ul class="actions">
			<li>
			<?php if ($rsEvents->visibility) : ?>
			<a href="module.php?m=diary&amp;action=index&amp;switch_status=<?php echo $rsEvents->id ?>"
			title="<?php printf(__('m_diary_switch_visibility_%s'),html::escapeHTML($rsEvents->title)) ?>"
			class="icon tick"><?php _e('m_diary_visible') ?></a>
			<?php else : ?>
			<a href="module.php?m=diary&amp;action=index&amp;switch_status=<?php echo $rsEvents->id ?>"
			title="<?php printf(__('m_diary_switch_visibility_%s'),html::escapeHTML($rsEvents->title)) ?>"
			class="icon cross"><?php _e('m_diary_hidden') ?></a>
			<?php endif; ?>
			</li>

			<li>
			<a href="module.php?m=diary&amp;action=edit&amp;event_id=<?php echo $rsEvents->id ?>"
			title="<?php printf(__('m_diary_edit_event_%s'),html::escapeHTML($rsEvents->title)) ?>"
			class="icon pencil"><?php _e('c_c_action_Edit') ?></a>
			</li>

			<?php if ($okt->checkPerm('diary_remove')) : ?>
			<li>
			<a href="module.php?m=diary&amp;action=delete&amp;event_id=<?php echo $rsEvents->id ?>"
			onclick="return window.confirm('<?php echo html::escapeJS(__('m_diary_confirm_deleting')) ?>')"
			title="<?php printf(__('m_diary_delete_event_%s'),html::escapeHTML($rsEvents->title)) ?>"
			class="icon delete"><?php _e('c_c_action_Delete') ?></a>
			</li>
			<?php endif; ?>
			</ul>
		</td>
	</tr>
	<?php endwhile; ?>
	</tbody>
</table>

<div class="two-cols">
	<div class="col">
		<p id="checkboxHelper"></p>
	</div>
	<div class="col right"><p>Action sur les éléments sélectionnées&nbsp;:
	<?php echo form::select('actions',$aActionsChoices) ?>
	<?php echo form::hidden('m','diary'); ?>
	<?php echo form::hidden('action','index'); ?>
	<?php echo form::hidden('sended',1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php echo 'ok'; ?>" /></p></div>
</div>
</form>

<?php if ($iNumEvents > 1) : ?>
<ul class="pagination"><?php echo $oPager->getLinks(); ?></ul>
<?php endif; ?>

<?php endif; ?>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>