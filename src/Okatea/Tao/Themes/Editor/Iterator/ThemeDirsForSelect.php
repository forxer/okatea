<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Themes\Editor\Iterator;

/**
 * Itérateur pour lister les répertoire d'un thème
 * et les retourner sous forme de liste HTML.
 *
 */
class ThemeDirsForSelect extends \RecursiveIteratorIterator
{
	public function nextElement()
	{
		global $sThemeId, $oThemeEditor;

		$oFile = $this->current();

		// Display leaf node
		if (!$this->callHasChildren()) {
			return;
		}

		// Display branch with label
		echo '<option value="'.str_replace($oThemeEditor->getThemePath(), '', $oFile->getPathname()).'">'.
			str_repeat('&nbsp;&nbsp;&nbsp;',$this->getDepth()).
			($this->getDepth() > 0 ? '• ' : '').$oFile->getFilename().'</option>';
	}

}