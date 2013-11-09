<?php
/**
 * @ingroup okt_module_estimate
 * @brief La page de configuration
 *
 */

# Accès direct interdit
if (!defined('ON_ESTIMATE_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Gestionnaires de templates
$oTemplatesForm = new oktTemplatesSet($okt, $okt->estimate->config->templates['form'], 'estimate/form', 'form');
$oTemplatesForm->setBaseUrl('module.php?m=estimate&amp;action=config&amp;');

$oTemplatesSummary = new oktTemplatesSet($okt, $okt->estimate->config->templates['summary'], 'estimate/summary', 'summary');
$oTemplatesSummary->setBaseUrl('module.php?m=estimate&amp;action=config&amp;');


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

	$p_tpl_form = $oTemplatesForm->getPostConfig();
	$p_tpl_summary = $oTemplatesSummary->getPostConfig();

	$p_public_form_url = !empty($_POST['p_public_form_url']) ? $_POST['p_public_form_url'] : '';

	foreach ($p_public_form_url as $lang=>$url) {
		$p_public_form_url[$lang] = util::formatAppPath($url,false,false);
	}

	$p_public_summary_url = !empty($_POST['p_public_summary_url']) ? $_POST['p_public_summary_url'] : '';

	foreach ($p_public_summary_url as $lang=>$url) {
		$p_public_summary_url[$lang] = util::formatAppPath($url,false,false);
	}


	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'name' => $p_name,
			'name_seo' => $p_name_seo,
			'title' => $p_title,
			'meta_description' => $p_meta_description,
			'meta_keywords' => $p_meta_keywords,

			'templates' => array(
				'form' => $p_tpl_form,
				'summary' => $p_tpl_summary
			),

			'public_form_url' => $p_public_form_url,
			'public_summary_url' => $p_public_summary_url
		);

		try
		{
			$okt->estimate->config->write($new_conf);
			http::redirect('module.php?m=estimate&action=config&updated=1');
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
$okt->page->addGlobalTitle(__('m_estimate_configuration'));

# Confirmations
$okt->page->messages->success('updated',__('c_c_confirm_configuration_updated'));

# Lockable
$okt->page->lockable();

# Onglets
$okt->page->tabs();

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered','.lang-switcher-buttons');
}


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<li><a href="#tab_general"><span><?php _e('m_estimate_config_tab_general') ?></span></a></li>
			<li><a href="#tab_tpl"><span><?php _e('m_estimate_config_tab_tpl') ?></span></a></li>
			<li><a href="#tab_seo"><span><?php _e('c_c_seo') ?></span></a></li>
		</ul>

		<div id="tab_general">
			<h3><?php _e('m_estimate_config_tab_general') ?></h3>

		</div><!-- #tab_general -->

		<div id="tab_tpl">
			<h3><?php _e('m_estimate_config_tab_tpl_title') ?></h3>

			<h4><?php _e('m_estimate_config_tpl_form') ?></h4>

			<?php echo $oTemplatesForm->getHtmlConfigUsablesTemplates(); ?>

			<h4><?php _e('m_estimate_config_tpl_summary') ?></h4>

			<?php echo $oTemplatesSummary->getHtmlConfigUsablesTemplates(); ?>

		</div><!-- #tab_tpl -->

		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<fieldset>
				<legend><?php _e('c_c_seo_identity_meta') ?></legend>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>
				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_intitle_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name['.$aLanguage['code'].']','p_name_'.$aLanguage['code']), 60, 255, (isset($okt->estimate->config->name[$aLanguage['code']]) ? html::escapeHTML($okt->estimate->config->name[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_tag_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($okt->estimate->config->title[$aLanguage['code']]) ? html::escapeHTML($okt->estimate->config->title[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_desc_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, (isset($okt->estimate->config->meta_description[$aLanguage['code']]) ? html::escapeHTML($okt->estimate->config->meta_description[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_seo_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_module_title_seo_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name_seo['.$aLanguage['code'].']','p_name_seo_'.$aLanguage['code']), 60, 255, (isset($okt->estimate->config->name_seo[$aLanguage['code']]) ? html::escapeHTML($okt->estimate->config->name_seo[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php printf(__('c_c_seo_meta_keywords_in_%s'), html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 57, 5, (isset($okt->estimate->config->meta_keywords[$aLanguage['code']]) ? html::escapeHTML($okt->estimate->config->meta_keywords[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>
			</fieldset>

			<fieldset>
				<legend><?php _e('c_c_seo_schema_url') ?></legend>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>
				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_public_form_url_<?php echo $aLanguage['code'] ?>"><?php printf(__('m_estimate_form_url_from_%s_in_%s'), '<code>'.$okt->config->app_url.$aLanguage['code'].'/</code>', html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_public_form_url['.$aLanguage['code'].']','p_public_form_url_'.$aLanguage['code']), 60, 255, (isset($okt->estimate->config->public_form_url[$aLanguage['code']]) ? html::escapeHTML($okt->estimate->config->public_form_url[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_public_summary_url_<?php echo $aLanguage['code'] ?>"><?php printf(__('m_estimate_summary_url_from_%s_in_%s'), '<code>'.$okt->config->app_url.$aLanguage['code'].'/</code>', html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_public_summary_url['.$aLanguage['code'].']','p_public_summary_url_'.$aLanguage['code']), 60, 255, (isset($okt->estimate->config->public_summary_url[$aLanguage['code']]) ? html::escapeHTML($okt->estimate->config->public_summary_url[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>

			</fieldset>
		</div><!-- #tab_seo -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','estimate'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
