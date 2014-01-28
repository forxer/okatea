<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

?>

<fieldset>
	<legend><?php _e('c_c_users_Identity')?></legend>

	<div class="three-cols">
		<p class="field col"><label for="civility"><?php _e('c_c_Civility') ?></label>
		<?php echo form::select('civility', $aCivilities, $aPageData['user']['civility']) ?></p>

		<p class="field col"><label for="lastname"><?php _e('c_c_Last_name') ?></label>
		<?php echo form::text('lastname', 40, 255, html::escapeHTML($aPageData['user']['lastname'])) ?></p>

		<p class="field col"><label for="firstname"><?php _e('c_c_First_name') ?></label>
		<?php echo form::text('firstname', 40, 255, html::escapeHTML($aPageData['user']['firstname'])) ?></p>
	</div>

	<div class="three-cols">
	<?php # affichage des champs "username" et "email" fusionnés
	if ($okt->config->users_registration['merge_username_email']) : ?>
		<p class="field col"><label for="email" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Email') ?></label>
		<?php echo form::text('email', 40, 255, $view->escape($aPageData['user']['email'])) ?></p>
	<?php endif; ?>

	<?php # affichage des champs "username" et "email" distincts
	if (!$okt->config->users_registration['merge_username_email']) : ?>
		<p class="field col"><label for="username" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_user_Username') ?></label>
		<?php echo form::text('username', 40, 255, $view->escape($aPageData['user']['username'])) ?></p>

		<p class="field col"><label for="email" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Email') ?></label>
		<?php echo form::text('email', 40, 255, $view->escape($aPageData['user']['email'])) ?></p>
	<?php endif; ?>

		<p class="field col"><label for="displayname"><?php _e('c_c_user_Display_name') ?></label>
		<?php echo form::text('displayname', 40, 255, html::escapeHTML($aPageData['user']['displayname'])) ?></p>
	</div>
</fieldset>

<fieldset>
	<legend><?php _e('c_a_menu_localization')?></legend>
	<div class="three-cols">
		<p class="field col"><label for="language"><?php _e('c_c_Language') ?></label>
		<?php echo form::select('language', $aLanguages, html::escapeHTML($aPageData['user']['language'])) ?></p>

		<p class="field col"><label for="timezone"><?php _e('c_c_Timezone') ?></label>
		<?php echo form::select('timezone', \dt::getZones(true, true), html::escapeHTML($aPageData['user']['timezone'])) ?></p>
	</div>
</fieldset>
