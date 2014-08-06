<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_users'), $view->generateAdminUrl('Users_index'));
$okt->page->addGlobalTitle(__('c_a_menu_users_groups'), $view->generateAdminUrl('Users_groups'));
$okt->page->addGlobalTitle(__('c_a_users_add_group'));

# button set
$okt->page->setButtonset('usersGroups', array(
	'id' => 'users-groups-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_c_action_Go_back'),
			'url' => $view->generateAdminUrl('Users_groups'),
			'ui-icon' => 'arrowreturnthick-1-w'
		)
	)
));

?>

<?php echo $okt->page->getButtonSet('usersGroups'); ?>

<form action="<?php echo $view->generateAdminUrl('Users_groups_add') ?>"
	method="post" id="group-form">

	<?php
	
	echo $view->render('Users/Groups/GroupForm', array(
		'iGroupId' => null,
		'aGroupData' => $aGroupData,
		'aPermissions' => $aPermissions
	))?>

	<p><?php echo $okt->page->formtoken()?>
	<?php echo form::hidden('form_sent', 1)?>
	<input type="submit" value="<?php _e('c_c_action_Add') ?>" />
	</p>
</form>
