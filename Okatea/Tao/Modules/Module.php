<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Modules;

/**
 * Définit un module Okatea.
 *
 */
class Module
{
	/**
	 * Okatea application instance.
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The database manager instance.
	 * @var object
	 */
	protected $db;

	/**
	 * The errors manager instance.
	 * @var object
	 */
	protected $error;

	/**
	 * Les informations concernant le module
	 * @var array
	 */
	public $infos;

	public $upload_dir;
	public $upload_url;

	/**
	 * Constructeur.
	 *
	 * @param object $okt		Okatea application instance.
	 * @return void
	 */
	public function __construct($okt)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		$this->infos = array(
			'id'		=> null,
			'root'		=> null,
			'name'		=> null,
			'version'	=> null,
			'desc'		=> null,
			'author'	=> null,
			'status'	=> null
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
	public function setInfos(array $infos = array())
	{
		foreach ($infos as $name=>$value) {
			$this->setInfo($name, $value);
		}
	}

	/**
	 * Définit une information donnée du module
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setInfo($name, $value)
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
		$define_file = $this->okt->options->get('modules_dir').'/'.$this->id().'/_define.php';

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
	public function register(array $aParams=array())
	{
		$this->setInfos(array(
			'root'			=> $this->okt->options->get('modules_dir').'/'.$this->id(),
			'name' 			=> (!empty($aParams['name']) 		? $aParams['name'] 					: $this->_id),
			'desc' 			=> (!empty($aParams['desc']) 		? $aParams['desc'] 					: null),
			'version' 		=> (!empty($aParams['version']) 	? $aParams['version'] 				: null),
			'author' 		=> (!empty($aParams['author']) 		? $aParams['author'] 				: null),
			'priority' 		=> (!empty($aParams['priority']) 	? (integer)$aParams['priority'] 	: 1000),
			'updatable' 	=> (!empty($aParams['updatable']) 	? (boolean)$aParams['updatable'] 	: true)
		));
	}


	/* Méthodes d'initialisation
	----------------------------------------------------------*/

	final public function init()
	{
		$this->okt->l10n->loadFile($this->root().'/Locales/'.$this->okt->user->language.'/main');

		$this->prepend();

		# répertoire upload
		$this->upload_dir = $this->okt->options->get('upload_dir').'/'.$this->getInfo('id');
		$this->upload_url = $this->okt->options->get('upload_url').'/'.$this->getInfo('id');
	}

	final public function initNs($ns)
	{
		if ($ns === 'admin') {
			$this->okt->l10n->loadFile($this->root().'/Locales/'.$this->okt->user->language.'/admin');
		}

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
}
