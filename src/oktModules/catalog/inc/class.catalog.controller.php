<?php
/**
 * @ingroup okt_module_catalog
 * @brief Controller public.
 *
 */

class catalogController extends oktController
{
	/**
	 * Affichage de la liste classique des produits.
	 *
	 */
	public function catalogList()
	{
		# module actuel
		$this->okt->page->module = 'catalog';
		$this->okt->page->action = 'list';

		# initialisation des paramètres
		$aProductsParams = array(
			'visibility' => 1,
			'search' => !empty($_REQUEST['search']) ? $_REQUEST['search'] : null
		);

		# initialisation des filtres
		$this->okt->catalog->filtersStart();

		# ré-initialisation filtres
		if (!empty($_GET['catalog_init_filters']))
		{
			$this->okt->catalog->filters->initFilters();
			http::redirect($this->okt->catalog->config->url);
		}

		# initialisation des filtres
		$this->okt->catalog->filters->setCatalogParams($aProductsParams);
		$this->okt->catalog->filters->getFilters();

		# initialisation de la pagination
		$num_filtered_products = $this->okt->catalog->getProds($aProductsParams,true);

		$oProductsPager = new publicPager($this->okt->catalog->filters->params->page, $num_filtered_products, $this->okt->catalog->filters->params->nb_per_page);

		$iNumPages = $oProductsPager->getNbPages();

		$this->okt->catalog->filters->normalizePage($iNumPages);

		$aProductsParams['limit'] = (($this->okt->catalog->filters->params->page-1)*$this->okt->catalog->filters->params->nb_per_page).','.$this->okt->catalog->filters->params->nb_per_page;

		# récupération des produits
		$productsList = $this->okt->catalog->getProds($aProductsParams);

		$count_line = 0;
		while ($productsList->fetch())
		{
			$productsList->odd_even = ($count_line%2 == 0 ? 'even' : 'odd');
			$count_line++;

			$productsList->url = $productsList->getProductUrl();

			if (!$this->okt->catalog->config->rte_enable) {
				$productsList->content = util::nlToP($productsList->content);
			}

			if ($this->okt->catalog->config->public_truncat_char > 0 )
			{
				$productsList->content = html::clean($productsList->content);
				$productsList->content = text::cutString($productsList->content,$this->okt->catalog->config->public_truncat_char);
			}

			$productsList->category_url = $productsList->getCategoryUrl();
		}

		# meta description
		if ($this->okt->catalog->config->meta_description != '') {
			$this->okt->page->meta_description = $this->okt->catalog->config->meta_description;
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($this->okt->catalog->config->meta_keywords != '') {
			$this->okt->page->meta_keywords = $this->okt->catalog->config->meta_keywords;
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# début du fil d'ariane
		$this->okt->page->breadcrumb->add($this->okt->catalog->getName(),$this->okt->catalog->config->url);

		# ajout du numéro de page au title
		if ($this->okt->catalog->filters->params->page > 1) {
			$this->okt->page->addTitleTag(sprintf(__('c_c_Page_%s'),$this->okt->catalog->filters->params->page));
		}

		# title tag du module
		$this->okt->page->addTitleTag($this->okt->catalog->getTitle());

		# titre de la page
		$this->okt->page->setTitle($this->okt->catalog->getName());

		# titre SEO de la page
		$this->okt->page->setTitleSeo($this->okt->catalog->getName());

		# raccourcis
		$productsList->numPages = $iNumPages;
		$productsList->pager = $oProductsPager;

		# affichage du template
		echo $this->okt->tpl->render('catalog_list_tpl', array(
			'productsList' => $productsList
		));
	}

	/**
	 * Affichage de la liste des produits d'une rubrique.
	 *
	 */
	public function catalogCategory($aMatches)
	{
		# si les rubriques ne sont pas actives -> 404
		if (!$this->okt->catalog->config->categories_enable) {
			$this->serve404();
		}

		# module actuel
		$this->okt->page->module = 'catalog';
		$this->okt->page->action = 'category';

		# récupération de la rubrique en fonction du slug
		if (!empty($aMatches[0])) {
			$slug = $aMatches[0];
		}
		else {
			$this->serve404();
		}

		$rsCategory = $this->okt->catalog->getCategories(array(
			'slug' => $slug,
			'visibility' => 1
		));

		if ($rsCategory->isEmpty()) {
			$this->serve404();
		}

		# initialisation des paramètres
		$aProductsParams = array(
			'visibility' => 1,
			'category_id' => $rsCategory->id
		);

		# initialisation des filtres
		$this->okt->catalog->filtersStart();

		# ré-initialisation filtres
		if (!empty($_GET['catalog_init_filters']))
		{
			$this->okt->catalog->filters->initFilters();
			http::redirect($this->okt->catalog->config->url);
		}

		# initialisation des filtres
		$this->okt->catalog->filters->setCatalogParams($aProductsParams);
		$this->okt->catalog->filters->getFilters();

		# initialisation de la pagination
		$num_filtered_products = $this->okt->catalog->getProds($aProductsParams,true);

		$oProductsPager = new publicPager($this->okt->catalog->filters->params->page, $num_filtered_products, $this->okt->catalog->filters->params->nb_per_page);

		$iNumPages = $oProductsPager->getNbPages();

		$this->okt->catalog->filters->normalizePage($iNumPages);

		$aProductsParams['limit'] = (($this->okt->catalog->filters->params->page-1)*$this->okt->catalog->filters->params->nb_per_page).','.$this->okt->catalog->filters->params->nb_per_page;

		# récupération des produits
		$productsList = $this->okt->catalog->getProds($aProductsParams);

		$count_line = 0;
		while ($productsList->fetch())
		{
			$productsList->odd_even = ($count_line%2 == 0 ? 'even' : 'odd');
			$count_line++;

			$productsList->url = $productsList->getProductUrl();

			if (!$this->okt->catalog->config->rte_enable) {
				$productsList->content = util::nlToP($productsList->content);
			}

			if ($this->okt->catalog->config->public_truncat_char > 0 )
			{
				$productsList->content = html::clean($productsList->content);
				$productsList->content = text::cutString($productsList->content,$this->okt->catalog->config->public_truncat_char);
			}

			$productsList->category_url = $productsList->getCategoryUrl();
		}

		# meta description
		if ($this->okt->catalog->config->meta_description != '') {
			$this->okt->page->meta_description = $this->okt->catalog->config->meta_description;
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($this->okt->catalog->config->meta_keywords != '') {
			$this->okt->page->meta_keywords = $this->okt->catalog->config->meta_keywords;
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# début du fil d'ariane
		$this->okt->page->breadcrumb->add($this->okt->catalog->getName(),$this->okt->catalog->config->url);

		# ajout du numéro de page au title
		if ($this->okt->catalog->filters->params->page > 1) {
			$this->okt->page->addTitleTag(sprintf(__('c_c_Page_%s'),$this->okt->catalog->filters->params->page));
		}

		# title tag du module
		$this->okt->page->addTitleTag($this->okt->catalog->getTitle());

		# ajout de la hiérarchie des catégories au fil d'ariane et au title tag
		$rsPath = $this->okt->catalog->getPath($rsCategory->id,true);

		while ($rsPath->fetch())
		{
			$this->okt->page->addTitleTag($rsPath->name);

			$this->okt->page->breadcrumb->add(
				$rsPath->name,
				$this->okt->page->getBaseUrl().$this->okt->catalog->config->public_catalog_url.'/'.$rsPath->slug
			);
		}

		# titre de la page
		$this->okt->page->setTitle($rsCategory->name);

		# titre SEO de la page
		$this->okt->page->setTitleSeo($rsCategory->name);

		# raccourcis
		$productsList->numPages = $iNumPages;
		$productsList->pager = $oProductsPager;

		# affichage du template
		echo $this->okt->tpl->render('catalog_list_tpl', array(
			'productsList' => $productsList,
			'rsCategory' => $rsCategory
		));
	}

	/**
	 * Affichage d'un produit.
	 *
	 */
	public function catalogItem($aMatches)
	{
		# module actuel
		$this->okt->page->module = 'catalog';
		$this->okt->page->action = 'item';

		# Récupération du produit en fonction du slug
		if (!empty($aMatches[0])) {
			$slug = $aMatches[0];
		}
		else {
			$this->serve404();
		}

		$product = $this->okt->catalog->getProds(array(
			'slug' => $slug,
			'visibility' => 1
		));

		if ($product->isEmpty()) {
			$this->serve404();
		}

		# Formatage des données
		if ($product->title_tag == '') {
			$product->title_tag = $product->title;
		}

		$product->url = $product->getProductUrl();

		if (!$this->okt->catalog->config->rte_enable) {
			$product->content = util::nlToP($product->content);
		}

		$product->category_url = $product->getCategoryUrl();

		# meta description
		if ($product->meta_description != '') {
			$this->okt->page->meta_description = $product->meta_description;
		}
		else if ($this->okt->catalog->config->meta_description != '') {
			$this->okt->page->meta_description = $this->okt->catalog->config->meta_description;
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($product->meta_keywords != '') {
			$this->okt->page->meta_keywords = $product->meta_keywords;
		}
		else if ($this->okt->catalog->config->meta_keywords != '') {
			$this->okt->page->meta_keywords = $this->okt->catalog->config->meta_keywords;
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# Récupération des images
		$product->images = $product->getImagesInfo();

		# Récupération des fichiers
		$product->files = $product->getFilesInfo();

		# Début du fil d'ariane
		$this->okt->page->breadcrumb->add($this->okt->catalog->getName(),$this->okt->catalog->config->url);

		# Title tag du module
		$this->okt->page->addTitleTag($this->okt->catalog->getTitle());

		# Title tag de la catégorie
		$this->okt->page->addTitleTag($product->category_name);

		# Title tag du produit
		$this->okt->page->addTitleTag($product->title_tag);

		# titre de la page
		$this->okt->page->setTitle($product->title);

		# titre SEO de la page
		$this->okt->page->setTitleSeo($product->title);

		# Ajout de la hiérarchie des catégories au fil d'ariane
		if ($this->okt->catalog->config->categories_enable && $product->category_id)
		{
			$rsPath = $this->okt->catalog->getPath($product->category_id,true);
			while ($rsPath->fetch())
			{
				$this->okt->page->breadcrumb->add(
					$rsPath->name,
					$this->okt->page->getBaseUrl().$this->okt->catalog->config->public_catalog_url.'/'.$rsPath->slug
				);
			}
			unset($rsPath);
		}

		# Fil d'ariane du produit
		$this->okt->page->breadcrumb->add($product->title,$product->url);

		# affichage du template
		echo $this->okt->tpl->render('catalog_item_tpl', array(
			'product' => $product
		));
	}

} # class
