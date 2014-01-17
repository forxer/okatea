<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Pages;

use Okatea\Admin\Menu as AdminMenu;
use Okatea\Admin\Page;
use Okatea\Tao\Core\Authentification;
use Okatea\Tao\Core\Triggers;
use Okatea\Tao\Database\MySqli;
use Okatea\Tao\Images\ImageUpload;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Misc\FileUpload;
use Okatea\Tao\Modules\Module as BaseModule;
use Okatea\Tao\Themes\SimpleReplacements;

class Module extends BaseModule
{
	public $config = null;
	public $categories = null;
	public $filters = null;

	public $upload_dir;
	public $upload_url;

	protected $locales = null;

	protected $t_pages;
	protected $t_pages_locales;
	protected $t_categories;
	protected $t_categories_locales;
	protected $t_permissions;

	protected $aParams = array();


	protected function prepend()
	{
		# permissions
		$this->okt->addPermGroup('pages', 	__('m_pages_perm_group'));
			$this->okt->addPerm('pages', 				__('m_pages_perm_global'), 'pages');
			$this->okt->addPerm('pages_categories', 	__('m_pages_perm_categories'), 'pages');
			$this->okt->addPerm('pages_add', 			__('m_pages_perm_add'), 'pages');
			$this->okt->addPerm('pages_remove', 		__('m_pages_perm_remove'), 'pages');
			$this->okt->addPerm('pages_display', 		__('m_pages_perm_display'), 'pages');
			$this->okt->addPerm('pages_config', 		__('m_pages_perm_config'), 'pages');

		# tables
		$this->t_pages 					= $this->db->prefix.'mod_pages';
		$this->t_pages_locales 			= $this->db->prefix.'mod_pages_locales';
		$this->t_permissions 			= $this->db->prefix.'mod_pages_permissions';
		$this->t_categories 			= $this->db->prefix.'mod_pages_categories';
		$this->t_categories_locales 	= $this->db->prefix.'mod_pages_categories_locales';

		# déclencheurs
		$this->triggers = new Triggers($this->okt);

		# config
		$this->config = $this->okt->newConfig('conf_pages');

		# répertoire upload
		$this->upload_dir = $this->okt->options->get('upload_dir').'/pages/';
		$this->upload_url = $this->okt->options->upload_url.'/pages/';

		# rubriques
		if ($this->config->categories['enable'])
		{
			$this->categories = new Categories(
				$this->okt,
				$this->t_pages,
				$this->t_pages_locales,
				$this->t_categories,
				$this->t_categories_locales,
				'id',
				'parent_id',
				'ord',
				'category_id',
				'language',
				array(
					'active',
					'ord'
				),
				array(
					'title',
					'title_tag',
					'title_seo',
					'slug',
					'content',
					'meta_description',
					'meta_keywords'
				)
			);
		}
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->pagesSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add(
				$this->getName(),
				null,
				null,
				20,
				$this->okt->checkPerm('pages'),
				null,
				$this->okt->page->pagesSubMenu,
				$this->okt->options->public_url.'/modules/'.$this->id().'/module_icon.png'
			);
				$this->okt->page->pagesSubMenu->add(
					__('c_a_menu_management'),
					$this->okt->adminRouter->generate('Pages_index'),
					in_array($this->okt->request->attributes->get('_route'), array('Pages_index', 'Pages_post')),
					1
				);
				$this->okt->page->pagesSubMenu->add(
					__('m_pages_menu_add_page'),
					$this->okt->adminRouter->generate('Pages_post_add'),
					$this->okt->request->attributes->get('_route') === 'Pages_post_add',
					2,
					$this->okt->checkPerm('pages_add')
				);
				$this->okt->page->pagesSubMenu->add(
					__('m_pages_menu_categories'),
					$this->okt->adminRouter->generate('Pages_categories'),
					in_array($this->okt->request->attributes->get('_route'), array('Pages_categories', 'Pages_category', 'Pages_category_add')),
					3,
					($this->config->categories['enable'] && $this->okt->checkPerm('pages_categories'))
				);
				$this->okt->page->pagesSubMenu->add(
					__('c_a_menu_display'),
					$this->okt->adminRouter->generate('Pages_display'),
					$this->okt->request->attributes->get('_route') === 'Pages_display',
					10,
					$this->okt->checkPerm('pages_display')
				);
				$this->okt->page->pagesSubMenu->add(
					__('c_a_menu_configuration'),
					$this->okt->adminRouter->generate('Pages_config'),
					$this->okt->request->attributes->get('_route') === 'Pages_config',
					20,
					$this->okt->checkPerm('pages_config')
				);
		}
	}

	protected function prepend_public()
	{
		$this->okt->triggers->registerTrigger('websiteAdminBarItems',
			array('Okatea\Modules\Pages\Module', 'websiteAdminBarItems'));
	}

	/**
	 * Ajout d'éléments à la barre admin côté publique.
	 *
	 * @param oktCore $okt
	 * @param arrayObject $aPrimaryAdminBar
	 * @param arrayObject $aSecondaryAdminBar
	 * @param arrayObject $aBasesUrl
	 * @return void
	 */
	public static function websiteAdminBarItems($okt, $aPrimaryAdminBar, $aSecondaryAdminBar, $aBasesUrl)
	{
		# lien ajouter une page
		if ($okt->checkPerm('pages_add'))
		{
			$aPrimaryAdminBar[200]['items'][100] = array(
				'href' => $aBasesUrl['admin'].'/module.php?m=pages&amp;action=add',
				'title' => __('m_pages_ab_page_title'),
				'intitle' => __('m_pages_ab_page')
			);
		}

		# modification de la page en cours
		if (isset($okt->page->module) && $okt->page->module == 'pages' && isset($okt->page->action) && $okt->page->action == 'item')
		{
			if (isset($okt->controller->rsPage) && $okt->controller->rsPage->isEditable())
			{
				$aPrimaryAdminBar[300] = array(
					'href' => $aBasesUrl['admin'].'/module.php?m=pages&amp;action=edit&amp;post_id='.$okt->controller->rsPage->id,
					'intitle' => __('m_pages_ab_edit_page')
				);
			}
		}
	}

	/**
	 * Indique si on as accès à la partie publique en fonction de la configuration.
	 *
	 * @return boolean
	 */
	public function isPublicAccessible()
	{
		# si on as pas le module users alors on as le droit
		if (!$this->moduleUsersExists()) {
			return true;
		}

		# si on est superadmin on as droit à tout
		if ($this->okt->user->is_superadmin) {
			return true;
		}

		# si on a le groupe id 0 (zero) alors tous le monde a droit
		# sinon il faut etre dans le bon groupe
		if (in_array(0,$this->config->perms) || in_array($this->okt->user->group_id,$this->config->perms)) {
			return true;
		}

		# toutes éventualités testées, on as pas le droit
		return false;
	}

	/**
	 * Initialisation des filtres
	 *
	 * @param string $part 	'public' ou 'admin'
	 */
	public function filtersStart($part='public')
	{
		if ($this->filters === null || !($this->filters instanceof Filters)) {
			$this->filters = new Filters($this->okt, $part);
		}
	}


	/* Gestion des pages
	----------------------------------------------------------*/

	/**
	 * Retourne une liste de pages sous forme de recordset selon des paramètres donnés.
	 *
	 * @param array $aParams 			Paramètres de requete
	 * @param boolean $bCountOnly 		Ne renvoi qu'un nombre de pages
	 * @return integer|Okatea\Modules\Pages\Recordset
	 */
	public function getPagesRecordset($aParams=array(), $bCountOnly=false)
	{
		$sReqPlus = '';

		if (!empty($aParams['id'])) {
			$sReqPlus .= ' AND p.id='.(integer)$aParams['id'].' ';
		}

		if (!empty($aParams['category_id'])) {
			$sReqPlus .= ' AND p.category_id='.(integer)$aParams['category_id'].' ';
		}

		if (!empty($aParams['slug'])) {
			$sReqPlus .= ' AND pl.slug=\''.$this->db->escapeStr($aParams['slug']).'\' ';
		}

		if (isset($aParams['active']))
		{
			if ($aParams['active'] == 0) {
				$sReqPlus .= 'AND p.active=0 ';
			}
			elseif ($aParams['active'] == 1) {
				$sReqPlus .= 'AND p.active=1 ';
			}
			elseif ($aParams['active'] == 2) {
				$sReqPlus .= '';
			}
		}
		else {
			$sReqPlus .= 'AND p.active=1 ';
		}

		if (!empty($aParams['search']))
		{
			$aWords = text::splitWords($aParams['search']);

			if (!empty($aWords))
			{
				foreach ($aWords as $i => $w) {
					$aWords[$i] = 'pl.words LIKE \'%'.$this->db->escapeStr($w).'%\' ';
				}
				$sReqPlus .= ' AND '.implode(' AND ',$aWords).' ';
			}
		}

		if ($bCountOnly)
		{
			$sQuery =
			'SELECT COUNT(p.id) AS num_pages '.
			$this->getSqlFrom($aParams).
			'WHERE 1 '.$sReqPlus;
		}
		else
		{
			$sQuery =
			'SELECT '.$this->getSelectFields($aParams).' '.
			$this->getSqlFrom($aParams).
			'WHERE 1 '.$sReqPlus;

			$sDirection = 'DESC';
			if (!empty($aParams['order_direction']) && strtoupper($aParams['order_direction']) == 'ASC') {
				$sDirection = 'ASC';
			}

			if (!empty($aParams['order'])) {
				$sQuery .= 'ORDER BY '.$aParams['order'].' '.$sDirection.' ';
			}
			else {
				$sQuery .= 'ORDER BY p.created_at '.$sDirection.' ';
			}

			if (!empty($aParams['limit'])) {
				$sQuery .= 'LIMIT '.$aParams['limit'].' ';
			}
		}

		if (($rs = $this->db->select($sQuery, 'Okatea\Modules\Pages\Recordset')) === false)
		{
			if ($bCountOnly) {
				return 0;
			}
			else {
				$rs = new Recordset(array());
				$rs->setCore($this->okt);
				return $rs;
			}
		}

		if ($bCountOnly) {
			return (integer)$rs->num_pages;
		}
		else {
			$rs->setCore($this->okt);
			return $rs;
		}
	}

	/**
	 * Retourne la chaine des champs pour le SELECT.
	 *
	 * @return string
	 */
	protected function getSelectFields($aParams)
	{
		$aFields = array(
			'p.id', 'p.user_id', 'p.category_id', 'p.active', 'p.created_at', 'p.updated_at', 'p.images', 'p.files', 'p.tpl',
			'pl.language', 'pl.title', 'pl.subtitle', 'pl.title_tag', 'pl.title_seo', 'pl.slug', 'pl.content', 'pl.meta_description', 'pl.meta_keywords', 'pl.words',
			'rl.title AS category_title', 'rl.slug AS category_slug', 'r.items_tpl AS category_items_tpl'
		);

		$oFields = new \ArrayObject($aFields);

		# -- TRIGGER MODULE PAGES : getPagesSelectFields
		$this->triggers->callTrigger('getPagesSelectFields', $oFields);

		return implode(', ', (array)$oFields);
	}

	/**
	 * Retourne la chaine FROM en fonction de paramètres.
	 *
	 * @param array $aParams
	 * @return string
	 */
	protected function getSqlFrom($aParams)
	{
		if (empty($aParams['language']))
		{
			$aFrom = array(
				'FROM '.$this->t_pages.' AS p ',
				'LEFT OUTER JOIN '.$this->t_pages_locales.' AS pl ON p.id=pl.page_id ',
				'LEFT OUTER JOIN '.$this->t_categories.' AS r ON r.id=p.category_id ',
				'LEFT OUTER JOIN '.$this->t_categories_locales.' AS rl ON r.id=rl.category_id '
			);
		}
		else
		{
			$aFrom = array(
				'FROM '.$this->t_pages.' AS p ',
				'INNER JOIN '.$this->t_pages_locales.' AS pl ON p.id=pl.page_id '.
					'AND pl.language=\''.$this->db->escapeStr($aParams['language']).'\' ',
				'LEFT OUTER JOIN '.$this->t_categories.' AS r ON r.id=p.category_id ',
				'LEFT OUTER JOIN '.$this->t_categories_locales.' AS rl ON r.id=rl.category_id '.
					'AND rl.language=\''.$this->db->escapeStr($aParams['language']).'\' '
			);
		}

		$oFrom = new \ArrayObject($aFrom);

		# -- TRIGGER MODULE PAGES : getPagesSqlFrom
		$this->triggers->callTrigger('getPagesSqlFrom', $oFrom);

		return implode(' ', (array)$oFrom);
	}

	/**
	 * Retourne une liste de pages sous forme de recordset selon des paramètres donnés
	 * et les prépares en vue d'un affichage.
	 *
	 * @param array $aParams 					Paramètres de requete
	 * @param integer $iTruncatChar (null) 		Nombre de caractère avant troncature du contenu
	 * @return object Okatea\Modules\Pages\Recordset
	 */
	public function getPages($aParams=array(), $iTruncatChar=null)
	{
		$rs = $this->getPagesRecordset($aParams);

		$this->preparePages($rs, $iTruncatChar);

		return $rs;
	}

	/**
	 * Retourne un compte du nombre de pages selon des paramètres donnés.
	 *
	 * @param array $aParams Paramètres de requete
	 * @return integer
	 */
	public function getPagesCount($aParams=array())
	{
		return $this->getPagesRecordset($aParams, true);
	}

	/**
	 * Retourne une page donnée sous forme de recordset.
	 *
	 * @param integer $mPageId 		Identifiant numérique ou slug de la page.
	 * @param integer $iActive 		Statut requis de la page
	 * @return object Okatea\Modules\Pages\Recordset
	 */
	public function getPage($mPageId, $iActive=2)
	{
		$aParams = array(
			'language' => $this->okt->user->language,
			'active' => $iActive
		);

		if (Utilities::isInt($mPageId)) {
			$aParams['id'] = $mPageId;
		}
		else {
			$aParams['slug'] = $mPageId;
		}

		$rs = $this->getPagesRecordset($aParams);

		$this->preparePage($rs);

		return $rs;
	}

	/**
	 * Indique si une page donnée existe.
	 *
	 * @param $iPageId
	 * @return boolean
	 */
	public function pageExists($iPageId)
	{
		if (empty($iPageId) || $this->getPagesRecordset(array('id'=>$iPageId, 'active'=>2))->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Retourne les localisations d'une page donnée.
	 *
	 * @param integer $iPageId
	 * @return recordset
	 */
	public function getPageI18n($iPageId)
	{
		$query =
		'SELECT * FROM '.$this->t_pages_locales.' '.
		'WHERE page_id='.(integer)$iPageId;

		if (($rs = $this->db->select($query)) === false) {
			$rs = new Recordset(array());
			return $rs;
		}

		return $rs;
	}

	/**
	 * Formatage des données d'un Recordset en vue d'un affichage d'une liste.
	 *
	 * @param Okatea\Modules\Pages\Recordset $rs
	 * @param integer $iTruncatChar (null)
	 * @return void
	 */
	public function preparePages(Recordset $rs, $iTruncatChar=null)
	{
		# on utilise une troncature personnalisée à cette préparation
		if (!is_null($iTruncatChar)) {
			$iNumCharBeforeTruncate = (integer)$iTruncatChar;
		}
		# on utilise la troncature de la configuration
		elseif ($this->config->public_truncat_char > 0) {
			$iNumCharBeforeTruncate = $this->config->public_truncat_char;
		}
		# on n'utilisent pas de troncature
		else {
			$iNumCharBeforeTruncate = 0;
		}

		$iCountLine = 0;
		while ($rs->fetch())
		{
			# odd/even
			$rs->odd_even = ($iCountLine%2 == 0 ? 'even' : 'odd');
			$iCountLine++;

			# formatages génériques
			$this->commonPreparation($rs);

			# troncature
			if ($iNumCharBeforeTruncate > 0)
			{
				$rs->content = html::clean($rs->content);
				$rs->content = text::cutString($rs->content, $iNumCharBeforeTruncate);
			}
		}
	}

	/**
	 * Formatage des données d'un Recordset en vue d'un affichage d'une page.
	 *
	 * @param Recordset $rs
	 * @return void
	 */
	public function preparePage(Recordset $rs)
	{
		# formatages génériques
		$this->commonPreparation($rs);
	}

	/**
	 * Formatages des données d'un Recordset communs aux listes et aux éléments.
	 *
	 * @param Recordset $rs
	 * @return void
	 */
	protected function commonPreparation(Recordset $rs)
	{
		# url page
		$rs->url = $this->okt->router->generate('pagesItem', array('slug' => $rs->slug));

		# url rubrique
		$rs->category_url = $rs->getCategoryUrl();

		# récupération des images
		$rs->images = $rs->getImagesInfo();

		# récupération des fichiers
		$rs->files = $rs->getFilesInfo();

		# contenu
		if (!$this->config->enable_rte) {
			$rs->content = Utilities::nlToP($rs->content);
		}

		# perform content replacements
		SimpleReplacements::setStartString('');
		SimpleReplacements::setEndString('');

		$aReplacements = array_merge(
			$this->okt->getCommonContentReplacementsVariables(),
			$this->okt->getImagesReplacementsVariables($rs->images)
		);

		$rs->content = SimpleReplacements::parse($rs->content, $aReplacements);
	}

	/**
	 * Créer une instance de cursor pour une page et la retourne.
	 *
	 * @param array $aPageData
	 * @return object cursor
	 */
	public function openPageCursor($aPageData=null)
	{
		$oCursor = $this->db->openCursor($this->t_pages);

		if (!empty($aPageData))
		{
			foreach ($aPageData as $k=>$v) {
				$oCursor->$k = $v;
			}
		}

		return $oCursor;
	}

	/**
	 * Ajout/modification des textes internationnalisés de la page.
	 *
	 * @param integer $iPageId
	 * @param array $aPageLocalesData
	 */
	protected function setPageI18n($iPageId, $aPageLocalesData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (empty($aPageLocalesData[$aLanguage['code']]['title'])) {
				continue;
			}

			$oCursor = $this->db->openCursor($this->t_pages_locales);

			$oCursor->page_id = $iPageId;

			$oCursor->language = $aLanguage['code'];

			foreach ($aPageLocalesData[$aLanguage['code']] as $k=>$v) {
				$oCursor->$k = $v;
			}

			$oCursor->content = $this->okt->HTMLfilter($oCursor->content);

			$oCursor->words = implode(' ',array_unique(text::splitWords($oCursor->title.' '.$oCursor->subtitle.' '.$oCursor->content)));

			$oCursor->meta_description = html::clean($oCursor->meta_description);

			$oCursor->meta_keywords = html::clean($oCursor->meta_keywords);

			$oCursor->insertUpdate();

			$this->setPageSlug($iPageId, $aLanguage['code']);
		}
	}

	/**
	 * Création du slug d'une page donnée dans une langue donnée.
	 *
	 * @param integer $iPageId
	 * @param string $sLanguage
	 * @return boolean
	 */
	protected function setPageSlug($iPageId, $sLanguage)
	{
		$rsPage = $this->getPages(array(
			'id' => $iPageId,
			'language' => $sLanguage,
			'active' => 2
		));

		if ($rsPage->isEmpty()) {
			throw new Exception(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
		}

		if (empty($rsPage->slug)) {
			$sUrl = $rsPage->title;
		}
		else {
			$sUrl = $rsPage->slug;
		}

		$sUrl = Utilities::strToSlug($sUrl, false);

		# Let's check if URL is taken…
		$rsTakenSlugs = $this->db->select(
			'SELECT slug FROM '.$this->t_pages_locales.' '.
			'WHERE slug=\''.$this->db->escapeStr($sUrl).'\' '.
			'AND page_id <> '.(integer)$iPageId.' '.
			'AND language=\''.$this->db->escapeStr($sLanguage).'\' '.
			'ORDER BY slug DESC'
		);

		if (!$rsTakenSlugs->isEmpty())
		{
			$rsCurrentSlugs = $this->db->select(
				'SELECT slug FROM '.$this->t_pages_locales.' '.
				'WHERE slug LIKE \''.$this->db->escapeStr($sUrl).'%\' '.
				'AND page_id <> '.(integer)$iPageId.' '.
				'AND language=\''.$this->db->escapeStr($sLanguage).'\' '.
				'ORDER BY slug DESC '
			);

			$a = array();
			while ($rsCurrentSlugs->fetch()) {
				$a[] = $rsCurrentSlugs->slug;
			}

			$sUrl = Utilities::getIncrementedString($a, $sUrl, '-');
		}

		$sQuery =
		'UPDATE '.$this->t_pages_locales.' SET '.
		'slug=\''.$this->db->escapeStr($sUrl).'\' '.
		'WHERE page_id='.(integer)$iPageId. ' '.
		'AND language=\''.$this->db->escapeStr($sLanguage).'\' ';

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return true;
	}

	/**
	 * Ajout d'une page.
	 *
	 * @param cursor $oCursor
	 * @param array $aPageLocalesData
	 * @param array $aPagePermsData
	 * @return integer
	 */
	public function addPage($oCursor, $aPageLocalesData, $aPagePermsData)
	{
		$sDate = date('Y-m-d H:i:s');
		$oCursor->created_at = $sDate;
		$oCursor->updated_at = $sDate;

		if (!$oCursor->insert()) {
			throw new Exception('Unable to insert page into database');
		}

		# récupération de l'ID
		$iNewId = $this->db->getLastID();

		# ajout des textes internationnalisés
		$this->setPageI18n($iNewId, $aPageLocalesData);

		# ajout des images
		if ($this->config->images['enable'] && $this->addImages($iNewId) === false) {
			throw new Exception('Unable to insert images page');
		}

		# ajout des fichiers
		if ($this->config->files['enable'] && $this->addFiles($iNewId) === false) {
			throw new Exception('Unable to insert files page');
		}

		# ajout permissions
		if (!$this->setPagePermissions($iNewId, (!empty($aPagePermsData) ? $aPagePermsData : array()))) {
			throw new Exception('Unable to set page permissions');
		}

		return $iNewId;
	}

	/**
	 * Mise à jour d'une page.
	 *
	 * @param cursor $oCursor
	 * @param array $aPageLocalesData
	 * @param array $aPagePermsData
	 * @return boolean
	 */
	public function updPage($oCursor, $aPageLocalesData, $aPagePermsData)
	{
		if (!$this->pageExists($oCursor->id)) {
			throw new Exception(sprintf(__('m_pages_page_%s_not_exists'), $oCursor->id));
		}

		# modification dans la DB
		$oCursor->updated_at = date('Y-m-d H:i:s');

		if (!$oCursor->update('WHERE id='.(integer)$oCursor->id.' ')) {
			throw new Exception('Unable to update page into database');
		}

		# modification des images
		if ($this->config->images['enable'] && $this->updImages($oCursor->id) === false) {
			throw new Exception('Unable to update images page');
		}

		# modification des fichiers
		if ($this->config->files['enable'] && $this->updFiles($oCursor->id) === false) {
			throw new Exception('Unable to update files page');
		}

		# modification permissions
		if (!$this->setPagePermissions($oCursor->id, (!empty($aPagePermsData) ? $aPagePermsData : array()))) {
			throw new Exception('Unable to set page permissions');
		}

		# modification des textes internationnalisés
		$this->setPageI18n($oCursor->id, $aPageLocalesData);

		return true;
	}

	/**
	 * Vérifie les données envoyées par formulaire.
	 *
	 * @param array $aPageData Le tableau de données de la page.
	 * @return boolean
	 */
	public function checkPostData($aPageData)
	{
		$bHasAtLeastOneTitle = false;
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (empty($aPageData['locales'][$aLanguage['code']]['title'])) {
				continue;
			}
			else {
				$bHasAtLeastOneTitle = true;
				break;
			}
		}

		if (!$bHasAtLeastOneTitle)
		{
			if ($this->okt->languages->unique) {
				$this->error->set(__('m_pages_page_must_enter_title'));
			}
			else {
				$this->error->set(__('m_pages_page_must_enter_at_least_one_title'));
			}
		}

		if ($this->canUsePerms() && empty($aPageData['perms'])) {
			$this->error->set(__('m_pages_page_must_set_perms'));
		}


		# -- TRIGGER MODULE PAGES : checkPostData
		$this->triggers->callTrigger('checkPostData', $aPageData);


		return $this->error->isEmpty();
	}

	/**
	 * Switch le statut de visibilité d'une page donnée
	 *
	 * @param integer $iPageId
	 * @return boolean
	 */
	public function switchPageStatus($iPageId)
	{
		if (!$this->pageExists($iPageId)) {
			throw new Exception(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
		}

		$sQuery =
		'UPDATE '.$this->t_pages.' SET '.
			'updated_at=NOW(), '.
			'active = 1-active '.
		'WHERE id='.(integer)$iPageId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to update page in database.');
		}

		return true;
	}

	/**
	 * Définit le statut de visibilité d'une page donnée
	 *
	 * @param integer $iPageId
	 * @param integer $status
	 * @return boolean
	 */
	public function setPageStatus($iPageId,$status)
	{
		if (!$this->pageExists($iPageId)) {
			throw new Exception(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
		}

		$sQuery =
		'UPDATE '.$this->t_pages.' SET '.
			'updated_at=NOW(), '.
			'active = '.($status == 1 ? 1 : 0).' '.
		'WHERE id='.(integer)$iPageId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to update page in database.');
		}

		return true;
	}

	/**
	 * Suppression d'une page.
	 *
	 * @param integer $iPageId
	 * @return boolean
	 */
	public function deletePage($iPageId)
	{
		if (!$this->pageExists($iPageId)) {
			throw new Exception(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
		}

		if ($this->deleteImages($iPageId) === false) {
			throw new Exception('Unable to delete images page.');
		}

		if ($this->deleteFiles($iPageId) === false) {
			throw new Exception('Unable to delete files page.');
		}

		$sQuery =
		'DELETE FROM '.$this->t_pages.' '.
		'WHERE id='.(integer)$iPageId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to remove page from database.');
		}

		$this->db->optimize($this->t_pages);

		$sQuery =
		'DELETE FROM '.$this->t_pages_locales.' '.
		'WHERE page_id='.(integer)$iPageId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to remove page locales from database.');
		}

		$this->db->optimize($this->t_pages_locales);

		$this->deletePagePermissions($iPageId);

		return true;
	}


	/* Gestion des permissions de pages
	----------------------------------------------------------*/

	/**
	 * Indique si les permissions sont utilisables.
	 *
	 * @return boolean
	 */
	public function canUsePerms()
	{
		static $bCanUse = null;

		if (is_null($bCanUse)) {
			$bCanUse = (boolean)($this->config->enable_group_perms && $this->moduleUsersExists());
		}

		return $bCanUse;
	}

	/**
	 * Indique si le module users est installé et activé.
	 *
	 * @return boolean
	 */
	public function moduleUsersExists()
	{
		static $bExists = null;

		if (is_null($bExists)) {
			$bExists = (boolean)$this->okt->modules->moduleExists('users');
		}

		return $bExists;
	}

	/**
	 * Retourne la liste des groupes pour les permissions.
	 *
	 * @param $bWithAdmin
	 * @param $bWithAll
	 * @return array
	 */
	public function getUsersGroupsForPerms($bWithAdmin=false,$bWithAll=false)
	{
		if (!$this->moduleUsersExists()) {
			return array();
		}

		$aParams = array(
			'group_id_not' => array(
				Authentification::guest_group_id,
				Authentification::superadmin_group_id
			)
		);

		if (!$this->okt->user->is_admin && !$bWithAdmin) {
			$aParams['group_id_not'][] = Authentification::admin_group_id;
		}

		$rsGroups = $this->okt->users->getGroups($aParams);

		$aGroups = array();

		if ($bWithAll) {
			$aGroups[] = __('c_c_All');
		}

		while ($rsGroups->fetch()) {
			$aGroups[$rsGroups->group_id] = html::escapeHTML($rsGroups->title);
		}

		return $aGroups;
	}

	/**
	 * Retourne les permissions d'une page donnée sous forme de tableau.
	 *
	 * @param integer $iPageId
	 * @return array
	 */
	public function getPagePermissions($iPageId)
	{
		$sQuery =
		'SELECT page_id, group_id '.
		'FROM '.$this->t_permissions.' '.
		'WHERE page_id='.(integer)$iPageId.' ';

		if (!$this->canUsePerms() || ($rs = $this->db->select($sQuery)) === false) {
			return array();
		}

		$aPerms = array();
		while ($rs->fetch()) {
			$aPerms[] = $rs->group_id;
		}

		return $aPerms;
	}

	/**
	 * Met à jour les permissions d'une page donnée.
	 *
	 * @param integer $iPageId
	 * @param array $aGroupsIds
	 * @return boolean
	 */
	protected function setPagePermissions($iPageId,$aGroupsIds)
	{
		if (!$this->canUsePerms() || empty($aGroupsIds)) {
			return $this->setDefaultPagePermissions($iPageId);
		}

		if (!$this->pageExists($iPageId)) {
			throw new Exception(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
		}

		# si l'utilisateur qui définit les permissions n'est pas un admin
		# alors on force la permission à ce groupe admin
		if (!$this->okt->user->is_admin) {
			$aGroupsIds[] = Authentification::admin_group_id;
		}

		# qu'une seule ligne par groupe pleaz
		$aGroupsIds = array_unique((array)$aGroupsIds);

		# liste des groupes existants réellement dans la base de données
		# (sauf invités et superadmin)
		$rsGroups = $this->okt->users->getGroups(array(
			'group_id_not' => array(
				Authentification::guest_group_id,
				Authentification::superadmin_group_id
			)
		));

		$aGroups = array();
		while ($rsGroups->fetch()) {
			$aGroups[] = $rsGroups->group_id;
		}
		unset($rsGroups);

		# suppression de toutes les permissions éventuellement existantes
		$this->deletePagePermissions($iPageId);

		# mise en base de données
		$return = true;
		foreach ($aGroupsIds as $iGroupId)
		{
			if ($iGroupId == 0 || in_array($iGroupId,$aGroups)) {
				$return = $return && $this->setPagePermission($iPageId,$iGroupId);
			}
		}

		return $return;
	}

	/**
	 * Met les permissions par défaut d'une page donnée.
	 *
	 * @param integer $iPageId
	 * @return boolean
	 */
	protected function setDefaultPagePermissions($iPageId)
	{
		if (!$this->pageExists($iPageId)) {
			throw new Exception(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
		}

		# suppression de toutes les permissions éventuellement existantes
		$this->deletePagePermissions($iPageId);

		# mise en base de données de la permission "tous" (0)
		return $this->setPagePermission($iPageId,0);
	}

	/**
	 * Insertion d'une permission donnée pour une page donnée.
	 *
	 * @param $iPageId
	 * @param $iGroupId
	 * @return boolean
	 */
	protected function setPagePermission($iPageId,$iGroupId)
	{
		$sQuery =
		'INSERT INTO '.$this->t_permissions.' '.
			'(page_id, group_id) '.
		'VALUES ('.
			(integer)$iPageId.', '.
			(integer)$iGroupId.' '.
		') ';

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to insert page permissions into database');
		}

		return true;
	}

	/**
	 * Supprime les permissions d'une page donnée.
	 *
	 * @param integer $iPageId
	 * @return boolean
	 */
	public function deletePagePermissions($iPageId)
	{
		$sQuery =
		'DELETE FROM '.$this->t_permissions.' '.
		'WHERE page_id='.(integer)$iPageId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to delete page permissions from database');
		}

		$this->db->optimize($this->t_permissions);

		return true;
	}


	/* Gestion des images des pages
	----------------------------------------------------------*/

	/**
	 * Retourne une instance de la classe oktImageUpload
	 *
	 * @return object oktImageUpload
	 */
	public function getImageUpload()
	{
		$o = new ImageUpload($this->okt, $this->config->images);
		$o->setConfig(array(
			'upload_dir' => $this->upload_dir.'img/',
			'upload_url' => $this->upload_url.'img/'
		));

		return $o;
	}

	/**
	 * Ajout d'image(s) à une page donnée
	 *
	 * @param $iPageId
	 * @return boolean
	 */
	public function addImages($iPageId)
	{
		$aImages = $this->getImageUpload()->addImages($iPageId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updImagesInDb($iPageId, $aImages);
	}

	/**
	 * Modification d'image(s) d'une page donnée
	 *
	 * @param $iPageId
	 * @return boolean
	 */
	public function updImages($iPageId)
	{
		$aCurrentImages = $this->getImagesFromDb($iPageId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aImages = $this->getImageUpload()->updImages($iPageId, $aCurrentImages);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updImagesInDb($iPageId, $aImages);
	}

	/**
	 * Suppression d'une image donnée d'une page donnée
	 *
	 * @param $iPageId
	 * @param $img_id
	 * @return boolean
	 */
	public function deleteImage($iPageId,$img_id)
	{
		$aCurrentImages = $this->getImagesFromDb($iPageId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aNewImages = $this->getImageUpload()->deleteImage($iPageId, $aCurrentImages, $img_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updImagesInDb($iPageId, $aNewImages);
	}

	/**
	 * Suppression des images d'une page donnée
	 *
	 * @param $iPageId
	 * @return boolean
	 */
	public function deleteImages($iPageId)
	{
		$aCurrentImages = $this->getImagesFromDb($iPageId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->getImageUpload()->deleteAllImages($iPageId, $aCurrentImages);

		return $this->updImagesInDb($iPageId);
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

		$rsPages = $this->getPages(array('active'=>2));

		while ($rsPages->fetch())
		{
			$aImages = $rsPages->getImagesInfo();
			$aImagesList = array();

			foreach ($aImages as $key=>$image)
			{
				$this->getImageUpload()->buildThumbnails($rsPages->id, $image['img_name']);

				$aImagesList[$key] = array_merge(
					$aImages[$key],
					$this->getImageUpload()->buildImageInfos($rsPages->id, $image['img_name'])
				);
			}

			$this->updImagesInDb($rsPages->id, $aImagesList);
		}

		return true;
	}

	/**
	 * Récupère la liste des images d'une page donnée
	 *
	 * @param $iPageId
	 * @return array
	 */
	public function getImagesFromDb($iPageId)
	{
		if (!$this->pageExists($iPageId)) {
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		$rsPage = $this->getPagesRecordset(array(
			'id' => $iPageId
		));

		$aImages = $rsPage->images ? unserialize($rsPage->images) : array();

		return $aImages;
	}

	/**
	 * Met à jours la liste des images d'une page donnée
	 *
	 * @param array $iPageId
	 * @param $aImages
	 * @return boolean
	 */
	public function updImagesInDb($iPageId, $aImages=array())
	{
		if (!$this->pageExists($iPageId)) {
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		$aImages = !empty($aImages) ? serialize($aImages) : NULL;

		$sQuery =
		'UPDATE '.$this->t_pages.' SET '.
			'images='.(!is_null($aImages) ? '\''.$this->db->escapeStr($aImages).'\'' : 'NULL').' '.
		'WHERE id='.(integer)$iPageId;

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return true;
	}


	/* Gestion des fichiers des pages
	----------------------------------------------------------*/

	/**
	 * Retourne une instance de la classe fileUpload
	 *
	 * @return object
	 */
	protected function getFileUpload()
	{
		return new FileUpload(
			$this->okt,
			$this->config->files,
			$this->upload_dir.'files/',
			$this->upload_url.'files/'
		);
	}

	/**
	 * Ajout de fichier(s) à une page donnée
	 *
	 * @param $iPageId
	 * @return boolean
	 */
	public function addFiles($iPageId)
	{
		$aFiles = $this->getFileUpload()->addFiles($iPageId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updPageFiles($iPageId, $aFiles);
	}

	/**
	 * Modification de fichier(s) d'une page donnée
	 *
	 * @param $iPageId
	 * @return boolean
	 */
	public function updFiles($iPageId)
	{
		$aCurrentFiles = $this->getPageFiles($iPageId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aFiles = $this->getFileUpload()->updFiles($iPageId,$aCurrentFiles);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updPageFiles($iPageId, $aFiles);
	}

	/**
	 * Suppression d'un fichier donné d'une page donnée
	 *
	 * @param $iPageId
	 * @param $file_id
	 * @return boolean
	 */
	public function deleteFile($iPageId,$file_id)
	{
		$aCurrentFiles = $this->getPageFiles($iPageId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aNewFiles = $this->getFileUpload()->deleteFile($iPageId,$aCurrentFiles,$file_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updPageFiles($iPageId,$aNewFiles);
	}

	/**
	 * Suppression des fichiers d'une page donnée
	 *
	 * @param $iPageId
	 * @return boolean
	 */
	public function deleteFiles($iPageId)
	{
		$aCurrentFiles = $this->getPageFiles($iPageId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->getFileUpload()->deleteAllFiles($aCurrentFiles);

		return $this->updPageFiles($iPageId);
	}

	/**
	 * Récupère la liste des fichiers d'une page donnée
	 *
	 * @param $iPageId
	 * @return array
	 */
	public function getPageFiles($iPageId)
	{
		if (!$this->pageExists($iPageId)) {
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		$rsPage = $this->getPagesRecordset(array(
			'id' => $iPageId
		));

		$aFiles = $rsPage->files ? unserialize($rsPage->files) : array();

		return $aFiles;
	}

	/**
	 * Met à jours la liste des fichiers d'une page donnée
	 *
	 * @param integer $iPageId
	 * @param array $aFiles
	 * @return boolean
	 */
	public function updPageFiles($iPageId, $aFiles=array())
	{
		if (!$this->pageExists($iPageId)) {
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		$aFiles = !empty($aFiles) ? serialize($aFiles) : NULL;

		$sQuery =
		'UPDATE '.$this->t_pages.' SET '.
			'files='.(!is_null($aFiles) ? '\''.$this->db->escapeStr($aFiles).'\'' : 'NULL').' '.
		'WHERE id='.(integer)$iPageId;

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return true;
	}



	/* Utilitaires
	----------------------------------------------------------*/

	/**
	 * Retourne le chemin du template de la liste des pages.
	 *
	 * @return string
	 */
	public function getListTplPath()
	{
		return 'pages/list/'.$this->config->templates['list']['default'].'/template';
	}

	/**
	 * Retourne le chemin du template du flux des pages.
	 *
	 * @return string
	 */
	public function getFeedTplPath()
	{
		return 'pages/feed/'.$this->config->templates['feed']['default'].'/template';
	}

	/**
	 * Retourne le chemin du template de l'encart des pages.
	 *
	 * @return string
	 */
	public function getInsertTplPath()
	{
		return 'pages/insert/'.$this->config->templates['insert']['default'].'/template';
	}

	/**
	 * Retourne le chemin du template de la liste des pages d'une rubrique.
	 *
	 * @return string
	 */
	public function getCategoryTplPath($sCategoryTemplate=null)
	{
		$sTemplate = $this->config->templates['list']['default'];

		if (!empty($sCategoryTemplate) && in_array($sCategoryTemplate, $this->config->templates['list']['usables'])) {
			$sTemplate = $sCategoryTemplate;
		}

		return 'pages/list/'.$sTemplate.'/template';
	}

	/**
	 * Retourne le chemin du template d'une page.
	 *
	 * @return string
	 */
	public function getItemTplPath($sPageTemplate=null, $sCatPageTemplate=null)
	{
		$sTemplate = $this->config->templates['item']['default'];

		if (!empty($sPageTemplate) && in_array($sPageTemplate, $this->config->templates['item']['usables'])) {
			$sTemplate = $sPageTemplate;
		}
		elseif (!empty($sCatPageTemplate) && in_array($sCatPageTemplate, $this->config->templates['item']['usables'])) {
			$sTemplate = $sCatPageTemplate;
		}

		return 'pages/item/'.$sTemplate.'/template';
	}

	/**
	 * Reconstruction des index de recherche de toutes les pages.
	 *
	 */
	public function indexAllPages()
	{
		$rsPages = $this->getPages(array('active' => 2));

		while ($rsPages->fetch())
		{
			$words =
				$rsPages->title.' '.
				$rsPages->subtitle.' '.
				$rsPages->content.' ';

			$words = implode(' ',text::splitWords($words));

			$query =
			'UPDATE '.$this->t_pages.' SET '.
				'words=\''.$this->db->escapeStr($words).'\' '.
			'WHERE id='.(integer)$rsPages->id;

			$this->db->execute($query);
		}

		return true;
	}


	/* Helpers
	----------------------------------------------------------*/

	/**
	 * Retourne sous forme de liste HTML les pages d'une catégorie donnée.
	 *
	 * @param integer $iCatId
	 * @param string $sBlockFormat 	Masque de formatage du bloc ('<ul>%s</ul>')
	 * @param string $sItemFormat 	Masque de formatage d'un élément ('<li>%s</li>')
	 * @param string $sItemActiveFormat 	Masque de formatage d'un élément actif ('<li class="active"><strong>%s</strong></li>')
	 * @param string $sLinkFormat 	Masque de formatage d'un lien ('<a href="%s">%s</a>')
	 * @param string $sItemsGlue 	Liant entre les différents éléments ('')
	 * @param array $aCustomParams Paramètres de sélection personnalisés (array())
	 * @return string
	 * @deprecated use PagesHelpers::getPagesByCatId() instead
	 */
	public function getPagesByCatId($iCatId, $sBlockFormat='<ul>%s</ul>', $sItemFormat='<li>%s</li>', $sItemActiveFormat='<li class="active"><strong>%s</strong></li>', $sLinkFormat='<a href="%s">%s</a>', $sItemsGlue='', $aCustomParams=array())
	{
		trigger_error('Deprecated method, please use PagesHelpers::getPagesByCatId() instead', E_USER_WARNING);

		return PagesHelpers::getPagesByCatId($iCatId, $sBlockFormat, $sItemFormat, $sItemActiveFormat, $sLinkFormat, $sItemsGlue, $aCustomParams);
	}

	/**
	 * Retourne sous forme de liste HTML les sous-catégories d'une catégorie donnée.
	 *
	 * @param integer $iCatId				L'identifiant de la catégorie a lister.
	 * @param string $sBlockFormat			Masque de formatage du bloc de la liste.
	 * @param string $sItemFormat 			Masque de formatage d'un élément de la liste.
	 * @param string $sItemActiveFormat 	Masque de formatage de l'élément actif de la liste.
	 * @param string $sLinkFormat 			Masque de formatage d'un lien de la liste.
	 * @param string $sItemsGlue 			Chaine de liaison entre les éléments.
	 * @return string
	 */
	public function getSubCatsByCatId($iCatId, $sBlockFormat='<ul>%s</ul>', $sItemFormat='<li>%s</li>', $sItemActiveFormat='<li class="active"><strong>%s</strong></li>', $sLinkFormat='<a href="%s">%s</a>', $sItemsGlue='')
	{
		trigger_error('Deprecated method, please use PagesHelpers::getSubCatsByCatId() instead', E_USER_WARNING);

		return PagesHelpers::getSubCatsByCatId($iCatId, $sBlockFormat, $sItemFormat, $sItemActiveFormat, $sLinkFormat, $sItemsGlue);
	}


}
