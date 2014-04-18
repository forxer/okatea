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

	<p><?php printf(__('m_builder_step_changelog_1'), '<strong>'.$okt->getVersion().' ('.date('Y-m-d').')</strong>') ?></p>

	<p><?php _e('m_builder_step_changelog_2') ?></p>

	<textarea id="config_editor" name="changelog_editor" rows="35" cols="97"><?php echo $sChangelog ?></textarea>

	<p><?php echo form::hidden('form_sent', 1) ?>
	<input type="submit" value="<?php _e('c_c_next') ?>" /></p>
</form>

