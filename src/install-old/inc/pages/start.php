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


/* Traitements
------------------------------------------------------------*/


/* Affichage
------------------------------------------------------------*/

# En-tête
require OKT_INSTAL_DIR.'/header.php'; ?>

<p><?php printf(__('i_start_about_'.$_SESSION['okt_install_process_type']), $oktVersion) ?></p>

<p><?php _e('i_start_choose_lang') ?></p>
<ul id="languageChoice">
	<li><a href="index.php?switch_language=fr"<?php if ($_SESSION['okt_install_language'] == 'fr') echo ' class="current"'; ?>><img src="<?php echo OKT_INSTAL_COMMON_URL ?>/img/flags/fr.png" alt="" /> français</a></li>
	<li><a href="index.php?switch_language=en"<?php if ($_SESSION['okt_install_language'] == 'en') echo ' class="current"'; ?>><img src="<?php echo OKT_INSTAL_COMMON_URL ?>/img/flags/en.png" alt="" /> english</a></li>
</ul>

<form action="index.php" method="post">
	<p class="note"><?php _e('i_start_click_next') ?></p>

	<p><input type="submit" value="<?php _e('c_c_next') ?>" />
	<input type="hidden" name="step" value="<?php echo $okt->stepper->getNextStep() ?>" /></p>
</form>

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>
