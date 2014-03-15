<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Builder/Admin/Templates/Builder');

?>

<form action="<?php echo $view->generateUrl('Builder_index', array('step' => $stepper->getCurrentStep())) ?>" method="post">

	<p>Cette étape va créer les packages.</p>

	<p><?php echo form::hidden('form_sent', 1) ?>
	<input type="submit" value="<?php _e('c_c_next') ?>" class="lazy-load" /></p>
</form>
