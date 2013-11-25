<?php
/**
 * Création des premières tables
 *
 * @addtogroup Okatea
 * @subpackage 		Install interface
 *
 */

if (!defined('OKT_INSTAL_PROCESS')) die;


/* Initialisations
------------------------------------------------------------*/

require_once OKT_CONFIG_PATH.'/connexion.php';

$db = oktDb::getInstance();

$oChecklist = new checkList();


/* Traitements
------------------------------------------------------------*/

foreach (new DirectoryIterator(OKT_INC_PATH.'/sql_schema/') as $oFileInfo)
{
	if ($oFileInfo->isDot() || !$oFileInfo->isFile() || $oFileInfo->getExtension() !== 'xml') {
		continue;
	}

	$xsql = new xmlsql($db, file_get_contents($oFileInfo->getPathname()), $oChecklist, $_SESSION['okt_install_process_type']);
	$xsql->replace('{{PREFIX}}',OKT_DB_PREFIX);
	$xsql->execute();
}


/* Affichage
------------------------------------------------------------*/

# En-tête
$title = __('Creating tables');
require OKT_INSTAL_DIR.'/header.php'; ?>

<?php echo $oChecklist->getHTML(); ?>

<?php if ($oChecklist->checkAll()) : ?>

	<?php if ($oChecklist->checkWarnings()) : ?>
	<p><?php _e('i_db_warning') ?></p>
	<?php endif; ?>

	<form action="index.php" method="post">
		<p><input type="submit" value="<?php _e('c_c_next') ?>" />
		<input type="hidden" name="step" value="<?php echo $stepper->getNextStep() ?>" /></p>
	</form>
<?php else : ?>
	<p class="warning"><?php _e('i_db_big_loose') ?></p>
<?php endif; ?>

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>
