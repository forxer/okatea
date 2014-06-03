<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

# Module title tag
$okt->page->addTitleTag($okt->module('News')
	->getTitle());

# Module start breadcrumb
$okt->page->addAriane($okt->module('News')
	->getName(), $view->generateUrl('News_index'));

# Titre de la page
$okt->page->addGlobalTitle(__('m_news_cats_categories'), $view->generateUrl('News_categories'));

# button set
$okt->page->setButtonset('newsCatsBtSt', array(
	'id' => 'news-cats-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('m_news_cats_add_category'),
			'url' => $view->generateUrl('News_category_add'),
			'ui-icon' => 'plusthick'
		)
	)
));
?>

<?php echo $okt->page->getButtonSet('newsCatsBtSt'); ?>

<?php if ($rsCategories->isEmpty()) : ?>

<p><?php _e('m_news_cats_no_cat') ?></p>

<?php else : ?>

<div id="rubriques_lists">
	<?php
	
	$ref_level = $level = $rsCategories->level - 1;
	
	while ($rsCategories->fetch())
	{
		$attr = ' id="rub' . $rsCategories->id . '"';
		
		if (! $rsCategories->active)
		{
			$attr .= ' class="disabled"';
		}
		
		# ouverture niveau
		if ($rsCategories->level > $level)
		{
			echo str_repeat('<ul><li' . $attr . '>', $rsCategories->level - $level);
		}
		# fermeture niveau
		elseif ($rsCategories->level < $level)
		{
			echo str_repeat('</li></ul>', - ($rsCategories->level - $level));
		}
		
		# nouvelle ligne
		if ($rsCategories->level <= $level)
		{
			echo '</li><li' . $attr . '>';
		}
		
		if ($rsCategories->num_posts > 1)
		{
			$sNumPosts = sprintf(__('m_news_cats_%s_posts'), $rsCategories->num_posts);
		}
		elseif ($rsCategories->num_posts == 1)
		{
			$sNumPosts = __('m_news_cats_one_post');
		}
		else
		{
			$sNumPosts = __('m_news_cats_no_post');
		}
		
		if ($rsCategories->num_total > 0)
		{
			$sNumPosts = sprintf(__('m_news_cats_%s_total_%s'), $sNumPosts, $rsCategories->num_total);
		}
		
		if ($rsCategories->num_posts == 0)
		{
			$sDeleteLink = ' - <a href="' . $view->generateUrl('News_categories') . '?delete=' . $rsCategories->id . '" ' . 'class="icon delete" ' . 'onclick="return window.confirm(\'' . $view->escapeJs(__('m_news_cats_delete_confirm')) . '\')">' . __('c_c_action_Delete') . '</a></p>';
		}
		else
		{
			$sDeleteLink = ' - <span class="disabled icon delete"></span>' . __('c_c_action_Delete');
		}
		
		echo '<p><strong>' . $view->escape($rsCategories->title) . '</strong> - ' . $sNumPosts . '</p>';
		
		echo '<p>';
		
		if ($rsCategories->active)
		{
			echo '<a href="' . $view->generateUrl('News_categories') . '?switch_status=' . $rsCategories->id . '" ' . 'class="icon tick">' . __('c_c_action_visible') . '</a>';
		}
		else
		{
			echo '<a href="' . $view->generateUrl('News_categories') . '?switch_status=' . $rsCategories->id . '" ' . 'class="icon cross">' . __('c_c_action_hidden_fem') . '</a>';
		}
		
		echo ' - <a href="' . $view->generateUrl('News_category', array(
			'category_id' => $rsCategories->id
		)) . '" ' . 'title="' . $view->escapeHtmlAttr(sprintf(__('m_news_cats_edit_%s'), $rsCategories->title)) . '" ' . 'class="icon pencil">' . __('c_c_action_Edit') . '</a>';
		
		echo $sDeleteLink . '</p>';
		
		$level = $rsCategories->level;
	}
	
	if ($ref_level - $level < 0)
	{
		echo str_repeat('</li></ul>', - ($ref_level - $level));
	}
	
	?>

</div>
<!-- #rubriques_lists  -->
<?php endif; ?>

