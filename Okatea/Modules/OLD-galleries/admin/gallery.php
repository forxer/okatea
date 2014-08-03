<?php
/**
 * @ingroup okt_module_galleries
 * @brief Page de gestion d'une galerie
 *
 */
use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Forms\Statics\SelectOption;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Themes\TemplatesSet;

# Accès direct interdit
if (! defined('ON_MODULE'))
	die();
	
	/* Initialisations
----------------------------------------------------------*/
	
# Chargement des locales
$okt['l10n']->loadFile(__DIR__ . '/../Locales/%s/admin.gallery');

# Récupération de la liste complète des galeries
$rsGalleriesList = $okt->galleries->tree->getGalleries(array(
	'active' => 2,
	'with_count' => true,
	'language' => $okt->user->language
));

$iGalleryId = null;

$aGalleryData['db'] = new ArrayObject();
$aGalleryData['locales'] = new ArrayObject();

$aGalleryData['db'] = array();
$aGalleryData['db']['active'] = 1;
$aGalleryData['db']['locked'] = 0;
$aGalleryData['db']['parent_id'] = 0;
$aGalleryData['db']['password'] = '';
$aGalleryData['db']['tpl'] = '';
$aGalleryData['db']['items_tpl'] = '';
$aGalleryData['db']['created_at'] = '';
$aGalleryData['db']['updated_at'] = '';

$aGalleryData['locales'] = array();

foreach ($okt['languages']->list as $aLanguage)
{
	$aGalleryData['locales'][$aLanguage['code']] = array();
	
	$aGalleryData['locales'][$aLanguage['code']]['title'] = '';
	$aGalleryData['locales'][$aLanguage['code']]['content'] = '';
	
	if ($okt->galleries->config->enable_metas)
	{
		$aGalleryData['locales'][$aLanguage['code']]['title_seo'] = '';
		$aGalleryData['locales'][$aLanguage['code']]['title_tag'] = '';
		$aGalleryData['locales'][$aLanguage['code']]['meta_description'] = '';
		$aGalleryData['locales'][$aLanguage['code']]['meta_keywords'] = '';
		$aGalleryData['locales'][$aLanguage['code']]['slug'] = '';
	}
}

$aGalleryData['image'] = array();

$rsGallery = null;
$rsGalleryI18n = null;

# update a gallery ?
if (! empty($_REQUEST['gallery_id']))
{
	$iGalleryId = intval($_REQUEST['gallery_id']);
	
	$rsGallery = $okt->galleries->tree->getGallery($iGalleryId);
	
	if ($rsGallery->isEmpty())
	{
		$okt->error->set(sprintf(__('m_galleries_error_gallery_%s_doesnt_exist'), $iGalleryId));
		$iGalleryId = null;
	}
	else
	{
		# si vérouillé et pas super-admin on renvoi sur la liste
		if ($rsGallery->locked && ! $okt->user->is_superadmin)
		{
			http::redirect('module.php?m=galleries&action=index');
		}
		
		$aGalleryData['db']['active'] = $rsGallery->active;
		$aGalleryData['db']['locked'] = $rsGallery->locked;
		$aGalleryData['db']['parent_id'] = $rsGallery->parent_id;
		$aGalleryData['db']['password'] = $rsGallery->password;
		$aGalleryData['db']['tpl'] = $rsGallery->tpl;
		$aGalleryData['db']['items_tpl'] = $rsGallery->items_tpl;
		$aGalleryData['db']['created_at'] = $rsGallery->created_at;
		$aGalleryData['db']['updated_at'] = $rsGallery->updated_at;
		
		$rsGalleryI18n = $okt->galleries->tree->getGalleryL10n($iGalleryId);
		
		foreach ($okt['languages']->list as $aLanguage)
		{
			while ($rsGalleryI18n->fetch())
			{
				if ($rsGalleryI18n->language == $aLanguage['code'])
				{
					$aGalleryData['locales'][$aLanguage['code']]['title'] = $rsGalleryI18n->title;
					$aGalleryData['locales'][$aLanguage['code']]['content'] = $rsGalleryI18n->content;
					
					if ($okt->galleries->config->enable_metas)
					{
						$aGalleryData['locales'][$aLanguage['code']]['title_seo'] = $rsGalleryI18n->title_seo;
						$aGalleryData['locales'][$aLanguage['code']]['title_tag'] = $rsGalleryI18n->title_tag;
						$aGalleryData['locales'][$aLanguage['code']]['meta_description'] = $rsGalleryI18n->meta_description;
						$aGalleryData['locales'][$aLanguage['code']]['meta_keywords'] = $rsGalleryI18n->meta_keywords;
						$aGalleryData['locales'][$aLanguage['code']]['slug'] = $rsGalleryI18n->slug;
					}
				}
			}
		}
		
		# image
		$aGalleryData['image'] = $rsGallery->getImagesInfo();
		
		# galeries voisines
		$aGalleryData['siblings'] = $okt->galleries->tree->getChildren($rsGallery->parent_id, false, $okt->user->language);
		
		$aGalleryData['num_items'] = $rsGallery->num_items;
		$aGalleryData['num_total_items'] = $rsGallery->num_total;
	}
}

# -- TRIGGER MODULE GALLERIES : adminGalleryInit
$okt->galleries->triggers->callTrigger('adminGalleryInit', $aGalleryData, $rsGallery, $rsGalleryI18n);

/* Traitements
----------------------------------------------------------*/

# AJAX : changement de l'ordre des galeries voisines
if (! empty($_GET['ajax_update_order']))
{
	$order = ! empty($_GET['ord']) && is_array($_GET['ord']) ? $_GET['ord'] : array();
	
	if (! empty($order))
	{
		try
		{
			foreach ($order as $ord => $id)
			{
				$ord = ((integer) $ord) + 1;
				$okt->galleries->tree->setGalleryPosition($id, $ord);
			}
			
			$okt->galleries->tree->rebuild();
		}
		catch (\Exception $e)
		{
			die($e->getMessage());
		}
	}
	
	exit();
}

# POST : changement de l'ordre des galeries voisines
if (! empty($_POST['order_galleries']))
{
	$order = ! empty($_POST['p_order']) && is_array($_POST['p_order']) ? $_POST['p_order'] : array();
	
	asort($order);
	$order = array_keys($order);
	
	if (! empty($order))
	{
		try
		{
			foreach ($order as $ord => $id)
			{
				$ord = ((integer) $ord) + 1;
				$okt->galleries->tree->setGalleryPosition($id, $ord);
			}
			
			$okt->galleries->tree->rebuild();
			
			$okt['flash']->success(__('m_galleries_gallery_order_update'));
			
			http::redirect('module.php?m=galleries&action=gallery&gallery_id=' . $iGalleryId);
		}
		catch (\Exception $e)
		{
			$okt->error->set($e->getMessage());
		}
	}
}

# switch status
if (! empty($_GET['switch_status']) && ! empty($iGalleryId))
{
	try
	{
		$okt->galleries->tree->switchGalleryStatus($iGalleryId);
		
		# log admin
		$okt->logAdmin->info(array(
			'code' => 32,
			'component' => 'galleries',
			'message' => 'gallery #' . $iGalleryId
		));
		
		http::redirect('module.php?m=galleries&action=gallery&gallery_id=' . $iGalleryId . '&switched=1');
	}
	catch (\Exception $e)
	{
		$okt->error->set($e->getMessage());
	}
}

# suppression de l'image
if (! empty($_GET['delete_image']) && ! empty($iGalleryId))
{
	$okt->galleries->tree->deleteImage($iGalleryId, $_GET['delete_image']);
	
	# log admin
	$okt->logAdmin->info(array(
		'code' => 41,
		'component' => 'galleries',
		'message' => 'gallery #' . $iGalleryId
	));
	
	$okt['flash']->success(__('m_galleries_gallery_updated'));
	
	http::redirect('module.php?m=galleries&action=gallery&gallery_id=' . $iGalleryId);
}

# ajout/modification de la galerie
if (! empty($_POST['sended']))
{
	$aGalleryData['db']['id'] = $iGalleryId;
	$aGalleryData['db']['active'] = ! empty($_POST['p_active']) ? 1 : 0;
	$aGalleryData['db']['locked'] = ! empty($_POST['p_locked']) && $okt->user->is_superadmin ? 1 : 0;
	$aGalleryData['db']['parent_id'] = ! empty($_POST['p_parent_id']) ? intval($_POST['p_parent_id']) : 0;
	$aGalleryData['db']['password'] = ! empty($_POST['p_password']) ? $_POST['p_password'] : '';
	$aGalleryData['db']['tpl'] = ! empty($_POST['p_tpl']) ? $_POST['p_tpl'] : null;
	$aGalleryData['db']['items_tpl'] = ! empty($_POST['p_items_tpl']) ? $_POST['p_items_tpl'] : null;
	
	foreach ($okt['languages']->list as $aLanguage)
	{
		$aGalleryData['locales'][$aLanguage['code']]['title'] = ! empty($_POST['p_title'][$aLanguage['code']]) ? $_POST['p_title'][$aLanguage['code']] : '';
		$aGalleryData['locales'][$aLanguage['code']]['content'] = ! empty($_POST['p_content'][$aLanguage['code']]) ? $_POST['p_content'][$aLanguage['code']] : '';
		
		if ($okt->galleries->config->enable_metas)
		{
			$aGalleryData['locales'][$aLanguage['code']]['title_seo'] = ! empty($_POST['p_title_seo'][$aLanguage['code']]) ? $_POST['p_title_seo'][$aLanguage['code']] : '';
			$aGalleryData['locales'][$aLanguage['code']]['title_tag'] = ! empty($_POST['p_title_tag'][$aLanguage['code']]) ? $_POST['p_title_tag'][$aLanguage['code']] : '';
			$aGalleryData['locales'][$aLanguage['code']]['meta_description'] = ! empty($_POST['p_meta_description'][$aLanguage['code']]) ? $_POST['p_meta_description'][$aLanguage['code']] : '';
			$aGalleryData['locales'][$aLanguage['code']]['meta_keywords'] = ! empty($_POST['p_meta_keywords'][$aLanguage['code']]) ? $_POST['p_meta_keywords'][$aLanguage['code']] : '';
			$aGalleryData['locales'][$aLanguage['code']]['slug'] = ! empty($_POST['p_slug'][$aLanguage['code']]) ? $_POST['p_slug'][$aLanguage['code']] : '';
		}
	}
	
	# -- TRIGGER MODULE GALLERIES : adminPopulateGalleryData
	$okt->galleries->triggers->callTrigger('adminPopulateGalleryData', $aGalleryData);
	
	# vérification des données avant modification dans la BDD
	if ($okt->galleries->tree->checkPostData($aGalleryData['db'], $aGalleryData['locales']))
	{
		$oGalleryCursor = $okt->galleries->tree->openGalleryCursor($aGalleryData['db']);
		
		# update gallery
		if (! empty($iGalleryId))
		{
			try
			{
				# -- TRIGGER MODULE GALLERIES : beforeGalleryUpdate
				$okt->galleries->triggers->callTrigger('beforeGalleryUpdate', $oGalleryCursor, $aGalleryData);
				
				$okt->galleries->tree->updGallery($oGalleryCursor, $aGalleryData['locales']);
				
				# -- TRIGGER MODULE GALLERIES : afterGalleryUpdate
				$okt->galleries->triggers->callTrigger('afterGalleryUpdate', $oGalleryCursor, $aGalleryData);
				
				# log admin
				$okt->logAdmin->info(array(
					'code' => 41,
					'component' => 'galleries',
					'message' => 'gallery #' . $iGalleryId
				));
				
				$okt['flash']->success(__('m_galleries_gallery_updated'));
				
				http::redirect('module.php?m=galleries&action=gallery&gallery_id=' . $iGalleryId);
			}
			catch (\Exception $e)
			{
				$okt->error->set($e->getMessage());
			}
		}
		
		# add gallery
		else
		{
			try
			{
				# -- TRIGGER MODULE GALLERIES : beforeGalleryCreate
				$okt->galleries->triggers->callTrigger('beforeGalleryCreate', $oGalleryCursor, $aGalleryData);
				
				$iGalleryId = $okt->galleries->tree->addGallery($oGalleryCursor, $aGalleryData['locales']);
				
				# -- TRIGGER MODULE GALLERIES : afterGalleryCreate
				$okt->galleries->triggers->callTrigger('afterGalleryCreate', $oGalleryCursor, $aGalleryData);
				
				# log admin
				$okt->logAdmin->info(array(
					'code' => 40,
					'component' => 'galleries',
					'message' => 'gallery #' . $iGalleryId
				));
				
				$okt['flash']->success(__('m_galleries_gallery_added'));
				
				http::redirect('module.php?m=galleries&action=gallery&gallery_id=' . $iGalleryId);
			}
			catch (\Exception $e)
			{
				$okt->error->set($e->getMessage());
			}
		}
	}
}

/* Affichage
----------------------------------------------------------*/

# Liste des templates utilisables
$oTemplatesList = new TemplatesSet($okt, $okt->galleries->config->templates['list'], 'galleries/list', 'list');
$aTplChoices = array_merge(array(
	'&nbsp;' => null
), $oTemplatesList->getUsablesTemplatesForSelect($okt->galleries->config->templates['list']['usables']));

$oItemsTemplatesList = new TemplatesSet($okt, $okt->galleries->config->templates['item'], 'galleries/item', 'item');
$aItemsTplChoices = array_merge(array(
	'&nbsp;' => null
), $oItemsTemplatesList->getUsablesTemplatesForSelect($okt->galleries->config->templates['item']['usables']));

# Calcul de la liste des parents possibles
$aAllowedParents = array(
	__('m_galleries_gallery_first_level') => 0
);

$aChildrens = array();
if ($iGalleryId)
{
	$rsDescendants = $okt->galleries->tree->getDescendants($iGalleryId, true);
	
	while ($rsDescendants->fetch())
	{
		$aChildrens[] = $rsDescendants->id;
	}
}

while ($rsGalleriesList->fetch())
{
	if (! in_array($rsGalleriesList->id, $aChildrens))
	{
		$aAllowedParents[] = new SelectOption(str_repeat('&nbsp;&nbsp;&nbsp;', $rsGalleriesList->level - 1) . '&bull; ' . html::escapeHTML($rsGalleriesList->title), $rsGalleriesList->id);
	}
}

# button set
$okt->page->setButtonset('galleriesGaleryBtSt', array(
	'id' => 'galleries-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_c_action_Go_back'),
			'url' => 'module.php?m=galleries&amp;action=index',
			'ui-icon' => 'arrowreturnthick-1-w'
		)
	)
));

if ($iGalleryId)
{
	# bouton add cat
	$okt->page->addButton('galleriesGaleryBtSt', array(
		'permission' => true,
		'title' => __('m_galleries_menu_add_gallery'),
		'url' => 'module.php?m=galleries&amp;action=gallery',
		'ui-icon' => 'plusthick'
	));
	# bouton ajout d'élément
	$okt->page->addButton('galleriesGaleryBtSt', array(
		'permission' => $okt->checkPerm('galleries_add'),
		'title' => __('m_galleries_menu_add_item'),
		'url' => 'module.php?m=galleries&amp;action=add',
		'ui-icon' => 'image'
	));
	
	# bouton ajout de plusieurs éléments
	$okt->page->addButton('galleriesGaleryBtSt', array(
		'permission' => $okt->galleries->config->enable_multiple_upload && $okt->checkPerm('galleries_add'),
		'title' => __('m_galleries_menu_add_items'),
		'url' => 'module.php?m=galleries&amp;action=add_multiples',
		'ui-icon' => 'folder-collapsed'
	));
	
	# bouton ajout depuis un fichier ZIP
	$okt->page->addButton('galleriesGaleryBtSt', array(
		'permission' => $okt->galleries->config->enable_zip_upload && $okt->checkPerm('galleries_add'),
		'title' => __('m_galleries_menu_add_zip'),
		'url' => 'module.php?m=galleries&amp;action=add_zip',
		'ui-icon' => 'script'
	));
	# bouton switch statut
	$okt->page->addButton('galleriesGaleryBtSt', array(
		'permission' => true,
		'title' => ($aGalleryData['db']['active'] ? __('c_c_status_Online') : __('c_c_status_Offline')),
		'url' => 'module.php?m=galleries&amp;action=gallery&amp;switch_status=1&amp;gallery_id=' . $iGalleryId,
		'ui-icon' => ($aGalleryData['db']['active'] ? 'volume-on' : 'volume-off'),
		'active' => $aGalleryData['db']['active']
	));
	# bouton de suppression
	$okt->page->addButton('galleriesGaleryBtSt', array(
		'permission' => ($aGalleryData['num_items'] == 0),
		'title' => __('c_c_action_Delete'),
		'url' => 'module.php?m=galleries&amp;delete=' . $iGalleryId,
		'ui-icon' => 'closethick',
		'onclick' => 'return window.confirm(\'' . html::escapeJS(__('m_galleries_gallery_confirm_del_gallery')) . '\')'
	));
	# bouton vers la galerie côté public
	$okt->page->addButton('galleriesGaleryBtSt', array(
		'permission' => ($aGalleryData['db']['active'] ? true : false),
		'title' => __('c_c_action_Show'),
		'url' => GalleriesHelpers::getGalleryUrl($aGalleryData['locales'][$okt->user->language]['slug']),
		'ui-icon' => 'extlink'
	));
}

# Titre de la page
if ($iGalleryId)
{
	$path = $okt->galleries->tree->getPath($iGalleryId, true, $okt->user->language);
	
	while ($path->fetch())
	{
		$okt->page->addGlobalTitle($path->title, 'module.php?m=galleries&action=gallery&gallery_id=' . $path->id);
	}
}
else
{
	$okt->page->addGlobalTitle(__('m_galleries_gallery_add_gallery'));
}

# Lockable
$okt->page->lockable();

# Tabs
$okt->page->tabs();

# RTE
$okt->page->applyRte($okt->galleries->config->enable_gal_rte, 'textarea.richTextEditor');

# Lang switcher
if (! $okt['languages']->unique)
{
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Sortable JS
if ($iGalleryId)
{
	$okt->page->js->addReady('
		$("#sortable").sortable({
			placeholder: "ui-state-highlight",
			axis: "y",
			revert: true,
			cursor: "move",
			change: function(event, ui) {
				$("#page,#sortable").css("cursor", "progress");
			},
			update: function(event, ui) {
				var result = $("#sortable").sortable("serialize");

				$.ajax({
					data: result,
					url: "module.php?m=galleries&action=gallery&gallery_id=' . $iGalleryId . '&ajax_update_order=1",
					success: function(data) {
						$("#page").css("cursor", "default");
						$("#sortable").css("cursor", "move");
					},
					error: function(data) {
						$("#page").css("cursor", "default");
						$("#sortable").css("cursor", "move");
					}
				});
			}

		});

		$("#sortable").find("input").hide();
		$("#save_order").hide();
		$("#sortable").css("cursor", "move");
	');
}

# En-tête
require OKT_ADMIN_HEADER_FILE;
?>

<?php echo $okt->page->getButtonSet('galleriesGaleryBtSt'); ?>

<?php if (!empty($iGalleryId)) : ?>
<p><?php printf(__('m_galleries_gallery_added_on'), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'), $aGalleryData['db']['created_at']).'</em>')?>

<?php if ($aGalleryData['db']['updated_at'] > $aGalleryData['db']['created_at']) : ?>
<span class="note"><?php printf(__('m_galleries_gallery_last_edit'), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'), $aGalleryData['db']['updated_at']).'</em>') ?></span>
<?php endif; ?>
</p>
<?php endif; ?>

<form action="module.php" method="post" enctype="multipart/form-data">

	<div id="tabered">
		<ul>
			<li><a href="#tab_gallery"><span><?php _e('m_galleries_gallery_gallery') ?></span></a></li>
			<?php if ($okt->galleries->config->images_gal['enable']) : ?>
			<li><a href="#tab_image"><span><?php _e('m_galleries_gallery_img') ?></span></a></li>
			<?php endif; ?>
			<li><a href="#tab_options"><span><?php _e('m_galleries_gallery_options') ?></span></a></li>
			<?php if ($okt->galleries->config->enable_metas) : ?>
			<li><a href="#tab_seo"><span><?php _e('c_c_seo') ?></span></a></li>
			<?php endif; ?>
		</ul>

		<div id="tab_gallery">
			<h3><?php _e('m_galleries_gallery_gallery_title') ?></h3>

			<?php foreach ($okt['languages']->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label for="p_title_<?php echo $aLanguage['code'] ?>"
					title="<?php _e('c_c_required_field') ?>" class="required"><?php $okt['languages']->unique ? _e('m_galleries_gallery_title') : printf(__('m_galleries_gallery_title_in_%s'),$aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 100, 255, html::escapeHTML($aGalleryData['locales'][$aLanguage['code']]['title'])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label for="p_content_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? _e('m_galleries_gallery_description') : printf(__('m_galleries_gallery_description_in_%s'),$aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
			<?php echo form::textarea(array('p_content['.$aLanguage['code'].']','p_content_'.$aLanguage['code']), 97, 15, $aGalleryData['locales'][$aLanguage['code']]['content'],'richTextEditor') ?></p>

			<?php endforeach; ?>

		</div>
		<!-- #tab_gallery -->

		<div id="tab_image">
			<h3><?php _e('m_galleries_gallery_img_title') ?></h3>

			<p class="field">
				<label for="p_image"><?php _e('m_galleries_gallery_image_file') ?></label>
			<?php echo form::file('p_images_1') ?></p>

			<?php 
# il y a une image ?
			if (! empty($aGalleryData['image']))
			:
				
				# affichage square ou icon ?
				if (isset($aGalleryData['image']['min_url']))
				{
					$sCurImageUrl = $aGalleryData['image']['min_url'];
					$sCurImageAttr = $aGalleryData['image']['min_attr'];
				}
				elseif (isset($aGalleryData['image']['square_url']))
				{
					$sCurImageUrl = $aGalleryData['image']['square_url'];
					$sCurImageAttr = $aGalleryData['image']['square_attr'];
				}
				else
				{
					$sCurImageUrl = $okt['public_url'] . '/img/media/image.png';
					$sCurImageAttr = ' width="48" height="48" ';
				}
				
				$aCurImageAlt = isset($aGalleryData['image']['alt']) ? $aGalleryData['image']['alt'] : array();
				$aCurImageTitle = isset($aGalleryData['image']['title']) ? $aGalleryData['image']['title'] : array();
				
				?>

				<?php foreach ($okt['languages']->list as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label for="p_images_title_1_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? __('m_galleries_gallery_image_title') : printf(__('m_galleries_gallery_image_title_in_%s'), $aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_images_title_1['.$aLanguage['code'].']','p_images_title_1_'.$aLanguage['code']), 40, 255, (isset($aCurImageTitle[$aLanguage['code']]) ? html::escapeHTML($aCurImageTitle[$aLanguage['code']]) : '')) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label for="p_images_alt_1_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? __('m_galleries_gallery_image_alt_text') : printf(__('m_galleries_gallery_image_alt_text_in_%s'), $aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_images_alt_1['.$aLanguage['code'].']','p_images_alt_1_'.$aLanguage['code']), 40, 255, (isset($aCurImageAlt[$aLanguage['code']]) ? html::escapeHTML($aCurImageAlt[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>

				<p>
				<a href="<?php echo $aGalleryData['image']['img_url']?>"
					rel="post_images"
					title="<?php echo $view->escapeHtmlAttr($aGalleryData['locales'][$aLanguage['code']]['title']) ?>"
					class="modal"><img src="<?php echo $sCurImageUrl ?>"
					<?php echo $sCurImageAttr ?> alt="" /></a>
			</p>

			<p>
				<a
					href="module.php?m=galleries&amp;action=gallery&amp;gallery_id=<?php echo $iGalleryId ?>&amp;delete_image=1"
					onclick="return window.confirm('<?php echo html::escapeJS(_e('m_galleries_gallery_delete_image_confirm')) ?>')"
					class="icon delete"><?php _e('m_galleries_gallery_delete_image') ?></a>
			</p>

			<?php else : ?>

				<?php foreach ($okt['languages']->list as $aLanguage) : ?>
				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label for="p_images_title_1_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? __('m_galleries_gallery_image_title') : printf(__('m_galleries_gallery_image_title_in_%s'), $aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_images_title_1['.$aLanguage['code'].']', 'p_images_title_1_'.$aLanguage['code']), 40, 255, '') ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label for="p_images_alt_1_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? __('m_galleries_gallery_image_alt_text') : printf(__('m_galleries_gallery_image_alt_text_in_%s'), $aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_images_alt_1['.$aLanguage['code'].']', 'p_images_alt_1_'.$aLanguage['code']), 40, 255, '') ?></p>
				<?php endforeach; ?>

			<?php endif; ?>

			<p class="note"><?php echo Utilities::getMaxUploadSizeNotice() ?></p>

		</div>
		<!-- #tab_image -->

		<div id="tab_options">
			<h3><?php _e('m_galleries_gallery_options_title') ?></h3>

			<div class="two-cols">
				<p class="field col">
					<label for="p_parent_id"><?php _e('m_galleries_gallery_parent') ?></label>
				<?php echo form::select('p_parent_id', $aAllowedParents, $aGalleryData['db']['parent_id']) ?></p>


				<?php if ($okt->galleries->config->enable_gal_password) : ?>
				<p class="field col">
					<label for="p_password"><?php _e('m_galleries_gallery_password')?></label>
				<?php echo form::text('p_password', 50, 255, html::escapeHTML($aGalleryData['db']['password']))?>
				<span class="note"><?php _e('m_galleries_gallery_password_empty') ?></span>
				</p>
				<?php endif; ?>
			</div>

			<div class="two-cols">
				<p class="field col">
					<label><?php echo form::checkbox('p_active', 1, $aGalleryData['db']['active']) ?> <?php _e('c_c_status_Online') ?></label>
				</p>

				<?php if ($okt->user->is_superadmin) : ?>
				<p class="field col">
					<label><?php echo form::checkbox('p_locked', 1, $aGalleryData['db']['locked']) ?> <?php _e('m_galleries_gallery_locked') ?></label>
				</p>
				<?php endif; ?>
			</div>

			<div class="two-cols">
				<?php if (!empty($okt->galleries->config->templates['list']['usables'])) : ?>
				<p class="field col">
					<label for="p_tpl"><?php _e('m_galleries_gallery_tpl') ?></label>
				<?php echo form::select('p_tpl', $aTplChoices, $aGalleryData['db']['tpl'])?></p>
				<?php endif; ?>

				<?php if (!empty($okt->galleries->config->templates['item']['usables'])) : ?>
				<p class="field col">
					<label for="p_items_tpl"><?php _e('m_galleries_gallery_items_tpl') ?></label>
				<?php echo form::select('p_items_tpl', $aItemsTplChoices, $aGalleryData['db']['items_tpl'])?></p>
				<?php endif; ?>
			</div>

		</div>
		<!-- #tab_options -->

		<?php if ($okt->galleries->config->enable_metas) : ?>
		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<?php foreach ($okt['languages']->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label for="p_title_tag_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? _e('c_c_seo_title_tag') : printf(__('c_c_seo_title_tag_in_%s'),$aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title_tag['.$aLanguage['code'].']','p_title_tag_'.$aLanguage['code']), 60, 255, html::escapeHTML($aGalleryData['locales'][$aLanguage['code']]['title_tag'])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? _e('c_c_seo_meta_desc') : printf(__('c_c_seo_meta_desc_in_%s'),$aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, html::escapeHTML($aGalleryData['locales'][$aLanguage['code']]['meta_description'])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label for="p_title_seo_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? _e('c_c_seo_title_seo') : printf(__('c_c_seo_title_seo_in_%s'),$aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title_seo['.$aLanguage['code'].']','p_title_seo_'.$aLanguage['code']), 60, 255, html::escapeHTML($aGalleryData['locales'][$aLanguage['code']]['title_seo'])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>">
				<label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? _e('c_c_seo_meta_keywords') : printf(__('c_c_seo_meta_keywords_in_%s'),$aLanguage['title']) ?> <span
					class="lang-switcher-buttons"></span></label>
			<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 58, 5, html::escapeHTML($aGalleryData['locales'][$aLanguage['code']]['meta_keywords'])) ?></p>

			<div class="lockable" lang="<?php echo $aLanguage['code'] ?>">
				<p class="field">
					<label for="p_slug_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->unique ? _e('c_c_seo_url') : printf(__('c_c_seo_url_in_%s'),$aLanguage['title']) ?> <span
						class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_slug['.$aLanguage['code'].']','p_slug_'.$aLanguage['code']), 60, 255, html::escapeHTML($aGalleryData['locales'][$aLanguage['code']]['slug']))?>
				<span class="lockable-note"><?php _e('c_c_seo_warning_edit_url') ?></span>
				</p>
			</div>

			<?php endforeach; ?>
		</div>
		<!-- #tab_seo -->
		<?php endif; ?>

	</div>
	<!-- #tabered -->

	<p><?php echo form::hidden(array('m'), 'galleries'); ?>
	<?php echo form::hidden(array('action'), 'gallery'); ?>
	<?php echo !empty($iGalleryId) ? form::hidden('gallery_id', $iGalleryId) : ''; ?>
	<?php echo form::hidden('sended', 1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit"
			value="<?php echo !empty($iGalleryId) ? _e('c_c_action_edit') : _e('c_c_action_add'); ?>" />
	</p>
</form>


<?php if ($iGalleryId && !$aGalleryData['siblings']->isEmpty()) : ?>
<form action="module.php" method="post">
	<div id="tab_siblings">
		<h3><?php _e('m_galleries_gallery_order_title') ?></h3>

		<ul id="sortable" class="ui-sortable">
		<?php
	
$i = 1;
	while ($aGalleryData['siblings']->fetch())
	:
		?>
			<li id="ord_<?php echo $aGalleryData['siblings']->id; ?>"
				class="ui-state-default"><label
				for="order_<?php echo $aGalleryData['siblings']->id ?>"> <span
					class="ui-icon ui-icon-arrowthick-2-n-s"></span>
			<?php echo html::escapeHTML($aGalleryData['siblings']->title) ?></label>
			<?php echo form::text(array('p_order['.$aGalleryData['siblings']->id.']','p_order_'.$aGalleryData['siblings']->id), 5, 10, $i++) ?></li>
		<?php endwhile; ?>
		</ul>
	</div>
	<!-- #tab_siblings -->

	<p><?php echo form::hidden(array('m'), 'galleries'); ?>
	<?php echo form::hidden(array('action'), 'gallery'); ?>
	<?php echo !empty($iGalleryId) ? form::hidden('gallery_id', $iGalleryId) : ''; ?>
	<?php echo form::hidden('order_galleries', 1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" id="save_order"
			value="<?php _e('c_c_action_save_order') ?>" />
	</p>
</form>
<?php endif; ?>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
