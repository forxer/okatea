<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$okt->page->css->addFile($okt->options->public_url . '/components/passfield/css/passfield.css');
$okt->page->js->addFile($okt->options->public_url . '/components/passfield/js/locales.js');
$okt->page->js->addFile($okt->options->public_url . '/components/passfield/js/passfield.js');
$okt->page->js->addReady('
	$("#password").passField({ /*options*/ });
');

?>

<fieldset>
	<legend><?php _e('c_c_users_Update_paswword')?></legend>

	<div class="three-cols">
		<p class="field col control-group">
			<label for="password"><?php _e('c_c_user_Password') ?></label>
		<?php echo form::password('password', 40, 255, $view->escape($aPageData['user']['password'])) ?></p>

		<p class="field col">
			<label for="password_confirm"><?php _e('c_c_auth_confirm_password') ?></label>
		<?php echo form::password('password_confirm', 40, 255, $view->escape($aPageData['user']['password_confirm'])) ?></p>
	</div>
</fieldset>
