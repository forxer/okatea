<?php
/**
 * Fin
 *
 * @addtogroup Okatea
 * @subpackage 		Install interface
 *
 */

if (!defined('OKT_INSTAL_PROCESS')) die;


/* Initialisations
------------------------------------------------------------*/

$user = !empty($_SESSION['okt_install_sudo_user']) ? $_SESSION['okt_install_sudo_user'] : '';
$password = !empty($_SESSION['okt_install_sudo_password']) ? $_SESSION['okt_install_sudo_password'] : '';


if ($_SESSION['okt_install_process_type'] == 'install')
{
	# Inclusion du prepend
	define('OKT_SKIP_CSRF_CONFIRM', true);
	require_once __DIR__.'/../../../oktInc/admin/prepend.php';

	# Locales
	l10n::set(OKT_INSTAL_DIR.'/inc/locales/'.$_SESSION['okt_install_language'].'/install');
	l10n::set(OKT_LOCALES_PATH.'/'.$_SESSION['okt_install_language'].'/admin.modules');

	# modules config sheme
	$sTplScheme = $okt->options->get('themes_dir').'/'.$_SESSION['okt_install_theme'].'/modules_config_scheme.php';

	if (file_exists($sTplScheme)) {
		include $sTplScheme;
	}
}


/* Traitements
------------------------------------------------------------*/


/* Affichage
------------------------------------------------------------*/

# En-tête
$title = __('i_end_'.$_SESSION['okt_install_process_type'].'_title');
require OKT_INSTAL_DIR.'/header.php'; ?>

<p><?php _e('i_end_'.$_SESSION['okt_install_process_type'].'_congrat') ?></p>

<p><?php printf(__('i_end_connect'),'./../admin/connexion?user_id='.$user.'&amp;user_pwd='.$password) ?></p>


<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php';

# destroy session data
$_SESSION = array();
session_destroy();


if (defined('OKT_ENVIRONMENT') && OKT_ENVIRONMENT == 'prod')  {
	@files::deltree(OKT_INSTAL_DIR, true);
}
