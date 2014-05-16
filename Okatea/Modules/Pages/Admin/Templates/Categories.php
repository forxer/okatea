<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('layout');

# Module title tag
$okt->page->addTitleTag($okt->module('Pages')
	->getTitle());

# Start breadcrumb
$okt->page->addAriane($okt->module('Pages')
	->getName(), $view->generateUrl('Pages_index'));

# Titre de la page
$okt->page->addGlobalTitle(__('m_pages_cats_categories'), $view->generateUrl('Pages_categories'));

# button set
$okt->page->setButtonset('pagesCatsBtSt', array(
	'id' => 'pages-cats-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('m_pages_cats_add_category'),
			'url' => $view->generateUrl('Pages_category_add'),
			'ui-icon' => 'plusthick'
		)
	)
));

?>

<?php echo $okt->page->getButtonSet('pagesCatsBtSt'); ?>

<?php if ($rsCategories->isEmpty()) : ?>

<p><?php _e('m_pages_cats_no_cat') ?></p>

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
		
		if ($rsCategories->num_pages > 1)
		{
			$sNumPages = sprintf(__('m_pages_cats_%s_pages'), $rsCategories->num_pages);
		}
		elseif ($rsCategories->num_pages == 1)
		{
			$sNumPages = __('m_pages_cats_one_page');
		}
		else
		{
			$sNumPages = __('m_pages_cats_no_page');
		}
		
		if ($rsCategories->num_total > 0)
		{
			$sNumPages = sprintf(__('m_pages_cats_%s_total_%s'), $sNumPages, $rsCategories->num_total);
		}
		
		if ($rsCategories->num_pages == 0)
		{
			$sDeleteLink = ' - <a href="' . $view->generateUrl('Pages_categories') . '?delete=' . $rsCategories->id . '" ' . 'class="icon delete" ' . 'onclick="return window.confirm(\'' . $view->escapeJs(__('m_pages_cats_delete_confirm')) . '\')">' . __('c_c_action_Delete') . '</a></p>';
		}
		else
		{
			$sDeleteLink = ' - <span class="disabled icon delete"></span>' . __('c_c_action_Delete') . '';
		}
		
		echo '<p><strong>' . $view->escape($rsCategories->title) . '</strong> - ' . $sNumPages . '</p>';
		
		echo '<p>';
		
		if ($rsCategories->active)
		{
			echo '<a href="' . $view->generateUrl('Pages_categories') . '?switch_status=' . $rsCategories->id . '" ' . 'class="icon tick">' . __('c_c_action_visible') . '</a>';
		}
		else
		{
			echo '<a href="' . $view->generateUrl('Pages_categories') . '?switch_status=' . $rsCategories->id . '" ' . 'class="icon cross">' . __('c_c_action_hidden_fem') . '</a>';
		}
		
		echo ' - <a href="' . $view->generateUrl('Pages_category', array(
			'category_id' => $rsCategories->id
		)) . '" ' . 'title="' . $view->escapeHtmlAttr(sprintf(__('m_pages_cats_edit_%s'), $rsCategories->title)) . '" ' . 'class="icon pencil">' . __('c_c_action_Edit') . '</a>';
		
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
