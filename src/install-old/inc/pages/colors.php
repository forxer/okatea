<?php
/**
 * Configuration des couleurs
 *
 * @addtogroup Okatea
 * @subpackage Install interface
 *
 */

if (!defined('OKT_INSTAL_PROCESS')) die;

use Tao\Themes\Editor\DefinitionsLess;


/* Initialisations
------------------------------------------------------------*/

# Inclusion du prepend
require_once __DIR__.'/../../../oktInc/prepend.php';

# Locales
l10n::set(OKT_INSTAL_DIR.'/inc/locales/'.$_SESSION['okt_install_language'].'/install');
l10n::set(OKT_LOCALES_PATH.'/'.$_SESSION['okt_install_language'].'/admin.modules');

$oDefinitionsLessEditor = new DefinitionsLess($okt);


/* Traitements
------------------------------------------------------------*/

# formulaire envoyé
if (!empty($_POST['sended']))
{
	$oDefinitionsLessEditor->writeFileFromPost($okt->options->get('themes_dir').'/'.$_SESSION['okt_install_theme'].'/css/definitions.less');

	http::redirect('index.php?step='.$stepper->getNextStep());
}


/* Affichage
------------------------------------------------------------*/

# Color picker et autres joyeusetés
$oDefinitionsLessEditor->setFormAssets($oHtmlPage, $_SESSION['okt_install_theme']);


# En-tête
$title = __('i_colors_title');
require OKT_INSTAL_DIR.'/header.php'; ?>


<form action="index.php" method="post">
	<?php echo $oDefinitionsLessEditor->getHtmlFields() ?>

	<p><input type="submit" value="<?php _e('c_c_next') ?>" />
	<input type="hidden" name="sended" value="1" />
	<input type="hidden" name="step" value="<?php echo $stepper->getCurrentStep() ?>" /></p>
</form>

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>