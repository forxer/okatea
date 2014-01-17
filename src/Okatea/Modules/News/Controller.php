<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\News;

use Okatea\Tao\Misc\Utilities;
use Okatea\Website\Controller as BaseController;
use Okatea\Website\Pager;

class Controller extends BaseController
{
	/**
	 * Affichage de la liste d'articles d'actualités classique.
	 *
	 */
	public function newsList()
	{
		# permission de lecture ?
		if (!$this->okt->News->isPublicAccessible())
		{
			if ($this->okt->user->is_guest) {
				return $this->redirect(html::escapeHTML(usersHelpers::getLoginUrl($this->generateUrl('newsList'))));
			}
			else {
				return $this->serve404();
			}
		}

		# is default route ?
		$bIsHomePageRoute = $this->isHomePageRoute();

		# initialisation paramètres
		$aNewsParams = array(
			'active' => 1,
			'language' => $this->okt->user->language
		);

		$sSearch = $this->request->query->get('search');

		if ($sSearch) {
			$aNewsParams['search'] = $sSearch;
		}

		# initialisation des filtres
		$this->okt->News->filtersStart('public');

		# ré-initialisation filtres
		if ($this->request->query->has('init_news_filters'))
		{
			$this->okt->News->filters->initFilters();
			return $this->redirect($this->generateUrl('newsList'));
		}

		# initialisation des filtres
		$this->okt->News->filters->setPostsParams($aNewsParams);
		$this->okt->News->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredPosts = $this->okt->News->getPostsCount($aNewsParams);

		$oNewsPager = new Pager($this->okt->News->filters->params->page, $iNumFilteredPosts, $this->okt->News->filters->params->nb_per_page);

		$iNumPages = $oNewsPager->getNbPages();

		$this->okt->News->filters->normalizePage($iNumPages);

		$aNewsParams['limit'] = (($this->okt->News->filters->params->page-1)*$this->okt->News->filters->params->nb_per_page).','.$this->okt->News->filters->params->nb_per_page;

		# récupération des pages
		$this->rsPostsList = $this->okt->News->getPosts($aNewsParams);

		# meta description
		if (!empty($this->okt->News->config->meta_description[$this->okt->user->language])) {
			$this->page->meta_description = $this->okt->News->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->okt->News->config->meta_keywords[$this->okt->user->language])) {
			$this->page->meta_keywords = $this->okt->News->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# fil d'ariane
		if (!$bIsHomePageRoute) {
			$this->page->breadcrumb->add($this->okt->News->getName(), $this->generateUrl('newsList'));
		}

		# ajout du numéro de page au title
		if ($this->okt->News->filters->params->page > 1) {
			$this->page->addTitleTag(sprintf(__('c_c_Page_%s'), $this->okt->News->filters->params->page));
		}

		# title tag du module
		$this->page->addTitleTag($this->okt->News->getTitle());

		# titre de la page
		$this->page->setTitle($this->okt->News->getName());

		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->News->getNameSeo());

		# raccourcis
		$this->rsPostsList->numPages = $iNumPages;
		$this->rsPostsList->pager = $oNewsPager;

		# rendu du template
		return $this->render($this->okt->News->getListTplPath(), array(
			'rsPostsList' => $this->rsPostsList
		));
	}

	/**
	 * Affichage du flux RSS des actualités.
	 *
	 */
	public function newsFeed()
	{
		# récupération des pages
		$this->rsPostsList = $this->okt->News->getPosts(array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'limit' => 20
		));

		# affichage du template
		$this->response->headers->set('Content-Type', 'application/rss+xml');
		return $this->render($this->okt->News->getFeedTplPath(), array(
			'rsPostsList' => $this->rsPostsList
		));
	}

	/**
	 * Affichage de la liste des articles d'une rubrique.
	 *
	 */
	public function newsCategory()
	{
		# si les rubriques ne sont pas actives -> 404
		if (!$this->okt->News->config->categories['enable']) {
			return $this->serve404();
		}

		# récupération de la rubrique en fonction du slug
		if (!$sCategorySlug = $this->request->attributes->get('slug')) {
			return $this->serve404();
		}

		# récupération de la rubrique
		$this->rsCategory = $this->okt->News->categories->getCategories(array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'slug' => $sCategorySlug
		));

		if ($this->rsCategory->isEmpty()) {
			return $this->serve404();
		}

		# permission de lecture ?
		if (!$this->okt->News->isPublicAccessible())
		{
			if ($this->okt->user->is_guest) {
				return $this->redirect(html::escapeHTML(usersHelpers::getLoginUrl($this->generateUrl('newsCategory', array('slug' => $this->rsCategory->slug)))));
			}
			else {
				return $this->serve404();
			}
		}

		# is default route ?
		$bIsHomePageRoute = $this->isHomePageRoute(__CLASS__, __FUNCTION__, $sCategorySlug);

		# formatage description rubrique
		if (!$this->okt->News->config->categories['rte']) {
			$this->rsCategory->content = Utilities::nlToP($this->rsCategory->content);
		}

		# initialisation paramètres
		$aNewsParams = array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'category_id' => $this->rsCategory->id
		);

		# initialisation des filtres
		$this->okt->News->filtersStart('public');

		# ré-initialisation filtres
		if ($this->request->query->has('init_news_filters'))
		{
			$this->okt->News->filters->initFilters();
			return $this->redirect($this->generateUrl('newsList'));
		}

		# initialisation des filtres
		$this->okt->News->filters->setPostsParams($aNewsParams);
		$this->okt->News->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredPosts = $this->okt->News->getPostsCount($aNewsParams);

		$oNewsPager = new Pager($this->okt->News->filters->params->page, $iNumFilteredPosts, $this->okt->News->filters->params->nb_per_page);

		$iNumPages = $oNewsPager->getNbPages();

		$this->okt->News->filters->normalizePage($iNumPages);

		$aNewsParams['limit'] = (($this->okt->News->filters->params->page-1)*$this->okt->News->filters->params->nb_per_page).','.$this->okt->News->filters->params->nb_per_page;

		# récupération des articles
		$this->rsPostsList = $this->okt->News->getPosts($aNewsParams);

		# meta description
		if (!empty($this->rsCategory->meta_description)) {
			$this->page->meta_description = $this->rsCategory->meta_description;
		}
		elseif (!empty($this->okt->News->config->meta_description[$this->okt->user->language])) {
			$this->page->meta_description = $this->okt->News->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->rsCategory->meta_keywords)) {
			$this->page->meta_keywords = $this->rsCategory->meta_keywords;
		}
		elseif (!empty($this->okt->News->config->meta_keywords[$this->okt->user->language])) {
			$this->page->meta_keywords = $this->okt->News->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# ajout du numéro de page au title
		if ($this->okt->News->filters->params->page > 1) {
			$this->page->addTitleTag(sprintf(__('c_c_Page_%s'), $this->okt->News->filters->params->page));
		}

		# title tag
		$this->page->addTitleTag((!empty($this->rsCategory->title_tag) ? $this->rsCategory->title_tag : $this->rsCategory->title));

		# fil d'ariane
		if (!$bIsHomePageRoute)
		{
			$this->page->breadcrumb->add($this->okt->News->getName(), $this->generateUrl('newsList'));

			# ajout de la hiérarchie des rubriques au fil d'ariane
			$rsPath = $this->okt->News->categories->getPath($this->rsCategory->id, true, $this->okt->user->language);
			while ($rsPath->fetch()) {
				$this->page->breadcrumb->add($rsPath->title, $this->generateUrl('newsCategory', array('slug' => $rsPath->slug)));
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
		return $this->render($this->okt->News->getCategoryTplPath($this->rsCategory->tpl), array(
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
		# récupération de l'article en fonction du slug
		if (!$sPostSlug = $this->request->attributes->get('slug')) {
			return $this->serve404();
		}

		# récupération de l'article
		$this->rsPost = $this->okt->News->getPost($sPostSlug, 1);

		if ($this->rsPost->isEmpty()) {
			return $this->serve404();
		}

		# is default route ?
		$bIsHomePageRoute = $this->isHomePageRoute(__CLASS__, __FUNCTION__, $sPostSlug);

		# permission de lecture ?
		if (!$this->okt->News->isPublicAccessible() || !$this->rsPost->isReadable())
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
		elseif (!empty($this->okt->News->config->meta_description[$this->okt->user->language])) {
			$this->page->meta_description = $this->okt->News->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->rsPost->meta_keywords)) {
			$this->page->meta_keywords = $this->rsPost->meta_keywords;
		}
		elseif (!empty($this->okt->News->config->meta_keywords[$this->okt->user->language])) {
			$this->page->meta_keywords = $this->okt->News->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# title tag du module
		$this->page->addTitleTag($this->okt->News->getTitle());

		# début du fil d'ariane
		if (!$bIsHomePageRoute) {
			$this->page->breadcrumb->add($this->okt->News->getName(), $this->generateUrl('newsList'));
		}

		# si les rubriques sont activées
		if ($this->okt->News->config->categories['enable'] && $this->rsPost->category_id)
		{
			# title tag de la rubrique
			$this->page->addTitleTag($this->rsPost->category_title);

			# ajout de la hiérarchie des rubriques au fil d'ariane
			if (!$bIsHomePageRoute)
			{
				$rsPath = $this->okt->News->categories->getPath($this->rsPost->category_id, true, $this->okt->user->language);
				while ($rsPath->fetch()) {
					$this->page->breadcrumb->add($rsPath->title, $this->generateUrl('newsCategory', array('slug' => $rsPath->slug)));
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
		if (!$bIsHomePageRoute) {
			$this->page->breadcrumb->add($this->rsPost->title, $this->rsPost->url);
		}

		# affichage du template
		return $this->render($this->okt->News->getItemTplPath($this->rsPost->tpl, $this->rsPost->category_items_tpl), array(
			'rsPost' => $this->rsPost
		));
	}
}
