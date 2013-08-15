<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class oktSimpleLogs
 * @ingroup okt_classes_tools
 * @brief Gestion et manipulation simple de logs
 *
 */


class oktSimpleLogs
{
	/**
	 * Le chemin du répertoire des logs.
	 * @var string
	 */
	protected $sLogDir;


	/**
	 * Constructor.
	 *
	 * @param string $sLogDir
	 */
	public function __construct($sLogDir=null)
	{
		if (is_null($sLogDir)) {
			$sLogDir = OKT_LOG_PATH;
		}

		$this->sLogDir = $sLogDir;

		if (!is_dir($this->sLogDir)) {
			files::makeDir($this->sLogDir,true);
		}
	}

	/**
	 * Retourne une instance du KLogger.
	 *
	 * @param integer $severity
	 * @return KLogger
	 */
	public function getLogger($severity=KLogger::INFO)
	{
		return new KLogger($this->sLogDir, $severity);
	}

	/**
	 * Retourne la liste des fichiers de logs.
	 *
	 * @param boolean $bReverse
	 * @return array
	 */
	public function getLogsFiles($bReverse=false)
	{
		$aLogsFiles = array();

		if (is_dir($this->sLogDir))
		{
			foreach (new DirectoryIterator($this->sLogDir) as $oFileInfo)
			{
				if ($oFileInfo->isDot() || in_array($oFileInfo->getFilename(),array('.svn','.htaccess','index.html'))) {
					continue;
				}

				$aLogsFiles[] = $oFileInfo->getFilename();
			}

			natsort($aLogsFiles);

			if ($bReverse) {
				$aLogsFiles = array_reverse($aLogsFiles);
			}
		}

		return $aLogsFiles;
	}

	/**
	 * Suppression de tous les fichiers de log.
	 *
	 * @return boolean
	 */
	public function delLogs($iMonths=null)
	{
		$aLogsFiles = $this->getLogsFiles();

		if (!is_null($iMonths))
		{
			$iTime = strtotime('-'.intval($iMonths).' months');

			foreach ($aLogsFiles as $k=>$sLogFilename)
			{
				if (self::getTimestampFromLogFilename($sLogFilename) > $iTime) {
					unset($aLogsFiles[$k]);
				}
			}
		}

		foreach ($aLogsFiles as $sLogFilename) {
			$this->delLog($sLogFilename);
		}

		return true;
	}

	/**
	 * Suppression d'un fichier de log.
	 *
	 * @param string $sLogFilename
	 * @return boolean
	 */
	public function delLog($sLogFilename)
	{
		$sLogFilePath = $this->sLogDir.'/'.$sLogFilename;

		if (file_exists($sLogFilePath)) {
			return unlink($sLogFilePath);
		}
	}

	/**
	 * Retourne le contenu d'un fichier de log.
	 *
	 * @param string $sLogFilename
	 * @return string
	 */
	public function getLogContent($sLogFilename)
	{
		$sLogFilePath = $this->sLogDir.'/'.$sLogFilename;

		if (file_exists($sLogFilePath)) {
			return file_get_contents($sLogFilePath);
		}
	}

	/**
	 * Retourne la date d'un log à partir d'un nom de fichier.
	 *
	 * @param string $sLogFilename
	 * @return string
	 */
	public static function getDateFromLogFilename($sLogFilename)
	{
		return str_replace(array('log_','.txt'), array('',''), basename($sLogFilename));
	}

	/**
	 * Retourne le timestamp d'un log à partir d'un nom de fichier.
	 *
	 * @param string $sLogFilename
	 * @return string
	 */
	public static function getTimestampFromLogFilename($sLogFile)
	{
		return strtotime(self::getDateFromLogFilename($sLogFile));
	}

	/**
	 * Retourne le nom de fichier d'un log à partir d'une date.
	 *
	 * @param string $sLogFile
	 * @return string
	 */
	public static function getLogFilenameFromDate($sDate)
	{
		return 'log_'.date('Y-m-d', strtotime($sDate)).'.txt';
	}
}
