<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

?>

<form id="edit-user-form" action="<?php echo $view->generateUrl('Users_edit', array('user_id' => $aPageData['user']['id'])) ?>" method="post">

	<?php echo $view->render('Users/User/UserForm', array(
		'aPageData'      => $aPageData,
		'aLanguages'     => $aLanguages,
		'aCivilities'    => $aCivilities
	)); ?>

	<div class="three-cols">

	<?php if ($aPageData['bWaitingValidation']) : ?>
		<p class="col"><?php _e('c_a_users_user_in_wait_of_validation') ?>
		<a href="<?php echo $view->generateUrl('Users_edit', array('user_id' => $aPageData['user']['id'])) ?>?validate=1">
		<?php _e('c_a_users_validate_this_user') ?></a></p>
	<?php else : ?>
		<p class="field col"><label for="group_id"><?php _e('c_c_Group') ?></label>
		<?php echo form::select('group_id', $aGroups, $aPageData['user']['group_id']) ?></p>
	<?php endif; ?>

		<p class="field col"><label for="status"><?php echo form::checkbox('status', 1, $aPageData['user']['status'])?>
		<?php _e('c_c_status_Active') ?></label></p>

	</div>

	<p><?php echo form::hidden('form_sent', 1) ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Edit') ?>" /></p>
</form>
