<?php
/**
 * @ingroup okt_module_partners
 * @brief La page de configuration du module.
 *
 */

use Tao\Utils as util;
use Tao\Forms\StaticFormElements as form;
use Tao\Images\ImageUploadConfig;

# Accès direct interdit
if (!defined('ON_PARTNERS_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$oImageUploadConfig = new ImageUploadConfig($okt,$okt->partners->getLogoUpload());
$oImageUploadConfig->setBaseUrl('module.php?m=partners&amp;action=config&amp;');
$oImageUploadConfig->setUnique(true);
$oImageUploadConfig->setWithWatermark(false);


/* Traitements
----------------------------------------------------------*/

# régénération des miniatures
if (!empty($_GET['minregen']))
{
	$okt->partners->regenMinLogos();

	$okt->page->flashMessages->addSuccess(__('c_c_confirm_thumb_regenerated'));

	http::redirect('module.php?m=partners&action=config');
}

# suppression filigrane
if (!empty($_GET['delete_watermark']))
{
	$okt->partners->config->write(array('images'=>$oImageUploadConfig->removeWatermak()));

	$okt->news->config->write(array('images'=>$oImageUploadConfig->removeWatermak()));

	http::redirect('module.php?m=partners&action=config');
}

# formulaire envoyé
if (!empty($_POST['form_sent']))
{
	$p_enable_categories = !empty($_POST['p_enable_categories']) ? true : false;
	$p_enable_rte = !empty($_POST['p_enable_rte']) ? $_POST['p_enable_rte'] : '';

	$p_chp_name = !empty($_POST['p_chp_name']) ? intval($_POST['p_chp_name']) : 0;
	$p_chp_description = !empty($_POST['p_chp_description']) ? intval($_POST['p_chp_description']) : 0;
	$p_chp_url = !empty($_POST['p_chp_url']) ? intval($_POST['p_chp_url']) : 0;
	$p_chp_url_title = !empty($_POST['p_chp_url_title']) ? intval($_POST['p_chp_url_title']) : 0;
	$p_chp_logo = !empty($_POST['p_chp_logo']) ? intval($_POST['p_chp_logo']) : 0;

	$aImagesConfig = $oImageUploadConfig->getPostConfig();

	$p_name = !empty($_POST['p_name']) && is_array($_POST['p_name'])  ? $_POST['p_name'] : array();
	$p_name_seo = !empty($_POST['p_name_seo']) && is_array($_POST['p_name_seo'])  ? $_POST['p_name_seo'] : array();
	$p_title = !empty($_POST['p_title']) && is_array($_POST['p_title']) ? $_POST['p_title'] : array();
	$p_meta_description = !empty($_POST['p_meta_description']) && is_array($_POST['p_meta_description']) ? $_POST['p_meta_description'] : array();
	$p_meta_keywords = !empty($_POST['p_meta_keywords']) && is_array($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : array();

	$p_public_url = !empty($_POST['p_public_url']) && is_array($_POST['p_public_url']) ? $_POST['p_public_url'] : array();

	foreach ($p_public_url as $lang=>$url) {
		$p_public_url[$lang] = util::formatAppPath($url,false,false);
	}


	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'enable_categories' => (boolean)$p_enable_categories,
			'enable_rte' => $p_enable_rte,

			'chp_name' => (integer)$p_chp_name,
			'chp_description' => (integer)$p_chp_description,
			'chp_url' => (integer)$p_chp_url,
			'chp_url_title' => (integer)$p_chp_url_title,
			'chp_logo' => (integer)$p_chp_logo,

			'name' => $p_name,
			'name_seo' => $p_name_seo,
			'title' => $p_title,
			'meta_description' => $p_meta_description,
			'meta_keywords' => $p_meta_keywords,

			'public_url' => $p_public_url,

			'images' => $aImagesConfig
		);

		try
		{
			$okt->partners->config->write($new_conf);

			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

			http::redirect('module.php?m=partners&action=config');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_configuration'));

# Lockable
$okt->page->lockable();

# Tabs
$okt->page->tabs();

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered','.lang-switcher-buttons');
}


$field_choice = util::getStatusFieldChoices();

$field_logo_choice = util::getStatusFieldChoices(false);


# En-tête
include OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_general"><span><?php _e('m_partners_general') ?></span></a></li>
			<li><a href="#tab_fields"><span><?php _e('m_partners_fields') ?></span></a></li>
			<li><a href="#tab_images"><span><?php _e('m_partners_images')?></span></a></li>
			<li><a href="#tab_seo"><span><?php _e('m_partners_seo') ?></span></a></li>
		</ul>

		<div id="tab_general">
			<h3><?php _e('m_partners_general') ?></h3>

			<p class="field"><label for="p_enable_categories"><?php echo form::checkbox('p_enable_categories', 1, $okt->partners->config->enable_categories) ?>
			<?php _e('m_partners_enable_categories') ?></label></p>

			<?php if ($okt->page->hasRte()) : ?>
				<p class="field"><label for="p_enable_rte"><?php _e('m_partners_rich_text_editor') ?></label>
				<?php echo form::select('p_enable_rte', array_merge(array(__('c_c_Disabled')=>0),$okt->page->getRteList(true)), $okt->partners->config->enable_rte) ?></p>
			<?php else : ?>
				<p><?php _e('m_partners_no_rich_text_editor') ?>
				<?php echo form::hidden('p_enable_rte', 0); ?></p>
			<?php endif;?>
		</div><!-- #tab_general -->

		<div id="tab_fields">
			<h3><?php _e('m_partners_fields') ?></h3>
			<fieldset>
				<legend><?php _e('m_partners_activate_fields') ?></legend>

				<div class="three-cols">
					<p class="col field"><label for="p_chp_name"><?php _e('c_c_Name') ?></label>
					<?php echo form::select('p_chp_name', $field_choice, $okt->partners->config->chp_name) ?></p>

					<p class="col field"><label for="p_chp_description"><?php _e('c_c_description') ?></label>
					<?php echo form::select('p_chp_description', $field_choice, $okt->partners->config->chp_description) ?></p>
				</div>
				<div class="three-cols">
					<p class="col field"><label for="p_chp_url"><?php _e('m_partners_website') ?></label>
					<?php echo form::select('p_chp_url', $field_choice, $okt->partners->config->chp_url) ?></p>

					<p class="col field"><label for="p_chp_url_title"><?php _e('m_partners_website_title') ?></label>
					<?php echo form::select('p_chp_url_title', $field_choice, $okt->partners->config->chp_url_title) ?></p>
				</div>
				<div id="three-cols">
					<p class="col field"><label for="p_chp_logo"><?php _e('m_partners_logo') ?></label>
					<?php echo form::select('p_chp_logo', $field_logo_choice, $okt->partners->config->chp_logo) ?></p>
				</div>
			</fieldset>
		</div><!-- #tab-fields -->

		<div id="tab_images"><!-- #tab-images  -->
			<h3><?php _e('m_partners_images')?></h3>
			<?php echo $oImageUploadConfig->getForm(); ?>
		</div><!-- #tab-images -->

		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<fieldset>
				<legend><?php _e('c_c_seo_identity_meta') ?></legend>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_module_intitle') : printf(__('c_c_seo_module_intitle_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name['.$aLanguage['code'].']','p_name_'.$aLanguage['code']), 60, 255, (isset($okt->partners->config->name[$aLanguage['code']]) ? html::escapeHTML($okt->partners->config->name[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_module_title_tag') : printf(__('c_c_seo_module_title_tag_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($okt->partners->config->title[$aLanguage['code']]) ? html::escapeHTML($okt->partners->config->title[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_desc') : printf(__('c_c_seo_meta_desc_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, (isset($okt->partners->config->meta_description[$aLanguage['code']]) ? html::escapeHTML($okt->partners->config->meta_description[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_seo_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_module_title_seo') : printf(__('c_c_seo_module_title_seo_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name_seo['.$aLanguage['code'].']','p_name_seo_'.$aLanguage['code']), 60, 255, (isset($okt->partners->config->name_seo[$aLanguage['code']]) ? html::escapeHTML($okt->partners->config->name_seo[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_keywords') : printf(__('c_c_seo_meta_keywords_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 57, 5, (isset($okt->partners->config->meta_keywords[$aLanguage['code']]) ? html::escapeHTML($okt->partners->config->meta_keywords[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>

			</fieldset>

			<fieldset>
				<legend><?php _e('c_c_seo_schema_url') ?></legend>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_public_url_<?php echo $aLanguage['code'] ?>"><?php printf(__('m_partners_%s_%s'), '<code>'.$okt->config->app_url.$aLanguage['code'].'/</code>', html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_public_url['.$aLanguage['code'].']','p_public_url_'.$aLanguage['code']), 40, 255, (isset($okt->partners->config->public_url[$aLanguage['code']]) ? html::escapeHTML($okt->partners->config->public_url[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>

			</fieldset>

		</div><!-- #tab_seo -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m', 'partners'); ?>
	<?php echo form::hidden('action', 'config'); ?>
	<?php echo form::hidden('form_sent', 1); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

