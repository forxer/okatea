<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao;

/**
 * Very simple localization management.
 *
 */
class Localization
{
	protected $sLanguage;

	protected $aLoaded;

	/**
	 * Initialize this primary class.
	 *
	 * @param string $sLanguage
	 * @param string $sTimeZone
	 */
	public function __construct($sLanguage, $sTimeZone)
	{
		$this->sLanguage = $sLanguage;

	//	date_default_timezone_set($sTimeZone);

		$GLOBALS['okt_l10n'] = array();
		$this->aLoaded = array();
	}

	/**
	 * Load a l10n file.
	 *
	 * @param string $sFilename    The file to be loaded.
	 * @param boolean $bForce      Force loading file.
	 * @return boolean|NULL
	public function loadFile($sFilename, $bForce = false)
	{
		if (!file_exists($sFilename.'.lang.php')) {
			return false;
		}

		if (!$bForce && in_array($sFilename, $this->aLoaded))  {
			return null;
		}

		require $sFilename.'.lang.php';

		$this->aLoaded[] = $sFilename;

		return true;
	}
	 */

	/**
	 * Load a l10n file.
	 *
	 * @param string $sFilename 	The file to be loaded.
	 * @param string $sLanguage 	Force loading language file.
	 * @return boolean|NULL
	 */
	public function loadFile($sFilename, $sLanguage = null)
	{
		if (null === $sLanguage) {
			$sLanguage = $this->sLanguage;
		}

		$sFilename = sprintf($sFilename, $sLanguage);

		if (!file_exists($sFilename.'.lang.php')) {
			return false;
		}

		if (in_array($sFilename, $this->aLoaded))  {
			return null;
		}

		require $sFilename.'.lang.php';

		$this->aLoaded[] = $sFilename;

		return true;
	}
}
