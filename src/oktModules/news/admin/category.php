<?php
/**
 * @ingroup okt_module_news
 * @brief Page de gestion des rubriques
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;
use Tao\Forms\Statics\SelectOption;
use Tao\Themes\TemplatesSet;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
$okt->l10n->loadFile(__DIR__.'/../locales/'.$okt->user->language.'/admin.categories');

# Récupération de la liste complète des rubriques
$rsCategories = $okt->news->categories->getCategories(array(
	'active' => 2,
	'with_count' => true,
	'language' => $okt->user->language
));

$iCategoryId = null;

$aCategoryData = new ArrayObject();
$aCategoryLocalesData = new ArrayObject();

$aCategoryData['active'] = 1;
$aCategoryData['parent_id'] = 0;
$aCategoryData['tpl'] = '';
$aCategoryData['items_tpl'] = '';

foreach ($okt->languages->list as $aLanguage)
{
	$aCategoryLocalesData[$aLanguage['code']] = array();

	$aCategoryLocalesData[$aLanguage['code']]['title'] = '';
	$aCategoryLocalesData[$aLanguage['code']]['content'] = '';

	if ($okt->news->config->enable_metas)
	{
		$aCategoryLocalesData[$aLanguage['code']]['title_seo'] = '';
		$aCategoryLocalesData[$aLanguage['code']]['title_tag'] = '';
		$aCategoryLocalesData[$aLanguage['code']]['meta_description'] = '';
		$aCategoryLocalesData[$aLanguage['code']]['meta_keywords'] = '';
		$aCategoryLocalesData[$aLanguage['code']]['slug'] = '';
	}
}

# update a category ?
if (!empty($_REQUEST['category_id']))
{
	$iCategoryId = intval($_REQUEST['category_id']);

	$rsCategory = $okt->news->categories->getCategory($iCategoryId);

	if ($rsCategory->isEmpty())
	{
		$okt->error->set(sprintf(__('m_news_cat_%s_not_exists'),$iCategoryId));
		$iCategoryId = null;
	}
	else
	{
		$aCategoryData['active'] = $rsCategory->active;
		$aCategoryData['parent_id'] = $rsCategory->parent_id;
		$aCategoryData['tpl'] = $rsCategory->tpl;
		$aCategoryData['items_tpl'] = $rsCategory->items_tpl;

		$rsCategoryI18n = $okt->news->categories->getCategoryI18n($iCategoryId);

		foreach ($okt->languages->list as $aLanguage)
		{
			while ($rsCategoryI18n->fetch())
			{
				if ($rsCategoryI18n->language == $aLanguage['code'])
				{
					$aCategoryLocalesData[$aLanguage['code']]['title'] = $rsCategoryI18n->title;
					$aCategoryLocalesData[$aLanguage['code']]['content'] = $rsCategoryI18n->content;

					if ($okt->news->config->enable_metas)
					{
						$aCategoryLocalesData[$aLanguage['code']]['title_seo'] = $rsCategoryI18n->title_seo;
						$aCategoryLocalesData[$aLanguage['code']]['title_tag'] = $rsCategoryI18n->title_tag;
						$aCategoryLocalesData[$aLanguage['code']]['meta_description'] = $rsCategoryI18n->meta_description;
						$aCategoryLocalesData[$aLanguage['code']]['meta_keywords'] = $rsCategoryI18n->meta_keywords;
						$aCategoryLocalesData[$aLanguage['code']]['slug'] = $rsCategoryI18n->slug;
					}
				}
			}
		}

		# rubriques voisines
		$rsSiblings = $okt->news->categories->getChildren($rsCategory->parent_id, false, $okt->user->language);

		$iCategoryNumPosts = $rsCategory->num_posts;

		unset($rsCategory,$rsCategoryI18n);
	}
}


/* Traitements
----------------------------------------------------------*/

# AJAX : changement de l'ordre des rubriques voisines
if (!empty($_GET['ajax_update_order']))
{
	$order = !empty($_GET['ord']) && is_array($_GET['ord']) ? $_GET['ord'] : array();

	if (!empty($order))
	{
		try
		{
			foreach ($order as $ord=>$id)
			{
				$ord = ((integer) $ord)+1;
				$okt->news->categories->setCategoryOrder($id,$ord);
			}

			$okt->news->categories->rebuild();
		}
		catch (Exception $e) {
			die($e->getMessage());
		}
	}

	exit();
}

# POST : changement de l'ordre des rubriques voisines
if (!empty($_POST['order_categories']))
{
	$order = !empty($_POST['p_order']) && is_array($_POST['p_order']) ? $_POST['p_order'] : array();

	asort($order);
	$order = array_keys($order);

	if (!empty($order))
	{
		try
		{
			foreach ($order as $ord=>$id)
			{
				$ord = ((integer) $ord)+1;
				$okt->news->categories->setCategoryOrder($id,$ord);
			}

			$okt->news->categories->rebuild();

			http::redirect('module.php?m=news&action=categories&do=edit&category_id='.$iCategoryId.'&ordered=1');
		}
		catch (Exception $e) {
			$okt->error->set($e->getMessage());
		}
	}
}

# switch status
if (!empty($_GET['switch_status']) && !empty($iCategoryId))
{
	try
	{
		$okt->news->categories->switchCategoryStatus($iCategoryId);

		# log admin
		$okt->logAdmin->info(array(
			'code' => 32,
			'component' => 'news',
			'message' => 'category #'.$iCategoryId
		));

		http::redirect('module.php?m=news&action=categories&do=edit&category_id='.$iCategoryId.'&switched=1');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# ajout/modification de la rubrique
if (!empty($_POST['sended']))
{
	$aCategoryData['id'] = $iCategoryId;
	$aCategoryData['active'] = !empty($_POST['p_active']) ? 1 : 0;
	$aCategoryData['parent_id'] = !empty($_POST['p_parent_id']) ? intval($_POST['p_parent_id']) : 0;
	$aCategoryData['tpl'] = !empty($_POST['p_tpl']) ? $_POST['p_tpl'] : null;
	$aCategoryData['items_tpl'] = !empty($_POST['p_items_tpl']) ? $_POST['p_items_tpl'] : null;

	foreach ($okt->languages->list as $aLanguage)
	{
		$aCategoryLocalesData[$aLanguage['code']]['title'] = !empty($_POST['p_title'][$aLanguage['code']]) ? $_POST['p_title'][$aLanguage['code']] : '';

		if ($okt->news->config->categories['descriptions']) {
			$aCategoryLocalesData[$aLanguage['code']]['content'] = !empty($_POST['p_content'][$aLanguage['code']]) ? $_POST['p_content'][$aLanguage['code']] : '';
		}

		if ($okt->news->config->enable_metas)
		{
			$aCategoryLocalesData[$aLanguage['code']]['title_seo'] = !empty($_POST['p_title_seo'][$aLanguage['code']]) ? $_POST['p_title_seo'][$aLanguage['code']] : '';
			$aCategoryLocalesData[$aLanguage['code']]['title_tag'] = !empty($_POST['p_title_tag'][$aLanguage['code']]) ? $_POST['p_title_tag'][$aLanguage['code']] : '';
			$aCategoryLocalesData[$aLanguage['code']]['meta_description'] = !empty($_POST['p_meta_description'][$aLanguage['code']]) ? $_POST['p_meta_description'][$aLanguage['code']] : '';
			$aCategoryLocalesData[$aLanguage['code']]['meta_keywords'] = !empty($_POST['p_meta_keywords'][$aLanguage['code']]) ? $_POST['p_meta_keywords'][$aLanguage['code']] : '';
			$aCategoryLocalesData[$aLanguage['code']]['slug'] = !empty($_POST['p_slug'][$aLanguage['code']]) ? $_POST['p_slug'][$aLanguage['code']] : '';
		}
	}

	# vérification des données avant modification dans la BDD
	if ($okt->news->categories->checkPostData($aCategoryData, $aCategoryLocalesData))
	{
		$oCategoryCursor = $okt->news->categories->openCategoryCursor($aCategoryData);

		# update category
		if (!empty($iCategoryId))
		{
			try
			{
				# -- TRIGGER MODULE NEWS : beforeCategoryUpdate
				$okt->news->triggers->callTrigger('beforeCategoryUpdate', $oCategoryCursor, $aCategoryData, $aCategoryLocalesData);

				$okt->news->categories->updCategory($oCategoryCursor, $aCategoryLocalesData);

				# -- TRIGGER MODULE NEWS : afterCategoryUpdate
				$okt->news->triggers->callTrigger('afterCategoryUpdate', $oCategoryCursor, $aCategoryData, $aCategoryLocalesData);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 41,
					'component' => 'news',
					'message' => 'category #'.$iCategoryId
				));

				$okt->page->flashMessages->addSuccess(__('m_news_cat_updated'));

				http::redirect('module.php?m=news&action=categories&do=edit&category_id='.$iCategoryId);
			}
			catch (Exception $e) {
				$okt->error->set($e->getMessage());
			}

		}

		# add category
		else
		{
			try
			{
				# -- TRIGGER MODULE NEWS : beforeCategoryCreate
				$okt->news->triggers->callTrigger('beforeCategoryCreate', $oCategoryCursor, $aCategoryData, $aCategoryLocalesData);

				$iCategoryId = $okt->news->categories->addCategory($oCategoryCursor, $aCategoryLocalesData);

				# -- TRIGGER MODULE NEWS : afterCategoryCreate
				$okt->news->triggers->callTrigger('afterCategoryCreate', $oCategoryCursor, $aCategoryData, $aCategoryLocalesData);

				# log admin
				$okt->logAdmin->info(array(
					'code' => 40,
					'component' => 'news',
					'message' => 'category #'.$iCategoryId
				));

				$okt->page->flashMessages->addSuccess(__('m_news_cat_added'));

				http::redirect('module.php?m=news&action=categories&do=edit&category_id='.$iCategoryId);
			}
			catch (Exception $e) {
				$okt->error->set($e->getMessage());
			}
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Liste des templates utilisables
$oTemplatesList = new TemplatesSet($okt, $okt->news->config->templates['list'], 'news/list', 'list');
$aTplChoices = array_merge(
	array('&nbsp;' => null),
	$oTemplatesList->getUsablesTemplatesForSelect($okt->news->config->templates['list']['usables'])
);

$oItemsTemplatesList = new TemplatesSet($okt, $okt->news->config->templates['item'], 'news/item', 'item');
$aItemsTplChoices = array_merge(
	array('&nbsp;' => null),
	$oItemsTemplatesList->getUsablesTemplatesForSelect($okt->news->config->templates['item']['usables'])
);

# Calcul de la liste des parents possibles
$aAllowedParents = array(__('m_news_cat_first_level')=>0);

$aChildrens = array();
if ($iCategoryId)
{
	$rsDescendants = $okt->news->categories->getDescendants($iCategoryId,true);

	while ($rsDescendants->fetch()) {
		$aChildrens[] = $rsDescendants->id;
	}
}

while ($rsCategories->fetch())
{
	if (!in_array($rsCategories->id,$aChildrens))
	{
		$aAllowedParents[] = new SelectOption(
			str_repeat('&nbsp;&nbsp;&nbsp;',$rsCategories->level-1).'&bull; '.html::escapeHTML($rsCategories->title),
			$rsCategories->id
		);
	}
}

# button set
$okt->page->setButtonset('newsCatsBtSt',array(
	'id' => 'news-cats-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> 'module.php?m=news&amp;action=categories',
			'ui-icon' 		=> 'arrowreturnthick-1-w',
		)
	)
));

if ($iCategoryId)
{
	# bouton add cat
	$okt->page->addButton('newsCatsBtSt',array(
		'permission' => true,
		'title' => __('m_news_cats_add_category'),
		'url' => 'module.php?m=news&amp;action=categories&amp;do=add',
		'ui-icon' => 'plusthick'
	));
	# bouton switch statut
	$okt->page->addButton('newsCatsBtSt',array(
		'permission' 	=> true,
		'title' 		=> ($aCategoryData['active'] ? __('c_c_status_Online') : __('c_c_status_Offline')),
		'url' 			=> 'module.php?m=news&amp;action=categories&amp;do=edit&amp;switch_status=1&amp;category_id='.$iCategoryId,
		'ui-icon' 		=> ($aCategoryData['active'] ? 'volume-on' : 'volume-off'),
		'active' 		=> $aCategoryData['active'],
	));
	# bouton de suppression
	$okt->page->addButton('newsCatsBtSt',array(
		'permission' 	=> ($iCategoryNumPosts == 0),
		'title' 		=> __('c_c_action_Delete'),
		'url' 			=> 'module.php?m=news&amp;action=categories&amp;delete='.$iCategoryId,
		'ui-icon' 		=> 'closethick',
		'onclick' 		=> 'return window.confirm(\''.html::escapeJS(__('m_news_cats_delete_confirm')).'\')',
	));
	# bouton vers la catégorie côté public
	$okt->page->addButton('newsCatsBtSt',array(
		'permission' 	=> ($aCategoryData['active'] ? true : false),
		'title' 		=> __('c_c_action_Show'),
		'url' 			=> NewsHelpers::getCategoryUrl($aCategoryLocalesData[$okt->user->language]['slug']),
		'ui-icon' 		=> 'extlink'
	));
}

# Titre de la page
$okt->page->addGlobalTitle(__('m_news_cats_categories'),'module.php?m=news&action=categories');

if ($iCategoryId)
{
	$path = $okt->news->categories->getPath($iCategoryId,true,$okt->user->language);

	while ($path->fetch()) {
		$okt->page->addGlobalTitle($path->title,'module.php?m=news&action=categories&do=edit&category_id='.$path->id);
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
if ($okt->news->config->categories['descriptions']) {
	$okt->page->applyRte($okt->news->config->categories['rte'],'textarea.richTextEditor');
}

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered', '.lang-switcher-buttons');
}

# Sortable JS
if ($iCategoryId)
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
					url: "module.php?m=news&action=categories&do=edit&category_id='.$iCategoryId.'&ajax_update_order=1",
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


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('newsCatsBtSt'); ?>

<form action="module.php" method="post">

	<div id="tabered">
		<ul>
			<li><a href="#tab_category"><span><?php _e('m_news_cat_category')?></span></a></li>
			<li><a href="#tab_options"><span><?php _e('m_news_cat_options')?></span></a></li>
			<?php if ($okt->news->config->enable_metas) : ?>
			<li><a href="#tab_seo"><span><?php _e('c_c_seo')?></span></a></li>
			<?php endif; ?>
		</ul>
		<div id="tab_category">
			<h3><?php _e('m_news_cat_category_title') ?></h3>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>" title="<?php _e('c_c_required_field') ?>" class="required"><?php $okt->languages->unique ? _e('m_news_cat_title') : printf(__('m_news_cat_title_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, html::escapeHTML($aCategoryLocalesData[$aLanguage['code']]['title'])) ?></p>

			<?php if ($okt->news->config->categories['descriptions']) : ?>
			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_content_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('m_news_cat_desc') : printf(__('m_news_cat_desc_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::textarea(array('p_content['.$aLanguage['code'].']','p_content_'.$aLanguage['code']), 57, 10, $aCategoryLocalesData[$aLanguage['code']]['content'],'richTextEditor') ?></p>
			<?php endif; ?>

			<?php endforeach; ?>

		</div><!-- #tab_category -->

		<div id="tab_options">
			<h3><?php _e('m_news_cat_options_title') ?></h3>

			<div class="two-cols">
				<p class="field col"><label for="p_parent_id"><?php _e('m_news_cat_parent') ?></label>
				<?php echo form::select('p_parent_id', $aAllowedParents, $aCategoryData['parent_id']) ?></p>

				<p class="field col"><label><?php echo form::checkbox('p_active', 1, $aCategoryData['active']) ?> <?php _e('c_c_status_Online') ?></label></p>
			</div>

			<div class="two-cols">
				<?php if (!empty($okt->news->config->templates['list']['usables'])) : ?>
				<p class="field col"><label for="p_tpl"><?php _e('m_news_cat_tpl') ?></label>
				<?php echo form::select('p_tpl', $aTplChoices, $aCategoryData['tpl'])?></p>
				<?php endif; ?>

				<?php if (!empty($okt->news->config->templates['item']['usables'])) : ?>
				<p class="field col"><label for="p_items_tpl"><?php _e('m_news_cat_items_tpl') ?></label>
				<?php echo form::select('p_items_tpl', $aItemsTplChoices, $aCategoryData['items_tpl'])?></p>
				<?php endif; ?>
			</div>

		</div><!-- #tab_options -->

		<?php if ($okt->news->config->enable_metas) : ?>
		<div id="tab_seo">
			<h3><?php _e('c_c_seo_help') ?></h3>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_tag_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_title_tag') : printf(__('c_c_seo_title_tag_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title_tag['.$aLanguage['code'].']','p_title_tag_'.$aLanguage['code']), 60, 255, html::escapeHTML($aCategoryLocalesData[$aLanguage['code']]['title_tag'])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_description_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_desc') : printf(__('c_c_seo_meta_desc_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_meta_description['.$aLanguage['code'].']','p_meta_description_'.$aLanguage['code']), 60, 255, html::escapeHTML($aCategoryLocalesData[$aLanguage['code']]['meta_description'])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_seo_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_title_seo') : printf(__('c_c_seo_title_seo_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title_seo['.$aLanguage['code'].']','p_title_seo_'.$aLanguage['code']), 60, 255, html::escapeHTML($aCategoryLocalesData[$aLanguage['code']]['title_seo'])) ?></p>

			<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_meta_keywords_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_meta_keywords') : printf(__('c_c_seo_meta_keywords_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
			<?php echo form::textarea(array('p_meta_keywords['.$aLanguage['code'].']','p_meta_keywords_'.$aLanguage['code']), 58, 5, html::escapeHTML($aCategoryLocalesData[$aLanguage['code']]['meta_keywords'])) ?></p>

			<div class="lockable" lang="<?php echo $aLanguage['code'] ?>">
				<p class="field"><label for="p_slug_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_c_seo_url') : printf(__('c_c_seo_url_in_%s'),$aLanguage['title']) ?> <span class="lang-switcher-buttons"></span></label>
				<?php echo form::text(array('p_slug['.$aLanguage['code'].']','p_slug_'.$aLanguage['code']), 60, 255, html::escapeHTML($aCategoryLocalesData[$aLanguage['code']]['slug'])) ?>
				<span class="lockable-note"><?php _e('c_c_seo_warning_edit_url') ?></span></p>
			</div>

			<?php endforeach; ?>
		</div><!-- #tab_seo -->
		<?php endif; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden(array('m'), 'news'); ?>
	<?php echo form::hidden(array('action'), 'categories'); ?>
	<?php echo form::hidden(array('do'), (!empty($iCategoryId) ? 'edit' : 'add')); ?>
	<?php echo !empty($iCategoryId) ? form::hidden('category_id',$iCategoryId) : ''; ?>
	<?php echo form::hidden('sended', 1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php echo !empty($iCategoryId) ? _e('c_c_action_edit') : _e('c_c_action_add'); ?>" /></p>
</form>


<?php if ($iCategoryId && !$rsSiblings->isEmpty()) : ?>
<form action="module.php" method="post">
	<div id="tab_siblings">
		<h3><?php _e('m_news_cat_order_title') ?></h3>

		<ul id="sortable" class="ui-sortable">
		<?php $i = 1;
		while ($rsSiblings->fetch()) : ?>
			<li id="ord_<?php echo $rsSiblings->id; ?>" class="ui-state-default"><label for="order_<?php echo $rsSiblings->id ?>">
			<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
			<?php echo html::escapeHTML($rsSiblings->title) ?></label>
			<?php echo form::text(array('p_order['.$rsSiblings->id.']','p_order_'.$rsSiblings->id), 5, 10, $i++) ?></li>
		<?php endwhile; ?>
		</ul>
	</div><!-- #tab_siblings -->

	<p><?php echo form::hidden(array('m'),'news'); ?>
	<?php echo form::hidden(array('action'),'categories'); ?>
	<?php echo form::hidden(array('do'), 'edit'); ?>
	<?php echo !empty($iCategoryId) ? form::hidden('category_id',$iCategoryId) : ''; ?>
	<?php echo form::hidden('order_categories',1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" id="save_order" value="<?php _e('c_c_action_save_order') ?>" /></p>
</form>
<?php endif; ?>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
