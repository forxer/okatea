<?php
use Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

$okt->page->css->addCss('
	#sudo_part {
		font-size: 1.1em;
	}
');

?>


<form action="<?php echo $view->generateUrl($okt->stepper->getCurrentStep()) ?>" method="post">

	<div class="two-cols">
		<div class="col" id="sudo_part">
			<h3><?php _e('i_supa_account_sudo') ?></h3>

			<p><?php _e('i_supa_account_sudo_note') ?></p>

			<p class="field"><label for="sudo_username" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_supa_username') ?></label>
			<?php echo form::text('sudo_username', 40, 255, $view->escape($aUsersData['sudo']['username'])) ?></p>

			<p class="field"><label for="sudo_password" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_supa_password') ?></label>
			<?php echo form::text('sudo_password', 40, 255, $view->escape($aUsersData['sudo']['password'])) ?></p>

			<p class="field"><label for="sudo_email" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('i_supa_email') ?></label>
			<?php echo form::text('sudo_email', 40, 255, $view->escape($aUsersData['sudo']['email'])) ?></p>
		</div>

		<div class="col" id="admin_part">
			<h3><?php _e('i_supa_account_admin') ?></h3>

			<p class="note"><?php _e('i_supa_account_admin_note') ?></p>

			<p class="field"><label for="admin_username"><?php _e('i_supa_username') ?></label>
			<?php echo form::text('admin_username', 40, 255, $view->escape($aUsersData['admin']['username'])) ?></p>

			<p class="field"><label for="admin_password"><?php _e('i_supa_password') ?></label>
			<?php echo form::text('admin_password', 40, 255, $view->escape($aUsersData['admin']['password'])) ?></p>

			<p class="field"><label for="admin_email"><?php _e('i_supa_email') ?></label>
			<?php echo form::text('admin_email', 40, 255, $view->escape($aUsersData['admin']['email'])) ?></p>
		</div>
	</div>

	<p><input type="submit" value="<?php _e('c_c_next') ?>" />
	<input type="hidden" name="sended" value="1" /></p>
</form>