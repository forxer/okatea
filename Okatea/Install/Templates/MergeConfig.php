<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('layout');

?>

<form
	action="<?php echo $view->generateUrl($okt->stepper->getNextStep()) ?>"
	method="post">

	<?php if ($bConfigMerged) : ?>
	<p><?php _e('i_merge_config_done')?></p>
	<?php else : ?>
	<p><?php _e('i_merge_config_not')?></p>
	<?php endif; ?>

	<p><?php echo $okt->page->formtoken()?>
	<input type="submit" value="<?php _e('c_c_next') ?>" />
	</p>
</form>