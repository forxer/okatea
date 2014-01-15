<?php

use Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# Module title tag
$okt->page->addTitleTag(__('Development'));

# Start breadcrumb
$okt->page->addAriane(__('Development'), $view->generateUrl('Development_index'));

# Titre de la page
$okt->page->addGlobalTitle(__('m_development_menu_debugbar'));

?>

<form action="<?php echo $view->generateUrl('Development_debugbar') ?>" method="post">

	<fieldset>
		<legend><?php _e('m_development_debugbar_enable') ?></legend>

		<p class="field"><label for="p_admin"><?php echo form::checkbox('p_admin',1,$okt->Development->config->debug_bar['admin']) ?>
		<?php _e('m_development_debugbar_enable_admin') ?></label></p>

		<p class="field"><label for="p_public"><?php echo form::checkbox('p_public',1,$okt->Development->config->debug_bar['public']) ?>
		<?php _e('m_development_debugbar_enable_public') ?></label></p>

	</fieldset>

	<fieldset>
		<legend><?php _e('m_development_debugbar_tabs') ?></legend>

		<p class="field"><label for="p_tabs_super_globales"><?php echo form::checkbox('p_tabs_super_globales',1,$okt->Development->config->debug_bar['tabs']['super_globales']) ?>
		<?php _e('m_development_debugbar_tab_super_globales') ?></label></p>

		<p class="field"><label for="p_tabs_app"><?php echo form::checkbox('p_tabs_app',1,$okt->Development->config->debug_bar['tabs']['app']) ?>
		<?php _e('m_development_debugbar_tab_app') ?></label></p>

		<p class="field"><label for="p_tabs_db"><?php echo form::checkbox('p_tabs_db',1,$okt->Development->config->debug_bar['tabs']['db']) ?>
		<?php _e('m_development_debugbar_tab_db') ?></label></p>

		<p class="field"><label for="p_tabs_tools"><?php echo form::checkbox('p_tabs_tools',1,$okt->Development->config->debug_bar['tabs']['tools']) ?>
		<?php _e('m_development_debugbar_tab_tools') ?></label></p>

	</fieldset>

	<fieldset>
		<legend><?php _e('m_development_debugbar_holmes') ?></legend>

		<p class="field"><label for="p_holmes"><?php echo form::checkbox('p_holmes',1,$okt->Development->config->debug_bar['holmes']) ?>
		<?php _e('m_development_debugbar_holmes_enable') ?></label></p>

	</fieldset>

	<p><?php echo form::hidden(array('form_sent'), 1) ?>
	<?php echo $okt->page->formtoken() ?>
	<input type="submit" name="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

