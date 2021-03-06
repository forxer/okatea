<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\L10n;

/**
 * Very simple localization management.
 */
class Localization
{
	protected $sLanguage;

	protected $sDefaultLanguage;

	protected $sTimeZone;

	protected $aLoaded;

	/**
	 * Set default l10n configuration.
	 *
	 * @param string $sLanguage
	 * @param string $sDefaultLanguage
	 * @param string $sTimeZone
	 */
	public function __construct($sLanguage, $sDefaultLanguage, $sTimeZone)
	{
		$this->setLanguage($sLanguage);

		$this->setDefaultLanguage($sDefaultLanguage);

		$this->setTimeZone($sTimeZone);

		$GLOBALS['okt_l10n'] = [];
		$this->aLoaded = [];
	}

	public function setLanguage($sLanguage)
	{
		$this->sLanguage = $sLanguage;

		Date::setUserLocale($sLanguage);
	}

	public function getLanguage()
	{
		return $this->sLanguage;
	}

	public function setDefaultLanguage($sDefaultLanguage)
	{
		$this->sDefaultLanguage = $sDefaultLanguage;
	}

	public function getDefaultLanguage()
	{
		return $this->sDefaultLanguage;
	}

	public function setTimeZone($sTimeZone)
	{
		$this->sTimeZone = $sTimeZone;

		Date::setUserTimezone($sTimeZone);
	}

	public function getTimeZone()
	{
		return $this->sTimeZone;
	}

	/**
	 * Load a translations file.
	 *
	 * @param string $sFilename The file to be loaded.
	 * @param string $sLanguage Force loading specific language.
	 * @param string $bForce Force loading file.
	 * @return boolean|null
	 */
	public function loadFile($sFilename, $sLanguage = null, $bForce = false)
	{
		if (null === $sLanguage) {
			$sLanguage = $this->sLanguage;
		}

		$sFileToLoad = sprintf($sFilename, $sLanguage) . '.lang.php';

		if (!file_exists($sFileToLoad))
		{
			if ($sLanguage === $this->sDefaultLanguage) {
				return false;
			}

			$sFileToLoad = sprintf($sFilename, $this->sDefaultLanguage) . '.lang.php';

			if (!file_exists($sFileToLoad)) {
				return false;
			}
		}

		if (in_array($sFileToLoad, $this->aLoaded) && !$bForce) {
			return null;
		}

		require $sFileToLoad;

		$this->aLoaded[] = $sFileToLoad;

		return true;
	}
}
