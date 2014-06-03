<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Users\Groups;

$view->extend('Layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_users'), $view->generateUrl('Users_index'));
$okt->page->addGlobalTitle(__('c_a_menu_users_groups'), $view->generateUrl('Users_groups'));
$okt->page->addGlobalTitle(__('c_a_users_edit_group'));

# button set
$okt->page->setButtonset('usersGroups', array(
	'id' => 'users-groups-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_c_action_Go_back'),
			'url' => $view->generateUrl('Users_groups'),
			'ui-icon' => 'arrowreturnthick-1-w'
		),
		array(
			'permission' => true,
			'title' => __('c_a_users_add_group'),
			'url' => $view->generateUrl('Users_groups_add'),
			'ui-icon' => 'plusthick'
		),
		array(
			'permission' => ! in_array($iGroupId, Groups::$native),
			'title' => __('c_c_action_Delete'),
			'url' => $view->generateUrl('Users_groups') . '?delete_id=' . $iGroupId,
			'ui-icon' => 'closethick',
			'onclick' => 'return window.confirm(\'' . $view->escapeJS(__('c_a_users_confirm_group_deletion')) . '\')'
		)
	)
));

?>

<?php echo $okt->page->getButtonSet('usersGroups'); ?>

<form
	action="<?php echo $view->generateUrl('Users_groups_edit', array('group_id' => $iGroupId)) ?>"
	method="post" id="group-form">

	<?php
	
	echo $view->render('Users/Groups/GroupForm', array(
		'iGroupId' => $iGroupId,
		'aGroupData' => $aGroupData,
		'aPermissions' => $aPermissions
	))?>

	<p><?php echo $okt->page->formtoken()?>
	<?php echo form::hidden('form_sent', 1)?>
	<input type="submit" value="<?php _e('c_c_action_Edit') ?>" />
	</p>
</form>
