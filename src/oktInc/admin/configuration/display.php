<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page de configuration de l'affichage
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


# locales
l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.display');


/* Initialisations
----------------------------------------------------------*/

$aUiThemes = htmlPage::getUiThemes();
$aThemes = array_flip(oktThemes::getThemes());

$aNotAllowedAdminThemes = array(
	'dark-hive',
	'dot-luv',
	'eggplant',
	'le-frog',
	'mint-choc',
	'swanky-purse',
	'trontastic',
	'ui-darkness',
	'vader'
);

$aAllowedAdminThemes = array_diff($aUiThemes,$aNotAllowedAdminThemes);

$aAllowedAdminThemes = array_combine($aAllowedAdminThemes,$aAllowedAdminThemes);


foreach ($aAllowedAdminThemes as $theme)
{
	if ($theme == $okt->config->admin_theme) {
		$aAllowedAdminThemes[$theme] = $theme.__('c_a_config_display_current_theme');
	}
}

$aAllowedAdminThemes = array_flip($aAllowedAdminThemes);


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	# traitement d'un éventuel theme uploadé
	if (isset($_FILES['p_upload_theme']) && !empty($_FILES['p_upload_theme']['tmp_name']))
	{
		$sUploadedFile = $_FILES['p_upload_theme'];
		$sTempDir = OKT_ROOT_PATH.'/temp/';
		$sZipFilename = $sTempDir.$sUploadedFile['name'];

		try {

			# on supprime l'éventuel répertoire temporaire s'il existe déjà
			if (is_dir($sTempDir)) {
				files::deltree($sTempDir);
			}

			$sExtension = files::getExtension($sUploadedFile['name']);

			# des erreurs d'upload ?
			util::uploadStatus($sUploadedFile);

			# vérification de l'extension
			if ($sExtension != 'zip') {
				throw new Exception(__('c_a_config_display_not_zip_file'));
			}

			# création répertoire temporaire
			files::makeDir($sTempDir);

			if (!move_uploaded_file($sUploadedFile['tmp_name'],$sZipFilename)) {
				throw new Exception(__('c_a_config_display_unable_move_file'));
			}

			$oZip = new fileUnzip($sZipFilename);
			$oZip->getList(false,'#(^|/)(__MACOSX|\.svn|\.DS_Store|Thumbs\.db|development-bundle|js)(/|$)#');

			$zip_root_dir = $oZip->getRootDir();

			if ($zip_root_dir !== false)
			{
				$sTargetDir = dirname($sZipFilename);
				$sDestinationDir = $sTargetDir.'/'.$zip_root_dir;
				$sCssFilename = $zip_root_dir.'/css/custom-theme/jquery-ui-.custom.css';
				$hasCssFile = $oZip->hasFile($sCssFilename);
			}
			else {
				$sTargetDir = dirname($sZipFilename).'/'.preg_replace('/\.([^.]+)$/','',basename($sZipFilename));
				$sDestinationDir = $sTargetDir;
				$sCssFilename = 'css/custom-theme/jquery-ui-.custom.css';
				$hasCssFile = $oZip->hasFile($sCssFilename);
			}

			if ($oZip->isEmpty())
			{
				$oZip->close();
				files::deltree($sTempDir);
				throw new Exception(__('c_a_config_display_empty_zip_file'));
			}

			if (!$hasCssFile)
			{
				$oZip->close();
				files::deltree($sTempDir);
				throw new Exception(__('c_a_config_display_not_valid_theme'));
			}

			$oZip->unzipAll($sTempDir);
			$oZip->close();

			util::rcopy($sTempDir.'css/custom-theme', OKT_PUBLIC_PATH.'/ui-themes/custom');

			$fp = fopen(OKT_PUBLIC_PATH.'/ui-themes/custom/jquery-ui.css', 'wb');
			fwrite($fp, file_get_contents(OKT_PUBLIC_PATH.'/ui-themes/base/jquery.ui.core.css'));
			fwrite($fp, file_get_contents(OKT_PUBLIC_PATH.'/ui-themes/base/jquery.ui.resizable.css'));
			fwrite($fp, file_get_contents(OKT_PUBLIC_PATH.'/ui-themes/base/jquery.ui.selectable.css'));
			fwrite($fp, file_get_contents(OKT_PUBLIC_PATH.'/ui-themes/base/jquery.ui.accordion.css'));
			fwrite($fp, file_get_contents(OKT_PUBLIC_PATH.'/ui-themes/base/jquery.ui.autocomplete.css'));
			fwrite($fp, file_get_contents(OKT_PUBLIC_PATH.'/ui-themes/base/jquery.ui.button.css'));
			fwrite($fp, file_get_contents(OKT_PUBLIC_PATH.'/ui-themes/base/jquery.ui.dialog.css'));
			fwrite($fp, file_get_contents(OKT_PUBLIC_PATH.'/ui-themes/base/jquery.ui.slider.css'));
			fwrite($fp, file_get_contents(OKT_PUBLIC_PATH.'/ui-themes/base/jquery.ui.tabs.css'));
			fwrite($fp, file_get_contents(OKT_PUBLIC_PATH.'/ui-themes/base/jquery.ui.datepicker.css'));
			fwrite($fp, file_get_contents(OKT_PUBLIC_PATH.'/ui-themes/base/jquery.ui.progressbar.css'));
			fwrite($fp, file_get_contents(OKT_PUBLIC_PATH.'/ui-themes/custom/jquery-ui-.custom.css'));
			fclose($fp);

			files::deltree($sTempDir);
			unlink(OKT_PUBLIC_PATH.'/ui-themes/custom/jquery-ui-.custom.css');

			$_POST['p_admin_theme'] = 'custom';
		}
		catch (Exception $e) {
			$okt->error->set($e->getMessage());
		}
	}

	# enregistrement de la configuration
	$p_public_theme = !empty($_POST['p_public_theme']) ? $_POST['p_public_theme'] : 'base';
	$p_enable_admin_bar = !empty($_POST['p_enable_admin_bar']) ? true : false;
	$p_admin_sidebar_position = !empty($_POST['p_admin_sidebar_position']) ? intval($_POST['p_admin_sidebar_position']) : 0;
	$p_admin_theme = !empty($_POST['p_admin_theme']) ? $_POST['p_admin_theme'] : 'base';
	$p_admin_compress_output = !empty($_POST['p_admin_compress_output']) ? true : false;

	if (!in_array($p_admin_theme,$aAllowedAdminThemes) && $p_admin_theme != 'custom') {
		$p_admin_theme = $okt->config->admin_theme;
	}

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'public_theme' 					=> $p_public_theme,
			'enable_admin_bar' 				=> $p_enable_admin_bar,
			'admin_theme' 					=> $p_admin_theme,
			'admin_sidebar_position'		=> $p_admin_sidebar_position,
			'admin_compress_output' 		=> $p_admin_compress_output
		);

		try
		{
			$okt->config->write($new_conf);
			$okt->redirect('configuration.php?action=display&updated=1');
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
		cssLink = "<link href=\"'.$okt->config->app_path.'oktCommon/ui-themes/" + style + "/jquery-ui.css\" type=\"text/css\" rel=\"Stylesheet\" />";

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

# Confirmations
$okt->page->messages->success('updated',__('c_c_confirm_configuration_updated'));



# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="configuration.php" method="post" enctype="multipart/form-data">
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
				<span class="note"><?php printf(__('c_a_config_display_upload_theme_note'),"javascript:(function(){if%20(!/Firefox[\/\s](\d+\.\d+)/.test(navigator.userAgent)){alert('Sorry,%20due%20to%20security%20restrictions,%20this%20tool%20only%20works%20in%20Firefox');%20return%20false;%20};%20if(window.jquitr){%20jquitr.addThemeRoller();%20}%20else{%20jquitr%20=%20{};%20jquitr.s%20=%20document.createElement('script');%20jquitr.s.src%20=%20'http://jqueryui.com/themeroller/developertool/developertool.js.php';%20document.getElementsByTagName('head')[0].appendChild(jquitr.s);}%20})();"); ?></span></p>

			</fieldset>
			<!--
			<p class="field"><label for="p_admin_compress_output"><?php echo form::checkbox('p_admin_compress_output', 1, $okt->config->admin_compress_output) ?>
			<?php _e('c_a_config_display_compress_output') ?></label></p>
			-->
		</div><!-- #tab_admin -->
	</div><!-- #tabered -->

	<p><?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'display'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

