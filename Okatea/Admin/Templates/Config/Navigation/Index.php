<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

$rsMenus = $okt->navigation->getMenus(array(
	'active' => 2
));

while ($rsMenus->fetch())
{
	if ($rsMenus->num_items > 0)
	{
		$rsMenus->items = $okt->navigation->getItems(array(
			'menu_id' => $rsMenus->id,
			'language' => $okt['visitor']->language,
			'active' => 2
		));
	}
}

# button set
$okt->page->setButtonset('navigationBtSt', array(
	'id' => 'navigation-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_a_config_navigation_add_menu'),
			'url' => $view->generateUrl('config_navigation') . '?do=menu',
			'ui-icon' => 'plusthick'
		),
		array(
			'permission' => true,
			'title' => __('c_a_config_navigation_config'),
			'url' => $view->generateUrl('config_navigation') . '?do=config',
			'ui-icon' => 'gear'
		)
	)
));
?>

<?php echo $okt->page->getButtonSet('navigationBtSt'); ?>

<?php if ($rsMenus->isEmpty()) : ?>
<p><?php _e('c_a_config_navigation_no_menu') ?></p>

<?php else : ?>

<table class="common">
	<caption><?php _e('c_a_config_navigation_menus_list') ?></caption>
	<thead>
		<tr>
			<th scope="col"><?php _e('c_a_config_navigation_menu_title') ?></th>
			<th scope="col"><?php _e('c_a_config_navigation_menu_actions') ?></th>
			<th scope="col"><?php _e('c_a_config_navigation_menu_items') ?></th>
			<th scope="col"><?php _e('c_a_config_navigation_menu_items_actions') ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	
	$count_line = 0;
	while ($rsMenus->fetch())
	:
		$td_class = $count_line % 2 == 0 ? 'even' : 'odd';
		$count_line ++;
		
		if (! $rsMenus->active)
		{
			$td_class = ' disabled';
		}
		?>
	<tr>
			<th class="<?php echo $td_class ?> fake-td" scope="row"><a
				href="<?php echo $view->generateUrl('config_navigation') ?>?do=menu&amp;menu_id=<?php echo $rsMenus->id ?>"><?php
		echo $view->escape($rsMenus->title)?></a></th>

			<td class="<?php echo $td_class ?> nowrap">
				<ul class="actions">
					<li>
				<?php if ($rsMenus->active) : ?>
				<a
						href="<?php echo $view->generateUrl('config_navigation') ?>?do=index&amp;switch_status=<?php echo $rsMenus->id ?>"
						title="<?php printf(__('c_c_action_Hide_%s'), $view->escapeHtmlAttr($rsMenus->title)) ?>"
						class="icon tick"><?php _e('c_c_action_visible')?></a>
				<?php else : ?>
				<a
						href="<?php echo $view->generateUrl('config_navigation') ?>?do=index&amp;switch_status=<?php echo $rsMenus->id ?>"
						title="<?php printf(__('c_c_action_Display_%s'), $view->escapeHtmlAttr($rsMenus->title)) ?>"
						class="icon cross"><?php _e('c_c_action_hidden')?></a>
				<?php endif; ?>
				</li>
					<li><a
						href="<?php echo $view->generateUrl('config_navigation') ?>?do=menu&amp;menu_id=<?php echo $rsMenus->id ?>"
						title="<?php printf(__('c_c_action_Edit_%s'), $view->escapeHtmlAttr($rsMenus->title)) ?>"
						class="icon pencil"><?php _e('c_c_action_edit')?></a></li>
					<li><a
						href="<?php echo $view->generateUrl('config_navigation') ?>?do=index&amp;delete_menu=<?php echo $rsMenus->id ?>"
						onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_config_navigation_menu_delete_confirm')) ?>')"
						title="<?php printf(__('c_c_action_Delete_%s'), $view->escapeHtmlAttr($rsMenus->title)) ?>"
						class="icon delete"><?php _e('c_c_action_delete')?></a></li>
				</ul>
			</td>

			<td class="<?php echo $td_class ?>">
			<?php if ($rsMenus->num_items == 0) : ?>
				<p><?php _e('c_a_config_navigation_no_item') ?></p>

			<?php elseif ($rsMenus->num_items == 1) : ?>
				<p><?php _e('c_a_config_navigation_one_item') ?></p>

			<?php elseif ($rsMenus->num_items > 1) : ?>
				<p><?php echo sprintf(__('c_a_config_navigation_%s_items'), $rsMenus->num_items) ?></p>
			<?php endif; ?>

			<?php if (isset($rsMenus->items) && !$rsMenus->items->isEmpty()) : ?>
			<ul>
				<?php while ($rsMenus->items->fetch()) : ?>
				<li><?php echo $view->escape($rsMenus->items->title) ?></li>
				<?php endwhile; ?>
			</ul>
			<?php endif; ?>
		</td>

			<td class="<?php echo $td_class ?>">
				<ul class="actions">
					<li><a
						href="<?php echo $view->generateUrl('config_navigation') ?>?do=items&amp;menu_id=<?php echo $rsMenus->id ?>"
						title="<?php printf(__('c_a_config_navigation_manage_items_menu_%s'), $view->escapeHtmlAttr($rsMenus->title)) ?>"
						class="icon application_view_list"><?php _e('c_a_config_navigation_manage_items')?></a>
					</li>
					<li><a
						href="<?php echo $view->generateUrl('config_navigation') ?>?do=item&amp;menu_id=<?php echo $rsMenus->id ?>"
						title="<?php printf(__('c_a_config_navigation_add_item_to_%s'), $view->escapeHtmlAttr($rsMenus->title)) ?>"
						class="icon application_add"><?php _e('c_a_config_navigation_add_item')?></a>
					</li>
				</ul>
			</td>
		</tr>
	<?php endwhile; ?>
	</tbody>
</table>
<?php endif; ?>
