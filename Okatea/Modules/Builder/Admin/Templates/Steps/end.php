<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$view->extend('Builder/Admin/Templates/Builder');

?>

<form action="<?php echo $view->generateUrl('Builder_index', array('step' => $stepper->getNextStep())) ?>" method="post">
	<p><input type="submit" value="<?php _e('c_c_next') ?>" /></p>
</form>
