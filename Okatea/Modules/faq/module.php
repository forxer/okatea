<?php
/**
 * @ingroup okt_module_faq
 * @brief La classe principale du module faq.
 *
 */

use Okatea\Admin\Menu as AdminMenu;
use Okatea\Admin\Page;
use Okatea\Tao\Images\ImageUpload;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Modules\Module;
use Okatea\Tao\Routing\Route;

class module_faq extends Module
{
	protected $t_faq;
	protected $t_faq_locales;
	protected $t_faq_cat;
	protected $t_faq_cat_locales;

	protected $t_users;

	protected $params = array();

	public $filters = null;

	/**
	 * Chargée à l'initialisation des modules
	 *
	 * @see inc/classes/modules/oktModule#prepend()
	 */
	protected function prepend()
	{
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'FaqController' => __DIR__.'/inc/FaqController.php',
			'FaqFilters' => __DIR__.'/inc/FaqFilters.php',
			'FaqHelpers' => __DIR__.'/inc/FaqHelpers.php',
			'FaqRecordset' => __DIR__.'/inc/FaqRecordset.php'
		));

		# permissions
		$this->okt->addPermGroup('faq', __('m_faq_perm_group'));
			$this->okt->addPerm('faq', __('m_faq_perm_global'), 'faq');
			$this->okt->addPerm('faq_add', __('m_faq_perm_add'), 'faq');
			$this->okt->addPerm('faq_remove', __('m_faq_perm_remove'), 'faq');
			$this->okt->addPerm('faq_categories', __('m_faq_perm_categories'), 'faq');
			$this->okt->addPerm('faq_display', __('m_faq_perm_display'), 'faq');
			$this->okt->addPerm('faq_config', __('m_faq_perm_config'), 'faq');

		# tables
		$this->t_faq = $this->db->prefix.'mod_faq';
		$this->t_faq_locales = $this->db->prefix.'mod_faq_locales';
		$this->t_faq_cat = $this->db->prefix.'mod_faq_cat';
		$this->t_faq_cat_locales = $this->db->prefix.'mod_faq_cat_locales';

		$this->t_users = $this->db->prefix.'core_users';

		# config
		$this->config = $this->okt->newConfig('conf_faq');
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->faqSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add(
				$this->getName(),
				'module.php?m=faq',
				$this->bCurrentlyInUse,
				10,
				$this->okt->checkPerm('faq'),
				null,
				$this->okt->page->faqSubMenu,
				$this->okt->options->public_url.'/modules/'.$this->id().'/module_icon.png'
			);
				$this->okt->page->faqSubMenu->add(
					__('c_a_menu_management'),
					'module.php?m=faq&amp;action=index',
					$this->bCurrentlyInUse && (!$this->okt->page->action || $this->okt->page->action === 'index' || $this->okt->page->action === 'edit'),
					1
				);
				$this->okt->page->faqSubMenu->add(
					__('m_faq_add_question'),
					'module.php?m=faq&amp;action=add',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'add'),
					2,
					$this->okt->checkPerm('faq_add')
				);
				$this->okt->page->faqSubMenu->add(
					__('m_faq_sections'),
					'module.php?m=faq&amp;action=categories',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'categories'),
					3,
					($this->config->enable_categories && $this->okt->checkPerm('faq_categories'))
				);
				$this->okt->page->faqSubMenu->add(
					__('c_a_menu_display'),
					'module.php?m=faq&amp;action=display',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'display'),
					10,
					$this->okt->checkPerm('faq_display')
				);
				$this->okt->page->faqSubMenu->add(
					__('c_a_menu_configuration'),
					'module.php?m=faq&amp;action=config',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'config'),
					20,
					$this->okt->checkPerm('faq_config')
				);
		}
	}

	/**
	 * Initialisation des filtres
	 *
	 * @param string $part 	'public' ou 'admin'
	 */
	public function filtersStart($part='public')
	{
		if ($this->filters === null || !($this->filters instanceof FaqFilters)) {
			$this->filters = new FaqFilters($this->okt, $this, $part);
		}
	}


	/* Gestion des questions internationalisées
	----------------------------------------------------------*/

	/**
	 * Retourne la liste des questions en fonction d'un tableau de paramètres.
	 *
	 * @param array $params
	 * @param boolean $count_only
	 * @return recordset
	 */
	public function getQuestions($params=array(), $count_only=false)
	{
		$reqPlus = '';

		if (!empty($params['id'])) {
			$reqPlus .= ' AND p.id='.(integer)$params['id'].' ';
		}

		if (!empty($params['slug'])) {
			$reqPlus .= ' AND pl.slug=\''.$this->db->escapeStr($params['slug']).'\' ';
		}

		if (!empty($params['language'])) {
			$reqPlus .= 'AND pl.language=\''.$this->db->escapeStr($params['language']).'\' ';
			$reqPlus .= 'AND (cl.language=\''.$this->db->escapeStr($params['language']).'\' OR p.cat_id IS NULL) ';
		}

		# active ?
		if (isset($params['active']))
		{
			if ($params['active'] == 0) {
				$reqPlus .= 'AND (p.active=0 OR c.active=0) ';
			}
			elseif ($params['active'] == 1) {
				$reqPlus .= 'AND p.active=1 AND c.active=1 ';
			}
			elseif ($params['active'] == 2) {
				$reqPlus .= '';
			}
		}
		else {
			$reqPlus .= 'AND p.active=1 ';
		}

		# mots clés
		if (!empty($params['keyword_search']))
		{
			$words = text::splitWords($params['keyword_search']);

			if (!empty($words))
			{
				foreach ($words as $i => $w) {
					$words[$i] = 'pl.words LIKE \'%'.$this->db->escapeStr($w).'%\' ';
				}
				$reqPlus .= ' AND '.implode(' AND ',$words).' ';
			}
		}

		if ($count_only)
		{
			$query =
			'SELECT COUNT(p.id) AS num_questions '.
			'FROM '.$this->t_faq.' AS p '.
				'LEFT JOIN '.$this->t_faq_locales.' AS pl ON pl.faq_id=p.id '.
				'LEFT JOIN '.$this->t_faq_cat.' AS c ON c.id=p.cat_id '.
				'LEFT JOIN '.$this->t_faq_cat_locales.' AS cl ON cl.cat_id=p.cat_id '.
			'WHERE 1 '.
			$reqPlus;
		}
		else {
			$query =
			'SELECT p.id, p.cat_id, cl.title AS category, p.active, p.images, p.files, '.
			'pl.title, pl.title_tag, pl.title_seo, pl.slug, pl.content, pl.meta_description, pl.meta_keywords '.
			'FROM '.$this->t_faq.' AS p '.
				'LEFT JOIN '.$this->t_faq_locales.' AS pl ON pl.faq_id=p.id '.
				'LEFT JOIN '.$this->t_faq_cat.' AS c ON c.id=p.cat_id '.
				'LEFT JOIN '.$this->t_faq_cat_locales.' AS cl ON cl.cat_id=p.cat_id '.
			'WHERE 1 '.
			$reqPlus;

			if (isset($params['order_direction']) && strtoupper($params['order_direction']) == 'ASC') {
				$order_direction = $params['order_direction'];
			}
			else {
				$order_direction = 'DESC';
			}

			if (!empty($params['order'])) {
				$query .= 'ORDER BY '.$params['order'].' '.$order_direction.' ';
			}
			else {
				$query .= 'ORDER BY c.ord ASC, '.$this->config->public_default_order_by.' '.$order_direction.' ';
			}

			if (!empty($params['limit'])) {
				$query .= 'LIMIT '.$params['limit'].' ';
			}
		}

		if (($rs = $this->db->select($query,'FaqRecordset')) === false)
		{
			if ($count_only) {
				return 0;
			}
			else {
				$rs = new FaqRecordset(array());
				$rs->setCore($this->okt);
				return $rs;
			}
		}

		if ($count_only) {
			return (integer)$rs->num_questions;
		}
		else {
			$rs->setCore($this->okt);
			return $rs;
		}
	}

	/**
	 * Retourne les infos d'une question donnée
	 *
	 * @param integer $id
	 * @return recordset
	 */
	public function getQuestion($id)
	{
		return $this->getQuestions(array('id'=>$id,'active'=>2));
	}

	/**
	 * Indique si une question existe
	 *
	 * @param $id
	 * @return boolean
	 */
	public function questionExists($id)
	{
		if ($this->getQuestion($id)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Retourne les localisations d'une question donnée
	 *
	 * @param integer $post_id
	 * @return recordset
	 */
	public function getQuestionI18n($question_id)
	{
		$query =
		'SELECT language, title, title_tag, title_seo, slug, content, meta_description, meta_keywords '.
		'FROM '.$this->t_faq_locales.' '.
		'WHERE faq_id='.(integer)$question_id;

		if (($rs = $this->db->select($query,'FaqRecordset')) === false) {
			$rs = new FaqRecordset(array());
			$rs->setCore($this->okt);
			return $rs;
		}

		$rs->setCore($this->okt);
		return $rs;
	}

	/**
	 * Vérifie les paramètres requis pour l'ajout et la modification d'une question.
	 * On testent si on as au moins le titre et le contenu de la langue par défaut.
	 *
	 * @param array $params
	 * @return void
	 */
	protected function checkParams()
	{
		if (!empty($this->params))
		{
			if (empty($this->params['title'][$this->okt->config->language])
				|| empty($this->params['content'][$this->okt->config->language]))
			{
				$this->error->set(sprintf(__('m_faq_error_missing_default_language_%s'),
					$this->okt->languages->list[$this->okt->config->language]['title']));
			}
		}
	}

	/**
	 * Ajout d'une question
	 *
	 * @param array $params
	 * 	'title' => array
	 * 	'content' => array
	 * 	'active' => boolean
	 *  'slugs' => array (optional)
	 * @return integer
	 */
	public function addQuestion($params=array())
	{
		$this->params = $params;

		$this->checkParams();

		$this->addFiles();

		if (!$this->error->isEmpty()) {
			return false;
		}

		# ajout de la question
		$this->params['files'] = !empty($this->params['files']) ? $this->params['files'] : array();
		$this->params['files'] = serialize($this->params['files']);

		$query =
		'INSERT INTO '.$this->t_faq.' ( '.
			'cat_id, active, files '.
		') VALUES ( '.
			(empty($this->params['cat_id']) ? 'NULL' : (integer)$this->params['cat_id']).', '.
			(integer)$this->params['active'].', '.
			'\''.$this->db->escapeStr($this->params['files']).'\''.
		'); ';

		if (!$this->db->execute($query)) {
			return false;
		}

		$iQuestionId = $this->db->getLastID();

		$this->params['id'] = $iQuestionId;

		# ajout des images
		if ($this->config->images['enable'] && $this->addImages($iQuestionId) === false) {
			return false;
		}

		# ajout des textes internationalisés
		if (!$this->setQuestionI18n()) {
			return false;
		}

		return $iQuestionId;
	}

	/**
	 * Mise à jour d'une question
	 *
	 * @param array $params
	 * 	'id' => integer
	 * 	'title' => array
	 * 	'content' => array
	 * 	'active' => boolean
	 *  'slugs' => array (optional)
	 * @return boolean
	 */
	public function updQuestion($params=array())
	{
		$this->params = $params;

		if (!$this->questionExists($this->params['id'])) {
			$this->error->set('La question #'.$this->params['id'].' n\'existe pas.');
			return false;
		}

		# vérification des paramètres
		$this->checkParams();

		# ajout des fichiers joints
		$this->editFiles();

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->params['files'] = !empty($this->params['files']) ? $this->params['files'] : array();
		$this->params['files'] = serialize($this->params['files']);

		$query =
		'UPDATE '.$this->t_faq.' SET '.
			'cat_id='.(empty($this->params['cat_id']) ? 'NULL' : (integer)$this->params['cat_id']).', '.
			'active='.(integer)$this->params['active'].', '.
			'files=\''.$this->db->escapeStr($this->params['files']).'\' '.
		'WHERE id='.(integer)$this->params['id'];

		if (!$this->db->execute($query)) {
			return false;
		}

		# modification des textes internationalisés
		if (!$this->setQuestionI18n()) {
			return false;
		}

		# modification des images
		if ($this->config->images['enable'] && $this->updImages($this->params['id']) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Suppression d'une question
	 *
	 * @param integer $id
	 * @return boolean
	 */
	public function deleteQuestion($id)
	{
		if (!$this->questionExists($id)) {
			return false;
		}

		# delete images
		$this->deleteImages($id);

		# delete questions
		$query =
		'DELETE FROM '.$this->t_faq.' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->db->optimize($this->t_faq);

		# delete i18n
		$query =
		'DELETE FROM '.$this->t_faq_locales.' '.
		'WHERE faq_id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->db->optimize($this->t_faq_locales);

		return true;
	}

	/**
	 * Switch le statut d'une question donnée
	 *
	 * @param integer $question_id
	 * @return boolean
	 */
	public function setQuestionStatus($question_id)
	{
		$query =
		'UPDATE '.$this->t_faq.' SET '.
			'active = 1-active '.
		'WHERE id='.(integer)$question_id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}


	/* Gestion des catégories (simple niveau)
	----------------------------------------------------------*/

	/**
	 * Retourne une liste de catégorie sous forme de recordset.
	 *
	 * @param array $params
	 * @return recordset
	 */
	public function getCategories($params=array())
	{
		$reqPlus = '';

		if (!empty($params['id'])) {
			$reqPlus .= ' AND c.id='.(integer)$params['id'].' ';
		}

		if (!empty($params['language'])) {
			$reqPlus .= 'AND cl.language=\''.$this->db->escapeStr($params['language']).'\' ';
		}

		# active ?
		if (isset($params['active']))
		{
			if ($params['active'] == 0) {
				$reqPlus .= 'AND c.active=0 ';
			}
			elseif ($params['active'] == 1) {
				$reqPlus .= 'AND c.active=1 ';
			}
			elseif ($params['active'] == 2) {
				$reqPlus .= '';
			}
		}
		else {
			$reqPlus .= 'AND c.active=1 ';
		}

		$query =
		'SELECT c.id, c.active, c.ord, cl.title '.
		'FROM '.$this->t_faq_cat.' AS c '.
		'LEFT JOIN '.$this->t_faq_cat_locales.' AS cl ON cl.cat_id=c.id '.
		'WHERE 1 '.
		$reqPlus;

		if (isset($params['order_direction']) && strtoupper($params['order_direction']) == 'ASC') {
			$order_direction = $params['order_direction'];
		}
		else {
			$order_direction = $this->config->public_default_order_direction;
		}

		if (!empty($params['order'])) {
			$query .= 'ORDER BY '.$params['order'].' '.$order_direction.' ';
		}
		else {
			$query .= 'ORDER BY c.ord ASC, '.$this->config->public_default_order_by.' '.$order_direction.' ';
		}

		if (!empty($params['limit'])) {
			$query .= 'LIMIT '.$params['limit'].' ';
		}

		if (($rs = $this->db->select($query)) === false) {
			return new recordset(array());
		}

		return $rs;
	}

	/**
	 * Retourne une catégorie donnée sous forme de recordset.
	 *
	 * @param integer $iCategoryId
	 * @param integer $iActive
	 * @return recordset
	 */
	public function getCategory($iCategoryId,$iActive=2)
	{
		return $this->getCategories(array('id'=>$iCategoryId,'active'=>$iActive));
	}

	/**
	 * Teste l'existence d'une catégorie.
	 *
	 * @param integer $iCategoryId
	 * @return boolean
	 */
	public function categoryExists($iCategoryId)
	{
		if ($this->getCategory($iCategoryId)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Retourne les localisations d'une catégorie donnée
	 *
	 * @param integer $post_id
	 * @return recordset
	 */
	public function getCategoryI18n($iCategoryId)
	{
		$query =
		'SELECT language, title '.
		'FROM '.$this->t_faq_cat_locales.' '.
		'WHERE cat_id='.(integer)$iCategoryId;

		if (($rs = $this->db->select($query)) === false) {
			return new recordset(array());
		}

		return $rs;
	}

	/**
	 * Ajout d'une catégorie.
	 *
	 * @param array $params
	 * @return integer
	 */
	public function addCategory($params)
	{
		$this->params = $params;

		$query = 'SELECT MAX(ord) FROM '.$this->t_faq_cat;
		$rs = $this->db->select($query);
		if ($rs->isEmpty()) {
			return false;
		}
		$max_ord = $rs->f(0);

		$query =
		'INSERT INTO '.$this->t_faq_cat. ' ( '.
			'active, ord'.
		') VALUES ('.
			(integer)$this->params['active'].','.
			(integer)($max_ord+1).
		'); ';

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->params['id'] = $this->db->getLastID();

		if (!$this->setPostI18n()) {
			return false;
		}

		return $this->params['id'];
	}

	/**
	 * Modification d'une catégorie.
	 *
	 * @param array $params
	 * @return boolean
	 */
	public function updCategory($params)
	{
		$this->params = $params;

		if (!$this->categoryExists($this->params['id'])) {
			$this->error->set('La catégorie n’existe pas.');
			return false;
		}

		$query =
		'UPDATE '.$this->t_faq_cat.' SET '.
		'active='.(integer)$this->params['active'].' '.
		'WHERE id='.(integer)$this->params['id'];

		if (!$this->db->execute($query)) {
			return false;
		}

		if (!$this->setPostI18n()) {
			return false;
		}
	}

	public function delCategory($iCategoryId)
	{
		$query =
		'DELETE FROM '.$this->t_faq_cat.' '.
		'WHERE id='.(integer)$iCategoryId;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->db->optimize($this->t_faq_cat);

		$query =
		'DELETE FROM '.$this->t_faq_cat_locales.' '.
		'WHERE cat_id='.(integer)$iCategoryId;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->db->optimize($this->t_faq_cat_locales);
	}

	/**
	 * Switch le statut d'une catégorie donnée.
	 *
	 * @param integer $id
	 * @return boolean
	 */
	public function switchCategoryStatus($id)
	{
		if (!$this->categoryExists($id)) {
			$this->error->set('La catégorie n’existe pas.');
			return false;
		}

		$query =
		'UPDATE '.$this->t_faq_cat.' SET '.
			'active = 1-active '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Met à jour la position d'une catégorie donnée
	 *
	 * @param integer $id
	 * @param integer $ord
	 * @return boolean
	 */
	public function updCategoryOrder($id,$ord)
	{
		$query =
		'UPDATE '.$this->t_faq_cat.' SET '.
		'ord='.(integer)$ord.' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	protected function setPostI18n()
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$query =
			'INSERT INTO '.$this->t_faq_cat_locales.' '.
				'(cat_id, language, title) '.
			'VALUES ('.
				(integer)$this->params['id'].', '.
				'\''.$this->db->escapeStr($aLanguage['code']).'\', '.
				(empty($this->params['title'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['title'][$aLanguage['code']]).'\'').' '.
			') ON DUPLICATE KEY UPDATE '.
				'title='.(empty($this->params['title'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['title'][$aLanguage['code']]).'\'').' ';

			if (!$this->db->execute($query)) {
				return false;
			}
		}

		return true;
	}


	/* Gestion des fichier des questions internationalisées
	----------------------------------------------------------*/

	/**
	 * Ajout des fichiers
	 *
	 * @return void
	 */
	protected function addFiles()
	{
		$aAllowedExts = explode(',',$this->config->files['allowed_exts']);

		$aFiles = array();

		foreach ($this->okt->languages->list as $aLanguage)
		{
			$aFiles[$aLanguage['code']] = array();
			$j = 1;

			for ($i=0; $i<=$this->config->files['number']; $i++)
			{
				if (!isset($_FILES['p_files_'.$aLanguage['code'].'_'.$i]) || empty($_FILES['p_files_'.$aLanguage['code'].'_'.$i]['tmp_name'])) {
					continue;
				}

				$sUploadedFile = $_FILES['p_files_'.$aLanguage['code'].'_'.$i];

				try {
					# des erreurs d'upload ?
					Utilities::uploadStatus($sUploadedFile);

					# vérification de l'extension
					$sExtension = pathinfo($sUploadedFile['name'],PATHINFO_EXTENSION);
					if (!in_array($sExtension,$aAllowedExts)) {
						throw new Exception('Type de fichier non-autorisé.');
					}

					if (!file_exists($this->upload_dir)) {
						files::makeDir($this->upload_dir,true);
					}

					$sDestination = $this->upload_dir.'/'.Utilities::strToLowerURL($this->params['title'][$aLanguage['code']],false).'-'.$aLanguage['code'].'-'.$j.'.'.$sExtension;

					if (!move_uploaded_file($sUploadedFile['tmp_name'],$sDestination)) {
						throw new Exception('Impossible de déplacer sur le serveur le fichier téléchargé.');
					}

					$aFiles[$aLanguage['code']][] = basename($sDestination);
					$j++;
				}
				catch (Exception $e) {
					$this->okt->error->set('Problème avec le fichier '.($i+1).' dans la langue '.$aLanguage['code'].' : '.$e->getMessage());
				}
			}
		}

		$this->params['files'] = $aFiles;
	}

	/**
	 * Modification des fichiers
	 *
	 * @return void
	 */
	protected function editFiles()
	{
		$aCurrentFiles = $this->getQuestion($this->params['id'])->getFilesInfo();

		$aNewFiles = array();

		foreach ($this->okt->languages->list as $aLanguage)
		{
			$aNewFiles[$aLanguage['code']] = array();
			$j = 1;

			for ($i=0; $i<=$this->config->files['number']; $i++)
			{
				if (!isset($_FILES['p_files_'.$aLanguage['code'].'_'.$i]) || empty($_FILES['p_files_'.$aLanguage['code'].'_'.$i]['tmp_name']))
				{
					if (!empty($aCurrentFiles[$aLanguage['code']][$i])) {
						$aNewFiles[$aLanguage['code']][$i] = $aCurrentFiles[$aLanguage['code']][$i]['filename'];
						$j++;
					}
					continue;
				}

				$sUploadedFile = $_FILES['p_files_'.$aLanguage['code'].'_'.$i];

				try {
					# des erreurs d'upload ?
					Utilities::uploadStatus($sUploadedFile);

					# vérification de l'extension
					$sExtension = pathinfo($sUploadedFile['name'],PATHINFO_EXTENSION);
					if (!in_array($sExtension,explode(',',$this->config->files['allowed_exts']))) {
						throw new Exception('Type de fichier non-autorisé.');
					}

					if (!file_exists($this->upload_dir)) {
						files::makeDir($this->upload_dir,true);
					}

					if (!empty($aCurrentFiles[$aLanguage['code']][$i]) && files::isDeletable($this->upload_dir.'/'.$aCurrentFiles[$aLanguage['code']][$i]['filename'])) {
						unlink($this->upload_dir.'/'.$aCurrentFiles[$aLanguage['code']][$i]['filename']);
					}

					$sDestination = $this->upload_dir.'/'.Utilities::strToLowerURL($this->params['title'][$aLanguage['code']],false).'-'.$aLanguage['code'].'-'.$j.'.'.$sExtension;

					if (!move_uploaded_file($sUploadedFile['tmp_name'],$sDestination)) {
						throw new Exception('Impossible de déplacer sur le serveur le fichier téléchargé.');
					}

					$aNewFiles[$aLanguage['code']][] = basename($sDestination);
					$j++;
				}
				catch (Exception $e) {
					$this->okt->error->set('Pour le fichier '.$i.' dans la langue '.$aLanguage['code'].' : '.$e->getMessage());
				}
			}
		}

		$this->params['files'] = $aNewFiles;
	}

	public function delFile($question_id,$filename)
	{
		$rs = $this->getQuestion($question_id);

		if ($rs->isEmpty()) {
			$this->error->set('La question n\'existe pas.');
			return false;
		}

		$i18n = $this->getQuestionI18n($question_id);

		# suppression du fichier sur le disque
		if (file_exists($this->upload_dir.'/'.$filename)) {
			unlink($this->upload_dir.'/'.$filename);
		}

		# suppression du nom dans les infos de la question
		$files_db = unserialize($rs->files);

		foreach ($files_db as $locale=>$files)
		{
			foreach ($files as $k=>$v)
			{
				if ($v == $filename) {
					unset($files_db[$locale][$k]);
				}
			}
		}

		foreach ($files_db as $locale=>$files) {
			$files_db[$locale] = array_values($files);
		}

		foreach ($files_db as $locale=>$files)
		{
			while ($i18n->fetch())
			{
				if ($i18n->language == $locale) {
					$slug = $i18n->slug;
				}
			}

			foreach ($files as $k=>$v)
			{
				$sExtension = pathinfo($v,PATHINFO_EXTENSION);
				$question_name = Utilities::strToLowerURL($slug,false).'-'.$locale.'-'.($k+1).'.'.$sExtension;

				if (file_exists($this->upload_dir.'/'.$v)) {
					rename($this->upload_dir.'/'.$v, $this->upload_dir.'/'.$question_name);
				}

				$files_db[$locale][$k] = $question_name;
			}
		}

		$query =
		'UPDATE '.$this->t_faq.' SET '.
		'files=\''.$this->db->escapeStr(serialize($files_db)).'\' '.
		'WHERE id='.(integer)$question_id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}


	/* Gestion des images des questions internationalisées
	----------------------------------------------------------*/

	/**
	 * Retourne une instance de la classe oktImageUpload
	 *
	 * @return object oktImageUpload
	 */
	public function getImageUpload()
	{
		$o = new ImageUpload($this->okt,$this->config->images);
		$o->setConfig(array(
			'upload_dir' => $this->upload_dir.'/img',
			'upload_url' => $this->upload_url.'/img'
		));

		return $o;
	}

	/**
	 * Ajout d'image(s) à une question donnée
	 *
	 * @param $question_id
	 * @return boolean
	 */
	public function addImages($question_id)
	{
		$aImages = $this->getImageUpload()->addImages($question_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updQuestionImages($question_id, $aImages);
	}

	/**
	 * Modification d'image(s) d'une question donnée
	 *
	 * @param $question_id
	 * @return boolean
	 */
	public function updImages($question_id)
	{
		$aCurrentImages = $this->getQuestionImages($question_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aImages = $this->getImageUpload()->updImages($question_id, $aCurrentImages);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updQuestionImages($question_id, $aImages);
	}

	/**
	 * Suppression d'une image donnée d'une question donnée
	 *
	 * @param $question_id
	 * @param $img_id
	 * @return boolean
	 */
	public function deleteImage($question_id,$img_id)
	{
		$aCurrentImages = $this->getQuestionImages($question_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aQuestionImages = $this->getImageUpload()->deleteImage($question_id, $aCurrentImages, $img_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updQuestionImages($question_id, $aQuestionImages);
	}

	/**
	 * Suppression des images d'une question donnée
	 *
	 * @param $question_id
	 * @param $img_id
	 * @return boolean
	 */
	public function deleteImages($question_id)
	{
		$aCurrentImages = $this->getQuestionImages($question_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->getImageUpload()->deleteAllImages($question_id, $aCurrentImages);

		return $this->updQuestionImages($question_id);
	}

	/**
	 * Régénération de toutes les miniatures des images
	 *
	 * @return void
	 */
	public function regenMinImages()
	{
		@ini_set('memory_limit',-1);
		set_time_limit(0);

		$rsQuestions = $this->getQuestions(array('active'=>2));

		while ($rsQuestions->fetch())
		{
			$aImages = $rsQuestions->getImagesArray();
			$aImagesList = array();

			foreach ($aImages as $key=>$image)
			{
				$this->getImageUpload()->buildThumbnails($rsQuestions->id, $image['img_name']);

				$aImagesList[$key] = array_merge(
					$aImages[$key],
					$this->getImageUpload()->buildImageInfos($rsQuestions->id, $image['img_name'])
				);
			}

			$this->updQuestionImages($rsQuestions->id, $aImagesList);
		}
	}

	/**
	 * Récupère la liste des images d'une question donnée
	 *
	 * @param $question_id
	 * @return array
	 */
	public function getQuestionImages($question_id)
	{
		if (!$this->questionExists($question_id)) {
			$this->error->set('La question #'.$question_id.' n\'existe pas.');
			return false;
		}

		$rsQuestion = $this->getQuestion($question_id);
		$aQuestionImages = $rsQuestion->images ? unserialize($rsQuestion->images) : array();

		return $aQuestionImages;
	}

	/**
	 * Met à jours la liste des images d'une question donnée
	 *
	 * @param array $question_id
	 * @param $aImages
	 * @return boolean
	 */
	public function updQuestionImages($question_id, $aImages=array())
	{
		if (!$this->questionExists($question_id)) {
			$this->error->set('La question #'.$post_id.' n\'existe pas.');
			return false;
		}

		$aImages = !empty($aImages) ? serialize($aImages) : NULL;

		$query =
		'UPDATE '.$this->t_faq.' SET '.
			'images='.(!is_null($aImages) ? '\''.$this->db->escapeStr($aImages).'\'' : 'NULL').' '.
		'WHERE id='.(integer)$question_id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}


	/* Utilitaires
	----------------------------------------------------------*/
	/**
	 * Reconstruction des index de recherche de toutes les questions
	 *
	 */
	public function indexAllQuestions()
	{
		$rsQuestions = $this->getQuestions(array());
		while ($rsQuestions->fetch())
		{
			$rsQuestionLocales = $this->getQuestionI18n($rsQuestions->id);
			while($rsQuestionLocales->fetch())
			{
				$words =
					$rsQuestionLocales->title.' '.
					$rsQuestionLocales->subtitle.' '.
					$rsQuestionLocales->content.' ';

				$words = implode(' ',text::splitWords($words));

				$query =
				'UPDATE '.$this->t_faq_locales.' SET '.
					'words=\''.$this->db->escapeStr($words).'\' '.
				'WHERE faq_id='.(integer)$rsQuestions->id.' AND language=\''.$this->db->escapeStr($rsQuestionLocales->language).'\' ';

				$this->db->execute($query);
			}
		}

		return true;
	}

	/**
	 * Enregistrement des textes internationalisés
	 *
	 * @return boolean
	 */
	protected function setQuestionI18n()
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$this->params['content'][$aLanguage['code']] = $this->okt->HTMLfilter($this->params['content'][$aLanguage['code']]);
			$words = implode(' ',text::splitWords($this->params['title'][$aLanguage['code']].' '.$this->params['content'][$aLanguage['code']]));

			$query =
			'INSERT INTO '.$this->t_faq_locales.' '.
				'(faq_id, language, title, title_tag, title_seo, slug, content, meta_description, meta_keywords, words) '.
			'VALUES ('.
				(integer)$this->params['id'].', '.
				'\''.$this->db->escapeStr($aLanguage['code']).'\', '.
				(empty($this->params['title'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['title'][$aLanguage['code']]).'\'').', '.
				(empty($this->params['title_tag'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['title_tag'][$aLanguage['code']]).'\'').', '.
				(empty($this->params['title_seo'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['title_seo'][$aLanguage['code']]).'\'').', '.
				(empty($this->params['slugs'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['slugs'][$aLanguage['code']]).'\'').', '.
				(empty($this->params['content'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['content'][$aLanguage['code']]).'\'').', '.
				(empty($this->params['meta_description'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['meta_description'][$aLanguage['code']]).'\'').', '.
				(empty($this->params['meta_keywords'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['meta_keywords'][$aLanguage['code']]).'\'').', '.
				(empty($words) ? 'NULL' : '\''.$this->db->escapeStr($words).'\'').' '.
			') ON DUPLICATE KEY UPDATE '.
				'title='.(empty($this->params['title'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['title'][$aLanguage['code']]).'\'').', '.
				'title_tag='.(empty($this->params['title_tag'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['title_tag'][$aLanguage['code']]).'\'').', '.
				'title_seo='.(empty($this->params['title_seo'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['title_seo'][$aLanguage['code']]).'\'').', '.
				'slug='.(empty($this->params['slugs'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['slugs'][$aLanguage['code']]).'\'').', '.
				'content='.(empty($this->params['content'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['content'][$aLanguage['code']]).'\'').', '.
				'meta_description='.(empty($this->params['meta_description'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['meta_description'][$aLanguage['code']]).'\'').', '.
				'meta_keywords='.(empty($this->params['meta_keywords'][$aLanguage['code']]) ? 'NULL' : '\''.$this->db->escapeStr($this->params['meta_keywords'][$aLanguage['code']]).'\'').', '.
				'words='.(empty($words) ? 'NULL' : '\''.$this->db->escapeStr($words).'\'').' ';

			if (!$this->db->execute($query)) {
				return false;
			}

			if (!$this->setQuestionSlug($this->params['id'],$aLanguage['code'])) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Création du slug d'une question donnée dans une langue donnée
	 *
	 * @param $question_id
	 * @param $lang_code
	 * @return boolean
	 */
	protected function setQuestionSlug($question_id,$lang_code)
	{
		$rs = $this->getQuestions(array('id'=>$question_id,'language'=>$lang_code,'active'=>2));

		if ($rs->isEmpty()) {
			$this->error->set('La question #'.$question_id.' n\'existe pas. Impossible de créer son slug.');
			return false;
		}

		$slug = $this->buildQuestionSlug($rs->title,$rs->slug,$question_id,$lang_code);

		$query =
		'UPDATE '.$this->t_faq_locales.' SET '.
			'slug='.(empty($slug) ? 'NULL' : '\''.$this->db->escapeStr($slug).'\'').' '.
		'WHERE faq_id='.(integer)$question_id.' '.
			'AND language=\''.$this->db->escapeStr($lang_code).'\' ';

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Construit le slug d'une question donnée dans une langue donnée
	 *
	 * @param string $title
	 * @param string $url
	 * @param $question_id
	 * @param $lang_code
	 * @return string
	 */
	protected function buildQuestionSlug($title,$url,$question_id,$lang_code)
	{
		if (empty($url)) {
			$url = $title;
		}

		$url = Utilities::strToSlug($url, false);

		# URL is empty?
		if ($url == '') {
			return $url;
		}

		# Let's check if URL is taken…
		$query =
		'SELECT slug FROM '.$this->t_faq_locales.' '.
		'WHERE slug=\''.$this->db->escapeStr($url).'\' '.
			'AND faq_id <> '.(integer)$question_id.' '.
			'AND language=\''.$this->db->escapeStr($lang_code). '\' '.
		'ORDER BY slug DESC';

		$rs = $this->db->select($query);

		if (!$rs->isEmpty())
		{
			$query =
			'SELECT slug FROM '.$this->t_faq_locales.' '.
			'WHERE slug LIKE \''.$this->db->escapeStr($url).'%\' '.
				'AND faq_id <> '.(integer)$question_id. ' '.
				'AND language=\''.$this->db->escapeStr($lang_code). '\' '.
			'ORDER BY slug DESC ';

			$rs = $this->db->select($query);
			$a = array();
			while ($rs->fetch()) {
				$a[] = $rs->slug;
			}

			$url = Utilities::getIncrementedString($a, $url, '-');
		}

		# URL is empty?
//		if ($url == '') {
//			throw new Exception(__('Empty questions URL'));
//		}

		return $url;
	}

	/**
	 * Retourne la liste des types de statuts au pluriel
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public static function getQuestionsStatuses($flip=false)
	{
		$aStatus = array(
			0 => __('c_c_status_offline'),
			1 => __('c_c_status_online')
		);

		if ($flip) {
			$aStatus = array_flip($aStatus);
		}

		return $aStatus;
	}

	/**
	 * Retourne la liste des types de statuts au singulier
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public static function getQuestionsStatus($flip=false)
	{
		$aStatus = array(
			0 => __('c_c_status_offline'),
			1 => __('c_c_status_online')
		);

		if ($flip) {
			$aStatus = array_flip($aStatus);
		}

		return $aStatus;
	}

}
