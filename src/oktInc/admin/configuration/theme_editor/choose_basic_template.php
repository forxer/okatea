<?php
/**
 * La page de choix de template de base pour la création de templates.
 *
 * @addtogroup Okatea
 */


# Accès direct interdit
if (!defined('ON_THEME_EDITOR')) die;


/* Initialisations
----------------------------------------------------------*/


/* Traitements
----------------------------------------------------------*/

# formulaire envoyé
if (!empty($_POST['form_sent']) && $sThemeId)
{
	$p_basic_template = !empty($_POST['p_basic_template']) ? $_POST['p_basic_template'] : '';

	if (empty($p_basic_template)) {
		$okt->error->set(__('c_a_te_error_must_basic_template'));
	}

	if ($okt->error->isEmpty()) {
		$okt->redirect('configuration.php?action=theme_editor&theme='.$sThemeId.'&new_template=1&basic_template='.rawurlencode($p_basic_template));
	}
}


/* Affichage
----------------------------------------------------------*/

$aTemplatesDirs = $oThemeEditor->getTemplatesDirs();

# Infos page
$okt->page->addGlobalTitle(__('c_a_theme_editor'), 'configuration.php?action=theme_editor');

if ($sThemeId) {
	$okt->page->addGlobalTitle($oThemeEditor->getThemeInfo('name'), 'configuration.php?action=theme_editor&theme='.$sThemeId);
}

$okt->page->addGlobalTitle(__('c_a_te_new_tpl'), 'configuration.php?action=theme_editor&theme='.$sThemeId.'&new_file=1');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>


<form action="configuration.php" method="post">

	<p class="field col"><label for="p_basic_template"><?php _e('c_a_te_basic_template') ?></label>
	<?php echo form::select('p_basic_template', array_merge(array('&nbsp;'=>null), $aTemplatesDirs), $sBasicTemplate)?></p>

	<p><?php echo form::hidden('action', 'theme_editor') ?>
	<?php echo form::hidden('theme', $sThemeId) ?>
	<?php echo form::hidden('new_template', 1) ?>
	<?php echo form::hidden('form_sent', 1) ?>
	<?php echo adminPage::formtoken() ?>
	<input type="submit" name="save" value="<?php _e('c_c_next') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
