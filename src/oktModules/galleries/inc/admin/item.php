<?php
/**
 * @ingroup okt_module_galleries
 * @brief Ajout ou modification d'un élément
 *
 */

use Tao\Admin\Page;
use Tao\Forms\StaticFormElements as form;
use Tao\Themes\TemplatesSet;

# Accès direct interdit
if (!defined('ON_GALLERIES_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
l10n::set(__DIR__.'/../../locales/'.$okt->user->language.'/admin.item');

$iGalleryId = !empty($_REQUEST['gallery_id']) ? intval($_REQUEST['gallery_id']) : null;

# Données de la page
$aItemData = new ArrayObject();

$aItemData['item'] = array();
$aItemData['item']['id'] = null;

$aItemData['item']['gallery_id'] = $iGalleryId;
$aItemData['item']['active'] = 1;
$aItemData['item']['tpl'] = '';
$aItemData['item']['created_at'] = '';
$aItemData['item']['updated_at'] = '';

$aItemData['locales'] = array();

foreach ($okt->languages->list as $aLanguage)
{
	$aItemData['locales'][$aLanguage['code']] = array();

	$aItemData['locales'][$aLanguage['code']]['title'] = '';
	$aItemData['locales'][$aLanguage['code']]['subtitle'] = '';
	$aItemData['locales'][$aLanguage['code']]['content'] = '';

	if ($okt->galleries->config->enable_metas)
	{
		$aItemData['locales'][$aLanguage['code']]['title_seo'] = '';
		$aItemData['locales'][$aLanguage['code']]['title_tag'] = '';
		$aItemData['locales'][$aLanguage['code']]['slug'] = '';
		$aItemData['locales'][$aLanguage['code']]['meta_description'] = '';
		$aItemData['locales'][$aLanguage['code']]['meta_keywords'] = '';
	}
}

$aItemData['image'] = array();

$rsItem = null;
$rsItemI18n = null;

# update item ?
if (!empty($_REQUEST['item_id']))
{
	$aItemData['item']['id'] = intval($_REQUEST['item_id']);

	$rsItem = $okt->galleries->items->getItemsRecordset(array(
		'id' => $aItemData['item']['id']
	));

	if ($rsItem->isEmpty())
	{
		$okt->error->set(sprintf(__('m_galleries_error_item_%s_doesnt_exist'), $aItemData['item']['id']));
		$aItemData['item']['id'] = null;
	}
	else
	{
		$iGalleryId = $rsItem->gallery_id;

		$aItemData['item']['gallery_id'] = $rsItem->gallery_id;
		$aItemData['item']['active'] = $rsItem->active;
		$aItemData['item']['tpl'] = $rsItem->tpl;
		$aItemData['item']['created_at'] = $rsItem->created_at;
		$aItemData['item']['updated_at'] = $rsItem->updated_at;

		$rsItemI18n = $okt->galleries->items->getItemI18n($aItemData['item']['id']);

		foreach ($okt->languages->list as $aLanguage)
		{
			while ($rsItemI18n->fetch())
			{
				if ($rsItemI18n->language == $aLanguage['code'])
				{
					$aItemData['locales'][$aLanguage['code']]['title'] = $rsItemI18n->title;
					$aItemData['locales'][$aLanguage['code']]['subtitle'] = $rsItemI18n->subtitle;
					$aItemData['locales'][$aLanguage['code']]['content'] = $rsItemI18n->content;

					if ($okt->galleries->config->enable_metas)
					{
						$aItemData['locales'][$aLanguage['code']]['title_seo'] = $rsItemI18n->title_seo;
						$aItemData['locales'][$aLanguage['code']]['title_tag'] = $rsItemI18n->title_tag;
						$aItemData['locales'][$aLanguage['code']]['slug'] = $rsItemI18n->slug;
						$aItemData['locales'][$aLanguage['code']]['meta_description'] = $rsItemI18n->meta_description;
						$aItemData['locales'][$aLanguage['code']]['meta_keywords'] = $rsItemI18n->meta_keywords;
					}
				}
			}
		}

		# Image
		$aItemData['image'] = $rsItem->getImagesInfo();
	}
}


# -- TRIGGER MODULE GALLERIES : adminItemInit
$okt->galleries->triggers->callTrigger('adminItemInit', $okt, $aItemData, $rsItem, $rsItemI18n);



/* Traitements
----------------------------------------------------------*/

# switch page status
if (!empty($_GET['switch_status']) && !empty($aItemData['item']['id']))
{
	try
	{
		$okt->galleries->items->switchItemStatus($aItemData['item']['id']);

		# log admin
		$okt->logAdmin->info(array(
			'code' => 32,
			'component' => 'galleries',
			'message' => 'item #'.$aItemData['item']['id']
		));

		http::redirect('module.php?m=galleries&action=edit&item_id='.$aItemData['item']['id'].'&switched=1');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# suppression d'une image
if (!empty($_GET['delete_image']) && !empty($aItemData['item']['id']))
{
	$okt->galleries->items->deleteImage($aItemData['item']['id'], $_GET['delete_image']);

	# log admin
	$okt->logAdmin->info(array(
		'code' => 41,
		'component' => 'galleries',
		'message' => 'item #'.$aItemData['item']['id']
	));

	http::redirect('module.php?m=galleries&action=edit&item_id='.$aItemData['item']['id'].'&updated=1');
}

#  ajout / modifications d'un élément
if (!empty($_POST['sended']))
{
	$aItemData['item']['gallery_id'] = !empty($_POST['p_gallery_id']) ? intval($_POST['p_gallery_id']) : 0;
	$aItemData['item']['active'] = !empty($_POST['p_active']) ? 1 : 0;
	$aItemData['item']['tpl'] = !empty($_POST['p_tpl']) ? $_POST['p_tpl'] : null;
	$aItemData['item']['created_at'] = $aItemData['item']['created_at'];
	$aItemData['item']['updated_at'] = $aItemData['item']['updated_at'];

	foreach ($okt->languages->list as $aLanguage)
	{
		$aItemData['locales'][$aLanguage['code']]['title'] = !empty($_POST['p_title'][$aLanguage['code']]) ? $_POST['p_title'][$aLanguage['code']] : '';
		$aItemData['locales'][$aLanguage['code']]['subtitle'] = !empty($_POST['p_subtitle'][$aLanguage['code']]) ? $_POST['p_subtitle'][$aLanguage['code']] : '';
		$aItemData['locales'][$aLanguage['code']]['content'] = !empty($_POST['p_content'][$aLanguage['code']]) ? $_POST['p_content'][$aLanguage['code']] : '';

		if ($okt->galleries->config->enable_metas)
		{
			$aItemData['locales'][$aLanguage['code']]['title_seo'] = !empty($_POST['p_title_seo'][$aLanguage['code']]) ? $_POST['p_title_seo'][$aLanguage['code']] : '';
			$aItemData['locales'][$aLanguage['code']]['title_tag'] = !empty($_POST['p_title_tag'][$aLanguage['code']]) ? $_POST['p_title_tag'][$aLanguage['code']] : '';
			$aItemData['locales'][$aLanguage['code']]['slug'] = !empty($_POST['p_slug'][$aLanguage['code']]) ? $_POST['p_slug'][$aLanguage['code']] : '';
			$aItemData['locales'][$aLanguage['code']]['meta_description'] = !empty($_POST['p_meta_description'][$aLanguage['code']]) ? $_POST['p_meta_description'][$aLanguage['code']] : '';
			$aItemData['locales'][$aLanguage['code']]['meta_keywords'] = !empty($_POST['p_meta_keywords'][$aLanguage['code']]) ? $_POST['p_meta_keywords'][$aLanguage['code']] : '';
		}
	}


	# -- TRIGGER MODULE GALLERIES : adminPopulateItemData
	$okt->galleries->triggers->callTrigger('adminPopulateItemData', $okt, $aItemData);


	# vérification des données avant modification dans la BDD
	if ($okt->galleries->items->checkPostData($aItemData))
	{
		$aItemData['cursor'] = $okt->galleries->items->openItemCursor($aItemData['item']);

		# update item
		if (!empty($aItemData['item']['id']))
		{
			try
			{
				# -- TRIGGER MODULE GALLERIES : adminBeforeItemUpdate
				$okt->galleries->triggers->callTrigger('adminBeforeItemUpdate', $okt, $aItemData);

				$okt->galleries->items->updItem($aItemData['cursor'], $aItemData['locales']);

				# -- TRIGGER MODULE GALLERIES : adminAfterItemUpdate
				$okt->galleries->triggers->callTrigger('adminAfterItemUpdate', $okt, $aItemData);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 41,
					'component' => 'galleries',
					'message' => 'item #'.$aItemData['item']['id']
				));

				http::redirect('module.php?m=galleries&action=edit&item_id='.$aItemData['item']['id'].'&updated=1');
			}
			catch (Exception $e) {
				$okt->error->set($e->getMessage());
			}
		}

		# add item
		else
		{
			try
			{
				# -- TRIGGER MODULE GALLERIES : adminBeforeItemCreate
				$okt->galleries->triggers->callTrigger('adminBeforeItemCreate', $okt, $aItemData);

				$aItemData['item']['id'] = $okt->galleries->items->addItem($aItemData['cursor'], $aItemData['locales']);

				# -- TRIGGER MODULE GALLERIES : adminAfterItemCreate
				$okt->galleries->triggers->callTrigger('adminAfterItemCreate', $okt, $aItemData);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 40,
					'component' => 'galleries',
					'message' => 'item #'.$aItemData['item']['id']
				));

				http::redirect('module.php?m=galleries&action=edit&item_id='.$aItemData['item']['id'].'&added=1');
			}
			catch (Exception $e) {
				$okt->error->set($e->getMessage());
			}
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Récupération de la liste complète des galeries
$rsGalleriesList = $okt->galleries->tree->getGalleries(array(
	'active' => 2,
	'with_count' => false,
	'language' => $okt->user->language
));

# Liste des templates utilisables
$oTemplatesItem = new TemplatesSet($okt, $okt->galleries->config->templates['item'], 'galleries/item', 'item');
$aTplChoices = array_merge(
	array('&nbsp;' => null),
	$oTemplatesItem->getUsablesTemplatesForSelect($okt->galleries->config->templates['item']['usables'])
);

# ajout bouton retour
$okt->page->addButton('galleriesBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_Go_back'),
	'url' 			=> ($iGalleryId
			? 'module.php?m=galleries&amp;action=items&amp;gallery_id='.$iGalleryId
			: 'module.php?m=galleries&amp;action=index'),
	'ui-icon' 		=> 'arrowreturnthick-1-w',
),'before');

# boutons update page
if (!empty($aItemData['item']['id']))
{
	$okt->page->addGlobalTitle(__('m_galleries_item_edit_an_item'));

	# bouton switch statut
	$okt->page->addButton('galleriesBtSt',array(
		'permission' 	=> true,
		'title' 		=> ($aItemData['item']['active'] ? __('c_c_status_Online') : __('c_c_status_Offline')),
		'url' 			=> 'module.php?m=galleries&amp;action=edit&amp;item_id='.$aItemData['item']['id'].'&amp;switch_status=1',
		'ui-icon' 		=> ($aItemData['item']['active'] ? 'volume-on' : 'volume-off'),
		'active' 		=> $aItemData['item']['active'],
	));
	# bouton de suppression si autorisé
	$okt->page->addButton('galleriesBtSt',array(
		'permission' 	=> $okt->checkPerm('galleries_remove'),
		'title' 		=> __('c_c_action_Delete'),
		'url' 			=> 'module.php?m=galleries&amp;action=delete&amp;item_id='.$aItemData['item']['id'],
		'ui-icon' 		=> 'closethick',
		'onclick' 		=> 'return window.confirm(\''.html::escapeJS(__('m_galleries_item_delete_confirm')).'\')',
	));
	# bouton vers la page côté public si publié
	$okt->page->addButton('galleriesBtSt',array(
		'permission' 	=> ($aItemData['item']['active'] ? true : false),
		'title' 		=> __('c_c_action_Show'),
		'url' 			=> galleriesHelpers::getItemUrl($aItemData['locales'][$okt->user->language]['slug']),
		'ui-icon' 		=> 'extlink'
	));

	$okt->page->messages->success('added', __('m_galleries_item_added'));
	$okt->page->messages->success('updated', __('m_galleries_item_updated'));
}

# boutons add page
else {
	$okt->page->addGlobalTitle(__('m_galleries_item_add_an_item'));
}

# Lockable
$okt->page->lockable();

# Tabs
$okt->page->tabs();

# Modal
$okt->page->applyLbl($okt->galleries->config->lightbox_type);

# RTE
$okt->page->applyRte($okt->galleries->config->enable_rte, 'textarea.richTextEditor');

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('galleriesBtSt'); ?>

<?php if (!empty($aItemData['item']['id'])) : ?>
<p><?php printf(__('m_galleries_item_added_on'), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aItemData['item']['created_at']).'</em>') ?>

<?php if ($aItemData['item']['updated_at'] > $aItemData['item']['created_at']) : ?>
<span class="note"><?php printf(__('m_galleries_item_last_edit'), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aItemData['item']['updated_at']).'</em>') ?></span>
<?php endif; ?>
</p>
<?php endif; ?>


<?php # Construction des onglets
$aItemData['tabs'] = new ArrayObject;

# onglet contenu
$aItemData['tabs'][10] = array(
	'id' => 'tab-content',
	'title' => __('m_galleries_item_tab_content'),
	'content' => ''
);

ob_start(); ?>

	<h3><?php _e('m_galleries_item_tab_title_content') ?></h3>

	<?php foreach ($okt->languages->list as $aLanguage) : ?>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_galleries_item_title') : printf(__('m_galleries_item_title_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 100, 255, html::escapeHTML($aItemData['locales'][$aLanguage['code']]['title'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_subtitle_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_galleries_item_subtitle') : printf(__('m_galleries_item_subtitle_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_subtitle['.$aLanguage['code'].']','p_subtitle_'.$aLanguage['code']), 100, 255, html::escapeHTML($aItemData['locales'][$aLanguage['code']]['subtitle'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_content_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_galleries_item_content') : printf(__('m_galleries_item_content_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::textarea(array('p_content['.$aLanguage['code'].']','p_content_'.$aLanguage['code']), 97, 15, $aItemData['locales'][$aLanguage['code']]['content'],'richTextEditor') ?></p>

	<?php endforeach; ?>

<?php

$aItemData['tabs'][10]['content'] = ob_get_clean();


# onglet images
$aItemData['tabs'][20] = array(
	'id' => 'tab-images',
	'title' => __('m_galleries_item_tab_image'),
	'content' => ''
);

ob_start(); ?>

<h3><?php _e('m_galleries_item_tab_title_image')?></h3>

<p class="field"><label for="p_image"><?php _e('m_galleries_item_image_file') ?></label>
<?php echo form::file('p_images_1') ?></p>

<?php # il y a une image ?
if (!empty($aItemData['image'])) :

	# affichage square ou icon ?
	if (isset($aItemData['image']['min_url'])) {
		$sCurImageUrl = $aItemData['image']['min_url'];
		$sCurImageAttr = $aItemData['image']['min_attr'];
	}
	elseif (isset($aItemData['image']['square_url'])) {
		$sCurImageUrl = $aItemData['image']['square_url'];
		$sCurImageAttr = $aItemData['image']['square_attr'];
	}
	else {
		$sCurImageUrl = OKT_PUBLIC_URL.'/img/media/image.png';
		$sCurImageAttr = ' width="48" height="48" ';
	}

	$aCurImageAlt = isset($aItemData['image']['alt']) ? $aItemData['image']['alt'] : array();
	$aCurImageTitle = isset($aItemData['image']['title']) ? $aItemData['image']['title'] : array();

	?>

	<?php foreach ($okt->languages->list as $aLanguage) : ?>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_title_1_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? __('m_galleries_item_image_title') : printf(__('m_galleries_item_image_title_in_%s'), $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_images_title_1['.$aLanguage['code'].']','p_images_title_1_'.$aLanguage['code']), 40, 255, (isset($aCurImageTitle[$aLanguage['code']]) ? html::escapeHTML($aCurImageTitle[$aLanguage['code']]) : '')) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_alt_1_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? __('m_galleries_item_image_alt_text') : printf(__('m_galleries_item_image_alt_text_in_%s'), $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_images_alt_1['.$aLanguage['code'].']','p_images_alt_1_'.$aLanguage['code']), 40, 255, (isset($aCurImageAlt[$aLanguage['code']]) ? html::escapeHTML($aCurImageAlt[$aLanguage['code']]) : '')) ?></p>

	<?php endforeach; ?>

	<p><a href="<?php echo $aItemData['image']['img_url']?>" rel="item_images"
	title="<?php echo util::escapeAttrHTML($aItemData['locales'][$aLanguage['code']]['title']) ?>"
	class="modal"><img src="<?php echo $sCurImageUrl ?>" <?php echo $sCurImageAttr ?> alt="" /></a></p>

	<p><a href="module.php?m=galleries&amp;action=gallery&amp;gallery_id=<?php echo $iGalleryId ?>&amp;delete_image=1"
	onclick="return window.confirm('<?php echo html::escapeJS(_e('m_galleries_item_delete_image_confirm')) ?>')"
	class="icon delete"><?php _e('m_galleries_item_delete_image') ?></a></p>

<?php else : ?>

	<?php foreach ($okt->languages->list as $aLanguage) : ?>
	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_title_1_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? __('m_galleries_item_image_title') : printf(__('m_galleries_item_image_title_in_%s'), $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_images_title_1['.$aLanguage['code'].']', 'p_images_title_1_'.$aLanguage['code']), 40, 255, '') ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_alt_1_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? __('m_galleries_item_image_alt_text') : printf(__('m_galleries_item_image_alt_text_in_%s'), $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_images_alt_1['.$aLanguage['code'].']', 'p_images_alt_1_'.$aLanguage['code']), 40, 255, '') ?></p>
	<?php endforeach; ?>

<?php endif; ?>

<p class="note"><?php printf(__('c_c_maximum_file_size_%s'), util::l10nFileSize(OKT_MAX_UPLOAD_SIZE)) ?></p>

<?php

$aItemData['tabs'][20]['content'] = ob_get_clean();


# onglet options
$aItemData['tabs'][30] = array(
	'id' => 'tab-options',
	'title' => __('m_galleries_item_tab_options'),
	'content' => ''
);

ob_start(); ?>

	<h3><?php _e('m_galleries_item_tab_title_options')?></h3>

	<div class="two-cols">
		<p class="field col"><label for="p_gallery_id" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_galleries_item_gallery')?></label>
		<select id="p_gallery_id" name="p_gallery_id">
			<?php
			while ($rsGalleriesList->fetch())
			{
				echo '<option value="'.$rsGalleriesList->id.'"'.
				($aItemData['item']['gallery_id'] == $rsGalleriesList->id ? ' selected="selected"' : '').
				'>'.str_repeat('&nbsp;&nbsp;&nbsp;', $rsGalleriesList->level).
				'&bull; '.html::escapeHTML($rsGalleriesList->title).
				'</option>';
			}
			?>
		</select></p>

		<p class="field col"><label><?php echo form::checkbox('p_active', 1, $aItemData['item']['active']) ?> <?php _e('c_c_status_Online') ?></label></p>

		<?php if (!empty($okt->galleries->config->templates['item']['usables'])) : ?>
		<p class="field col"><label for="p_tpl"><?php _e('m_galleries_item_tpl') ?></label>
		<?php echo form::select('p_tpl', $aTplChoices, $aItemData['item']['tpl'])?></p>
		<?php endif; ?>

	</div>

<?php

$aItemData['tabs'][30]['content'] = ob_get_clean();


# onglet seo
if ($okt->galleries->config->enable_metas)
{
	$aItemData['tabs'][40] = array(
		'id' => 'tab-seo',
		'title' => __('m_galleries_item_tab_seo'),
		'content' => ''
	);

	ob_start(); ?>

	<h3><?php _e('c_c_seo_help') ?></h3>

	<?php foreach ($okt->languages->list as $aLanguage) : ?>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_tag_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_title_tag') : printf(__('c_c_seo_title_tag_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_title_tag['.$aLanguage['code'].']','p_title_tag_'.$aLanguage['code']), 60, 255, html::escapeHTML($aItemData['locales'][$aLanguage['code']]['title_tag'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_desc') : printf(__('c_c_seo_meta_desc_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, html::escapeHTML($aItemData['locales'][$aLanguage['code']]['meta_description'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_seo_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_title_seo') : printf(__('c_c_seo_title_seo_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_title_seo['.$aLanguage['code'].']','p_title_seo_'.$aLanguage['code']), 60, 255, html::escapeHTML($aItemData['locales'][$aLanguage['code']]['title_seo'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_keywords') : printf(__('c_c_seo_meta_keywords_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 58, 5, html::escapeHTML($aItemData['locales'][$aLanguage['code']]['meta_keywords'])) ?></p>

	<div class="lockable" lang="<?php echo $aLanguage['code'] ?>">
		<p class="field"><label for="p_slug_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_url') : printf(__('c_c_seo_url_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_slug['.$aLanguage['code'].']','p_slug_'.$aLanguage['code']), 60, 255, html::escapeHTML($aItemData['locales'][$aLanguage['code']]['slug'])) ?>
		<span class="lockable-note"><?php _e('c_c_seo_warning_edit_url') ?></span></p>
	</div>

	<?php endforeach; ?>

	<?php

	$aItemData['tabs'][40]['content'] = ob_get_clean();
}


# -- TRIGGER MODULE GALLERIES : adminItemBuildTabs
$okt->galleries->triggers->callTrigger('adminItemBuildTabs', $okt, $aItemData['tabs']);

$aItemData['tabs']->ksort();
?>

<form id="item-form" action="module.php" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<?php foreach ($aItemData['tabs'] as $aTabInfos) : ?>
			<li><a href="#<?php echo $aTabInfos['id'] ?>"><span><?php echo $aTabInfos['title'] ?></span></a></li>
			<?php endforeach; ?>
		</ul>

		<?php foreach ($aItemData['tabs'] as $sTabUrl=>$aTabInfos) : ?>
		<div id="<?php echo $aTabInfos['id'] ?>">
			<?php echo $aTabInfos['content'] ?>
		</div><!-- #<?php echo $aTabInfos['id'] ?> -->
		<?php endforeach; ?>
	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','galleries'); ?>
	<?php echo form::hidden('action',!empty($aItemData['item']['id']) ? 'edit' : 'add'); ?>
	<?php echo !empty($aItemData['item']['id']) ? form::hidden('item_id', $aItemData['item']['id']) : ''; ?>
	<?php echo form::hidden('sended',1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php echo !empty($aItemData['item']['id']) ? _e('c_c_action_edit') : _e('c_c_action_add'); ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
