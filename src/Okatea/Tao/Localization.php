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
    protected $aLoaded;
    
    /**
     * Initialize this primary class.
     * 
     * @param string $sLocalesDir
     * @param string $sLanguage
     * @param string $sTimeZone
     */
	public function __construct($sLocalesDir, $sLanguage, $sTimeZone)
	{
	//	date_default_timezone_set($sTimeZone);

	    $GLOBALS['okt_l10n'] = array();
		$this->aLoaded = array();

		$this->loadFile($sLocalesDir.'/'.$sLanguage.'/main');
		$this->loadFile($sLocalesDir.'/'.$sLanguage.'/date');
		$this->loadFile($sLocalesDir.'/'.$sLanguage.'/users');
	}

	/**
	 * Load a l10n file.
	 * 
	 * @param string $sFilename    The file to bi loaded.
	 * @return boolean|NULL
	 */
	public function loadFile($sFilename)
	{
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
