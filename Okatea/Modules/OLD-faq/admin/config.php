<?php
/**
 * @ingroup okt_module_faq
 * @brief La page de configuration du module
 *
 */
use Okatea\Admin\Page;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Images\ImageUploadConfig;

# Accès direct interdit
if (! defined('ON_MODULE'))
	die();
	
	/* Initialisations
----------------------------------------------------------*/

$oImageUploadConfig = new ImageUploadConfig($okt, $okt->faq->getImageUpload());
$oImageUploadConfig->setBaseUrl('module.php?m=faq&amp;action=config&amp;');

/* Traitements
----------------------------------------------------------*/

# régénération des miniatures
if (! empty($_GET['minregen']))
{
	$okt->faq->regenMinImages();
	
	$okt->flash->success(__('c_c_confirm_thumb_regenerated'));
	
	http::redirect('module.php?m=faq&action=config');
}

# suppression filigrane
if (! empty($_GET['delete_watermark']))
{
	$okt->faq->config->write(array(
		'images' => $oImageUploadConfig->removeWatermak()
	));
	
	$okt->flash->success(__('c_c_confirm_watermark_deleted'));
	
	http::redirect('module.php?m=faq&action=config');
}

# enregistrement configuration
if (! empty($_POST['form_sent']))
{
	$p_enable_rte = ! empty($_POST['p_enable_rte']) ? $_POST['p_enable_rte'] : '';
	$p_enable_metas = ! empty($_POST['p_enable_metas']) ? true : false;
	$p_enable_filters = ! empty($_POST['p_enable_filters']) ? true : false;
	$p_enable_categories = ! empty($_POST['p_enable_categories']) ? true : false;
	
	$p_enable_files = ! empty($_POST['p_enable_files']) ? true : false;
	$p_number_files = ! empty($_POST['p_number_files']) ? intval($_POST['p_number_files']) : 0;
	$p_allowed_exts = ! empty($_POST['p_allowed_exts']) ? $_POST['p_allowed_exts'] : '';
	
	$aImagesConfig = $oImageUploadConfig->getPostConfig();
	
	$p_name = ! empty($_POST['p_name']) && is_array($_POST['p_name']) ? $_POST['p_name'] : array();
	$p_name_seo = ! empty($_POST['p_name_seo']) && is_array($_POST['p_name_seo']) ? $_POST['p_name_seo'] : array();
	$p_title = ! empty($_POST['p_title']) && is_array($_POST['p_title']) ? $_POST['p_title'] : array();
	
	$p_meta_description = ! empty($_POST['p_meta_description']) && is_array($_POST['p_meta_description']) ? $_POST['p_meta_description'] : array();
	$p_meta_keywords = ! empty($_POST['p_meta_keywords']) && is_array($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : array();
	
	if ($okt->error->isEmpty())
	{
		$faq_conf = array(
			'name' => $p_name,
			'name_seo' => $p_name_seo,
			'title' => $p_title,
			
			'meta_description' => $p_meta_description,
			'meta_keywords' => $p_meta_keywords,
			
			'enable_rte' => $p_enable_rte,
			'enable_metas' => (boolean) $p_enable_metas,
			'enable_filters' => (boolean) $p_enable_filters,
			'enable_categories' => (boolean) $p_enable_categories,
			
			'files' => array(
				'enable' => (boolean) $p_enable_files,
				'number' => $p_number_files,
				'allowed_exts' => $p_allowed_exts
			),
			
			'images' => $aImagesConfig
		);
		
		$okt->faq->config->write($faq_conf);
		
		$okt->flash->success(__('c_c_confirm_configuration_updated'));
		
		http::redirect('module.php?m=faq&action=config');
	}
}

/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_configuration'));

# Lockable
$okt->page->lockable();

# Onglets
$okt->page->tabs();

# Lang switcher
if (! $okt->languages->unique)
{
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# En-tête
require OKT_ADMIN_HEADER_FILE;
?>

<form action="module.php" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<li><a href="#tab_general"><span><?php _e('m_faq_general') ?></span></a></li>
			<li><a href="#tab_files"><span><?php _e('m_faq_attached_files') ?></span></a></li>
			<li><a href="#tab_seo"><span><?php _e('c_c_seo') ?></span></a></li>
		</ul>

		<div id="tab_general">
			<h3><?php _e('m_faq_general') ?></h3>

			<fieldset>
				<legend><?php _e('m_faq_features') ?></legend>

				<p class="field">
					<label><?php echo form::checkbox('p_enable_metas', 1, $okt->faq->config->enable_metas)?>
				<?php _e('c_c_enable_seo_help') ?></label>
				</p>

				<p class="field">
					<label><?php echo form::checkbox('p_enable_filters', 1, $okt->faq->config->enable_filters)?>
				<?php _e('m_faq_filters_website') ?></label>
				</p>

				<p class="field">
					<label><?php echo form::checkbox('p_enable_categories', 1, $okt->faq->config->enable_categories)?>
				<?php _e('m_faq_enable_sections') ?></label>
				</p>

			<?php if ($okt->page->hasRte()) : ?>
				<p class="field">
					<label for="p_enable_rte"><?php _e('m_faq_rich_text_editor') ?></label>
				<?php echo form::select('p_enable_rte',array_merge(array('Désactivé'=>0), $okt->page->getRteList(true)), $okt->faq->config->enable_rte) ?></p>
			<?php else : ?>
				<p><?php _e('m_faq_no_rich_text_editor')?>
				<?php echo form::hidden('p_enable_rte', 0); ?></p>
			<?php endif;?>
			</fieldset>
		</div>
		<!-- #tab_general -->

		<div id="tab_files">
			<h3><?php _e('m_faq_attached_files') ?></h3>

			<h4><?php _e('m_faq_images') ?></h4>

			<?php echo $oImageUploadConfig->getForm(); ?>

			<h4><?php _e('m_faq_other_files') ?></h4>

			<fieldset>
				<legend><?php _e('m_faq_other_attached_files') ?></legend>

				<p class="field">
					<label><?php echo form::checkbox('p_enable_files',1,$okt->faq->config->files['enable'])?>
				<?php _e('m_faq_enable_attached_files') ?></label>
				</p>

				<p class="field">
					<label for="p_number_files"><?php _e('m_faq_num_attached_files') ?></label>
				<?php echo form::text('p_number_files', 10, 255, $okt->faq->config->files['number']) ?></p>

				<p class="field">
					<label for="p_allowed_exts"><?php _e('m_faq_extensions_list_allowed') ?></label>
				<?php echo form::text('p_allowed_exts', 60, 255, $okt->faq->config->files['allowed_exts']) ?></p>
			</fieldset>
		</div>
		<!-- #tab_files -->

		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<fieldset>
				<legend><?php _e('c_c_seo_identity_meta') ?></legend>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
					<label for="p_name_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_intitle_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span
						class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name['.$aLanguage['code'].']','p_name_'.$aLanguage['code']), 40, 255, (isset($okt->faq->config->name[$aLanguage['code']]) ? html::escapeHTML($okt->faq->config->name[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
					<label for="p_title_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_tag_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span
						class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 40, 255, (isset($okt->faq->config->title[$aLanguage['code']]) ? html::escapeHTML($okt->faq->config->title[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
					<label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_desc_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span
						class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 40, 255, (isset($okt->faq->config->meta_description[$aLanguage['code']]) ? html::escapeHTML($okt->faq->config->meta_description[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
					<label for="p_name_seo_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_seo_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span
						class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name_seo['.$aLanguage['code'].']','p_name_seo_'.$aLanguage['code']), 60, 255, (isset($okt->faq->config->name_seo[$aLanguage['code']]) ? html::escapeHTML($okt->faq->config->name_seo[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
					<label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_keywords_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span
						class="lang-switcher-buttons"></span></label>
				<?php echo form::textarea('p_meta_keywords['.$aLanguage['code'].']', 57, 5, (isset($okt->faq->config->meta_keywords[$aLanguage['code']]) ? html::escapeHTML($okt->faq->config->meta_keywords[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>
			</fieldset>

		</div>
		<!-- #tab_seo -->

	</div>
	<!-- #tabered -->

	<p><?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden('m', 'faq'); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" />
	</p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
