<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$view->extend('Builder/Admin/Templates/Builder');

?>

<p>Vous êtes sur l'interface de création de packages de nouvelle version d'Okatea.</p>

<p>Ce package sera basé sur cette installation d'Okatea.</p>

<form action="<?php echo $view->generateUrl('Builder_index', array('step' => $stepper->getNextStep())) ?>" method="post">
	<p><input type="submit" value="<?php _e('c_c_next') ?>" /></p>
</form>
