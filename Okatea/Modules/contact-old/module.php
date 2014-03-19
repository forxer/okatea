<?php
/**
 * @ingroup okt_module_contact
 * @brief La classe principale du Module Contact.
 *
 */


use Okatea\Admin\Menu as AdminMenu;
use Okatea\Admin\Page;
use Okatea\Tao\Html\Modifiers;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Modules\Module;
use Okatea\Tao\Routing\Route;

class module_contact extends Module
{
	public $aPostedData = array();
	public $rsFields;

	public $config = null;
	protected $locales = null;

	protected $aRecipientsTo;
	protected $aRecipientsCc;
	protected $aRecipientsBcc;
	protected $mFromTo;
	protected $mReplyTo;
	protected $sSubject;
	protected $sSenderName;
	protected $sBody;

	protected $t_fields;
	protected $t_fields_locales;
	protected $params = array();

	protected static $aUnDeletableFields = array(1,2,3,4,5,6,7);
	protected static $aUnDisablableFields = array(4,6,7);

	protected function prepend()
	{
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'ContactController' => __DIR__.'/inc/ContactController.php',
			'ContactHelpers' => __DIR__.'/inc/ContactHelpers.php',
			'ContactRecordset' => __DIR__.'/inc/ContactRecordset.php'
		));

		# permissions
		$this->okt->addPermGroup('contact', __('m_contact_perm_group'));
			$this->okt->addPerm('contact_recipients', __('m_contact_perm_recipients'), 'contact');
			$this->okt->addPerm('contact_fields', __('m_contact_perm_fields'), 'contact');
			$this->okt->addPerm('contact_config', __('m_contact_perm_config'), 'contact');

		# tables
		$this->t_fields = $this->db->prefix.'mod_contact_fields';
		$this->t_fields_locales = $this->db->prefix.'mod_contact_fields_locales';

		# config
		$this->config = $this->okt->newConfig('conf_contact');
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->contactSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add(
				$this->getName(),
				'module.php?m=contact',
				$this->bCurrentlyInUse,
				2000,
				$this->okt->checkPerm('contact_recipients'),
				null,
				$this->okt->page->contactSubMenu,
				$this->okt->options->public_url.'/modules/'.$this->id().'/module_icon.png'
			);
				$this->okt->page->contactSubMenu->add(
					__('m_contact_menu_recipients'),
					'module.php?m=contact&amp;action=index',
					$this->bCurrentlyInUse && (!$this->okt->page->action || $this->okt->page->action === 'index'),
					1,
					$this->okt->checkPerm('contact_recipients')
				);
				$this->okt->page->contactSubMenu->add(
					__('m_contact_menu_fields'),
					'module.php?m=contact&amp;action=fields',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'fields' || $this->okt->page->action === 'field'),
					2,
					$this->okt->checkPerm('contact_fields')
				);
				$this->okt->page->contactSubMenu->add(
					__('m_contact_menu_configuration'),
					'module.php?m=contact&amp;action=config',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'config'),
					3,
					$this->okt->checkPerm('contact_config')
				);
		}
	}

	protected function prepend_public()
	{
		$this->okt->page->loadCaptcha($this->config->captcha);
	}


	/* Gestion des champs
	----------------------------------------------------------*/

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

		if (($rs = $this->db->select($query,'ContactRecordset')) === false)
		{
			$rs = new ContactRecordset(array());
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

		if (($rs = $this->db->select($query,'ContactRecordset')) === false) {
			$rs = new ContactRecordset(array());
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


	/* Méthodes de préparation des données pour l'envoi du mail
	----------------------------------------------------------*/

	/**
	 * Retourne les destinataires To.
	 *
	 * @return array
	 */
	public function getRecipientsTo()
	{
		if (empty($this->aRecipientsTo))
		{
			if (!empty($this->config->recipients_to)) {
				$this->aRecipientsTo = (array)$this->config->recipients_to;
			}

			if (empty($this->aRecipientsTo))
			{
				if (!empty($this->okt->config->email['name'])) {
					$this->aRecipientsTo = array($this->okt->config->email['to'] => html::escapeHTML($this->okt->config->email['name']));
				}
				else {
					$this->aRecipientsTo = array($this->okt->config->email['to']);
				}
			}
		}

		return $this->aRecipientsTo;
	}

	/**
	 * Définit les destinataires To.
	 *
	 * @param array $aRecipientsTo
	 * @return void
	 */
	public function setRecipientsTo($aRecipientsTo)
	{
		$this->aRecipientsTo = $aRecipientsTo;
	}

	/**
	 * Retourne les destinataires Cc.
	 *
	 * @return array
	 */
	public function getRecipientsCc()
	{
		if (empty($this->aRecipientsCc)) {
			$this->aRecipientsCc = !empty($this->config->recipients_cc) ? (array)$this->config->recipients_cc : array();
		}

		return $this->aRecipientsCc;
	}

	/**
	 * Définit les destinataires Cc.
	 *
	 * @param array $aRecipientsCc
	 * @return void
	 */
	public function setRecipientsCc($aRecipientsCc)
	{
		$this->aRecipientsCc = $aRecipientsCc;
	}

	/**
	 * Retourne les destinataires Bcc.
	 *
	 * @return array
	 */
	public function getRecipientsBcc()
	{
		if (empty($this->aRecipientsBcc)) {
			$this->aRecipientsBcc = !empty($this->config->recipients_bcc) ? (array)$this->config->recipients_bcc : array();
		}

		return $this->aRecipientsBcc;
	}

	/**
	 * Définit les destinataires Bcc.
	 *
	 * @param array $aRecipientsBcc
	 * @return void
	 */
	public function setRecipientsBcc($aRecipientsBcc)
	{
		$this->aRecipientsBcc = $aRecipientsBcc;
	}

	/**
	 * Retourne la valeur de FromTo.
	 *
	 * @return mixed
	 */
	public function getFromTo()
	{
		if (empty($this->mFromTo)) {
			$this->setFromToFromPostedData();
		}

		return $this->mFromTo;
	}

	/**
	 * Définit le FromTO.
	 *
	 * @param mixed $mFromTo
	 * @return void
	 */
	public function setFromTo($mFromTo)
	{
		$this->mFromTo = $mFromTo;
	}

	/**
	 * Définit le FromTO en fonction des données saisies dans le formulaire.
	 *
	 * @return void
	 */
	public function setFromToFromPostedData()
	{
		$this->mFromTo = $this->aPostedData[4];

		if (!empty($this->aPostedData[2]))
		{
			if (!empty($this->aPostedData[3])) {
				$this->mFromTo = array($this->aPostedData[4] => $this->aPostedData[3].' '.$this->aPostedData[2]);
			}
			else {
				$this->mFromTo = array($this->aPostedData[4] => $this->aPostedData[2]);
			}
		}
	}

	/**
	 * Retourne la valeur de ReplyTo.
	 *
	 * @return mixed
	 */
	public function getReplyTo()
	{
		if (empty($this->mReplyTo)) {
			$this->setReplyToFromPostedData();
		}

		return $this->mReplyTo;
	}

	/**
	 * Définit le ReplyTo.
	 *
	 * @param mixed $mReplyTo
	 * @return void
	 */
	public function setReplyTo($mReplyTo)
	{
		$this->mReplyTo = $mReplyTo;
	}

	/**
	 * Définit le ReplyTo en fonction des données saisies dans le formulaire.
	 *
	 * @return void
	 */
	public function setReplyToFromPostedData()
	{
		$this->mReplyTo = $this->aPostedData[4];

		if (!empty($this->aPostedData[2]))
		{
			if (!empty($this->aPostedData[3])) {
				$this->mReplyTo = array($this->aPostedData[4] => $this->aPostedData[3].' '.$this->aPostedData[2]);
			}
			else {
				$this->mReplyTo = array($this->aPostedData[4] => $this->aPostedData[2]);
			}
		}
	}

	/**
	 * Retourne le nom de l'expediteur.
	 *
	 * @return string
	 */
	public function getSenderName()
	{
		if (empty($this->sSenderName)) {
			$this->setSenderNameFromPostedData();
		}

		return (string)$this->sSenderName;
	}

	/**
	 * Définit le nom de l'expediteur.
	 *
	 * @param string $sSenderName
	 * @return void
	 */
	public function setSenderName($sSenderName)
	{
		$this->sSenderName = (string)$sSenderName;
	}

	/**
	 * Définit le nom de l'expediteur en fonction des données saisies dans le formulaire.
	 *
	 * @return void
	 */
	public function setSenderNameFromPostedData()
	{
		$this->sSenderName = '';

		if (isset($this->aPostedData[1]))
		{
			switch ($this->aPostedData[1])
			{
				case 0:
					$this->sSenderName .= 'Madame ';
				break;

				case 1:
					$this->sSenderName .= 'Mademoiselle ';
				break;

				case 2:
					$this->sSenderName .= 'Monsieur ';
				break;
			}
		}

		if (!empty($this->aPostedData[2])) {
			$this->sSenderName .= $this->aPostedData[2].' ';
		}

		if (!empty($this->aPostedData[3])) {
			$this->sSenderName .= $this->aPostedData[3];
		}
	}

	/**
	 * Retourne le sujet du mail.
	 *
	 * @return string
	 */
	public function getSubject()
	{
		if (empty($this->sSubject)) {
			$this->setSubjectFromPostedData();
		}

		return (string)$this->sSubject;
	}

	/**
	 * Définit le sujet du mail.
	 *
	 * @param string $sSubject
	 * @return void
	 */
	public function setSubject($sSubject)
	{
		$this->sSubject = (string)$sSubject;
	}

	/**
	 * Définit le sujet du mail en fonction des données saisies dans le formulaire.
	 *
	 * @return void
	 */
	public function setSubjectFromPostedData()
	{
		if (!empty($this->aPostedData[6])) {
			$this->sSubject = html::escapeHTML($this->aPostedData[6]);
		}
		else {
			$this->sSubject = 'Contact depuis le site internet '.html::escapeHTML($this->okt->page->getSiteTitle());
		}
	}

	/**
	 * Retourne le corps du mail.
	 *
	 * @return string
	 */
	public function getBody()
	{
		if (empty($this->sBody)) {
			$this->setBodyFromPostedData();
		}

		return (string)$this->sBody;
	}

	/**
	 * Définit le corps du mail.
	 *
	 * @param string $sSubject
	 * @return void
	 */
	public function setBody($sBody)
	{
		$this->sBody = (string)$sBody;
	}

	/**
	 * Définit le corps du mail en fonction des données saisies dans le formulaire.
	 *
	 * @return void
	 */
	public function setBodyFromPostedData()
	{
		$this->sBody = 'Contact depuis le site internet '.html::escapeHTML($this->okt->page->getSiteTitle()).
			' ['.$this->okt->request->getSchemeAndHttpHost().$this->okt->config->app_path.']'.PHP_EOL.PHP_EOL;

		$sSenderName = $this->getSenderName();
		if (!empty($sSenderName)) {
			$this->sBody .= 'Nom : '.$sSenderName.PHP_EOL;
		}

		$this->sBody .= 'E-mail : '.$this->aPostedData[4].PHP_EOL;

		if (!empty($this->aPostedData[5])) {
			$this->sBody .= 'Téléphone : '.$this->aPostedData[5].PHP_EOL;
		}

		$this->sBody .= PHP_EOL.'Sujet : '.$this->getSubject().PHP_EOL;

		$this->sBody .= 'Message : '.PHP_EOL.PHP_EOL;
		$this->sBody .= $this->aPostedData[7].PHP_EOL.PHP_EOL;

		# ajout des autres champs
		while ($this->rsFields->fetch())
		{
			if ($this->isDefaultField($this->rsFields->id)) {
				continue;
			}

			if (!empty($this->aPostedData[$this->rsFields->id]))
			{
				$sFieldValue = null;

				switch ($this->rsFields->type)
				{
					default:
					case 1 : # Champ texte
					case 2 : # Zone de texte
						$sFieldValue = $this->aPostedData[$this->rsFields->id];
					break;

					case 3 : # Menu déroulant
					case 4 : # Boutons radio
					case 5 : # Cases à cocher
						$aValues = array_filter((array)unserialize($this->rsFields->value));

						if(is_array($this->aPostedData[$this->rsFields->id])){
							$aFieldValue = array();
							foreach($this->aPostedData[$this->rsFields->id] as $value){
								if(isset($aValues[$value])){
									$aFieldValue[] = $aValues[$value];
								}
							}
							$sFieldValue = implode(', ', $aFieldValue);
						}else{
							$sFieldValue = (isset($aValues[$this->aPostedData[$this->rsFields->id]]) ? $aValues[$this->aPostedData[$this->rsFields->id]] : '');
						}
					break;
				}

				$this->sBody .= html::escapeHtml($this->rsFields->title).' : '.html::escapeHtml($sFieldValue).PHP_EOL;
			}
		}
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
	 * Retourne la liste des types de statuts au pluriel (masqués/visibles/obligatoires)
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public static function getFieldsStatuses($flip=false,$unDisablable=false)
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
	public static function getFieldsStatus($flip=false,$unDisablable=false)
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
	public static function getFieldsTypes($flip=false)
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
	 * Retourne la liste des ID des champs qui ne peuvent etres supprimés
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
		return !in_array($iFieldId,self::getUnDisablableFields());
	}

	/**
	 * Retourne la liste des ID des champs qui ne peuvent etres désactivés
	 *
	 * @return array
	 */
	public static function getUnDisablableFields()
	{
		return self::$aUnDisablableFields;
	}

	/**
	 * Retourne l'adresse de la société pour le plan Google Map.
	 * Si les coordonnées GPS  sont remplies, elles prennent le pas sur l'adresse complète.
	 *
	 * @return string
	 */
	public function getAdressForGmap()
	{
		if ($this->okt->config->gps['lat'] != '' && $this->okt->config->gps['long'] != '')
		{
			return $this->okt->config->gps['lat'].', '.$this->okt->config->gps['long'];
		}
		else
		{
			$sAdressForGmap =
				$this->okt->config->address['street'].' '.
				(!empty($this->okt->config->address['street_2']) ? $this->okt->config->address['street_2'].' ' : '').
				$this->okt->config->address['code'].' '.
				$this->okt->config->address['city'].' '.
				$this->okt->config->address['country'];

			return str_replace(',', '', $sAdressForGmap);
		}
	}

	public function genImgMail()
	{
		$font = $this->okt->options->public_dir.'/fonts/OpenSans/OpenSans-Regular.ttf';
		$size = ($this->config->mail_size * 72) / 96;
		$image_src = $this->okt->options->public_dir.'/img/misc/empty.png';

		# Génération de l'image de base
		list($width_orig, $height_orig) = getimagesize($image_src);
		$image_in = imagecreatefrompng($image_src);
		imagealphablending($image_in, false);
		imagesavealpha($image_in, true);

		# Calcul de l'espace que prendra le texte
		$aParam = imageftbbox($size,0,$font,$this->okt->config->email['to']);
		$dest_w = $aParam[4] - $aParam[6] + 2;
		$dest_h = $aParam[1] - $aParam[7] + 2;

		# Génération de l'image final
		$image_out = imagecreatetruecolor($dest_w, $dest_h);
		imagealphablending($image_out, false);
		imagesavealpha($image_out, true);
		imagecopyresampled($image_out, $image_in, 0,0,0,0, $dest_w, $dest_h, $width_orig, $height_orig);

		# Ajout du texte dans l'image
		$txt_color = imagecolorallocate($image_out, hexdec(substr($this->config->mail_color,0,2)), hexdec(substr($this->config->mail_color,2,2)), hexdec(substr($this->config->mail_color,4,2)));
		imagettftext($image_out,$size,0,0,12,$txt_color,$font,$this->okt->config->email['to']);

		# Génération du src de l'image et destruction des ressources
		ob_start();
		imagepng($image_out, null, 9);
		$contenu_image = ob_get_contents();
		ob_end_clean();

		imagedestroy($image_in);
		imagedestroy($image_out);

		return "data:image/png;base64,".base64_encode($contenu_image);
	}
}
