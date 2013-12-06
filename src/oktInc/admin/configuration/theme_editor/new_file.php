<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page de création de fichier de l'éditeur de themes.
 *
 * @addtogroup Okatea
 */


# Accès direct interdit
if (!defined('ON_THEME_EDITOR')) die;


/* Initialisations
----------------------------------------------------------*/

# locales
l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.theme.editor');

$sThemeId = !empty($_REQUEST['theme']) ? $_REQUEST['theme'] : null;


$oThemeEditor = new Okatea\Themes\Editor\Editor($okt, OKT_THEMES_DIR, OKT_THEMES_PATH);



if ($sThemeId)
{
	try
	{
		$oThemeEditor->loadTheme($sThemeId);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
		$sThemeId = null;
	}
}
else {
	$okt->error->set(__('c_a_te_error_choose_theme'));
}


/* Traitements
----------------------------------------------------------*/

# formulaire envoyé
if (!empty($_POST['form_sent']))
{
	$p_filename = !empty($_POST['p_filename']) ? $_POST['p_filename'] : '';
	$p_parent = !empty($_POST['p_parent']) ? rawurldecode($_POST['p_parent']) : '/';

	if (empty($p_filename)) {
		$okt->error->set(__('c_a_te_error_must_filename'));
	}

	if ($okt->error->isEmpty())
	{
		file_put_contents($oThemeEditor->getThemePath().$p_parent.'/'.$p_filename, '');

		http::redirect('configuration.php?action=theme_editor&theme='.$sThemeId.'&file='.rawurlencode($p_parent.'/'.$p_filename));
	}
}


/* Affichage
----------------------------------------------------------*/

# Infos page
$okt->page->addGlobalTitle(__('c_a_theme_editor'), 'configuration.php?action=theme_editor');

if ($sThemeId) {
	$okt->page->addGlobalTitle($oThemeEditor->getThemeInfo('name'), 'configuration.php?action=theme_editor&theme='.$sThemeId);
}

$okt->page->addGlobalTitle(__('c_a_te_new_file'), 'configuration.php?action=theme_editor&theme='.$sThemeId.'&new_file=1');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>


<form action="configuration.php" method="post">

	<div class="two-cols">
		<p class="field col"><label for="p_filename"><?php _e('c_a_te_filename') ?></label>
		<?php echo form::text('p_filename', 40, 256) ?></p>

		<p class="field col"><label for="p_parent"><?php _e('c_a_te_parent_dir') ?></label>
		<select name="p_parent" id="p_parent">
			<option value="/">&nbsp;</option>
			<?php $oThemeEditor->loadThemeDirTree();
			foreach ($oThemeEditor->getThemeFiles() as $key=>$item); ?>
		</select></p>
	</div>

	<p><?php echo form::hidden(array('action'), 'theme_editor') ?>
	<?php echo form::hidden('theme', $sThemeId) ?>
	<?php echo form::hidden('new_file', 1) ?>
	<?php echo form::hidden('form_sent', 1) ?>
	<?php echo adminPage::formtoken() ?>
	<input type="submit" name="save" value="<?php _e('c_c_action_Save') ?>" /></p>
</form>



<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
