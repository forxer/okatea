<?php
/**
 * @ingroup okt_module_guestbook
 * @brief La classe principale du module Guestbook.
 *
 */

use Tao\Admin\Menu as AdminMenu;
use Tao\Admin\Page;
use Tao\Database\MySqli;
use Tao\Misc\Utilities as util;
use Tao\Modules\Module;
use Tao\Routing\Route;

class module_guestbook extends Module
{
	private $t_guestbook;
	public $config = null;

	protected function prepend()
	{
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'GuestbookController' => __DIR__.'/inc/GuestbookController.php',
			'GuestbookHelpers' => __DIR__.'/inc/GuestbookHelpers.php'
		));

		# permissions
		$this->okt->addPermGroup('guestbook', __('m_guestbook_perm_group'));
			$this->okt->addPerm('guestbook', __('m_guestbook_perm_global'), 'guestbook');
			$this->okt->addPerm('guestbook_display', __('m_guestbook_perm_display'), 'guestbook');
			$this->okt->addPerm('guestbook_config', __('m_guestbook_perm_config'), 'guestbook');

		# config
		$this->config = $this->okt->newConfig('conf_guestbook');

		# table
		$this->t_guestbook = $this->db->prefix.'mod_guestbook';
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->guestbookSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);

			$this->okt->page->mainMenu->add(
				$this->getName(),
				'module.php?m=guestbook',
				$this->bCurrentlyInUse,
				10,
				$this->okt->checkPerm('guestbook'),
				null,
				$this->okt->page->guestbookSubMenu,
				$this->url().'/icon.png'
			);
				$this->okt->page->guestbookSubMenu->add(
					__('c_a_menu_management'),
					'module.php?m=guestbook&amp;action=index',
					$this->bCurrentlyInUse && ($this->okt->page->action !== 'display' && $this->okt->page->action !== 'config'),
					1
				);
				$this->okt->page->guestbookSubMenu->add(
					__('c_a_menu_display'),
					'module.php?m=guestbook&amp;action=display',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'display'),
					2,
					$this->okt->checkPerm('guestbook_display')
				);
				$this->okt->page->guestbookSubMenu->add(
					__('c_a_menu_configuration'),
					'module.php?m=guestbook&amp;action=config',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'config'),
					3,
					$this->okt->checkPerm('guestbook_config')
				);
		}
	}

	protected function prepend_public()
	{
		$this->okt->page->loadCaptcha($this->config->captcha);
	}

	/**
	 * Retourne la clause WHERE pour différentes requetes en fonction d'un tableau de paramètres
	 *
	 * @param array $params
	 * @return string
	 */
	protected function getWhereClause($params)
	{
		$reqPlus = 'WHERE 1 ';

		if (!empty($params['id'])) {
			$reqPlus .= 'AND id='.(integer)$params['id'].' ';
		}

		if (!empty($params['is_visible'])) {
			$reqPlus .= 'AND visible=1 ';
		}

		if (!empty($params['is_not_visible'])) {
			$reqPlus .= 'AND visible=0 ';
		}

		if (!empty($params['is_spam'])) {
			$reqPlus .= 'AND spam_status IS NOT NULL ';
		}

		if (!empty($params['is_not_spam'])) {
			$reqPlus .= 'AND spam_status IS NULL ';
		}

		if (!empty($params['custom_where'])) {
			$reqPlus .= ' '.$params['custom_where'].' ';
		}

		if (!empty($params['language'])) {
			$reqPlus .= 'AND language=\''.$this->db->escapeStr($params['language']).'\' ';
		}

		return $reqPlus;
	}

	/**
	 * Retourne une ou plusieurs signatures
	 *
	 * @param array $params		Différents paramètres de sélection
	 * @param boolean $count_only
	 * @return recordset
	 */
	public function getSig($params = array(),$count_only=false)
	{
		if ($count_only)
		{
			$query =
			'SELECT COUNT(id) AS num_sig '.
			'FROM '.$this->t_guestbook.' '.
			$this->getWhereClause($params);
		}
		else {
			$query =
			'SELECT id, language, message, nom, email, url, note, ip, '.
			'date_sign, visible, spam_status, spam_filter '.
			'FROM '.$this->t_guestbook.' '.
			$this->getWhereClause($params);
		}

		if (!empty($params['order'])) {
			$query .= 'ORDER BY '.$params['order'].' ';
		}
		else {
			$query .= 'ORDER BY date_sign DESC ';
		}

		if (!empty($params['limit']) && !$count_only) {
			$query .= 'LIMIT '.$params['limit'].' ';
		}

		if (($rs = $this->db->select($query)) === false)
		{
			if ($count_only) {
				return 0;
			}
			else {
				return new recordset(array());
			}
		}

		if ($count_only) {
			return (integer)$rs->num_sig;
		}
		else {
			return $rs;
		}
	}

	/**
	 * Vérifie l'existence d'une signature.
	 *
	 * @param integer $id
	 * @return boolean
	 */
	private function sigExists($id)
	{
		if ($this->getSig(array('id'=>$id),true) === 0) {
			return false;
		}

		return true;
	}

	/**
	 * Gère les données envoyées par l'utilisateur
	 *
	 * @param array $data
	 */
	public function handleUserData($data)
	{
		# champ message (est un champs requis)
		$data['message'] = util::linebreaks(html::clean($data['message']));

		if (empty($data['message'])) {
			$this->error->set(__('m_guestbook_must_message'));
		}

		# champ nom
		if ($this->config->chp_nom > 0)
		{
			if ($this->config->chp_nom == 2 && empty($data['nom'])) {
				$this->error->set(__('m_guestbook_must_name'));
			}
		}
		else {
			$data['nom'] = null;
		}

		# champ email
		if ($this->config->chp_mail > 0)
		{
			if ($data['email'] != '' && !text::isEmail($data['email'])) {
				$this->error->set(__('m_guestbook_email_invalid'));
			}

			if ($this->config->chp_mail == 2 && empty($data['email'])) {
				$this->error->set(__('m_guestbook_must_email'));
			}
		}
		else {
			$data['email'] = null;
		}

		# champ URL
		if ($this->config->chp_url > 0)
		{
			if ($data['url'] == 'http://') {
				$data['url'] = '';
			}

			if ($this->config->chp_url == 2 && empty($data['url'])) {
				$this->error->set(__('m_guestbook_must_url'));
			}
		}
		else {
			$data['url'] = null;
		}

		# note
		if ($this->config->chp_note > 0)
		{
			if ($this->config->chp_note == 2 && (empty($data['note']) || $data['note'] == 'nc')) {
				$this->error->set(__('m_guestbook_must_rating'));
			}

			if (empty($data['note']) || $data['note'] == 'nc') {
				$data['note'] = null;
			}
		}
		else {
			$data['note'] = null;
		}

		if (empty($data['language'])) {
			$data['language'] = $this->okt->config->language;
		}

		return $data;
	}

	/**
	 * Ajout d'une signature
	 *
	 * @param array $data
	 *
	 * @return integer ID de la signature nouvellement ajoutée
	 */
	public function addSig($data)
	{
		$data['is_spam'] = null;

		if ($this->okt->modules->moduleExists('antispam')) {
			$data['is_spam'] = oktAntispam::isSpam('guestbook',$data['nom'],$data['email'],$data['url'],$data['ip'],$data['message']);
		}

		if (!is_array($data['is_spam']))
		{
			$data['is_spam'] = array (
				'spam_status' => null,
				'spam_filter' => null,
			);
		}

		$query =
		'INSERT INTO '.$this->t_guestbook.' ( '.
			'language, message, nom, email, url, note, ip, date_sign, visible, spam_status, spam_filter '.
		') VALUES ( '.
			(is_null($data['language']) ? 'NULL' : '\''.$this->db->escapeStr($data['language']).'\'').', '.
			'\''.$this->db->escapeStr($data['message']).'\', '.
			(is_null($data['nom']) ? 'NULL' : '\''.$this->db->escapeStr($data['nom']).'\'').', '.
			(is_null($data['email']) ? 'NULL' : '\''.$this->db->escapeStr($data['email']).'\'').', '.
			(is_null($data['url']) ? 'NULL' : '\''.$this->db->escapeStr($data['url']).'\'').', '.
			(is_null($data['note']) ? 'NULL' : (integer)$data['note']).', '.
			(is_null($data['ip']) ? 'NULL' : '\''.$this->db->escapeStr($data['ip']).'\'').', '.
			(empty($data['date']) ? 'NOW()' : '\''.$this->db->escapeStr(MySqli::formatDateTime($data['date'])).'\'').', '.
			(integer)$data['visible'].', '.
			(is_null($data['is_spam']['spam_status']) ? 'NULL' : '\''.$this->db->escapeStr($data['is_spam']['spam_status']).'\'').', '.
			(is_null($data['is_spam']['spam_filter']) ? 'NULL' : '\''.$this->db->escapeStr($data['is_spam']['spam_filter']).'\'').' '.
		'); ';

		if (!$this->db->execute($query)) {
			return false;
		}

		return $this->db->getLastID();
	}

	/**
	 * Modification d'une signature
	 *
	 * @param integer $id
	 * @param string $message
	 * @param boolean $visible
	 * @param string $nom
	 * @param string $email
	 * @param string $url
	 * @param string $note
	 * @return boolean success
	 */
	public function updSig($data)
	{
		if (!$this->sigExists($data['id'])) {
			return false;
		}

		$query =
		'UPDATE '.$this->t_guestbook.' SET '.
			'language='.(is_null($data['language']) ? 'NULL' : '\''.$this->db->escapeStr($data['language']).'\'').', '.
			'nom='.(is_null($data['nom']) ? 'NULL' : '\''.$this->db->escapeStr($data['nom']).'\'').', '.
			'email='.(is_null($data['email']) ? 'NULL' : '\''.$this->db->escapeStr($data['email']).'\'').', '.
			'url='.(is_null($data['url']) ? 'NULL' : '\''.$this->db->escapeStr($data['url']).'\'').', '.
			'message=\''.$this->db->escapeStr($data['message']).'\', '.
			'note='.(is_null($data['note']) ? 'NULL' : (integer)$data['note']).', '.
			'visible='. (integer)$data['visible'].' '.
		'WHERE id='.(integer)$data['id'];

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Suppression d'une ou de plusieurs signatures
	 *
	 * @param integer $id
	 * @return boolean success
	 */
	public function delSig($params)
	{
		if (($sig = $this->getSig($params)) === false) {
			return false;
		}

		$query =
		'DELETE FROM '.$this->t_guestbook.' '.
		$this->getWhereClause($params);

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->db->optimize($this->t_guestbook);

		return true;
	}

	/**
	 * Marque une signature comme validée
	 *
	 * @param integer $id
	 * @return boolean success
	 */
	public function validateSig($id)
	{
		if (!$this->sigExists($id)) {
			return false;
		}

		$query =
		'UPDATE '.$this->t_guestbook.' SET '.
			'visible=1 '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Marque une signature comme étant du SPAM
	 *
	 * @param integer $id
	 * @return boolean success
	 */
	public function markSigAsSpam($id)
	{
		if (!$this->sigExists($id)) {
			return false;
		}

		$query =
		'UPDATE '.$this->t_guestbook.' SET '.
			'spam_status=1, '.
			'spam_filter=\'user\' '.
		'WHERE id='.(integer)$id.' ';

		if (!$this->db->execute($query)) {
			return false;
		}

	//	$this->updAntispam('spam', $id, $sig->trained, $sig->nom, $sig->message);

		return true;
	}

	/**
	 * Marque une signature comme étant acceptable
	 *
	 * @param integer $id
	 * @return boolean success
	 */
	public function markSigAsNoSpam($id)
	{
		if (!$this->sigExists($id)) {
			return false;
		}

		$query =
		'UPDATE '.$this->t_guestbook.' SET '.
			'spam_status=NULL, '.
			'spam_filter=NULL '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

	//	$this->updAntispam('nospam', $id, $sig->trained, $sig->nom, $sig->message);

		return true;
	}

	/**
	 * Mise à jour de l'antispam
	 *
	 * @todo : need to be refactored
	 */
	private function updAntispam($action, $id, $trained, $nom, $message)
	{
		/*
		$antispam = new antispam($this->okt);

		if ($trained) {
			$antispam->untrain('guestbook_'.$id);
		}

		$antispam->train('guestbook_'.$id, $action, $nom.' '.$message);
		$antispam->updateProbabilities();
		*/
		return true;
	}

}
