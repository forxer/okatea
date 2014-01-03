<?php

use Tao\Forms\Statics\FormElements as form;

$this->extend('layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_configuration'));

# Lockable
$okt->page->lockable();

# Onglets
$okt->page->tabs();

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered','.lang-switcher-buttons');
}

# Loader
$okt->page->loader('.lazy-load');

# Permission checkboxes
$okt->page->updatePermissionsCheckboxes();

?>

<form action="<?php $view->generateUrl('News_config') ?>" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<li><a href="#tab_general"><span><?php _e('m_news_config_tab_general') ?></span></a></li>
			<li><a href="#tab_categories"><span><?php _e('m_news_config_tab_categories') ?></span></a></li>
			<li><a href="#tab_tpl"><span><?php _e('m_news_config_tab_tpl') ?></span></a></li>
			<li><a href="#tab_files"><span><?php _e('m_news_config_tab_attached_files') ?></span></a></li>
			<li><a href="#tab_seo"><span><?php _e('c_c_seo') ?></span></a></li>
		</ul>

		<div id="tab_general">
			<h3><?php _e('m_news_config_tab_general') ?></h3>

			<fieldset>
				<legend><?php _e('m_news_config_features') ?></legend>

				<p class="field"><label><?php echo form::checkbox('p_enable_metas', 1, $okt->News->config->enable_metas) ?>
				<?php _e('m_news_config_enable_news_seo') ?></label></p>

				<p class="field"><label><?php echo form::checkbox('p_enable_filters', 1, $okt->News->config->enable_filters) ?>
				<?php _e('m_news_config_filters_website') ?></label></p>
			</fieldset>

			<fieldset>
				<legend><?php _e('m_news_config_access_restrictions') ?></legend>

				<?php if (!$okt->News->moduleUsersExists()) : ?>
				<p class="note"><?php _e('m_news_config_install_users') ?></p>
				<?php endif; ?>

				<?php if ($okt->News->moduleUsersExists()) : ?>
				<ul class="checklist">
					<?php foreach ($aGroups as $g_id=>$g_title) : ?>
					<li><label for="p_perm_g_<?php echo $g_id ?>"><?php echo form::checkbox(array('p_perms[]', 'p_perm_g_'.$g_id),
					$g_id, in_array($g_id,$okt->News->config->perms)) ?> <?php echo $g_title ?></label></li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>

				<p class="field"><label><?php echo form::checkbox('p_enable_group_perms', 1, $okt->News->config->enable_group_perms, '', '', !$okt->News->moduleUsersExists()) ?>
				<?php _e('m_news_config_enable_group_permissions') ?></label></p>
			</fieldset>

			<fieldset>
				<legend><?php _e('m_news_config_rich_text_editor') ?></legend>

			<?php if ($okt->page->hasRte()) : ?>
				<p class="field"><label for="p_enable_rte"><?php _e('m_news_config_rich_text_editor') ?></label>
				<?php echo form::select('p_enable_rte',array_merge(array(__('c_c_Disabled')=>0), $okt->page->getRteList(true)), $okt->News->config->enable_rte) ?></p>
			<?php else : ?>
				<p><?php _e('m_news_config_no_rich_text_editor') ?>
				<?php echo form::hidden('p_enable_rte',0); ?></p>
			<?php endif;?>
			</fieldset>
		</div><!-- #tab_general -->

		<div id="tab_categories">
			<h3><?php _e('m_news_config_tab_categories') ?></h3>

			<p class="field"><label for="p_categories_enable"><?php echo form::checkbox('p_categories_enable', 1, $okt->News->config->categories['enable']) ?>
			<?php _e('m_news_config_categories_enable') ?></label></p>

			<p class="field"><label for="p_categories_descriptions"><?php echo form::checkbox('p_categories_descriptions', 1, $okt->News->config->categories['descriptions']) ?>
			<?php _e('m_news_config_categories_desc_enable') ?></label></p>

			<?php if ($okt->page->hasRte()) : ?>
				<p class="field"><label for="p_categories_rte"><?php _e('m_news_config_rich_text_editor') ?></label>
				<?php echo form::select('p_categories_rte', array_merge(array(__('c_c_Disabled')=>0), $okt->page->getRteList(true)), $okt->News->config->categories['rte']) ?></p>
			<?php else : ?>
				<p><?php _e('m_news_config_no_rich_text_editor') ?>
				<?php echo form::hidden('p_categories_rte', 0); ?></p>
			<?php endif;?>

		</div><!-- #tab_categories -->

		<div id="tab_tpl">
			<h3><?php _e('m_news_config_tab_tpl_title') ?></h3>

			<h4><?php _e('m_news_config_tpl_list') ?></h4>

			<?php echo $oTemplatesList->getHtmlConfigUsablesTemplates(); ?>

			<h4><?php _e('m_news_config_tpl_item') ?></h4>

			<?php echo $oTemplatesItem->getHtmlConfigUsablesTemplates(); ?>

			<h4><?php _e('m_news_config_tpl_insert') ?></h4>

			<?php echo $oTemplatesInsert->getHtmlConfigUsablesTemplates(false); ?>

			<h4><?php _e('m_news_config_tpl_feed') ?></h4>

			<?php echo $oTemplatesFeed->getHtmlConfigUsablesTemplates(false); ?>

		</div><!-- #tab_tpl -->

		<div id="tab_files">
			<h3><?php _e('m_news_config_tab_attached_files') ?></h3>

			<h4><?php _e('m_news_config_images') ?></h4>

			<?php echo $oImageUploadConfig->getForm(); ?>

			<h4><?php _e('m_news_config_other_files') ?></h4>

			<p class="field"><label><?php echo form::checkbox('p_enable_files',1,$okt->News->config->files['enable']) ?>
			<?php _e('m_news_config_enable_attached_files') ?></label></p>

			<p class="field"><label for="p_number_files"><?php _e('m_news_config_num_attached_files') ?></label>
			<?php echo form::text('p_number_files', 10, 255, $okt->News->config->files['number']) ?></p>

			<p class="field"><label for="p_allowed_exts"><?php _e('m_news_config_extensions_list_allowed') ?></label>
			<?php echo form::text('p_allowed_exts', 60, 255, $okt->News->config->files['allowed_exts']) ?></p>

		</div><!-- #tab_files -->

		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<fieldset>
				<legend><?php _e('c_c_seo_identity_meta') ?></legend>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_<?php echo $aLanguage['code'] ?>">
				<?php $okt->languages->unique ? _e('c_c_seo_module_intitle') : printf(__('c_c_seo_module_intitle_in_%s'), html::escapeHTML($aLanguage['title'])) ?>
				<span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name['.$aLanguage['code'].']','p_name_'.$aLanguage['code']), 60, 255, (isset($okt->News->config->name[$aLanguage['code']]) ? html::escapeHTML($okt->News->config->name[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>">
				<?php $okt->languages->unique ? _e('c_c_seo_module_title_tag') : printf(__('c_c_seo_module_title_tag_in_%s'), html::escapeHTML($aLanguage['title'])) ?>
				<span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($okt->News->config->title[$aLanguage['code']]) ? html::escapeHTML($okt->News->config->title[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>">
				<?php $okt->languages->unique ? _e('c_c_seo_meta_desc') : printf(__('c_c_seo_meta_desc_in_%s'), html::escapeHTML($aLanguage['title'])) ?>
				<span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, (isset($okt->News->config->meta_description[$aLanguage['code']]) ? html::escapeHTML($okt->News->config->meta_description[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_seo_<?php echo $aLanguage['code'] ?>">
				<?php $okt->languages->unique ? _e('c_c_seo_module_title_seo') : printf(__('c_c_seo_module_title_seo_in_%s'), html::escapeHTML($aLanguage['title'])) ?>
				<span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name_seo['.$aLanguage['code'].']','p_name_seo_'.$aLanguage['code']), 60, 255, (isset($okt->News->config->name_seo[$aLanguage['code']]) ? html::escapeHTML($okt->News->config->name_seo[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>">
				<?php $okt->languages->unique ? _e('c_c_seo_meta_keywords') : printf(__('c_c_seo_meta_keywords_in_%s'), html::escapeHTML($aLanguage['title'])) ?>
				<span class="lang-switcher-buttons"></span></label>
				<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 57, 5, (isset($okt->News->config->meta_keywords[$aLanguage['code']]) ? html::escapeHTML($okt->News->config->meta_keywords[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>

			</fieldset>

		</div><!-- #tab_seo -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>
