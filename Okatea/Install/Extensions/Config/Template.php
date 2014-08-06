<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

# Toggle With Legend
$okt->page->toggleWithLegend('config_advanced_title', 'config_advanced_content');

?>

<form action="<?php echo $view->generateUrl($okt->stepper->getCurrentStep()) ?>" method="post">

	<h3><?php _e('c_a_config_tab_general') ?></h3>

	<?php foreach ($okt['languages']->getList() as $aLanguage) : ?>

	<div class="two-cols">
		<p class="col field" lang="<?php echo $aLanguage['code'] ?>">
			<label for="p_title_<?php echo $aLanguage['code'] ?>"
				title="<?php _e('c_c_required_field') ?>" class="required"><?php $okt['languages']->hasUniqueLanguage() ? _e('c_a_config_website_title') : printf(__('c_a_config_website_title_in_%s'), $view->escape($aLanguage['title'])); ?><span
				class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, (isset($aValues['title'][$aLanguage['code']]) ? $view->escape($aValues['title'][$aLanguage['code']]) : '')) ?></p>

		<p class="col field" lang="<?php echo $aLanguage['code'] ?>">
			<label for="p_desc_<?php echo $aLanguage['code'] ?>"><?php $okt['languages']->hasUniqueLanguage() ? _e('c_a_config_website_desc') : printf(__('c_a_config_website_desc_in_%s'), $view->escape($aLanguage['title'])); ?><span
				class="lang-switcher-buttons"></span></label>
			<?php echo form::text(array('p_desc['.$aLanguage['code'].']','p_desc_'.$aLanguage['code']), 60, 255, (isset($aValues['desc'][$aLanguage['code']]) ? $view->escape($aValues['desc'][$aLanguage['code']]) : '')) ?></p>
	</div>

	<?php endforeach; ?>

	<h3><?php _e('c_a_config_email_config') ?></h3>

	<div id="config_email_content" class="two-cols">
		<p class="col field"><label for="p_email_to" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_email_to') ?></label>
		<?php echo form::text('p_email_to', 60, 255, $view->escape($aValues['email']['to'])) ?></p>

		<p class="col field"><label for="p_email_from" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_email_from') ?></label>
		<?php echo form::text('p_email_from', 60, 255, $view->escape($aValues['email']['from'])) ?></p>
	</div>

	<div class="two-cols">
		<p class="col field"><label for="p_email_name"><?php _e('c_a_config_email_name') ?></label>
		<?php echo form::text('p_email_name', 60, 255, $view->escape($aValues['email']['name'])) ?></p>
	</div>


	<h3 id="config_advanced_title"><?php _e('c_a_config_advanced') ?></h3>

	<div id="config_advanced_content" class="two-cols">
		<p class="col field"><label for="p_app_path"><?php printf(__('c_a_config_advanced_app_path'), $okt['request']->getSchemeAndHttpHost()) ?></label>
		<?php echo form::text('p_app_path', 40, 255, $view->escape($aValues['app_path'])) ?></p>

		<p class="col field"><label for="p_domain"><?php printf(__('c_a_config_advanced_domain'), $okt['request']->getScheme().'://') ?></label>
		<?php echo form::text('p_domain', 40, 255, $view->escape($aValues['domain'])) ?></p>
	</div>

	<p><?php echo form::hidden('sended', 1) ?>
	<input type="submit" value="<?php _e('c_c_next') ?>" /></p>
</form>
