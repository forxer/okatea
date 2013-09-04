<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Page d'administration d'un menu de navigation
 *
 * @addtogroup Okatea
 *
 */

# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$iMenuId = !empty($_REQUEST['menu_id']) ? intval($_REQUEST['menu_id']) : null;

$rsMenu = $okt->navigation->getMenu($iMenuId);

if (empty($iMenuId) || $rsMenu->isEmpty()) {
	$okt->redirect('configuration.php?action=navigation');
}


/* Traitements
----------------------------------------------------------*/


/* Affichage
----------------------------------------------------------*/

$rsItems = $okt->navigation->getItems(array(
	'menu_id' => $iMenuId,
	'language' => $okt->user->language
));


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
				url: "configuration.php?action=navigation&do=items&menu_id='.$iMenuId.'&ajax_update_order=1",
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
	'id' => 'menu-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> 'configuration.php?action=navigation&amp;do=index',
			'ui-icon' 		=> 'arrowreturnthick-1-w',
		),
		array(
			'permission' 	=> true,
			'title' 		=> __('c_a_config_navigation_add_item'),
			'url' 			=> 'configuration.php?action=navigation&amp;do=item&amp;menu_id='.$iMenuId,
			'ui-icon' 		=> 'plusthick',
		)
	)
));

$okt->page->addGlobalTitle(sprintf(__('c_a_config_navigation_items_%s_menu'), $rsMenu->title));

# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('navigationBtSt'); ?>

<?php if ($rsItems->isEmpty()) : ?>
<p><?php _e('c_a_config_navigation_no_item') ?></p>

<?php else : ?>

<form action="configuration.php" method="post" id="ordering">
	<ul id="sortable" class="ui-sortable">
	<?php $i = 1;
	while ($rsItems->fetch()) : ?>
	<li id="ord_<?php echo $rsItems->id ?>" class="ui-state-default"><label for="p_order_<?php echo $rsItems->id ?>">

		<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>

		<?php echo html::escapeHTML($rsItems->title) ?></label>

		<?php echo form::text(array('p_order['.$rsItems->id.']','p_order_'.$rsItems->id), 5, 10, $i++) ?>

		<?php if ($rsItems->active) : ?>
		- <a href="configuration.php?action=languages&amp;disable=<?php echo $rsItems->id ?>"
		title="<?php printf(__('c_c_action_Disable_%s'),html::escapeHTML($rsItems->title)) ?>"
		class="link_sprite ss_tick"><?php _e('c_c_action_Disable') ?></a>
		<?php else : ?>
		- <a href="configuration.php?action=languages&amp;enable=<?php echo $rsItems->id ?>"
		title="<?php printf(__('c_c_action_Enable_%s'),html::escapeHTML($rsItems->title)) ?>"
		class="link_sprite ss_cross"><?php _e('c_c_action_Enable') ?></a>
		<?php endif; ?>

		- <a href="configuration.php?action=languages&amp;id=<?php echo $rsItems->id ?>"
		title="<?php printf(__('c_c_action_Edit_%s'),html::escapeHTML($rsItems->title)) ?>"
		class="link_sprite ss_pencil"><?php _e('c_c_action_Edit') ?></a>

		- <a href="configuration.php?action=languages&amp;delete=<?php echo $rsItems->id ?>"
		onclick="return window.confirm('<?php echo html::escapeJS(__('c_a_config_l10n_confirm_delete')) ?>')"
		title="<?php printf(__('c_c_action_Delete_%s'),html::escapeHTML($rsItems->title)) ?>"
		class="link_sprite ss_delete"><?php _e('c_c_action_Delete') ?></a>

	</li>
	<?php endwhile; ?>
	</ul>
	<p><?php echo form::hidden('action', 'navigation') ?>
	<?php echo form::hidden('do', 'item'); ?>
	<?php echo form::hidden('menu_id', $iMenuId); ?>
	<?php echo form::hidden('ordered', 1); ?>
	<?php echo form::hidden('order_items', 1); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" id="save_order" value="<?php _e('c_c_action_save_order') ?>" /></p>
	
</form>

<?php endif; ?>

	
<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
