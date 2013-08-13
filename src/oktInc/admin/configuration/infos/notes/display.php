<?php
/**
 * Outil infos Notes d'installation (partie affichage)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;



?>

<h3><?php _e('c_a_infos_notes_title') ?></h3>

<?php if (!$bHasNotes) : ?>

	<p><em><?php _e('c_a_infos_no_notes') ?></em></p>

	<p><a href="configuration.php?action=infos&amp;create_notes=1"><?php _e('c_a_infos_create_notes_file') ?></a></p>

<?php else : ?>

	<?php if ($bEditNotes) : ?>

		<form action="configuration.php" method="post">

			<p><?php echo form::textarea('notes_content', 80, 20, $sNotesMd)?></p>

			<p><?php echo form::hidden(array('action'), 'infos') ?>
			<?php echo form::hidden('save_notes', 1) ?>
			<?php echo adminPage::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
		</form>

	<?php else : ?>

		<?php echo $sNotesHtml ?>
		<p><a href="configuration.php?action=infos&amp;edit_notes=1" class="button"><?php _e('c_c_action_edit') ?></a></p>

	<?php endif; ?>

<?php endif; ?>
