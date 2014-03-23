<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Contact;

use Okatea\Tao\Html\Modifiers;
use Okatea\Tao\Misc\Utilities;

class Fields
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

	protected $t_fields;

	protected $t_fields_locales;

	protected $params = array();

	protected static $aUnDeletableFields = array(1,2,3,4,5,6,7);
	protected static $aUnDisablableFields = array(4,6,7);

	/**
	 * Constructeur.
	 *
	 * @param object $okt Okatea application instance.
	 * @return void
	 */
	public function __construct($okt)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		# tables
		$this->t_fields = $this->db->prefix.'mod_contact_fields';
		$this->t_fields_locales = $this->db->prefix.'mod_contact_fields_locales';

	}

	/**
	 * Retourne, sous forme de recordset, les champs selon des paramètres donnés
	 *
	 * @param	array	params			Paramètres de requete
	 * @return recordset
	 */
	public function getFields($params=array())
	{
		$reqPlus = '';

		if (!empty($params['id'])) {
			$reqPlus .= ' AND id='.(integer)$params['id'].' ';
		}

		if (!empty($params['active'])) {
			$reqPlus .= ' AND active>0 ';
		}

		if (!empty($params['language'])) {
			$reqPlus .= 'AND fl.language=\''.$this->db->escapeStr($params['language']).'\' ';
		}

		$query =
		'SELECT f.id, f.active, f.type, f.ord, f.html_id, fl.title, fl.description, fl.value '.
		'FROM '.$this->t_fields.' f '.
		'LEFT JOIN '.$this->t_fields_locales.' AS fl ON fl.field_id=f.id '.
		'WHERE 1 '.
		$reqPlus.
		'ORDER BY ord ASC ';

		if (($rs = $this->db->select($query, 'Okatea\Modules\Contact\FieldsRecordset')) === false)
		{
			$rs = new FieldsRecordset(array());
			$rs->setCore($this->okt);
			return $rs;
		}

		$rs->setCore($this->okt);
		return $rs;
	}

	/**
	 * Retourne, sous forme de recordset, un champs donné
	 *
	 * @param integer $id
	 * @return recordset
	 */
	public function getField($iFieldId)
	{
		return $this->getFields(array('id'=>$iFieldId));
	}

	/**
	 * Indique si un champ donné existe
	 *
	 * @param integer $iFieldId
	 * @param boolean
	 */
	public function fieldExists($iFieldId)
	{
		if ($this->getField($iFieldId)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Retourne les localisations d'un champ donné
	 *
	 * @param integer $iFieldId
	 * @return recordset
	 */
	public function getFieldI18n($iFieldId)
	{
		$query =
		'SELECT language, title, value, description '.
		'FROM '.$this->t_fields_locales.' '.
		'WHERE field_id='.(integer)$iFieldId;

		if (($rs = $this->db->select($query, 'Okatea\Modules\Contact\FieldsRecordset')) === false) {
			$rs = new FieldsRecordset(array());
			$rs->setCore($this->okt);
			return $rs;
		}

		$rs->setCore($this->okt);
		return $rs;
	}

	/**
	 * Ajout d'un champ
	 *
	 * @param array $aFieldData
	 * @return integer
	 */
	public function addField($aFieldData)
	{
		$this->params = $aFieldData;

		$query = 'SELECT MAX(ord) FROM '.$this->t_fields;
		$rs = $this->db->select($query);
		if ($rs->isEmpty()) {
			return false;
		}
		$max_ord = $rs->f(0);

		$query =
		'INSERT INTO '.$this->t_fields.' ( '.
			'active, type, ord, html_id '.
		' ) VALUES ( '.
			(integer)$aFieldData['active'].', '.
			(integer)$aFieldData['type'].', '.
			(integer)($max_ord+1).', '.
			'\''.$this->db->escapeStr($aFieldData['html_id']).'\' '.
		'); ';

		if (!$this->db->execute($query)) {
			return false;
		}

		# récupération de l'ID
		$iNewId = $this->db->getLastID();
		$this->params['id'] = $iNewId;

		# modification des textes internationalisés
		if (!$this->setFieldI18n()) {
			return false;
		}

		# modification de l'ID HTML
		if ($this->setFieldHtmlId($iNewId) === false) {
			return false;
		}

		return $iNewId;
	}

	/**
	 * Modification d'un champ donné
	 *
	 * @param integer $iFieldId
	 * @param array $aFieldData
	 * @return boolean
	 */
	public function updField($iFieldId, $aFieldData)
	{
		if (!$this->fieldExists($iFieldId)) {
			return false;
		}

		$aFieldData['active'] = (integer)$aFieldData['active'];

		if ($aFieldData['active'] == 0 && in_array($iFieldId,self::getUnDisablableFields())) {
			$aFieldData['active'] = 1;
		}

		$query =
		'UPDATE '.$this->t_fields.' SET '.
		'active='.(integer)$aFieldData['active'].', '.
		'type='.(integer)$aFieldData['type'].', '.
		'html_id=\''.$this->db->escapeStr($aFieldData['html_id']).'\' '.
		'WHERE id='.(integer)$iFieldId;

		if (!$this->db->execute($query)) {
			return false;
		}

		# modification des textes internationalisés
		$this->params = $aFieldData;
		$this->params['id'] = (integer)$iFieldId;
		if (!$this->setFieldI18n()) {
			return false;
		}

		# modification de l'ID HTML
		if ($this->setFieldHtmlId($iFieldId) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Met à jour la valeur d'un champs donné
	 *
	 * @param integer $iFieldId
	 * @param string $value
	 * @return boolean
	 */
	public function setFieldValue($iFieldId,$value)
	{
		$rsField = $this->getField($iFieldId);

		if ($rsField->isEmpty()) {
			return false;
		}

		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (!$rsField->isSimpleField()) {
				$value[$aLanguage['code']] = serialize($value[$aLanguage['code']]);
			}

			$query =
			'INSERT INTO '.$this->t_fields_locales.' '.
				'(field_id, language, value) '.
			'VALUES ('.
				(integer)$iFieldId.', '.
				'\''.$this->db->escapeStr($aLanguage['code']).'\', '.
				(empty($value[$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($value[$aLanguage['code']]).'\'').' '.
			') ON DUPLICATE KEY UPDATE '.
				'value='.(empty($value[$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($value[$aLanguage['code']]).'\'');

			if (!$this->db->execute($query)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Enregistrement des textes internationalisés
	 *
	 * @return boolean
	 */
	protected function setFieldI18n()
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$query =
			'INSERT INTO '.$this->t_fields_locales.' '.
				'(field_id, language, title, description) '.
			'VALUES ('.
				(integer)$this->params['id'].', '.
				'\''.$this->db->escapeStr($aLanguage['code']).'\', '.
				(empty($this->params['title'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['title'][$aLanguage['code']]).'\'').', '.
				(empty($this->params['description'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['description'][$aLanguage['code']]).'\'').' '.
			') ON DUPLICATE KEY UPDATE '.
				'title='.(empty($this->params['title'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['title'][$aLanguage['code']]).'\'').', '.
				'description='.(empty($this->params['description'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['description'][$aLanguage['code']]).'\'');

			if (!$this->db->execute($query)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Met à jour la position d'un champ donné
	 *
	 * @param integer $iFieldId
	 * @param integer $ord
	 * @return boolean
	 */
	public function updFieldOrder($iFieldId,$ord)
	{
		if (!$this->fieldExists($iFieldId)) {
			return false;
		}

		$query =
		'UPDATE '.$this->t_fields.' SET '.
		'ord='.(integer)$ord.' '.
		'WHERE id='.(integer)$iFieldId;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Suppression d'un champ donné
	 *
	 * @param integer $iFieldId
	 * @return boolean
	 */
	public function delField($iFieldId)
	{
		if (!$this->fieldExists($iFieldId)) {
			return false;
		}

		if (!$this->isDeletable($iFieldId)) {
			return false;
		}

		$query =
		'DELETE FROM '.$this->t_fields.' '.
		'WHERE id='.(integer)$iFieldId;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->db->optimize($this->t_fields);

		return true;
	}

	/**
	 * Création de l'ID HTML d'un champ donné
	 *
	 * @param integer $post_id
	 * @return boolean
	 */
	protected function setFieldHtmlId($iFieldId)
	{
		$rs = $this->getField($iFieldId);

		$html_id = $this->buildFieldHtmlId($rs->title,$rs->html_id,$iFieldId);

		$query =
		'UPDATE '.$this->t_fields.' SET '.
		'html_id=\''.$this->db->escapeStr($html_id).'\' '.
		'WHERE id='.(integer)$iFieldId;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Construit l'ID HTML d'un champ donné
	 *
	 * @param string $title
	 * @param string $html_id
	 * @param integer $iFieldId
	 * @return string
	 */
	protected function buildFieldHtmlId($title,$html_id,$iFieldId)
	{
		if (empty($html_id)) {
			$html_id = $title;
		}

		$html_id = Modifiers::strToUnderscored($html_id,false);

		# Let's check if URL is taken…
		$query =
		'SELECT html_id FROM '.$this->t_fields.' '.
		'WHERE html_id=\''.$this->db->escapeStr($html_id).'\' '.
		'AND id <> '.(integer)$iFieldId. ' '.
		'ORDER BY html_id DESC';

		$rs = $this->db->select($query);

		if (!$rs->isEmpty())
		{
			$query =
			'SELECT html_id FROM '.$this->t_fields.' '.
			'WHERE html_id LIKE \''.$this->db->escapeStr($html_id).'%\' '.
			'AND id <> '.(integer)$iFieldId. ' '.
			'ORDER BY html_id DESC ';

			$rs = $this->db->select($query);
			$a = array();
			while ($rs->fetch()) {
				$a[] = $rs->html_id;
			}

			$html_id = Utilities::getIncrementedString($a, $html_id, '-');
		}

		# URL is empty?
		if ($html_id == '') {
			throw new \Exception(__('m_contact_Empty_HTML_ID'));
		}

		return $html_id;
	}


	/**
	 * Retourne la liste des types de statuts au pluriel (masqués/visibles/obligatoires)
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public static function getFieldsStatuses($flip = false, $unDisablable = false)
	{
		$aStatus = array(
			0 => __('m_contact_masked'),
			1 => __('m_contact_visible'),
			2 => __('m_contact_mandatory')
		);

		if ($unDisablable) {
			unset($aStatus[0]);
		}

		if ($flip) {
			$aStatus = array_flip($aStatus);
		}

		return $aStatus;
	}

	/**
	 * Retourne la liste des types de statuts au singulier (masqué/visible/obligatoire)
	 *
	 * @param boolean $flip
	 * @param boolean $unDisablable
	 * @return array
	 */
	public static function getFieldsStatus($flip = false, $unDisablable = false)
	{
		$aStatus = array(
			0 => __('m_contact_masked'),
			1 => __('m_contact_visible'),
			2 => __('m_contact_mandatory')
		);

		if ($unDisablable) {
			unset($aStatus[0]);
		}

		if ($flip) {
			$aStatus = array_flip($aStatus);
		}

		return $aStatus;
	}

	/**
	 * Retourne la liste des types de champs
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public static function getFieldsTypes($flip = false)
	{
		$aTypes = array(
			1 => __('m_contact_Text_fields'),
			2 => __('m_contact_Text_aeras'),
			3 => __('m_contact_drop_down_menu'),
			4 => __('m_contact_Radio_buttons'),
			5 => __('m_contact_check_boxes')
		);

		if ($flip) {
			$aTypes = array_flip($aTypes);
		}

		return $aTypes;
	}

	/**
	 * Indique de quelle forme est le type. Simple (Champ texte et Zone de texte)
	 * ou multiple (Menu déroulant, Cases à cocher ou Boutons radio).
	 *
	 * @param integer $type
	 */
	public static function getFormType($type)
	{
		if ($type == 1 || $type == 2) {
			return 'simple';
		}
		else {
			return 'multiple';
		}
	}

	/**
	 * Indique si un champ donné est supprimable.
	 *
	 * @param integer $iFieldId
	 * @return boolean
	 */
	public function isDeletable($iFieldId)
	{
		return !in_array($iFieldId, self::getUnDeletableFields());
	}

	/**
	 * Indique si un champ donné est un champs par défaut.
	 *
	 * @param integer $iFieldId
	 * @return boolean
	 */
	public function isDefaultField($iFieldId)
	{
		return in_array($iFieldId, self::getUnDeletableFields());
	}

	/**
	 * Retourne la liste des ID des champs qui ne peuvent êtres supprimés.
	 *
	 * @return array
	 */
	public static function getUnDeletableFields()
	{
		return self::$aUnDeletableFields;
	}

	/**
	 * Indique si un champ donné est désactivable.
	 *
	 * @param integer $iFieldId
	 * @return boolean
	 */
	public function isDisablable($iFieldId)
	{
		return !in_array($iFieldId, self::getUnDisablableFields());
	}

	/**
	 * Retourne la liste des ID des champs qui ne peuvent êtres désactivés.
	 *
	 * @return array
	 */
	public static function getUnDisablableFields()
	{
		return self::$aUnDisablableFields;
	}
}
