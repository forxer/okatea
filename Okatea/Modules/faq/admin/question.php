<?php
/**
 * @ingroup okt_module_faq
 * @brief Ajout ou modification d'une question internationalisée
 *
 */

use Okatea\Admin\Page;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$questions_id = null;

$p_title = array();
$p_content = array();
$p_files = array();

if ($okt->faq->config->enable_metas)
{
	$p_title_seo = array();
	$p_title_tag = array();
	$p_meta_description = array();
	$p_meta_keywords = array();
	$p_slug = array();
}

foreach ($okt->languages->list as $aLanguage)
{
	$p_title[$aLanguage['code']] = '';
	$p_content[$aLanguage['code']] = '';

	if ($okt->faq->config->enable_metas)
	{
		$p_title_seo[$aLanguage['code']] = '';
		$p_title_tag[$aLanguage['code']] = '';
		$p_meta_description[$aLanguage['code']] = '';
		$p_meta_keywords[$aLanguage['code']] = '';
		$p_slug[$aLanguage['code']] = '';
	}

	$p_files[$aLanguage['code']] = array();
	for ($i=0; $i<$okt->faq->config->files['number']; $i++) {
		$p_files[$aLanguage['code']][$i] = '';
	}
}

$p_cat_id = null;
$p_active = 1;

# update questions ?
if (!empty($_REQUEST['questions_id']))
{
	$questions_id = intval($_REQUEST['questions_id']);

	$question_infos = $okt->faq->getQuestions(array('id'=>$questions_id,'active'=>2));
	$question_i18n = $okt->faq->getQuestionI18n($questions_id);

	foreach ($okt->languages->list as $aLanguage)
	{
		$p_title[$aLanguage['code']] = '';
		$p_content[$aLanguage['code']] = '';

		if ($okt->faq->config->enable_metas)
		{
			$p_title_seo[$aLanguage['code']] = '';
			$p_title_tag[$aLanguage['code']] = '';
			$p_meta_description[$aLanguage['code']] = '';
			$p_meta_keywords[$aLanguage['code']] = '';
			$p_slug[$aLanguage['code']] = '';
		}

		while ($question_i18n->fetch())
		{
			if ($question_i18n->language == $aLanguage['code'])
			{
				$p_title[$question_i18n->language] = $question_i18n->title;
				$p_content[$question_i18n->language] = $question_i18n->content;

				if ($okt->faq->config->enable_metas)
				{
					$p_title_seo[$aLanguage['code']] = $question_i18n->title_seo;
					$p_title_tag[$aLanguage['code']] = $question_i18n->title_tag;
					$p_meta_description[$question_i18n->language] = $question_i18n->meta_description;
					$p_meta_keywords[$question_i18n->language] = $question_i18n->meta_keywords;
					$p_slug[$question_i18n->language] = $question_i18n->slug;
				}
			}
		}
	}

	$p_active = $question_infos->active;
	$p_cat_id = $question_infos->cat_id;

	if ($okt->faq->config->images['enable']) {
		$post_images = $question_infos->getImagesInfo();
	}

	$files_infos = $question_infos->getFilesInfo();
}


/* Traitements
----------------------------------------------------------*/

# Switch question statut
if (!empty($_GET['switch_status']) && !empty($questions_id))
{
	$okt->faq->setQuestionStatus($questions_id);
	http::redirect('module.php?m=faq&action=edit&questions_id='.$questions_id.'&switched=1');
}

# Suppression d'une image
if (!empty($_GET['delete_image']) && !empty($questions_id))
{
	$okt->faq->deleteImage($questions_id,$_GET['delete_image']);
	http::redirect('module.php?m=faq&action=edit&questions_id='.$questions_id.'&edited=1');
}

# Suppression d'un fichier
if (!empty($_GET['delfile']) && !empty($questions_id))
{
	$okt->faq->delFile($questions_id,$_GET['delfile']);
	http::redirect('module.php?m=faq&action=edit&questions_id='.$questions_id.'&edited=1');
}

# Formulaire envoyé
if (!empty($_POST['sended']))
{
	$p_active = !empty($_POST['p_active']) ? 1 : 0;
	$p_cat_id = !empty($_POST['p_cat_id']) ? $_POST['p_cat_id'] : null;
	$p_title = !empty($_POST['p_title']) ? array_map('trim',$_POST['p_title']) : array();
	$p_content = !empty($_POST['p_content']) ? array_map('trim',$_POST['p_content']) : array();

	if ($okt->faq->config->enable_metas)
	{
		$p_title_seo = !empty($_POST['p_title_seo']) ? array_map('trim',$_POST['p_title_seo']) : array();
		$p_title_tag = !empty($_POST['p_title_tag']) ? array_map('trim',$_POST['p_title_tag']) : array();
		$p_meta_description = !empty($_POST['p_meta_description']) ? array_map('trim',$_POST['p_meta_description']) : array();
		$p_meta_keywords = !empty($_POST['p_meta_keywords']) ? array_map('trim',$_POST['p_meta_keywords']) : array();
		$p_slug = !empty($_POST['p_slug']) ? array_map('trim',$_POST['p_slug']) : array();
	}

	$params = array(
		'active' => $p_active,
		'cat_id' => $p_cat_id,
		'title' => $p_title,
		'content' => $p_content
	);

	if ($okt->faq->config->enable_metas)
	{
		$params['title_seo'] = $p_title_seo;
		$params['title_tag'] = $p_title_tag;
		$params['meta_description'] = $p_meta_description;
		$params['meta_keywords'] = $p_meta_keywords;
		$params['slugs'] = $p_slug;
	}

	# update questions
	if (!empty($questions_id))
	{
		$params['id'] = $questions_id;

		$okt->faq->updQuestion($params);
		http::redirect('module.php?m=faq&action=edit&questions_id='.$questions_id.'&updated=1');
	}
	# add question
	else
	{
		$questions_id = $okt->faq->addQuestion($params);
		http::redirect('module.php?m=faq&action=edit&questions_id='.$questions_id.'&added=1');
	}

}


/* Affichage
----------------------------------------------------------*/

# bouton retour
$okt->page->addButton('faqBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_Go_back'),
	'url' 			=> 'module.php?m=faq&amp;action=index',
	'ui-icon' 		=> 'arrowreturnthick-1-w',
),'before');

# add questions
if (empty($questions_id)) {
	$okt->page->addGlobalTitle(__('m_faq_add_question'));
}
else {
	$okt->page->addGlobalTitle(__('m_faq_edit_question'));

	# Buttons
	$okt->page->addButton('faqBtSt',array(
		'permission' 	=> true,
		'title' 		=> ($p_active ? __('c_c_status_Online') : __('c_c_status_Offline')),
		'url' 			=> 'module.php?m=faq&amp;action=edit&amp;questions_id='.$questions_id.'&amp;switch_status=1',
		'ui-icon' 		=> ($p_active ? 'volume-on' : 'volume-off'),
		'active' 		=> $p_active,
	));
	$okt->page->addButton('faqBtSt',array(
		'permission' 	=> $okt->checkPerm('faq_remove'),
		'title' 		=> __('c_c_action_delete'),
		'url' 			=> 'module.php?m=faq&amp;action=delete&amp;questions_id='.$questions_id,
		'ui-icon' 		=> 'closethick',
		'onclick' 		=> 'return window.confirm(\''.html::escapeJS(__('m_faq_delete_confirm')).'\')',
	));
}


# Lockable
$okt->page->lockable();

# Tabs
$okt->page->tabs();

# Modal
$okt->page->applyLbl($okt->faq->config->lightbox_type);

# RTE
$okt->page->applyRte($okt->faq->config->enable_rte,'textarea.richTextEditor');

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Liste des catégories
if ($okt->faq->config->enable_categories)
{
	$rsCategories = $okt->faq->getCategories(array('active'=>1,'language'=>$okt->user->language));

	$aCategoriesChoice = array('&nbsp;'=>null);
	while ($rsCategories->fetch()) {
		$aCategoriesChoice[$rsCategories->title] = $rsCategories->id;
	}

	unset($rsCategories);
}


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('faqBtSt'); ?>


<form action="module.php" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<li><a href="#tab_question"><span><?php _e('m_faq_question') ?></span></a></li>
			<li><a href="#tab_options"><span><?php _e('m_faq_options') ?></span></a></li>
			<?php if ($okt->faq->config->images['enable']) : ?>
			<li><a href="#tab_images"><span><?php _e('Image')?></span></a></li>
			<?php endif; ?>
			<?php if ($okt->faq->config->files['enable']) : ?>
			<li><a href="#tab_files"><span><?php _e('m_faq_attached_files') ?></span></a></li>
			<?php endif; ?>
			<?php if ($okt->faq->config->enable_metas) : ?>
			<li><a href="#tab_seo"><span><?php _e('m_faq_seo') ?></span></a></li>
			<?php endif; ?>
		</ul>

		<div id="tab_question">
			<h3><?php _e('m_faq_question') ?></h3>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php _e('m_faq_title')?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, html::escapeHTML($p_title[$aLanguage['code']])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_content_<?php echo $aLanguage['code'] ?>"><?php _e('m_faq_content')?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::textarea(array('p_content['.$aLanguage['code'].']','p_content_'.$aLanguage['code']), 57, 10, $p_content[$aLanguage['code']],'richTextEditor') ?></p>

			<?php endforeach; ?>

		</div><!-- #tab_question -->

		<div id="tab_options">
			<div class="two-cols">
				<p class="field col"><label><?php echo form::checkbox('p_active', 1, $p_active) ?> <?php _e('c_c_status_online') ?></label></p>

				<?php if ($okt->faq->config->enable_categories) : ?>
				<p class="field col"><label for="p_cat_id"><?php _e('m_faq_section') ?></label>
				<?php echo form::select('p_cat_id',$aCategoriesChoice,$p_cat_id) ?></p>
				<?php endif; ?>

			</div>
		</div><!-- #tab_options -->

		<?php if ($okt->faq->config->images['enable']) : ?>
		<div id="tab_images">
			<h3><?php  _e('m_faq_images_question')?></h3>

			<div class="two-cols modal-box">
			<?php for ($i=1; $i<=$okt->faq->config->images['number']; $i++) : ?>
				<div class="col">

					<p class="field"><label for="p_images_<?php echo $i ?>"><?php printf(__('m_faq_images_%s'),$i) ?></label>
					<?php echo form::file('p_images_'.$i) ?></p>

					<?php # il y a une image ?
					if (!empty($post_images[$i])) :

						# affichage square ou icon ?
						if (isset($post_images[$i]['square_url'])) {
							$cur_image_url = $post_images[$i]['square_url'];
							$cur_image_attr = $post_images[$i]['square_attr'];
						}
						else {
							$cur_image_url = $okt->options->public_url.'/img/media/image.png';
							$cur_image_attr = ' width="48" height="48" ';
						}

						$cur_image_alt = isset($post_images[$i]['alt']) ? $post_images[$i]['alt'] : '';

						?>

						<p class="field"><label for="p_images_alt_<?php echo $i ?>"><?php printf(__('m_faq_alternative_text_image_%s'), $i) ?></label>
						<?php echo form::text('p_images_alt_'.$i,40,255,html::escapeHTML($cur_image_alt)) ?></p>

						<p><a href="<?php echo $post_images[$i]['img_url']?>"
						rel="questions_images" class="modal"><img src="<?php echo $cur_image_url ?>"
						<?php echo $cur_image_attr ?> alt="" /></a></p>

						<p><a href="module.php?m=faq&amp;action=edit&amp;questions_id=<?php
						echo $questions_id ?>&amp;delete_image=<?php echo $i ?>"
						class="icon delete"><?php _e('m_faq_delete_image')?></a></p>

					<?php else : ?>

						<p class="field"><label for="p_images_alt_<?php echo $i ?>"><?php printf(__('m_faq_alternative_text_image_%s'), $i) ?></label>
						<?php echo form::text('p_images_alt_'.$i,40,255,'') ?></p>

					<?php endif; ?>
				</div>
			<?php endfor; ?>
			</div>
		</div><!-- #tab_images -->
		<?php endif; ?>

		<?php if ($okt->faq->config->files['enable']) : ?>
		<div id="tab_files">
			<h3><?php _e('m_faq_attached_files')?> <span class="lang-switcher-buttons"></span></h3>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>
			<div class="two-cols" lang="<?php echo $aLanguage['code'] ?>">
				<?php for ($i=0;$i<$okt->faq->config->files['number'];$i++) : ?>
				<div class="col">

				<p class="field"><label for="p_files_<?php echo $aLanguage['code'] ?>_<?php echo $i ?>">Fichier <?php echo $i+1 ?></label>
				<?php echo form::file('p_files_'.$aLanguage['code'].'_'.$i, html::escapeHTML($p_files[$aLanguage['code']][$i])) ?></p>

				<?php if (!empty($files_infos[$aLanguage['code']][$i])) : ?>
				<p><img src="<?php
				echo $okt->config->app_path.'oktCommon/img/media/'.$files_infos[$aLanguage['code']][$i]['type'].'.png'
				?>" alt="<?php echo $files_infos[$aLanguage['code']][$i]['type'] ?>" />
				<?php echo $files_infos[$aLanguage['code']][$i]['filename'] ?>
				- <?php echo $files_infos[$aLanguage['code']][$i]['type'] ?> (<?php echo $files_infos[$aLanguage['code']][$i]['mime'] ?>)
				- <?php echo Utilities::l10nFileSize($files_infos[$aLanguage['code']][$i]['size']) ?>
				- <a href="module.php?m=faq&amp;action=edit&amp;questions_id=<?php echo $questions_id ?>&amp;delfile=<?php echo $files_infos[$aLanguage['code']][$i]['filename'] ?>"><?php _e('c_c_action_delete')?></a>
				</p>
				<?php endif; ?>
				</div>
				<?php endfor; ?>
			</div>
			<?php endforeach; ?>
		</div><!-- #tab_files -->
		<?php endif; ?>

		<?php if ($okt->faq->config->enable_metas) : ?>
		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_tag_<?php echo $aLanguage['code'] ?>"><?php _e('m_faq_title_tag')?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title_tag['.$aLanguage['code'].']','p_title_tag_'.$aLanguage['code']), 60, 255, html::escapeHTML($p_title_tag[$aLanguage['code']])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_seo_meta_desc')?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, html::escapeHTML($p_meta_description[$aLanguage['code']])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_seo_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_seo_title_seo')?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title_seo['.$aLanguage['code'].']','p_title_seo_'.$aLanguage['code']), 60, 255, html::escapeHTML($p_title_seo[$aLanguage['code']])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_seo_meta_keywords')?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 58, 5, html::escapeHTML($p_meta_keywords[$aLanguage['code']])) ?></p>

			<div class="lockable">
				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_slug_<?php echo $aLanguage['code'] ?>">URL <span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_slug['.$aLanguage['code'].']','p_slug_'.$aLanguage['code']), 60, 255, html::escapeHTML($p_slug[$aLanguage['code']])) ?>
				<span class="lockable-note"><?php _e('c_c_seo_warning_edit_url') ?></span></p>
			</div>

			<?php endforeach; ?>
		</div><!-- #tab_seo -->
		<?php endif; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','faq'); ?>
	<?php echo form::hidden('action',!empty($questions_id) ? 'edit' : 'add'); ?>
	<?php echo !empty($questions_id) ? form::hidden('questions_id',$questions_id) : ''; ?>
	<?php echo form::hidden('sended',1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php echo !empty($questions_id) ? _e('c_c_action_edit') : _e('c_c_action_add'); ?>" /></p>
</form>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
