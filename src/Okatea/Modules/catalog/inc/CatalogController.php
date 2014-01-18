<?php
/**
 * @ingroup okt_module_catalog
 * @brief Controller public.
 *
 */

use Okatea\Website\Controller;
use Okatea\Tao\Misc\Utilities;
use Okatea\Website\Pager;

class CatalogController extends Controller
{
	/**
	 * Affichage de la liste classique des produits.
	 *
	 */
	public function catalogList()
	{
		# module actuel
		$this->page->module = 'catalog';
		$this->page->action = 'list';

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
			return $this->redirect(CatalogHelpers::getCatalogUrl());
		}

		# initialisation des filtres
		$this->okt->catalog->filters->setCatalogParams($aProductsParams);
		$this->okt->catalog->filters->getFilters();

		# initialisation de la pagination
		$num_filtered_products = $this->okt->catalog->getProds($aProductsParams,true);

		$oProductsPager = new Pager($this->okt->catalog->filters->params->page, $num_filtered_products, $this->okt->catalog->filters->params->nb_per_page);

		$iNumPages = $oProductsPager->getNbPages();

		$this->okt->catalog->filters->normalizePage($iNumPages);

		$aProductsParams['limit'] = (($this->okt->catalog->filters->params->page-1)*$this->okt->catalog->filters->params->nb_per_page).','.$this->okt->catalog->filters->params->nb_per_page;

		# récupération des produits
		$this->rsProductsList = $this->okt->catalog->getProds($aProductsParams);

		$count_line = 0;
		while ($this->rsProductsList->fetch())
		{
			$this->rsProductsList->odd_even = ($count_line%2 == 0 ? 'even' : 'odd');
			$count_line++;

			$this->rsProductsList->url = $this->rsProductsList->getProductUrl();

			if (!$this->okt->catalog->config->rte_enable) {
				$this->rsProductsList->content = Utilities::nlToP($this->rsProductsList->content);
			}

			if ($this->okt->catalog->config->public_truncat_char > 0 )
			{
				$this->rsProductsList->content = strip_tags($this->rsProductsList->content);
				$this->rsProductsList->content = text::cutString($this->rsProductsList->content,$this->okt->catalog->config->public_truncat_char);
			}

			$this->rsProductsList->category_url = $this->rsProductsList->getCategoryUrl();
		}

		# meta description
		if (!empty($this->okt->catalog->config->meta_description)) {
			$this->page->meta_description = $this->okt->catalog->config->meta_description;
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->okt->catalog->config->meta_keywords)) {
			$this->page->meta_keywords = $this->okt->catalog->config->meta_keywords;
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# fil d'ariane
		if (!$this->isHomePageRoute()) {
			$this->page->breadcrumb->add($this->okt->catalog->getName(), CatalogHelpers::getCatalogUrl());
		}

		# ajout du numéro de page au title
		if ($this->okt->catalog->filters->params->page > 1) {
			$this->page->addTitleTag(sprintf(__('c_c_Page_%s'), $this->okt->catalog->filters->params->page));
		}

		# title tag du module
		$this->page->addTitleTag($this->okt->catalog->getTitle());

		# titre de la page
		$this->page->setTitle($this->okt->catalog->getName());

		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->catalog->getName());

		# raccourcis
		$this->rsProductsList->numPages = $iNumPages;
		$this->rsProductsList->pager = $oProductsPager;

		# affichage du template
		return $this->render('catalog_list_tpl', array(
			'productsList' => $this->rsProductsList
		));
	}

	/**
	 * Affichage de la liste des produits d'une rubrique.
	 *
	 */
	public function catalogCategory($aMatches)
	{
		# module actuel
		$this->page->module = 'catalog';
		$this->page->action = 'category';

		# si les rubriques ne sont pas actives -> 404
		if (!$this->okt->catalog->config->categories_enable) {
			return $this->serve404();
		}

		# récupération de la rubrique en fonction du slug
		if (!empty($aMatches[0])) {
			$slug = $aMatches[0];
		}
		else {
			return $this->serve404();
		}

		$this->rsCategory = $this->okt->catalog->getCategories(array(
			'slug' => $slug,
			'visibility' => 1
		));

		if ($this->rsCategory->isEmpty()) {
			return $this->serve404();
		}

		# route par défaut ?
		$bIsHomePageRoute = $this->isHomePageRoute(__CLASS__, __FUNCTION__, $slug);

		# initialisation des paramètres
		$aProductsParams = array(
			'visibility' => 1,
			'category_id' => $this->rsCategory->id
		);

		# initialisation des filtres
		$this->okt->catalog->filtersStart();

		# ré-initialisation filtres
		if (!empty($_GET['catalog_init_filters']))
		{
			$this->okt->catalog->filters->initFilters();
			return $this->redirect(CatalogHelpers::getCatalogUrl());
		}

		# initialisation des filtres
		$this->okt->catalog->filters->setCatalogParams($aProductsParams);
		$this->okt->catalog->filters->getFilters();

		# initialisation de la pagination
		$num_filtered_products = $this->okt->catalog->getProds($aProductsParams,true);

		$oProductsPager = new Pager($this->okt->catalog->filters->params->page, $num_filtered_products, $this->okt->catalog->filters->params->nb_per_page);

		$iNumPages = $oProductsPager->getNbPages();

		$this->okt->catalog->filters->normalizePage($iNumPages);

		$aProductsParams['limit'] = (($this->okt->catalog->filters->params->page-1)*$this->okt->catalog->filters->params->nb_per_page).','.$this->okt->catalog->filters->params->nb_per_page;

		# récupération des produits
		$this->rsProductsList = $this->okt->catalog->getProds($aProductsParams);

		$count_line = 0;
		while ($this->rsProductsList->fetch())
		{
			$this->rsProductsList->odd_even = ($count_line%2 == 0 ? 'even' : 'odd');
			$count_line++;

			$this->rsProductsList->url = $this->rsProductsList->getProductUrl();

			if (!$this->okt->catalog->config->rte_enable) {
				$this->rsProductsList->content = Utilities::nlToP($this->rsProductsList->content);
			}

			if ($this->okt->catalog->config->public_truncat_char > 0 )
			{
				$this->rsProductsList->content = strip_tags($this->rsProductsList->content);
				$this->rsProductsList->content = text::cutString($this->rsProductsList->content,$this->okt->catalog->config->public_truncat_char);
			}

			$this->rsProductsList->category_url = $this->rsProductsList->getCategoryUrl();
		}

		# meta description
		if (!empty($this->okt->catalog->config->meta_description)) {
			$this->page->meta_description = $this->okt->catalog->config->meta_description;
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->okt->catalog->config->meta_keywords)) {
			$this->page->meta_keywords = $this->okt->catalog->config->meta_keywords;
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# ajout du numéro de page au title
		if ($this->okt->catalog->filters->params->page > 1) {
			$this->page->addTitleTag(sprintf(__('c_c_Page_%s'), $this->okt->catalog->filters->params->page));
		}

		# title tag du module
		$this->page->addTitleTag($this->okt->catalog->getTitle());

		# fil d'ariane
		if (!$bIsHomePageRoute)
		{
			$this->page->breadcrumb->add($this->okt->catalog->getName(), CatalogHelpers::getCatalogUrl());

			$rsPath = $this->okt->catalog->getPath($this->rsCategory->id,true);

			while ($rsPath->fetch())
			{
				$this->page->addTitleTag($rsPath->name);

				$this->page->breadcrumb->add(
					$rsPath->name,
					$this->page->getBaseUrl().$this->okt->catalog->config->public_catalog_url.'/'.$rsPath->slug
				);
			}
		}

		# titre de la page
		$this->page->setTitle($this->rsCategory->name);

		# titre SEO de la page
		$this->page->setTitleSeo($this->rsCategory->name);

		# raccourcis
		$this->rsProductsList->numPages = $iNumPages;
		$this->rsProductsList->pager = $oProductsPager;

		# affichage du template
		return $this->render('catalog_list_tpl', array(
			'productsList' => $this->rsProductsList,
			'rsCategory' => $this->rsCategory
		));
	}

	/**
	 * Affichage d'un produit.
	 *
	 */
	public function catalogProduct()
	{
		# module actuel
		$this->page->module = 'catalog';
		$this->page->action = 'item';

		# Récupération du produit en fonction du slug
		if (!$slug = $this->request->attributes->get('slug')) {
			return $this->serve404();
		}

		$this->rsProduct = $this->okt->catalog->getProds(array(
			'slug' => $slug,
			'visibility' => 1
		));

		if ($this->rsProduct->isEmpty()) {
			return $this->serve404();
		}

		# route par défaut ?
		$bIsHomePageRoute = $this->isHomePageRoute(__CLASS__, __FUNCTION__, $slug);

		# Formatage des données
		if ($this->rsProduct->title_tag == '') {
			$this->rsProduct->title_tag = $this->rsProduct->title;
		}

		$this->rsProduct->url = $this->rsProduct->getProductUrl();

		if (!$this->okt->catalog->config->rte_enable) {
			$this->rsProduct->content = Utilities::nlToP($this->rsProduct->content);
		}

		$this->rsProduct->category_url = $this->rsProduct->getCategoryUrl();

		# meta description
		if (!empty($this->rsProduct->meta_description)) {
			$this->page->meta_description = $this->rsProduct->meta_description;
		}
		elseif (!empty($this->okt->catalog->config->meta_description)) {
			$this->page->meta_description = $this->okt->catalog->config->meta_description;
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (!empty($this->rsProduct->meta_keywords)) {
			$this->page->meta_keywords = $this->rsProduct->meta_keywords;
		}
		elseif (!empty($this->okt->catalog->config->meta_keywords)) {
			$this->page->meta_keywords = $this->okt->catalog->config->meta_keywords;
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# Récupération des images
		$this->rsProduct->images = $this->rsProduct->getImagesInfo();

		# Récupération des fichiers
		$this->rsProduct->files = $this->rsProduct->getFilesInfo();


		# Title tag du module
		$this->page->addTitleTag($this->okt->catalog->getTitle());

		# Title tag de la catégorie
		$this->page->addTitleTag($this->rsProduct->category_name);

		# Title tag du produit
		$this->page->addTitleTag($this->rsProduct->title_tag);

		# titre de la page
		$this->page->setTitle($this->rsProduct->title);

		# titre SEO de la page
		$this->page->setTitleSeo($this->rsProduct->title);

		# fil d'ariane
		if (!$bIsHomePageRoute)
		{
			$this->page->breadcrumb->add($this->okt->catalog->getName(), CatalogHelpers::getCatalogUrl());

			if ($this->okt->catalog->config->categories_enable && $this->rsProduct->category_id)
			{
				$rsPath = $this->okt->catalog->getPath($this->rsProduct->category_id,true);
				while ($rsPath->fetch())
				{
					$this->page->breadcrumb->add(
						$rsPath->name,
						$this->page->getBaseUrl().$this->okt->catalog->config->public_catalog_url.'/'.$rsPath->slug
					);
				}
				unset($rsPath);
			}

			$this->page->breadcrumb->add($this->rsProduct->title,$this->rsProduct->url);
		}

		# affichage du template
		return $this->render('catalog_item_tpl', array(
			'product' => $this->rsProduct
		));
	}

}
