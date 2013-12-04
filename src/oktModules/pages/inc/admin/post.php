<?php
/**
 * @ingroup okt_module_pages
 * @brief Ajout ou modification d'une page
 *
 */

# Accès direct interdit
if (!defined('ON_PAGES_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
l10n::set(__DIR__.'/../../locales/'.$okt->user->language.'/admin.post');

# Données de la page
$aPageData = new ArrayObject();

$aPageData['post'] = array();
$aPageData['post']['id'] = null;

$aPageData['post']['category_id'] = 0;
$aPageData['post']['active'] = 1;
$aPageData['post']['tpl'] = '';
$aPageData['post']['created_at'] = '';
$aPageData['post']['updated_at'] = '';

$aPageData['locales'] = array();

foreach ($okt->languages->list as $aLanguage)
{
	$aPageData['locales'][$aLanguage['code']] = array();

	$aPageData['locales'][$aLanguage['code']]['title'] = '';
	$aPageData['locales'][$aLanguage['code']]['subtitle'] = '';
	$aPageData['locales'][$aLanguage['code']]['content'] = '';

	if ($okt->pages->config->enable_metas)
	{
		$aPageData['locales'][$aLanguage['code']]['title_seo'] = '';
		$aPageData['locales'][$aLanguage['code']]['title_tag'] = '';
		$aPageData['locales'][$aLanguage['code']]['slug'] = '';
		$aPageData['locales'][$aLanguage['code']]['meta_description'] = '';
		$aPageData['locales'][$aLanguage['code']]['meta_keywords'] = '';
	}
}

$aPageData['perms'] = array(0);
$aPageData['images'] = array();
$aPageData['files'] = array();

$rsPage = null;
$rsPageI18n = null;

# update page ?
if (!empty($_REQUEST['post_id']))
{
	$aPageData['post']['id'] = intval($_REQUEST['post_id']);

	$rsPage = $okt->pages->getPagesRecordset(array(
		'id' => $aPageData['post']['id'],
		'active' => 2
	));

	if ($rsPage->isEmpty())
	{
		$okt->error->set(sprintf(__('m_pages_page_%s_not_exists'), $aPageData['post']['id']));
		$aPageData['post']['id'] = null;
	}
	else
	{
		$aPageData['post']['category_id'] = $rsPage->category_id;
		$aPageData['post']['active'] = $rsPage->active;
		$aPageData['post']['tpl'] = $rsPage->tpl;
		$aPageData['post']['created_at'] = $rsPage->created_at;
		$aPageData['post']['updated_at'] = $rsPage->updated_at;

		$rsPageI18n = $okt->pages->getPageI18n($aPageData['post']['id']);

		foreach ($okt->languages->list as $aLanguage)
		{
			while ($rsPageI18n->fetch())
			{
				if ($rsPageI18n->language == $aLanguage['code'])
				{
					$aPageData['locales'][$aLanguage['code']]['title'] = $rsPageI18n->title;
					$aPageData['locales'][$aLanguage['code']]['subtitle'] = $rsPageI18n->subtitle;
					$aPageData['locales'][$aLanguage['code']]['content'] = $rsPageI18n->content;

					if ($okt->pages->config->enable_metas)
					{
						$aPageData['locales'][$aLanguage['code']]['title_seo'] = $rsPageI18n->title_seo;
						$aPageData['locales'][$aLanguage['code']]['title_tag'] = $rsPageI18n->title_tag;
						$aPageData['locales'][$aLanguage['code']]['slug'] = $rsPageI18n->slug;
						$aPageData['locales'][$aLanguage['code']]['meta_description'] = $rsPageI18n->meta_description;
						$aPageData['locales'][$aLanguage['code']]['meta_keywords'] = $rsPageI18n->meta_keywords;
					}
				}
			}
		}

		# Images
		if ($okt->pages->config->images['enable']) {
			$aPageData['images'] = $rsPage->getImagesInfo();
		}

		# Fichiers
		if ($okt->pages->config->files['enable']) {
			$aPageData['files'] = $rsPage->getFilesInfo();
		}

		# Permissions
		if ($okt->pages->canUsePerms()) {
			$aPageData['perms'] = $okt->pages->getPagePermissions($aPageData['post']['id']);
		}

		# URL
		$sPageUrl = pagesHelpers::getPageUrl($aPageData['locales'][$okt->user->language]['slug']);
	}
}


# -- TRIGGER MODULE PAGES : adminPostInit
$okt->pages->triggers->callTrigger('adminPostInit', $okt, $aPageData, $rsPage, $rsPageI18n);



/* Traitements
----------------------------------------------------------*/

# switch page status
if (!empty($_GET['switch_status']) && !empty($aPageData['post']['id']))
{
	try
	{
		$okt->pages->switchPageStatus($aPageData['post']['id']);

		# log admin
		$okt->logAdmin->info(array(
			'code' => 32,
			'component' => 'pages',
			'message' => 'page #'.$aPageData['post']['id']
		));

		http::redirect('module.php?m=pages&action=edit&post_id='.$aPageData['post']['id'].'&switched=1');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# suppression d'une image
if (!empty($_GET['delete_image']) && !empty($aPageData['post']['id']))
{
	$okt->pages->deleteImage($aPageData['post']['id'], $_GET['delete_image']);

	# log admin
	$okt->logAdmin->info(array(
		'code' => 41,
		'component' => 'pages',
		'message' => 'page #'.$aPageData['post']['id']
	));

	$okt->page->flashMessages->addSuccess(__('m_pages_page_updated'));

	http::redirect('module.php?m=pages&action=edit&post_id='.$aPageData['post']['id']);
}

# suppression d'un fichier
if (!empty($_GET['delete_file']) && !empty($aPageData['post']['id']))
{
	$okt->pages->deleteFile($aPageData['post']['id'],$_GET['delete_file']);

	# log admin
	$okt->logAdmin->info(array(
		'code' => 41,
		'component' => 'pages',
		'message' => 'page #'.$aPageData['post']['id']
	));

	$okt->page->flashMessages->addSuccess(__('m_pages_page_updated'));

	http::redirect('module.php?m=pages&action=edit&post_id='.$aPageData['post']['id']);
}

#  ajout / modifications d'une page
if (!empty($_POST['sended']))
{
	$aPageData['post']['category_id'] = !empty($_POST['p_category_id']) ? intval($_POST['p_category_id']) : 0;
	$aPageData['post']['active'] = !empty($_POST['p_active']) ? 1 : 0;
	$aPageData['post']['tpl'] = !empty($_POST['p_tpl']) ? $_POST['p_tpl'] : null;
	$aPageData['post']['created_at'] = $aPageData['post']['created_at'];
	$aPageData['post']['updated_at'] = $aPageData['post']['updated_at'];

	foreach ($okt->languages->list as $aLanguage)
	{
		$aPageData['locales'][$aLanguage['code']]['title'] = !empty($_POST['p_title'][$aLanguage['code']]) ? $_POST['p_title'][$aLanguage['code']] : '';
		$aPageData['locales'][$aLanguage['code']]['subtitle'] = !empty($_POST['p_subtitle'][$aLanguage['code']]) ? $_POST['p_subtitle'][$aLanguage['code']] : '';
		$aPageData['locales'][$aLanguage['code']]['content'] = !empty($_POST['p_content'][$aLanguage['code']]) ? $_POST['p_content'][$aLanguage['code']] : '';

		if ($okt->pages->config->enable_metas)
		{
			$aPageData['locales'][$aLanguage['code']]['title_seo'] = !empty($_POST['p_title_seo'][$aLanguage['code']]) ? $_POST['p_title_seo'][$aLanguage['code']] : '';
			$aPageData['locales'][$aLanguage['code']]['title_tag'] = !empty($_POST['p_title_tag'][$aLanguage['code']]) ? $_POST['p_title_tag'][$aLanguage['code']] : '';
			$aPageData['locales'][$aLanguage['code']]['slug'] = !empty($_POST['p_slug'][$aLanguage['code']]) ? $_POST['p_slug'][$aLanguage['code']] : '';
			$aPageData['locales'][$aLanguage['code']]['meta_description'] = !empty($_POST['p_meta_description'][$aLanguage['code']]) ? $_POST['p_meta_description'][$aLanguage['code']] : '';
			$aPageData['locales'][$aLanguage['code']]['meta_keywords'] = !empty($_POST['p_meta_keywords'][$aLanguage['code']]) ? $_POST['p_meta_keywords'][$aLanguage['code']] : '';
		}
	}

	$aPageData['perms'] = !empty($_POST['perms']) ? $_POST['perms'] : array();


	# -- TRIGGER MODULE PAGES : adminPopulateData
	$okt->pages->triggers->callTrigger('adminPopulateData', $okt, $aPageData);


	# vérification des données avant modification dans la BDD
	if ($okt->pages->checkPostData($aPageData))
	{
		$aPageData['cursor'] = $okt->pages->openPageCursor($aPageData['post']);

		# update page
		if (!empty($aPageData['post']['id']))
		{
			try
			{
				# -- TRIGGER MODULE PAGES : beforePageUpdate
				$okt->pages->triggers->callTrigger('beforePageUpdate', $okt, $aPageData);

				$okt->pages->updPage($aPageData['cursor'], $aPageData['locales'], $aPageData['perms']);

				# -- TRIGGER MODULE PAGES : afterPageUpdate
				$okt->pages->triggers->callTrigger('afterPageUpdate', $okt, $aPageData);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 41,
					'component' => 'pages',
					'message' => 'page #'.$aPageData['post']['id']
				));

				$okt->page->flashMessages->addSuccess(__('m_pages_page_updated'));

				http::redirect('module.php?m=pages&action=edit&post_id='.$aPageData['post']['id']);
			}
			catch (Exception $e) {
				$okt->error->set($e->getMessage());
			}
		}

		# add page
		else
		{
			try
			{
				# -- TRIGGER MODULE PAGES : beforePageCreate
				$okt->pages->triggers->callTrigger('beforePageCreate', $okt, $aPageData);

				$aPageData['post']['id'] = $okt->pages->addPage($aPageData['cursor'], $aPageData['locales'], $aPageData['perms']);

				# -- TRIGGER MODULE PAGES : afterPageCreate
				$okt->pages->triggers->callTrigger('afterPageCreate', $okt, $aPageData);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 40,
					'component' => 'pages',
					'message' => 'page #'.$aPageData['post']['id']
				));

				$okt->page->flashMessages->addSuccess(__('m_pages_page_added'));

				http::redirect('module.php?m=pages&action=edit&post_id='.$aPageData['post']['id']);
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
if ($okt->pages->config->categories['enable'])
{
	$rsCategories = $okt->pages->categories->getCategories(array(
		'active' => 2,
		'language' => $okt->user->language
	));
}

# Liste des templates utilisables
$oTemplatesItem = new oktTemplatesSet($okt, $okt->pages->config->templates['item'], 'pages/item', 'item');
$aTplChoices = array_merge(
	array('&nbsp;' => null),
	$oTemplatesItem->getUsablesTemplatesForSelect($okt->pages->config->templates['item']['usables'])
);

# Récupération de la liste des groupes si les permissions sont activées
if ($okt->pages->canUsePerms()) {
	$aGroups = $okt->pages->getUsersGroupsForPerms(false,true);
}

# ajout bouton retour
$okt->page->addButton('pagesBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_Go_back'),
	'url' 			=> 'module.php?m=pages&amp;action=index',
	'ui-icon' 		=> 'arrowreturnthick-1-w',
),'before');

# boutons update page
if (!empty($aPageData['post']['id']))
{
	$okt->page->addGlobalTitle(__('m_pages_page_edit_a_page'));

	# bouton switch statut
	$okt->page->addButton('pagesBtSt',array(
		'permission' 	=> true,
		'title' 		=> ($aPageData['post']['active'] ? __('c_c_status_Online') : __('c_c_status_Offline')),
		'url' 			=> 'module.php?m=pages&amp;action=edit&amp;post_id='.$aPageData['post']['id'].'&amp;switch_status=1',
		'ui-icon' 		=> ($aPageData['post']['active'] ? 'volume-on' : 'volume-off'),
		'active' 		=> $aPageData['post']['active'],
	));
	# bouton de suppression si autorisé
	$okt->page->addButton('pagesBtSt',array(
		'permission' 	=> $okt->checkPerm('pages_remove'),
		'title' 		=> __('c_c_action_Delete'),
		'url' 			=> 'module.php?m=pages&amp;action=delete&amp;post_id='.$aPageData['post']['id'],
		'ui-icon' 		=> 'closethick',
		'onclick' 		=> 'return window.confirm(\''.html::escapeJS(__('m_pages_page_delete_confirm')).'\')',
	));
	# bouton vers la page côté public si publié
	$okt->page->addButton('pagesBtSt',array(
		'permission' 	=> ($aPageData['post']['active'] ? true : false),
		'title' 		=> __('c_c_action_Show'),
		'url' 			=> $sPageUrl,
		'ui-icon' 		=> 'extlink'
	));
}

# boutons add page
else {
	$okt->page->addGlobalTitle(__('m_pages_page_add_a_page'));
}

# Lockable
$okt->page->lockable();

# Tabs
$okt->page->tabs();

# Modal
$okt->page->applyLbl($okt->pages->config->lightbox_type);

# RTE
$okt->page->applyRte($okt->pages->config->enable_rte,'textarea.richTextEditor');

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Validation javascript
$okt->page->validate('page-form',array(
	array(
		'id' => 'p_title',
		'rules' => array(
			'required: true',
			'minlength: 3'
		)
	),
	array(
		'id' => 'p_content',
		'rules' => array(
			'required: true',
		)
	)
));

# Permission checkboxes
$okt->page->updatePermissionsCheckboxes('perm_g_');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('pagesBtSt'); ?>

<?php if (!empty($aPageData['post']['id'])) : ?>
<p><?php printf(__('m_pages_page_added_on'), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aPageData['post']['created_at']).'</em>') ?>

<?php if ($aPageData['post']['updated_at'] > $aPageData['post']['created_at']) : ?>
<span class="note"><?php printf(__('m_pages_page_last_edit'), '<em>'.dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$aPageData['post']['updated_at']).'</em>') ?></span>
<?php endif; ?>
</p>
<?php endif; ?>


<?php # Construction des onglets
$aPageData['tabs'] = new ArrayObject;

# onglet contenu
$aPageData['tabs'][10] = array(
	'id' => 'tab-content',
	'title' => __('m_pages_page_tab_content'),
	'content' => ''
);

ob_start(); ?>

	<h3><?php _e('m_pages_page_tab_title_content') ?></h3>

	<?php foreach ($okt->languages->list as $aLanguage) : ?>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_pages_page_title') : printf(__('m_pages_page_title_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 100, 255, html::escapeHTML($aPageData['locales'][$aLanguage['code']]['title'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_subtitle_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_pages_page_subtitle') : printf(__('m_pages_page_subtitle_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_subtitle['.$aLanguage['code'].']','p_subtitle_'.$aLanguage['code']), 100, 255, html::escapeHTML($aPageData['locales'][$aLanguage['code']]['subtitle'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_content_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_pages_page_content') : printf(__('m_pages_page_content_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::textarea(array('p_content['.$aLanguage['code'].']','p_content_'.$aLanguage['code']), 97, 15, $aPageData['locales'][$aLanguage['code']]['content'],'richTextEditor') ?></p>

	<?php endforeach; ?>

<?php

$aPageData['tabs'][10]['content'] = ob_get_clean();


# onglet images
if ($okt->pages->config->images['enable'])
{
	$aPageData['tabs'][20] = array(
		'id' => 'tab-images',
		'title' => __('m_pages_page_tab_images'),
		'content' => ''
	);

	ob_start(); ?>

	<h3><?php _e('m_pages_page_tab_title_images')?></h3>
	<div class="two-cols modal-box">
	<?php for ($i=1; $i<=$okt->pages->config->images['number']; $i++) : ?>
		<div class="col">
			<fieldset>
				<legend><?php printf(__('m_pages_page_image_%s'), $i) ?></legend>

				<p class="field"><label for="p_images_<?php echo $i ?>"><?php printf(__('m_pages_page_image_%s'), $i) ?></label>
				<?php echo form::file('p_images_'.$i) ?></p>

				<?php # il y a une image ?
				if (!empty($aPageData['images'][$i])) :

					# affichage square ou icon ?
					if (isset($aPageData['images'][$i]['min_url'])) {
						$sCurImageUrl = $aPageData['images'][$i]['min_url'];
						$sCurImageAttr = $aPageData['images'][$i]['min_attr'];
					}
					elseif (isset($aPageData['images'][$i]['square_url'])) {
						$sCurImageUrl = $aPageData['images'][$i]['square_url'];
						$sCurImageAttr = $aPageData['images'][$i]['square_attr'];
					}
					else {
						$sCurImageUrl = OKT_PUBLIC_URL.'/img/media/image.png';
						$sCurImageAttr = ' width="48" height="48" ';
					}

					$aCurImageAlt = isset($aPageData['images'][$i]['alt']) ? $aPageData['images'][$i]['alt'] : array();
					$aCurImageTitle = isset($aPageData['images'][$i]['title']) ? $aPageData['images'][$i]['title'] : array();

					?>

					<?php foreach ($okt->languages->list as $aLanguage) : ?>

					<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_pages_page_image_title_%s'), $i) : printf(__('m_pages_page_image_title_%s_in_%s'), $i, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
					<?php echo form::text(array('p_images_title_'.$i.'['.$aLanguage['code'].']','p_images_title_'.$i.'_'.$aLanguage['code']), 40, 255, (isset($aCurImageTitle[$aLanguage['code']]) ? html::escapeHTML($aCurImageTitle[$aLanguage['code']]) : '')) ?></p>

					<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_alt_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_pages_page_image_alt_text_%s'), $i) : printf(__('m_pages_page_image_alt_text_%s_in_%s'), $i, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
					<?php echo form::text(array('p_images_alt_'.$i.'['.$aLanguage['code'].']','p_images_alt_'.$i.'_'.$aLanguage['code']), 40, 255, (isset($aCurImageAlt[$aLanguage['code']]) ? html::escapeHTML($aCurImageAlt[$aLanguage['code']]) : '')) ?></p>

					<?php endforeach; ?>

					<p><a href="<?php echo $aPageData['images'][$i]['img_url']?>" rel="pages_images"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_pages_page_image_title_attr_%s'),$aPageData['locales'][$okt->user->language]['title'], $i)) ?>"
					class="modal"><img src="<?php echo $sCurImageUrl ?>"
					<?php echo $sCurImageAttr ?> alt="" /></a></p>

					<p><a href="module.php?m=pages&amp;action=edit&amp;post_id=<?php
					echo $aPageData['post']['id'] ?>&amp;delete_image=<?php echo $i ?>"
					onclick="return window.confirm('<?php echo html::escapeJS(_e('m_pages_page_delete_image_confirm')) ?>')"
					class="icon delete"><?php _e('m_pages_page_delete_image') ?></a></p>

				<?php else : ?>

					<?php foreach ($okt->languages->list as $aLanguage) : ?>
					<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_pages_page_image_title_%s'), $i) : printf(__('m_pages_page_image_title_%s_in_%s'), $i,$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
					<?php echo form::text(array('p_images_title_'.$i.'['.$aLanguage['code'].']','p_images_title_'.$i.'_'.$aLanguage['code']), 40, 255, '') ?></p>

					<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_images_alt_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_pages_page_image_alt_text_%s'), $i) : printf(__('m_pages_page_image_alt_text_%s_in_%s'), $i,$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
					<?php echo form::text(array('p_images_alt_'.$i.'['.$aLanguage['code'].']','p_images_alt_'.$i.'_'.$aLanguage['code']), 40, 255, '') ?></p>
					<?php endforeach; ?>

				<?php endif; ?>

			</fieldset>
		</div>
	<?php endfor; ?>
	</div>
	<p class="note"><?php printf(__('c_c_maximum_file_size_%s'), util::l10nFileSize(OKT_MAX_UPLOAD_SIZE)) ?></p>

	<?php

	$aPageData['tabs'][20]['content'] = ob_get_clean();
}


# onglet fichiers
if ($okt->pages->config->files['enable'])
{
	$aPageData['tabs'][30] = array(
		'id' => 'tab-files',
		'title' => __('m_pages_page_tab_files'),
		'content' => ''
	);

	ob_start(); ?>

	<h3><?php _e('m_pages_page_tab_title_files')?></h3>

	<div class="two-cols">
	<?php for ($i=1; $i<=$okt->pages->config->files['number']; $i++) : ?>
		<div class="col">
			<p class="field"><label for="p_files_<?php echo $i ?>"><?php printf(__('m_pages_page_file_%s'), $i)?> </label>
			<?php echo form::file('p_files_'.$i) ?></p>

			<?php # il y a un fichier ?
			if (!empty($aPageData['files'][$i])) :

				$aCurFileTitle = isset($aPageData['files'][$i]['title']) ? $aPageData['files'][$i]['title'] : array(); ?>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_files_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_pages_page_file_title_%s'), $i) : printf(__('m_pages_page_file_title_%s_in_%s'), $i, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_files_title_'.$i.'['.$aLanguage['code'].']','p_files_title_'.$i.'_'.$aLanguage['code']), 40, 255, (isset($aCurFileTitle[$aLanguage['code']]) ? html::escapeHTML($aCurFileTitle[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>

				<p><a href="<?php echo $aPageData['files'][$i]['url'] ?>"><img src="<?php echo OKT_PUBLIC_URL.'/img/media/'.$aPageData['files'][$i]['type'].'.png' ?>" alt="" /></a>
				<?php echo $aPageData['files'][$i]['type'] ?> (<?php echo $aPageData['files'][$i]['mime'] ?>)
				- <?php echo util::l10nFileSize($aPageData['files'][$i]['size']) ?></p>

				<p><a href="module.php?m=pages&amp;action=edit&amp;post_id=<?php
				echo $aPageData['post']['id'] ?>&amp;delete_file=<?php echo $i ?>"
				onclick="return window.confirm('<?php echo html::escapeJS(_e('m_pages_page_delete_file_confirm')) ?>')"
				class="icon delete"><?php _e('m_pages_page_delete_file')?></a></p>

			<?php else : ?>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>
				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_files_title_<?php echo $i ?>_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? printf(__('m_pages_page_file_title_%s'), $i) : printf(__('m_pages_page_file_title_%s_in_%s'), $i, $aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_files_title_'.$i.'['.$aLanguage['code'].']','p_files_title_'.$i.'_'.$aLanguage['code']), 40, 255, '') ?></p>
				<?php endforeach; ?>

			<?php endif; ?>
		</div>
	<?php endfor; ?>
	</div>

	<p class="note"><?php printf(__('c_c_maximum_file_size_%s'),util::l10nFileSize(OKT_MAX_UPLOAD_SIZE)) ?></p>

	<?php

	$aPageData['tabs'][30]['content'] = ob_get_clean();
}


# onglet options
$aPageData['tabs'][40] = array(
	'id' => 'tab-options',
	'title' => __('m_pages_page_tab_options'),
	'content' => ''
);

ob_start(); ?>

	<h3><?php _e('m_pages_page_tab_title_options')?></h3>

	<div class="two-cols">
		<?php if ($okt->pages->config->categories['enable']) : ?>
		<p class="field col"><label for="p_category_id"><?php _e('m_pages_page_category')?></label>
		<select id="p_category_id" name="p_category_id">
			<option value="0"><?php _e('m_pages_page_category_first_level') ?></option>
			<?php
			while ($rsCategories->fetch())
			{
				echo '<option value="'.$rsCategories->id.'"'.
				($aPageData['post']['category_id'] == $rsCategories->id ? ' selected="selected"' : '').
				'>'.str_repeat('&nbsp;&nbsp;&nbsp;', $rsCategories->level).
				'&bull; '.html::escapeHTML($rsCategories->title).
				'</option>';
			}
			?>
		</select></p>
		<?php endif; ?>

		<p class="field col"><label><?php echo form::checkbox('p_active', 1, $aPageData['post']['active']) ?> <?php _e('c_c_status_Online') ?></label></p>

		<?php if (!empty($okt->pages->config->templates['item']['usables'])) : ?>
		<p class="field col"><label for="p_tpl"><?php _e('m_pages_page_tpl') ?></label>
		<?php echo form::select('p_tpl', $aTplChoices, $aPageData['post']['tpl'])?></p>
		<?php endif; ?>

		<?php # si les permissions de groupe sont activées
		if ($okt->pages->canUsePerms()) : ?>
		<div class="col">
			<p><?php _e('m_pages_page_permissions_group')?></p>

			<ul class="checklist">
				<?php foreach ($aGroups as $g_id=>$g_title) : ?>
				<li><label><?php echo form::checkbox(array('perms[]','perm_g_'.$g_id), $g_id, in_array($g_id, (array)$aPageData['perms'])) ?> <?php echo $g_title ?></label></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>
	</div>

<?php

$aPageData['tabs'][40]['content'] = ob_get_clean();


# onglet seo
if ($okt->pages->config->enable_metas)
{
	$aPageData['tabs'][50] = array(
		'id' => 'tab-seo',
		'title' => __('m_pages_page_tab_seo'),
		'content' => ''
	);

	ob_start(); ?>

	<h3><?php _e('c_c_seo_help') ?></h3>

	<?php foreach ($okt->languages->list as $aLanguage) : ?>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_tag_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_title_tag') : printf(__('c_c_seo_title_tag_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_title_tag['.$aLanguage['code'].']','p_title_tag_'.$aLanguage['code']), 60, 255, html::escapeHTML($aPageData['locales'][$aLanguage['code']]['title_tag'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_desc') : printf(__('c_c_seo_meta_desc_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, html::escapeHTML($aPageData['locales'][$aLanguage['code']]['meta_description'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_seo_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_title_seo') : printf(__('c_c_seo_title_seo_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::text(array('p_title_seo['.$aLanguage['code'].']','p_title_seo_'.$aLanguage['code']), 60, 255, html::escapeHTML($aPageData['locales'][$aLanguage['code']]['title_seo'])) ?></p>

	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_keywords') : printf(__('c_c_seo_meta_keywords_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
	<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 58, 5, html::escapeHTML($aPageData['locales'][$aLanguage['code']]['meta_keywords'])) ?></p>

	<div class="lockable" lang="<?php echo $aLanguage['code'] ?>">
		<p class="field"><label for="p_slug_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_url') : printf(__('c_c_seo_url_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_slug['.$aLanguage['code'].']','p_slug_'.$aLanguage['code']), 60, 255, html::escapeHTML($aPageData['locales'][$aLanguage['code']]['slug'])) ?>
		<span class="lockable-note"><?php _e('c_c_seo_warning_edit_url') ?></span></p>
	</div>

	<?php endforeach; ?>

	<?php

	$aPageData['tabs'][50]['content'] = ob_get_clean();
}


# -- TRIGGER MODULE PAGES : adminPostBuildTabs
$okt->pages->triggers->callTrigger('adminPostBuildTabs', $okt, $aPageData);

$aPageData['tabs']->ksort();
?>

<form id="page-form" action="module.php" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<?php foreach ($aPageData['tabs'] as $aTabInfos) : ?>
			<li><a href="#<?php echo $aTabInfos['id'] ?>"><span><?php echo $aTabInfos['title'] ?></span></a></li>
			<?php endforeach; ?>
		</ul>

		<?php foreach ($aPageData['tabs'] as $sTabUrl=>$aTabInfos) : ?>
		<div id="<?php echo $aTabInfos['id'] ?>">
			<?php echo $aTabInfos['content'] ?>
		</div><!-- #<?php echo $aTabInfos['id'] ?> -->
		<?php endforeach; ?>
	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','pages'); ?>
	<?php echo form::hidden('action',!empty($aPageData['post']['id']) ? 'edit' : 'add'); ?>
	<?php echo !empty($aPageData['post']['id']) ? form::hidden('post_id',$aPageData['post']['id']) : ''; ?>
	<?php echo form::hidden('sended',1); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php echo !empty($aPageData['post']['id']) ? _e('c_c_action_edit') : _e('c_c_action_add'); ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
