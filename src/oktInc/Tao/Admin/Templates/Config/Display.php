<?php

use Tao\Forms\Statics\FormElements as form;

$view->extend('layout');


$okt->page->js->addScript('

	// quand on changent de theme, on rechargent la CSS
	$("#p_admin_theme").change(function() {
		$("#p_admin_theme option:selected").each(function () {
			reloadCSS($(this).val());
		});
	});

	// les styles communs à tous les thèmes
	commonStyles =
		"<style type=\"text/css\">"+
		".ui-widget{"+
			"font-size: 1em;"+
			"font-family: \"Trebuchet MS\", Verdana, helvetica, \"Bitstream Vera Sans\", sans-serif;"+
		"}"+
		"</style>";

	changeCount = 0;

	function reloadCSS(style)
	{
		// theme actuel (dans la config PHP)
		currenStyle = "'.$okt->config->admin_theme.'";

		// nouveau theme
		cssLink = "<link href=\"'.$okt->config->app_path.basename($okt->options->get('public_dir')).'/ui-themes/" + style + "/jquery-ui.css\" type=\"text/css\" rel=\"Stylesheet\" />";

		// si il y a deja une prévisualisation on ajoutent un theme
		if ($(\'link[href*="jquery-ui.css"]\').size() > 0){
			$(\'link[href*="jquery-ui.css"]:last\').eq(0).after(cssLink);
		}
		// sinon on ajoute le theme à prévisualiser
		else {
			$("head").append(cssLink);
		}

		// si il y a deja des prévisualisations on supprime la première
		if( $(\'link[href*="jquery-ui.css"]\').size() > 2){
			$(\'link[href*="jquery-ui.css"]:first\').remove();
		}

		// si on veut prévisualiser le theme actuel on virent la notification
		if (style == currenStyle) {
			$("#themePreviewNotification").remove();
		}
		// sinon on affichent la notification
		else if ($("#themePreviewNotification").size() == 0) {
			$("label[for=p_admin_theme]").append("<span id=\"themePreviewNotification\"> (apercu)</span>");
		}

		// a la première prévisualisation on chargent les styles communs à tous les thèmes
		changeCount++;
		if (changeCount == 1) {
			$("head").append(commonStyles);
		}
	};

');

# Radio buttons
$okt->page->js->addReady('$("#sidebar_position_choice").buttonset();');

# Tabs
$okt->page->tabs();

# infos page
$okt->page->addGlobalTitle(__('c_a_config_display'));

?>

<form action="<?php $view->generateUrl('config_display') ?>" method="post" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<li><a href="#tab_public"><span><?php _e('c_a_config_display_public_part') ?></span></a></li>
			<li><a href="#tab_admin"><span><?php _e('c_a_config_display_admin_part') ?></span></a></li>
		</ul>

		<div id="tab_public">

			<p class="field"><label for="p_public_theme"><?php _e('c_a_config_display_choose_jquery_ui_theme') ?></label>
			<?php echo form::select('p_public_theme',array_combine($aUiThemes,$aUiThemes),$okt->config->public_theme) ?></p>

			<p><?php echo form::checkbox('p_enable_admin_bar', 1, $okt->config->enable_admin_bar) ?>
			<label for="p_enable_admin_bar"><?php _e('c_a_config_display_admin_bar') ?></label>
			<span class="note"><?php _e('c_a_config_display_admin_bar_note') ?></span></p>

		</div><!-- #tab_public -->

		<div id="tab_admin">
			<p class="field"><?php _e('c_a_config_display_menu_pos') ?></p>

			<p id="sidebar_position_choice">
				<?php echo form::radio(array('p_admin_sidebar_position', 'p_admin_sidebar_position_0'), 0, ($okt->config->admin_sidebar_position == 0)) ?>
				<label for="p_admin_sidebar_position_0"><?php _e('c_c_direction_Left') ?></label>
				<?php echo form::radio(array('p_admin_sidebar_position', 'p_admin_sidebar_position_1'), 1, $okt->config->admin_sidebar_position) ?>
				<label for="p_admin_sidebar_position_1"><?php _e('c_c_direction_Right') ?></label>
			</p>

			<fieldset>
				<legend><?php _e('c_a_config_display_theme') ?></legend>

				<p class="field"><label for="p_admin_theme"><?php _e('c_a_config_display_choose_jquery_ui_theme') ?></label>
				<?php echo form::select('p_admin_theme', $aAllowedAdminThemes, $okt->config->admin_theme) ?>
				<span class="note"><?php _e('c_a_config_display_choose_theme_note') ?></span></p>

				<p class="field"><label for="p_upload_theme"><?php _e('c_a_config_display_upload_theme') ?></label>
				<?php echo form::file('p_upload_theme') ?>
				<span class="note"><?php _e('c_a_config_display_upload_theme_note'); ?></span></p>

			</fieldset>
			<!--
			<p class="field"><label for="p_admin_compress_output"><?php echo form::checkbox('p_admin_compress_output', 1, $okt->config->admin_compress_output) ?>
			<?php _e('c_a_config_display_compress_output') ?></label></p>
			-->
		</div><!-- #tab_admin -->
	</div><!-- #tabered -->

	<p><?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

