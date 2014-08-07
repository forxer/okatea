<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

# button set
$okt->page->setButtonset('navigationBtSt', array(
	'id' => 'navigation-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_a_config_navigation_add_menu'),
			'url' => $view->generateAdminUrl('config_navigation') . '?do=menu',
			'ui-icon' => 'plusthick'
		),
		array(
			'permission' => true,
			'title' => __('c_a_config_navigation_config'),
			'url' => $view->generateAdminUrl('config_navigation') . '?do=config',
			'ui-icon' => 'gear'
		)
	)
));
?>

<?php echo $okt->page->getButtonSet('navigationBtSt'); ?>

<?php if (empty($aMenus)) : ?>
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
	<?php $iCountLine = 0;
	foreach ($aMenus as $aMenu) :
		$sTdClass = $iCountLine % 2 == 0 ? 'even' : 'odd';
		$iCountLine++;

		if (!$aMenu['active']) {
			$sTdClass .= ' disabled';
		}
		?>
	<tr>
			<th class="<?php echo $sTdClass ?> fake-td" scope="row"><a
				href="<?php echo $view->generateAdminUrl('config_navigation') ?>?do=menu&amp;menu_id=<?php echo $aMenu['id'] ?>"><?php
				echo $view->escape($aMenu['title'])?></a></th>

			<td class="<?php echo $sTdClass ?> nowrap">
				<ul class="actions">
					<li>
				<?php if ($aMenu['active']) : ?>
				<a
						href="<?php echo $view->generateAdminUrl('config_navigation') ?>?do=index&amp;switch_status=<?php echo $aMenu['id'] ?>"
						title="<?php printf(__('c_c_action_Hide_%s'), $view->escapeHtmlAttr($aMenu['title'])) ?>"
						class="icon tick"><?php _e('c_c_action_visible')?></a>
				<?php else : ?>
				<a
						href="<?php echo $view->generateAdminUrl('config_navigation') ?>?do=index&amp;switch_status=<?php echo $aMenu['id'] ?>"
						title="<?php printf(__('c_c_action_Display_%s'), $view->escapeHtmlAttr($aMenu['title'])) ?>"
						class="icon cross"><?php _e('c_c_action_hidden')?></a>
				<?php endif; ?>
				</li>
					<li><a
						href="<?php echo $view->generateAdminUrl('config_navigation') ?>?do=menu&amp;menu_id=<?php echo $aMenu['id'] ?>"
						title="<?php printf(__('c_c_action_Edit_%s'), $view->escapeHtmlAttr($aMenu['title'])) ?>"
						class="icon pencil"><?php _e('c_c_action_edit')?></a></li>
					<li><a
						href="<?php echo $view->generateAdminUrl('config_navigation') ?>?do=index&amp;delete_menu=<?php echo $aMenu['id'] ?>"
						onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_config_navigation_menu_delete_confirm')) ?>')"
						title="<?php printf(__('c_c_action_Delete_%s'), $view->escapeHtmlAttr($aMenu['title'])) ?>"
						class="icon delete"><?php _e('c_c_action_delete')?></a></li>
				</ul>
			</td>

			<td class="<?php echo $sTdClass ?>">
			<?php if ($aMenu['num_items'] == 0) : ?>
				<p><?php _e('c_a_config_navigation_no_item') ?></p>

			<?php elseif ($aMenu['num_items'] == 1) : ?>
				<p><?php _e('c_a_config_navigation_one_item') ?></p>

			<?php elseif ($aMenu['num_items'] > 1) : ?>
				<p><?php echo sprintf(__('c_a_config_navigation_%s_items'), $aMenu['num_items']) ?></p>
			<?php endif; ?>

			<?php if (isset($aMenu['items']) && !$aMenu['items']->isEmpty()) : ?>
			<ul>
				<?php while ($aMenu['items']->fetch()) : ?>
				<li><?php echo $view->escape($aMenu['items']->title) ?></li>
				<?php endwhile; ?>
			</ul>
			<?php endif; ?>
		</td>

			<td class="<?php echo $sTdClass ?>">
				<ul class="actions">
					<li><a
						href="<?php echo $view->generateAdminUrl('config_navigation') ?>?do=items&amp;menu_id=<?php echo $aMenu['id'] ?>"
						title="<?php printf(__('c_a_config_navigation_manage_items_menu_%s'), $view->escapeHtmlAttr($aMenu['title'])) ?>"
						class="icon application_view_list"><?php _e('c_a_config_navigation_manage_items')?></a>
					</li>
					<li><a
						href="<?php echo $view->generateAdminUrl('config_navigation') ?>?do=item&amp;menu_id=<?php echo $aMenu['id'] ?>"
						title="<?php printf(__('c_a_config_navigation_add_item_to_%s'), $view->escapeHtmlAttr($aMenu['title'])) ?>"
						class="icon application_add"><?php _e('c_a_config_navigation_add_item')?></a>
					</li>
				</ul>
			</td>
		</tr>
	<?php endwhile; ?>
	</tbody>
</table>
<?php endif; ?>
