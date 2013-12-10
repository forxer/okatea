<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Themes\Editor\Iterator;

/**
 * Itérateur pour lister les fichiers d'un thème
 * et les retourner sous forme de liste HTML.
 *
 */
class ThemeFiles extends \RecursiveIteratorIterator
{
	protected $sTab = "\t";
	protected $sEol = PHP_EOL;

	protected $aEditablesExtensions = array('css','less','js','txt','php','html','.htaccess');

	public function beginChildren()
	{
		if (count($this->getInnerIterator()) == 0) {
			return;
		}

		echo str_repeat($this->sTab, $this->getDepth()).'<ul>'.$this->sEol;
	}

	public function endChildren()
	{
		if (count($this->getInnerIterator()) == 0) {
			return;
		}

		echo str_repeat($this->sTab, $this->getDepth()), '</ul></li>'.$this->sEol;
	}

	public function nextElement()
	{
		global $sThemeId, $oThemeEditor;

		$oFile = $this->current();

		if ($this->isDot()) {
			return;
		}

		// Display leaf node
		if (!$this->callHasChildren())
		{
			$sFileExtension = $oFile->getExtension();

			if ($sFileExtension == 'bak') {
				return;
			}

			# Editable leaf ?
			if (in_array($sFileExtension, $this->aEditablesExtensions))
			{
				$sFilepath = str_replace($oThemeEditor->getThemePath(), '', $oFile->getPathname());

				$sLink = '<a href="configuration.php?action=theme_editor&amp;theme='.$sThemeId.'&amp;file='.rawurlencode($sFilepath).'">';

				if ($oThemeEditor->getFilename() == $sFilepath) {
					$sLink .= '<strong>'.$oFile->getFilename().'</strong>';
				}
				else {
					$sLink .= $oFile->getFilename();
				}

				$sLink .= '</a>';
			}
			else {
				$sLink = '<span class="disabled">'.$oFile->getFilename().'</span>';
			}

			echo str_repeat($this->sTab, $this->getDepth()+1).'<li><span class="file">'.$sLink.'</span></li>'.$this->sEol;

			return;
		}

		// Display branch with label
		echo str_repeat($this->sTab, $this->getDepth()+1).'<li><span class="folder">'.$oFile->getFilename().'</span>';

		if (count($this->callGetChildren()) == 0) {
			echo '</li>'.$this->sEol;
		}
		else {
			echo $this->sEol;
		}
	}
}
