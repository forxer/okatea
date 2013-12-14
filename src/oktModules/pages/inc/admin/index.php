<?php
/**
 * @ingroup okt_module_pages
 * @brief La liste des pages
 *
 */

use Tao\Admin\Page;
use Tao\Admin\Pager;
use Tao\Misc\Utilities as util;
use Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_PAGES_MODULE')) die;


/* json pages list for autocomplete
----------------------------------------------------------*/

if (!empty($_REQUEST['json']))
{
	$rsPages = $okt->pages->getPages(array(
		'language' => $okt->user->language,
		'search' => $_GET['term']
	));

	$aResults = array();
	while ($rsPages->fetch()) {
		$aResults[] = $rsPages->title;
	}

	header('Content-type: application/json');
	echo json_encode($aResults);

	exit;
}


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
$okt->l10n->loadFile(__DIR__.'/../../locales/'.$okt->user->language.'/admin.list');

# initialisation des filtres
$okt->pages->filtersStart('admin');


/* Traitements
----------------------------------------------------------*/

# Ré-initialisation filtres
if (!empty($_GET['init_filters']))
{
	$okt->pages->filters->initFilters();
	http::redirect('module.php?m=pages&action=index');
}

# Switch statut
if (!empty($_GET['switch_status']))
{
	try
	{
		$okt->pages->switchPageStatus($_GET['switch_status']);

		# log admin
		$okt->logAdmin->info(array(
			'code' => 32,
			'component' => 'pages',
			'message' => 'page #'.$_GET['switch_status']
		));

		http::redirect('module.php?m=pages&action=index&switched=1');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Traitements par lots
if (!empty($_POST['actions']) && !empty($_POST['pages']) && is_array($_POST['pages']))
{
	$aPagesId = array_map('intval',$_POST['pages']);

	try
	{
		if ($_POST['actions'] == 'show')
		{
			foreach ($aPagesId as $pageId)
			{
				$okt->pages->setPageStatus($pageId,1);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 30,
					'component' => 'pages',
					'message' => 'page #'.$pageId
				));
			}

			http::redirect('module.php?m=pages&action=index&switcheds=1');
		}
		elseif ($_POST['actions'] == 'hide')
		{
			foreach ($aPagesId as $pageId)
			{
				$okt->pages->setPageStatus($pageId,0);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 31,
					'component' => 'pages',
					'message' => 'page #'.$pageId
				));
			}

			http::redirect('module.php?m=pages&action=index&switcheds=1');
		}
		elseif ($_POST['actions'] == 'delete' && $okt->checkPerm('pages_remove'))
		{
			foreach ($aPagesId as $pageId)
			{
				$okt->pages->deletePage($pageId);

				# log admin
				$okt->logAdmin->warning(array(
					'code' => 42,
					'component' => 'pages',
					'message' => 'page #'.$pageId
				));
			}

			http::redirect('module.php?m=pages&action=index&deleteds=1');
		}
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}


/* Affichage
----------------------------------------------------------*/

# Initialisation des filtres
$aParams = array();

$sSearch = null;

if (!empty($_REQUEST['search']))
{
	$sSearch = trim($_REQUEST['search']);
	$aParams['search'] = $sSearch;
}
$okt->pages->filters->setPagesParams($aParams);

# Création des filtres
$okt->pages->filters->getFilters();

# Initialisation de la pagination
$iNumFilteredPosts = $okt->pages->getPagesCount($aParams);

$oPager = new Pager($okt->pages->filters->params->page, $iNumFilteredPosts, $okt->pages->filters->params->nb_per_page);

$iNumPages = $oPager->getNbPages();

$okt->pages->filters->normalizePage($iNumPages);

$aParams['limit'] = (($okt->pages->filters->params->page-1)*$okt->pages->filters->params->nb_per_page).','.$okt->pages->filters->params->nb_per_page;

# Récupération des pages
$rsPages = $okt->pages->getPages($aParams);

# Liste des groupes si les permissions sont activées
if ($okt->pages->canUsePerms()) {
	$aGroups = $okt->pages->getUsersGroupsForPerms(true,true);
}

# Tableau de choix d'actions pour le traitement par lot
$aActionsChoices = array(
	__('c_c_action_display') => 'show',
	__('c_c_action_hide') => 'hide'
);

if ($okt->checkPerm('pages_remove')) {
	$aActionsChoices[__('c_c_action_delete')] = 'delete';
}

# Autocomplétion du formulaire de recherche
$okt->page->js->addReady('
	$("#search").autocomplete({
		source: "module.php?search=&m=pages&action=index&json=1",
		minLength: 2
	});
');

if (!empty($sSearch))
{
	$okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/putCursorAtEnd/jquery.putCursorAtEnd.min.js');
	$okt->page->js->addReady('
		$("#search").putCursorAtEnd();
	');
}

# Ajout de boutons
$okt->page->addButton('pagesBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_display_filters'),
	'url' 			=> '#',
	'ui-icon' 		=> 'search',
	'active' 		=> $okt->pages->filters->params->show_filters,
	'id'			=> 'filter-control',
	'class'			=> 'button-toggleable'
));


# Bouton vers le module côté public
$okt->page->addButton('pagesBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_show'),
	'url' 			=> html::escapeHTML($okt->pages->config->url),
	'ui-icon' 		=> 'extlink'
));

# Filters control
if ($okt->pages->config->admin_filters_style == 'slide')
{
	# Slide down
	$okt->page->filterControl($okt->pages->filters->params->show_filters);
}
elseif ($okt->pages->config->admin_filters_style == 'dialog')
{
	# Display a UI dialog box
	$okt->page->js->addReady("
		$('#filters-form').dialog({
			title:'".html::escapeJS(__('c_c_display_filters'))."',
			autoOpen: false,
			modal: true,
			width: 500,
			height: 300
		});

		$('#filter-control').click(function() {
			$('#filters-form').dialog('open');
		})
	");
}

# Checkboxes helper
$okt->page->checkboxHelper('pages-list','checkboxHelper');

# Un peu de CSS
$okt->page->css->addCss('
.ui-autocomplete {
	max-height: 150px;
	overflow-y: auto;
	overflow-x: hidden;
}
.search_form p {
	margin: 0;
}
#search {
	background: transparent url('.OKT_PUBLIC_URL.'/img/admin/preview.png) no-repeat center right;
}
#post-count {
	margin-top: 0;
}
');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<div class="double-buttonset">
	<div class="buttonsetA">
		<?php echo $okt->page->getButtonSet('pagesBtSt'); ?>
	</div>
	<div class="buttonsetB">
		<form action="module.php" method="get" id="search_form" class="search_form">
			<p><label for="search"><?php _e('m_pages_list_Search') ?></label>
			<?php echo form::text('search',20,255,html::escapeHTML((isset($sSearch) ? $sSearch : ''))); ?>

			<?php echo form::hidden('m','pages') ?>
			<?php echo form::hidden('action','index') ?>
			<input type="submit" name="search_submit" id="search_submit" value="ok" /></p>
		</form>
	</div>
</div>

<?php # formulaire des filtres ?>
<form action="module.php" method="get" id="filters-form">
	<fieldset>
		<legend><?php _e('m_pages_display_filters') ?></legend>

		<?php echo $okt->pages->filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><?php echo form::hidden('m','pages') ?>
		<?php echo form::hidden('action','index') ?>
		<input type="submit" name="<?php echo $okt->pages->filters->getFilterSubmitName() ?>" value="<?php _e('c_c_action_display') ?>" />
		<a href="module.php?m=pages&amp;action=index&amp;init_filters=1"><?php _e('c_c_reset_filters') ?></a></p>

	</fieldset>
</form>

<div id="pagesList">

<?php # Affichage du compte de pages
if ($iNumFilteredPosts == 0) : ?>
<p id="post-count"><?php _e('m_pages_list_no_page') ?></p>
<?php elseif ($iNumFilteredPosts == 1) : ?>
<p id="post-count"><?php _e('m_pages_list_one_page') ?></p>
<?php else : ?>
	<?php if ($iNumPages > 1) : ?>
		<p id="post-count"><?php printf(__('m_pages_list_%s_pages_on_%s_pages'), $iNumFilteredPosts, $iNumPages) ?></p>
	<?php else : ?>
		<p id="post-count"><?php printf(__('m_pages_list_%s_pages'), $iNumFilteredPosts) ?></p>
	<?php endif; ?>
<?php endif; ?>

<?php # Si on as des pages à afficher
if (!$rsPages->isEmpty()) : ?>

<form action="module.php" method="post" id="pages-list">

	<table class="common">
		<caption><?php _e('m_pages_list_table_caption') ?></caption>
		<thead><tr>
			<th scope="col"><?php _e('m_pages_list_table_th_title') ?></th>
			<?php if ($okt->pages->config->categories['enable']) : ?>
			<th scope="col"><?php _e('m_pages_list_table_th_category') ?></th>
			<?php endif; ?>
			<?php if ($okt->pages->canUsePerms()) : ?>
			<th scope="col"><?php _e('m_pages_list_table_th_access') ?></th>
			<?php endif; ?>
			<th scope="col"><?php _e('c_c_Actions') ?></th>
		</tr></thead>
		<tbody>
		<?php $count_line = 0;
		while ($rsPages->fetch()) :
			$td_class = $count_line%2 == 0 ? 'even' : 'odd';
			$count_line++;
		?>
		<tr>
			<th class="<?php echo $td_class ?> fake-td">
				<?php echo form::checkbox(array('pages[]'),$rsPages->id) ?>
				<a href="module.php?m=pages&amp;action=edit&amp;post_id=<?php echo $rsPages->id ?>"><?php
				echo html::escapeHTML($rsPages->title) ?></a>
			</th>

			<?php if ($okt->pages->config->categories['enable']) : ?>
			<td class="<?php echo $td_class ?>"><?php echo html::escapeHTML($rsPages->category_title) ?></td>
			<?php endif; ?>

			<?php # droits d'accès
			if ($okt->pages->canUsePerms()) :

				$aGroupsAccess = array();
				$aPerms = $okt->pages->getPagePermissions($rsPages->id);
				foreach ($aPerms as $iPerm) {
					$aGroupsAccess[] = html::escapeHTML($aGroups[$iPerm]);
				}
				unset($aPerms);
			?>
			<td class="<?php echo $td_class ?>">
				<?php if (!empty($aGroupsAccess)) : ?>
				<ul>
					<li><?php echo implode('</li><li>',$aGroupsAccess) ?></li>
				</ul>
				<?php endif; ?>
			</td>
			<?php endif; ?>

			<td class="<?php echo $td_class ?> small">
				<ul class="actions">
					<?php if ($rsPages->active) : ?>
					<li><a href="module.php?m=pages&amp;action=index&amp;switch_status=<?php echo $rsPages->id ?>"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_pages_list_switch_visibility_%s'), $rsPages->title)) ?>"
					class="icon tick"><?php _e('c_c_action_visible') ?></a></li>
					<?php else : ?>
					<li><a href="module.php?m=pages&amp;action=index&amp;switch_status=<?php echo $rsPages->id ?>"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_pages_list_switch_visibility_%s'), $rsPages->title)) ?>"
					class="icon cross"><?php _e('c_c_action_hidden_fem') ?></a></li>
					<?php endif; ?>

					<li><a href="module.php?m=pages&amp;action=edit&amp;post_id=<?php echo $rsPages->id ?>"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_pages_list_edit_%s'), $rsPages->title)) ?>"
					class="icon pencil"><?php _e('c_c_action_edit') ?></a></li>

					<?php if ($okt->checkPerm('pages_remove')) : ?>
					<li><a href="module.php?m=pages&amp;action=delete&amp;post_id=<?php echo $rsPages->id ?>"
					onclick="return window.confirm('<?php echo html::escapeJS(__('m_pages_list_page_delete_confirm')) ?>')"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_pages_list_delete_%s'), $rsPages->title)) ?>"
					class="icon delete"><?php _e('c_c_action_delete') ?></a></li>
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
		<div class="col right"><p><?php _e('m_pages_list_pages_action')?>
		<?php echo form::select('actions',$aActionsChoices) ?>
		<?php echo form::hidden('m','pages'); ?>
		<?php echo form::hidden('action','index'); ?>
		<?php echo form::hidden('sended',1); ?>
		<?php echo Page::formtoken(); ?>
		<input type="submit" value="<?php echo 'ok'; ?>" /></p></div>
	</div>
</form>

<?php if ($iNumPages > 1) : ?>
<ul class="pagination"><?php echo $oPager->getLinks(); ?></ul>
<?php endif; ?>

<?php endif; ?>

</div><!-- #pagesList -->

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
