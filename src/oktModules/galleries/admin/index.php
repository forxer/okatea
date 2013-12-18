<?php
/**
 * @ingroup okt_module_galleries
 * @brief Page de gestion des galeries
 *
 */

# Accès direct interdit
if (!defined('ON_GALLERIES_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
$okt->l10n->loadFile(__DIR__.'/../../locales/'.$okt->user->language.'/admin.list');


/* Traitements
----------------------------------------------------------*/

# Régénération des miniatures d'une galerie
if (!empty($_REQUEST['regenerate_thumbnails']))
{
	if ($okt->galleries->items->regenMinImages($_REQUEST['regenerate_thumbnails'])) {
		http::redirect('module.php?m=galleries&minregenerated=1');
	}
}

# Switch statut
if (!empty($_GET['switch_status']) && $okt->checkPerm('galleries_manage'))
{
	try
	{
		$okt->galleries->tree->switchGalleryStatus($_GET['switch_status']);

		# log admin
		$okt->logAdmin->info(array(
			'code' => 32,
			'component' => 'galleries',
			'message' => 'category #'.$_GET['switch_status']
		));

		http::redirect('module.php?m=galleries&switched=1');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Suppression d'une galerie
if (!empty($_GET['delete']) && $okt->checkPerm('galleries_manage'))
{
	if ($okt->galleries->tree->deleteGallery(intval($_GET['delete']))) {
		http::redirect('module.php?m=galleries&deleted=1');
	}
}


/* Affichage
----------------------------------------------------------*/

# Récupération de la liste complète des galeries
$rsGalleriesList = $okt->galleries->tree->getGalleries(array(
	'active' => 2,
	'with_count' => true,
	'language' => $okt->user->language
));


# Modal
$okt->page->applyLbl($okt->galleries->config->lightbox_type);

# Loader
$okt->page->loader('.lazy-load');

# CSS
$okt->page->css->addCSS('
	#rubriques_lists ul li p {
		margin: 0 0 1em 0;
	}
	#rubriques_lists ul li p.galleries_image {
		float: left;
		margin: 0 1em 1em 0;
	}
');

# bouton ajout de galerie
$okt->page->addButton('galleriesBtSt', array(
	'permission' 	=> $okt->checkPerm('galleries_manage'),
	'title' 		=> __('m_galleries_menu_add_gallery'),
	'url' 			=> 'module.php?m=galleries&amp;action=gallery',
	'ui-icon' 		=> 'plusthick',
	'id'			=> 'add_gallery_button'
));
/*
# bouton ajout d'élément
$okt->page->addButton('galleriesBtSt',array(
	'permission' 	=> ($okt->page->action !== 'add') && $okt->checkPerm('galleries_add'),
	'title' 		=> __('m_galleries_menu_add_item'),
	'url' 			=> 'module.php?m=galleries&amp;action=add',
	'ui-icon' 		=> 'image'
));

# bouton ajout de plusieurs éléments
$okt->page->addButton('galleriesBtSt',array(
	'permission' 	=> ($okt->page->action !== 'add') && $okt->galleries->config->enable_multiple_upload && $okt->checkPerm('galleries_add'),
	'title' 		=> __('m_galleries_menu_add_items'),
	'url' 			=> 'module.php?m=galleries&amp;action=add_multiples',
	'ui-icon' 		=> 'folder-collapsed'
));

# bouton ajout depuis un fichier ZIP
$okt->page->addButton('galleriesBtSt',array(
	'permission' 	=> ($okt->page->action !== 'add') && $okt->galleries->config->enable_zip_upload && $okt->checkPerm('galleries_add'),
	'title' 		=> __('m_galleries_menu_add_zip'),
	'url' 			=> 'module.php?m=galleries&amp;action=add_zip',
	'ui-icon' 		=> 'script'
));
*/
# bouton vers la liste des galeries côté public
$okt->page->addButton('galleriesBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_show'),
	'url' 			=> html::escapeHTML(GalleriesHelpers::getGalleriesUrl()),
	'ui-icon' 		=> 'extlink'
));


# Confirmations
$okt->page->messages->success('minregenerated', __('m_galleries_list_gallery_min_regenerated'));
$okt->page->messages->success('switched', __('m_galleries_list_gallery_visibility_switched'));
$okt->page->messages->success('deleted', __('m_galleries_list_gallery_deleted'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('galleriesBtSt'); ?>

<h3><?php _e('m_galleries_list_title') ?></h3>

<?php # Si on as pas de galerie à afficher
if ($rsGalleriesList->isEmpty()) : ?>

<p><?php _e('m_galleries_list_no_gallery') ?></p>


<?php # Sinon, on as des galeries à afficher
else : ?>

<div id="rubriques_lists">
<?php

$ref_level = $level = $rsGalleriesList->level-1;

while ($rsGalleriesList->fetch())
{
	$attr = ' id="rub'.$rsGalleriesList->id.'"';

	if (!$rsGalleriesList->active) {
		$attr .= ' class="disabled"';
	}

	# ouverture niveau
	if ($rsGalleriesList->level > $level) {
		echo str_repeat('<ul><li'.$attr.'>', $rsGalleriesList->level - $level);
	}
	# fermeture niveau
	elseif ($rsGalleriesList->level < $level) {
		echo str_repeat('</li></ul>', -($rsGalleriesList->level - $level));
	}

	# nouvelle ligne
	if ($rsGalleriesList->level <= $level) {
		echo '</li><li'.$attr.'>';
	}

	if ($rsGalleriesList->num_items > 1) {
		$sNumItems = sprintf(__('m_galleries_list_%s_items'), $rsGalleriesList->num_items);
	}
	elseif ($rsGalleriesList->num_items == 1) {
		$sNumItems = __('m_galleries_list_one_item');
	}
	else {
		$sNumItems = __('m_galleries_list_no_item');
	}

	if ($rsGalleriesList->num_total > 0 && $rsGalleriesList->num_total > $rsGalleriesList->num_items) {
		$sNumItems = sprintf(__('m_galleries_list_%s_total_%s'), $sNumItems, $rsGalleriesList->num_total);
	}

	if ($rsGalleriesList->num_items == 0)
	{
		$sDeleteLink = ' - <a href="module.php?m=galleries&amp;delete='.$rsGalleriesList->id.'" '.
		'title="'.sprintf(__('m_galleries_list_delete_gallery_%s'), html::escapeHTML($rsGalleriesList->name)).'" '.
		'onclick="return window.confirm(\''.html::escapeJS(__('m_galleries_list_confirm_del_gallery')).'\')" '.
		' class="icon delete">'.__('c_c_action_delete').'</a>';

		$sManageLink = ' - <span class="disabled icon application_view_tile"></span>'.__('m_galleries_list_content_management');
	}
	else
	{
		$sDeleteLink = ' - <span class="disabled icon delete"></span>'.__('c_c_action_delete');

		$sManageLink = ' - <a href="module.php?m=galleries&amp;action=items&amp;gallery_id='.$rsGalleriesList->id.'" '.
		'title="'.sprintf(__('m_galleries_list_manage_items_of_gallery_%s'), html::escapeHTML($rsGalleriesList->name)).'" '.
		'class="icon application_view_tile">'.__('m_galleries_list_content_management').'</a>';
	}

	# image
	$image = $rsGalleriesList->getImagesInfo();

	if (!empty($image) && isset($image['square_url']))
	{
		echo '<p class="modal-box galleries_image"><a href="'.$image['img_url'].'" title="'.html::escapeHTML($rsGalleriesList->name).'" class="modal">'.
		'<img src="'.$image['square_url'].'" '.$image['square_attr'].' alt="" /></a></p>';
	}

	echo '<p><strong>'.html::escapeHTML($rsGalleriesList->title).'</strong> - '.$sNumItems.$sManageLink;

	if ($okt->checkPerm('galleries_add'))
	{
		echo ' - <a href="module.php?m=galleries&amp;action=add&amp;gallery_id='.$rsGalleriesList->id.'" '.
		'title="'.sprintf(__('m_galleries_list_add_item_to_gallery_%s'), html::escapeHTML($rsGalleriesList->name)).'" '.
		'class="icon picture_add">'.__('m_galleries_list_add_item').'</a>';

		if ($okt->galleries->config->enable_multiple_upload)
		{
			echo ' - <a href="module.php?m=galleries&amp;action=add_multiples&amp;gallery_id='.$rsGalleriesList->id.'" '.
			'title="'.sprintf(__('m_galleries_list_add_multiple_items_to_gallery_%s'), html::escapeHTML($rsGalleriesList->name)).'" '.
			'class="icon pictures">'.__('m_galleries_list_add_items').'</a>';
		}

		if ($okt->galleries->config->enable_zip_upload)
		{
			echo ' - <a href="module.php?m=galleries&amp;action=add_zip&amp;gallery_id='.$rsGalleriesList->id.'" '.
			'title="'.sprintf(__('m_galleries_list_add_zip_to_gallery_%s'), html::escapeHTML($rsGalleriesList->name)).'" '.
			'class="icon page_white_zip">'.__('m_galleries_list_add_zip').'</a>';
		}

		if ($okt->checkPerm('is_superadmin'))
		{
			echo ' - <a href="module.php?m=galleries&amp;action=index&amp;regenerate_thumbnails='.$rsGalleriesList->id.'" '.
			'title="'.sprintf(__('m_galleries_list_regenerate_thumbnails_of_gallery_%s'), html::escapeHTML($rsGalleriesList->name)).'" '.
			'class="icon arrow_refresh_small lazy-load">'.__('m_galleries_list_regenerate_thumbnails').'</a>';
		}
	}

	echo '</p>';

	if ((!$rsGalleriesList->locked && $okt->checkPerm('galleries_manage')) || $okt->user->is_superadmin)
	{
		echo '<p>';

		if ($rsGalleriesList->active)
		{
			echo '<a href="module.php?m=galleries&amp;action=index&amp;switch_status='.$rsGalleriesList->id.'" '.
			'title="'.sprintf(__('m_galleries_list_switch_visibility_gallery_%s'), html::escapeHTML($rsGalleriesList->name)).'" '.
			'class="icon tick">'.__('c_c_action_visible').'</a>';
		}
		else
		{
			echo '<a href="module.php?m=galleries&amp;action=index&amp;switch_status='.$rsGalleriesList->id.'" '.
			'title="'.sprintf(__('m_galleries_list_switch_visibility_gallery_%s'), html::escapeHTML($rsGalleriesList->name)).'" '.
			'class="icon cross">'.__('c_c_action_hidden_fem').'</a>';
		}

		echo ' - <a href="module.php?m=galleries&amp;action=gallery&amp;gallery_id='.$rsGalleriesList->id.'" '.
		'title="'.sprintf(__('m_galleries_list_edit_gallery_%s'), html::escapeHTML($rsGalleriesList->name)).'" '.
		'class="icon pencil">'.__('c_c_action_edit').'</a>';

		echo $sDeleteLink;

		if ($rsGalleriesList->password) {
			echo ' - <span class="icon key">'.__('m_galleries_list_protected_password').'</span>';
		}

		if ($okt->user->is_superadmin && $rsGalleriesList->locked) {
			echo ' - <span class="icon lock">'.__('m_galleries_list_locked').'</span>';
		}

		echo '</p>';
	}

	echo '<div class="clearer"></div>';

	$level = $rsGalleriesList->level;
}

if ($ref_level - $level < 0) {
	echo str_repeat('</li></ul>', -($ref_level - $level));
}

?>
</div><!-- #galleries_lists  -->
<?php endif; ?>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
