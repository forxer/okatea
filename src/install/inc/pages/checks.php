<?php
/**
 * Vérification des pré-requis
 *
 * @addtogroup Okatea
 * @subpackage 		Install interface
 *
 */

if (!defined('OKT_INSTAL_PROCESS')) die;

use Tao\Html\CheckList;


/* Initialisations
------------------------------------------------------------*/

l10n::set(OKT_LOCALES_PATH.'/'.$_SESSION['okt_install_language'].'/pre-requisites');

require OKT_INC_PATH.'/systeme_requirements.php';

foreach ($requirements as $group)
{
	${'check_'.$group['group_id']} = new CheckList();

	foreach ($group['requirements'] as $requirement) {
		${'check_'.$group['group_id']}->addItem($requirement['id'], $requirement['test'], $requirement['msg_ok'], $requirement['msg_ko']);
	}
}

$pass_test = true;
$warning_empty = true;

foreach ($requirements as $group)
{
	$pass_test = $pass_test && ${'check_'.$group['group_id']}->checkAll();
	$warning_empty = $warning_empty && !${'check_'.$group['group_id']}->checkWarnings();
}


/* Traitements
------------------------------------------------------------*/

# création d'éventuels fichiers s'ils n'existent pas
if ($pass_test)
{
	if (!file_exists($okt->options->getRootPath().'/.htaccess') && file_exists($okt->options->getRootPath().'/.htaccess.oktDist')) {
		copy($okt->options->getRootPath().'/.htaccess.oktDist', $okt->options->getRootPath().'/.htaccess');
	}
}


/* Affichage
------------------------------------------------------------*/

$oHtmlPage->tabs();

# En-tête
$title = __('i_checks_title');
require OKT_INSTAL_DIR.'/header.php'; ?>

<div id="website_infos"></div><!-- #website_infos -->

<?php foreach ($requirements as $group) : ?>

	<h3><?php echo $group['group_title'] ?></h3>
	<?php echo ${'check_'.$group['group_id']}->getHTML(); ?>

<?php endforeach; ?>

<?php if ($pass_test) : ?>

	<?php if (!$warning_empty) : ?>
	<p><?php _e('i_checks_warning') ?></p>
	<?php endif; ?>

	<form action="index.php" method="post">
		<p><input type="submit" value="<?php _e('c_c_next') ?>" />
		<input type="hidden" name="step" value="<?php echo $stepper->getNextStep() ?>" /></p>
	</form>

<?php else : ?>

	<p class="warning"><?php _e('i_checks_big_loose') ?></p>

<?php endif; ?>

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>
