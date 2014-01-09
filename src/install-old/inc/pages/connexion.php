<?php
/**
 * Test de la connexion MySQL
 *
 * @addtogroup Okatea
 * @subpackage 		Install interface
 *
 */

if (!defined('OKT_INSTAL_PROCESS')) die;


/* Initialisations
------------------------------------------------------------*/

# Tentative de connexion à la base de données
$errors = new oktErrors;

require OKT_CONFIG_PATH.'/connexion.php';


/* Traitements
------------------------------------------------------------*/

$con_id = mysqli_connect(OKT_DB_HOST, OKT_DB_USER, OKT_DB_PWD);

if (!$con_id) {
	$errors->set('MySQL: '.mysqli_connect_errno().' '.mysqli_connect_error());
}
else
{
	$db = mysqli_select_db($con_id, OKT_DB_NAME);

	if (!$db) {
		$errors->set('MySQL: '.mysqli_errno($con_id).' '.mysqli_error($con_id));
	}

	mysqli_close($con_id);
}


/* Affichage
------------------------------------------------------------*/

# En-tête
$title = __('i_connexion_title');
require OKT_INSTAL_DIR.'/header.php'; ?>

<?php if ($errors->isEmpty()) : ?>

	<form action="index.php" method="post">

		<p><?php _e('i_connexion_success') ?></p>

		<p><input type="submit" value="<?php _e('c_c_next') ?>" />
		<input type="hidden" name="sended" value="1" />
		<input type="hidden" name="step" value="<?php echo $okt->stepper->getNextStep() ?>" /></p>
	</form>

<?php endif; ?>

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>
