<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao;

/**
 * Le gestionnnaire de localisation.
 *
 */
class Localisation
{
	public function __construct($sLocalesDir, $sLanguage, $sTimeZone)
	{
	//	date_default_timezone_set($sTimeZone);

		$GLOBALS['__l10n'] = array();

		$this->loadFile($sLocalesDir.'/'.$sLanguage.'/main');
		$this->loadFile($sLocalesDir.'/'.$sLanguage.'/date');
	}

	public function loadFile($sFile)
	{
		if (file_exists($sFile.'.lang.php')) {
			require $sFile.'.lang.php';
		}
	}
}
