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
	public function __construct($sLanguage)
	{
		\l10n::init($sLanguage);
	}

	public function loadFile($sFile)
	{
		\l10n::set($sFile);
	}
}
