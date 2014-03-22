<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin\Controller\Config;

use Okatea\Admin\Controller;
use Okatea\Tao\Themes\Editor\DefinitionsLess;

class Theme extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('themes')) {
			return $this->serve401();
		}

		$sThemeId = $this->request->attributes->get('theme_id');

		if (!$this->okt->themes->isLoaded($sThemeId)) {
			return $this->serve404();
		}

		# Themes locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/%s/admin/themes');

		# theme infos
		$aThemeInfos = $this->okt->themes->getInstance($sThemeId)->getInfos();

		$aThemeInfos['screenshot'] = file_exists($this->okt->options->get('public_dir').'/themes/'.$sThemeId.'/screenshot.png');

		# Notes de développement
		$sDevNotesFilename = $this->okt->options->get('themes_dir').'/'.$sThemeId.'/notes.md';
		$bHasDevNotes = $bEditDevNotes = false;
		$sDevNotesMd = $sDevNotesHtml = null;
		if (file_exists($sDevNotesFilename))
		{
			$bHasDevNotes = true;

			$bEditDevNotes = $this->request->query->has('edit_notes');

			$sDevNotesMd = file_get_contents($sDevNotesFilename);

			$sDevNotesHtml = \Parsedown::instance()->parse($sDevNotesMd);
		}


		# Definitions LESS
		$sDefinitionsLessFilename = $this->okt->options->get('public_dir').'/themes/'.$sThemeId.'/css/definitions.less';

		$bHasDefinitionsLess = false;
		$oDefinitionsLessEditor = null;
		$aCurrentDefinitionsLess = array();
		if (file_exists($sDefinitionsLessFilename))
		{
			$bHasDefinitionsLess = true;

			$oDefinitionsLessEditor = new DefinitionsLess($this->okt);
			$aCurrentDefinitionsLess = $oDefinitionsLessEditor->getValuesFromFile($sDefinitionsLessFilename);
		}

		# save notes
		if (!empty($_POST['save_notes']))
		{
			if ($bHasDevNotes) {
				file_put_contents($sDevNotesFilename, $this->request->request->get('notes_content'));
			}

			return $this->redirect($this->generateUrl('config_theme', array('theme_id' => $sThemeId)));
		}

		# save definitions less
		if (!empty($_POST['save_def_less']))
		{
			if ($bHasDefinitionsLess) {
				$oDefinitionsLessEditor->writeFileFromPost($sDefinitionsLessFilename);
			}

			return $this->redirect($this->generateUrl('config_theme', array('theme_id' => $sThemeId)));
		}

		return $this->render('Config/Themes/Theme', array(
			'sThemeId' => $sThemeId,
			'aThemeInfos' => $aThemeInfos,
			'bHasDevNotes' => $bHasDevNotes,
			'bEditDevNotes' => $bEditDevNotes,
			'sDevNotesMd' => $sDevNotesMd,
			'sDevNotesHtml' => $sDevNotesHtml,
			'bHasDefinitionsLess' => $bHasDefinitionsLess,
			'oDefinitionsLessEditor' => $oDefinitionsLessEditor,
			'aCurrentDefinitionsLess' => $aCurrentDefinitionsLess
		));
	}

}
