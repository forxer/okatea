<?php
/**
 * @ingroup okt_module_users
 * @brief Users custom fields management.
 *
 */

use Tao\Misc\Utilities as util;

class UsersCustomFields
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

	protected $t_fields;
	protected $t_fields_locales;
	protected $t_fields_values;

	public function __construct($okt)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		$this->t_fields = $this->db->prefix.'mod_users_fields';
		$this->t_fields_locales = $this->db->prefix.'mod_users_fields_locales';
		$this->t_fields_values = $this->db->prefix.'mod_users_fields_values';
	}

	/**
	 * Retourne, sous forme de recordset, les champs personnalisés selon des paramètres donnés.
	 *
	 * @param	array	params			Paramètres de requete
	 * @return UsersFieldRecordset
	 */
	public function getFields($aParams=array())
	{
		$reqPlus = '';

		if (!empty($aParams['id'])) {
			$reqPlus .= ' AND f.id='.(integer)$aParams['id'].' ';
		}

		if (!empty($aParams['status'])) {
			$reqPlus .= ' AND f.status>0 ';
		}

		if (!empty($aParams['register'])) {
			$reqPlus .= ' AND f.register_status=1 ';
		}

		if (!empty($aParams['user_editable'])) {
			$reqPlus .= ' AND f.user_editable=1 ';
		}

		if (!empty($aParams['admin_editable'])) {
			$reqPlus .= ' AND f.user_editable=0 ';
		}

		if (!empty($aParams['language'])) {
			$reqPlus .= 'AND fl.language=\''.$this->db->escapeStr($aParams['language']).'\' ';
		}

		$query =
		'SELECT f.id, f.status, f.register_status, f.user_editable, f.type, f.ord, f.html_id, f.options, '.
			'fl.title, fl.description, fl.value '.
		'FROM '.$this->t_fields.' f '.
			'LEFT JOIN '.$this->t_fields_locales.' AS fl ON fl.field_id=f.id '.
		'WHERE 1 '.
		$reqPlus.
		'ORDER BY ord ASC ';

		if (($rs = $this->db->select($query,'UsersFieldRecordset')) === false)
		{
			$rs = new UsersFieldRecordset(array());
			$rs->setCore($this->okt);
			return $rs;
		}

		$rs->setCore($this->okt);
		return $rs;
	}

	/**
	 * Retourne, sous forme de recordset, un champs personnalisé donné.
	 *
	 * @param integer $iFieldId
	 * @return recordset
	 */
	public function getField($iFieldId)
	{
		return $this->getFields(array('id'=>$iFieldId));
	}

	/**
	 * Indique si un champ personnalisé donné existe.
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
	 * Retourne les localisations d'un champ personnalisé donné.
	 *
	 * @param integer $iFieldId
	 * @return recordset
	 */
	public function getFieldI18n($iFieldId)
	{
		$query =
		'SELECT field_id, language, title, value, description '.
		'FROM '.$this->t_fields_locales.' '.
		'WHERE field_id='.(integer)$iFieldId;

		if (($rs = $this->db->select($query,'UsersFieldRecordset')) === false) {
			$rs = new UsersFieldRecordset(array());
			$rs->setCore($this->okt);
			return $rs;
		}

		$rs->setCore($this->okt);
		return $rs;
	}

	/**
	 * Ajout d'un champ personnalisé.
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
			'status, register_status, user_editable, type, ord, html_id '.
		' ) VALUES ( '.
			(integer)$aFieldData['status'].', '.
			(integer)$aFieldData['register_status'].', '.
			(integer)$aFieldData['user_editable'].', '.
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
	 * Modification d'un champ personnalisé donné.
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

		$query =
		'UPDATE '.$this->t_fields.' SET '.
			'status='.(integer)$aFieldData['status'].', '.
			'register_status='.(integer)$aFieldData['register_status'].', '.
			'user_editable='.(integer)$aFieldData['user_editable'].', '.
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
	 * Met à jour la valeur d'un champs personnalisé donné.
	 *
	 * @param integer $iFieldId
	 * @param string $aValues
	 * @return boolean
	 */
	public function setFieldValue($iFieldId, $aValues)
	{
		$rsField = $this->getField($iFieldId);

		if ($rsField->isEmpty()) {
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
	 * @param integer $iOrd
	 * @return boolean
	 */
	public function updFieldOrder($iFieldId,$iOrd)
	{
		if (!$this->fieldExists($iFieldId)) {
			return false;
		}

		$query =
		'UPDATE '.$this->t_fields.' SET '.
		'ord='.(integer)$iOrd.' '.
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
	 * Définition des valeurs d'un champ personnalisé pour un utilisateur donné.
	 *
	 * @param integer $iUserId
	 * @param integer $iFieldId
	 * @param mixed $mValue
	 * @return boolean
	 */
	public function setUserValues($iUserId, $iFieldId, $mValue)
	{
		$query =
		'INSERT INTO '.$this->t_fields_values.' '.
			'(user_id, field_id, value) '.
		'VALUES ('.
			(integer)$iUserId.', '.
			(integer)$iFieldId.', '.
			(empty($mValue) ? 'NULL' : '\''.$this->db->escapeStr($mValue).'\'').' '.
		') ON DUPLICATE KEY UPDATE '.
			'value='.(empty($mValue) ? 'NULL' : '\''.$this->db->escapeStr($mValue).'\'');

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Récupération des valeurs des champs personnalisés pour un utilisateur donné.
	 *
	 * @param integer $iUserId
	 * @return recordset
	 */
	public function getUserValues($iUserId)
	{
		$query =
		'SELECT field_id, value '.
		'FROM '.$this->t_fields_values.' '.
		'WHERE user_id = '.(integer)$iUserId;

		if (($rs = $this->db->select($query)) === false)
		{
			$rs = new recordset(array());
			return $rs;
		}

		return $rs;
	}

	/**
	 * Suppression des valeurs des champs personnalisés d'un utilisateur donné.
	 *
	 * @param integer $iUserId
	 * @return boolean
	 */
	public function delUserValue($iUserId)
	{
		$query =
		'DELETE FROM '.$this->t_fields_values.' '.
		'WHERE user_id='.(integer)$iUserId;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->db->optimize($this->t_fields_values);

		return true;
	}


	/* Utilitaires
	----------------------------------------------------------*/

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

		$html_id = util::strToUnderscored($html_id,false);

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

			$html_id = util::getIncrementedString($a, $html_id, '-');
		}

		# URL is empty?
		if ($html_id == '') {
			throw new Exception(__('m_users_Empty_HTML_ID'));
		}

		return $html_id;
	}

	public function getPostData($rsFields, &$aPostedData)
	{

		while ($rsFields->fetch())
		{
			switch ($rsFields->type)
			{
				default:
				case 1 : # Champ texte
				case 2 : # Zone de texte
					$aPostedData[$rsFields->id] = !empty($_POST[$rsFields->html_id]) ? $_POST[$rsFields->html_id] : '';
				break;

				case 3 : # Menu déroulant
					$aPostedData[$rsFields->id] = isset($_POST[$rsFields->html_id]) ? $_POST[$rsFields->html_id] : '';
					break;

				case 4 : # Boutons radio
					$aPostedData[$rsFields->id] = isset($_POST[$rsFields->html_id]) ? $_POST[$rsFields->html_id] : '';
					break;

				case 5 : # Cases à cocher
					$aPostedData[$rsFields->id] = !empty($_POST[$rsFields->html_id]) && is_array($_POST[$rsFields->html_id]) ? $_POST[$rsFields->html_id] : '';
					break;
			}

			if ($rsFields->status == 2 && empty($aPostedData[$rsFields->id])) {
				$this->error->set('Vous devez renseigner le champ "'.html::escapeHtml($rsFields->title).'".');
			}
		}
	}

	/**
	 * Retourne la liste des types de statuts au pluriel (masqués/visibles/obligatoires)
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public static function getFieldsStatuses($flip=false)
	{
		$aStatus = array(
			0 => __('m_users_masked'),
			1 => __('m_users_visible'),
			2 => __('m_users_mandatory')
		);

		if ($flip) {
			$aStatus = array_flip($aStatus);
		}

		return $aStatus;
	}

	/**
	 * Retourne la liste des types de statuts au singulier (masqué/visible/obligatoire)
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public static function getFieldsStatus($flip=false)
	{
		$aStatus = array(
			0 => __('m_users_masked'),
			1 => __('m_users_visible'),
			2 => __('m_users_mandatory')
		);

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
	public static function getFieldsTypes($flip=false)
	{
		$aTypes = array(
			1 => __('m_users_Text_fields'),
			2 => __('m_users_Text_aeras'),
			3 => __('m_users_drop_down_menu'),
			4 => __('m_users_Radio_buttons'),
			5 => __('m_users_check_boxes')
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


}