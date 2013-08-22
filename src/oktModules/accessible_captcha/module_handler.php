<?php
/**
 * @ingroup okt_module_accessible_captcha
 * @brief La classe principale du module captcha accessible
 *
 */


class module_accessible_captcha extends oktModule
{
	/**
	 * La pile de questions
	 * @var array
	 */
	protected $captcha_questions;

	/**
	 * La position du curseur dans la pile
	 * @var integer
	 */
	protected $captcha_index;

	/**
	 * Les questions prêtes à être affichées
	 * @var array
	 */
	protected $questions;

	/**
	 * La question encodée
	 * @var array
	 */
	protected $qencoded;

	/**
	 * Le nom de la table
	 * @var string
	 */
	protected $t_captcha;

	protected function prepend()
	{
		# chargement des principales locales
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# permissions
		$this->okt->addPerm('accessible_captcha_config', __('m_accessible_captcha_perm_config'), 'configuration');

		# config
		$this->config = $this->okt->newConfig('conf_accessible_captcha');

		# table
		$this->t_captcha = $this->db->prefix.'mod_accessible_captcha_question';

		# enregistrement dans la pile de captcha disponibles
		$this->okt->page->addCaptcha('accessible_captcha',__('Accessible Captcha'), array(

			# behaviors page contact
			'publicModuleContactControllerStart' => array('module_accessible_captcha','publicControllerStart'),
			'publicModuleContactControllerFormCheckValues' => array('module_accessible_captcha','publicControllerFormCheckValues'),
			'publicModuleContactJsValidateRules' => array('module_accessible_captcha','publicJsValidateRules'),
			'publicModuleContactTplFormBottom' => array('module_accessible_captcha','publicTplFormBottom'),

			# behaviors livre d'or
			'publicModuleGuestbookControllerStart' => array('module_accessible_captcha','publicControllerStart'),
			'publicModuleGuestbookControllerFormCheckValues' => array('module_accessible_captcha','publicControllerFormCheckValues'),
			'publicModuleGuestbookJsValidateRules' => array('module_accessible_captcha','publicJsValidateRules'),
			'publicModuleGuestbookTplFormBottom' => array('module_accessible_captcha','publicTplFormBottom')
		));
	}

	protected function prepend_admin()
	{
		# on détermine si on est actuellement sur ce module
		$this->onThisModule();

		# chargement des locales admin
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/admin');

		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->configSubMenu->add(
				__('Accessible Captcha'),
				'module.php?m=accessible_captcha&amp;action=index',
				ON_ACCESSIBLE_CAPTCHA_MODULE && (!$this->okt->page->action || $this->okt->page->action === 'index'),
				30,
				$this->okt->checkPerm('accessible_captcha_config'),
				null
			);
		}

	}


	/*
	 * Gestion du captcha
	 */

	/**
	 * Initialisaion du captcha.
	 */
	public function initQuestion()
	{
		$this->captcha_questions = array();

		$rs = $this->get($this->okt->user->language);

		if (!$rs->isEmpty())
		{
			while ($rs->fetch()) {
				$this->captcha_questions[$rs->question] = $rs->reponse;
			}

			$this->captcha_index = rand(0,count($this->captcha_questions)-1);
			$this->questions = array_keys($this->captcha_questions);
			$this->qencoded = sha1($this->questions[$this->captcha_index]);
		}
	}

	/**
	 * Vérification du captcha
	 *
	 * @param 	string 	question		La question encodée
	 * @param	string	answer			La réponse
	 * @return boolean
	 */
	public function check($question, $answer)
	{
		$questions_arry = array();
		foreach ($this->captcha_questions as $k=>$v) {
			$questions_arry[sha1($k)] = $v;
		}

		if (empty($questions_arry[$question]) || $questions_arry[$question] != $answer) {
			return false;
		}

		return true;
	}

	/**
	 * Retourne la question du captcha
	 *
	 * @return string
	 */
	public function getQuestion()
	{
		return $this->questions[$this->captcha_index];
	}

	/**
	 * Retourne la question encodée
	 *
	 * @return string
	 */
	public function getEncQuestion()
	{
		return $this->qencoded;
	}

	/**
	 * Retourne les questions/réponses sous forme de recordset.
	 *
	 * @param string $sLanguageCode
	 * @return recordset
	 */
	public function get($sLanguageCode=null)
	{
		return $this->getFromDb($sLanguageCode);
	}


	protected function getFromDb($sLanguageCode=null)
	{
		$query = 'SELECT * FROM '.$this->t_captcha.' ';

		if (!is_null($sLanguageCode)) {
			$query .= 'WHERE language_code = \''.$this->db->escapeStr($sLanguageCode).'\' ';
		}

		if (($rs = $this->db->select($query)) === false) {
			return new recordset(array());
		}

		return $rs;
	}

	/**
	 * Ajout d'une question
	 *
	 * @param	string	question		La question
	 * @param	string	reponse			La réponse
	 * @param	string	language_code	Le code de la langue
	 * @return boolean
	 */
	public function add($question,$reponse,$language_code)
	{
		$query =
		'INSERT INTO '.$this->t_captcha.' '.
		'(question, reponse, language_code) VALUES ( '.
		'\''.$this->db->escapeStr($question).'\', '.
		'\''.$this->db->escapeStr($reponse).'\', '.
		'\''.$this->db->escapeStr($language_code).'\' '.
		')';

		if (!$this->db->execute($query)) {
			return false;
		}

		return $this->db->getLastID();
	}

	/**
	 * Modification d'une question
	 *
	 * @param	integer	id				Identifiant de la question
	 * @param	string	question		La question
	 * @param	string	reponse			La réponse
	 * @return boolean
	 */
	public function edit($id, $question, $reponse)
	{
		$query =
		'UPDATE '.$this->t_captcha.' SET '.
			'question = \''.$this->db->escapeStr($question).'\', '.
			'reponse = \''.$this->db->escapeStr($reponse).'\' '.
		'WHERE id = '.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * suppression d'une question
	 *
	 * @param	integer	id				Identifiant de la question
	 * @return boolean
	 */
	public function del($id)
	{
		$query =
		'DELETE FROM '.$this->t_captcha.' '.
		'WHERE id = '.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}



	/*
	 * Behaviors
	 */

	/**
	 * Initialisation
	 *
	 * @param object $okt
	 */
	public static function publicControllerStart($okt, $sName)
	{
		if($sName == "accessible_captcha"){
			$okt->accessible_captcha->initQuestion();
		}
	}

	/**
	 * Vérification
	 *
	 * @param object $okt
	 */
	public static function publicControllerFormCheckValues($okt, $sName)
	{
		if($sName == "accessible_captcha"){
			$p_question = !empty($_POST['captcha_q']) ? trim($_POST['captcha_q']) : null;
			$p_answer = !empty($_POST['captcha']) ? trim($_POST['captcha']) : null;

			if (!$okt->accessible_captcha->check($p_question, $p_answer))
			{
				$okt->error->set(__('The answer provided to the question is incorrect.'));

				return false;
			}
		}
		return true;
	}

	/**
	 * Préparation validation
	 *
	 * @param object $okt
	 * @param object $aJsValidateRules
	 */
	public static function publicJsValidateRules($okt,$aJsValidateRules, $sName)
	{
		if($sName == "accessible_captcha"){
			$aJsValidateRules[] = 'captcha: { required: true }';
		}
	}

	/**
	 * Affichage
	 *
	 * @param object $okt
	 * @param string $sName
	 */
	public static function publicTplFormBottom($okt, $sName)
	{
		if($sName == "accessible_captcha"){
		echo
		'<fieldset id="accessible_captcha">'.
		'<legend>'.__('SPAM prevention').'</legend>'.
		'<p>'.__('In order to validate the form please answer the following question, this allows to ensure us that you are not a spam robot. Thank you for your comprehension.').'</p>'.
		'<p class="field"><label for="captcha">'.$okt->accessible_captcha->getQuestion().'</label>'.
		'<input name="captcha" id="captcha" type="text" class="text" size="50" maxlength="255" />'.
		'<input name="captcha_q" value="'.$okt->accessible_captcha->getEncQuestion().'" type="hidden" /></p>'.
		'</fieldset>';
		}
	}

} # class
