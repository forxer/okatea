<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

?>

<form
	action="<?php echo $view->generateUrl($okt->stepper->getCurrentStep()) ?>"
	method="post">

<?php foreach ($okt->languages->list as $aLanguage) : ?>

	<div class="two-cols">
		<p class="col field" lang="<?php echo $aLanguage['code'] ?>">
			<label for="p_title_<?php echo $aLanguage['code'] ?>"
				title="<?php _e('c_c_required_field') ?>" class="required"><?php $okt->languages->unique ? _e('c_a_config_website_title') : printf(__('c_a_config_website_title_in_%s'), $view->escape($aLanguage['title'])); ?><span
				class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($aValues['title'][$aLanguage['code']]) ? $view->escape($aValues['title'][$aLanguage['code']]) : '')) ?></p>

		<p class="col field" lang="<?php echo $aLanguage['code'] ?>">
			<label for="p_desc_<?php echo $aLanguage['code'] ?>"><?php $okt->languages->unique ? _e('c_a_config_website_desc') : printf(__('c_a_config_website_desc_in_%s'), $view->escape($aLanguage['title'])); ?><span
				class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_desc['.$aLanguage['code'].']','p_desc_'.$aLanguage['code']), 60, 255, (isset($aValues['desc'][$aLanguage['code']]) ? $view->escape($aValues['desc'][$aLanguage['code']]) : '')) ?></p>
	</div>

<?php endforeach; ?>

	<div class="two-cols">
		<p class="col field">
			<label for="p_app_path"><?php printf(__('c_a_config_advanced_app_path'), $okt->request->getSchemeAndHttpHost()) ?></label>
		<?php echo form::text('p_app_path', 40, 255, $view->escape($aValues['app_path'])) ?></p>

		<p class="col field">
			<label for="p_domain"><?php _e('c_a_config_advanced_domain') ?></label>
		<?php echo $okt->request->getScheme() ?>://<?php echo form::text('p_domain', 40, 255, $view->escape($aValues['domain'])) ?></p>
	</div>

	<p>
		<input type="submit" value="<?php _e('c_c_next') ?>" /> <input
			type="hidden" name="sended" value="1" />
	</p>
</form>
