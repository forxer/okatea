<?php
/**
 * @ingroup okt_module_catalog
 * @brief Controller public.
 *
 */

use Tao\Misc\Utilities as util;
use Tao\Core\Controller;
use Tao\Website\Pager;

class catalogController extends Controller
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
			return $this->redirect($this->okt->catalog->config->url);
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
			$this->page->meta_description = $this->okt->catalog->config->meta_description;
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if ($this->okt->catalog->config->meta_keywords != '') {
			$this->page->meta_keywords = $this->okt->catalog->config->meta_keywords;
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# fil d'ariane
		if (!$this->isDefaultRoute(__CLASS__, __FUNCTION__)) {
			$this->page->breadcrumb->add($this->okt->catalog->getName(), $this->okt->catalog->config->url);
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
		$productsList->numPages = $iNumPages;
		$productsList->pager = $oProductsPager;

		# affichage du template
		return $this->render('catalog_list_tpl', array(
			'productsList' => $productsList
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

		$rsCategory = $this->okt->catalog->getCategories(array(
			'slug' => $slug,
			'visibility' => 1
		));

		if ($rsCategory->isEmpty()) {
			return $this->serve404();
		}

		# route par défaut ?
		$bIsDefaultRoute = $this->isDefaultRoute(__CLASS__, __FUNCTION__, $slug);

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
			return $this->redirect($this->okt->catalog->config->url);
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
			$this->page->meta_description = $this->okt->catalog->config->meta_description;
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if ($this->okt->catalog->config->meta_keywords != '') {
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
		if (!$bIsDefaultRoute)
		{
			$this->page->breadcrumb->add($this->okt->catalog->getName(), $this->okt->catalog->config->url);

			$rsPath = $this->okt->catalog->getPath($rsCategory->id,true);

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
		$this->page->setTitle($rsCategory->name);

		# titre SEO de la page
		$this->page->setTitleSeo($rsCategory->name);

		# raccourcis
		$productsList->numPages = $iNumPages;
		$productsList->pager = $oProductsPager;

		# affichage du template
		return $this->render('catalog_list_tpl', array(
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
		$this->page->module = 'catalog';
		$this->page->action = 'item';

		# Récupération du produit en fonction du slug
		if (!empty($aMatches[0])) {
			$slug = $aMatches[0];
		}
		else {
			return $this->serve404();
		}

		$product = $this->okt->catalog->getProds(array(
			'slug' => $slug,
			'visibility' => 1
		));

		if ($product->isEmpty()) {
			return $this->serve404();
		}

		# route par défaut ?
		$bIsDefaultRoute = $this->isDefaultRoute(__CLASS__, __FUNCTION__, $slug);

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
			$this->page->meta_description = $product->meta_description;
		}
		else if ($this->okt->catalog->config->meta_description != '') {
			$this->page->meta_description = $this->okt->catalog->config->meta_description;
		}
		else {
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if ($product->meta_keywords != '') {
			$this->page->meta_keywords = $product->meta_keywords;
		}
		else if ($this->okt->catalog->config->meta_keywords != '') {
			$this->page->meta_keywords = $this->okt->catalog->config->meta_keywords;
		}
		else {
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# Récupération des images
		$product->images = $product->getImagesInfo();

		# Récupération des fichiers
		$product->files = $product->getFilesInfo();


		# Title tag du module
		$this->page->addTitleTag($this->okt->catalog->getTitle());

		# Title tag de la catégorie
		$this->page->addTitleTag($product->category_name);

		# Title tag du produit
		$this->page->addTitleTag($product->title_tag);

		# titre de la page
		$this->page->setTitle($product->title);

		# titre SEO de la page
		$this->page->setTitleSeo($product->title);

		# fil d'ariane
		if (!$bIsDefaultRoute)
		{
			$this->page->breadcrumb->add($this->okt->catalog->getName(), $this->okt->catalog->config->url);

			if ($this->okt->catalog->config->categories_enable && $product->category_id)
			{
				$rsPath = $this->okt->catalog->getPath($product->category_id,true);
				while ($rsPath->fetch())
				{
					$this->page->breadcrumb->add(
						$rsPath->name,
						$this->page->getBaseUrl().$this->okt->catalog->config->public_catalog_url.'/'.$rsPath->slug
					);
				}
				unset($rsPath);
			}

			$this->page->breadcrumb->add($product->title,$product->url);
		}

		# affichage du template
		return $this->render('catalog_item_tpl', array(
			'product' => $product
		));
	}

}
