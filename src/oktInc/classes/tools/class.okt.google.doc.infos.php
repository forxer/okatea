<?php
/**
 * @class oktConfig
 * @ingroup okt_classes_core
 * @brief Gestion des infos du google document.
 *
 */

set_include_path(get_include_path() . PATH_SEPARATOR . OKT_VENDOR_PATH.'/ZendGdata/library');

class oktGoogleDocInfos
{
	private $sCacheFile;

	private $sCacheTtl = '-6 hours';

	private $oGoogleSpreadsheet = null;

	private $aData = null;

	public $sHttpHost;

	/**
	 * Consdtruteur
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->sHttpHost = str_replace('www.','',$_SERVER['HTTP_HOST']);

		$this->sCacheFile = OKT_CACHE_PATH.'/googleDocInfos.php';
	}

	public function getGoogleSpreadsheet()
	{
		if ($this->oGoogleSpreadsheet === null)
		{
			$this->oGoogleSpreadsheet = new googleSpreadsheet(
				OKT_GOOGLE_DOC_USERNAME, OKT_GOOGLE_DOC_PASSWORD,
				OKT_GOOGLE_DOC_SPREADSHEET, OKT_GOOGLE_DOC_WORKSHEET
			);
		}

		return $this->oGoogleSpreadsheet;
	}

	/**
	 * Retourne les données
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function get($name=null)
	{
		if ($this->aData === null) {
			$this->loadData();
		}

		if ($name === null) {
			return $this->aData;
		}

		if (isset($this->aData[$name])) {
			return $this->aData[$name];
		}

		return null;
	}

	/**
	 * Ajoute les données
	 */
	public function add()
	{
		$this->prepareData();

		$this->getGoogleSpreadsheet()->addRow($this->aData);
		$this->generateCacheFile();
	}

	/**
	 * Met à jour les données
	 */
	public function update()
	{
		$this->prepareData();

		$this->getGoogleSpreadsheet()->updateRow($this->aData,'url='.$this->sHttpHost);
		$this->generateCacheFile();
	}

	/**
	 * Magic get
	 *
	 * @see getData()
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * Magic set
	 *
	 */
	public function __set($name,$value)
	{
		$this->aData[$name] = $value;
	}

	/**
	 * Magic isset
	 *
	 */
	public function __isset($name)
	{
		return isset($this->aData[$name]);
	}

	/**
	 * Magic unset
	 *
	 */
	public function __unset($name)
	{
		if (isset($this->aData[$name])) {
			unset($this->aData[$name]);
		}
	}

	/**
	 * Charge les données depuis le fichier de cache.
	 *
	 * @return void
	 */
	private function loadData()
	{
		if (!file_exists($this->sCacheFile) || filemtime($this->sCacheFile) < strtotime($this->sCacheTtl)) {
			$this->generateCacheFile();
		}

		require $this->sCacheFile;

		if (isset($this->aData['modules']))
		{
			if (!is_array($this->aData['modules'])) {
				$this->aData['modules'] = explode(',',$this->aData['modules']);
			}
		}
		else {
			$this->aData['modules'] = array();
		}
	}

	/**
	 * Ecrit le fichier de cache
	 *
	 * @return void
	 */
	private function generateCacheFile()
	{
		return file_put_contents($this->sCacheFile,
			"<?php ".PHP_EOL.
			"# ".PHP_EOL.
			"# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!".PHP_EOL.
			"#  /!\ NE MODIFIEZ PAS MANUELLEMENT LES FICHIERS CACHE /!\    ".PHP_EOL.
			"#         /!\ DO NOT EDIT MANUALLY CACHE FILES /!\            ".PHP_EOL.
			"# !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!".PHP_EOL.
			"# ".PHP_EOL.
			"# Generated on ".date('r').PHP_EOL.
			"# ".PHP_EOL.
			PHP_EOL.PHP_EOL.
			'$this->aData = '.var_export($this->getData(),true).
			";".PHP_EOL
		);
	}

	private function getData()
	{
		$aGData = $this->getGoogleSpreadsheet()->getRows('url='.$this->sHttpHost);

		if (isset($aGData[0])) {
			return $aGData[0];
		}

		return array();
	}

	private function prepareData()
	{
		if (empty($this->aData['url'])) {
			$this->aData['url'] = $this->sHttpHost;
		}

		if (empty($this->aData['intervenants'])) {
			$this->aData['intervenants'] = http::realIP();
		}

		if (isset($this->aData['modules']) && is_array($this->aData['modules'])) {
			$this->aData['modules'] = implode(',',$this->aData['modules']);
		}

		if (!empty($this->aData['loginsudo']) && !empty($this->aData['passesudo']))
		{
			$this->aData['interfacesudo'] =
				'=HYPERLINK("'.$this->aData['url'].$this->aData['chemin'].OKT_ADMIN_DIR.'/connexion.php?user_id='.
				$this->aData['loginsudo'].'&user_pwd='.$this->aData['passesudo'].'"; "Interface super-admin")';
		}

		if (!empty($this->aData['loginadmin']) && !empty($this->aData['passeadmin']))
		{
			$this->aData['interfaceadmin'] =
				'=HYPERLINK("'.$this->aData['url'].$this->aData['chemin'].OKT_ADMIN_DIR.'/connexion.php?user_id='.
				$this->aData['loginadmin'].'&user_pwd='.$this->aData['passeadmin'].'"; "Interface admin")';
		}
	}

} # class
