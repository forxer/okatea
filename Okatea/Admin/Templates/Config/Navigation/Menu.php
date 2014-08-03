<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

if (! empty($iMenuId))
{
	$okt->page->addGlobalTitle(__('c_a_config_navigation_edit_menu'));
}
else
{
	$okt->page->addGlobalTitle(__('c_a_config_navigation_add_menu'));
}

# button set
$okt->page->setButtonset('navigationBtSt', array(
	'id' => 'navigation-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_c_action_Go_back'),
			'url' => $view->generateUrl('config_navigation') . '?do=index',
			'ui-icon' => 'arrowreturnthick-1-w'
		)
	)
));

if ($iMenuId)
{
	# bouton add menu
	$okt->page->addButton('navigationBtSt', array(
		'permission' => true,
		'title' => __('c_a_config_navigation_add_menu'),
		'url' => $view->generateUrl('config_navigation') . '?do=menu',
		'ui-icon' => 'plusthick'
	));
	
	# bouton manage items
	$okt->page->addButton('navigationBtSt', array(
		'permission' => true,
		'title' => __('c_a_config_navigation_manage_items'),
		'url' => $view->generateUrl('config_navigation') . '?do=items&amp;menu_id=' . $iMenuId,
		'ui-icon' => 'pencil'
	));
}

?>

<?php echo $okt->page->getButtonSet('navigationBtSt'); ?>

<?php if (!empty($iMenuId)) : ?>
<h3><?php _e('c_a_config_navigation_edit_menu') ?></h3>
<?php else : ?>
<h3><?php _e('c_a_config_navigation_add_menu') ?></h3>
<?php endif; ?>

<form id="menu-form"
	action="<?php echo $view->generateUrl('config_navigation') ?>?do=menu"
	method="post">

	<div class="two-cols">
		<p class="field col">
			<label for="p_title" title="<?php _e('c_c_required_field') ?>"
				class="required"><?php _e('c_a_config_navigation_menu_title') ?></label>
		<?php echo form::text('p_title', 100, 255, $view->escape($aMenuData['title']))?>

		<?php if (!empty($okt['config']['menus']_tpl['usables'])) : ?>
		
		
		
		
		
		
		
		
		
		<p class="field col">
			<label for="p_tpl"><?php _e('c_a_config_navigation_menu_tpl') ?></label>
		<?php echo form::select('p_tpl', $aTplChoices, $aMenuData['tpl'])?></p>
		<?php endif; ?>
	</div>

	<p class="field">
		<label for="p_active"><?php echo form::checkbox('p_active', 1, $aMenuData['active']) ?> <?php _e('c_c_action_visible') ?></label>
	</p>

	<p><?php echo !empty($iMenuId) ? form::hidden('menu_id', $iMenuId) : ''; ?>
	<?php echo form::hidden('sended', 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit"
			value="<?php empty($iMenuId) ? _e('c_c_action_add') : _e('c_c_action_edit'); ?>" />
	</p>
</form>

<?php if (!empty($iMenuId)) : ?>
<div class="note">
	<p><?php _e('c_a_config_navigation_menu_usage') ?></p>
	<p><?php _e('c_a_config_navigation_menu_usage_by_id') ?><br />
		<code><?php echo $view->escape('<?php echo $okt['menus']->render('.$iMenuId.') ?>') ?></code>
	</p>
	<p><?php _e('c_a_config_navigation_menu_usage_by_title') ?><br />
		<code><?php echo $view->escape('<?php echo $okt['menus']->render(\''.$aMenuData['title'].'\') ?>') ?></code>
	</p>
</div>
<?php endif; ?>
