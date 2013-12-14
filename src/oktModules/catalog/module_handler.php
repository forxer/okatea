<?php
/**
 * @ingroup okt_module_catalog
 * @brief La classe principale du module.
 *
 */

use Tao\Admin\Page;
use Tao\Misc\Utilities as util;
use Tao\Modules\Module;
use Tao\Routing\Route;
use Tao\Admin\Menu as AdminMenu;
use Tao\Images\ImageUpload;
use Tao\Misc\FileUpload;
use Tao\Misc\NestedTree;

class module_catalog extends Module
{
	public $config = null;
	public $tree = null;

	public $filters = null;

	public $upload_dir;
	public $upload_url;

	protected $t_products;
	protected $t_categories;

	protected function prepend()
	{
		# chargement des principales locales
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# autoload
		$this->okt->autoloader->addClassMap(array(
			'catalogController' => __DIR__.'/inc/class.catalog.controller.php',
			'catalogFilters' => __DIR__.'/inc/class.catalog.filters.php',
			'catalogRecordset' => __DIR__.'/inc/class.catalog.recordset.php'
		));

		# permissions
		$this->okt->addPermGroup('catalog', __('m_catalog_perm_group'));
			$this->okt->addPerm('catalog', __('m_catalog_perm_global'), 'catalog');
			$this->okt->addPerm('catalog_categories', __('m_catalog_perm_categories'), 'catalog');
			$this->okt->addPerm('catalog_add', __('m_catalog_perm_add'), 'catalog');
			$this->okt->addPerm('catalog_remove', __('m_catalog_perm_remove'), 'catalog');
			$this->okt->addPerm('catalog_display', __('m_catalog_perm_display'), 'catalog');
			$this->okt->addPerm('catalog_config', __('m_catalog_perm_config'), 'catalog');

		# tables
		$this->t_products = $this->db->prefix.'mod_catalog_products';
		$this->t_categories = $this->db->prefix.'mod_catalog_categories';

		# config
		$this->config = $this->okt->newConfig('conf_catalog');
		$this->config->url = $this->okt->page->getBaseUrl().$this->config->public_catalog_url;

		# définition des routes
		$this->okt->router->addRoute('catalogList', new Route(
			html::escapeHTML($this->config->public_catalog_url),
			'catalogController', 'catalogList'
		));

		$this->okt->router->addRoute('catalogCategory', new Route(
			'^'.html::escapeHTML($this->config->public_catalog_url).'/(.*)$',
			'catalogController', 'catalogCategory'
		));

		$this->okt->router->addRoute('catalogItem', new Route(
			'^'.html::escapeHTML($this->config->public_product_url).'/(.*)$',
			'catalogController', 'catalogItem'
		));

		# répertoire upload
		$this->upload_dir = OKT_UPLOAD_PATH.'/catalog/';
		$this->upload_url = OKT_UPLOAD_URL.'/catalog/';

		# categories
		if ($this->config->categories_enable)
		{
			$this->tree = new NestedTree(
				$this->okt,
				$this->t_categories,
				'id',
				'parent_id',
				'ord',
				array(
					'active',
					'name',
					'slug',
					'ord'
				)
			);
		}
	}

	protected function prepend_admin()
	{
		# on détermine si on est actuellement sur ce module
		$this->onThisModule();

		# chargement des locales admin
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/admin');

		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->catalogSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);

			$this->okt->page->mainMenu->add(
				$this->getName(),
				'module.php?m=catalog',
				ON_CATALOG_MODULE,
				10,
				$this->okt->checkPerm('catalog'),
				null,
				$this->okt->page->catalogSubMenu,
				$this->url().'/icon.png'
			);
				$this->okt->page->catalogSubMenu->add(
					'Gestion',
					'module.php?m=catalog&amp;action=index',
					ON_CATALOG_MODULE && (!$this->okt->page->action || $this->okt->page->action === 'index' || $this->okt->page->action === 'edit'),
					1
				);
				$this->okt->page->catalogSubMenu->add(
					'Ajouter un produit',
					'module.php?m=catalog&amp;action=add',
					ON_CATALOG_MODULE && ($this->okt->page->action === 'add'),
					2,
					($this->config->categories_enable && $this->okt->checkPerm('catalog_add'))
				);
				$this->okt->page->catalogSubMenu->add(
					'Catégories',
					'module.php?m=catalog&amp;action=categories',
					ON_CATALOG_MODULE && ($this->okt->page->action === 'categories'),
					5,
					($this->config->categories_enable && $this->okt->checkPerm('catalog_categories'))
				);
				$this->okt->page->catalogSubMenu->add(
					'Affichage',
					'module.php?m=catalog&amp;action=display',
					ON_CATALOG_MODULE && ($this->okt->page->action === 'display'),
					10,
					$this->okt->checkPerm('catalog_display')
				);
				$this->okt->page->catalogSubMenu->add(
					'Configuration',
					'module.php?m=catalog&amp;action=config',
					ON_CATALOG_MODULE && ($this->okt->page->action === 'config'),
					20,
					$this->okt->checkPerm('catalog_config')
				);
		}
	}

	/**
	 * Initialisation des filtres
	 *
	 * @param string $part 	'public' ou 'admin'
	 */
	public function filtersStart($part='public')
	{
		if ($this->filters === null || !($this->filters instanceof catalogFilters)) {
			$this->filters = new catalogFilters($this,$part);
		}
	}


	/* Gestion des produits
	----------------------------------------------------------*/

	/**
	 * Retourne une liste de produits
	 *
	 * @param array $aParams
	 * @param boolean $bCountOnly
	 * @return object recordset/integer
	 */
	public function getProds($aParams=array(), $bCountOnly=false)
	{
		$sReqPlus = '';

		if (!empty($aParams['id'])) {
			$sReqPlus .= ' AND p.id='.(integer)$aParams['id'].' ';
		}

		if (!empty($aParams['category_id'])) {
			$sReqPlus .= ' AND p.category_id='.(integer)$aParams['category_id'].' ';
		}

		if (!empty($aParams['slug'])) {
			$sReqPlus .= ' AND p.slug=\''.$this->db->escapeStr($aParams['slug']).'\' ';
		}

		if (!empty($aParams['promo']) || !empty($aParams['promo_only']))
		{
			if ($this->config->fields['promo'] == 2) {
				$sReqPlus .= ' AND (NOW() BETWEEN p.promo_start AND p.promo_end) ';
			}
			else {
				$sReqPlus .= ' AND p.promo=1 ';
			}
		}

		if (!empty($aParams['nouvo']) || !empty($aParams['nouvo_only']))
		{
			if ($this->config->fields['nouvo'] == 2) {
				$sReqPlus .= ' AND (NOW() BETWEEN p.nouvo_start AND p.nouvo_end) ';
			}
			else {
				$sReqPlus .= ' AND p.nouvo=1 ';
			}
		}

		if (!empty($aParams['favo']) || !empty($aParams['favo_only']))
		{
			if ($this->config->fields['favo'] == 2) {
				$sReqPlus .= ' AND (NOW() BETWEEN p.favo_start AND p.favo_end) ';
			}
			else {
				$sReqPlus .= ' AND p.favo=1 ';
			}
		}


		# visibility ?
		if (isset($aParams['visibility']))
		{
			if ($aParams['visibility'] == 0) {
				$sReqPlus .= 'AND p.visibility=0 ';
			}
			elseif ($aParams['visibility'] == 1) {
				$sReqPlus .= 'AND p.visibility=1 ';
			}
			elseif ($aParams['visibility'] == 2) {
				$sReqPlus .= '';
			}
		}
		else {
			$reqPlus .= 'AND p.visibility=1 ';
		}

		if (!empty($aParams['search']))
		{
			$words = text::splitWords($aParams['search']);

			if (!empty($words))
			{
				foreach ($words as $i => $w) {
					$words[$i] = 'p.words LIKE \'%'.$this->db->escapeStr($w).'%\' ';
				}
				$sReqPlus .= ' AND '.implode(' AND ',$words).' ';
			}
		}

		if ($bCountOnly)
		{
			$sQuery =
			'SELECT COUNT(p.id) AS num_catalog '.
			'FROM '.$this->t_products.' AS p '.
			'WHERE 1 '.
			$sReqPlus;
		}
		else {
			$sQuery =
			'SELECT p.id, p.category_id, p.visibility, p.title, p.subtitle, p.title_tag, '.
			'p.slug, p.content, p.content_short, p.created_at, p.updated_at, p.price, p.price_promo, '.
			'p.promo, p.promo_start, p.promo_end, '.
			'p.nouvo, p.nouvo_start, p.nouvo_end, '.
			'p.favo, p.favo_start, p.favo_end, '.
			'p.meta_description, p.meta_keywords, p.images, p.files, '.
			'c.name AS category_name, c.slug AS category_slug, ';

			if ($this->config->fields['promo'] == 2) {
				$sQuery .= '(NOW() BETWEEN p.promo_start AND p.promo_end) AS is_promo, ';
			}
			else {
				$sQuery .= '(p.promo = 1) AS is_promo, ';
			}

			if ($this->config->fields['nouvo'] == 2) {
				$sQuery .= '(NOW() BETWEEN p.nouvo_start AND p.nouvo_end) AS is_nouvo, ';
			}
			else {
				$sQuery .= '(p.nouvo = 1) AS is_nouvo, ';
			}

			if ($this->config->fields['favo'] == 2) {
				$sQuery .= '(NOW() BETWEEN p.favo_start AND p.favo_end) AS is_favo ';
			}
			else {
				$sQuery .= '(p.favo = 1) AS is_favo ';
			}


			$sQuery .=
			'FROM '.$this->t_products.' AS p '.
				'LEFT JOIN '.$this->t_categories.' AS c ON p.category_id=c.id '.
			'WHERE 1 '.
			$sReqPlus;

			if (!empty($aParams['order'])) {
				$sQuery .= 'ORDER BY '.$aParams['order'].' ';
			}
			else {
				$sQuery .= 'ORDER BY p.created_at DESC ';
			}

			if (!empty($aParams['limit'])) {
				$sQuery .= 'LIMIT '.$aParams['limit'].' ';
			}
		}

		if (($rs = $this->db->select($sQuery,'catalogRecordset')) === false)
		{
			if ($bCountOnly) {
				return 0;
			}
			else {
				$rs = new catalogRecordset(array());
				$rs->setCore($this->okt);
				return $rs;
			}
		}

		if ($bCountOnly) {
			return (integer)$rs->num_catalog;
		}
		else {
			$rs->setCore($this->okt);
			return $rs;
		}
	}

	/**
	 * Retourne un produit donné sous forme de recordset
	 *
	 * @param $product_id
	 * @return object recordset
	 */
	public function getProd($product_id,$visibility=2)
	{
		return $this->getProds(array('id'=>$product_id,'visibility'=>$visibility));
	}

	/**
	 * Indique si un produit donné existe
	 *
	 * @param $id
	 * @return boolean
	 */
	public function prodExists($product_id)
	{
		if (empty($product_id) || $this->getProd($product_id)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Créer une instance de cursor et la retourne.
	 *
	 * @param array $data
	 * @return object cursor
	 */
	public function openCursor($data=null)
	{
		$cursor = $this->db->openCursor($this->t_products);

		if (!empty($data))
		{
			$aAllowedFields = array(
				'id',
				'category_id',
				'visibility',
				'title',
				'subtitle',
				'title_tag',
				'slug',
				'content',
				'content_short',
				'price',
				'price_promo',
				'created_at',
				'meta_description',
				'meta_keywords',
				'words',
				'promo',
				'promo_start',
				'promo_end',
				'nouvo',
				'nouvo_start',
				'nouvo_end',
				'favo',
				'favo_start',
				'favo_end'
			);

			foreach ($data as $k=>$v)
			{
				if (in_array($k,$aAllowedFields)) {
					$cursor->$k = $v;
				}
			}
		}

		return $cursor;

	}

	/**
	 * Ajout d'un produit
	 *
	 * @param cursor $cursor
	 * @return integer
	 */
	public function addProd($cursor)
	{
		# ajout dans la DB
		$cursor->created_at = date('Y-m-d H:i:s');
		$cursor->content = $this->okt->HTMLfilter($cursor->content);

		$cursor->words = implode(' ',array_unique(text::splitWords($cursor->title.' '.$cursor->subtitle.' '.$cursor->content_short.' '.$cursor->content)));

		if (!$cursor->insert()) {
			return false;
		}

		# récupération de l'ID
		$iNewId = $this->db->getLastID();

		# modification du slug
		if ($this->setProdSlug($iNewId) === false) {
			return false;
		}

		# ajout des images
		if ($this->config->images['enable'] && $this->addImages($iNewId) === false) {
			return false;
		}

		# ajout des fichiers
		if ($this->config->files['enable'] && $this->addFiles($iNewId) === false) {
			return false;
		}

		return $iNewId;
	}

	/**
	 * Mise à jour d'un produit
	 *
	 * @param integer $product_id
	 * @param cursor $cursor
	 * @return boolean
	 */
	public function updProd($product_id, $cursor)
	{
		if (!$this->okt->checkPerm('catalog')) {
			$this->error->set('Vous ne pouvez pas modifier ce produit.');
			return false;
		}

		$rs = $this->getProd($product_id);

		if ($rs->isEmpty()) {
			$this->error->set('Le produit #'.$product_id.' n’existe pas.');
			return false;
		}

		if (!$rs->isEditable()) {
			$this->error->set('Vous ne pouvez pas modifier ce produit.');
			return false;
		}

		# modification dans la DB
		$cursor->updated_at = date('Y-m-d H:i:s');
		$cursor->content = $this->okt->HTMLfilter($cursor->content);

		$cursor->words = implode(' ',text::splitWords($cursor->title.' '.$cursor->subtitle.' '.$cursor->content_short.' '.$cursor->content));

		if (!$cursor->update('WHERE id='.(integer)$product_id.' ')) {
			return false;
		}

		# modification du slug
		if ($this->setProdSlug($product_id) === false) {
			return false;
		}

		# modification des images
		if ($this->config->images['enable'] && $this->updImages($product_id) === false) {
			return false;
		}

		# modification des fichiers
		if ($this->config->files['enable'] && $this->updFiles($product_id) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Suppression d'un produit donné
	 *
	 * @param integer $product_id
	 * @return boolean
	 */
	public function deleteProd($product_id)
	{
		if (!$this->okt->checkPerm('catalog_remove')) {
			$this->error->set('Vous ne pouvez pas supprimer les produits.');
			return false;
		}

		$rs = $this->getProd($product_id);

		if ($rs->isEmpty()) {
			$this->error->set('Le produit #'.$product_id.' n’existe pas.');
			return false;
		}

		if (!$rs->isDeletable()) {
			$this->error->set('Vous ne pouvez pas supprimer ce produit.');
			return false;
		}

		$this->deleteImages($product_id);

		$this->deleteFiles($product_id);

		$query =
		'DELETE FROM '.$this->t_products.' '.
		'WHERE id='.(integer)$product_id;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->db->optimize($this->t_products);

		return true;
	}

	public function checkProdData($params)
	{
		if (empty($params['title'])) {
			$this->error->set('Vous devez saisir un titre.');
		}

		if (empty($params['content'])) {
			$this->error->set('Vous devez saisir un contenu.');
		}

		if (!empty($params['price']) && !empty($params['price_promo']) && ($params['price_promo'] >= $params['price'])) {
			$this->error->set('Le prix promotionnel doit être inférieur au prix.');
		}

		return $this->error->isEmpty();
	}

	/**
	 * Switch le statut d'un produit donné
	 *
	 * @param integer $product_id
	 * @return boolean
	 */
	public function switchProdStatus($product_id)
	{
		if (!$this->okt->checkPerm('catalog')) {
			$this->error->set('Vous ne pouvez pas modifier ce produit.');
			return false;
		}

		$rs = $this->getProd($product_id);

		if ($rs->isEmpty()) {
			$this->error->set('Le produit #'.$product_id.' n’existe pas.');
			return false;
		}

		if (!$rs->isEditable()) {
			$this->error->set('Vous ne pouvez pas modifier ce produit.');
			return false;
		}

		$query =
		'UPDATE '.$this->t_products.' SET '.
			'visibility=1-visibility '.
		'WHERE id='.(integer)$product_id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Modifie le statut d'un produit donné
	 *
	 * @param integer $product_id
	 * @param integer $status
	 * @return boolean
	 */
	public function setProdStatus($product_id,$status)
	{
		$rs = $this->getProd($product_id);

		if ($rs->isEmpty()) {
			$this->error->set('Le produit #'.$product_id.' n’existe pas.');
			return false;
		}

		$query =
		'UPDATE '.$this->t_products.' SET '.
			'visibility='.($status == 1 ? 0 : 1).' '.
		'WHERE id='.(integer)$product_id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Création du slug d'un produit donné
	 *
	 * @param integer $product_id
	 * @return boolean
	 */
	protected function setProdSlug($product_id)
	{
		$rs = $this->getProd($product_id);

		$slug = $this->buildProdURL($rs->title,$rs->slug,$product_id);

		$query =
		'UPDATE '.$this->t_products.' SET '.
			'slug=\''.$this->db->escapeStr($slug).'\' '.
		'WHERE id='.(integer)$product_id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Construit l'URL d'un produit donné
	 *
	 * @param string $title
	 * @param string $url
	 * @param integer $product_id
	 * @return string
	 */
	protected function buildProdURL($title,$url,$product_id)
	{
		if (empty($url)) {
			$url = $title;
		}

		$url = util::strToSlug($url, false);

		# Let's check if URL is taken…
		$query =
		'SELECT slug FROM '.$this->t_products.' '.
		'WHERE slug=\''.$this->db->escapeStr($url).'\' '.
		'AND id <> '.(integer)$product_id. ' '.
		'ORDER BY slug DESC';

		$rs = $this->db->select($query);

		if (!$rs->isEmpty())
		{
			$query =
			'SELECT slug FROM '.$this->t_products.' '.
			'WHERE slug LIKE \''.$this->db->escapeStr($url).'%\' '.
			'AND id <> '.(integer)$product_id. ' '.
			'ORDER BY slug DESC ';

			$rs = $this->db->select($query);
			$a = array();
			while ($rs->fetch()) {
				$a[] = $rs->slug;
			}

			$url = util::getIncrementedString($a, $url, '-');
		}

		# URL is empty?
		if ($url == '') {
			throw new Exception(__('Empty entry URL'));
		}

		return $url;
	}

	/**
	 * Retourne la liste des types de statuts au pluriel
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public static function getProdsStatuses($flip=false)
	{
		$aStatus = array(
			0 => 'masqués',
			1 => 'visibles'
		);

		if ($flip) {
			$aStatus = array_flip($aStatus);
		}

		return $aStatus;
	}

	/**
	 * Retourne la liste des types de statuts au singulier
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public static function getProdsStatus($flip=false)
	{
		$aStatus = array(
			0 => 'masqué',
			1 => 'visible'
		);

		if ($flip) {
			$aStatus = array_flip($aStatus);
		}

		return $aStatus;
	}


	/* Gestion des images des produits
	----------------------------------------------------------*/

	public function getImageUpload()
	{
		$o = new ImageUpload($this->okt,$this->config->images);
		$o->setConfig(array(
			'upload_dir' => $this->upload_dir.'img/',
			'upload_url' => $this->upload_url.'img/'
		));

		return $o;
	}

	/**
	 * Ajout d'image(s) à un produit donné
	 *
	 * @param $product_id
	 * @return boolean
	 */
	public function addImages($product_id)
	{
		$aImages = $this->getImageUpload()->addImages($product_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updProdImages($product_id, $aImages);
	}

	/**
	 * Modification d'image(s) d'un produit donné
	 *
	 * @param $product_id
	 * @return boolean
	 */
	public function updImages($product_id)
	{
		$aCurrentImages = $this->getProdImages($product_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aImages = $this->getImageUpload()->updImages($product_id, $aCurrentImages);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updProdImages($product_id, $aImages);
	}

	/**
	 * Suppression d'une image donnée d'un produit donné
	 *
	 * @param $product_id
	 * @param $img_id
	 * @return boolean
	 */
	public function deleteImage($product_id,$img_id)
	{
		$aCurrentImages = $this->getProdImages($product_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aNewImages = $this->getImageUpload()->deleteImage($product_id, $aCurrentImages, $img_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updProdImages($product_id, $aNewImages);
	}

	/**
	 * Suppression des images d'un produit donné
	 *
	 * @param $product_id
	 * @return boolean
	 */
	public function deleteImages($product_id)
	{
		$aCurrentImages = $this->getProdImages($product_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->getImageUpload()->deleteAllImages($product_id, $aCurrentImages);

		return $this->updProdImages($product_id);
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

		$rsProds = $this->getProds(array('visibility'=>2));

		while ($rsProds->fetch())
		{
			$aImages = $rsProds->getImagesInfo();
			$aImagesList = array();

			foreach ($aImages as $key=>$image)
			{
				$this->getImageUpload()->buildThumbnails($rsProds->id, $image['img_name']);

				$aImagesList[$key] = array_merge(
					$aImages[$key],
					$this->getImageUpload()->buildImageInfos($rsProds->id, $image['img_name'])
				);
			}

			$this->updProdImages($rsProds->id, $aImagesList);
		}

		return true;
	}

	/**
	 * Récupère la liste des images d'un produit donné
	 *
	 * @param $product_id
	 * @return array
	 */
	public function getProdImages($product_id)
	{
		if (!$this->prodExists($product_id)) {
			$this->error->set('Le produit #'.$product_id.' n’existe pas.');
			return false;
		}

		$rsProd = $this->getProd($product_id);
		$aProdImages = $rsProd->images ? unserialize($rsProd->images) : array();

		return $aProdImages;
	}

	/**
	 * Met à jours la liste des images d'un produit donné
	 *
	 * @param array $product_id
	 * @param $aImages
	 * @return unknown_type
	 */
	public function updProdImages($product_id, $aImages=array())
	{
		if (!$this->prodExists($product_id)) {
			$this->error->set('Le produit #'.$product_id.' n’existe pas.');
			return false;
		}

		$aImages = !empty($aImages) ? serialize($aImages) : NULL;

		$query =
		'UPDATE '.$this->t_products.' SET '.
			'images='.(!is_null($aImages) ? '\''.$this->db->escapeStr($aImages).'\'' : 'NULL').' '.
		'WHERE id='.(integer)$product_id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}


	/* Gestion des fichiers des produits
	----------------------------------------------------------*/

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
	 * Ajout de fichier(s) à un produit donné
	 *
	 * @param $product_id
	 * @return boolean
	 */
	public function addFiles($product_id)
	{
		$aFiles = $this->getFileUpload()->addFiles($product_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updProdFiles($product_id, $aFiles);
	}

	/**
	 * Modification de fichier(s) d'un produit donné
	 *
	 * @param $product_id
	 * @return boolean
	 */
	public function updFiles($product_id)
	{
		$aCurrentFiles = $this->getProdFiles($product_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aFiles = $this->getFileUpload()->updFiles($product_id,$aCurrentFiles);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updProdFiles($product_id, $aFiles);
	}

	/**
	 * Suppression d'un fichier donné d'un produit donné
	 *
	 * @param $product_id
	 * @param $file_id
	 * @return boolean
	 */
	public function deleteFile($product_id,$file_id)
	{
		$aCurrentFiles = $this->getProdFiles($product_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aNewFiles = $this->getFileUpload()->deleteFile($product_id,$aCurrentFiles,$file_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updProdFiles($product_id,$aNewFiles);
	}

	/**
	 * Suppression des fichiers d'un produit donné
	 *
	 * @param $product_id
	 * @return boolean
	 */
	public function deleteFiles($product_id)
	{
		$aCurrentFiles = $this->getProdFiles($product_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->getFileUpload()->deleteAllFiles($aCurrentFiles);

		return $this->updProdFiles($product_id);
	}

	/**
	 * Récupère la liste des fichiers d'un produit donné
	 *
	 * @param $product_id
	 * @return array
	 */
	public function getProdFiles($product_id)
	{
		if (!$this->prodExists($product_id)) {
			$this->error->set('Le produit #'.$product_id.' n’existe pas.');
			return false;
		}

		$rs = $this->getProd($product_id);
		$aProdFiles = $rs->files ? unserialize($rs->files) : array();

		return $aProdFiles;
	}

	/**
	 * Met à jours la liste des fichiers d'un produit donné
	 *
	 * @param integer $product_id
	 * @param array $aFiles
	 * @return boolean
	 */
	public function updProdFiles($product_id, $aFiles=array())
	{
		if (!$this->prodExists($product_id)) {
			$this->error->set('Le produit #'.$product_id.' n’existe pas.');
			return false;
		}

		$aFiles = !empty($aFiles) ? serialize($aFiles) : NULL;

		$query =
		'UPDATE '.$this->t_products.' SET '.
			'files='.(!is_null($aFiles) ? '\''.$this->db->escapeStr($aFiles).'\'' : 'NULL').' '.
		'WHERE id='.(integer)$product_id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}


	/* Gestion des catégories de produits
	----------------------------------------------------------*/

	/**
	 * Récupération de catégories.
	 *
	 * @param $params
	 * @param $count_only
	 * @return recordset
	 */
	public function getCategories($params=array(), $count_only=false)
	{
		$reqPlus = '';

		$with_count = isset($params['with_count']) ? (boolean)$params['with_count'] : false;

		if (!empty($params['id'])) {
			$reqPlus .= 'AND c.id='.(integer)$params['id'].' ';
			$with_count = false;
		}

		if (!empty($params['slug'])) {
			$reqPlus .= 'AND c.slug=\''.$this->db->escapeStr($params['slug']).'\' ';
			$with_count = false;
		}

		if (isset($params['active']))
		{
			if ($params['active'] == 0) {
				$reqPlus .= 'AND c.active=0 ';
				$with_count = false;
			}
			elseif ($params['active'] == 1) {
				$reqPlus .= 'AND c.active=1 ';
				$with_count = false;
			}
			elseif ($params['active'] == 2) {
				$reqPlus .= '';
			}
		}
		else {
			$reqPlus .= 'AND c.active=1 ';
			$with_count = false;
		}

		if ($count_only)
		{
			$query =
			'SELECT COUNT(c.id) AS num_categories '.
			'FROM '.$this->t_categories.' AS r '.
			'WHERE 1 '.
			$reqPlus.' ';
		}
		else {
			$query =
			'SELECT c.id, c.active, c.name, c.slug, c.ord, c.parent_id, c.level, '.
			'COUNT(p.id) AS num_products '.
			'FROM '.$this->t_categories.' AS c '.
				'LEFT JOIN '.$this->t_products.' AS p ON c.id=p.category_id '.
			'WHERE 1 '.
			$reqPlus.' '.
			'GROUP BY c.id '.
			'ORDER BY nleft asc ';

			if (!empty($params['limit'])) {
				$query .= 'LIMIT '.$params['limit'].' ';
			}
		}

		if (($rs = $this->db->select($query)) === false) {
			return new recordset(array());
		}

		if ($count_only) {
			return (integer)$rs->num_categories;
		}
		else {
			if ($with_count)
			{
				$data = array();
				$stack = array();
				$level = 0;
				foreach(array_reverse($rs->getData()) as $category)
				{
					$num_products = (integer) $category['num_products'];

					if ($category['level'] > $level) {
						$nb_total = $num_products;
						$stack[$category['level']] = $num_products;
					} elseif ($category['level'] == $level) {
						$nb_total = $num_products;
						$stack[$category['level']] += $num_products;
					} else {
						$nb_total = $stack[$category['level']+1] + $num_products;
						if (isset($stack[$category['level']])) {
							$stack[$category['level']] += $nb_total;
						} else {
							$stack[$category['level']] = $nb_total;
						}
						unset($stack[$category['level']+1]);
					}

					$level = $category['level'];

					$category['num_products'] = $num_products;
					$category['num_total'] = $nb_total;

					array_unshift($data,$category);
				}

				return new recordset($data);
			}
			else {
				return $rs;
			}
		}
	}

	public function getCategory($id)
	{
		return $this->getCategories(array('id'=>$id,'active'=>2));
	}

	public function categoryExists($id)
	{
		if ($this->getCategory($id)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Ajout d'une catégorie
	 *
	 * @param integer $active
	 * @param string $name
	 * @param string $slug
	 * @return integer
	 */
	public function addCategory($active,$name,$slug,$parent_id=0)
	{
		$max_ord = $this->numChildren($parent_id);

		# infos parents
		if ($parent_id > 0)
		{
			$parent = $this->getCategory($parent_id);
			$slug = $parent->slug.'/'.$slug;

			if ($parent->active == 0) {
				$active = 0;
			}
		}

		$query =
		'INSERT INTO '.$this->t_categories.' ( '.
			'active, name, slug, parent_id, ord '.
		') VALUES ( '.
			(integer)$active.', '.
			'\''.$this->db->escapeStr($name).'\', '.
			'\''.$this->db->escapeStr($slug).'\', '.
			(integer)$parent_id.', '.
			($max_ord+1).
		'); ';

		if (!$this->db->execute($query)) {
			return false;
		}

		$new_id =  $this->db->getLastID();

		$this->rebuildTree();

		return $new_id;
	}

	/**
	 * Modification d'une catégorie
	 *
	 * @param integer $id
	 * @param integer $active
	 * @param string $name
	 * @param string $slug
	 * @return boolean
	 */
	public function updCategory($id, $active, $name, $slug, $parent_id)
	{
		if (!$this->categoryExists($id)) {
			$this->error->set('La catégorie #'.$id.' n’existe pas.');
			return false;
		}

		# infos parent
		if ($parent_id > 0)
		{
			if ($this->isDescendantOf($parent_id,$id)) {
				$this->error->set('Vous ne pouvez pas mettre une catégorie dans ses enfants.');
				return false;
			}

			$parent = $this->getCategory($parent_id);
			$slug = $parent->slug.'/'.$slug;

			if ($parent->active == 0) {
				$active = 0;
			}
		}

		$query =
		'UPDATE '.$this->t_categories.' SET '.
			'active='.(integer)$active.', '.
			'name=\''.$this->db->escapeStr($name).'\', '.
			'slug=\''.$this->db->escapeStr($slug).'\', '.
			'parent_id='.(integer)$parent_id.' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		if ($active == 0)
		{
			$childrens = $this->getDescendants($id);
			while ($childrens->fetch()) {
				$this->setStatus($childrens->id,$active);
			}
		}

		$this->rebuildTree();

		return true;
	}

	/**
	 * Reconstruction des index de recherche de tous les produits
	 *
	 */
	public function indexAllProducts()
	{
		$rsProducts = $this->getProds(array('visibility' => 2));
		while ($rsProducts->fetch())
		{
			$words =
				$rsProducts->title.' '.
				$rsProducts->subtitle.' '.
				$rsProducts->content_short.' '.
				$rsProducts->content.' ';

			$words = implode(' ',text::splitWords($words));

			$query =
			'UPDATE '.$this->t_products.' SET '.
				'words=\''.$this->db->escapeStr($words).'\' '.
			'WHERE id='.(integer)$rsProducts->id;

			$this->db->execute($query);
		}

		return true;
	}

	public function updCategoryOrder($id,$ord)
	{
		$query =
		'UPDATE '.$this->t_categories.' SET '.
			'ord='.(integer)$ord.' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Switch le statut de visibilité d'une catégorie donnée
	 *
	 * @param integer $id
	 * @return boolean
	 */
	public function switchCategoryStatus($id)
	{
		$rsCategory = $this->getCategory($id);

		if ($rsCategory->isEmpty()) {
			$this->error->set('La catégorie #'.$id.' n’existe pas.');
			return false;
		}

		$status = $rsCategory->active ? 0 : 1;

		if ($status == 0)
		{
			$childrens = $this->getDescendants($id);
			while ($childrens->fetch()) {
				$this->setStatus($childrens->id,0);
			}
		}
		else if ($rsCategory->parent_id != 0)
		{
			$rsParent = $this->getCategory($rsCategory->parent_id);

			if ($rsParent->active == 0) {
				$this->error->set('La catégorie parent est masquée, vous devez la rendre visible avant de le faire pour celle-ci.');
				return false;
			}
		}

		return $this->setStatus($id,$status);
	}

	private function setStatus($id,$status)
	{
		$query =
		'UPDATE '.$this->t_categories.' SET '.
			'active='.(integer)$status.' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Suppression d'une catégorie
	 *
	 * @param integer $id
	 * @return boolean
	 */
	public function delCategory($id)
	{
		$rsCategory = $this->getCategory($id);

		if ($rsCategory->isEmpty()) {
			$this->error->set('La catégorie #'.$id.' n’existe pas.');
			return false;
		}

		$childrens = $this->getChildren($id);
		while ($childrens->fetch()) {
			$this->setParentId($childrens->id,$rsCategory->parent_id);
		}

		$query =
		'DELETE FROM '.$this->t_categories.' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->rebuildTree();

		$this->db->optimize($this->t_categories);

		return true;
	}

	private function setParentId($id,$parent_id)
	{
		$query =
		'UPDATE '.$this->t_categories.' SET '.
			'parent_id='.(integer)$parent_id.' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	public function getDescendants($id=0, $includeSelf=false)
	{
		return $this->tree->getDescendants($id, $includeSelf, false);
	}

	public function getChildren($id=0, $includeSelf=false)
	{
		return $this->tree->getChildren($id,$includeSelf);
	}

	public function getPath($id=0, $includeSelf=false)
	{
		return $this->tree->getPath($id, $includeSelf);
	}

	public function isDescendantOf($descendant_id, $ancestor_id)
	{
		return $this->tree->isDescendantOf($descendant_id, $ancestor_id);
	}

	public function isChildOf($child_id, $parent_id)
	{
		return $this->tree->isChildOf($child_id, $parent_id);
	}

	public function numDescendants($id)
	{
		return $this->tree->numDescendants($id);
	}

	public function numChildren($id)
	{
		return $this->tree->numChildren($id);
	}

	public function rebuildTree()
	{
		return $this->tree->rebuild();
	}

}
