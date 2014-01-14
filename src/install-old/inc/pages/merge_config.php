<?php
/**
 * Début
 *
 * @addtogroup Okatea
 * @subpackage 		Install interface
 *
 */

if (!defined('OKT_INSTAL_PROCESS')) die;


/* Initialisations
------------------------------------------------------------*/

# Inclusion du prepend
require_once __DIR__.'/../../../oktInc/prepend.php';

use Symfony\Component\Yaml\Yaml;

# Locales
l10n::set(OKT_INSTAL_DIR.'/inc/locales/'.$_SESSION['okt_install_language'].'/install');
l10n::set(OKT_LOCALES_PATH.'/'.$_SESSION['okt_install_language'].'/admin.modules');


/* Traitements
------------------------------------------------------------*/

$bConfigMerged = false;
if (file_exists(OKT_CONFIG_PATH.'/conf_site.yaml.bak'))
{
	$aMergedConf = array_merge(
		(array)Yaml::parse(OKT_CONFIG_PATH.'/conf_site.yaml'),
		(array)Yaml::parse(OKT_CONFIG_PATH.'/conf_site.yaml.bak')
	);

	$okt->config->write($aMergedConf);

	Utilities::deleteOktCacheFiles();

	unlink(OKT_CONFIG_PATH.'/conf_site.yaml.bak');

	$bConfigMerged = true;
}


/* Affichage
------------------------------------------------------------*/

# En-tête
$title = __('i_merge_config_title');
require OKT_INSTAL_DIR.'/header.php'; ?>

<form action="index.php" method="post">

	<?php if ($bConfigMerged) : ?>
	<p><?php _e('i_merge_config_done')?></p>
	<?php else : ?>
	<p><?php _e('i_merge_config_not')?></p>
	<?php endif; ?>

	<p><input type="submit" value="<?php _e('c_c_next') ?>" />
	<input type="hidden" name="step" value="<?php echo $okt->stepper->getNextStep() ?>" /></p>
</form>

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>
