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
$okt->page->addGlobalTitle(__('c_a_menu_users'), $view->generateUrl('Users_index'));

$okt->page->addGlobalTitle(__('c_a_users_Add_user'));

# button set
$okt->page->setButtonset('users', array(
	'id' => 'users-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_c_action_Go_back'),
			'url' => $view->generateUrl('Users_index'),
			'ui-icon' => 'arrowreturnthick-1-w'
		)
	)
));

$okt->page->css->addFile($okt['public_url'] . '/components/passfield/css/passfield.css');
$okt->page->js->addFile($okt['public_url'] . '/components/passfield/js/locales.js');
$okt->page->js->addFile($okt['public_url'] . '/components/passfield/js/passfield.js');
$okt->page->js->addReady('
	$("#password").passField({ /*options*/ });
');

?>

<?php echo $okt->page->getButtonSet('users'); ?>

<form id="add-user-form"
	action="<?php echo $view->generateUrl('Users_add') ?>" method="post">

	<?php
	echo $view->render('Users/User/UserForm', array(
		'aPageData' => $aPageData,
		'aLanguages' => $aLanguages,
		'aCivilities' => $aCivilities
	));
	?>

<fieldset>
		<legend><?php _e('c_c_user_Password')?></legend>

		<div class="three-cols">

			<p class="field col control-group">
				<label for="password" title="<?php _e('c_c_required_field') ?>"
					class="required"><?php _e('c_c_user_Password') ?></label>
		<?php echo form::password('password', 40, 255, $view->escape($aPageData['user']['password'])) ?></p>

			<p class="field col">
				<label for="password_confirm"
					title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_auth_confirm_password') ?></label>
		<?php echo form::password('password_confirm', 40, 255, $view->escape($aPageData['user']['password_confirm'])) ?></p>

		</div>
	</fieldset>

	<p><?php echo form::hidden('form_sent', 1)?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Add') ?>" />
	</p>
</form>
