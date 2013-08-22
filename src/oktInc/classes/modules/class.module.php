<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class oktModule
 * @ingroup okt_classes_modules
 * @brief Définit un module.
 *
 */
class oktModule
{
	/**
	 * L'objet core.
	 * @var object oktCore
	 */
	protected $okt;

	/**
	 * L'objet gestionnaire de base de données.
	 * @var object
	 */
	protected $db;

	/**
	 * L'objet  gestionnaire d'erreurs
	 * @var object
	 */
	protected $error;

	/**
	 * Le nom de la table module
	 * @var string
	 */
	protected $t_module;

	/**
	 * Les informations concernant le module
	 * @var array
	 */
	public $infos;

	/**
	 * Constructeur.
	 *
	 * @param	object	okt		Instance d'un objet de type oktCore
	 * @return void
	 */
	public function __construct($okt)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		$this->t_module = $okt->db->prefix.'core_modules';

		$this->infos = array(
			'id'			=> null,
			'root'			=> null,
			'url'			=> null,
			'name'			=> null,
			'version'		=> null,
			'desc'			=> null,
			'author'		=> null,
			'status'		=> null
		);
	}

	/**
	 * Retourne une information du module
	 *
	 * @param string $info
	 * @return mixed
	 */
	public function getInfo($info)
	{
		if (isset($this->infos[$info])) {
			return $this->infos[$info];
		}

		return null;
	}

	/**
	 * Définit les informations du module
	 *
	 * @param array $infos
	 * @return void
	 */
	public function setInfos($infos=array())
	{
		foreach ($infos as $name=>$value) {
			$this->setInfo($name,$value);
		}
	}

	/**
	 * Définit une information donnée du module
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setInfo($name,$value)
	{
		$this->infos[$name] = $value;
	}

	/**
	 * Définit les infos du module d'après le fichier _define.php du module
	 *
	 * @return void
	 */
	public function setInfosFromDefineFile()
	{
		$define_file = path::real($this->okt->modules->path.'/'.$this->id().'/_define.php');

		if (file_exists($define_file)) {
			require $define_file;
		}
	}

	/**
	 * Cette fonction est utilisée dans les fichiers _define.php
	 * des modules pour qu'ils soient pris en compte par le système.
	 *
	 * Cette méthode reçoit en argument un tableau de paramètres,
	 * les paramètres possibles sont les suivants :
	 * 	- name 		Le nom de l'extension
	 * 	- desc 		La description de l'extension
	 * 	- version 	Le numero de version de l'extension
	 * 	- author 	L'auteur de l'extension ('')
	 * 	- priority 	Priorité de l'extension (1000)
	 * 	- updatable	Blocage de mise à jour (true)
	 *
	 * @param array $aParams 			Le tableau de paramètres
	 * @return void
	 */
	public function registerModule(array $aParams=array())
	{
		$this->setInfos(array(
			'root'			=> $this->okt->modules->path.'/'.$this->id(),
			'name' 			=> (!empty($aParams['name']) ? $aParams['name'] : $this->_id),
			'desc' 			=> (!empty($aParams['desc']) ? $aParams['desc'] : null),
			'version' 		=> (!empty($aParams['version']) ? $aParams['version'] : null),
			'author' 		=> (!empty($aParams['author']) ? $aParams['author'] : null),
			'priority' 		=> (!empty($aParams['priority']) ? (integer)$aParams['priority'] : 1000),
			'updatable' 	=> (!empty($aParams['updatable']) ? (boolean)$aParams['updatable'] : true)
		));
	}


	/* Méthodes d'initialisation
	----------------------------------------------------------*/

	public function init()
	{
		$this->prepend();
	}

	public function initNs($ns)
	{
		$this->{'prepend_'.$ns}();
	}

	protected function prepend()
	{
		return null;
	}

	protected function prepend_admin()
	{
		return null;
	}

	protected function prepend_public()
	{
		return null;
	}


	/* Méthodes d'information
	----------------------------------------------------------*/

	/**
	 * Retourne l'identifiant du module
	 *
	 * @return string
	 */
	public function id()
	{
		return $this->getInfo('id');
	}

	/**
	 * Retourne le nom du module
	 *
	 * @return string
	 */
	public function name()
	{
		return $this->getInfo('name');
	}

	/**
	 * Retourne la version du module
	 *
	 * @return string
	 */
	public function version()
	{
		return $this->getInfo('version');
	}

	/**
	 * Retourne la description du module
	 *
	 * @return string
	 */
	public function desc()
	{
		return $this->getInfo('desc');
	}

	/**
	 * Retourne l'auteur du module
	 *
	 * @return string
	 */
	public function author()
	{
		return $this->getInfo('author');
	}

	/**
	 * Retourne la priorité du module
	 *
	 * @return integer
	 */
	public function priority()
	{
		return intval($this->getInfo('priority'));
	}

	/**
	 * Retourne le statut du module
	 *
	 * @return integer
	 */
	public function status()
	{
		return intval($this->getInfo('status'));
	}

	/**
	 * Indique si le module est activé
	 *
	 * @return boolean
	 */
	public function isEnabled()
	{
		return (boolean)$this->getInfo('status');
	}

	/**
	 * Indique si le module est installé
	 *
	 * @return boolean
	 */
	public function isInstalled()
	{
		return true;
	}

	/**
	 * Retourne le chemin du répertoire du module
	 *
	 * @return string
	 */
	public function root()
	{
		return $this->getInfo('root');
	}

	/**
	 * Retourne l'URL du répertoire du module
	 *
	 * @return string
	 */
	public function url()
	{
		return $this->getInfo('url');
	}

	/**
	 * Retourne le nom internationnalisé en provenance de la config.
	 *
	 * @return string
	 */
	public function getName()
	{
		static $sName = false;

		if ($sName !== false) {
			return $sName;
		}

		if (!isset($this->config) || !isset($this->config->name)) {
			$sName = null;
		}
		elseif (is_array($this->config->name))
		{
			if (isset($this->config->name[$this->okt->user->language])) {
				$sName = $this->config->name[$this->okt->user->language];
			}
			elseif ($this->config->name[$this->okt->config->language]) {
				$sName = $this->config->name[$this->okt->config->language];
			}
		}
		else {
			$sName = $this->config->name;
		}

		return $sName;
	}

	/**
	 * Retourne le title internationnalisé en provenance de la config.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		static $sTitle = false;

		if ($sTitle !== false) {
			return $sTitle;
		}

		if (!isset($this->config) || !isset($this->config->title)) {
			$sTitle = null;
		}
		elseif (is_array($this->config->title))
		{
			if (isset($this->config->title[$this->okt->user->language])) {
				$sTitle = $this->config->title[$this->okt->user->language];
			}
			elseif ($this->config->title[$this->okt->config->language]) {
				$sTitle = $this->config->title[$this->okt->config->language];
			}
		}
		else {
			$sTitle = $this->config->title;
		}

		return $sTitle;
	}

	/**
	 * Retourne le titre SEO internationnalisé en provenance de la config.
	 *
	 * @return string
	 */
	public function getNameSeo()
	{
		static $sNameSeo = false;

		if ($sNameSeo !== false) {
			return $sNameSeo;
		}

		if (!isset($this->config) || !isset($this->config->title)) {
			$sNameSeo = null;
		}
		elseif (is_array($this->config->name_seo))
		{
			if (isset($this->config->name_seo[$this->okt->user->language])) {
				$sNameSeo = $this->config->name_seo[$this->okt->user->language];
			}
			elseif ($this->config->name_seo[$this->okt->config->language]) {
				$sNameSeo = $this->config->name_seo[$this->okt->config->language];
			}
		}
		else {
			$sNameSeo = $this->config->name_seo;
		}

		return $sNameSeo;
	}


	/* Méthodes utilitaires
	----------------------------------------------------------*/

	/**
	 * Indique si on est sur ce module coté admin
	 *
	 * @return void
	 */
	protected function onThisModule()
	{
		$upperId = strtoupper($this->id());

		if (OKT_FILENAME == 'module.php' && !empty($_REQUEST['m']) && $_REQUEST['m'] == $this->id()) {
			define('ON_'.$upperId.'_MODULE', true);
		}
		else {
			define('ON_'.$upperId.'_MODULE', false);
		}
	}

} # class oktModule
