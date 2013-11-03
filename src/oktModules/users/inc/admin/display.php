<?php
/**
 * @ingroup okt_module_users
 * @brief La page de configuration de l'affichage
 *
 */


# Accès direct interdit
if (!defined('ON_USERS_MODULE')) die;


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_admin_filters_style = !empty($_POST['p_admin_filters_style']) ? $_POST['p_admin_filters_style'] : 'dialog';

	$p_public_default_nb_per_page = !empty($_POST['p_public_default_nb_per_page']) ? intval($_POST['p_public_default_nb_per_page']) : 10;
	$p_admin_default_nb_per_page = !empty($_POST['p_admin_default_nb_per_page']) ? intval($_POST['p_admin_default_nb_per_page']) : 10;

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'admin_filters_style' => $p_admin_filters_style,
			'admin_default_nb_per_page' => $p_admin_default_nb_per_page,
			'public_default_nb_per_page' => $p_public_default_nb_per_page
		);

		try
		{
			$okt->users->config->write($new_conf);

			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

			$okt->redirect('module.php?m=users&action=display');
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
$okt->page->addGlobalTitle(__('c_a_menu_display'));

# Tabs
$okt->page->tabs();


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_public"><span><?php _e('m_users_Site_side')?></span></a></li>
			<li><a href="#tab_admin"><span><?php _e('m_users_Administration_interface')?></span></a></li>
		</ul>

		<div id="tab_public">
			<h3><?php _e('m_users_Display_on_site_side')?></h3>

			<fieldset>
				<legend><?php _e('m_users_Display_of_user_lists')?></legend>

				<p class="field"><label for="p_public_default_nb_per_page"><?php _e('m_users_number_of_user_on_public_part')?></label>
				<?php echo form::text('p_public_default_nb_per_page', 3, 3, $okt->users->config->public_default_nb_per_page) ?></p>
			</fieldset>

		</div><!-- #tab_public -->

		<div id="tab_admin">
			<h3><?php _e('m_users_Display_on_admin')?></h3>

			<fieldset>
				<legend><?php _e('m_users_Display_of_user_lists') ?></legend>

				<p class="field"><label for="p_admin_default_nb_per_page"><?php _e('m_users_number_of_user_on_admin')?></label>
				<?php echo form::text('p_admin_default_nb_per_page', 3, 3, $okt->users->config->admin_default_nb_per_page) ?></p>
			</fieldset>

			<fieldset>
				<legend><?php _e('m_users_Filters_users_list')?></legend>

				<p class="field"><?php _e('m_users_Display_filters')?>
					<label><?php _e('m_users_in_dialog_box')?> <?php echo form::radio(array('p_admin_filters_style'),'dialog',($okt->users->config->admin_filters_style=='dialog'))?></label>
					<label><?php _e('m_users_in_page')?> <?php echo form::radio(array('p_admin_filters_style'),'slide',($okt->users->config->admin_filters_style=='slide'))?></label>
				</p>
			</fieldset>

		</div><!-- #tab_admin -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','users'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'display'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
