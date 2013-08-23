<?php
/**
 * @ingroup okt_module_pages
 * @brief Controller public.
 *
 */

class pagesController extends oktController
{
	/**
	 * Affichage de la liste des pages classique.
	 *
	 */
	public function pagesList()
	{
		# module actuel
		$this->okt->page->module = 'pages';
		$this->okt->page->action = 'list';

		# permission de lecture ?
		if (!$this->okt->pages->isPublicAccessible())
		{
			if ($this->okt->user->is_guest) {
				http::redirect(html::escapeHTML(usersHelpers::getLoginUrl($this->okt->pages->config->url)));
			}
			else {
				$this->serve404();
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
		if (!empty($_GET['init_pages_filters']))
		{
			$this->okt->pages->filters->initFilters();
			http::redirect($this->okt->pages->config->url);
		}

		# initialisation des filtres
		$this->okt->pages->filters->setPagesParams($aPagesParams);
		$this->okt->pages->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredPages = $this->okt->pages->getPagesCount($aPagesParams);

		$oPagesPager = new publicPager($this->okt->pages->filters->params->page, $iNumFilteredPages, $this->okt->pages->filters->params->nb_per_page);

		$iNumPages = $oPagesPager->getNbPages();

		$this->okt->pages->filters->normalizePage($iNumPages);

		$aPagesParams['limit'] = (($this->okt->pages->filters->params->page-1)*$this->okt->pages->filters->params->nb_per_page).','.$this->okt->pages->filters->params->nb_per_page;

		# récupération des pages
		$rsPagesList = $this->okt->pages->getPages($aPagesParams);

		# formatage des données avant affichage
		//$this->okt->pages->preparePages($rsPagesList);

		# meta description
		if ($this->okt->pages->config->meta_description[$this->okt->user->language] != '') {
			$this->okt->page->meta_description = $this->okt->pages->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($this->okt->pages->config->meta_keywords[$this->okt->user->language] != '') {
			$this->okt->page->meta_keywords = $this->okt->pages->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# ajout du numéro de page au title
		if ($this->okt->pages->filters->params->page > 1) {
			$this->okt->page->addTitleTag(sprintf(__('c_c_Page_%s'),$this->okt->pages->filters->params->page));
		}

		# title tag du module
		$this->okt->page->addTitleTag($this->okt->pages->getTitle());

		# titre de la page
		$this->okt->page->setTitle($this->okt->pages->getName());

		# titre SEO de la page
		$this->okt->page->setTitleSeo($this->okt->pages->getNameSeo());

		# raccourcis
		$rsPagesList->numPages = $iNumPages;
		$rsPagesList->pager = $oPagesPager;

		# affichage du template
		echo $this->okt->tpl->render($this->okt->pages->getListTplPath(), array(
			'rsPagesList' => $rsPagesList,
			'rsCategory' => (isset($rsCategory) ? $rsCategory : null)
		));
	}

	/**
	 * Affichage du flux RSS des pages.
	 *
	 */
	public function pagesFeed()
	{
		# module actuel
		$this->okt->page->module = 'pages';
		$this->okt->page->action = 'feed';

		# récupération des pages
		$rsPagesList = $this->okt->pages->getPages(array(
			'active' => 1,
			'limit' => 20
		));

		# affichage du template
		header('Content-Type: application/rss+xml; charset=utf-8');
		echo $this->okt->tpl->render($this->okt->pages->getFeedTplPath(), array(
			'rsPagesList' => $rsPagesList
		));
	}

	/**
	 * Affichage de la liste des pages d'une rubrique.
	 *
	 */
	public function pagesCategory($aMatches)
	{
		# module actuel
		$this->okt->page->module = 'pages';
		$this->okt->page->action = 'category';

		# si les rubriques ne sont pas actives -> 404
		if (!$this->okt->pages->config->categories['enable']) {
			$this->serve404();
		}

		# récupération de la rubrique en fonction du slug
		if (!empty($aMatches[0])) {
			$slug = $aMatches[0];
		}
		else {
			$this->serve404();
		}

		# récupération de la rubrique
		$rsCategory = $this->okt->pages->categories->getCategories(array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'slug' => $slug
		));

		if ($rsCategory->isEmpty()) {
			$this->serve404();
		}

		# permission de lecture ?
		if (!$this->okt->pages->isPublicAccessible())
		{
			if ($this->okt->user->is_guest) {
				http::redirect(html::escapeHTML(usersHelpers::getLoginUrl(pagesHelpers::getCategoryUrl($rsCategory->slug))));
			}
			else {
				$this->serve404();
			}
		}

		# formatage description rubrique
		if (!$this->okt->pages->config->categories['rte']) {
			$rsCategory->content = util::nlToP($rsCategory->content);
		}

		# initialisation paramètres
		$aPagesParams = array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'category_id' => $rsCategory->id
		);

		# initialisation des filtres
		$this->okt->pages->filtersStart('public');

		# ré-initialisation filtres
		if (!empty($_GET['init_pages_filters']))
		{
			$this->okt->pages->filters->initFilters();
			http::redirect($this->okt->pages->config->url);
		}

		# initialisation des filtres
		$this->okt->pages->filters->setPagesParams($aPagesParams);
		$this->okt->pages->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredPages = $this->okt->pages->getPagesCount($aPagesParams);

		$oPagesPager = new publicPager($this->okt->pages->filters->params->page, $iNumFilteredPages, $this->okt->pages->filters->params->nb_per_page);

		$iNumPages = $oPagesPager->getNbPages();

		$this->okt->pages->filters->normalizePage($iNumPages);

		$aPagesParams['limit'] = (($this->okt->pages->filters->params->page-1)*$this->okt->pages->filters->params->nb_per_page).','.$this->okt->pages->filters->params->nb_per_page;

		# récupération des pages
		$rsPagesList = $this->okt->pages->getPages($aPagesParams);

		# meta description
		if ($rsCategory->meta_description != '') {
			$this->okt->page->meta_description = $rsCategory->meta_description;
		}
		else if ($this->okt->pages->config->meta_description[$this->okt->user->language] != '') {
			$this->okt->page->meta_description = $this->okt->pages->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($rsCategory->meta_keywords != '') {
			$this->okt->page->meta_keywords = $rsCategory->meta_keywords;
		}
		else if ($this->okt->pages->config->meta_keywords[$this->okt->user->language] != '') {
			$this->okt->page->meta_keywords = $this->okt->pages->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# ajout du numéro de page au title
		if ($this->okt->pages->filters->params->page > 1) {
			$this->okt->page->addTitleTag(sprintf(__('c_c_Page_%s'),$this->okt->pages->filters->params->page));
		}

		# title tag du module
		$this->okt->page->addTitleTag(($rsCategory->title_tag != '' ? $rsCategory->title_tag : $rsCategory->title));

		# ajout de la hiérarchie des rubriques au fil d'ariane et au title tag
		if (!$this->isDefaultRoute(__CLASS__, __FUNCTION__, $slug))
		{
			$rsPath = $this->okt->pages->categories->getPath($rsCategory->id, true, $this->okt->user->language);
			while ($rsPath->fetch())
			{
		//		$this->okt->page->addTitleTag(($rsPath->title_tag != '' ? $rsPath->title_tag : $rsPath->title));

				$this->okt->page->breadcrumb->add($rsPath->title, pagesHelpers::getCategoryUrl($rsPath->slug));
			}
		}

		# titre de la page
		$this->okt->page->setTitle($rsCategory->title);

		# titre SEO de la page
		$this->okt->page->setTitleSeo($rsCategory->title_seo);

		# raccourcis
		$rsPagesList->numPages = $iNumPages;
		$rsPagesList->pager = $oPagesPager;

		# affichage du template
		echo $this->okt->tpl->render($this->okt->pages->getCategoryTplPath($rsCategory->tpl), array(
			'rsPagesList' => $rsPagesList,
			'rsCategory' => $rsCategory
		));
	}

	/**
	 * Affichage d'une page.
	 *
	 */
	public function pagesItem($aMatches)
	{
		# module actuel
		$this->okt->page->module = 'pages';
		$this->okt->page->action = 'item';

		# récupération de la page en fonction du slug
		if (!empty($aMatches[0])) {
			$sPageSlug = $aMatches[0];
		}
		else {
			$this->serve404();
		}

		# récupération de la page
		$rsPage = $this->okt->pages->getPage($sPageSlug, 1);

		if ($rsPage->isEmpty()) {
			$this->serve404();
		}

		# permission de lecture ?
		if (!$this->okt->pages->isPublicAccessible() || !$rsPage->isReadable())
		{
			if ($this->okt->user->is_guest) {
				http::redirect(html::escapeHTML(usersHelpers::getLoginUrl($rsPage->getPageUrl())));
			}
			else {
				$this->serve404();
			}
		}

		# meta description
		if ($rsPage->meta_description != '') {
			$this->okt->page->meta_description = $rsPage->meta_description;
		}
		else if ($this->okt->pages->config->meta_description[$this->okt->user->language] != '') {
			$this->okt->page->meta_description = $this->okt->pages->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($rsPage->meta_keywords != '') {
			$this->okt->page->meta_keywords = $rsPage->meta_keywords;
		}
		else if ($this->okt->pages->config->meta_keywords[$this->okt->user->language] != '') {
			$this->okt->page->meta_keywords = $this->okt->pages->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# si les rubriques sont activées
		if ($this->okt->pages->config->categories['enable'] && $rsPage->category_id)
		{
			# title tag de la rubrique
			$this->okt->page->addTitleTag($rsPage->category_title);

			# ajout de la hiérarchie des rubriques au fil d'ariane
			$rsPath = $this->okt->pages->categories->getPath($rsPage->category_id, true, $this->okt->user->language);
			while ($rsPath->fetch())
			{
				$this->okt->page->breadcrumb->add($rsPath->title, pagesHelpers::getCategoryUrl($rsPath->slug));
			}
			unset($rsPath);
		}

		# title tag de la page
		$this->okt->page->addTitleTag(($rsPage->title_tag == '' ? $rsPage->title : $rsPage->title_tag));

		# titre de la page
		$this->okt->page->setTitle($rsPage->title);

		# titre SEO de la page
		$this->okt->page->setTitleSeo($rsPage->title_seo);

		# fil d'ariane de la page
		if (!$this->isDefaultRoute(__CLASS__, __FUNCTION__, $rsPage->slug)) {
			$this->okt->page->breadcrumb->add($rsPage->title, $rsPage->url);
		}

		# affichage du template
		echo $this->okt->tpl->render($this->okt->pages->getItemTplPath($rsPage->tpl, $rsPage->category_items_tpl), array(
			'rsPage' => $rsPage
		));
	}

} # class
