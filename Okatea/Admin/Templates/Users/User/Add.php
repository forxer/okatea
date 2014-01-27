<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$view->extend('layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_users'), $view->generateUrl('Users_index'));

$okt->page->addGlobalTitle(__('c_a_users_Add_user'));

# button set
$okt->page->setButtonset('users', array(
	'id' => 'users-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title'     => __('c_c_action_Go_back'),
			'url'       => $view->generateUrl('Users_index'),
			'ui-icon'   => 'arrowreturnthick-1-w',
		)
	)
));
?>

<?php echo $okt->page->getButtonSet('users'); ?>

<form id="add-user-form" action="<?php echo $view->generateUrl('Users_add') ?>" method="post">

	<?php echo $view->render('Users/User/form_user', array(
		'userData'       => $userData,
		'aLanguages'     => $aLanguages,
		'aCivilities'    => $aCivilities
	)); ?>

	<?php echo $view->render('Users/User/form_password', array(
		'userData'       => $userData
	)); ?>

	<p><?php echo form::hidden('form_sent', 1) ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Edit') ?>" /></p>
</form>
