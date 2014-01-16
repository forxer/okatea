<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# Sortable
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
				url: "'.$view->generateUrl('config_navigation').'?do=items&menu_id='.$iMenuId.'&ajax_update_order=1",
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

# button set
$okt->page->setButtonset('navigationBtSt', array(
	'id' => 'navigation-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> $view->generateUrl('config_navigation').'?do=index',
			'ui-icon' 		=> 'arrowreturnthick-1-w'
		),
		array(
			'permission' 	=> true,
			'title' 		=> __('c_a_config_navigation_add_item'),
			'url' 			=> $view->generateUrl('config_navigation').'?do=item&amp;menu_id='.$iMenuId,
			'ui-icon' 		=> 'plusthick'
		)
	)
));

$okt->page->addGlobalTitle(sprintf(__('c_a_config_navigation_items_%s_menu'), $rsMenu->title));

?>

<?php echo $okt->page->getButtonSet('navigationBtSt'); ?>

<?php if ($rsItems->isEmpty()) : ?>
<p><?php _e('c_a_config_navigation_no_item') ?></p>

<?php else : ?>

<form action="<?php echo $view->generateUrl('config_navigation') ?>" method="post" id="ordering">
	<ul id="sortable" class="ui-sortable">
	<?php $i = 1;
	while ($rsItems->fetch()) : ?>
	<li id="ord_<?php echo $rsItems->id ?>" class="ui-state-default"><label for="p_order_<?php echo $rsItems->id ?>">

		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>

		<?php echo $view->escape($rsItems->title) ?></label>

		<?php echo form::text(array('p_order['.$rsItems->id.']','p_order_'.$rsItems->id), 5, 10, $i++) ?>

		<?php if ($rsItems->active) : ?>
		- <a href="<?php echo $view->generateUrl('config_navigation') ?>?do=items&amp;menu_id=<?php echo $iMenuId ?>&amp;disable=<?php echo $rsItems->id ?>"
		title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Disable_%s'), $rsItems->title)) ?>"
		class="icon tick"><?php _e('c_c_action_Disable') ?></a>
		<?php else : ?>
		- <a href="<?php echo $view->generateUrl('config_navigation') ?>?do=items&amp;menu_id=<?php echo $iMenuId ?>&amp;enable=<?php echo $rsItems->id ?>"
		title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Enable_%s'), $rsItems->title)) ?>"
		class="icon cross"><?php _e('c_c_action_Enable') ?></a>
		<?php endif; ?>

		- <a href="<?php echo $view->generateUrl('config_navigation') ?>?do=item&amp;menu_id=<?php echo $iMenuId ?>&amp;item_id=<?php echo $rsItems->id ?>"
		title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Edit_%s'), $rsItems->title)) ?>"
		class="icon pencil"><?php _e('c_c_action_Edit') ?></a>

		- <a href="<?php echo $view->generateUrl('config_navigation') ?>?do=items&amp;menu_id=<?php echo $iMenuId ?>&amp;delete=<?php echo $rsItems->id ?>"
		onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_config_navigation_item_delete_confirm')) ?>')"
		title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_c_action_Delete_%s'), $rsItems->title)) ?>"
		class="icon delete"><?php _e('c_c_action_Delete') ?></a>

	</li>
	<?php endwhile; ?>
	</ul>
	<p><?php echo form::hidden('do', 'items'); ?>
	<?php echo form::hidden('menu_id', $iMenuId); ?>
	<?php echo form::hidden('ordered', 1); ?>
	<?php echo form::hidden('order_items', 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" id="save_order" value="<?php _e('c_c_action_save_order') ?>" /></p>

</form>

<?php endif; ?>
