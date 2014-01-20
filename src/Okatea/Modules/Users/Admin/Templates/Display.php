<?php

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# Titre de la page
$okt->page->addGlobalTitle(__('Users'), $view->generateUrl('Users_index'));

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_display'));

# Tabs
$okt->page->tabs();

?>


<form action="<?php $view->generateUrl('Users_display') ?>" method="post">
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
				<?php echo form::text('p_public_default_nb_per_page', 3, 3, $okt->Users->config->public_default_nb_per_page) ?></p>
			</fieldset>

		</div><!-- #tab_public -->

		<div id="tab_admin">
			<h3><?php _e('m_users_Display_on_admin')?></h3>

			<fieldset>
				<legend><?php _e('m_users_Display_of_user_lists') ?></legend>

				<p class="field"><label for="p_admin_default_nb_per_page"><?php _e('m_users_number_of_user_on_admin')?></label>
				<?php echo form::text('p_admin_default_nb_per_page', 3, 3, $okt->Users->config->admin_default_nb_per_page) ?></p>
			</fieldset>

			<fieldset>
				<legend><?php _e('m_users_Filters_users_list')?></legend>

				<p class="field"><?php _e('m_users_Display_filters')?>
					<label><?php _e('m_users_in_dialog_box')?> <?php echo form::radio(array('p_admin_filters_style'),'dialog',($okt->Users->config->admin_filters_style=='dialog'))?></label>
					<label><?php _e('m_users_in_page')?> <?php echo form::radio(array('p_admin_filters_style'),'slide',($okt->Users->config->admin_filters_style=='slide'))?></label>
				</p>
			</fieldset>

		</div><!-- #tab_admin -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden('form_sent', 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Save') ?>" /></p>
</form>
