<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

?>

<form action="<?php echo $view->generateInstallUrl($okt->stepper->getNextStep()) ?>" method="post">
	<?php echo $oChecklist->getHTML(); ?>

	<?php if ($oChecklist->checkAll()) : ?>

		<?php if ($oChecklist->checkWarnings()) : ?>
		<p><?php _e('i_db_warning') ?></p>
		<?php endif; ?>

	<?php else : ?>
	<p class="warning"><?php _e('i_db_big_loose') ?></p>
	<?php endif; ?>

	<p><input type="submit" value="<?php _e('c_c_next') ?>" /></p>
</form>
