<?php
/**
 * @ingroup okt_module_estimate
 * @brief La page d'administration des demandes de devis
 *
 */
use Okatea\Admin\Page;
use Okatea\Admin\Pager;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE'))
	die();
	
	/* Initialisations
----------------------------------------------------------*/
	
# Chargement des locales
$okt['l10n']->loadFile(__DIR__ . '/../Locales/%s/admin.list');

# initialisation des filtres
$okt->estimate->filtersStart('admin');

/* Traitements
----------------------------------------------------------*/

# Ré-initialisation filtres
if (!empty($_GET['init_filters']))
{
	$okt->estimate->filters->initFilters();
	http::redirect('module.php?m=estimate&action=index');
}

# Marque une demande comme traitée
if (!empty($_GET['treated']))
{
	try
	{
		$okt->estimate->markAsTreated($_GET['treated']);
		
		# log admin
		$okt['logAdmin']->info(array(
			'code' => 32,
			'component' => 'estimate',
			'message' => 'estimate #' . $_GET['treated']
		));
		
		$okt['flashMessages']->success(__('m_estimate_list_marked_as_treated'));
		
		http::redirect('module.php?m=estimate&action=index');
	}
	catch (\Exception $e)
	{
		$okt->error->set($e->getMessage());
	}
}

# Marque une demande comme non traitée
if (!empty($_GET['untreated']))
{
	try
	{
		$okt->estimate->markAsUntreated($_GET['untreated']);
		
		# log admin
		$okt['logAdmin']->info(array(
			'code' => 32,
			'component' => 'estimate',
			'message' => 'estimate #' . $_GET['untreated']
		));
		
		$okt['flashMessages']->success(__('m_estimate_list_marked_as_untreated'));
		
		http::redirect('module.php?m=estimate&action=index');
	}
	catch (\Exception $e)
	{
		$okt->error->set($e->getMessage());
	}
}

# Traitements par lots
if (!empty($_POST['actions']) && !empty($_POST['estimates']) && is_array($_POST['estimates']))
{
	$aEstimatesIds = array_map('intval', $_POST['estimates']);
	
	try
	{
		if ($_POST['actions'] == 'treateds')
		{
			foreach ($aEstimatesIds as $iEstimateId)
			{
				$okt->estimate->markAsTreated($iEstimateId);
				
				# log admin
				$okt['logAdmin']->info(array(
					'code' => 30,
					'component' => 'estimate',
					'message' => 'estimate #' . $iEstimateId
				));
			}
			
			$okt['flashMessages']->success(__('m_estimate_list_marked_as_treateds'));
			
			http::redirect('module.php?m=estimate&action=index');
		}
		elseif ($_POST['actions'] == 'untreateds')
		{
			foreach ($aEstimatesIds as $iEstimateId)
			{
				$okt->estimate->markAsUntreated($iEstimateId);
				
				# log admin
				$okt['logAdmin']->info(array(
					'code' => 31,
					'component' => 'estimate',
					'message' => 'estimate #' . $iEstimateId
				));
			}
			
			$okt['flashMessages']->success(__('m_estimate_list_marked_as_untreateds'));
			
			http::redirect('module.php?m=estimate&action=index');
		}
		elseif ($_POST['actions'] == 'delete')
		{
			foreach ($aEstimatesIds as $iEstimateId)
			{
				$okt->estimate->deleteEstimate($iEstimateId);
				
				# log admin
				$okt['logAdmin']->warning(array(
					'code' => 42,
					'component' => 'estimate',
					'message' => 'estimate #' . $iEstimateId
				));
			}
			
			$okt['flashMessages']->success(__('m_estimate_list_deleteds'));
			
			http::redirect('module.php?m=estimate&action=index');
		}
	}
	catch (\Exception $e)
	{
		$okt->error->set($e->getMessage());
	}
}

/* Affichage
----------------------------------------------------------*/

# Initialisation des filtres
$aParams = [];
$okt->estimate->filters->setEstimatesParams($aParams);

# Création des filtres
$okt->estimate->filters->getFilters();

# Initialisation de la pagination
$iNumFilteredEstimates = $okt->estimate->getEstimatesCount($aParams);

$oPager = new Pager($okt, $okt->estimate->filters->params->page, $iNumFilteredEstimates, $okt->estimate->filters->params->nb_per_page);

$iNumPages = $oPager->getNbPages();

$okt->estimate->filters->normalizePage($iNumPages);

$aParams['limit'] = (($okt->estimate->filters->params->page - 1) * $okt->estimate->filters->params->nb_per_page) . ',' . $okt->estimate->filters->params->nb_per_page;

# Récupération des demandes de devis à afficher
$rsEstimates = $okt->estimate->getEstimates($aParams);

# Tableau de choix d'actions pour le traitement par lot
$aActionsChoices = array(
	'&nbsp;' => null,
	__('m_estimate_list_mark_as_treateds') => 'treateds',
	__('m_estimate_list_mark_as_untreateds') => 'untreateds',
	__('m_estimate_list_delete') => 'delete'
);

# button set
$okt->page->setButtonset('estimateBtSt', array(
	'id' => 'estimate-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_c_display_filters'),
			'url' => '#',
			'ui-icon' => 'search',
			'active' => $okt->estimate->filters->params->show_filters,
			'id' => 'filter-control',
			'class' => 'button-toggleable'
		),
		array(
			'permission' => true,
			'title' => __('c_c_action_show'),
			'url' => html::escapeHTML(EstimateHelpers::getFormUrl()),
			'ui-icon' => 'extlink'
		)
	)
));

# Filters control, display a UI dialog box
$okt->page->js->addReady("
	$('#filters-form').dialog({
		title:'" . html::escapeJS(__('c_c_display_filters')) . "',
		autoOpen: false,
		modal: true,
		width: 500,
		height: 300
	});

	$('#filter-control').click(function() {
		$('#filters-form').dialog('open');
	})
");

# Checkboxes helper
$okt->page->checkboxHelper('estimates-list', 'checkboxHelper');

# En-tête
require OKT_ADMIN_HEADER_FILE;
?>

<?php echo $okt->page->getButtonSet('estimateBtSt'); ?>

<?php # formulaire des filtres ?>
<form action="module.php" method="get" id="filters-form">
	<fieldset>
		<legend><?php _e('m_estimate_display_filters') ?></legend>

		<?php echo $okt->estimate->filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><?php echo form::hidden('m','estimate')?>
		<?php echo form::hidden('action','index')?>
		<input type="submit"
				name="<?php echo $okt->estimate->filters->getFilterSubmitName() ?>"
				value="<?php _e('c_c_action_display') ?>" /> <a
				href="module.php?m=estimate&amp;action=index&amp;init_filters=1"><?php _e('c_c_reset_filters') ?></a>
		</p>

	</fieldset>
</form>


<div id="estimatesList">

<?php 
# Affichage du compte d'articles
if ($iNumFilteredEstimates == 0)
:
	?>
<p id="estimate-count"><?php _e('m_estimate_list_no_estimate') ?></p>
<?php elseif ($iNumFilteredEstimates == 1) : ?>
<p id="estimate-count"><?php _e('m_estimate_list_one_estimate') ?></p>
<?php else : ?>
	<?php if ($iNumPages > 1) : ?>
		<p id="estimate-count"><?php printf(__('m_estimate_list_%s_estimates_on_%s_pages'), $iNumFilteredEstimates, $iNumPages) ?></p>
	<?php else : ?>
		<p id="estimate-count"><?php printf(__('m_estimate_list_%s_estimates'), $iNumFilteredEstimates) ?></p>
	<?php endif; ?>
<?php endif; ?>

<?php 
# Si on as des demandes de devis à afficher
if (!$rsEstimates->isEmpty())
:
	?>
<form action="module.php" method="post" id="estimates-list">
		<table class="common">
			<caption><?php _e('m_estimate_list_table_caption') ?></caption>
			<thead>
				<tr>
					<th scope="col"><?php _e('m_estimate_list_table_th_id') ?></th>
					<th scope="col"><?php _e('m_estimate_list_table_th_dates') ?></th>
					<th scope="col"><?php _e('m_estimate_list_table_th_registered') ?></th>
					<th scope="col"><?php _e('c_c_Actions') ?></th>
				</tr>
			</thead>
			<tbody>
		<?php while ($rsEstimates->fetch()) : ?>
		<tr
					class="<?php echo $rsEstimates->odd_even ?><?php if ($rsEstimates->status) : ?> disabled<?php endif; ?>">
					<th class="<?php echo $rsEstimates->odd_even ?> fake-td">
				<?php echo form::checkbox(array('estimates[]'),$rsEstimates->id)?>
				<a
						href="module.php?m=estimate&amp;action=details&amp;estimate_id=<?php echo $rsEstimates->id ?>"># <?php
		echo html::escapeHTML($rsEstimates->id)?></a>
					</th>

					<td class="<?php echo $rsEstimates->odd_even ?>">
				<?php if (empty($rsEstimates->end_at) || $rsEstimates->start_at == $rsEstimates->end_at) : ?>
				<?php printf(__('On %s'), dt::dt2str(__('%A, %B %d, %Y'), html::escapeHTML($rsEstimates->start_at)))?>

				<?php else : ?>
				<?php
			
printf(__('From %s to %s'), dt::dt2str(__('%A, %B %d, %Y'), html::escapeHTML($rsEstimates->start_at)), dt::dt2str(__('%A, %B %d, %Y'), html::escapeHTML($rsEstimates->end_at)));
			?>
				<?php endif; ?>
			</td>

					<td class="<?php echo $rsEstimates->odd_even ?>">
				<?php echo dt::dt2str(__('%Y-%m-%d %H:%M'), html::escapeHTML($rsEstimates->created_at))?>
			</td>

					<td class="<?php echo $rsEstimates->odd_even ?> small nowrap">
						<ul class="actions">
							<li>
					<?php if ($rsEstimates->status) : ?>
					<a
								href="module.php?m=estimate&amp;action=index&amp;untreated=<?php echo $rsEstimates->id ?>"
								title="<?php printf(__('m_estimate_list_mark_%s_as_untreated'), $rsEstimates->id) ?>"
								class="icon tick"><?php _e('m_estimate_list_treated')?></a>
					<?php else : ?>
					<a
								href="module.php?m=estimate&amp;action=index&amp;treated=<?php echo $rsEstimates->id ?>"
								title="<?php printf(__('m_estimate_list_mark_%s_as_treated'), $rsEstimates->id) ?>"
								class="icon clock"><?php _e('m_estimate_list_untreated')?></a>
					<?php endif; ?>
					</li>
							<li><a
								href="module.php?m=estimate&amp;action=details&amp;estimate_id=<?php echo $rsEstimates->id ?>"
								title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_estimate_list_show_%s'), $rsEstimates->id)) ?>"
								class="icon table"><?php _e('m_estimate_list_show_details') ?></a></li>

							<li><a
								href="module.php?m=estimate&amp;action=delete&amp;estimate_id=<?php echo $rsEstimates->id ?>"
								onclick="return window.confirm('<?php echo html::escapeJS(__('m_estimate_list_delete_confirm')) ?>')"
								title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_estimate_list_delete_%s'), $rsEstimates->id)) ?>"
								class="icon delete"><?php _e('c_c_action_Delete') ?></a></li>
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
			<div class="col right">
				<p><?php _e('m_estimates_list_estimates_action')?>
		<?php echo form::select('actions',$aActionsChoices)?>
		<?php echo form::hidden('m','estimate'); ?>
		<?php echo form::hidden('action','index'); ?>
		<?php echo form::hidden('sended',1); ?>
		<?php echo Page::formtoken(); ?>
		<input type="submit" value="<?php echo 'ok'; ?>" />
				</p>
			</div>
		</div>
	</form>

<?php if ($iNumPages > 1) : ?>
<ul class="pagination"><?php echo $oPager->getLinks(); ?></ul>
<?php endif; ?>

<?php endif; ?>

</div>
<!-- #estimatesList -->


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
