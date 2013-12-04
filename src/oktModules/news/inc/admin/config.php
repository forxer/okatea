<?php
/**
 * @ingroup okt_module_news
 * @brief La page de configuration
 *
 */


# Accès direct interdit
if (!defined('ON_NEWS_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
l10n::set(__DIR__.'/../../locales/'.$okt->user->language.'/admin.config');

# Gestion des images
$oImageUploadConfig = new Okatea\Images\ImageUploadConfig($okt,$okt->news->getImageUpload());
$oImageUploadConfig->setBaseUrl('module.php?m=news&amp;action=config&amp;');

# Gestionnaires de templates
$oTemplatesList = new Okatea\Themes\TemplatesSet($okt,
	$okt->news->config->templates['list'],
	'news/list',
	'list',
	'module.php?m=news&amp;action=config&amp;'
);

$oTemplatesItem = new Okatea\Themes\TemplatesSet($okt,
	$okt->news->config->templates['item'],
	'news/item',
	'item',
	'module.php?m=news&amp;action=config&amp;'
);

$oTemplatesInsert = new Okatea\Themes\TemplatesSet($okt,
	$okt->news->config->templates['insert'],
	'news/insert',
	'insert',
	'module.php?m=news&amp;action=config&amp;'
);

$oTemplatesFeed = new Okatea\Themes\TemplatesSet($okt,
	$okt->news->config->templates['feed'],
	'news/feed',
	'feed',
	'module.php?m=news&amp;action=config&amp;'
);


/* Traitements
----------------------------------------------------------*/

# régénération des miniatures
if (!empty($_GET['minregen']))
{
	$okt->news->regenMinImages();

	$okt->page->flashMessages->addSuccess(__('c_c_confirm_thumb_regenerated'));

	http::redirect('module.php?m=news&action=config');
}

# suppression filigrane
if (!empty($_GET['delete_watermark']))
{
	$okt->news->config->write(array('images'=>$oImageUploadConfig->removeWatermak()));

	$okt->page->flashMessages->addSuccess(__('c_c_confirm_watermark_deleted'));

	http::redirect('module.php?m=news&action=config');
}

# enregistrement configuration
if (!empty($_POST['form_sent']))
{
	$p_enable_metas = !empty($_POST['p_enable_metas']) ? true : false;
	$p_enable_filters = !empty($_POST['p_enable_filters']) ? true : false;

	$p_perms = !empty($_POST['p_perms']) && is_array($_POST['p_perms']) ? array_map('intval',$_POST['p_perms']) : array(0);
	$p_enable_group_perms = !empty($_POST['p_enable_group_perms']) ? true : false;

	$p_enable_rte = !empty($_POST['p_enable_rte']) ? $_POST['p_enable_rte'] : '';

	$p_categories_enable = !empty($_POST['p_categories_enable']) ? true : false;
	$p_categories_descriptions = !empty($_POST['p_categories_descriptions']) ? true : false;
	$p_categories_rte = !empty($_POST['p_categories_rte']) ? $_POST['p_categories_rte'] : '';

	$p_tpl_list = $oTemplatesList->getPostConfig();
	$p_tpl_item = $oTemplatesItem->getPostConfig();
	$p_tpl_insert = $oTemplatesInsert->getPostConfig();
	$p_tpl_feed = $oTemplatesFeed->getPostConfig();

	$aImagesConfig = $oImageUploadConfig->getPostConfig();

	$p_enable_files = !empty($_POST['p_enable_files']) ? true : false;
	$p_number_files = !empty($_POST['p_number_files']) ? intval($_POST['p_number_files']) : 0;
	$p_allowed_exts = !empty($_POST['p_allowed_exts']) ? $_POST['p_allowed_exts'] : '';

	$p_name = !empty($_POST['p_name']) && is_array($_POST['p_name'])  ? $_POST['p_name'] : array();
	$p_name_seo = !empty($_POST['p_name_seo']) && is_array($_POST['p_name_seo'])  ? $_POST['p_name_seo'] : array();
	$p_title = !empty($_POST['p_title']) && is_array($_POST['p_title']) ? $_POST['p_title'] : array();
	$p_meta_description = !empty($_POST['p_meta_description']) && is_array($_POST['p_meta_description']) ? $_POST['p_meta_description'] : array();
	$p_meta_keywords = !empty($_POST['p_meta_keywords']) && is_array($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : array();

	$p_public_list_url = !empty($_POST['p_public_list_url']) && is_array($_POST['p_public_list_url']) ? $_POST['p_public_list_url'] : array();

	foreach ($p_public_list_url as $lang=>$url) {
		$p_public_list_url[$lang] = util::formatAppPath($url,false,false);
	}

	$p_public_feed_url = !empty($_POST['p_public_feed_url']) && is_array($_POST['p_public_feed_url']) ? $_POST['p_public_feed_url'] : array();

	foreach ($p_public_feed_url as $lang=>$url) {
		$p_public_feed_url[$lang] = util::formatAppPath($url,false,false);
	}

	$p_public_post_url = !empty($_POST['p_public_post_url']) && is_array($_POST['p_public_post_url']) ? $_POST['p_public_post_url'] : array();

	foreach ($p_public_post_url as $lang=>$url) {
		$p_public_post_url[$lang] = util::formatAppPath($url,false,false);
	}

	foreach ($okt->languages->list as $aLanguage)
	{
		if (substr($p_public_post_url[$aLanguage['code']],0,strlen($p_public_list_url[$aLanguage['code']])) == $p_public_list_url[$aLanguage['code']]) {
			$okt->error->set(__('m_news_config_error_list_url_match_item_url'));
		}
	}

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'enable_metas' => (boolean)$p_enable_metas,
			'enable_filters' => (boolean)$p_enable_filters,

			'perms' => (array)$p_perms,
			'enable_group_perms' => (boolean)$p_enable_group_perms,

			'categories' => array(
				'enable' => (boolean)$p_categories_enable,
				'descriptions' => (boolean)$p_categories_descriptions,
				'rte' => $p_categories_rte
			),

			'enable_rte' => $p_enable_rte,

			'images' => $aImagesConfig,

			'files' => array(
				'enable' => (boolean)$p_enable_files,
				'number' => (integer)$p_number_files,
				'allowed_exts' => $p_allowed_exts
			),

			'templates' => array(
				'list' => $p_tpl_list,
				'item' => $p_tpl_item,
				'insert' => $p_tpl_insert,
				'feed' => $p_tpl_feed
			),

			'name' => $p_name,
			'name_seo' => $p_name_seo,
			'title' => $p_title,
			'meta_description' => $p_meta_description,
			'meta_keywords' => $p_meta_keywords,

			'public_list_url' => $p_public_list_url,
			'public_feed_url' => $p_public_feed_url,
			'public_post_url' => $p_public_post_url
		);

		try
		{
			$okt->news->config->write($new_conf);

			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

			http::redirect('module.php?m=news&action=config');
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

# Liste des groupes pour les permissions
if ($okt->news->moduleUsersExists()) {
	$aGroups = $okt->news->getUsersGroupsForPerms(true,true);
}

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


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post" enctype="multipart/form-data">
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

				<p class="field"><label><?php echo form::checkbox('p_enable_metas', 1, $okt->news->config->enable_metas) ?>
				<?php _e('m_news_config_enable_news_seo') ?></label></p>

				<p class="field"><label><?php echo form::checkbox('p_enable_filters', 1, $okt->news->config->enable_filters) ?>
				<?php _e('m_news_config_filters_website') ?></label></p>
			</fieldset>

			<fieldset>
				<legend><?php _e('m_news_config_access_restrictions') ?></legend>

				<?php if (!$okt->news->moduleUsersExists()) : ?>
				<p class="note"><?php _e('m_news_config_install_users') ?></p>
				<?php endif; ?>

				<?php if ($okt->news->moduleUsersExists()) : ?>
				<ul class="checklist">
					<?php foreach ($aGroups as $g_id=>$g_title) : ?>
					<li><label for="p_perm_g_<?php echo $g_id ?>"><?php echo form::checkbox(array('p_perms[]', 'p_perm_g_'.$g_id),
					$g_id, in_array($g_id,$okt->news->config->perms)) ?> <?php echo $g_title ?></label></li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>

				<p class="field"><label><?php echo form::checkbox('p_enable_group_perms', 1, $okt->news->config->enable_group_perms, '', '', !$okt->news->moduleUsersExists()) ?>
				<?php _e('m_news_config_enable_group_permissions') ?></label></p>
			</fieldset>

			<fieldset>
				<legend><?php _e('m_news_config_rich_text_editor') ?></legend>

			<?php if ($okt->page->hasRte()) : ?>
				<p class="field"><label for="p_enable_rte"><?php _e('m_news_config_rich_text_editor') ?></label>
				<?php echo form::select('p_enable_rte',array_merge(array(__('c_c_Disabled')=>0), $okt->page->getRteList(true)), $okt->news->config->enable_rte) ?></p>
			<?php else : ?>
				<p><?php _e('m_news_config_no_rich_text_editor') ?>
				<?php echo form::hidden('p_enable_rte',0); ?></p>
			<?php endif;?>
			</fieldset>
		</div><!-- #tab_general -->

		<div id="tab_categories">
			<h3><?php _e('m_news_config_tab_categories') ?></h3>

			<p class="field"><label for="p_categories_enable"><?php echo form::checkbox('p_categories_enable', 1, $okt->news->config->categories['enable']) ?>
			<?php _e('m_news_config_categories_enable') ?></label></p>

			<p class="field"><label for="p_categories_descriptions"><?php echo form::checkbox('p_categories_descriptions', 1, $okt->news->config->categories['descriptions']) ?>
			<?php _e('m_news_config_categories_desc_enable') ?></label></p>

			<?php if ($okt->page->hasRte()) : ?>
				<p class="field"><label for="p_categories_rte"><?php _e('m_news_config_rich_text_editor') ?></label>
				<?php echo form::select('p_categories_rte', array_merge(array(__('c_c_Disabled')=>0), $okt->page->getRteList(true)), $okt->news->config->categories['rte']) ?></p>
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

			<p class="field"><label><?php echo form::checkbox('p_enable_files',1,$okt->news->config->files['enable']) ?>
			<?php _e('m_news_config_enable_attached_files') ?></label></p>

			<p class="field"><label for="p_number_files"><?php _e('m_news_config_num_attached_files') ?></label>
			<?php echo form::text('p_number_files', 10, 255, $okt->news->config->files['number']) ?></p>

			<p class="field"><label for="p_allowed_exts"><?php _e('m_news_config_extensions_list_allowed') ?></label>
			<?php echo form::text('p_allowed_exts', 60, 255, $okt->news->config->files['allowed_exts']) ?></p>

		</div><!-- #tab_files -->

		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<fieldset>
				<legend><?php _e('c_c_seo_identity_meta') ?></legend>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_<?php echo $aLanguage['code'] ?>">
				<?php $okt->languages->unique ? _e('c_c_seo_module_intitle') : printf(__('c_c_seo_module_intitle_in_%s'), html::escapeHTML($aLanguage['title'])) ?>
				<span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name['.$aLanguage['code'].']','p_name_'.$aLanguage['code']), 60, 255, (isset($okt->news->config->name[$aLanguage['code']]) ? html::escapeHTML($okt->news->config->name[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>">
				<?php $okt->languages->unique ? _e('c_c_seo_module_title_tag') : printf(__('c_c_seo_module_title_tag_in_%s'), html::escapeHTML($aLanguage['title'])) ?>
				<span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($okt->news->config->title[$aLanguage['code']]) ? html::escapeHTML($okt->news->config->title[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>">
				<?php $okt->languages->unique ? _e('c_c_seo_meta_desc') : printf(__('c_c_seo_meta_desc_in_%s'), html::escapeHTML($aLanguage['title'])) ?>
				<span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, (isset($okt->news->config->meta_description[$aLanguage['code']]) ? html::escapeHTML($okt->news->config->meta_description[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_name_seo_<?php echo $aLanguage['code'] ?>">
				<?php $okt->languages->unique ? _e('c_c_seo_module_title_seo') : printf(__('c_c_seo_module_title_seo_in_%s'), html::escapeHTML($aLanguage['title'])) ?>
				<span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_name_seo['.$aLanguage['code'].']','p_name_seo_'.$aLanguage['code']), 60, 255, (isset($okt->news->config->name_seo[$aLanguage['code']]) ? html::escapeHTML($okt->news->config->name_seo[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>">
				<?php $okt->languages->unique ? _e('c_c_seo_meta_keywords') : printf(__('c_c_seo_meta_keywords_in_%s'), html::escapeHTML($aLanguage['title'])) ?>
				<span class="lang-switcher-buttons"></span></label>
				<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 57, 5, (isset($okt->news->config->meta_keywords[$aLanguage['code']]) ? html::escapeHTML($okt->news->config->meta_keywords[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>

			</fieldset>

			<fieldset>
				<legend><?php _e('c_c_seo_schema_url') ?></legend>

				<?php foreach ($okt->languages->list as $aLanguage) : ?>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_public_list_url_<?php echo $aLanguage['code'] ?>"><?php printf(__('m_news_config_url_posts_list_from_%s'), '<code>'.$okt->config->app_url.($okt->languages->unique ? '' : $aLanguage['code'].'/').'</code>', html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_public_list_url['.$aLanguage['code'].']','p_public_list_url_'.$aLanguage['code']), 60, 255, (isset($okt->news->config->public_list_url[$aLanguage['code']]) ? html::escapeHTML($okt->news->config->public_list_url[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_public_feed_url_<?php echo $aLanguage['code'] ?>"><?php printf(__('m_news_config_url_rss_from_%s'), '<code>'.$okt->config->app_url.($okt->languages->unique ? '' : $aLanguage['code'].'/').'</code>', html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_public_feed_url['.$aLanguage['code'].']','p_public_feed_url_'.$aLanguage['code']), 60, 255, (isset($okt->news->config->public_feed_url[$aLanguage['code']]) ? html::escapeHTML($okt->news->config->public_feed_url[$aLanguage['code']]) : '')) ?></p>

				<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_public_post_url_<?php echo $aLanguage['code'] ?>"><?php printf(__('m_news_config_url_post_from_%s'), '<code>'.$okt->config->app_url.($okt->languages->unique ? '' : $aLanguage['code'].'/').'</code>', html::escapeHTML($aLanguage['title'])) ?><span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_public_post_url['.$aLanguage['code'].']','p_public_post_url_'.$aLanguage['code']), 60, 255, (isset($okt->news->config->public_post_url[$aLanguage['code']]) ? html::escapeHTML($okt->news->config->public_post_url[$aLanguage['code']]) : '')) ?></p>

				<?php endforeach; ?>

			</fieldset>

		</div><!-- #tab_seo -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden(array('m'),'news'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
