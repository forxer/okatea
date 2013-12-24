<?php
/**
 * @ingroup okt_module_development
 * @brief Page de gestion de la debug barre
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_admin = !empty($_POST['p_admin']) ? true : false;
	$p_public = !empty($_POST['p_public']) ? true : false;

	$p_tabs_super_globales = !empty($_POST['p_tabs_super_globales']) ? true : false;
	$p_tabs_app = !empty($_POST['p_tabs_app']) ? true : false;
	$p_tabs_db = !empty($_POST['p_tabs_db']) ? true : false;
	$p_tabs_tools = !empty($_POST['p_tabs_tools']) ? true : false;

	$p_holmes = !empty($_POST['p_holmes']) ? true : false;

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'debug_bar' => array(
				'admin' => (boolean)$p_admin,
				'public' => (boolean)$p_public,
				'tabs' => array(
					'super_globales' => (boolean)$p_tabs_super_globales,
					'app' => (boolean)$p_tabs_app,
					'db' => (boolean)$p_tabs_db,
					'tools' => (boolean)$p_tabs_tools
				),
				'holmes' => (boolean)$p_holmes
			)
		);

		try
		{
			$okt->development->config->write($new_conf);

			$okt->page->flash->success(__('c_c_confirm_configuration_updated'));

			http::redirect('module.php?m=development&action=debug_bar');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_development_menu_debugbar'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">

	<fieldset>
		<legend><?php _e('m_development_debugbar_enable') ?></legend>

		<p class="field"><label for="p_admin"><?php echo form::checkbox('p_admin',1,$okt->development->config->debug_bar['admin']) ?>
		<?php _e('m_development_debugbar_enable_admin') ?></label></p>

		<p class="field"><label for="p_public"><?php echo form::checkbox('p_public',1,$okt->development->config->debug_bar['public']) ?>
		<?php _e('m_development_debugbar_enable_public') ?></label></p>

	</fieldset>

	<fieldset>
		<legend><?php _e('m_development_debugbar_tabs') ?></legend>

		<p class="field"><label for="p_tabs_super_globales"><?php echo form::checkbox('p_tabs_super_globales',1,$okt->development->config->debug_bar['tabs']['super_globales']) ?>
		<?php _e('m_development_debugbar_tab_super_globales') ?></label></p>

		<p class="field"><label for="p_tabs_app"><?php echo form::checkbox('p_tabs_app',1,$okt->development->config->debug_bar['tabs']['app']) ?>
		<?php _e('m_development_debugbar_tab_app') ?></label></p>

		<p class="field"><label for="p_tabs_db"><?php echo form::checkbox('p_tabs_db',1,$okt->development->config->debug_bar['tabs']['db']) ?>
		<?php _e('m_development_debugbar_tab_db') ?></label></p>

		<p class="field"><label for="p_tabs_tools"><?php echo form::checkbox('p_tabs_tools',1,$okt->development->config->debug_bar['tabs']['tools']) ?>
		<?php _e('m_development_debugbar_tab_tools') ?></label></p>

	</fieldset>

	<fieldset>
		<legend><?php _e('m_development_debugbar_holmes') ?></legend>

		<p class="field"><label for="p_holmes"><?php echo form::checkbox('p_holmes',1,$okt->development->config->debug_bar['holmes']) ?>
		<?php _e('m_development_debugbar_holmes_enable') ?></label></p>

	</fieldset>

	<p><?php echo form::hidden(array('m'),'development') ?>
	<?php echo form::hidden(array('action'), 'debug_bar') ?>
	<?php echo form::hidden(array('form_sent'), 1) ?>
	<?php echo Page::formtoken() ?>
	<input type="submit" name="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

