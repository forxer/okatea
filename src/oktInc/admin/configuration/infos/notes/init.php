<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil infos Notes d'installation (partie initialisation)
 *
 * @addtogroup Okatea
 *
 */


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;

# Notes de développement
$sNotesFilename = OKT_ROOT_PATH.'/notes.md';
$bHasNotes = $bEditNotes = false;
if (file_exists($sNotesFilename))
{
	$bHasNotes = true;

	$sNotesMd = file_get_contents($sNotesFilename);

	$bEditNotes = !empty($_REQUEST['edit_notes']) ? $_REQUEST['edit_notes'] : null;

	$sNotesHtml = Parsedown::instance()->parse($sNotesMd);
}
