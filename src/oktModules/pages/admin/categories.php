<?php
/**
 * @ingroup okt_module_pages
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
$rsCategories = $okt->pages->categories->getCategories(array(
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
		$okt->pages->categories->switchCategoryStatus($_GET['switch_status']);

		# log admin
		$okt->logAdmin->info(array(
			'code' => 32,
			'component' => 'pages',
			'message' => 'category #'.$_GET['switch_status']
		));

		http::redirect('module.php?m=pages&action=categories&switched=1');
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
		$okt->pages->categories->delCategory(intval($_GET['delete']));

		# log admin
		$okt->logAdmin->warning(array(
			'code' => 42,
			'component' => 'pages',
			'message' => 'category #'.$_GET['delete']
		));

		$okt->page->flashMessages->addSuccess(__('m_pages_cat_deleted'));

		http::redirect('module.php?m=pages&action=categories');
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_pages_cats_categories'),'module.php?m=pages&action=categories');

# button set
$okt->page->setButtonset('pagesCatsBtSt',array(
	'id' => 'pages-cats-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('m_pages_cats_add_category'),
			'url' => 'module.php?m=pages&amp;action=categories&amp;do=add',
			'ui-icon' => 'plusthick'
		)
	)
));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('pagesCatsBtSt'); ?>

<?php if ($rsCategories->isEmpty()) : ?>

<p><?php _e('m_pages_cats_no_cat') ?></p>

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


		if ($rsCategories->num_pages > 1) {
			$sNumPages = sprintf(__('m_pages_cats_%s_pages'),$rsCategories->num_pages);
		}
		elseif ($rsCategories->num_pages == 1) {
			$sNumPages = __('m_pages_cats_one_page');
		}
		else {
			$sNumPages = __('m_pages_cats_no_page');
		}

		if ($rsCategories->num_total > 0) {
			$sNumPages = sprintf(__('m_pages_cats_%s_total_%s'), $sNumPages, $rsCategories->num_total);
		}

		if ($rsCategories->num_pages == 0)
		{
			$sDeleteLink = ' - <a href="module.php?m=pages&amp;action=categories&amp;delete='.$rsCategories->id.'" '.
			'class="icon delete" '.
			'onclick="return window.confirm(\''.html::escapeJS(__('m_pages_cats_delete_confirm')).'\')">'.
			__('c_c_action_Delete').'</a></p>';
		}
		else {
			$sDeleteLink = ' - <span class="disabled icon delete">'.__('c_c_action_Delete').'</span>';
		}

		echo '<p><strong>'.html::escapeHTML($rsCategories->title).'</strong> - '.$sNumPages.'</p>';

		echo '<p>';

		if ($rsCategories->active)
		{
			echo '<a href="module.php?m=pages&amp;action=categories&amp;switch_status='.$rsCategories->id.'" '.
			'class="icon tick">'.__('c_c_action_visible').'</a>';
		}
		else {
			echo '<a href="module.php?m=pages&amp;action=categories&amp;switch_status='.$rsCategories->id.'" '.
			'class="icon cross">'.__('c_c_action_hidden_fem').'</a>';
		}

		echo
		' - <a href="module.php?m=pages&amp;action=categories&amp;do=edit&amp;category_id='.$rsCategories->id.'" '.
		'title="'.util::escapeAttrHTML(sprintf(__('m_pages_cats_edit_%s'), $rsCategories->title)).'" '.
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
