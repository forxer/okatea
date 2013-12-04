<?php
##header##


# Accès direct interdit
if (!defined('ON_##module_upper_id##_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# initialisation des filtres
$okt->##module_id##->filtersStart('admin');


/* Traitements
----------------------------------------------------------*/

# Ré-initialisation filtres
if (!empty($_GET['init_filters']))
{
	$okt->##module_id##->filters->initFilters();
	http::redirect('module.php?m=##module_id##&action=index');
}

# Switch statut
if (!empty($_GET['switch_status']))
{
	$okt->##module_id##->switchItemStatus($_GET['switch_status']);
	http::redirect('module.php?m=##module_id##&action=index&switched=1');
}

# Traitements par lots
if (!empty($_POST['actions']) && !empty($_POST['items']) && is_array($_POST['items']))
{
	$aItemsId = array_map('intval',$_POST['items']);

	if ($_POST['actions'] == 'show')
	{
		foreach ($aItemsId as $itemId) {
			$okt->##module_id##->setItemStatus($itemId,1);
		}

		http::redirect('module.php?m=##module_id##&action=index&switcheds=1');
	}
	elseif ($_POST['actions'] == 'hide')
	{
		foreach ($aItemsId as $itemId) {
			$okt->##module_id##->setItemStatus($itemId,0);
		}

		http::redirect('module.php?m=##module_id##&action=index&switcheds=1');
	}
	elseif($_POST['actions'] == 'delete')
	{
		foreach ($aItemsId as $itemId) {
			$okt->##module_id##->delItem($itemId);
		}

		http::redirect('module.php?m=##module_id##&action=index&deleteds=1');
	}
}


/* Affichage
----------------------------------------------------------*/

# initialisation des filtres
$aParams = array(
	'visibility' => 2
);
$okt->##module_id##->filters->setParams($aParams);

# création des filtres
$okt->##module_id##->filters->getFilters();

# initialisation de la pagination
$iNumFilteredItems = $okt->##module_id##->getItems($aParams,true);

$oPager = new adminPager($okt->##module_id##->filters->params->page, $iNumFilteredItems, $okt->##module_id##->filters->params->nb_per_page);

$iNumItems = $oPager->getNbPages();

$okt->##module_id##->filters->normalizePage($iNumItems);

$aParams['limit'] = (($okt->##module_id##->filters->params->page-1)*$okt->##module_id##->filters->params->nb_per_page).','.$okt->##module_id##->filters->params->nb_per_page;


# récupération de la liste des éléments filtrés
$rsItems = $okt->##module_id##->getItems($aParams);


# ajout de boutons
$okt->page->addButton('##module_id##BtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_display_filters'),
	'url' 			=> '#',
	'ui-icon' 		=> 'search',
	'active' 		=> $okt->##module_id##->filters->params->show_filters,
	'id'			=> 'filter-control',
	'class'			=> 'button-toggleable'
));

# bouton vers le module côté public
$okt->page->addButton('##module_id##BtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_Show'),
	'url' 			=> html::escapeHTML($okt->##module_id##->config->url),
	'ui-icon' 		=> 'extlink'
));


# Filters control
if ($okt->##module_id##->config->admin_filters_style == 'slide')
{
	# Slide down
	$okt->page->filterControl($okt->##module_id##->filters->params->show_filters);
}
elseif ($okt->##module_id##->config->admin_filters_style == 'dialog')
{
	# Display a UI dialog box
	$okt->page->js->addReady("
		$('#filters-form').dialog({
			title: '".html::escapeJs(__('m_##module_id##_display_filters'))."',
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
$okt->page->checkboxHelper('##module_id##-list','checkboxHelper');


# Confirmations
$okt->page->messages->success('deleted',__('m_##module_id##_confirm_deleted'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('##module_id##BtSt'); ?>

<?php # formulaire des filtres ?>
<form action="module.php" method="get" id="filters-form">
	<fieldset>
		<legend><?php _e('m_##module_id##_display_filters') ?></legend>

		<?php echo $okt->##module_id##->filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><?php echo form::hidden('m','##module_id##') ?>
		<?php echo form::hidden('action','index') ?>
		<input type="submit" name="<?php echo $okt->##module_id##->filters->getFilterSubmitName() ?>" value="<?php _e('c_c_action_display') ?>" />
		<a href="module.php?m=##module_id##&amp;action=index&amp;init_filters=1"><?php _e('c_c_reset_filters') ?></a>
		</p>
	</fieldset>
</form>

<?php if ($rsItems->isEmpty()) : ?>
<p><?php _e('m_##module_id##_there_is_no_item') ?></p>
<?php else : ?>

<form action="module.php" method="post" id="##module_id##-list">
<table class="common">
	<caption><?php _e('m_##module_id##_list_of_items') ?></caption>
	<thead><tr>
		<th scope="col"><?php _e('m_##module_id##_title') ?></th>
		<th scope="col"><?php _e('m_##module_id##_actions') ?></th>
	</tr></thead>
	<tbody>
	<?php $count_line = 0;
	while ($rsItems->fetch()) :
		$td_class = $count_line%2 == 0 ? 'even' : 'odd';
		$count_line++;
	?>
	<tr>
		<th class="<?php echo $td_class ?> fake-td">
			<?php echo form::checkbox(array('items[]'),$rsItems->id) ?>
			<a href="module.php?m=##module_id##&amp;action=edit&amp;item_id=<?php
			echo $rsItems->id ?>"><?php echo html::escapeHTML($rsItems->title) ?></a></th>
		<td class="<?php echo $td_class ?> small">
			<ul class="actions">
			<li>
			<a href="module.php?m=##module_id##&amp;action=index&amp;switch_status=<?php echo $rsItems->id ?>"
			title="<?php printf(__('m_##module_id##_switch_visibility_%s'),html::escapeHTML($rsItems->title)) ?>"
			<?php if ($rsItems->visibility) : ?>
			class="icon tick"><?php _e('m_##module_id##_visible') ?>
			<?php else : ?>
			class="icon cross"><?php _e('m_##module_id##_hidden') ?>
			<?php endif; ?></a>
			</li>

			<li>
			<a href="module.php?m=##module_id##&amp;action=edit&amp;item_id=<?php echo $rsItems->id ?>"
			title="<?php printf(__('m_##module_id##_edit_item_%s'),html::escapeHTML($rsItems->title)) ?>"
			class="icon pencil"><?php _e('c_c_action_Edit') ?></a>
			</li>

			<?php if ($okt->checkPerm('##module_id##_remove')) : ?>
			<li>
			<a href="module.php?m=##module_id##&amp;action=delete&amp;item_id=<?php echo $rsItems->id ?>"
			onclick="return window.confirm('<?php echo html::escapeJS(__('m_##module_id##_confirm_deleting')) ?>')"
			title="<?php printf(__('m_##module_id##_delete_item_%s'),html::escapeHTML($rsItems->title)) ?>"
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
	<?php echo form::hidden('m','##module_id##'); ?>
	<?php echo form::hidden('action','index'); ?>
	<?php echo form::hidden('sended',1); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php echo 'ok'; ?>" /></p></div>
</div>
</form>

<?php if ($iNumItems > 1) : ?>
<ul class="pagination"><?php echo $oPager->getLinks(); ?></ul>
<?php endif; ?>

<?php endif; ?>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>