<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Development;

class CountingFilesAndLines
{
	protected $sDirectoryPath;

	protected $iNumFolders = 0;
	protected $iNumFiles = 0;
	protected $iNumLines = 0;

	protected $bCounted;

	public $aExcludeExtensions = array('gif', 'jpg', 'jpeg', 'png', 'tft', 'bmp');
	public $aExcludeFiles = array();
	public $aExcludeFolders = array('.svn','upload');

	public function __construct($sDirectoryPath)
	{
		$this->sDirectoryPath = $sDirectoryPath;

		$this->iNumFolders = 0;
		$this->iNumFiles = 0;
		$this->iNumLines = 0;

		$this->bCounted = false;
	}

	public function getNumFolders()
	{
		$this->counting();
		return $this->iNumFolders;
	}

	public function getNumFiles()
	{
		$this->counting();
		return $this->iNumFiles;
	}

	public function getNumLines()
	{
		$this->counting();
		return $this->iNumLines;
	}

	protected function counting()
	{
		if ($this->bCounted) {
			return true;
		}

		ini_set('max_execution_time', 0);
		ini_set('memory_limit', -1);

		$this->recursiveCounting($this->sDirectoryPath);

		$this->bCounted = true;
	}

	protected function recursiveCounting($sDirectoryPath)
	{
		$oDirectory = dir($sDirectoryPath);

		while (($sFilename = $oDirectory->read()) !== false)
		{
			if ($sFilename == '.' || $sFilename == '..') {
				continue;
			}

			$sFile = $sDirectoryPath.'/'.$sFilename;

			if (is_dir($sFile) && !in_array($sFilename, $this->aExcludeFolders))
			{
				$this->iNumFolders++;
				$this->recursiveCounting($sFile);
			}
			elseif (is_file($sFile))
			{
				$this->iNumFiles++;

				$sExtension = pathinfo($sFile,PATHINFO_EXTENSION);

				if (!in_array($sExtension, $this->aExcludeExtensions) && !in_array($sFilename, $this->aExcludeFiles)) {
					$this->iNumLines += count(file($sFile));
				}
			}

		}

		$oDirectory->close();
	}

}
