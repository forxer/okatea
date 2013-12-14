<?php
/**
 * @ingroup okt_module_galleries
 * @brief La liste des éléments des galleries
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;


# Accès direct interdit
if (!defined('ON_GALLERIES_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
$okt->l10n->loadFile(__DIR__.'/../../locales/'.$okt->user->language.'/admin.items');

# Récupération des infos de la galerie
$gallery_id = !empty($_REQUEST['gallery_id']) ? intval($_REQUEST['gallery_id']) : null;

$rsGallery = $okt->galleries->tree->getGallery($gallery_id);

if (is_null($gallery_id) || $rsGallery->isEmpty()) {
	# @FIXME: need redirect 404
	http::redirect('module.php?m=galleries&action=index');
}


/* Traitements
----------------------------------------------------------*/

# Switch statut
if (!empty($_GET['switch_status']))
{
	try
	{
		$okt->galleries->items->switchItemStatus($_GET['switch_status']);

		http::redirect('module.php?m=galleries&action=items&gallery_id='.$gallery_id.'&switched=1');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# suppression d'un élément
if (!empty($_GET['delete']) && $okt->checkPerm('galleries_remove'))
{
	try
	{
		$okt->galleries->items->deleteItem($_GET['delete']);

		http::redirect('module.php?m=galleries&action=items&gallery_id='.$gallery_id.'&deleted=1');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Traitements par lots
if (!empty($_POST['actions']) && !empty($_POST['items']) && is_array($_POST['items']))
{
	try
	{
		$aItemsId = array_map('intval', $_POST['items']);

		if ($_POST['actions'] == 'show')
		{
			foreach ($aItemsId as $itemId) {
				$okt->galleries->items->setItemStatus($itemId,1);
			}

			http::redirect('module.php?m=galleries&action=items&gallery_id='.$gallery_id.'&sitems_witched=1');
		}
		elseif ($_POST['actions'] == 'hide')
		{
			foreach ($aItemsId as $itemId) {
				$okt->galleries->items->setItemStatus($itemId,0);
			}

			http::redirect('module.php?m=galleries&action=items&gallery_id='.$gallery_id.'&items_switched=1');
		}
		elseif ($_POST['actions'] == 'delete')
		{
			foreach ($aItemsId as $itemId) {
				$okt->galleries->items->deleteItem($itemId);
			}

			http::redirect('module.php?m=galleries&action=items&gallery_id='.$gallery_id.'&items_deleted=1');
		}
		else {
			throw new Exception('no valid action selected');
		}
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(html::escapeHTML($rsGallery->title));

# Boutons
$okt->page->addButton('galleriesBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_Go_back'),
	'url' 			=> 'module.php?m=galleries&amp;action=index',
	'ui-icon' 		=> 'arrowreturnthick-1-w',
));
$okt->page->addButton('galleriesBtSt',array(
	'permission' 	=> ($okt->page->action !== 'add') && $okt->checkPerm('galleries_add'),
	'title' 		=> __('m_galleries_menu_add_item'),
	'url' 			=> 'module.php?m=galleries&amp;action=add'.($gallery_id ? '&amp;gallery_id='.$gallery_id : ''),
	'ui-icon' 		=> 'image'
));
# bouton ajout de plusieurs éléments
$okt->page->addButton('galleriesBtSt',array(
	'permission' 	=> ($okt->page->action !== 'add') && $okt->galleries->config->enable_multiple_upload && $okt->checkPerm('galleries_add'),
	'title' 		=> __('m_galleries_menu_add_items'),
	'url' 			=> 'module.php?m=galleries&amp;action=add_multiples'.($gallery_id ? '&amp;gallery_id='.$gallery_id : ''),
	'ui-icon' 		=> 'folder-collapsed'
));
# bouton ajout depuis un fichier ZIP
$okt->page->addButton('galleriesBtSt',array(
	'permission' 	=> ($okt->page->action !== 'add') && $okt->galleries->config->enable_zip_upload && $okt->checkPerm('galleries_add'),
	'title' 		=> __('m_galleries_menu_add_zip'),
	'url' 			=> 'module.php?m=galleries&amp;action=add_zip'.($gallery_id ? '&amp;gallery_id='.$gallery_id : ''),
	'ui-icon' 		=> 'script'
));
$okt->page->addButton('galleriesBtSt',array(
	'permission' 	=> ($rsGallery->active ? true : false),
	'title' 		=> __('c_c_action_show'),
	'url' 			=> html::escapeHTML(galleriesHelpers::getGalleryUrl($rsGallery->slug)),
	'ui-icon' 		=> 'extlink'
));


# Récupération des éléments
$rsItems = $okt->galleries->items->getItems(array(
	'language' => $okt->user->language,
	'gallery_id' => $gallery_id,
	'active' => 2
));


# Confirmations
$okt->page->messages->success('switched', __('m_galleries_items_item_switched'));
$okt->page->messages->success('deleted', __('m_galleries_items_item_deleted'));

# Smart columns
$okt->page->smartColumns();

# Modal
$okt->page->applyLbl($okt->galleries->config->lightbox_type);


# Sortables smart columns
$okt->page->js->addReady('
	$(".smartColumns").sortable({
		revert: true,
		handle: "h3",
		update: function(event, ui) {
			var result = $(".smartColumns").sortable("serialize");

			$("#ajaxloader").show();

			$.ajax({
				data: result,
				url: "'.OKT_MODULES_URL.'/galleries/service_ordering_items.php",
				success: function(data) {
					$("#ajaxloader").fadeOut(400);
				},
				error: function(data) {
					$("#ajaxloader").fadeOut(400);
				}
			});
		}
	});

	$(".smartColumns").disableSelection();
');

$okt->page->css->addCSS('
	ul.smartColumns li.column .block {
		height: 260px;
	}
	ul.smartColumns li.column {
	}
	ul.smartColumns li.column .block h3 {
		cursor: move
	}
	.ui-sortable-placeholder {
		border: 1px dotted black;
		visibility: visible !important;
		height: 260px !important;
		margin-right: 10px !important;
	}
	.ui-sortable-placeholder * {
		visibility: hidden;
	}
	.buttonsetB {
		width: auto !important;
	}
	#ajaxloader {
		display: none;
		background: transparent url('.OKT_PUBLIC_URL.'/img/ajax-loader/indicator-big.gif) no-repeat center;
		width: 32px;
		height: 32px;
	}
');


# tableau de choix d'actions pour le traitement par lot
$aActionsChoices = array(
	__('c_c_action_display') => 'show',
	__('c_c_action_hide') => 'hide',
	__('c_c_action_delete') => 'delete'
);


# checkboxes helper
$okt->page->checkboxHelper('items-list','checkboxHelper');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>


<div class="double-buttonset">
	<div class="buttonsetA"><?php echo $okt->page->getButtonSet('galleriesBtSt'); ?></div>
	<div class="buttonsetB"><div id="ajaxloader"></div></div>
</div>

<?php if ($rsItems->isEmpty()) : ?>
<p><?php _e('m_galleries_items_no_item') ?></p>

<?php else : ?>

<form action="module.php" method="post" id="items-list">

<ul class="smartColumns">
	<?php while ($rsItems->fetch()) : ?>
	<li class="column" id="ord_<?php echo $rsItems->id; ?>"><div class="block ui-widget ui-widget-content ui-corner-all">

		<h3 class="ui-widget-header ui-corner-all"><?php echo form::checkbox(array('items[]'),$rsItems->id) ?>
		<a href="module.php?m=galleries&amp;action=edit&amp;item_id=<?php echo $rsItems->id ?>"><?php
			echo html::escapeHTML($rsItems->title) ?></a></h3>

		<?php # image
		if (!empty($rsItems->image) && isset($rsItems->image['min_url'])) : ?>

		<p class="modal-box"><a href="<?php echo $rsItems->image['img_url']?>" title="<?php echo html::escapeHTML($rsItems->title) ?>" class="modal" rel="gallery">
		<img src="<?php echo $rsItems->image['min_url']?>" <?php echo $rsItems->image['min_attr']?> alt="" /></a></p>

		<?php endif; ?>

		<ul class="actions">
			<li><?php if ($rsItems->active) : ?>
			<a href="module.php?m=galleries&amp;action=items&amp;switch_status=<?php echo $rsItems->id.($gallery_id ? '&amp;gallery_id='.$gallery_id : '') ?>"
			title="<?php printf(__('m_galleries_switch_visibility_%s'),html::escapeHTML($rsItems->title)) ?>" class="icon tick"><?php _e('c_c_action_visible') ?></a>
			<?php else : ?>
			<a href="module.php?m=galleries&amp;action=items&amp;switch_status=<?php echo $rsItems->id.($gallery_id ? '&amp;gallery_id='.$gallery_id : '') ?>"
			title="<?php printf(__('m_galleries_switch_visibility_%s'),html::escapeHTML($rsItems->title)) ?>" class="icon cross"><?php _e('c_c_action_hidden') ?></a>
			<?php endif; ?></li>

			<li><a href="module.php?m=galleries&amp;action=edit&amp;item_id=<?php echo $rsItems->id ?>"
			title="<?php printf(__('m_galleries_edit_%s'),html::escapeHTML($rsItems->title)) ?>"
			class="icon pencil"><?php _e('c_c_action_Edit')?></a></li>

			<?php if ($okt->checkPerm('galleries_remove')) : ?>
			<li><a href="module.php?m=galleries&amp;action=items&amp;delete=<?php echo $rsItems->id.($gallery_id ? '&amp;gallery_id='.$gallery_id : '') ?>"
			onclick="return window.confirm('<?php echo html::escapeJS(__('m_galleries_item_delete_confirm')) ?>')"
			title="<?php printf(__('m_galleries_delete_%s'),html::escapeHTML($rsItems->title)) ?>"
			class="icon delete"><?php _e('c_c_action_Delete')?></a></li>
			<?php endif; ?>
		</ul>
	</div>
	</li>
	<?php endwhile; ?>
</ul>
<div class="clearer"></div>

<div class="two-cols">
	<div class="col">
		<p id="checkboxHelper"></p>
		<p class="note"><?php _e('m_galleries_items_order_drag_drop') ?></p>
	</div>
	<div class="col right"><p><?php _e('m_galleries_items_action_selected_items'); ?>&nbsp;:
	<?php echo form::select('actions', $aActionsChoices); ?>
	<?php echo form::hidden('m', 'galleries'); ?>
	<?php echo form::hidden('action', 'items'); ?>
	<?php echo form::hidden('gallery_id', $gallery_id); ?>
	<?php echo form::hidden('sended', 1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php echo 'ok'; ?>" /></p></div>
</div>
</form>

<?php endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
