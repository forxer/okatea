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

	protected $sDefaultLanguage;

	protected $aLoaded;

	/**
	 * Initialize this primary class.
	 *
	 * @param string $sLanguage
	 * @param string $sDefaultLanguage
	 * @param string $sTimeZone
	 */
	public function __construct($sLanguage, $sDefaultLanguage, $sTimeZone)
	{
		$this->sLanguage = $sLanguage;

		$this->sDefaultLanguage = $sDefaultLanguage;

	//	date_default_timezone_set($sTimeZone);

		$GLOBALS['okt_l10n'] = array();
		$this->aLoaded = array();
	}

	/**
	 * Load a l10n file.
	 *
	 * @param string $sFilename 	The file to be loaded.
	 * @param string $sLanguage 	Force loading specific language.
	 * @return boolean|null
	 */
	public function loadFile($sFilename, $sLanguage = null)
	{
		if (null === $sLanguage) {
			$sLanguage = $this->sLanguage;
		}

		$sFileToLoad = sprintf($sFilename, $sLanguage).'.lang.php';

		if (!file_exists($sFileToLoad))
		{
			if ($sLanguage !== $this->sDefaultLanguage)
			{
				$sFileToLoad = sprintf($sFilename, $this->sDefaultLanguage).'.lang.php';

				if (!file_exists($sFileToLoad)) {
					return false;
				}
			}

			return false;
		}

		if (in_array($sFileToLoad, $this->aLoaded))  {
			return null;
		}

		require $sFileToLoad;

		$this->aLoaded[] = $sFileToLoad;

		return true;
	}
}
