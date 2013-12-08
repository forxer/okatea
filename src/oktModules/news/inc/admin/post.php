<?php
/**
 * @ingroup okt_module_news
 * @brief Ajout ou modification d'un article
 *
 */

use Tao\Admin\Page;
use Tao\Misc\Utilities as util;
use Tao\Forms\StaticFormElements as form;
use Tao\Themes\TemplatesSet;

# Accès direct interdit
if (!defined('ON_NEWS_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
l10n::set(__DIR__.'/../../locales/'.$okt->user->language.'/admin.post');

$bCanViewPage = true;
$bCanEditPost = ($okt->checkPerm('news_usage') || $okt->checkPerm('news_contentadmin'));
$bCanPublish = ($okt->checkPerm('news_publish') || $okt->checkPerm('news_contentadmin'));
$bCanDelete = ($okt->checkPerm('news_delete') || $okt->checkPerm('news_contentadmin'));

# Données de l'article
$aPostData = new ArrayObject();

$aPostData['post'] = array();
$aPostData['post']['id'] = null;

$aPostData['post']['category_id'] = 0;
$aPostData['post']['active'] = 1;
$aPostData['post']['selected'] = 0;
$aPostData['post']['tpl'] = '';
$aPostData['post']['created_at'] = '';
$aPostData['post']['updated_at'] = '';

# If user can't publish
if (!$bCanPublish) {
	$aPostData['post']['active'] = 2;
}

$aPostData['extra'] = array();
$aPostData['extra']['date'] = '';
$aPostData['extra']['hours'] = '';
$aPostData['extra']['minutes'] = '';

$aPostData['locales'] = array();

foreach ($okt->languages->list as $aLanguage)
{
	$aPostData['locales'][$aLanguage['code']] = array();

	$aPostData['locales'][$aLanguage['code']]['title'] = '';
	$aPostData['locales'][$aLanguage['code']]['subtitle'] = '';
	$aPostData['locales'][$aLanguage['code']]['content'] = '';

	if ($okt->news->config->enable_metas)
	{
		$aPostData['locales'][$aLanguage['code']]['title_seo'] = '';
		$aPostData['locales'][$aLanguage['code']]['title_tag'] = '';
		$aPostData['locales'][$aLanguage['code']]['slug'] = '';
		$aPostData['locales'][$aLanguage['code']]['meta_description'] = '';
		$aPostData['locales'][$aLanguage['code']]['meta_keywords'] = '';
	}
}

$aPostData['perms'] = array(0);
$aPostData['images'] = array();
$aPostData['files'] = array();

$rsPost = null;
$rsPostI18n = null;

# update post ?
if (!empty($_REQUEST['post_id']))
{
	$aPostData['post']['id'] = intval($_REQUEST['post_id']);

	$rsPost = $okt->news->getPostsRecordset(array(
		'id' => $aPostData['post']['id']
	));

	if ($rsPost->isEmpty())
	{
		$okt->error->set(sprintf(__('m_news_post_%s_not_exists'), $aPostData['post']['id']));
		$aPostData['post']['id'] = null;
	}
	else
	{
		$bCanEditPost = $rsPost->isEditable();
		$bCanPublish = $rsPost->isPublishable();
		$bCanDelete = $rsPost->isDeletable();

		$aPostData['post']['category_id'] = $rsPost->category_id;
		$aPostData['post']['active'] = $rsPost->active;
		$aPostData['post']['selected'] = $rsPost->selected;
		$aPostData['post']['tpl'] = $rsPost->tpl;
		$aPostData['post']['created_at'] = $rsPost->created_at;
		$aPostData['post']['updated_at'] = $rsPost->updated_at;

		$iPotsTs = strtotime($rsPost->created_at);

		$aPostData['extra']['date'] = date('d-m-Y', $iPotsTs);
		$aPostData['extra']['hours'] = date('H', $iPotsTs);
		$aPostData['extra']['minutes'] = date('i', $iPotsTs);

		$rsPostI18n = $okt->news->getPostI18n($aPostData['post']['id']);

		foreach ($okt->languages->list as $aLanguage)
		{
			while ($rsPostI18n->fetch())
			{
				if ($rsPostI18n->language == $aLanguage['code'])
				{
					$aPostData['locales'][$aLanguage['code']]['title'] = $rsPostI18n->title;
					$aPostData['locales'][$aLanguage['code']]['subtitle'] = $rsPostI18n->subtitle;
					$aPostData['locales'][$aLanguage['code']]['content'] = $rsPostI18n->content;

					if ($okt->news->config->enable_metas)
					{
						$aPostData['locales'][$aLanguage['code']]['title_seo'] = $rsPostI18n->title_seo;
						$aPostData['locales'][$aLanguage['code']]['title_tag'] = $rsPostI18n->title_tag;
						$aPostData['locales'][$aLanguage['code']]['slug'] = $rsPostI18n->slug;
						$aPostData['locales'][$aLanguage['code']]['meta_description'] = $rsPostI18n->meta_description;
						$aPostData['locales'][$aLanguage['code']]['meta_keywords'] = $rsPostI18n->meta_keywords;
					}
				}
			}
		}

		# Images
		if ($okt->news->config->images['enable']) {
			$aPostData['images'] = $rsPost->getImagesInfo();
		}

		# Fichiers
		if ($okt->news->config->files['enable']) {
			$aPostData['files'] = $rsPost->getFilesInfo();
		}

		# Permissions
		if ($okt->news->canUsePerms()) {
			$aPostData['perms'] = $okt->news->getPostPermissions($aPostData['post']['id']);
		}

		# URL
		$sPostUrl = newsHelpers::getPostUrl($aPostData['locales'][$okt->user->language]['slug']);
	}
}


# -- TRIGGER MODULE NEWS : adminPostInit
$okt->news->triggers->callTrigger('adminPostInit', $okt, $aPostData, $rsPost, $rsPostI18n);



/* Traitements
----------------------------------------------------------*/

# switch post status
if (!empty($_GET['switch_status']) && !empty($aPostData['post']['id']) && $bCanEditPost)
{
	try
	{
		$okt->news->switchPostStatus($aPostData['post']['id']);

		# log admin
		$okt->logAdmin->info(array(
			'code' => 32,
			'component' => 'news',
			'message' => 'post #'.$aPostData['post']['id']
		));

		http::redirect('module.php?m=news&action=edit&post_id='.$aPostData['post']['id'].'&switched=1');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# publication de l'article
if (!empty($_GET['publish']) && !empty($aPostData['post']['id']) && $bCanPublish)
{
	$okt->news->publishPost($aPostData['post']['id']);

	# log admin
	$okt->logAdmin->info(array(
		'code' => 41,
		'component' => 'news',
		'message' => 'post #'.$aPostData['post']['id']
	));

	http::redirect('module.php?m=news&action=edit&post_id='.$aPostData['post']['id'].'&published=1');
}

# suppression d'une image
if (!empty($_GET['delete_image']) && !empty($aPostData['post']['id']) && $bCanEditPost)
{
	$okt->news->deleteImage($aPostData['post']['id'],$_GET['delete_image']);

	# log admin
	$okt->logAdmin->info(array(
		'code' => 41,
		'component' => 'news',
		'message' => 'post #'.$aPostData['post']['id']
	));

	http::redirect('module.php?m=news&action=edit&post_id='.$aPostData['post']['id'].'&edited=1');
}

# suppression d'un fichier
if (!empty($_GET['delete_file']) && !empty($aPostData['post']['id']) && $bCanEditPost)
{
	$okt->news->deleteFile($aPostData['post']['id'],$_GET['delete_file']);

	# log admin
	$okt->logAdmin->info(array(
		'code' => 41,
		'component' => 'news',
		'message' => 'post #'.$aPostData['post']['id']
	));

	http::redirect('module.php?m=news&action=edit&post_id='.$aPostData['post']['id'].'&edited=1');
}

#  ajout / modifications d'un article
if (!empty($_POST['sended']) && $bCanEditPost)
{
	$aPostData['post']['category_id'] = !empty($_POST['p_category_id']) ? intval($_POST['p_category_id']) : 0;
	$aPostData['post']['active'] = !empty($_POST['p_active']) ? 1 : 0;
	$aPostData['post']['selected'] = !empty($_POST['p_selected']) ? 1 : 0;
	$aPostData['post']['tpl'] = !empty($_POST['p_tpl']) ? $_POST['p_tpl'] : null;

	$aPostData['post']['created_at'] = $aPostData['post']['created_at'];
	$aPostData['post']['updated_at'] = $aPostData['post']['updated_at'];

	$aPostData['extra']['date'] = !empty($_POST['p_date']) ? $_POST['p_date'] : null;
	$aPostData['extra']['hours'] = !empty($_POST['p_hours']) ? intval($_POST['p_hours']) : null;
	$aPostData['extra']['minutes'] = !empty($_POST['p_minutes']) ? intval($_POST['p_minutes']) : null;

	if (!empty($aPostData['extra']['date']))
	{
		$aPostData['post']['created_at'] = $aPostData['extra']['date'].' '.
			(!empty($aPostData['extra']['hours']) ? $aPostData['extra']['hours'] : date('H')).':'.
			(!empty($aPostData['extra']['minutes']) ? $aPostData['extra']['minutes'] : date('i'));
	}

	foreach ($okt->languages->list as $aLanguage)
	{
		$aPostData['locales'][$aLanguage['code']]['title'] = !empty($_POST['p_title'][$aLanguage['code']]) ? $_POST['p_title'][$aLanguage['code']] : '';
		$aPostData['locales'][$aLanguage['code']]['subtitle'] = !empty($_POST['p_subtitle'][$aLanguage['code']]) ? $_POST['p_subtitle'][$aLanguage['code']] : '';
		$aPostData['locales'][$aLanguage['code']]['content'] = !empty($_POST['p_content'][$aLanguage['code']]) ? $_POST['p_content'][$aLanguage['code']] : '';

		if ($okt->news->config->enable_metas)
		{
			$aPostData['locales'][$aLanguage['code']]['title_seo'] = !empty($_POST['p_title_seo'][$aLanguage['code']]) ? $_POST['p_title_seo'][$aLanguage['code']] : '';
			$aPostData['locales'][$aLanguage['code']]['title_tag'] = !empty($_POST['p_title_tag'][$aLanguage['code']]) ? $_POST['p_title_tag'][$aLanguage['code']] : '';
			$aPostData['locales'][$aLanguage['code']]['slug'] = !empty($_POST['p_slug'][$aLanguage['code']]) ? $_POST['p_slug'][$aLanguage['code']] : '';
			$aPostData['locales'][$aLanguage['code']]['meta_description'] = !empty($_POST['p_meta_description'][$aLanguage['code']]) ? $_POST['p_meta_description'][$aLanguage['code']] : '';
			$aPostData['locales'][$aLanguage['code']]['meta_keywords'] = !empty($_POST['p_meta_keywords'][$aLanguage['code']]) ? $_POST['p_meta_keywords'][$aLanguage['code']] : '';
		}
	}

	$aPostData['perms'] = !empty($_POST['perms']) ? $_POST['perms'] : array();


	# -- TRIGGER MODULE NEWS : adminPopulateData
	$okt->news->triggers->callTrigger('adminPopulateData', $okt, $aPostData);


	# vérification des données avant modification dans la BDD
	if ($okt->news->checkPostData($aPostData['post'], $aPostData['locales'], $aPostData['perms']))
	{
		$aPostData['cursor'] = $okt->news->openPostCursor($aPostData['post']);

		# update post
		if (!empty($aPostData['post']['id']))
		{
			try
			{
				# -- TRIGGER MODULE NEWS : beforePostUpdate
				$okt->news->triggers->callTrigger('beforePostUpdate', $okt, $aPostData);

				$okt->news->updPost($aPostData['cursor'], $aPostData['locales'], $aPostData['perms']);

				# -- TRIGGER MODULE NEWS : afterPostUpdate
				$okt->news->triggers->callTrigger('afterPostUpdate', $okt, $aPostData);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 41,
					'component' => 'news',
					'message' => 'post #'.$aPostData['post']['id']
				));

				http::redirect('module.php?m=news&action=edit&post_id='.$aPostData['post']['id'].'&updated=1');
			}
			catch (Exception $e) {
				$okt->error->set($e->getMessage());
			}
		}

		# add post
		else
		{
			try
			{
				# -- TRIGGER MODULE NEWS : beforePostCreate
				$okt->news->triggers->callTrigger('beforePostCreate', $okt, $aPostData);

				$aPostData['post']['id'] = $okt->news->addPost($aPostData['cursor'], $aPostData['locales'], $aPostData['perms']);

				# -- TRIGGER MODULE NEWS : afterPostCreate
				$okt->news->triggers->callTrigger('afterPostCreate', $okt, $aPostData);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 40,
					'component' => 'news',
					'message' => 'post #'.$aPostData['post']['id']
				));

				http::redirect('module.php?m=news&action=edit&post_id='.$aPostData['post']['id'].'&added=1');
			}
			catch (Exception $e) {
				$okt->error->set($e->getMessage());
			}
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Récupération de la liste complète des rubriques
if ($okt->news->config->categories['enable'])
{
	$rsCategories = $okt->news->categories->getCategories(array(
		'active' => 2,
		'language' => $okt->user->language
	));
}

# Liste des templates utilisables
$oTemplatesItem = new TemplatesSet($okt, $okt->news->config->templates['item'], 'news/item', 'item');
$aTplChoices = array_merge(
	array('&nbsp;' => null),
	$oTemplatesItem->getUsablesTemplatesForSelect($okt->news->config->templates['item']['usables'])
);

# Récupération de la liste des groupes si les permissions sont activées
if ($okt->news->canUsePerms()) {
	$aGroups = $okt->news->getUsersGroupsForPerms(false,true);
}

# ajout bouton retour
$okt->page->addButton('newsBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_Go_back'),
	'url' 			=> 'module.php?m=news&amp;action=index',
	'ui-icon' 		=> 'arrowreturnthick-1-w',
),'before');

# boutons update post
if (!empty($aPostData['post']['id']))
{
	$okt->page->addGlobalTitle(__('m_news_post_edit_a_post'));

	# bouton switch statut
	$okt->page->addButton('newsBtSt',array(
		'permission' 	=> ($aPostData['post']['active'] <= 1 && $bCanEditPost ? true : false),
		'title' 		=> ($aPostData['post']['active'] ? __('c_c_status_Online') : __('c_c_status_Offline')),
		'url' 			=> 'module.php?m=news&amp;action=edit&amp;post_id='.$aPostData['post']['id'].'&amp;switch_status=1',
		'ui-icon' 		=> ($aPostData['post']['active'] ? 'volume-on' : 'volume-off'),
		'active' 		=> $aPostData['post']['active'],
	));
	# bouton publier si autorisé
	$okt->page->addButton('newsBtSt',array(
		'permission' 	=> ($aPostData['post']['active'] == 2 && $bCanPublish ? true : false),
		'title' 		=> __('c_c_action_Publish'),
		'url' 			=> 'module.php?m=news&amp;action=edit&amp;post_id='.$aPostData['post']['id'].'&amp;publish=1',
		'ui-icon' 		=> 'clock'
	));
	# bouton de suppression si autorisé
	$okt->page->addButton('newsBtSt',array(
		'permission' 	=> $bCanDelete,
		'title' 		=> __('c_c_action_Delete'),
		'url' 			=> 'module.php?m=news&amp;action=delete&amp;post_id='.$aPostData['post']['id'],
		'ui-icon' 		=> 'closethick',
		'onclick' 		=> 'return window.confirm(\''.html::escapeJS(__('m_news_post_delete_confirm')).'\')',
	));
	# bouton vers l'article côté public si publié
	$okt->page->addButton('newsBtSt',array(
		'permission' 	=> ($aPostData['post']['active'] ? true : false),
		'title' 		=> __('c_c_action_Show'),
		'url' 			=> $sPostUrl,
		'ui-icon' 		=> 'extlink'
	));

	$okt->page->messages->success('added',__('m_news_post_added'));
	$okt->page->messages->success('updated',__('m_news_post_updated'));
	$okt->page->messages->success('published',__('m_news_post_published'));
}

# boutons add post
else {
	$okt->page->addGlobalTitle(__('m_news_post_add_a_post'));
}

# Lockable
$okt->page->lockable();

# Tabs
$okt->page->tabs();

# Datepicker
$okt->page->datePicker();

# Modal
$okt->page->applyLbl($okt->news->config->lightbox_type);

# RTE
$okt->page->applyRte($okt->news->config->enable_rte,'textarea.richTextEditor');

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Permission checkboxes
$okt->page->updatePermissionsCheckboxes('perm_g_');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('newsBtSt'); ?>

<?php if (!empty($aPostData['post']['id'])) : ?>
	<?php if ($aPostData['post']['active'] == 3) : ?>
	<p><?php printf(__('m_news_post_sheduled_%s'), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aPostData['post']['created_at']).'</em>') ?></p>

	<?php else : ?>
	<p><?php printf(($aPostData['post']['active'] == 2 ? __('m_news_post_added_on') : __('m_news_post_published_on')), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aPostData['post']['created_at']).'</em>') ?>

		<?php if ($aPostData['post']['updated_at'] > $aPostData['post']['created_at']) : ?>
		<span class="note"><?php printf(__('m_news_post_last_edit'), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aPostData['post']['updated_at']).'</em>') ?></span>
		<?php endif; ?>
	</p>
	<?php endif; ?>
<?php endif; ?>


<?php # Construction des onglets
$aPostData['tabs'] = new ArrayObject;

# onglet contenu
$aPostData['tabs'][10] = array(
	'id' => 'tab-content',
	'title' => __('m_news_post_tab_content'),
	'content' => ''
);

ob_start(); ?>

	<h3><?php _e('m_news_post_tab_title_content') ?></h3>

	<?php foreach ($okt->languages->list as $aLanguage) : ?>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_news_post_title') : printf(__('m_news_post_title_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 100, 255, html::escapeHTML($aPostData['locales'][$aLanguage['code']]['title'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_subtitle_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_news_post_subtitle') : printf(__('m_news_post_subtitle_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_subtitle['.$aLanguage['code'].']','p_subtitle_'.$aLanguage['code']), 100, 255, html::escapeHTML($aPostData['locales'][$aLanguage['code']]['subtitle'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_content_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_news_post_content') : printf(__('m_news_post_content_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::textarea(array('p_content['.$aLanguage['code'].']','p_content_'.$aLanguage['code']), 97, 15, $aPostData['locales'][$aLanguage['code']]['content'],'richTextEditor') ?></p>

	<?php endforeach; ?>

<?php

$aPostData['tabs'][10]['content'] = ob_get_clean();


# onglet images
if ($okt->news->config->images['enable'])
{
	$aPostData['tabs'][20] = array(
		'id' => 'tab-images',
		'title' => __('m_news_post_tab_images'),
		'content' => ''
	);

	ob_start(); ?>

	<h3><?php _e('m_news_post_tab_title_images')?></h3>
	<div class="two-cols modal-box">
	<?php for ($i=1; $i<=$okt->news->config->images['number']; $i++) : ?>
		<div class="col">
			<fieldset>
				<legend><?php printf(__('m_news_post_image_%s'), $i) ?></legend>

				<p class="field"><label for="p_images_<?php echo $i ?>"><?php printf(__('m_news_post_image_%s'), $i) ?></label>
				<?php echo form::file('p_images_'.$i) ?></p>

				<?php # il y a une image ?
				if (!empty($aPostData['images'][$i])) :

					# affichage square ou icon ?
					if (isset($aPostData['images'][$i]['min_url'])) {
						$sCurImageUrl = $aPostData['images'][$i]['min_url'];
						$sCurImageAttr = $aPostData['images'][$i]['min_attr'];
					}
					elseif (isset($aPostData['images'][$i]['square_url'])) {
						$sCurImageUrl = $aPostData['images'][$i]['square_url'];
						$sCurImageAttr = $aPostData['images'][$i]['square_attr'];
					}
					else {
						$sCurImageUrl = OKT_PUBLIC_URL.'/img/media/image.png';
						$sCurImageAttr = ' width="48" height="48" ';
					}

					$aCurImageAlt = isset($aPostData['images'][$i]['alt']) ? $aPostData['images'][$i]['alt'] : array();
					$aCurImageTitle = isset($aPostData['images'][$i]['title']) ? $aPostData['images'][$i]['title'] : array();

					?>

					<?php foreach ($okt->languages->list as $aLanguage) : ?>

					<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_news_post_image_title_%s'), $i) : printf(__('m_news_post_image_title_%s_in_%s'), $i, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
					<?php echo form::text(array('p_images_title_'.$i.'['.$aLanguage['code'].']','p_images_title_'.$i.'_'.$aLanguage['code']), 40, 255, (isset($aCurImageTitle[$aLanguage['code']]) ? html::escapeHTML($aCurImageTitle[$aLanguage['code']]) : '')) ?></p>

					<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_alt_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_news_post_image_alt_text_%s'), $i) : printf(__('m_news_post_image_alt_text_%s_in_%s'), $i, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
					<?php echo form::text(array('p_images_alt_'.$i.'['.$aLanguage['code'].']','p_images_alt_'.$i.'_'.$aLanguage['code']), 40, 255, (isset($aCurImageAlt[$aLanguage['code']]) ? html::escapeHTML($aCurImageAlt[$aLanguage['code']]) : '')) ?></p>

					<?php endforeach; ?>

					<p><a href="<?php echo $aPostData['images'][$i]['img_url']?>" rel="post_images"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_news_post_image_title_attr_%s'),$aPostData['locales'][$okt->user->language]['title'], $i)) ?>"
					class="modal"><img src="<?php echo $sCurImageUrl ?>"
					<?php echo $sCurImageAttr ?> alt="" /></a></p>

					<?php if ($bCanEditPost) : ?>
					<p><a href="module.php?m=news&amp;action=edit&amp;post_id=<?php
					echo $aPostData['post']['id'] ?>&amp;delete_image=<?php echo $i ?>"
					onclick="return window.confirm('<?php echo html::escapeJS(_e('m_news_post_delete_image_confirm')) ?>')"
					class="icon delete"><?php _e('m_news_post_delete_image') ?></a></p>
					<?php endif; ?>

				<?php else : ?>

					<?php foreach ($okt->languages->list as $aLanguage) : ?>
					<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_news_post_image_title_%s'), $i) : printf(__('m_news_post_image_title_%s_in_%s'), $i,$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
					<?php echo form::text(array('p_images_title_'.$i.'['.$aLanguage['code'].']','p_images_title_'.$i.'_'.$aLanguage['code']), 40, 255, '') ?></p>

					<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_alt_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_news_post_image_alt_text_%s'), $i) : printf(__('m_news_post_image_alt_text_%s_in_%s'), $i,$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
					<?php echo form::text(array('p_images_alt_'.$i.'['.$aLanguage['code'].']','p_images_alt_'.$i.'_'.$aLanguage['code']), 40, 255, '') ?></p>
					<?php endforeach; ?>

				<?php endif; ?>

			</fieldset>
		</div>
	<?php endfor; ?>
	</div>
	<p class="note"><?php printf(__('c_c_maximum_file_size_%s'), util::l10nFileSize(OKT_MAX_UPLOAD_SIZE)) ?></p>

	<?php

	$aPostData['tabs'][20]['content'] = ob_get_clean();
}


# onglet fichiers
if ($okt->news->config->files['enable'])
{
	$aPostData['tabs'][30] = array(
		'id' => 'tab-files',
		'title' => __('m_news_post_tab_files'),
		'content' => ''
	);

	ob_start(); ?>

	<h3><?php _e('m_news_post_tab_title_files')?></h3>

	<div class="two-cols">
	<?php for ($i=1; $i<=$okt->news->config->files['number']; $i++) : ?>
		<div class="col">
			<p class="field"><label for="p_files_<?php echo $i ?>"><?php printf(__('m_news_post_file_%s'), $i)?> </label>
			<?php echo form::file('p_files_'.$i) ?></p>

			<?php # il y a un fichier ?
			if (!empty($aPostData['files'][$i])) :

				$aCurFileTitle = isset($aPageData['files'][$i]['title']) ? $aPageData['files'][$i]['title'] : array(); ?>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_files_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_news_post_file_title_%s'), $i) : printf(__('m_news_post_file_title_%s_in_%s'), $i, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_files_title_'.$i.'['.$aLanguage['code'].']','p_files_title_'.$i.'_'.$aLanguage['code']), 40, 255, (isset($aCurFileTitle[$aLanguage['code']]) ? html::escapeHTML($aCurFileTitle[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>

				<p><a href="<?php echo $aPostData['files'][$i]['url'] ?>"><img src="<?php echo OKT_PUBLIC_URL.'/img/media/'.$aPostData['files'][$i]['type'].'.png' ?>" alt="" /></a>
				<?php echo $aPostData['files'][$i]['type'] ?> (<?php echo $aPostData['files'][$i]['mime'] ?>)
				- <?php echo util::l10nFileSize($aPostData['files'][$i]['size']) ?></p>

				<?php if ($bCanEditPost) : ?>
				<p><a href="module.php?m=news&amp;action=edit&amp;post_id=<?php
				echo $aPostData['post']['id'] ?>&amp;delete_file=<?php echo $i ?>"
				onclick="return window.confirm('<?php echo html::escapeJS(_e('m_news_post_delete_file_confirm')) ?>')"
				class="icon delete"><?php _e('m_news_post_delete_file')?></a></p>
				<?php endif; ?>

			<?php else : ?>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>
				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_files_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_news_post_file_title_%s'), $i) : printf(__('m_news_post_file_title_%s_in_%s'), $i, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_files_title_'.$i.'['.$aLanguage['code'].']','p_files_title_'.$i.'_'.$aLanguage['code']), 40, 255, '') ?></p>
				<?php endforeach; ?>

			<?php endif; ?>
		</div>
	<?php endfor; ?>
	</div>

	<p class="note"><?php printf(__('c_c_maximum_file_size_%s'),util::l10nFileSize(OKT_MAX_UPLOAD_SIZE)) ?></p>

	<?php

	$aPostData['tabs'][30]['content'] = ob_get_clean();
}


# onglet options
$aPostData['tabs'][40] = array(
	'id' => 'tab-options',
	'title' => __('m_news_post_tab_options'),
	'content' => ''
);

ob_start(); ?>

	<h3><?php _e('m_news_post_tab_title_options')?></h3>

	<div class="two-cols">
		<p class="field col"><label for="p_date"><?php _e('m_news_post_date') ?></label>
		<?php echo form::text('p_date', 20, 255, (!empty($aPostData['extra']['date']) ? dt::dt2str('%d-%m-%Y', $aPostData['extra']['date']) : ''), 'datepicker') ?>
		<span class="note"><?php _e('m_news_post_date_note') ?></span></p>

		<div class="col">
			<p class="field floatLeftEspace"><label for="p_hours"><?php _e('m_news_post_hour') ?></label>
			<?php echo form::text('p_hours', 2, 2, (!empty($aPostData['extra']['hours']) ? $aPostData['extra']['hours'] : '')) ?></p>

			<p class="field floatLeftEspace"><label for="p_minutes"><?php _e('m_news_post_minute') ?></label>
			<?php echo form::text('p_minutes', 2, 2, (!empty($aPostData['extra']['minutes']) ? $aPostData['extra']['minutes'] : '')) ?></p>

			<div class="clearer"></div>
		</div>
	</div>

	<div class="two-cols">
		<?php if ($okt->news->config->categories['enable']) : ?>
		<p class="field col"><label for="p_category_id"><?php _e('m_news_post_category')?></label>
		<select id="p_category_id" name="p_category_id">
			<option value="0"><?php _e('m_news_post_category_first_level') ?></option>
			<?php
			while ($rsCategories->fetch())
			{
				echo '<option value="'.$rsCategories->id.'"'.
				($aPostData['post']['category_id'] == $rsCategories->id ? ' selected="selected"' : '').
				'>'.str_repeat('&nbsp;&nbsp;&nbsp;', $rsCategories->level).
				'&bull; '.html::escapeHTML($rsCategories->title).
				'</option>';
			}
			?>
		</select></p>
		<?php endif; ?>

		<?php # si les permissions de groupe sont activées
		if ($okt->news->canUsePerms()) : ?>
		<div class="col">
			<p><?php _e('m_news_post_permissions_group')?></p>

			<ul class="checklist">
				<?php foreach ($aGroups as $g_id=>$g_title) : ?>
				<li><label><?php echo form::checkbox(array('perms[]','perm_g_'.$g_id), $g_id, in_array($g_id, (array)$aPostData['perms'])) ?> <?php echo $g_title ?></label></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
	</div>

	<div class="two-cols">

		<?php if (!empty($aPostData['post']['id'])) : ?>

			<?php if ($aPostData['post']['active'] == 3) : ?>
				<?php if ($bCanPublish) : ?>
					<p class="field col"><label for="p_active"><?php _e('m_news_post_status') ?></label>
					<?php echo form::select('p_active', module_news::getPostsStatus(true), $aPostData['post']['active']) ?></p>
				<?php else : ?>
					<p class="field col"><span class="icon time"></span><?php _e('m_news_post_delayed_publication') ?></p>
				<?php endif; ?>

			<?php elseif ($aPostData['post']['active'] == 2) : ?>

				<?php if ($bCanPublish) : ?>
					<p class="field col"><a href="module.php?m=news&amp;action=edit&amp;post_id=<?php echo $aPostData['post']['id'] ?>&amp;publish=1"
					class="icon time"><?php _e('m_news_post_publish_post') ?></a></p>
				<?php else : ?>
					<p class="field col"><span class="icon time"></span> <?php _e('m_news_post_awaiting_validation') ?></p>
				<?php endif; ?>

			<?php else : ?>
				<?php if ($bCanPublish) : ?>
					<p class="field col"><label for="p_active"><?php _e('m_news_post_status') ?></label>
					<?php echo form::select('p_active', module_news::getPostsStatus(true), $aPostData['post']['active']) ?></p>
				<?php else : ?>
					<p class="field col"><label><?php echo form::checkbox('p_active', 1, $aPostData['post']['active']) ?> <?php _e('c_c_status_Online') ?></label></p>
				<?php endif; ?>
			<?php endif; ?>

		<?php elseif ($bCanPublish) : ?>
			<p class="field col"><label for="p_active"><?php _e('m_news_post_status') ?></label>
			<?php echo form::select('p_active', module_news::getPostsStatus(true), $aPostData['post']['active']) ?></p>

		<?php endif; ?>

		<?php if (!empty($okt->news->config->templates['item']['usables'])) : ?>
		<p class="field col"><label for="p_tpl"><?php _e('m_news_post_tpl') ?></label>
		<?php echo form::select('p_tpl', $aTplChoices, $aPostData['post']['tpl'])?></p>
		<?php endif; ?>

		<p class="field col"><label for="p_selected"><?php echo form::checkbox('p_selected', 1, $aPostData['post']['selected']) ?>
		<?php _e('m_news_post_selected') ?></label></p>
	</div>

<?php

$aPostData['tabs'][40]['content'] = ob_get_clean();


# onglet seo
if ($okt->news->config->enable_metas)
{
	$aPostData['tabs'][50] = array(
		'id' => 'tab-seo',
		'title' => __('m_news_post_tab_seo'),
		'content' => ''
	);

	ob_start(); ?>

		<h3><?php _e('c_c_seo_help') ?></h3>

		<?php foreach ($okt->languages->list as $aLanguage) : ?>

		<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_tag_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_title_tag') : printf(__('c_c_seo_title_tag_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_title_tag['.$aLanguage['code'].']','p_title_tag_'.$aLanguage['code']), 60, 255, html::escapeHTML($aPostData['locales'][$aLanguage['code']]['title_tag'])) ?></p>

		<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_desc') : printf(__('c_c_seo_meta_desc_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, html::escapeHTML($aPostData['locales'][$aLanguage['code']]['meta_description'])) ?></p>

		<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_seo_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_title_seo') : printf(__('c_c_seo_title_seo_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_title_seo['.$aLanguage['code'].']','p_title_seo_'.$aLanguage['code']), 60, 255, html::escapeHTML($aPostData['locales'][$aLanguage['code']]['title_seo'])) ?></p>

		<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_keywords') : printf(__('c_c_seo_meta_keywords_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
		<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 58, 5, html::escapeHTML($aPostData['locales'][$aLanguage['code']]['meta_keywords'])) ?></p>

		<div class="lockable" lang="<?php echo $aLanguage['code'] ?>">
			<p class="field"><label for="p_slug_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_url') : printf(__('c_c_seo_url_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_slug['.$aLanguage['code'].']','p_slug_'.$aLanguage['code']), 60, 255, html::escapeHTML($aPostData['locales'][$aLanguage['code']]['slug'])) ?>
			<span class="lockable-note"><?php _e('c_c_seo_warning_edit_url') ?></span></p>
		</div>

		<?php endforeach; ?>

	<?php

	$aPostData['tabs'][50]['content'] = ob_get_clean();
}


# -- TRIGGER MODULE NEWS : adminPostBuildTabs
$okt->news->triggers->callTrigger('adminPostBuildTabs', $okt, $aPostData);

$aPostData['tabs']->ksort();
?>

<form id="post-form" action="module.php" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<?php foreach ($aPostData['tabs'] as $aTabInfos) : ?>
			<li><a href="#<?php echo $aTabInfos['id'] ?>"><span><?php echo $aTabInfos['title'] ?></span></a></li>
			<?php endforeach; ?>
		</ul>

		<?php foreach ($aPostData['tabs'] as $sTabUrl=>$aTabInfos) : ?>
		<div id="<?php echo $aTabInfos['id'] ?>">
			<?php echo $aTabInfos['content'] ?>
		</div><!-- #<?php echo $aTabInfos['id'] ?> -->
		<?php endforeach; ?>
	</div><!-- #tabered -->

	<?php if ($bCanEditPost) : ?>
	<p><?php echo form::hidden('m','news'); ?>
	<?php echo form::hidden('action',!empty($aPostData['post']['id']) ? 'edit' : 'add'); ?>
	<?php echo !empty($aPostData['post']['id']) ? form::hidden('post_id',$aPostData['post']['id']) : ''; ?>
	<?php echo form::hidden('sended',1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php echo !empty($aPostData['post']['id']) ? _e('c_c_action_edit') : _e('c_c_action_add'); ?>" /></p>
	<?php endif; ?>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
