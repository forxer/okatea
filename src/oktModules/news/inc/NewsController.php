<?php
/**
 * @ingroup okt_module_news
 * @brief Controller public.
 *
 */

use Tao\Misc\Utilities as util;
use Tao\Core\Controller;
use Tao\Website\Pager;

class NewsController extends Controller
{
	/**
	 * Affichage de la liste d'articles d'actualités classique.
	 *
	 */
	public function newsList()
	{
		# module actuel
		$this->page->module = 'news';
		$this->page->action = 'list';

		# permission de lecture ?
		if (!$this->okt->news->isPublicAccessible())
		{
			if ($this->okt->user->is_guest) {
				return $this->redirect(html::escapeHTML(usersHelpers::getLoginUrl(NewsHelpers::getNewsUrl())));
			}
			else {
				return $this->serve404();
			}
		}

		# is default route ?
		$bIsDefaultRoute = $this->isDefaultRoute(__CLASS__, __FUNCTION__);

		# initialisation paramètres
		$aNewsParams = array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'search' => !empty($_REQUEST['search']) ? $_REQUEST['search'] : null
		);

		# initialisation des filtres
		$this->okt->news->filtersStart('public');

		# ré-initialisation filtres
		if ($this->request->query->has('init_news_filters'))
		{
			$this->okt->news->filters->initFilters();
			return $this->redirect(NewsHelpers::getNewsUrl());
		}

		# initialisation des filtres
		$this->okt->news->filters->setPostsParams($aNewsParams);
		$this->okt->news->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredPosts = $this->okt->news->getPostsCount($aNewsParams);

		$oNewsPager = new Pager($this->okt->news->filters->params->page, $iNumFilteredPosts, $this->okt->news->filters->params->nb_per_page);

		$iNumPages = $oNewsPager->getNbPages();

		$this->okt->news->filters->normalizePage($iNumPages);

		$aNewsParams['limit'] = (($this->okt->news->filters->params->page-1)*$this->okt->news->filters->params->nb_per_page).','.$this->okt->news->filters->params->nb_per_page;

		# récupération des pages
		$this->rsPostsList = $this->okt->news->getPosts($aNewsParams);

		# meta description
		if (!empty($this->okt->news->config->meta_description[$this->okt->user->language])) {
			$this->page->meta_description = $this->okt->news->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->okt->news->config->meta_keywords[$this->okt->user->language])) {
			$this->page->meta_keywords = $this->okt->news->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# fil d'ariane
		if (!$bIsDefaultRoute) {
			$this->page->breadcrumb->add($this->okt->news->getName(), NewsHelpers::getNewsUrl());
		}

		# ajout du numéro de page au title
		if ($this->okt->news->filters->params->page > 1) {
			$this->page->addTitleTag(sprintf(__('c_c_Page_%s'), $this->okt->news->filters->params->page));
		}

		# title tag du module
		$this->page->addTitleTag($this->okt->news->getTitle());

		# titre de la page
		$this->page->setTitle($this->okt->news->getName());

		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->news->getNameSeo());

		# raccourcis
		$this->rsPostsList->numPages = $iNumPages;
		$this->rsPostsList->pager = $oNewsPager;

		# rendu du template
		return $this->render($this->okt->news->getListTplPath(), array(
			'rsPostsList' => $this->rsPostsList
		));
	}

	/**
	 * Affichage du flux RSS des actualités.
	 *
	 */
	public function newsFeed()
	{
		# module actuel
		$this->page->module = 'news';
		$this->page->action = 'feed';

		# récupération des pages
		$this->rsPostsList = $this->okt->news->getPosts(array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'limit' => 20
		));

		# affichage du template
		$this->response->headers->set('Content-Type', 'application/rss+xml');
		return $this->render($this->okt->news->getFeedTplPath(), array(
			'rsPostsList' => $this->rsPostsList
		));
	}

	/**
	 * Affichage de la liste des articles d'une rubrique.
	 *
	 */
	public function newsCategory()
	{
		# module actuel
		$this->page->module = 'news';
		$this->page->action = 'category';

		# si les rubriques ne sont pas actives -> 404
		if (!$this->okt->news->config->categories['enable']) {
			return $this->serve404();
		}

		# récupération de la rubrique en fonction du slug
		if (!$sCategorySlug = $this->request->attributes->get('slug')) {
			return $this->serve404();
		}

		# récupération de la rubrique
		$this->rsCategory = $this->okt->news->categories->getCategories(array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'slug' => $sCategorySlug
		));

		if ($this->rsCategory->isEmpty()) {
			return $this->serve404();
		}

		# permission de lecture ?
		if (!$this->okt->news->isPublicAccessible())
		{
			if ($this->okt->user->is_guest) {
				return $this->redirect(html::escapeHTML(usersHelpers::getLoginUrl(NewsHelpers::getCategoryUrl($this->rsCategory->slug))));
			}
			else {
				return $this->serve404();
			}
		}

		# is default route ?
		$bIsDefaultRoute = $this->isDefaultRoute(__CLASS__, __FUNCTION__, $sCategorySlug);

		# formatage description rubrique
		if (!$this->okt->news->config->categories['rte']) {
			$this->rsCategory->content = util::nlToP($this->rsCategory->content);
		}

		# initialisation paramètres
		$aNewsParams = array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'category_id' => $this->rsCategory->id
		);

		# initialisation des filtres
		$this->okt->news->filtersStart('public');

		# ré-initialisation filtres
		if ($this->request->query->has('init_news_filters'))
		{
			$this->okt->news->filters->initFilters();
			return $this->redirect(NewsHelpers::getNewsUrl());
		}

		# initialisation des filtres
		$this->okt->news->filters->setPostsParams($aNewsParams);
		$this->okt->news->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredPosts = $this->okt->news->getPostsCount($aNewsParams);

		$oNewsPager = new Pager($this->okt->news->filters->params->page, $iNumFilteredPosts, $this->okt->news->filters->params->nb_per_page);

		$iNumPages = $oNewsPager->getNbPages();

		$this->okt->news->filters->normalizePage($iNumPages);

		$aNewsParams['limit'] = (($this->okt->news->filters->params->page-1)*$this->okt->news->filters->params->nb_per_page).','.$this->okt->news->filters->params->nb_per_page;

		# récupération des articles
		$this->rsPostsList = $this->okt->news->getPosts($aNewsParams);

		# meta description
		if (!empty($this->rsCategory->meta_description)) {
			$this->page->meta_description = $this->rsCategory->meta_description;
		}
		elseif (!empty($this->okt->news->config->meta_description[$this->okt->user->language])) {
			$this->page->meta_description = $this->okt->news->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->rsCategory->meta_keywords)) {
			$this->page->meta_keywords = $this->rsCategory->meta_keywords;
		}
		elseif (!empty($this->okt->news->config->meta_keywords[$this->okt->user->language])) {
			$this->page->meta_keywords = $this->okt->news->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# ajout du numéro de page au title
		if ($this->okt->news->filters->params->page > 1) {
			$this->page->addTitleTag(sprintf(__('c_c_Page_%s'), $this->okt->news->filters->params->page));
		}

		# title tag
		$this->page->addTitleTag((!empty($this->rsCategory->title_tag) ? $this->rsCategory->title_tag : $this->rsCategory->title));

		# fil d'ariane
		if (!$bIsDefaultRoute)
		{
			$this->page->breadcrumb->add($this->okt->news->getName(), NewsHelpers::getNewsUrl());

			# ajout de la hiérarchie des rubriques au fil d'ariane
			$rsPath = $this->okt->news->categories->getPath($this->rsCategory->id, true, $this->okt->user->language);
			while ($rsPath->fetch()) {
				$this->page->breadcrumb->add($rsPath->title, NewsHelpers::getCategoryUrl($rsPath->slug));
			}
		}

		# titre de la page
		$this->page->setTitle($this->rsCategory->title);

		# titre SEO de la page
		$this->page->setTitleSeo($this->rsCategory->title_seo);

		# raccourcis
		$this->rsPostsList->numPages = $iNumPages;
		$this->rsPostsList->pager = $oNewsPager;

		# affichage du template
		return $this->render($this->okt->news->getCategoryTplPath($this->rsCategory->tpl), array(
			'rsPostsList' => $this->rsPostsList,
			'rsCategory' => $this->rsCategory
		));
	}

	/**
	 * Affichage d'un article d'actualités.
	 *
	 */
	public function newsItem()
	{
		# module actuel
		$this->page->module = 'news';
		$this->page->action = 'item';

		# récupération de l'article en fonction du slug
		if (!$sPostSlug = $this->request->attributes->get('slug')) {
			return $this->serve404();
		}

		# récupération de l'article
		$this->rsPost = $this->okt->news->getPost($sPostSlug, 1);

		if ($this->rsPost->isEmpty()) {
			return $this->serve404();
		}

		# is default route ?
		$bIsDefaultRoute = $this->isDefaultRoute(__CLASS__, __FUNCTION__, $sPostSlug);

		# permission de lecture ?
		if (!$this->okt->news->isPublicAccessible() || !$this->rsPost->isReadable())
		{
			if ($this->okt->user->is_guest) {
				return $this->redirect(html::escapeHTML(usersHelpers::getLoginUrl($this->rsPost->url)));
			}
			else {
				return $this->serve404();
			}
		}

		# meta description
		if (!empty($this->rsPost->meta_description)) {
			$this->page->meta_description = $this->rsPost->meta_description;
		}
		elseif (!empty($this->okt->news->config->meta_description[$this->okt->user->language])) {
			$this->page->meta_description = $this->okt->news->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->rsPost->meta_keywords)) {
			$this->page->meta_keywords = $this->rsPost->meta_keywords;
		}
		elseif (!empty($this->okt->news->config->meta_keywords[$this->okt->user->language])) {
			$this->page->meta_keywords = $this->okt->news->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# title tag du module
		$this->page->addTitleTag($this->okt->news->getTitle());

		# début du fil d'ariane
		if (!$bIsDefaultRoute) {
			$this->page->breadcrumb->add($this->okt->news->getName(), NewsHelpers::getNewsUrl());
		}

		# si les rubriques sont activées
		if ($this->okt->news->config->categories['enable'] && $this->rsPost->category_id)
		{
			# title tag de la rubrique
			$this->page->addTitleTag($this->rsPost->category_title);

			# ajout de la hiérarchie des rubriques au fil d'ariane
			if (!$bIsDefaultRoute)
			{
				$rsPath = $this->okt->news->categories->getPath($this->rsPost->category_id, true, $this->okt->user->language);
				while ($rsPath->fetch()) {
					$this->page->breadcrumb->add($rsPath->title, NewsHelpers::getCategoryUrl($rsPath->slug));
				}
			}
		}

		# title tag de la page
		$this->page->addTitleTag(($this->rsPost->title_tag == '' ? $this->rsPost->title : $this->rsPost->title_tag));

		# titre de la page
		$this->page->setTitle($this->rsPost->title);

		# titre SEO de la page
		$this->page->setTitleSeo($this->rsPost->title_seo);

		# fil d'ariane de la page
		if (!$bIsDefaultRoute) {
			$this->page->breadcrumb->add($this->rsPost->title, $this->rsPost->url);
		}

		# affichage du template
		return $this->render($this->okt->news->getItemTplPath($this->rsPost->tpl, $this->rsPost->category_items_tpl), array(
			'rsPost' => $this->rsPost
		));
	}
}
