<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

?>

<form
	action="<?php echo $view->generateUrl($okt->stepper->getCurrentStep()) ?>"
	method="post">

	<p>
		<input type="submit" value="<?php _e('c_c_next') ?>" /> <input
			type="hidden" name="sended" value="1" />
	</p>
</form>
