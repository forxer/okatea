<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Okatea\Module\News;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Tao\Admin\Menu as AdminMenu;
use Tao\Admin\Page;
use Tao\Core\Authentification;
use Tao\Core\Triggers;
use Tao\Images\ImageUpload;
use Tao\Misc\Utilities;
use Tao\Misc\FileUpload;
use Tao\Modules\Module as BaseModule;
use Tao\Themes\SimpleReplacements;

class Module extends BaseModule
{
	public $config = null;
	public $categories = null;
	public $filters = null;

	public $upload_dir;
	public $upload_url;

	protected $locales = null;

	protected $t_news;
	protected $t_news_locales;
	protected $t_categories;
	protected $t_categories_locales;
	protected $t_permissions;
	protected $t_users;


	protected function prepend()
	{
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'Okatea\Module\News\Categories' 	=> __DIR__.'/Categories.php',
			'Okatea\Module\News\Controller' 	=> __DIR__.'/Controller.php',
			'Okatea\Module\News\Filters' 		=> __DIR__.'/Filters.php',
			'Okatea\Module\News\Helpers' 		=> __DIR__.'/Helpers.php',
			'Okatea\Module\News\Recordset' 		=> __DIR__.'/Recordset.php'
		));

		# permissions
		$this->okt->addPermGroup('news', 		__('m_news_perm_group'));
			$this->okt->addPerm('news_usage', 			__('m_news_perm_global'), 'news');
			$this->okt->addPerm('news_show_all', 		__('m_news_perm_show_all'), 'news');
			$this->okt->addPerm('news_publish', 		__('m_news_perm_publish'), 'news');
			$this->okt->addPerm('news_delete', 			__('m_news_perm_delete'), 'news');
			$this->okt->addPerm('news_contentadmin', 	__('m_news_perm_contentadmin'), 'news');
			$this->okt->addPerm('news_categories', 		__('m_news_perm_categories'), 'news');
			$this->okt->addPerm('news_display', 		__('m_news_perm_display'), 'news');
			$this->okt->addPerm('news_config', 			__('m_news_perm_config'), 'news');

		# tables
		$this->t_news 				= $this->db->prefix.'mod_news';
		$this->t_news_locales 		= $this->db->prefix.'mod_news_locales';
		$this->t_categories 		= $this->db->prefix.'mod_news_categories';
		$this->t_categories_locales = $this->db->prefix.'mod_news_categories_locales';
		$this->t_permissions 		= $this->db->prefix.'mod_news_permissions';
		$this->t_users 				= $this->db->prefix.'core_users';

		# déclencheurs
		$this->triggers = new Triggers();

		# config
		$this->config = $this->okt->newConfig('conf_news');

		# répertoire upload
		$this->upload_dir = $this->okt->options->get('upload_dir').'/news/';
		$this->upload_url = $this->okt->options->upload_url.'/news/';

		# rubriques
		if ($this->config->categories['enable'])
		{
			$this->categories = new Categories(
				$this->okt,
				$this->t_news,
				$this->t_news_locales,
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
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'Okatea\Module\News\Admin\Controller\Index' 		=> __DIR__.'/Admin/Controller/Index.php',
			'Okatea\Module\News\Admin\Controller\Post' 			=> __DIR__.'/Admin/Controller/Post.php',
			'Okatea\Module\News\Admin\Controller\Categories' 	=> __DIR__.'/Admin/Controller/Categories.php',
			'Okatea\Module\News\Admin\Controller\Category' 		=> __DIR__.'/Admin/Controller/Category.php',
			'Okatea\Module\News\Admin\Controller\Display' 		=> __DIR__.'/Admin/Controller/Display.php',
			'Okatea\Module\News\Admin\Controller\Config' 		=> __DIR__.'/Admin/Controller/Config.php'
		));

		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->newsSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);

			$this->okt->page->mainMenu->add($this->getName(),
				$this->okt->adminRouter->generate('News_index'),
				$this->okt->request->attributes->get('_route') === 'News_index',
				20,
				($this->okt->checkPerm('news_usage') || $this->okt->checkPerm('news_contentadmin')),
				null,
				$this->okt->page->newsSubMenu,
				$this->url().'/icon.png'
			);
				$this->okt->page->newsSubMenu->add(__('c_a_menu_management'),
					$this->okt->adminRouter->generate('News_index'),
					in_array($this->okt->request->attributes->get('_route'), array('News_index', 'News_post')),
					1
				);
				$this->okt->page->newsSubMenu->add(__('m_news_menu_add_post'),
					$this->okt->adminRouter->generate('News_post_add'),
					$this->okt->request->attributes->get('_route') === 'News_post_add',
					2
				);
				$this->okt->page->newsSubMenu->add(__('m_news_menu_categories'),
					$this->okt->adminRouter->generate('News_categories'),
					$this->okt->request->attributes->get('_route') === 'News_categories',
					3,
					($this->config->categories['enable'] && $this->okt->checkPerm('news_categories'))
				);
				$this->okt->page->newsSubMenu->add(__('c_a_menu_display'),
					$this->okt->adminRouter->generate('News_display'),
					$this->okt->request->attributes->get('_route') === 'News_display',
					10,
					$this->okt->checkPerm('news_display')
				);
				$this->okt->page->newsSubMenu->add(__('c_a_menu_configuration'),
					$this->okt->adminRouter->generate('News_config'),
					$this->okt->request->attributes->get('_route') === 'News_config',
					20,
					$this->okt->checkPerm('news_config')
				);
		}
	}

	protected function prepend_public()
	{
		# Publication des articles différés
		$this->publishScheduledPosts();

		# Ajout d'éléments à la barre admin
		$this->okt->triggers->registerTrigger('publicAdminBarItems',
			array('Okatea\Module\News\Module', 'publicAdminBarItems'));
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
	public static function publicAdminBarItems($okt, $aPrimaryAdminBar, $aSecondaryAdminBar, $aBasesUrl)
	{
		# lien ajouter un article
		if ($okt->checkPerm('news_usage') || $okt->checkPerm('news_contentadmin'))
		{
			$aPrimaryAdminBar[200]['items'][200] = array(
				'href' => $okt->adminRouter->generateFromWebsite('News_post_add'),
				'title' => __('m_news_ab_post_title'),
				'intitle' => __('m_news_ab_post')
			);
		}

		# modification de l'article en cours
		if (isset($okt->page->module) && $okt->page->module == 'news' && isset($okt->page->action) && $okt->page->action == 'item')
		{
			if (isset($okt->controller->rsPost) && $okt->controller->rsPost->isEditable())
			{
				$aPrimaryAdminBar[300] = array(
					'href' => $okt->adminRouter->generateFromWebsite('News_post', array('post_id' => $okt->controller->rsPost->id)),
					'intitle' => __('m_news_ab_edit_post')
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
		if (in_array(0,$this->config->perms) || in_array($this->okt->user->group_id, $this->config->perms)) {
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
			$this->filters = new Filters($this->okt,$part);
		}
	}


	/* Gestion des articles d'actualité
	----------------------------------------------------------*/

	/**
	 * Retourne une liste d'articles sous forme de recordset selon des paramètres donnés.
	 *
	 * @param array $aParams 		Paramètres de requete
	 * @param boolean $bCountOnly 	Ne renvoi qu'un nombre d'articles
	 * @return object Recordset/integer
	 */
	public function getPostsRecordset($aParams=array(), $bCountOnly=false)
	{
		$sReqPlus = '';

		if (!empty($aParams['id'])) {
			$sReqPlus .= ' AND p.id='.(integer)$aParams['id'].' ';
		}

		if (!empty($aParams['user_id'])) {
			$sReqPlus .= ' AND p.user_id='.(integer)$aParams['user_id'].' ';
		}

		if (!empty($aParams['category_id'])) {
			$sReqPlus .= ' AND p.category_id='.(integer)$aParams['category_id'].' ';
		}

		if (!empty($aParams['selected'])) {
			$sReqPlus .= ' AND p.selected='.(integer)$aParams['selected'].' ';
		}

		if (!empty($aParams['slug'])) {
			$sReqPlus .= ' AND pl.slug=\''.$this->db->escapeStr($aParams['slug']).'\' ';
		}

		if (!empty($aParams['created_after'])) {
			$sReqPlus .= ' AND created_at>=\''.$this->db->escapeStr($aParams['created_after']).'\' ';
		}

		if (!empty($aParams['created_before'])) {
			$sReqPlus .= ' AND created_at<=\''.$this->db->escapeStr($aParams['created_before']).'\' ';
		}

		if (!empty($aParams['pending'])) {
			$sReqPlus .= 'AND p.active=2 ';
		}
		elseif (!empty($aParams['scheduled'])) {
			$sReqPlus .= 'AND p.active=3 ';
		}
		elseif (isset($aParams['active']))
		{
			if ($aParams['active'] == 0) {
				$sReqPlus .= 'AND p.active=0 ';
			}
			elseif ($aParams['active'] == 1) {
				$sReqPlus .= 'AND p.active=1 ';
			}
			elseif ($aParams['active'] == 2) {
				$sReqPlus .= 'AND p.active=2 ';
			}
			elseif ($aParams['active'] == 3) {
				$sReqPlus .= 'AND p.active=3 ';
			}
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
			'SELECT COUNT(p.id) AS num_posts '.
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
				$sQuery .= 'ORDER BY p.selected DESC, '.$aParams['order'].' '.$sDirection.' ';
			}
			else {
				$sQuery .= 'ORDER BY p.selected DESC, p.created_at '.$sDirection.' ';
			}

			if (!empty($aParams['limit'])) {
				$sQuery .= 'LIMIT '.$aParams['limit'].' ';
			}
		}

		if (($rsPosts = $this->db->select($sQuery, 'Okatea\Module\News\Recordset')) === false)
		{
			if ($bCountOnly) {
				return 0;
			}
			else {
				$rsPosts = new Recordset(array());
				$rsPosts->setCore($this->okt);
				return $rsPosts;
			}
		}

		if ($bCountOnly) {
			return (integer)$rsPosts->num_posts;
		}
		else {
			$rsPosts->setCore($this->okt);
			return $rsPosts;
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
			'p.id', 'p.user_id', 'p.category_id', 'p.active', 'p.selected', 'p.created_at', 'p.updated_at', 'p.images', 'p.files', 'p.tpl',
			'pl.language', 'pl.title', 'pl.subtitle', 'pl.title_tag', 'pl.title_seo', 'pl.slug', 'pl.content', 'pl.meta_description', 'pl.meta_keywords', 'pl.words',
			'u.username', 'u.lastname', 'u.firstname',
			'rl.title AS category_title', 'rl.slug AS category_slug', 'r.items_tpl AS category_items_tpl'
		);

		$oFields = new \ArrayObject($aFields);

		# -- TRIGGER MODULE NEWS : getPostsSelectFields
		$this->triggers->callTrigger('getPostsSelectFields', $oFields);

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
				'FROM '.$this->t_news.' AS p ',
				'LEFT OUTER JOIN '.$this->t_users.' AS u ON u.id=p.user_id ',
				'LEFT OUTER JOIN '.$this->t_news_locales.' AS pl ON p.id=pl.post_id ',
				'LEFT OUTER JOIN '.$this->t_categories.' AS r ON r.id=p.category_id ',
				'LEFT OUTER JOIN '.$this->t_categories_locales.' AS rl ON r.id=rl.category_id '
			);
		}
		else
		{
			$aFrom = array(
				'FROM '.$this->t_news.' AS p ',
				'LEFT OUTER JOIN '.$this->t_users.' AS u ON u.id=p.user_id ',
				'INNER JOIN '.$this->t_news_locales.' AS pl ON p.id=pl.post_id '.
					'AND pl.language=\''.$this->db->escapeStr($aParams['language']).'\' ',
				'LEFT OUTER JOIN '.$this->t_categories.' AS r ON r.id=p.category_id ',
				'LEFT OUTER JOIN '.$this->t_categories_locales.' AS rl ON r.id=rl.category_id '.
					'AND rl.language=\''.$this->db->escapeStr($aParams['language']).'\' '
			);
		}

		$oFrom = new \ArrayObject($aFrom);

		# -- TRIGGER MODULE NEWS : getPostsSqlFrom
		$this->triggers->callTrigger('getPostsSqlFrom', $oFrom);

		return implode(' ', (array)$oFrom);
	}

	/**
	 * Retourne une liste d'articles sous forme de recordset selon des paramètres donnés
	 * et les prépares en vue d'un affichage.
	 *
	 * @param array $aParams 					Paramètres de requete
	 * @param integer $iTruncatChar (null) 		Nombre de caractère avant troncature du contenu
	 * @return object Recordset
	 */
	public function getPosts($aParams=array(), $iTruncatChar=null)
	{
		$rs = $this->getPostsRecordset($aParams);

		$this->preparePosts($rs, $iTruncatChar);

		return $rs;
	}

	/**
	 * Retourne un compte du nombre d'articles selon des paramètres donnés.
	 *
	 * @param array $aParams 					Paramètres de requete
	 * @return integer
	 */
	public function getPostsCount($aParams=array())
	{
		return $this->getPostsRecordset($aParams, true);
	}

	/**
	 * Retourne un article donné sous forme de recordset.
	 *
	 * @param integer $mPostId 		Identifiant numérique ou slug de l'article.
	 * @param integer $iActive
	 * @return object recordset
	 */
	public function getPost($mPostId, $iActive=null)
	{
		$aParams = array(
			'language' => $this->okt->user->language
		);

		if (!is_null($iActive)) {
			$aParams['active'] = $iActive;
		}

		if (Utilities::isInt($mPostId)) {
			$aParams['id'] = $mPostId;
		}
		else {
			$aParams['slug'] = $mPostId;
		}

		$rs = $this->getPostsRecordset($aParams);

		$this->preparePost($rs);

		return $rs;
	}

	/**
	 * Indique si un article donné existe.
	 *
	 * @param $iPostId
	 * @return boolean
	 */
	public function postExists($iPostId)
	{
		if (empty($iPostId) || $this->getPostsRecordset(array('id'=>$iPostId))->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Retourne les localisations d'un article donné.
	 *
	 * @param integer $iPostId
	 * @return recordset
	 */
	public function getPostI18n($iPostId)
	{
		$query =
		'SELECT * FROM '.$this->t_news_locales.' '.
		'WHERE post_id='.(integer)$iPostId;

		if (($rsPostLocales = $this->db->select($query)) === false) {
			$rsPostLocales = new Recordset(array());
			return $rsPostLocales;
		}

		return $rsPostLocales;
	}

	/**
	 * Formatage des données d'un Recordset en vue d'un affichage d'une liste.
	 *
	 * @param Recordset $rsPosts
	 * @param integer $iTruncatChar (null)
	 * @return void
	 */
	public function preparePosts(Recordset $rsPosts, $iTruncatChar=null)
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
		while ($rsPosts->fetch())
		{
			# odd/even
			$rsPosts->odd_even = ($iCountLine%2 == 0 ? 'even' : 'odd');
			$iCountLine++;

			# formatages génériques
			$this->commonPreparation($rsPosts);

			# troncature
			if ($iNumCharBeforeTruncate > 0)
			{
				$rsPosts->content = \html::clean($rsPosts->content);
				$rsPosts->content = \text::cutString($rsPosts->content, $iNumCharBeforeTruncate);
			}
		}
	}

	/**
	 * Formatage des données d'un Recordset en vue d'un affichage d'un article.
	 *
	 * @param Recordset $rsPost
	 * @return void
	 */
	public function preparePost(Recordset $rsPost)
	{
		# formatages génériques
		$this->commonPreparation($rsPost);
	}

	/**
	 * Formatages des données d'un Recordset communs aux listes et aux éléments.
	 *
	 * @param Recordset $rsPost
	 * @return void
	 */
	protected function commonPreparation(Recordset $rsPost)
	{
		# url post
		$rsPost->url = $rsPost->getPostUrl();

		# url rubrique
		if ($this->config->categories['enable'])
			$rsPost->category_url = $rsPost->getCategoryUrl();

		# author
		$rsPost->author = $rsPost->getPostAuthor();

		# récupération des images
		$rsPost->images = $rsPost->getImagesInfo();

		# récupération des fichiers
		$rsPost->files = $rsPost->getFilesInfo();

		# contenu
		if (!$this->config->enable_rte) {
			$rsPost->content = Utilities::nlToP($rsPost->content);
		}

		# perform content replacements
		SimpleReplacements::setStartString('');
		SimpleReplacements::setEndString('');

		$aReplacements = array_merge(
			$this->okt->getCommonContentReplacementsVariables(),
			$this->okt->getImagesReplacementsVariables($rsPost->images)
		);

		$rsPost->content = SimpleReplacements::parse($rsPost->content, $aReplacements);
	}

	/**
	 * Créer une instance de cursor pour un article et la retourne.
	 *
	 * @param array $aPostData
	 * @return object cursor
	 */
	public function openPostCursor($aPostData=null)
	{
		$oCursor = $this->db->openCursor($this->t_news);

		if (!empty($aPostData))
		{
			foreach ($aPostData as $k=>$v) {
				$oCursor->$k = $v;
			}
		}

		return $oCursor;
	}

	/**
	 * Ajout/modification des textes internationnalisés de l'article.
	 *
	 * @param integer $iPostId
	 * @param array $aPostLocalesData
	 */
	protected function setPostI18n($iPostId, $aPostLocalesData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$oCursor = $this->db->openCursor($this->t_news_locales);

			$oCursor->post_id = $iPostId;

			$oCursor->language = $aLanguage['code'];

			foreach ($aPostLocalesData[$aLanguage['code']] as $k=>$v) {
				$oCursor->$k = $v;
			}

			$oCursor->content = $this->okt->HTMLfilter($oCursor->content);

			$oCursor->words = implode(' ',array_unique(\text::splitWords($oCursor->title.' '.$oCursor->subtitle.' '.$oCursor->content)));

			$oCursor->meta_description = \html::clean($oCursor->meta_description);

			$oCursor->meta_keywords = \html::clean($oCursor->meta_keywords);

			$oCursor->insertUpdate();

			$this->setPostSlug($iPostId, $aLanguage['code']);
		}
	}

	/**
	 * Création du slug d'un article donné dans une langue donnée.
	 *
	 * @param integer $iPostId
	 * @param string $sLanguage
	 * @return boolean
	 */
	protected function setPostSlug($iPostId, $sLanguage)
	{
		$rsPost = $this->getPostsRecordset(array(
			'id' => $iPostId,
			'language' => $sLanguage
		));

		if ($rsPost->isEmpty()) {
			throw new \Exception(sprintf(__('m_news_post_%s_not_exists'), $iPostId));
		}

		if (empty($rsPost->slug)) {
			$sUrl = $rsPost->title;
		}
		else {
			$sUrl = $rsPost->slug;
		}

		$sUrl = Utilities::strToSlug($sUrl, false);

		# Let's check if URL is taken…
		$rsTakenSlugs = $this->db->select(
			'SELECT slug FROM '.$this->t_news_locales.' '.
			'WHERE slug=\''.$this->db->escapeStr($sUrl).'\' '.
			'AND post_id <> '.(integer)$iPostId.' '.
			'AND language=\''.$this->db->escapeStr($sLanguage).'\' '.
			'ORDER BY slug DESC'
		);

		if (!$rsTakenSlugs->isEmpty())
		{
			$rsCurrentSlugs = $this->db->select(
				'SELECT slug FROM '.$this->t_news_locales.' '.
				'WHERE slug LIKE \''.$this->db->escapeStr($sUrl).'%\' '.
				'AND post_id <> '.(integer)$iPostId.' '.
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
		'UPDATE '.$this->t_news_locales.' SET '.
		'slug=\''.$this->db->escapeStr($sUrl).'\' '.
		'WHERE post_id='.(integer)$iPostId. ' '.
		'AND language=\''.$this->db->escapeStr($sLanguage).'\' ';

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return true;
	}

	/**
	 * Ajout d'un article.
	 *
	 * @param cursor $oCursor
	 * @param array $aPostLocalesData
	 * @param array $aPostPermsData
	 * @return integer
	 */
	public function addPost($oCursor, $aPostLocalesData, $aPostPermsData)
	{
		# insertion dans la DB
		$this->preparePostCursor($oCursor);

		$oCursor->user_id = $this->okt->user->id;

		if (!$oCursor->insert()) {
			throw new \Exception('Unable to insert post into database');
		}

		# récupération de l'ID
		$iNewId = $this->db->getLastID();

		# ajout des textes internationnalisés
		$this->setPostI18n($iNewId, $aPostLocalesData);

		# ajout des images
		if ($this->config->images['enable'] && $this->addImages($iNewId) === false) {
			throw new \Exception('Unable to insert images post');
		}

		# ajout des fichiers
		if ($this->config->files['enable'] && $this->addFiles($iNewId) === false) {
			throw new \Exception('Unable to insert files post');
		}

		# ajout permissions
		if (!$this->setPostPermissions($iNewId, (!empty($aPostPermsData) ? $aPostPermsData : array()))) {
			throw new \Exception('Unable to set post permissions');
		}

		return $iNewId;
	}

	/**
	 * Mise à jour d'un article.
	 *
	 * @param cursor $oCursor
	 * @param array $aPostLocalesData
	 * @param array $aPostPermsData
	 * @return boolean
	 */
	public function updPost($oCursor, $aPostLocalesData, $aPostPermsData)
	{
		$rsPost = $this->getPostsRecordset(array(
			'id' => $oCursor->id
		));

		if ($rsPost->isEmpty()) {
			throw new \Exception(sprintf(__('m_news_post_%s_not_exists'), $oCursor->id));
		}

		if (!$rsPost->isEditable()) {
			throw new \Exception(__('m_news_post_not_editable'));
		}

		# modification dans la DB
		$this->preparePostCursor($oCursor);

		if (!$oCursor->update('WHERE id='.(integer)$oCursor->id.' ')) {
			throw new \Exception('Unable to update post into database');
		}

		# modification des images
		if ($this->config->images['enable'] && $this->updImages($oCursor->id) === false) {
			throw new \Exception('Unable to update files post');
		}

		# modification des fichiers
		if ($this->config->files['enable'] && $this->updFiles($oCursor->id) === false) {
			throw new \Exception('Unable to update files post');
		}

		# modification permissions
		if (!$this->setPostPermissions($oCursor->id, (!empty($aPostPermsData) ? $aPostPermsData : array()))) {
			throw new \Exception('Unable to set post permissions');
		}

		# modification des textes internationnalisés
		$this->setPostI18n($oCursor->id, $aPostLocalesData);

		return true;
	}

	/**
	 * Réalise les opérations communes sur le cursor pour l'insertion et la modification.
	 *
	 * @param cursor $oCursor
	 */
	protected function preparePostCursor($oCursor)
	{
		$sDate = date('Y-m-d H:i:s');

		if (empty($oCursor->created_at)) {
			$oCursor->created_at = $sDate;
		}
		else {
			$oCursor->created_at = date('Y-m-d H:i:s', strtotime($oCursor->created_at));
		}

		$oCursor->updated_at = $sDate;

		if (strtotime($oCursor->created_at) > time()) {
			$oCursor->active = 3;
		}
	}

	/**
	 * Vérifie les données envoyées par formulaire.
	 *
	 * @param array $aPostData Le tableau de données de l'article.
	 * @param array $aPostLocalesData Le tableau de données des textes internationnalisés de l'article.
	 * @param array $aPostPermsData  Le tableau de données des permissions de l'article.
	 * @return boolean
	 */
	public function checkPostData($aPostData, $aPostLocalesData, $aPostPermsData)
	{
		$bHasAtLeastOneTitle = false;
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (empty($aPostLocalesData[$aLanguage['code']]['title'])) {
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
				$this->error->set(__('m_news_post_must_enter_title'));
			}
			else {
				$this->error->set(__('m_news_post_must_enter_at_least_one_title'));
			}
		}

		if ($this->canUsePerms() && empty($aPostPermsData)) {
			$this->error->set(__('m_news_post_must_set_perms'));
		}


		# -- TRIGGER MODULE NEWS : checkPostData
		$this->triggers->callTrigger('checkPostData', $this->okt, $aPostData, $aPostLocalesData, $aPostPermsData);


		return $this->error->isEmpty();
	}

	/**
	 * Switch le statut de visibilité d'un article donné.
	 *
	 * @param integer $iPostId
	 * @return boolean
	 */
	public function switchPostStatus($iPostId)
	{
		$rsPost = $this->getPostsRecordset(array(
			'id' => $iPostId
		));

		if ($rsPost->isEmpty()) {
			throw new \Exception(sprintf(__('m_news_post_%s_not_exists'), $iPostId));
		}

		if (!$rsPost->isEditable()) {
			throw new \Exception(__('m_news_post_not_editable'));
		}

		if ($rsPost->visibility == 2) {
			throw new \Exception(__('m_news_post_not_yet_validated'));
		}

		$sQuery =
		'UPDATE '.$this->t_news.' SET '.
			'updated_at=NOW(), '.
			'active = 1-active '.
		'WHERE id='.(integer)$iPostId;

		if (!$this->db->execute($sQuery)) {
			throw new \Exception('Unable to update post in database.');
		}

		return true;
	}

	/**
	 * Masquage d'un article.
	 *
	 * @param integer $iPostId
	 * @throws Exception
	 * @return boolean
	 */
	public function hidePost($iPostId)
	{
		$rsPost = $this->getPostsRecordset(array(
			'id' => $iPostId
		));

		if ($rsPost->isEmpty()) {
			throw new \Exception(sprintf(__('m_news_post_%s_not_exists'), $iPostId));
		}

		if ($rsPost->active != 1) {
			return false;
		}

		if (!$rsPost->isEditable()) {
			throw new \Exception(__('m_news_post_not_editable'));
		}

		$this->setPostStatus($iPostId, 0);

		return true;
	}

	/**
	 * Modification du statut d'un article à "visible".
	 *
	 * @param integer $iPostId
	 * @throws Exception
	 * @return boolean
	 */
	public function showPost($iPostId)
	{
		$rsPost = $this->getPostsRecordset(array(
			'id' => $iPostId
		));

		if ($rsPost->isEmpty()) {
			throw new \Exception(sprintf(__('m_news_post_%s_not_exists'), $iPostId));
		}

		if ($rsPost->active != 0) {
			return false;
		}

		if (!$rsPost->isEditable()) {
			throw new \Exception(__('m_news_post_not_editable'));
		}

		$this->setPostStatus($iPostId, 1);

		return true;
	}

	/**
	 * Publication d'un article en attente de publication.
	 *
	 * @param integer $iPostId
	 * @throws Exception
	 * @return boolean
	 */
	public function publishPost($iPostId)
	{
		$rsPost = $this->getPostsRecordset(array(
			'id' => $iPostId
		));

		if ($rsPost->isEmpty()) {
			throw new \Exception(sprintf(__('m_news_post_%s_not_exists'), $iPostId));
		}

		if ($rsPost->active != 2) {
			return false;
		}

		if (!$rsPost->isEditable()) {
			throw new \Exception(__('m_news_post_not_editable'));
		}

		if (!$rsPost->isPublishable()) {
			throw new \Exception(__('m_news_post_not_publishable'));
		}

		$this->setPostStatus($iPostId, 1);

		return true;
	}

	/**
	 * Publication des articles programmés.
	 *
	 * @return boolean
	 */
	public function publishScheduledPosts()
	{
		$rsPosts = $this->getPostsRecordset(array(
			'scheduled' => true
		));

		if ($rsPosts->isEmpty()) {
			return null;
		}

		$iNow = time();

		while ($rsPosts->fetch())
		{
			if ($iNow > strtotime($rsPosts->created_at))
			{
				$this->setPostStatus($rsPosts->id, 1);
			}
		}

		return true;
	}

	/**
	 * Définit le statut de visibilité d'un article donné.
	 *
	 * @param integer $iPostId
	 * @param integer $iStatus
	 * @return boolean
	 */
	protected function setPostStatus($iPostId, $iStatus)
	{
		$sQuery =
		'UPDATE '.$this->t_news.' SET '.
			'updated_at=NOW(), '.
			'active = '.(integer)$iStatus.' '.
		'WHERE id='.(integer)$iPostId;

		if (!$this->db->execute($sQuery)) {
			throw new \Exception('Unable to update post in database.');
		}

		return true;
	}

	/**
	 * Switch la selection d'un article donné.
	 *
	 * @param integer $iPostId
	 * @return boolean
	 */
	public function switchPostSelected($iPostId)
	{
		$rsPost = $this->getPostsRecordset(array(
			'id' => $iPostId
		));

		if ($rsPost->isEmpty()) {
			throw new \Exception(sprintf(__('m_news_post_%s_not_exists'), $iPostId));
		}

		if (!$rsPost->isEditable()) {
			throw new \Exception(__('m_news_post_not_editable'));
		}

		$sQuery =
		'UPDATE '.$this->t_news.' SET '.
			'updated_at=NOW(), '.
			'selected = 1-selected '.
		'WHERE id='.(integer)$iPostId;

		if (!$this->db->execute($sQuery)) {
			throw new \Exception('Unable to update post in database.');
		}

		return true;
	}

	/**
	 * Définit la selection d'un article donné.
	 *
	 * @param integer $iPostId
	 * @param boolean $bSelected
	 * @return boolean
	 */
	public function setPostSelected($iPostId, $bSelected)
	{
		$rsPost = $this->getPostsRecordset(array(
			'id' => $iPostId
		));

		if ($rsPost->isEmpty()) {
			throw new \Exception(sprintf(__('m_news_post_%s_not_exists'), $iPostId));
		}

		if (!$rsPost->isEditable()) {
			throw new \Exception(__('m_news_post_not_editable'));
		}

		$sQuery =
		'UPDATE '.$this->t_news.' SET '.
			'updated_at=NOW(), '.
			'selected = '.($bSelected ? '1' : '0').' '.
		'WHERE id='.(integer)$iPostId;

		if (!$this->db->execute($sQuery)) {
			throw new \Exception('Unable to update post in database.');
		}

		return true;
	}

	/**
	 * Suppression d'un article.
	 *
	 * @param integer $iPostId
	 * @return boolean
	 */
	public function deletePost($iPostId)
	{
		$rsPost = $this->getPostsRecordset(array(
			'id' => $iPostId
		));

		if ($rsPost->isEmpty()) {
			throw new \Exception(sprintf(__('m_news_post_%s_not_exists'), $iPostId));
		}

		if (!$rsPost->isDeletable()) {
			throw new \Exception(__('m_news_post_not_deletable'));
		}

		if ($this->deleteImages($iPostId) === false) {
			throw new \Exception('Unable to delete images post.');
		}

		if ($this->deleteFiles($iPostId) === false) {
			throw new \Exception('Unable to delete files post.');
		}

		$sQuery =
		'DELETE FROM '.$this->t_news.' '.
		'WHERE id='.(integer)$iPostId;

		if (!$this->db->execute($sQuery)) {
			throw new \Exception('Unable to remove post from database.');
		}

		$this->db->optimize($this->t_news);

		$sQuery =
		'DELETE FROM '.$this->t_news_locales.' '.
		'WHERE post_id='.(integer)$iPostId;

		if (!$this->db->execute($sQuery)) {
			throw new \Exception('Unable to remove post locales from database.');
		}

		$this->db->optimize($this->t_news_locales);

		$this->deletePostPermissions($iPostId);

		return true;
	}


	/* Gestion des permissions des articles
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
			$aGroups[$rsGroups->group_id] = \html::escapeHTML($rsGroups->title);
		}

		return $aGroups;
	}

	/**
	 * Retourne les permissions d'un article donné sous forme de tableau.
	 *
	 * @param integer $iPostId
	 * @return array
	 */
	public function getPostPermissions($iPostId)
	{
		$sQuery =
		'SELECT post_id, group_id '.
		'FROM '.$this->t_permissions.' '.
		'WHERE post_id='.(integer)$iPostId.' ';

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
	 * Met à jour les permissions d'un article donné.
	 *
	 * @param integer $iPostId
	 * @param array $aGroupsIds
	 * @return boolean
	 */
	protected function setPostPermissions($iPostId,$aGroupsIds)
	{
		if (!$this->canUsePerms() || empty($aGroupsIds)) {
			return $this->setDefaultPostPermissions($iPostId);
		}

		if (!$this->postExists($iPostId)) {
			throw new \Exception(sprintf(__('m_news_post_%s_not_exists'), $iPostId));
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
		$this->deletePostPermissions($iPostId);

		# mise en base de données
		$return = true;
		foreach ($aGroupsIds as $iGroupId)
		{
			if ($iGroupId == 0 || in_array($iGroupId,$aGroups)) {
				$return = $return && $this->setPostPermission($iPostId,$iGroupId);
			}
		}

		return $return;
	}

	/**
	 * Met les permissions par défaut d'un article donné.
	 *
	 * @param integer $iPostId
	 * @return boolean
	 */
	protected function setDefaultPostPermissions($iPostId)
	{
		if (!$this->postExists($iPostId)) {
			throw new \Exception(sprintf(__('m_news_post_%s_not_exists'), $iPostId));
		}

		# suppression de toutes les permissions éventuellement existantes
		$this->deletePostPermissions($iPostId);

		# mise en base de données de la permission "tous" (0)
		return $this->setPostPermission($iPostId,0);
	}

	/**
	 * Insertion d'une permission donnée pour un article donné.
	 *
	 * @param $iPostId
	 * @param $iGroupId
	 * @return boolean
	 */
	protected function setPostPermission($iPostId,$iGroupId)
	{
		$sQuery =
		'INSERT INTO '.$this->t_permissions.' '.
			'(post_id, group_id) '.
		'VALUES ('.
			(integer)$iPostId.', '.
			(integer)$iGroupId.' '.
		') ';

		if (!$this->db->execute($sQuery)) {
			throw new \Exception('Unable to insert post permissions into database');
		}

		return true;
	}

	/**
	 * Supprime les permissions d'un article donné.
	 *
	 * @param integer $iPostId
	 * @return boolean
	 */
	public function deletePostPermissions($iPostId)
	{
		$sQuery =
		'DELETE FROM '.$this->t_permissions.' '.
		'WHERE post_id='.(integer)$iPostId;

		if (!$this->db->execute($sQuery)) {
			throw new \Exception('Unable to delete post permissions from database');
		}

		$this->db->optimize($this->t_permissions);

		return true;
	}


	/* Gestion des images des articles
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
	 * Ajout d'image(s) à un article donné
	 *
	 * @param $iPostId
	 * @return boolean
	 */
	public function addImages($iPostId)
	{
		$aImages = $this->getImageUpload()->addImages($iPostId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updImagesInDb($iPostId, $aImages);
	}

	/**
	 * Modification d'image(s) d'un article donné
	 *
	 * @param $iPostId
	 * @return boolean
	 */
	public function updImages($iPostId)
	{
		$aCurrentImages = $this->getImagesFromDb($iPostId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aImages = $this->getImageUpload()->updImages($iPostId, $aCurrentImages);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updImagesInDb($iPostId, $aImages);
	}

	/**
	 * Suppression d'une image donnée d'un article donné
	 *
	 * @param $iPostId
	 * @param $img_id
	 * @return boolean
	 */
	public function deleteImage($iPostId,$img_id)
	{
		$aCurrentImages = $this->getImagesFromDb($iPostId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aNewImages = $this->getImageUpload()->deleteImage($iPostId, $aCurrentImages, $img_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updImagesInDb($iPostId, $aNewImages);
	}

	/**
	 * Suppression des images d'un article donné
	 *
	 * @param $iPostId
	 * @return boolean
	 */
	public function deleteImages($iPostId)
	{
		$aCurrentImages = $this->getImagesFromDb($iPostId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->getImageUpload()->deleteAllImages($iPostId, $aCurrentImages);

		return $this->updImagesInDb($iPostId);
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

		$rsPosts = $this->getPostsRecordset();

		while ($rsPosts->fetch())
		{
			$aImages = $rsPosts->getImagesInfo();
			$aImagesList = array();

			foreach ($aImages as $key=>$image)
			{
				$this->getImageUpload()->buildThumbnails($rsPosts->id, $image['img_name']);

				$aImagesList[$key] = array_merge(
					$aImages[$key],
					$this->getImageUpload()->buildImageInfos($rsPosts->id, $image['img_name'])
				);
			}

			$this->updImagesInDb($rsPosts->id, $aImagesList);
		}

		return true;
	}

	/**
	 * Récupère la liste des images d'un article donné
	 *
	 * @param $iPostId
	 * @return array
	 */
	public function getImagesFromDb($iPostId)
	{
		if (!$this->postExists($iPostId)) {
			$this->error->set(__('m_news_post_%s_not_exists'), $iPostId);
			return false;
		}

		$rsPost = $this->getPostsRecordset(array(
			'id' => $iPostId
		));

		$aImages = $rsPost->images ? unserialize($rsPost->images) : array();

		return $aImages;
	}

	/**
	 * Met à jours la liste des images d'un article donné.
	 *
	 * @param integer $iPostId
	 * @param array $aImages
	 * @return boolean
	 */
	public function updImagesInDb($iPostId, $aImages=array())
	{
		if (!$this->postExists($iPostId)) {
			$this->error->set(__('m_news_post_%s_not_exists'), $iPostId);
			return false;
		}

		$aImages = !empty($aImages) ? serialize($aImages) : NULL;

		$sQuery =
		'UPDATE '.$this->t_news.' SET '.
			'images='.(!is_null($aImages) ? '\''.$this->db->escapeStr($aImages).'\'' : 'NULL').' '.
		'WHERE id='.(integer)$iPostId;

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return true;
	}


	/* Gestion des fichiers des articles
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
	 * Ajout de fichier(s) à un article donné
	 *
	 * @param $iPostId
	 * @return boolean
	 */
	public function addFiles($iPostId)
	{
		$aFiles = $this->getFileUpload()->addFiles($iPostId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updPostFiles($iPostId, $aFiles);
	}

	/**
	 * Modification de fichier(s) d'un article donné
	 *
	 * @param $iPostId
	 * @return boolean
	 */
	public function updFiles($iPostId)
	{
		$aCurrentFiles = $this->getPostFiles($iPostId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aFiles = $this->getFileUpload()->updFiles($iPostId,$aCurrentFiles);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updPostFiles($iPostId, $aFiles);
	}

	/**
	 * Suppression d'un fichier donné d'un article donné
	 *
	 * @param $iPostId
	 * @param $file_id
	 * @return boolean
	 */
	public function deleteFile($iPostId,$file_id)
	{
		$aCurrentFiles = $this->getPostFiles($iPostId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aNewFiles = $this->getFileUpload()->deleteFile($iPostId,$aCurrentFiles,$file_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updPostFiles($iPostId,$aNewFiles);
	}

	/**
	 * Suppression des fichiers d'un article donné
	 *
	 * @param $iPostId
	 * @return boolean
	 */
	public function deleteFiles($iPostId)
	{
		$aCurrentFiles = $this->getPostFiles($iPostId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->getFileUpload()->deleteAllFiles($aCurrentFiles);

		return $this->updPostFiles($iPostId);
	}

	/**
	 * Récupère la liste des fichiers d'un article donné
	 *
	 * @param $iPostId
	 * @return array
	 */
	public function getPostFiles($iPostId)
	{
		if (!$this->postExists($iPostId)) {
			$this->error->set(__('m_news_post_%s_not_exists'), $iPostId);
			return false;
		}

		$rsPost = $this->getPostsRecordset(array(
			'id' => $iPostId
		));

		$aFiles = $rsPost->files ? unserialize($rsPost->files) : array();

		return $aFiles;
	}

	/**
	 * Met à jours la liste des fichiers d'un article donné
	 *
	 * @param integer $iPostId
	 * @param array $aFiles
	 * @return boolean
	 */
	public function updPostFiles($iPostId, $aFiles=array())
	{
		if (!$this->postExists($iPostId)) {
			$this->error->set(__('m_news_post_%s_not_exists'), $iPostId);
			return false;
		}

		$aFiles = !empty($aFiles) ? serialize($aFiles) : NULL;

		$sQuery =
		'UPDATE '.$this->t_news.' SET '.
			'files='.(!is_null($aFiles) ? '\''.$this->db->escapeStr($aFiles).'\'' : 'NULL').' '.
		'WHERE id='.(integer)$iPostId;

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return true;
	}


	/* Utilitaires
	----------------------------------------------------------*/

	/**
	 * Retourne le chemin du template de la liste des actualités.
	 *
	 * @return string
	 */
	public function getListTplPath()
	{
		return 'news/list/'.$this->config->templates['list']['default'].'/template';
	}

	/**
	 * Retourne le chemin du template du flux des actualités.
	 *
	 * @return string
	 */
	public function getFeedTplPath()
	{
		return 'news/feed/'.$this->config->templates['feed']['default'].'/template';
	}

	/**
	 * Retourne le chemin du template de l'encart des actualités.
	 *
	 * @return string
	 */
	public function getInsertTplPath()
	{
		return 'news/insert/'.$this->config->templates['insert']['default'].'/template';
	}

	/**
	 * Retourne le chemin du template de la liste des actualités d'une rubrique.
	 *
	 * @return string
	 */
	public function getCategoryTplPath($sCategoryTemplate=null)
	{
		$sTemplate = $this->config->templates['list']['default'];

		if (!empty($sCategoryTemplate) && in_array($sCategoryTemplate, $this->config->templates['list']['usables'])) {
			$sTemplate = $sCategoryTemplate;
		}

		return 'news/list/'.$sTemplate.'/template';
	}

	/**
	 * Retourne le chemin du template d'une actualité.
	 *
	 * @return string
	 */
	public function getItemTplPath($sPostTemplate=null, $sCatPostTemplate=null)
	{
		$sTemplate = $this->config->templates['item']['default'];

		if (!empty($sPostTemplate) && in_array($sPostTemplate, $this->config->templates['item']['usables'])) {
			$sTemplate = $sPostTemplate;
		}
		elseif (!empty($sCatPostTemplate) && in_array($sCatPostTemplate, $this->config->templates['item']['usables'])) {
			$sTemplate = $sCatPostTemplate;
		}

		return 'news/item/'.$sTemplate.'/template';
	}

	/**
	 * Reconstruction des index de recherche de tous les articles.
	 *
	 */
	public function indexAllPosts()
	{
		$rsPosts = $this->db->select('SELECT post_id, language, title, subtitle, content FROM '.$this->t_news_locales);

		while ($rsPosts->fetch())
		{
			$words =
				$rsPosts->title.' '.
				$rsPosts->subtitle.' '.
				$rsPosts->content.' ';

			$words = implode(' ',text::splitWords($words));

			$query =
			'UPDATE '.$this->t_news.' SET '.
				'words=\''.$this->db->escapeStr($words).'\' '.
			'WHERE post_id='.(integer)$rsPosts->id.' '.
			'AND language=\''.$this->db->escapeStr($rsPosts->language).'\' ';

			$this->db->execute($query);
		}

		return true;
	}

}
