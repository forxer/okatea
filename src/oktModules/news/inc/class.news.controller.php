<?php
/**
 * @ingroup okt_module_news
 * @brief Controller public.
 *
 */

class newsController extends oktController
{
	/**
	 * Affichage de la liste d'articles d'actualités classique.
	 *
	 */
	public function newsList()
	{
		# module actuel
		$this->okt->page->module = 'news';
		$this->okt->page->action = 'list';

		# permission de lecture ?
		if (!$this->okt->news->isPublicAccessible())
		{
			if ($this->okt->user->is_guest) {
				http::redirect(html::escapeHTML(usersHelpers::getLoginUrl($this->okt->news->config->url)));
			}
			else {
				$this->serve404();
			}
		}

		# is default route ?
		$bIsDefaultRoute = $this->isDefaultRoute(__CLASS__, __FUNCTION__, '');

		# initialisation paramètres
		$aNewsParams = array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'search' => !empty($_REQUEST['search']) ? $_REQUEST['search'] : null
		);

		# initialisation des filtres
		$this->okt->news->filtersStart('public');

		# ré-initialisation filtres
		if (!empty($_GET['init_news_filters']))
		{
			$this->okt->news->filters->initFilters();
			http::redirect($this->okt->news->config->url);
		}

		# initialisation des filtres
		$this->okt->news->filters->setPostsParams($aNewsParams);
		$this->okt->news->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredPosts = $this->okt->news->getPostsCount($aNewsParams);

		$oNewsPager = new publicPager($this->okt->news->filters->params->page, $iNumFilteredPosts, $this->okt->news->filters->params->nb_per_page);

		$iNumPages = $oNewsPager->getNbPages();

		$this->okt->news->filters->normalizePage($iNumPages);

		$aNewsParams['limit'] = (($this->okt->news->filters->params->page-1)*$this->okt->news->filters->params->nb_per_page).','.$this->okt->news->filters->params->nb_per_page;

		# récupération des pages
		$rsPostsList = $this->okt->news->getPosts($aNewsParams);

		# meta description
		if ($this->okt->news->config->meta_description[$this->okt->user->language] != '') {
			$this->okt->page->meta_description = $this->okt->news->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($this->okt->news->config->meta_keywords[$this->okt->user->language] != '') {
			$this->okt->page->meta_keywords = $this->okt->news->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# fil d'ariane
		if (!$bIsDefaultRoute) {
			$this->okt->page->breadcrumb->add($this->okt->news->getName(), $this->okt->news->config->url);
		}

		# ajout du numéro de page au title
		if ($this->okt->news->filters->params->page > 1) {
			$this->okt->page->addTitleTag(sprintf(__('c_c_Page_%s'), $this->okt->news->filters->params->page));
		}

		# title tag du module
		$this->okt->page->addTitleTag($this->okt->news->getTitle());

		# titre de la page
		$this->okt->page->setTitle($this->okt->news->getName());

		# titre SEO de la page
		$this->okt->page->setTitleSeo($this->okt->news->getNameSeo());

		# raccourcis
		$rsPostsList->numPages = $iNumPages;
		$rsPostsList->pager = $oNewsPager;

		# affichage du template
		echo $this->okt->tpl->render($this->okt->news->getListTplPath(), array(
			'rsPostsList' => $rsPostsList,
			'rsCategory' => (isset($rsCategory) ? $rsCategory : null)
		));
	}

	/**
	 * Affichage du flux RSS des actualités.
	 *
	 */
	public function newsFeed()
	{
		# module actuel
		$this->okt->page->module = 'news';
		$this->okt->page->action = 'feed';

		# récupération des pages
		$rsPostsList = $this->okt->news->getPosts(array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'limit' => 20
		));

		# affichage du template
		header('Content-Type: application/rss+xml; charset=utf-8');
		echo $this->okt->tpl->render($this->okt->news->getFeedTplPath(), array(
			'rsPostsList' => $rsPostsList
		));
	}

	/**
	 * Affichage de la liste des articles d'une rubrique.
	 *
	 */
	public function newsCategory($aMatches)
	{
		# module actuel
		$this->okt->page->module = 'news';
		$this->okt->page->action = 'category';

		# si les rubriques ne sont pas actives -> 404
		if (!$this->okt->news->config->categories['enable']) {
			$this->serve404();
		}

		# récupération de la rubrique en fonction du slug
		if (!empty($aMatches[0])) {
			$sCategorySlug = $aMatches[0];
		}
		else {
			$this->serve404();
		}

		# récupération de la rubrique
		$rsCategory = $this->okt->news->categories->getCategories(array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'slug' => $sCategorySlug
		));

		if ($rsCategory->isEmpty()) {
			$this->serve404();
		}

		# permission de lecture ?
		if (!$this->okt->news->isPublicAccessible())
		{
			if ($this->okt->user->is_guest) {
				http::redirect(html::escapeHTML(usersHelpers::getLoginUrl(newsHelpers::getCategoryUrl($rsCategory->slug))));
			}
			else {
				$this->serve404();
			}
		}

		# is default route ?
		$bIsDefaultRoute = $this->isDefaultRoute(__CLASS__, __FUNCTION__, $sCategorySlug);

		# formatage description rubrique
		if (!$this->okt->news->config->categories['rte']) {
			$rsCategory->content = util::nlToP($rsCategory->content);
		}

		# initialisation paramètres
		$aNewsParams = array(
			'active' => 1,
			'language' => $this->okt->user->language,
			'category_id' => $rsCategory->id
		);

		# initialisation des filtres
		$this->okt->news->filtersStart('public');

		# ré-initialisation filtres
		if (!empty($_GET['init_news_filters']))
		{
			$this->okt->news->filters->initFilters();
			http::redirect($this->okt->news->config->url);
		}

		# initialisation des filtres
		$this->okt->news->filters->setPostsParams($aNewsParams);
		$this->okt->news->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredPosts = $this->okt->news->getPostsCount($aNewsParams);

		$oNewsPager = new publicPager($this->okt->news->filters->params->page, $iNumFilteredPosts, $this->okt->news->filters->params->nb_per_page);

		$iNumPages = $oNewsPager->getNbPages();

		$this->okt->news->filters->normalizePage($iNumPages);

		$aNewsParams['limit'] = (($this->okt->news->filters->params->page-1)*$this->okt->news->filters->params->nb_per_page).','.$this->okt->news->filters->params->nb_per_page;

		# récupération des articles
		$rsPostsList = $this->okt->news->getPosts($aNewsParams);

		# meta description
		if (!empty($rsCategory->meta_description)) {
			$this->okt->page->meta_description = $rsCategory->meta_description;
		}
		elseif (!empty($this->okt->news->config->meta_description[$this->okt->user->language])) {
			$this->okt->page->meta_description = $this->okt->news->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($rsCategory->meta_keywords)) {
			$this->okt->page->meta_keywords = $rsCategory->meta_keywords;
		}
		elseif (!empty($this->okt->news->config->meta_keywords[$this->okt->user->language])) {
			$this->okt->page->meta_keywords = $this->okt->news->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# ajout du numéro de page au title
		if ($this->okt->news->filters->params->page > 1) {
			$this->okt->page->addTitleTag(sprintf(__('c_c_Page_%s'), $this->okt->news->filters->params->page));
		}

		# title tag
		$this->okt->page->addTitleTag((!empty($rsCategory->title_tag) ? $rsCategory->title_tag : $rsCategory->title));

		# fil d'ariane
		if (!$bIsDefaultRoute)
		{
			$this->okt->page->breadcrumb->add($this->okt->news->getName(), $this->okt->news->config->url);

			# ajout de la hiérarchie des rubriques au fil d'ariane
			$rsPath = $this->okt->news->categories->getPath($rsCategory->id, true, $this->okt->user->language);
			while ($rsPath->fetch()) {
				$this->okt->page->breadcrumb->add($rsPath->title, newsHelpers::getCategoryUrl($rsPath->slug));
			}
		}

		# titre de la page
		$this->okt->page->setTitle($rsCategory->title);

		# titre SEO de la page
		$this->okt->page->setTitleSeo($rsCategory->title_seo);

		# raccourcis
		$rsPostsList->numPages = $iNumPages;
		$rsPostsList->pager = $oNewsPager;

		# affichage du template
		echo $this->okt->tpl->render($this->okt->news->getCategoryTplPath($rsCategory->tpl), array(
			'rsPostsList' => $rsPostsList,
			'rsCategory' => $rsCategory
		));
	}

	/**
	 * Affichage d'un article d'actualités.
	 *
	 */
	public function newsItem($aMatches)
	{
		# module actuel
		$this->okt->page->module = 'news';
		$this->okt->page->action = 'item';

		# récupération de la page en fonction du slug
		if (!empty($aMatches[0])) {
			$sPostSlug = $aMatches[0];
		}
		else {
			$this->serve404();
		}

		# récupération de l'article
		$rsPost = $this->okt->news->getPost($sPostSlug, 1);

		if ($rsPost->isEmpty()) {
			$this->serve404();
		}

		# is default route ?
		$bIsDefaultRoute = $this->isDefaultRoute(__CLASS__, __FUNCTION__, $sPostSlug);

		# permission de lecture ?
		if (!$this->okt->news->isPublicAccessible() || !$rsPost->isReadable())
		{
			if ($this->okt->user->is_guest) {
				http::redirect(html::escapeHTML(usersHelpers::getLoginUrl($rsPost->url)));
			}
			else {
				$this->serve404();
			}
		}

		# meta description
		if ($rsPost->meta_description != '') {
			$this->okt->page->meta_description = $rsPost->meta_description;
		}
		else if ($this->okt->news->config->meta_description[$this->okt->user->language] != '') {
			$this->okt->page->meta_description = $this->okt->news->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($rsPost->meta_keywords != '') {
			$this->okt->page->meta_keywords = $rsPost->meta_keywords;
		}
		else if ($this->okt->news->config->meta_keywords[$this->okt->user->language] != '') {
			$this->okt->page->meta_keywords = $this->okt->news->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# title tag du module
		$this->okt->page->addTitleTag($this->okt->news->getTitle());

		# début du fil d'ariane
		if (!$bIsDefaultRoute) {
			$this->okt->page->breadcrumb->add($this->okt->news->getName(), $this->okt->news->config->url);
		}

		# si les rubriques sont activées
		if ($this->okt->news->config->categories['enable'] && $rsPost->category_id)
		{
			# title tag de la rubrique
			$this->okt->page->addTitleTag($rsPost->category_title);

			# ajout de la hiérarchie des rubriques au fil d'ariane
			if (!$bIsDefaultRoute)
			{
				$rsPath = $this->okt->news->categories->getPath($rsPost->category_id, true, $this->okt->user->language);
				while ($rsPath->fetch())
				{
					$this->okt->page->breadcrumb->add($rsPath->title, newsHelpers::getCategoryUrl($rsPath->slug));
				}
				unset($rsPath);
			}
		}

		# title tag de la page
		$this->okt->page->addTitleTag(($rsPost->title_tag == '' ? $rsPost->title : $rsPost->title_tag));

		# titre de la page
		$this->okt->page->setTitle($rsPost->title);

		# titre SEO de la page
		$this->okt->page->setTitleSeo($rsPost->title_seo);

		# fil d'ariane de la page
		if (!$bIsDefaultRoute) {
			$this->okt->page->breadcrumb->add($rsPost->title, $rsPost->url);
		}

		# affichage du template
		echo $this->okt->tpl->render($this->okt->news->getItemTplPath($rsPost->tpl, $rsPost->category_items_tpl), array(
			'rsPost' => $rsPost
		));
	}

} # class
