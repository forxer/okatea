<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

?>

<form
	action="<?php echo $view->generateAdminUrl('Users_edit', array('user_id' => $aPageData['user']['id'])) ?>"
	method="post">

	<?php echo $view->render('Users/User/PasswordForm', array(
		'aPageData' => $aPageData
	)); ?>

	<p class="field col">
		<label><?php echo form::checkbox('send_password_mail', 1, 0)?>
	<?php _e('c_a_users_Alert_user_by_email') ?></label>
	</p>

	<p><?php echo form::hidden('change_password', 1)?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Edit') ?>" />
	</p>
</form>

