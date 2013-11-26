<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @ingroup okt_theme_testing
 * @brief La page de configuration d'un thème.
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Locales
l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.themes');

# Themes object
$oThemes = new oktThemes($okt, OKT_THEMES_PATH);

# Liste des thèmes présents
$aInstalledThemes = $oThemes->getThemesAdminList();

# Tri par ordre alphabétique des listes de thème
uasort($aInstalledThemes, array('oktThemes','sortThemesList'));

# Theme infos
$sThemeId = !empty($_REQUEST['theme_id']) ? $_REQUEST['theme_id'] : null;

if (!isset($aInstalledThemes[$sThemeId]) /* || !$aInstalledThemes[$sThemeId]['is_active'] || $aInstalledThemes[$sThemeId]['has_config']*/) {
	http::redirect('configuration.php?action=themes');
}

$aThemeInfos = $aInstalledThemes[$sThemeId];


# Notes de développement
$sDevNotesFilename = OKT_THEMES_PATH.'/'.$sThemeId.'/notes.md';
$bHasDevNotes = $bEditDevNotes = false;
if (file_exists($sDevNotesFilename))
{
	$bHasDevNotes = true;

	$sDevNotesMd = file_get_contents($sDevNotesFilename);

	$bEditDevNotes = !empty($_REQUEST['edit_notes']) ? $_REQUEST['edit_notes'] : null;

	$sDevNotesHtml = Parsedown::instance()->parse($sDevNotesMd);
}


# Definitions LESS
$sDefinitionsLessFilename = OKT_THEMES_PATH.'/'.$sThemeId.'/css/definitions.less';
$bHasDefinitionsLess = false;
if (file_exists($sDefinitionsLessFilename))
{
	$bHasDefinitionsLess = true;

	$oDefinitionsLessEditor = new oktDefinitionsLessEditor($okt);
	$aCurrentDefinitionsLess = $oDefinitionsLessEditor->getValuesFromFile($sDefinitionsLessFilename);
}


/* Traitements
----------------------------------------------------------*/

# enregistrement notes
if (!empty($_POST['save_notes']))
{
	if ($bHasDevNotes) {
		file_put_contents($sDevNotesFilename, $_POST['notes_content']);
	}

	http::redirect('configuration.php?action=theme&theme_id='.$sThemeId);
}

# enregistrement definitions less
if (!empty($_POST['save_def_less']))
{
	if ($bHasDefinitionsLess) {
		$oDefinitionsLessEditor->writeFileFromPost($sDefinitionsLessFilename);
	}

	http::redirect('configuration.php?action=theme&theme_id='.$sThemeId);
}


/* Affichage
----------------------------------------------------------*/

# Onglets
$okt->page->tabs();

# Color picker et autres joyeusetés
if ($bHasDefinitionsLess) {
	$oDefinitionsLessEditor->setFormAssets($okt->page, $sThemeId);
}

# infos page
$okt->page->addGlobalTitle(__('c_a_themes_management'),'configuration.php?action=themes');
$okt->page->addGlobalTitle($aThemeInfos['name']);


# CSS
$okt->page->css->addCss('
#theme-screenshot {
	float: left;
	margin: 0 1em 1em 0;
	width: 400px;
}
#no-screenshot {
	width: 400px;
	height: 300px;
	background: #f1f1f1;
	border: 1px solid #f1f1f1;
	text-align: center;
}
#no-screenshot em {
	position: relative;
	top: 45%;
}
');

# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

	<div id="tabered">
		<ul>
			<li><a href="#tab_infos"><span><?php _e("Infos") ?></span></a></li>
			<?php if ($bHasDevNotes) : ?>
			<li><a href="#tab_dev_notes"><span><?php _e('c_a_themes_notes') ?></span></a></li>
			<?php endif; ?>
			<?php if ($bHasDefinitionsLess) : ?>
			<li><a href="#tab_def_less"><span>definitions.less</span></a></li>
			<?php endif; ?>
		</ul>

		<div id="tab_infos" class="ui-helper-clearfix">
			<h3><?php echo html::escapeHTML($aThemeInfos['name']) ?></h3>

				<div id="theme-screenshot">
					<?php if ($aThemeInfos['screenshot']) : ?>
					<img src="<?php echo $okt->config->app_path.OKT_THEMES_DIR.'/'.$aThemeInfos['id'].'/screenshot.jpg' ?>" width="100%" height="100%" alt="" />
					<?php else : ?>
					<div id="no-screenshot"><em class="note"><?php _e('c_a_themes_no_screenshot') ?></em></div>
					<?php endif; ?>
				</div>

				<div class="theme-infos">

					<p><?php echo $aThemeInfos['desc'] ?></p>

					<p><?php printf(__('c_a_themes_version_%s'), $aThemeInfos['version']) ?></p>

					<p><?php printf(__('c_a_themes_author_%s'), $aThemeInfos['author']) ?></p>

					<p><?php echo html::escapeHTML($aThemeInfos['tags']) ?></p>

				</div>

		</div><!-- #tab_infos -->

		<?php if ($bHasDevNotes) : ?>
		<div id="tab_dev_notes">

			<?php if ($bEditDevNotes) : ?>
				<form action="configuration.php" method="post">

					<p><?php echo form::textarea('notes_content', 80, 20, $sDevNotesMd)?></p>

					<p><?php echo form::hidden(array('action'), 'theme') ?>
					<?php echo form::hidden(array('theme_id'), $sThemeId) ?>
					<?php echo form::hidden('save_notes', 1) ?>
					<?php echo adminPage::formtoken(); ?>
					<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
				</form>
			<?php else : ?>
				<?php echo $sDevNotesHtml ?>
				<p><a href="configuration.php?action=theme&amp;theme_id=<?php echo $sThemeId ?>&amp;edit_notes=1#tab_dev_notes" class="button"><?php _e('c_c_action_edit') ?></a></p>
			<?php endif; ?>

		</div><!-- #tab_dev_notes -->
		<?php endif; ?>

		<?php if ($bHasDefinitionsLess) : ?>
		<div id="tab_def_less">
			<h3>definitions.less</h3>

			<form action="configuration.php" method="post">
				<?php # affichage champs definitions.less
				echo $oDefinitionsLessEditor->getHtmlFields($aCurrentDefinitionsLess, 4); ?>

				<p><?php echo form::hidden(array('action'), 'theme') ?>
				<?php echo form::hidden(array('theme_id'), $sThemeId) ?>
				<?php echo form::hidden('save_def_less', 1) ?>
				<?php echo adminPage::formtoken(); ?>
				<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
			</form>
		</div><!-- #tab_def_less -->
		<?php endif; ?>

	</div><!-- #tabered -->



<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
