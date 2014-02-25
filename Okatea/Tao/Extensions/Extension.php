<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Extensions;

class Extension
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
	 * Le chemin du répertoire des extensions
	 * @var string
	 */
	protected $sExtensionsPath;

	/**
	 * Les informations concernant l'extension
	 * @var array
	 */
	protected $infos;

	/**
	 * Constructeur.
	 *
	 * @param object $okt Okatea application instance.
	 * @param string $sExtensionsPath Le chemin du répertoire des extensions.
	 * @return void
	 */
	public function __construct($okt, $sExtensionsPath)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		$this->sExtensionsPath = $sExtensionsPath;

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
	 * Retourne une information de l'extension.
	 *
	 * @param string $sKey
	 * @return mixed
	 */
	public function getInfo($sKey)
	{
		if (isset($this->infos[$sKey])) {
			return $this->infos[$sKey];
		}

		return null;
	}

	/**
	 * Définit une information donnée d'une extension
	 *
	 * @param string $sKey
	 * @param mixed $mValue
	 * @return void
	 */
	public function setInfo($sKey, $mValue)
	{
		$this->infos[$sKey] = $mValue;
	}

	/**
	 * Définit les informations d'une extension
	 *
	 * @param array $aInfos
	 * @return void
	 */
	public function setInfos(array $aInfos = array())
	{
		foreach ($aInfos as $sKey => $mValue) {
			$this->setInfo($sKey, $mValue);
		}
	}

	/**
	 * Définit les infos de l'extension d'après le fichier _define.php
	 *
	 * @return void
	 */
	public function setInfosFromDefineFile()
	{
		$define = $this->sExtensionsPath.'/'.$this->id().'/_define.php';

		if (file_exists($define)) {
			require $define;
		}
	}

	/**
	 * Cette fonction est utilisée dans les fichiers _define.php
	 * des extensions pour qu'elles soient prises en compte par le système.
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
	public function register(array $aParams = array())
	{
		$this->setInfos(array(
			'root'			=> $this->sExtensionsPath.'/'.$this->id(),
			'name' 			=> (!empty($aParams['name']) 		? $aParams['name'] 					: $this->_id),
			'desc' 			=> (!empty($aParams['desc']) 		? $aParams['desc'] 					: null),
			'version' 		=> (!empty($aParams['version']) 	? $aParams['version'] 				: null),
			'author' 		=> (!empty($aParams['author']) 		? $aParams['author'] 				: null),
			'priority' 		=> (!empty($aParams['priority']) 	? (integer)$aParams['priority'] 	: 1000),
			'updatable' 	=> (!empty($aParams['updatable']) 	? (boolean)$aParams['updatable'] 	: true)
		));
	}

	public function init()
	{
		$this->okt->l10n->loadFile($this->root().'/Locales/'.$this->okt->user->language.'/main');

		$this->prepend();
	}

	public function initNs($ns)
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

	/**
	 * Retourne l'identifiant de l'extension.
	 *
	 * @return string
	 */
	final public function id()
	{
		return $this->getInfo('id');
	}

	/**
	 * Retourne le nom de l'extension.
	 *
	 * @return string
	 */
	final public function name()
	{
		return $this->getInfo('name');
	}

	/**
	 * Retourne la version de l'extension.
	 *
	 * @return string
	 */
	final public function version()
	{
		return $this->getInfo('version');
	}

	/**
	 * Retourne la description de l'extension.
	 *
	 * @return string
	 */
	final public function desc()
	{
		return $this->getInfo('desc');
	}

	/**
	 * Retourne l'auteur de l'extension.
	 *
	 * @return string
	 */
	final public function author()
	{
		return $this->getInfo('author');
	}

	/**
	 * Retourne la priorité de l'extension.
	 *
	 * @return integer
	 */
	final public function priority()
	{
		return intval($this->getInfo('priority'));
	}

	/**
	 * Retourne le statut de l'extension.
	 *
	 * @return integer
	 */
	final public function status()
	{
		return intval($this->getInfo('status'));
	}

	/**
	 * Indique si l'extension est activée.
	 *
	 * @return boolean
	 */
	final public function isEnabled()
	{
		return (boolean)$this->getInfo('status');
	}

	/**
	 * Retourne le chemin du répertoire de l'extension.
	 *
	 * @return string
	 */
	final public function root()
	{
		return $this->getInfo('root');
	}
}
