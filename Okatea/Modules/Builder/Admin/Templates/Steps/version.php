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

<form
	action="<?php echo $view->generateAdminUrl('Builder_index', array('step' => $stepper->getCurrentStep())) ?>"
	method="post">

	<p><?php printf(__('m_builder_step_version_1'), '<strong>'.$version.'</strong>') ?></p>

	<p><?php _e('m_builder_step_version_2') ?></p>
	<ul class="col field">
		<li><label for="type_stable"><?php echo form::radio(array('type', 'type_stable'), 'stable', ($type == 'stable')) ?> <?php _e('m_builder_step_version_3') ?></label></li>
		<li><label for="type_dev"><?php echo form::radio(array('type', 'type_dev'), 'dev', ($type == 'dev')) ?> <?php _e('m_builder_step_version_4') ?></label></li>
	</ul>

	<p><?php echo form::hidden('form_sent', 1)?>
	<input type="submit" value="<?php _e('c_c_next') ?>" />
	</p>
</form>
