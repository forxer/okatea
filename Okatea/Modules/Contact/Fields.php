<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Contact;

use Exception;
use Okatea\Tao\Database\Recordset;
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

	protected $aFieldData = array();

	protected static $aUnDeletableFields = array(1, 2, 3, 4, 5, 6, 7);
	protected static $aUnDisablableFields = array(4, 6, 7);

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
	 * Retourne, sous forme de recordset, les champs selon des paramètres donnés.
	 *
	 * @param array $params
	 * @return \Okatea\Modules\Contact\FieldsRecordset
	 */
	public function getFields(array $params = array())
	{
		$reqPlus = '';

		if (!empty($params['id'])) {
			$reqPlus .= ' AND id='.(integer)$params['id'].' ';
		}

		if (!empty($params['status'])) {
			$reqPlus .= ' AND status>0 ';
		}

		if (!empty($params['language'])) {
			$reqPlus .= 'AND fl.language=\''.$this->db->escapeStr($params['language']).'\' ';
		}

		$query =
		'SELECT f.id, f.status, f.type, f.ord, f.html_id, fl.title, fl.description, fl.value '.
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
	 * Retourne, sous forme de recordset, un champs donné.
	 *
	 * @param integer $iFieldId
	 * @return \Okatea\Modules\Contact\FieldsRecordset
	 */
	public function getField($iFieldId)
	{
		return $this->getFields(array('id' => $iFieldId));
	}

	/**
	 * Indique si un champ donné existe.
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
	 * Retourne les localisations d'un champ donné.
	 *
	 * @param integer $iFieldId
	 * @return \Okatea\Tao\Database\Recordset
	 */
	public function getFieldL10n($iFieldId)
	{
		$query =
		'SELECT language, title, value, description '.
		'FROM '.$this->t_fields_locales.' '.
		'WHERE field_id='.(integer)$iFieldId;

		if (($rs = $this->db->select($query)) === false) {
			return new Recordset(array());
		}

		return $rs;
	}

	/**
	 * Ajout d'un champ.
	 *
	 * @param array $aFieldData
	 * @return integer
	 */
	public function addField($aFieldData)
	{
		$this->aFieldData = $aFieldData;

		$query = 'SELECT MAX(ord) FROM '.$this->t_fields;
		$rs = $this->db->select($query);
		if ($rs->isEmpty()) {
			return false;
		}
		$max_ord = $rs->f(0);

		$query =
		'INSERT INTO '.$this->t_fields.' ( '.
			'status, type, ord, html_id '.
		' ) VALUES ( '.
			(integer)$this->aFieldData['status'].', '.
			(integer)$this->aFieldData['type'].', '.
			(integer)($max_ord+1).', '.
			'\''.$this->db->escapeStr($this->aFieldData['html_id']).'\' '.
		'); ';

		if (!$this->db->execute($query)) {
			return false;
		}

		# récupération de l'ID
		$this->aFieldData['id'] = $this->db->getLastID();

		# modification des textes internationalisés
		if (!$this->setFieldL10n()) {
			return false;
		}

		# modification de l'ID HTML
		if ($this->setFieldHtmlId() === false) {
			return false;
		}

		return $this->aFieldData['id'];
	}

	/**
	 * Modification d'un champ donné.
	 *
	 * @param array $aFieldData
	 * @return boolean
	 */
	public function updField($aFieldData)
	{
		if (!$this->fieldExists($aFieldData['id'])) {
			$this->error->set(sprintf(__('m_contact_field_%s_not_exists'), $aFieldData['id']));
			return false;
		}

		$this->aFieldData = $aFieldData;

		$this->aFieldData['status'] = (integer)$this->aFieldData['status'];

		if ($this->aFieldData['status'] == 0 && in_array($aFieldData['id'], self::getUnDisablableFields())) {
			$this->aFieldData['status'] = 1;
		}

		$query =
		'UPDATE '.$this->t_fields.' SET '.
			'status='.(integer)$this->aFieldData['status'].', '.
			'type='.(integer)$this->aFieldData['type'].', '.
			'html_id=\''.$this->db->escapeStr($this->aFieldData['html_id']).'\' '.
		'WHERE id='.(integer)$aFieldData['id'];

		if (!$this->db->execute($query)) {
			return false;
		}

		# modification des textes internationalisés
		if (!$this->setFieldL10n()) {
			return false;
		}

		# modification de l'ID HTML
		if ($this->setFieldHtmlId() === false) {
			return false;
		}

		return true;
	}

	public function checkPostData($aFieldData)
	{
		$aFieldData['status'] = intval($aFieldData['status']);
		if (!array_key_exists($aFieldData['status'], self::getFieldsStatus())) {
			$this->error->set(__('m_contact_field_error_status'));
		}

		$aFieldData['type'] = intval($aFieldData['type']);
		if (!array_key_exists($aFieldData['type'], self::getFieldsTypes())) {
			$this->error->set(__('m_contact_field_error_type'));
		}

		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (empty($aFieldData['locales'][$aLanguage['code']]['title']))
			{
				if ($this->okt->languages->unique) {
					$this->error->set(__('m_contact_field_error_title'));
				}
				else {
					$this->error->set(sprintf(__('m_contact_field_error_title_in_%s'), $aLanguage['title']));
				}
			}
		}
	}

	/**
	 * Met à jour les valeurs d'un champs donné.
	 *
	 * @param integer $iFieldId
	 * @param array $aValues
	 * @return boolean
	 */
	public function setFieldValues($iFieldId, $aValues)
	{
		$rsField = $this->getField($iFieldId);

		if ($rsField->isEmpty()) {
			$this->error->set(sprintf(__('m_contact_field_%s_not_exists'), $iFieldId));
			return false;
		}

		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (!$rsField->isSimpleField()) {
				$aValues[$aLanguage['code']] = serialize($aValues[$aLanguage['code']]);
			}

			$query =
			'INSERT INTO '.$this->t_fields_locales.' '.
				'(field_id, language, value) '.
			'VALUES ('.
				(integer)$iFieldId.', '.
				'\''.$this->db->escapeStr($aLanguage['code']).'\', '.
				(empty($aValues[$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($aValues[$aLanguage['code']]).'\'').' '.
			') ON DUPLICATE KEY UPDATE '.
				'value='.(empty($aValues[$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($aValues[$aLanguage['code']]).'\'');

			if (!$this->db->execute($query)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Enregistrement des textes internationalisés.
	 *
	 * @return boolean
	 */
	protected function setFieldL10n()
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$query =
			'INSERT INTO '.$this->t_fields_locales.' '.
				'(field_id, language, title, description) '.
			'VALUES ('.
				(integer)$this->aFieldData['id'].', '.
				'\''.$this->db->escapeStr($aLanguage['code']).'\', '.
				(empty($this->aFieldData['locales'][$aLanguage['code']]['title']) ? 'NULL' : '\''.$this->db->escapeStr($this->aFieldData['locales'][$aLanguage['code']]['title']).'\'').', '.
				(empty($this->aFieldData['locales'][$aLanguage['code']]['description']) ? 'NULL' : '\''.$this->db->escapeStr($this->aFieldData['locales'][$aLanguage['code']]['description']).'\'').' '.
			') ON DUPLICATE KEY UPDATE '.
				'title='.(empty($this->aFieldData['locales'][$aLanguage['code']]['title']) ? 'NULL' : '\''.$this->db->escapeStr($this->aFieldData['locales'][$aLanguage['code']]['title']).'\'').', '.
				'description='.(empty($this->aFieldData['locales'][$aLanguage['code']]['description']) ? 'NULL' : '\''.$this->db->escapeStr($this->aFieldData['locales'][$aLanguage['code']]['description']).'\'');

			if (!$this->db->execute($query)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Met à jour la position d'un champ donné.
	 *
	 * @param integer $iFieldId
	 * @param integer $ord
	 * @return boolean
	 */
	public function updFieldOrder($iFieldId, $ord)
	{
		if (!$this->fieldExists($iFieldId)) {
			$this->error->set(sprintf(__('m_contact_field_%s_not_exists'), $iFieldId));
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
	 * Suppression d'un champ donné.
	 *
	 * @param integer $iFieldId
	 * @return boolean
	 */
	public function deleteField($iFieldId)
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
	 * Création de l'ID HTML d'un champ donné.
	 *
	 * @param integer $post_id
	 * @return boolean
	 */
	protected function setFieldHtmlId()
	{
		$rsField = $this->getField($this->aFieldData['id']);

		if ($rsField->isEmpty()) {
			$this->error->set(sprintf(__('m_contact_field_%s_not_exists'), $this->aFieldData['id']));
			return false;
		}

		$html_id = $this->buildFieldHtmlId($rsField->title, $rsField->html_id, $this->aFieldData['id']);

		$query =
		'UPDATE '.$this->t_fields.' SET '.
			'html_id=\''.$this->db->escapeStr($html_id).'\' '.
		'WHERE id='.(integer)$this->aFieldData['id'];

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Construit l'ID HTML d'un champ donné.
	 *
	 * @param string $title
	 * @param string $html_id
	 * @param integer $iFieldId
	 * @return string
	 */
	protected function buildFieldHtmlId($title, $html_id, $iFieldId)
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
			throw new Exception(__('m_contact_Empty_HTML_ID'));
		}

		return $html_id;
	}


	/**
	 * Retourne la liste des types de statuts au pluriel (masqués/visibles/obligatoires).
	 *
	 * @param boolean $bFlip
	 * @return array
	 */
	public static function getFieldsStatuses($bFlip = false, $unDisablable = false)
	{
		$aStatus = array(
			0 => __('m_contact_fields_statuses_0'),
			1 => __('m_contact_fields_statuses_1'),
			2 => __('m_contact_fields_statuses_2')
		);

		if ($unDisablable) {
			unset($aStatus[0]);
		}

		if ($bFlip) {
			$aStatus = array_flip($aStatus);
		}

		return $aStatus;
	}

	/**
	 * Retourne la liste des types de statuts au singulier (masqué/visible/obligatoire).
	 *
	 * @param boolean $bFlip
	 * @param boolean $unDisablable
	 * @return array
	 */
	public static function getFieldsStatus($bFlip = false, $unDisablable = false)
	{
		$aStatus = array(
			0 => __('m_contact_fields_status_0'),
			1 => __('m_contact_fields_status_1'),
			2 => __('m_contact_fields_status_2')
		);

		if ($unDisablable) {
			unset($aStatus[0]);
		}

		if ($bFlip) {
			$aStatus = array_flip($aStatus);
		}

		return $aStatus;
	}

	/**
	 * Retourne la liste des types de champs.
	 *
	 * @param boolean $bFlip
	 * @return array
	 */
	public static function getFieldsTypes($bFlip = false)
	{
		$aTypes = array(
			1 => __('m_contact_fields_type_1'),
			2 => __('m_contact_fields_type_2'),
			3 => __('m_contact_fields_type_3'),
			4 => __('m_contact_fields_type_4'),
			5 => __('m_contact_fields_type_5')
		);

		if ($bFlip) {
			$aTypes = array_flip($aTypes);
		}

		return $aTypes;
	}

	/**
	 * Indique de quelle forme est le type. Simple (Champ texte et Zone de texte)
	 * ou multiple (Menu déroulant, Cases à cocher ou Boutons radio).
	 *
	 * @param integer $iType
	 */
	public static function getFormType($iType)
	{
		if ($iType == 1 || $iType == 2) {
			return 'simple';
		}
		else {
			return 'multiple';
		}
	}

	public static function isSimpleType($iType)
	{
		if ($iType == 1 || $iType == 2) {
			return true;
		}

		return false;
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
