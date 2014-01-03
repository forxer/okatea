<?php
/**
 * @ingroup okt_module_news
 * @brief Page de gestion des rubriques
 *
 */

use Tao\Misc\Utilities as util;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
$okt->l10n->loadFile(__DIR__.'/../locales/'.$okt->user->language.'/admin.categories');

# Récupération de la liste complète des rubriques
$rsCategories = $okt->News->categories->getCategories(array(
	'active' => 2,
	'with_count' => true,
	'language' => $okt->user->language
));


/* Traitements
----------------------------------------------------------*/

# switch statut
if (!empty($_GET['switch_status']))
{
	try
	{
		$okt->News->categories->switchCategoryStatus($_GET['switch_status']);

		# log admin
		$okt->logAdmin->info(array(
			'code' => 32,
			'component' => 'news',
			'message' => 'category #'.$_GET['switch_status']
		));

		http::redirect('module.php?m=news&action=categories&switched=1');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# suppression d'une rubrique
if (!empty($_GET['delete']))
{
	try
	{
		$okt->News->categories->delCategory(intval($_GET['delete']));

		# log admin
		$okt->logAdmin->warning(array(
			'code' => 42,
			'component' => 'news',
			'message' => 'category #'.$_GET['delete']
		));

		$okt->page->flash->success(__('m_news_cat_deleted'));

		http::redirect('module.php?m=news&action=categories');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_news_cats_categories'),'module.php?m=news&action=categories');

# button set
$okt->page->setButtonset('newsCatsBtSt',array(
	'id' => 'news-cats-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('m_news_cats_add_category'),
			'url' => 'module.php?m=news&amp;action=categories&amp;do=add',
			'ui-icon' => 'plusthick'
		)
	)
));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('newsCatsBtSt'); ?>

<?php if ($rsCategories->isEmpty()) : ?>

<p><?php _e('m_news_cats_no_cat') ?></p>

<?php else : ?>

<div id="rubriques_lists">
	<?php

	$ref_level = $level = $rsCategories->level-1;

	while ($rsCategories->fetch())
	{
		$attr = ' id="rub'.$rsCategories->id.'"';

		if (!$rsCategories->active) {
			$attr .= ' class="disabled"';
		}

		# ouverture niveau
		if ($rsCategories->level > $level) {
			echo str_repeat('<ul><li'.$attr.'>', $rsCategories->level - $level);
		}
		# fermeture niveau
		elseif ($rsCategories->level < $level) {
			echo str_repeat('</li></ul>', -($rsCategories->level - $level));
		}

		# nouvelle ligne
		if ($rsCategories->level <= $level) {
			echo '</li><li'.$attr.'>';
		}


		if ($rsCategories->num_posts > 1) {
			$sNumPosts = sprintf(__('m_news_cats_%s_posts'),$rsCategories->num_posts);
		}
		elseif ($rsCategories->num_posts == 1) {
			$sNumPosts = __('m_news_cats_one_post');
		}
		else {
			$sNumPosts = __('m_news_cats_no_post');
		}

		if ($rsCategories->num_total > 0) {
			$sNumPosts = sprintf(__('m_news_cats_%s_total_%s'), $sNumPosts, $rsCategories->num_total);
		}

		if ($rsCategories->num_posts == 0)
		{
			$sDeleteLink = ' - <a href="module.php?m=news&amp;action=categories&amp;delete='.$rsCategories->id.'" '.
			'class="icon delete" '.
			'onclick="return window.confirm(\''.html::escapeJS(__('m_news_cats_delete_confirm')).'\')">'.
			__('c_c_action_Delete').'</a></p>';
		}
		else {
			$sDeleteLink = ' - <span class="disabled icon delete"></span>'.__('c_c_action_Delete');
		}

		echo '<p><strong>'.html::escapeHTML($rsCategories->title).'</strong> - '.$sNumPosts.'</p>';

		echo '<p>';

		if ($rsCategories->active)
		{
			echo '<a href="module.php?m=news&amp;action=categories&amp;switch_status='.$rsCategories->id.'" '.
			'class="icon tick">'.__('c_c_action_visible').'</a>';
		}
		else {
			echo '<a href="module.php?m=news&amp;action=categories&amp;switch_status='.$rsCategories->id.'" '.
			'class="icon cross">'.__('c_c_action_hidden_fem').'</a>';
		}

		echo
		' - <a href="module.php?m=news&amp;action=categories&amp;do=edit&amp;category_id='.$rsCategories->id.'" '.
		'title="'.util::escapeAttrHTML(sprintf(__('m_news_cats_edit_%s'), $rsCategories->title)).'" '.
		'class="icon pencil">'.__('c_c_action_Edit').'</a>';

		echo $sDeleteLink.'</p>';

		$level = $rsCategories->level;
	}

	if ($ref_level - $level < 0) {
		echo str_repeat('</li></ul>', -($ref_level - $level));
	}

	?>

</div><!-- #rubriques_lists  -->
<?php endif; ?>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
