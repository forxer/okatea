<?php

use Tao\Forms\Statics\FormElements as form;

?>

<h3><?php _e('c_a_infos_notes_title') ?></h3>

<?php if (!$aNotes['has']) : ?>

	<p><em><?php _e('c_a_infos_no_notes') ?></em></p>

	<p><a href="<?php echo $view->generateUrl('config_infos') ?>?create_notes=1"><?php _e('c_a_infos_create_notes_file') ?></a></p>

<?php else : ?>

	<?php if ($aNotes['edit']) : ?>

		<form action="<?php echo $view->generateUrl('config_infos') ?>" method="post">

			<p><?php echo form::textarea('notes_content', 80, 20, $this->aNotes['md'])?></p>

			<p><?php echo form::hidden('save_notes', 1) ?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
		</form>

	<?php else : ?>

		<?php echo $this->aNotes['html'] ?>
		<p><a href="<?php echo $view->generateUrl('config_infos') ?>?edit_notes=1" class="button"><?php _e('c_c_action_edit') ?></a></p>

	<?php endif; ?>

<?php endif; ?>
