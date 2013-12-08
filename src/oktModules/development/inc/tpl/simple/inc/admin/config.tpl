<?php
##header##

use Tao\Admin\Page;
use Tao\Misc\Utilities as util;

# Accès direct interdit
if (!defined('ON_##module_upper_id##_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/


/* Traitements
----------------------------------------------------------*/

# enregistrement configuration
if (!empty($_POST['form_sent']))
{
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
			'name' => $p_name,
			'name_seo' => $p_name_seo,
			'title' => $p_title,
			'meta_description' => $p_meta_description,
			'meta_keywords' => $p_meta_keywords,

			'public_url' => $p_public_url,
		);

		try
		{
			$okt->##module_id##->config->write($new_conf);
			http::redirect('module.php?m=##module_id##&action=config&updated=1');
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
$okt->page->addGlobalTitle(__('m_##module_id##_configuration'));

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered','.lang-switcher-buttons');
}

# Confirmations
$okt->page->messages->success('updated',__('c_c_confirm_configuration_updated'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">

		<h3><?php _e('c_c_seo_help') ?></h3>

		<fieldset>
			<legend><?php _e('c_c_seo_identity_meta') ?></legend>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_module_intitle') : printf(__('c_c_seo_module_intitle_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_name['.$aLanguage['code'].']','p_name_'.$aLanguage['code']), 60, 255, (isset($okt->##module_id##->config->name[$aLanguage['code']]) ? html::escapeHTML($okt->##module_id##->config->name[$aLanguage['code']]) : '')) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_module_title_tag') : printf(__('c_c_seo_module_title_tag_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($okt->##module_id##->config->title[$aLanguage['code']]) ? html::escapeHTML($okt->##module_id##->config->title[$aLanguage['code']]) : '')) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_desc') : printf(__('c_c_seo_meta_desc_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, (isset($okt->##module_id##->config->meta_description[$aLanguage['code']]) ? html::escapeHTML($okt->##module_id##->config->meta_description[$aLanguage['code']]) : '')) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_seo_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_module_title_seo') : printf(__('c_c_seo_module_title_seo_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_name_seo['.$aLanguage['code'].']','p_name_seo_'.$aLanguage['code']), 60, 255, (isset($okt->##module_id##->config->name_seo[$aLanguage['code']]) ? html::escapeHTML($okt->##module_id##->config->name_seo[$aLanguage['code']]) : '')) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_keywords') : printf(__('c_c_seo_meta_keywords_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 57, 5, (isset($okt->##module_id##->config->meta_keywords[$aLanguage['code']]) ? html::escapeHTML($okt->##module_id##->config->meta_keywords[$aLanguage['code']]) : '')) ?></p>

			<?php endforeach; ?>

		</fieldset>

		<fieldset>
			<legend><?php _e('c_c_seo_schema_url') ?></legend>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_public_url_<?php echo $aLanguage['code'] ?>"><?php printf(__('m_##module_id##_config_url_from_%s'), '<code>'.$okt->config->app_url.($okt->languages->unique ? '' : $aLanguage['code'].'/').'</code>', html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_public_url['.$aLanguage['code'].']','p_public_url_'.$aLanguage['code']), 60, 255, (isset($okt->##module_id##->config->public_url[$aLanguage['code']]) ? html::escapeHTML($okt->##module_id##->config->public_url[$aLanguage['code']]) : '')) ?></p>

			<?php endforeach; ?>

		</fieldset>

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','##module_id##'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
