<?php
/**
 * @ingroup okt_module_galleries
 * @brief La page de configuration
 *
 */
use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Images\ImageUploadConfig;
use Okatea\Tao\Themes\TemplatesSet;

# Accès direct interdit
if (!defined('ON_MODULE'))
	die();
	
	/* Initialisations
----------------------------------------------------------*/
	
# Chargement des locales
$okt['l10n']->loadFile(__DIR__ . '/../Locales/%s/admin.config');

# Gestion des images des éléments
$oItemImageUploadConfig = new ImageUploadConfig($okt, $okt->galleries->items->getImageUploadInstance());
$oItemImageUploadConfig->setBaseUrl('module.php?m=galleries&amp;action=config&amp;item_');
$oItemImageUploadConfig->setFormPrefix('p_item_');
$oItemImageUploadConfig->setUnique(true);

# Gestion des images des galeries
$oGalleryImageUploadConfig = new ImageUploadConfig($okt, $okt->galleries->tree->getImageUploadInstance());
$oGalleryImageUploadConfig->setBaseUrl('module.php?m=galleries&amp;action=config&amp;gallery_');
$oGalleryImageUploadConfig->setFormPrefix('p_gallery_');
$oGalleryImageUploadConfig->setUnique(true);

# Gestionnaires de templates
$oTemplatesList = new TemplatesSet($okt, $okt->galleries->config->templates['list'], 'galleries/list', 'list', 'module.php?m=galleries&amp;action=config&amp;');

$oTemplatesGallery = new TemplatesSet($okt, $okt->galleries->config->templates['gallery'], 'galleries/gallery', 'gallery', 'module.php?m=galleries&amp;action=config&amp;');

$oTemplatesItem = new TemplatesSet($okt, $okt->galleries->config->templates['item'], 'galleries/item', 'item', 'module.php?m=galleries&amp;action=config&amp;');

/* Traitements
----------------------------------------------------------*/

# régénération des miniatures
if (!empty($_GET['item_minregen']))
{
	$okt->galleries->items->regenMinImages();
	
	$okt['flashMessages']->success(__('c_c_confirm_thumb_regenerated'));
	
	http::redirect('module.php?m=galleries&action=config');
}
if (!empty($_GET['gallery_minregen']))
{
	$okt->galleries->tree->regenMinImages();
	
	$okt['flashMessages']->success(__('c_c_confirm_thumb_regenerated'));
	
	http::redirect('module.php?m=galleries&action=config');
}

# suppression filigrane
if (!empty($_GET['item_delete_watermark']))
{
	$okt->galleries->config->write(array(
		'images' => $oItemImageUploadConfig->removeWatermak()
	));
	
	$okt['flashMessages']->success(__('c_c_confirm_watermark_deleted'));
	
	http::redirect('module.php?m=galleries&action=config');
}
if (!empty($_GET['gallery_delete_watermark']))
{
	$okt->galleries->config->write(array(
		'images_gal' => $oGalleryImageUploadConfig->removeWatermak()
	));
	
	$okt['flashMessages']->success(__('c_c_confirm_watermark_deleted'));
	
	http::redirect('module.php?m=galleries&action=config');
}

# enregistrement configuration
if (!empty($_POST['form_sent']))
{
	$p_name = !empty($_POST['p_name']) && is_array($_POST['p_name']) ? $_POST['p_name'] : [];
	$p_name_seo = !empty($_POST['p_name_seo']) && is_array($_POST['p_name_seo']) ? $_POST['p_name_seo'] : [];
	$p_title = !empty($_POST['p_title']) && is_array($_POST['p_title']) ? $_POST['p_title'] : [];
	$p_meta_description = !empty($_POST['p_meta_description']) && is_array($_POST['p_meta_description']) ? $_POST['p_meta_description'] : [];
	$p_meta_keywords = !empty($_POST['p_meta_keywords']) && is_array($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : [];
	
	$p_enable_metas = !empty($_POST['p_enable_metas']) ? true : false;
	$p_enable_gal_password = !empty($_POST['p_enable_gal_password']) ? true : false;
	$p_enable_gal_rte = !empty($_POST['p_enable_gal_rte']) ? $_POST['p_enable_gal_rte'] : '';
	$p_enable_rte = !empty($_POST['p_enable_rte']) ? $_POST['p_enable_rte'] : '';
	
	$p_enable_zip_upload = !empty($_POST['p_enable_zip_upload']) ? true : false;
	$p_enable_multiple_upload = !empty($_POST['p_enable_multiple_upload']) ? true : false;
	
	$p_multiple_upload_type = !empty($_POST['p_multiple_upload_type']) ? $_POST['p_multiple_upload_type'] : 'plupload';
	
	$p_images = $oItemImageUploadConfig->getPostConfig();
	
	$p_images_gal = $oGalleryImageUploadConfig->getPostConfig();
	
	$p_tpl_list = $oTemplatesList->getPostConfig();
	$p_tpl_gallery = $oTemplatesGallery->getPostConfig();
	$p_tpl_item = $oTemplatesItem->getPostConfig();
	
	if ($okt->error->isEmpty())
	{
		$aNewConf = array(
			'name' => $p_name,
			'name_seo' => $p_name_seo,
			'title' => $p_title,
			'meta_description' => $p_meta_description,
			'meta_keywords' => $p_meta_keywords,
			
			'enable_metas' => (boolean) $p_enable_metas,
			'enable_gal_password' => (boolean) $p_enable_gal_password,
			'enable_gal_rte' => $p_enable_gal_rte,
			'enable_rte' => $p_enable_rte,
			
			'enable_zip_upload' => $p_enable_zip_upload,
			'enable_multiple_upload' => $p_enable_multiple_upload,
			'multiple_upload_type' => $p_multiple_upload_type,
			
			'images' => $p_images,
			
			'images_gal' => $p_images_gal,
			
			'templates' => array(
				'list' => $p_tpl_list,
				'gallery' => $p_tpl_gallery,
				'item' => $p_tpl_item
			)
		);
		
		$okt->galleries->config->write($aNewConf);
		
		$okt['flashMessages']->success(__('c_c_confirm_configuration_updated'));
		
		http::redirect('module.php?m=galleries&action=config');
	}
}

/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_configuration'));

# Lockable
$okt->page->lockable();

# javascript
$okt->page->tabs();

# Lang switcher
if (!$okt['languages']->hasUniqueLanguage())
{
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Loader
$okt->page->loader('.lazy-load');

# JS pour activer/désactiver le choix du type d'upload multiple
$okt->page->js->addScript('
	function handleMultipleUploaStatus() {
		if ($("#p_enable_multiple_upload").is(":checked")) {
			$("#p_multiple_upload_type").removeAttr("disabled")
			.parent().removeClass("disabled");
		}
		else {
			$("#p_multiple_upload_type").attr("disabled", "")
			.parent().addClass("disabled");
		}
	}
');
$okt->page->js->addReady('
	handleMultipleUploaStatus();
	$("#p_enable_multiple_upload").change(function(){handleMultipleUploaStatus();});
');

# En-tête
require OKT_ADMIN_HEADER_FILE;
?>

<form action="module.php" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<li><a href="#tab_general"><span><?php _e('m_galleries_config_tab_general') ?></span></a></li>
			<li><a href="#tab_images_galleries"><span><?php _e('m_galleries_config_tab_images_galleries') ?></span></a></li>
			<li><a href="#tab_images_items"><span><?php _e('m_galleries_config_tab_images_items') ?></span></a></li>
			<li><a href="#tab_tpl"><span><?php _e('m_galleries_config_tab_tpl') ?></span></a></li>
			<li><a href="#tab_seo"><span><?php _e('c_c_seo') ?></span></a></li>
		</ul>

		<div id="tab_general">
			<h3><?php _e('m_galleries_config_tab_general') ?></h3>

			<fieldset>
				<legend><?php _e('m_galleries_config_features') ?></legend>

				<p class="field">
					<label><?php echo form::checkbox('p_enable_metas', 1, $okt->galleries->config->enable_metas)?>
				<?php _e('m_galleries_config_enable_seo') ?></label>
				</p>

				<p class="field">
					<label><?php echo form::checkbox('p_enable_gal_password', 1, $okt->galleries->config->enable_gal_password)?>
				<?php _e('m_galleries_config_enable_password') ?></label>
				</p>

			<?php if ($okt->page->hasRte()) : ?>
				<p class="field">
					<label for="p_enable_rte"><?php _e('m_galleries_config_galleries_rich_text_editor') ?></label>
				<?php echo form::select('p_enable_gal_rte', array_merge(array(__('c_c_Disabled')=>0), $okt->page->getRteList(true)),$okt->galleries->config->enable_gal_rte) ?></p>

				<p class="field">
					<label for="p_enable_rte"><?php _e('m_galleries_config_items_rich_text_editor') ?></label>
				<?php echo form::select('p_enable_rte', array_merge(array(__('c_c_Disabled')=>0), $okt->page->getRteList(true)), $okt->galleries->config->enable_rte) ?></p>
			<?php else : ?>
				<p><?php _e('m_galleries_config_no_rich_text_editor')?>
				<?php echo form::hidden('p_enable_gal_rte', 0); ?>
				<?php echo form::hidden('p_enable_rte', 0); ?></p>
			<?php endif;?>

			</fieldset>

			<fieldset>
				<legend><?php _e('m_galleries_config_multiples_upload') ?></legend>

				<p class="field">
					<label><?php echo form::checkbox('p_enable_zip_upload', 1, $okt->galleries->config->enable_zip_upload)?>
				<?php _e('m_galleries_config_zip_upload_enable') ?></label>
				</p>

				<p class="field">
					<label><?php echo form::checkbox('p_enable_multiple_upload', 1, $okt->galleries->config->enable_multiple_upload)?>
				<?php _e('m_galleries_config_multiples_upload_enable') ?></label>
				</p>

				<p class="field">
					<label for="p_multiple_upload_type"><?php _e('m_galleries_config_multiples_upload_type') ?></label>
				<?php echo form::select('p_multiple_upload_type', $okt->galleries->getMultipleUploadTypes(), $okt->galleries->config->multiple_upload_type) ?></p>

			</fieldset>
		</div>
		<!-- #tab_general -->

		<div id="tab_images_galleries">
			<h3><?php _e('m_galleries_config_tab_images_galleries') ?></h3>
			<?php echo $oGalleryImageUploadConfig->getForm(); ?>
		</div>
		<!-- #tab_images_galleries -->

		<div id="tab_images_items">
			<h3><?php _e('m_galleries_config_tab_images_items') ?></h3>
			<?php echo $oItemImageUploadConfig->getForm(); ?>
		</div>
		<!-- #tab_images_items -->

		<div id="tab_tpl">
			<h3><?php _e('m_galleries_config_tab_tpl_title') ?></h3>

			<h4><?php _e('m_galleries_config_tpl_list') ?></h4>

			<?php echo $oTemplatesList->getHtmlConfigUsablesTemplates(); ?>

			<h4><?php _e('m_galleries_config_tpl_gallery') ?></h4>

			<?php echo $oTemplatesGallery->getHtmlConfigUsablesTemplates(); ?>

			<h4><?php _e('m_galleries_config_tpl_item') ?></h4>

			<?php echo $oTemplatesItem->getHtmlConfigUsablesTemplates(); ?>

		</div>
		<!-- #tab_tpl -->

		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<fieldset>
				<legend><?php _e('c_c_seo_identity_meta') ?></legend>

				<?php foreach ($okt['languages']->getList() as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
					<label for="p_name_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->hasUniqueLanguage() ? _e('c_c_seo_module_intitle') : printf(__('c_c_seo_module_intitle_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span
						class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name['.$aLanguage['code'].']','p_name_'.$aLanguage['code']), 60, 255, (isset($okt->galleries->config->name[$aLanguage['code']]) ? html::escapeHTML($okt->galleries->config->name[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
					<label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->hasUniqueLanguage() ? _e('c_c_seo_module_title_tag') : printf(__('c_c_seo_module_title_tag_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span
						class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($okt->galleries->config->title[$aLanguage['code']]) ? html::escapeHTML($okt->galleries->config->title[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
					<label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->hasUniqueLanguage() ? _e('c_c_seo_meta_desc') : printf(__('c_c_seo_meta_desc_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span
						class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, (isset($okt->galleries->config->meta_description[$aLanguage['code']]) ? html::escapeHTML($okt->galleries->config->meta_description[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
					<label for="p_name_seo_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->hasUniqueLanguage() ? _e('c_c_seo_module_title_seo') : printf(__('c_c_seo_module_title_seo_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span
						class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name_seo['.$aLanguage['code'].']','p_name_seo_'.$aLanguage['code']), 60, 255, (isset($okt->galleries->config->name_seo[$aLanguage['code']]) ? html::escapeHTML($okt->galleries->config->name_seo[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>">
					<label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->hasUniqueLanguage() ? _e('c_c_seo_meta_keywords') : printf(__('c_c_seo_meta_keywords_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span
						class="lang-switcher-buttons"></span></label>
				<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 57, 5, (isset($okt->galleries->config->meta_keywords[$aLanguage['code']]) ? html::escapeHTML($okt->galleries->config->meta_keywords[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>

			</fieldset>

		</div>
		<!-- #tab_seo -->

	</div>
	<!-- #tabered -->

	<p><?php echo form::hidden(array('m'),'galleries')?>
	<?php echo form::hidden(array('form_sent'), 1)?>
	<?php echo form::hidden(array('action'), 'config')?>
	<?php echo Page::formtoken() ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" />
	</p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
