<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Core;

/**
 * Le gestionnnaire de localisation.
 *
 */
class Localisation
{
	public function __construct($sLocalesDir, $sLanguage, $sTimeZone)
	{
		date_default_timezone_set($sTimeZone);

		\l10n::init($sLanguage);

		$this->loadFile($sLocalesDir.'/'.$sLanguage.'/main');
		$this->loadFile($sLocalesDir.'/'.$sLanguage.'/date');
	}

	public function loadFile($sFile)
	{
		\l10n::set($sFile);
	}
}
