<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

?>

<form action="<?php echo $view->generateUrl($okt->stepper->getCurrentStep()) ?>" method="post">

	<div class="two-cols">
		<p class="col field">
			<label for="p_app_path"><?php printf(__('c_a_config_advanced_app_path'), $okt->request->getSchemeAndHttpHost()) ?></label>
		<?php echo form::text('p_app_path', 40, 255, $view->escape($aValues['app_path'])) ?></p>

		<p class="col field">
			<label for="p_domain"><?php _e('c_a_config_advanced_domain') ?></label>
		<?php echo $okt->request->getScheme() ?>://<?php echo form::text('p_domain', 40, 255, $view->escape($aValues['domain'])) ?></p>
	</div>

	<p>
		<input type="submit" value="<?php _e('c_c_next') ?>" />
		<input type="hidden" name="sended" value="1" />
	</p>
</form>
