<?php

use Tao\Forms\Statics\FormElements as form;

$this->extend('layout');

# button set
$okt->page->setButtonset('newsCatsBtSt',array(
	'id' => 'news-cats-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> $view->generateUrl('News_categories'),
			'ui-icon' 		=> 'arrowreturnthick-1-w',
		)
	)
));

if ($aCategoryData['cat']['id'])
{
	# bouton add cat
	$okt->page->addButton('newsCatsBtSt', array(
		'permission' => true,
		'title' => __('m_news_cats_add_category'),
		'url' => $view->generateUrl('News_category_add'),
		'ui-icon' => 'plusthick'
	));
			# bouton switch statut
			$okt->page->addButton('newsCatsBtSt', array(
				'permission' 	=> true,
				'title' 		=> ($aCategoryData['cat']['active'] ? __('c_c_status_Online') : __('c_c_status_Offline')),
				'url' 			=> $view->generateUrl('News_category', array('category_id' => $aCategoryData['cat']['id'])).'?switch_status=1',
				'ui-icon' 		=> ($aCategoryData['cat']['active'] ? 'volume-on' : 'volume-off'),
				'active' 		=> $aCategoryData['cat']['active']
			));
			# bouton de suppression
			$okt->page->addButton('newsCatsBtSt', array(
				'permission' 	=> ($aCategoryData['extra']['iNumPosts']== 0),
				'title' 		=> __('c_c_action_Delete'),
				'url' 			=> $view->generateUrl('News_categories').'?delete='.$aCategoryData['cat']['id'],
				'ui-icon' 		=> 'closethick',
				'onclick' 		=> 'return window.confirm(\''.$view->escapeJs(__('m_news_cats_delete_confirm')).'\')'
			));
			# bouton vers la catégorie côté public
			$okt->page->addButton('newsCatsBtSt', array(
				'permission' 	=> ($aCategoryData['cat']['active'] ? true : false),
				'title' 		=> __('c_c_action_Show'),
				'url' 			=> $okt->router->generateFromAdmin('newsCategory', array('slug' => $aCategoryData['locales'][$okt->user->language]['slug']), null, true),
				'ui-icon' 		=> 'extlink'
			));
}


# Module title tag
$okt->page->addTitleTag($okt->News->getTitle());

# Module start breadcrumb
$okt->page->addAriane($okt->News->getName(), $view->generateUrl('News_index'));

# Titre de la page
$okt->page->addGlobalTitle(__('m_news_cats_categories'), $view->generateUrl('News_categories'));

if ($aCategoryData['cat']['id'])
{
	$path = $okt->News->categories->getPath($aCategoryData['cat']['id'], true, $okt->user->language);

	while ($path->fetch()) {
		$okt->page->addGlobalTitle($path->title, $view->generateUrl('News_category', array('category_id' => $path->id)));
	}
}
else {
	$okt->page->addGlobalTitle(__('m_news_cats_add_category'));
}


# Lockable
$okt->page->lockable();

# Tabs
$okt->page->tabs();

# RTE
if ($okt->News->config->categories['descriptions']) {
	$okt->page->applyRte($okt->News->config->categories['rte'],'textarea.richTextEditor');
}

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Sortable JS
if ($aCategoryData['cat']['id'])
{
	$okt->page->js->addReady('
		$("#sortable").sortable({
			placeholder: "ui-state-highlight",
			axis: "y",
			revert: true,
			cursor: "move",
			change: function(event, ui) {
				$("#page,#sortable").css("cursor", "progress");
			},
			update: function(event, ui) {
				var result = $("#sortable").sortable("serialize");

				$.ajax({
					data: result,
					url: "'.$view->generateUrl('News_category', array('category_id' => $aCategoryData['cat']['id'])).'?ajax_update_order=1",
					success: function(data) {
						$("#page").css("cursor", "default");
						$("#sortable").css("cursor", "move");
					},
					error: function(data) {
						$("#page").css("cursor", "default");
						$("#sortable").css("cursor", "move");
					}
				});
			}

		});

		$("#sortable").find("input").hide();
		$("#save_order").hide();
		$("#sortable").css("cursor", "move");
	');
}
?>


<?php echo $okt->page->getButtonSet('newsCatsBtSt'); ?>

<form action="<?php echo !empty($aCategoryData['cat']['id']) ? $view->generateUrl('News_category', array('category_id' => $aCategoryData['cat']['id'])) : $view->generateUrl('News_category_add'); ?>" method="post">

	<div id="tabered">
		<ul>
			<li><a href="#tab_category"><span><?php _e('m_news_cat_category')?></span></a></li>
			<li><a href="#tab_options"><span><?php _e('m_news_cat_options')?></span></a></li>
			<?php if ($okt->News->config->enable_metas) : ?>
			<li><a href="#tab_seo"><span><?php _e('c_c_seo')?></span></a></li>
			<?php endif; ?>
		</ul>
		<div id="tab_category">
			<h3><?php _e('m_news_cat_category_title') ?></h3>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>" title="<?php _e('c_c_required_field') ?>" class="required"><?php $okt->languages->unique ? _e('m_news_cat_title') : printf(__('m_news_cat_title_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, $view->escape($aCategoryData['locales'][$aLanguage['code']]['title'])) ?></p>

			<?php if ($okt->News->config->categories['descriptions']) : ?>
			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_content_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_news_cat_desc') : printf(__('m_news_cat_desc_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::textarea(array('p_content['.$aLanguage['code'].']','p_content_'.$aLanguage['code']), 57, 10, $aCategoryData['locales'][$aLanguage['code']]['content'],'richTextEditor') ?></p>
			<?php endif; ?>

			<?php endforeach; ?>

		</div><!-- #tab_category -->

		<div id="tab_options">
			<h3><?php _e('m_news_cat_options_title') ?></h3>

			<div class="two-cols">
				<p class="field col"><label for="p_parent_id"><?php _e('m_news_cat_parent') ?></label>
				<?php echo form::select('p_parent_id', $aAllowedParents, $aCategoryData['cat']['parent_id']) ?></p>

				<p class="field col"><label><?php echo form::checkbox('p_active', 1, $aCategoryData['cat']['active']) ?> <?php _e('c_c_status_Online') ?></label></p>
			</div>

			<div class="two-cols">
				<?php if (!empty($okt->News->config->templates['list']['usables'])) : ?>
				<p class="field col"><label for="p_tpl"><?php _e('m_news_cat_tpl') ?></label>
				<?php echo form::select('p_tpl', $aTplChoices, $aCategoryData['cat']['tpl'])?></p>
				<?php endif; ?>

				<?php if (!empty($okt->News->config->templates['item']['usables'])) : ?>
				<p class="field col"><label for="p_items_tpl"><?php _e('m_news_cat_items_tpl') ?></label>
				<?php echo form::select('p_items_tpl', $aItemsTplChoices, $aCategoryData['cat']['items_tpl'])?></p>
				<?php endif; ?>
			</div>

		</div><!-- #tab_options -->

		<?php if ($okt->News->config->enable_metas) : ?>
		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_tag_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_title_tag') : printf(__('c_c_seo_title_tag_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title_tag['.$aLanguage['code'].']','p_title_tag_'.$aLanguage['code']), 60, 255, $view->escape($aCategoryData['locales'][$aLanguage['code']]['title_tag'])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_desc') : printf(__('c_c_seo_meta_desc_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, $view->escape($aCategoryData['locales'][$aLanguage['code']]['meta_description'])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_seo_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_title_seo') : printf(__('c_c_seo_title_seo_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title_seo['.$aLanguage['code'].']','p_title_seo_'.$aLanguage['code']), 60, 255, $view->escape($aCategoryData['locales'][$aLanguage['code']]['title_seo'])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_keywords') : printf(__('c_c_seo_meta_keywords_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 58, 5, $view->escape($aCategoryData['locales'][$aLanguage['code']]['meta_keywords'])) ?></p>

			<div class="lockable" lang="<?php echo $aLanguage['code'] ?>">
				<p class="field"><label for="p_slug_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_url') : printf(__('c_c_seo_url_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_slug['.$aLanguage['code'].']','p_slug_'.$aLanguage['code']), 60, 255, $view->escape($aCategoryData['locales'][$aLanguage['code']]['slug'])) ?>
				<span class="lockable-note"><?php _e('c_c_seo_warning_edit_url') ?></span></p>
			</div>

			<?php endforeach; ?>
		</div><!-- #tab_seo -->
		<?php endif; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden('sended', 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php echo !empty($aCategoryData['cat']['id']) ? _e('c_c_action_edit') : _e('c_c_action_add'); ?>" /></p>
</form>


<?php if ($aCategoryData['cat']['id'] && !$aCategoryData['extra']['rsSiblings']->isEmpty()) : ?>
<form action="<?php echo !empty($aCategoryData['cat']['id']) ? $view->generateUrl('News_category', array('category_id' => $aCategoryData['cat']['id'])) : $view->generateUrl('News_category_add'); ?>" method="post">
	<div id="tab_siblings">
		<h3><?php _e('m_news_cat_order_title') ?></h3>

		<ul id="sortable" class="ui-sortable">
		<?php $i = 1;
		while ($aCategoryData['extra']['rsSiblings']->fetch()) : ?>
			<li id="ord_<?php echo $aCategoryData['extra']['rsSiblings']->id; ?>" class="ui-state-default"><label for="order_<?php echo $aCategoryData['extra']['rsSiblings']->id ?>">
			<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
			<?php echo $view->escape($aCategoryData['extra']['rsSiblings']->title) ?></label>
			<?php echo form::text(array('p_order['.$aCategoryData['extra']['rsSiblings']->id.']','p_order_'.$aCategoryData['extra']['rsSiblings']->id), 5, 10, $i++) ?></li>
		<?php endwhile; ?>
		</ul>
	</div><!-- #tab_siblings -->

	<p><?php echo form::hidden('order_categories',1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" id="save_order" value="<?php _e('c_c_action_save_order') ?>" /></p>
</form>
<?php endif; ?>
