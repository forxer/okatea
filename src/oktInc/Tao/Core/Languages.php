<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Core;

/**
 * Le gestionnnaire de langues.
 *
 */
class Languages
{
	/**
	 * Liste des langues
	 * @var array
	 */
	public $list;

	/**
	 * Nombre de langues
	 * @var integer
	 */
	public $num;

	/**
	 * Langue unique
	 * @var boolean
	 */
	public $unique;

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
	 * L'objet gestionnaire d'erreurs
	 * @var object
	 */
	protected $error;

	/**
	 * Le nom de la table languages
	 * @var string
	 */
	protected $t_languages;

	/**
	 * L'objet gestionnaire de cache
	 * @var object
	 */
	protected $cache;

	/**
	 * L'identifiant du cache
	 * @var string
	 */
	protected $cache_id;

	/**
	 * Constructor.
	 *
	 * @param oktCore $okt
	 * @return void
	 */
	public function __construct($okt)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		$this->cache = $okt->cache;
		$this->cache_id = 'languages';

		$this->t_languages = $okt->db->prefix.'core_languages';
	}

	/**
	 * Charge la liste des langues actives.
	 *
	 * @return void
	 */
	public function load()
	{
		if (!$this->cache->contains($this->cache_id)) {
			$this->generateCacheList();
		}

		$this->list = $this->cache->fetch($this->cache_id);

		$this->num = count($this->list);
		$this->unique = (boolean)($this->num == 1);
	}

	/**
	 * Indique si une langue donnée est active.
	 *
	 * @param string $lang
	 * return boolean
	 */
	public function isActive($lang)
	{
		return array_key_exists($lang, $this->list);
	}

	/**
	 * Retourne l'identifiant d'une langue selon son code.
	 *
	 * @param string $code
	 * @return integer
	 */
	public function getIdByCode($code)
	{
		return isset($this->list[$code]) ? $this->list[$code]['id'] : false;
	}

	/**
	 * Retourne le d'une langue selon son identifiant.
	 *
	 * @param string $id
	 * @return integer
	 */
	public function getCodeById($id)
	{
		foreach ($this->list as $lang)
		{
			if ($lang['id'] == $id) {
				return $lang['code'];
			}
		}
	}

	/**
	 * Génère le fichier cache de la liste des langues actives.
	 *
	 * @return boolean
	 */
	public function generateCacheList()
	{
		$aLanguagesList = array();

		$list = $this->getLanguages(array('active'=>1));

		while ($list->fetch())
		{
			$aLanguagesList[$list->f('code')] = array(
				'id' => (integer)$list->f('id'),
				'title' => $list->f('title'),
				'code' => $list->f('code'),
				'img' => $list->f('img')
			);
		}

		return $this->cache->save($this->cache_id, $aLanguagesList);
	}

	/**
	 * Retourne, sous forme de recordset, la liste des langues selon des paramètres donnés.
	 *
	 * @param	array	params			Paramètres de requete
	 * @return Tao\Database\Recordset
	 */
	public function getLanguages($params=array(), $count_only=false)
	{
		$reqPlus = '';

		if (!empty($params['id'])) {
			$reqPlus .= ' AND id='.(integer)$params['id'].' ';
		}

		if (!empty($params['title'])) {
			$reqPlus .= ' AND title=\''.$this->db->escapeStr($params['title']).'\' ';
		}

		if (!empty($params['code'])) {
			$reqPlus .= ' AND code=\''.$this->db->escapeStr($params['code']).'\' ';
		}

		if (!empty($params['active'])) {
			$reqPlus .= ' AND active='.(integer)$params['active'].' ';
		}

		if ($count_only)
		{
			$query =
			'SELECT COUNT(id) AS num_languages '.
			'FROM '.$this->t_languages.' '.
			'WHERE 1 '.
			$reqPlus;
		}
		else
		{
			$query =
			'SELECT id, title, code, img, active, ord '.
			'FROM '.$this->t_languages.' '.
			'WHERE 1 '.
			$reqPlus;

			if (!empty($params['order'])) {
				$query .= 'ORDER BY '.$params['order'].' ';
			}
			else {
				$query .= 'ORDER BY ord ASC ';
			}

			if (!empty($params['limit'])) {
				$query .= 'LIMIT '.$params['limit'].' ';
			}
		}

		if (($rs = $this->db->select($query)) === false) {
			return new Tao\Database\Recordset(array());
		}

		if ($count_only) {
			return (integer)$rs->num_languages;
		}
		else {
			return $rs;
		}
	}

	/**
	 * Retourne, sous forme de recordset, une langue donnée.
	 *
	 * @param integer $id
	 * @return Tao\Database\Recordset
	 */
	public function getLanguage($id)
	{
		return $this->getLanguages(array('id'=>$id));
	}

	/**
	 * Indique si une langue donnée existe.
	 *
	 * @param integer $id
	 * @param boolean
	 */
	public function languageExists($id)
	{
		if ($this->getLanguage($id)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Ajout d'une langue.
	 *
	 * @param array $aData
	 * @return integer
	 */
	public function addLanguage($aData=array())
	{
		$query = 'SELECT MAX(ord) FROM '.$this->t_languages;
		$rs = $this->db->select($query);
		if ($rs->isEmpty()) {
			return false;
		}
		$max_ord = $rs->f(0);

		$query =
		'INSERT INTO '.$this->t_languages.' ( '.
			'title, code, img, active, ord '.
		' ) VALUES ( '.
			'\''.$this->db->escapeStr($aData['title']).'\', '.
			'\''.$this->db->escapeStr(\html::clean($aData['code'])).'\', '.
			'\''.$this->db->escapeStr($aData['img']).'\', '.
			(integer)$aData['active'].', '.
			(integer)($max_ord+1).
		'); ';

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->afterProcess();

		return $this->db->getLastID();
	}

	/**
	 * Mise à jour d'une langue.
	 *
	 * @param array $aData
	 * @return boolean
	 */
	public function updLanguage($aData=array())
	{
		$query =
		'UPDATE '.$this->t_languages.' SET '.
			'title=\''.$this->db->escapeStr($aData['title']).'\', '.
			'code=\''.$this->db->escapeStr(\html::clean($aData['code'])).'\', '.
			'img=\''.$this->db->escapeStr($aData['img']).'\', '.
			'active='.(integer)$aData['active'].' '.
		'WHERE id='.(integer)$aData['id'];

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->afterProcess();

		return true;
	}

	/**
	 * Vérifie les données envoyées dans les formulaires.
	 *
	 * @param array $aData
	 * @return boolean
	 */
	public function checkPostData($aData=array())
	{
		if (empty($aData['title'])) {
			$this->error->set('Vous devez saisir un titre de langue.');
		}

		if (empty($aData['code'])) {
			$this->error->set('Vous devez saisir un code de langue.');
		}

		return $this->error->isEmpty();
	}

	/**
	 * Switch le statut d'une langue donnée.
	 *
	 * @param integer $id
	 * @return boolean
	 */
	public function switchLangStatus($id)
	{
		if (!$this->languageExists($id)) {
			return false;
		}

		$query =
		'UPDATE '.$this->t_languages.' SET '.
			'active = 1-active '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->afterProcess();

		return true;
	}

	/**
	 * Définit le statut d'une langue donnée.
	 *
	 * @param integer $id
	 * @param integer $iStatus
	 * @return boolean
	 */
	public function setLangStatus($id, $iStatus)
	{
		if (!$this->languageExists($id)) {
			return false;
		}

		$iStatus = ($iStatus == 1) ? 1 : 0;

		$query =
		'UPDATE '.$this->t_languages.' SET '.
		'active = '.$iStatus.' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->afterProcess();

		return true;
	}

	/**
	 * Met à jour la position d'une langue donnée.
	 *
	 * @param integer $id	ID langue
	 * @param integer $ord	Ordre
	 * @return boolean
	 */
	public function updLanguageOrder($id,$ord)
	{
		$query =
		'UPDATE '.$this->t_languages.' SET '.
			'ord='.(integer)$ord.' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Suppression d'une langue.
	 *
	 * @param integer $id
	 * @return boolean
	 */
	public function delLanguage($id)
	{
		if (!$this->languageExists($id)) {
			return false;
		}

		$query =
		'DELETE FROM '.$this->t_languages.' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->db->optimize($this->t_languages);

		$this->afterProcess();

		return true;
	}

	protected function afterProcess()
	{
		$this->generateCacheList();

		$this->okt->router->touchResources();
	}
}
