<?php
/**
 * Outil infos Notes d'installation (partie traitements)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


# création du fichier de notes
if (!empty($_GET['create_notes']) && !$bHasNotes)
{
	file_put_contents($sNotesFilename, '');

	http::redirect('configuration.php?action=infos&edit_notes=1');
}

# enregistrement notes
if (!empty($_POST['save_notes']))
{
	if ($bHasNotes) {
		file_put_contents($sNotesFilename, $_POST['notes_content']);
	}

	http::redirect('configuration.php?action=infos');
}
