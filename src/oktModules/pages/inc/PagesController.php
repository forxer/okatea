<?php
/**
 * @ingroup okt_module_pages
 * @brief Controller public.
 *
 */

use Tao\Core\Controller;
use Tao\Misc\Utilities as util;
use Tao\Website\Pager;

class PagesController extends Controller
{
	/**
	 * Affichage de la liste des pages classique.
	 *
	 */
	public function pagesList()
	{
		# module actuel
		$this->page->module = 'pages';
		$this->page->action = 'list';

		# permission de lecture ?
		if (!$this->okt->pages->isPublicAccessible())
		{
			if ($this->okt->user->is_guest) {
				return $this->redirect(html::escapeHTML(usersHelpers::getLoginUrl(PagesHelpers::getPagesUrl())));
			}
			else {
				return $this->serve404();
			}
		}

		# initialisation paramètres
		$aPagesParams = array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'search' => !empty($_REQUEST['search']) ? $_REQUEST['search'] : null
		);

		# initialisation des filtres
		$this->okt->pages->filtersStart('public');

		# ré-initialisation filtres
		if ($this->request->query->has('init_pages_filters'))
		{
			$this->okt->pages->filters->initFilters();
			return $this->redirect(PagesHelpers::getPagesUrl());
		}

		# initialisation des filtres
		$this->okt->pages->filters->setPagesParams($aPagesParams);
		$this->okt->pages->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredPages = $this->okt->pages->getPagesCount($aPagesParams);

		$oPagesPager = new Pager($this->okt->pages->filters->params->page, $iNumFilteredPages, $this->okt->pages->filters->params->nb_per_page);

		$iNumPages = $oPagesPager->getNbPages();

		$this->okt->pages->filters->normalizePage($iNumPages);

		$aPagesParams['limit'] = (($this->okt->pages->filters->params->page-1)*$this->okt->pages->filters->params->nb_per_page).','.$this->okt->pages->filters->params->nb_per_page;

		# récupération des pages
		$this->rsPagesList = $this->okt->pages->getPages($aPagesParams);

		# meta description
		if (!empty($this->okt->pages->config->meta_description[$this->okt->user->language])) {
			$this->page->meta_description = $this->okt->pages->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->okt->pages->config->meta_keywords[$this->okt->user->language])) {
			$this->page->meta_keywords = $this->okt->pages->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# ajout du numéro de page au title
		if ($this->okt->pages->filters->params->page > 1) {
			$this->page->addTitleTag(sprintf(__('c_c_Page_%s'),$this->okt->pages->filters->params->page));
		}

		# title tag du module
		$this->page->addTitleTag($this->okt->pages->getTitle());

		# titre de la page
		$this->page->setTitle($this->okt->pages->getName());

		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->pages->getNameSeo());

		# raccourcis
		$this->rsPagesList->numPages = $iNumPages;
		$this->rsPagesList->pager = $oPagesPager;

		# affichage du template
		return $this->render($this->okt->pages->getListTplPath(), array(
			'rsPagesList' => $this->rsPagesList
		));
	}

	/**
	 * Affichage du flux RSS des pages.
	 *
	 */
	public function pagesFeed()
	{
		# module actuel
		$this->page->module = 'pages';
		$this->page->action = 'feed';

		# récupération des pages
		$this->rsPagesList = $this->okt->pages->getPages(array(
			'active' => 1,
			'limit' => 20
		));

		# affichage du template
		$this->response->headers->set('Content-Type', 'application/rss+xml');
		return $this->render($this->okt->pages->getFeedTplPath(), array(
			'rsPagesList' => $this->rsPagesList
		));
	}

	/**
	 * Affichage de la liste des pages d'une rubrique.
	 *
	 */
	public function pagesCategory()
	{
		# module actuel
		$this->page->module = 'pages';
		$this->page->action = 'category';

		# si les rubriques ne sont pas actives -> 404
		if (!$this->okt->pages->config->categories['enable']) {
			return $this->serve404();
		}

		# récupération de la rubrique en fonction du slug
		if (!$sCategorySlug = $this->request->attributes->get('slug')) {
			return $this->serve404();
		}

		# récupération de la rubrique
		$this->rsCategory = $this->okt->pages->categories->getCategories(array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'slug' => $sCategorySlug
		));

		if ($this->rsCategory->isEmpty()) {
			return $this->serve404();
		}

		# permission de lecture ?
		if (!$this->okt->pages->isPublicAccessible())
		{
			if ($this->okt->user->is_guest) {
				return $this->redirect(html::escapeHTML(usersHelpers::getLoginUrl(PagesHelpers::getCategoryUrl($this->rsCategory->slug))));
			}
			else {
				return $this->serve404();
			}
		}

		# formatage description rubrique
		if (!$this->okt->pages->config->categories['rte']) {
			$this->rsCategory->content = util::nlToP($this->rsCategory->content);
		}

		# initialisation paramètres
		$aPagesParams = array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'category_id' => $this->rsCategory->id
		);

		# initialisation des filtres
		$this->okt->pages->filtersStart('public');

		# ré-initialisation filtres
		if ($this->request->query->has('init_pages_filters'))
		{
			$this->okt->pages->filters->initFilters();
			return $this->redirect(PagesHelpers::getPagesUrl());
		}

		# initialisation des filtres
		$this->okt->pages->filters->setPagesParams($aPagesParams);
		$this->okt->pages->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredPages = $this->okt->pages->getPagesCount($aPagesParams);

		$oPagesPager = new Pager($this->okt->pages->filters->params->page, $iNumFilteredPages, $this->okt->pages->filters->params->nb_per_page);

		$iNumPages = $oPagesPager->getNbPages();

		$this->okt->pages->filters->normalizePage($iNumPages);

		$aPagesParams['limit'] = (($this->okt->pages->filters->params->page-1)*$this->okt->pages->filters->params->nb_per_page).','.$this->okt->pages->filters->params->nb_per_page;

		# récupération des pages
		$this->rsPagesList = $this->okt->pages->getPages($aPagesParams);

		# meta description
		if (!empty($this->rsCategory->meta_description)) {
			$this->page->meta_description = $this->rsCategory->meta_description;
		}
		elseif (!empty($this->okt->pages->config->meta_description[$this->okt->user->language])) {
			$this->page->meta_description = $this->okt->pages->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->rsCategory->meta_keywords)) {
			$this->page->meta_keywords = $this->rsCategory->meta_keywords;
		}
		elseif (!empty($this->okt->pages->config->meta_keywords[$this->okt->user->language])) {
			$this->page->meta_keywords = $this->okt->pages->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# ajout du numéro de page au title
		if ($this->okt->pages->filters->params->page > 1) {
			$this->page->addTitleTag(sprintf(__('c_c_Page_%s'),$this->okt->pages->filters->params->page));
		}

		# title tag du module
		$this->page->addTitleTag((!empty($this->rsCategory->title_tag) ? $this->rsCategory->title_tag : $this->rsCategory->title));

		# ajout de la hiérarchie des rubriques au fil d'ariane et au title tag
		if (!$this->isDefaultRoute(__CLASS__, __FUNCTION__, $sCategorySlug))
		{
			$rsPath = $this->okt->pages->categories->getPath($this->rsCategory->id, true, $this->okt->user->language);
			while ($rsPath->fetch())
			{
		//		$this->page->addTitleTag((!empty($rsPath->title_tag) ? $rsPath->title_tag : $rsPath->title));

				$this->page->breadcrumb->add($rsPath->title, PagesHelpers::getCategoryUrl($rsPath->slug));
			}
		}

		# titre de la page
		$this->page->setTitle($this->rsCategory->title);

		# titre SEO de la page
		$this->page->setTitleSeo($this->rsCategory->title_seo);

		# raccourcis
		$this->rsPagesList->numPages = $iNumPages;
		$this->rsPagesList->pager = $oPagesPager;

		# affichage du template
		return $this->render($this->okt->pages->getCategoryTplPath($this->rsCategory->tpl), array(
			'rsPagesList' => $this->rsPagesList,
			'rsCategory' => $this->rsCategory
		));
	}

	/**
	 * Affichage d'une page.
	 *
	 */
	public function pagesItem()
	{
		# module actuel
		$this->page->module = 'pages';
		$this->page->action = 'item';

		# récupération de la page en fonction du slug
		if (!$sPageSlug = $this->request->attributes->get('slug')) {
			return $this->serve404();
		}

		# récupération de la page
		$this->rsPage = $this->okt->pages->getPage($sPageSlug, 1);

		if ($this->rsPage->isEmpty()) {
			return $this->serve404();
		}

		# permission de lecture ?
		if (!$this->okt->pages->isPublicAccessible() || !$this->rsPage->isReadable())
		{
			if ($this->okt->user->is_guest) {
				return $this->redirect(html::escapeHTML(usersHelpers::getLoginUrl($this->rsPage->getPageUrl())));
			}
			else {
				return $this->serve404();
			}
		}

		# meta description
		if (!empty($this->rsPage->meta_description)) {
			$this->page->meta_description = $this->rsPage->meta_description;
		}
		elseif (!empty($this->okt->pages->config->meta_description[$this->okt->user->language])) {
			$this->page->meta_description = $this->okt->pages->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->rsPage->meta_keywords)) {
			$this->page->meta_keywords = $this->rsPage->meta_keywords;
		}
		elseif (!empty($this->okt->pages->config->meta_keywords[$this->okt->user->language])) {
			$this->page->meta_keywords = $this->okt->pages->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# si les rubriques sont activées
		if ($this->okt->pages->config->categories['enable'] && $this->rsPage->category_id)
		{
			# title tag de la rubrique
			$this->page->addTitleTag($this->rsPage->category_title);

			# ajout de la hiérarchie des rubriques au fil d'ariane
			$rsPath = $this->okt->pages->categories->getPath($this->rsPage->category_id, true, $this->okt->user->language);
			while ($rsPath->fetch()) {
				$this->page->breadcrumb->add($rsPath->title, PagesHelpers::getCategoryUrl($rsPath->slug));
			}
		}

		# title tag de la page
		$this->page->addTitleTag(($this->rsPage->title_tag == '' ? $this->rsPage->title : $this->rsPage->title_tag));

		# titre de la page
		$this->page->setTitle($this->rsPage->title);

		# titre SEO de la page
		$this->page->setTitleSeo($this->rsPage->title_seo);

		# fil d'ariane de la page
		if (!$this->isDefaultRoute(__CLASS__, __FUNCTION__, $sPageSlug)) {
			$this->page->breadcrumb->add($this->rsPage->title, $this->rsPage->url);
		}

		# affichage du template
		return $this->render($this->okt->pages->getItemTplPath($this->rsPage->tpl, $this->rsPage->category_items_tpl), array(
			'rsPage' => $this->rsPage
		));
	}
}
