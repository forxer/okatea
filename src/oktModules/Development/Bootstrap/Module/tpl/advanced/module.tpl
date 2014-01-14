<?php
##header##


use Tao\Admin\Page;
use Tao\Misc\Utilities;
use Tao\Admin\Menu as AdminMenu;
use Tao\Images\ImageUpload;
use Tao\Misc\FileUpload;
use Tao\Modules\Module;

class module_##module_id## extends Module
{
	public $config = null;
	public $filters = null;

	public $upload_dir;
	public $upload_url;

	protected $table;

	protected function prepend()
	{
		# chargement des principales locales
		$this->okt->l10n->loadFile(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# autoload
		$this->okt->autoloader->addClassMap(array(
			'##module_camel_case_id##Recordset/inc/class.##module_id##.recordset.php',
			'##module_camel_case_id##Filters/inc/class.##module_id##.filters.php'
		));

		# permissions
		$this->okt->addPermGroup('##module_id##', __('m_##module_id##_perm_group'));
			$this->okt->addPerm('##module_id##', __('m_##module_id##_perm_global'), '##module_id##');
			$this->okt->addPerm('##module_id##_add', __('m_##module_id##_perm_add'), '##module_id##');
			$this->okt->addPerm('##module_id##_remove', __('m_##module_id##_perm_remove'), '##module_id##');
			$this->okt->addPerm('##module_id##_display', __('m_##module_id##_perm_display'), '##module_id##');
			$this->okt->addPerm('##module_id##_config', __('m_##module_id##_perm_config'), '##module_id##');

		# configuration
		$this->config = $this->okt->newConfig('conf_##module_id##');
		$this->config->url = $this->okt->config->app_path.$this->config->public_list_url;

		# tables
		$this->table = $this->db->prefix.'mod_##module_id##';

		# répertoire upload
		$this->upload_dir = $this->okt->options->get('upload_dir').'/##module_id##/';
		$this->upload_url = $okt->options->upload_url.'/##module_id##/';
	}

	protected function prepend_admin()
	{
		# chargement des locales admin
		$this->okt->l10n->loadFile(__DIR__.'/locales/'.$this->okt->user->language.'/admin');

		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->##module_camel_case_id##SubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);

			$this->okt->page->mainMenu->add(
				$this->getName(),
				'module.php?m=##module_id##',
				$this->bCurrentlyInUse,
				20,
				$this->okt->checkPerm('##module_id##'),
				null,
				$this->okt->page->##module_camel_case_id##SubMenu,
				$this->url().'/icon.png'
			);

				$this->okt->page->##module_camel_case_id##SubMenu->add(
					__('m_##module_id##_menu_management'),
					'module.php?m=##module_id##&amp;action=index',
					$this->bCurrentlyInUse && (!$this->okt->page->action || $this->okt->page->action === 'index' || $this->okt->page->action === 'edit'),
					10
				);

				$this->okt->page->##module_camel_case_id##SubMenu->add(
					__('m_##module_id##_menu_add_item'),
					'module.php?m=##module_id##&amp;action=add',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'add'),
					20,
					$this->okt->checkPerm('##module_id##_add')
				);

				$this->okt->page->##module_camel_case_id##SubMenu->add(
					__('m_##module_id##_menu_display'),
					'module.php?m=##module_id##&amp;action=display',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'display'),
					200,
					$this->okt->checkPerm('##module_id##_display')
				);

				$this->okt->page->##module_camel_case_id##SubMenu->add(
					__('m_##module_id##_menu_config'),
					'module.php?m=##module_id##&amp;action=config',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'config'),
					300,
					$this->okt->checkPerm('##module_id##_config')
				);
		}

	}

	protected function prepend_public()
	{
	}

	/**
	 * Initialisation des filtres
	 *
	 * @param string $part 	'public' ou 'admin'
	 */
	public function filtersStart($part='public')
	{
		if ($this->filters === null || !($this->filters instanceof ##module_camel_case_id##Filters)) {
			$this->filters = new ##module_camel_case_id##Filters($this->okt, $this->config, $part);
		}
	}


	/* Gestion des éléments
	----------------------------------------------------------*/

	/**
	 * Retourne une liste d'éléments selon des paramètres donnés
	 *
	 * @param	array	params			Paramètres de requete
	 * @param	boolean	count_only		Ne renvoi qu'un nombre d'éléments
	 * @return  object recordset/integer
	 */
	public function getItems($params=array(), $count_only=false)
	{
		$reqPlus = '';

		if (!empty($params['id'])) {
			$reqPlus .= ' AND id='.(integer)$params['id'].' ';
		}

		if (!empty($params['slug'])) {
			$reqPlus .= ' AND slug=\''.$this->db->escapeStr($params['slug']).'\' ';
		}

		if (isset($params['visibility']))
		{
			if ($params['visibility'] == 0) {
				$reqPlus .= 'AND visibility=0 ';
			}
			elseif ($params['visibility'] == 1) {
				$reqPlus .= 'AND visibility=1 ';
			}
			elseif ($params['visibility'] == 2) {
				$reqPlus .= '';
			}
		}
		else {
			$reqPlus .= 'AND visibility=1 ';
		}

		if ($count_only)
		{
			$query =
			'SELECT COUNT(id) AS num_items '.
			'FROM '.$this->table.' '.
			'WHERE 1 '.
			$reqPlus;
		}
		else {
			$query =
			'SELECT '.
				'id, visibility, title, slug, title_tag, description, created_at, updated_at, '.
				'images, files, meta_description, meta_keywords '.
			'FROM '.$this->table.'  '.
			'WHERE 1 '.
			$reqPlus;

			if (!empty($params['limit'])) {
				$query .= 'LIMIT '.$params['limit'].' ';
			}
		}

		if (($rs = $this->db->select($query,'##module_camel_case_id##Recordset')) === false)
		{
			if ($count_only) {
				return 0;
			}
			else {
				$rs = new ##module_camel_case_id##Recordset(array());
				$rs->setCore($this->okt);
				return $rs;
			}
		}

		if ($count_only) {
			return (integer)$rs->num_items;
		}
		else {
			$rs->setCore($this->okt);
			return $rs;
		}
	}

	/**
	 * Retourne un élément donné sous forme de recordset
	 *
	 * @param integer $id
	 * @param integer $visibility
	 * @return recordset
	 */
	public function getItem($id,$visibility=2)
	{
		return $this->getItems(array('id'=>$id,'visibility'=>$visibility));
	}

	/**
	 * Teste l'existence d'un élément
	 *
	 * @param $id
	 * @return boolean
	 */
	public function itemExists($id)
	{
		if (empty($id) || $this->getItem($id)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Créer une instance de cursor et la retourne
	 *
	 * @param array $data
	 * @return object cursor
	 */
	public function openCursor($data=null)
	{
		$cursor = $this->db->openCursor($this->table);

		if (!empty($data) && is_array($data))
		{
			foreach ($data as $k=>$v) {
				$cursor->$k = $v;
			}
		}

		return $cursor;
	}

	/**
	 * Ajout d'un élément
	 *
	 * @param cursor $cursor
	 * @return integer
	 */
	public function addItem($cursor)
	{
		$date = date('Y-m-d H:i:s');
		$cursor->created_at = $date;
		$cursor->updated_at = $date;

		$cursor->description = $this->okt->HTMLfilter($cursor->description);

		$cursor->meta_description = html::clean($cursor->meta_description);
		$cursor->meta_keywords = html::clean($cursor->meta_keywords);

		if (!$cursor->insert()) {
			return false;
		}

		# récupération de l'ID
		$iNewId = $this->db->getLastID();

		# ajout des images
		if ($this->config->images['enable'] && $this->addImages($iNewId) === false) {
			return false;
		}

		# ajout des fichiers
		if ($this->config->files['enable'] && $this->addFiles($iNewId) === false) {
			return false;
		}

		# création du slug
		if ($this->setItemSlug($iNewId) === false) {
			return false;
		}

		return $iNewId;
	}

	/**
	 * Mise à jour d'un élément
	 *
	 * @param integer id
	 * @param cursor $cursor
	 * @return boolean
	 */
	public function updItem($id, $cursor)
	{
		if (!$this->itemExists($id)) {
			$this->error->set(sprintf(__('m_##module_id##_item_%s_not_exists'),$id));
			return false;
		}

		$cursor->updated_at = date('Y-m-d H:i:s');

		$cursor->description = $this->okt->HTMLfilter($cursor->description);

		$cursor->meta_description = html::clean($cursor->meta_description);
		$cursor->meta_keywords = html::clean($cursor->meta_keywords);

		if (!$cursor->update('WHERE id='.(integer)$id.' ')) {
			return false;
		}

		# modification des images
		if ($this->config->images['enable'] && $this->updImages($id) === false) {
			return false;
		}

		# modification des fichiers
		if ($this->config->files['enable'] && $this->updFiles($id) === false) {
			return false;
		}

		# modification du slug
		if ($this->setItemSlug($id) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Suppression d'un élément donné
	 *
	 * @param integer $id
	 * @return boolean
	 */
	public function delItem($id)
	{
		if (!$this->itemExists($id)) {
			$this->error->set(sprintf(__('m_##module_id##_item_%s_not_exists'),$id));
			return false;
		}

		if ($this->deleteImages($id) === false) {
			return false;
		}

		if ($this->deleteFiles($id) === false) {
			return false;
		}

		$query =
		'DELETE FROM '.$this->table.' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->db->optimize($this->table);

		return true;
	}

	/**
	 * Switch le statut de visibilité d'un élément donné
	 *
	 * @param integer $id
	 * @return boolean
	 */
	public function switchItemStatus($id)
	{
		if (!$this->itemExists($id)) {
			$this->error->set(sprintf(__('m_##module_id##_item_%s_not_exists'),$id));
			return false;
		}

		$query =
		'UPDATE '.$this->table.' SET '.
			'updated_at=NOW(), '.
			'visibility = 1-visibility '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Définit le statut de visibilité d'un élément donné
	 *
	 * @param integer $id
	 * @param integer $visibility
	 * @return boolean
	 */
	public function setItemStatus($id,$visibility)
	{
		if (!$this->itemExists($id)) {
			$this->error->set(sprintf(__('m_##module_id##_item_%s_not_exists'),$id));
			return false;
		}

		$query =
		'UPDATE '.$this->table.' SET '.
			'updated_at=NOW(), '.
			'visibility = '.($visibility == 1 ? 1 : 0).' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Vérifie les données envoyées en fonction de la configuration
	 *
	 * @param array $params
	 * @return boolean
	 */
	public function checkPostData($item_data)
	{
		if (empty($item_data['title'])) {
			$this->error->set('Vous devez saisir un titre.');
		}

		if (empty($item_data['description'])) {
			$this->error->set('Vous devez saisir une description.');
		}

		return $this->error->isEmpty();
	}


	/* Gestion des images des éléments
	----------------------------------------------------------*/

	/**
	 * Retourne une instance de la classe oktImageUpload
	 *
	 * @return object
	 */
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
	 * Ajout d'image(s) à un élément donné
	 *
	 * @param $item_id
	 * @return boolean
	 */
	public function addImages($item_id)
	{
		$aImages = $this->getImageUpload()->addImages($item_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updImagesInDb($item_id,$aImages);
	}

	/**
	 * Modification d'image(s) d'un élément donné
	 *
	 * @param $item_id
	 * @return boolean
	 */
	public function updImages($item_id)
	{
		$aCurrentImages = $this->getImagesFromDb($item_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aImages = $this->getImageUpload()->updImages($item_id,$aCurrentImages);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updImagesInDb($item_id,$aImages);
	}

	/**
	 * Suppression d'une image donnée d'un élément donné
	 *
	 * @param $item_id
	 * @param $img_id
	 * @return boolean
	 */
	public function deleteImage($item_id,$img_id)
	{
		$aCurrentImages = $this->getImagesFromDb($item_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aNewImages = $this->getImageUpload()->deleteImage($item_id,$aCurrentImages,$img_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updImagesInDb($item_id,$aNewImages);
	}

	/**
	 * Suppression des images d'un élément donné
	 *
	 * @param $item_id
	 * @return boolean
	 */
	public function deleteImages($item_id)
	{
		$aCurrentImages = $this->getImagesFromDb($item_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->getImageUpload()->deleteAllImages($item_id,$aCurrentImages);

		return $this->updImagesInDb($item_id);
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

		$rsItems = $this->getItems(array('visibility'=>2));

		while ($rsItems->fetch())
		{
			$aImages = $rsItems->getImagesArray();
			$aImagesList = array();

			foreach ($aImages as $key=>$image)
			{
				$this->getImageUpload()->buildThumbnails($rsItems->id, $image['img_name']);

				$aImagesList[$key] = array_merge(
					$aImages[$key],
					$this->getImageUpload()->buildImageInfos($rsItems->id, $image['img_name'])
				);
			}

			$this->updImagesInDb($rsItems->id, $aImagesList);
		}
	}

	/**
	 * Récupère la liste des images d'un élément donné
	 *
	 * @param $item_id
	 * @return array
	 */
	public function getImagesFromDb($item_id)
	{
		if (!$this->itemExists($item_id)) {
			$this->error->set(sprintf(__('m_##module_id##_item_%s_not_exists'),$item_id));
			return false;
		}

		$rsItem = $this->getItem($item_id);
		$aImages = $rsItem->images ? unserialize($rsItem->images) : array();

		return $aImages;
	}

	/**
	 * Met à jours la liste des images d'un élément donné
	 *
	 * @param array $item_id
	 * @param $aImages
	 * @return boolean
	 */
	public function updImagesInDb($item_id, $aImages=array())
	{
		if (!$this->itemExists($item_id)) {
			$this->error->set(sprintf(__('m_##module_id##_item_%s_not_exists'),$item_id));
			return false;
		}

		$aImages = !empty($aImages) ? serialize($aImages) : NULL;

		$query =
		'UPDATE '.$this->table.' SET '.
			'images='.(!is_null($aImages) ? '\''.$this->db->escapeStr($aImages).'\'' : 'NULL').' '.
		'WHERE id='.(integer)$item_id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}


	/* Gestion des fichiers des éléments
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
	 * Ajout de fichier(s) à un élément donné
	 *
	 * @param $item_id
	 * @return boolean
	 */
	public function addFiles($item_id)
	{
		$aFiles = $this->getFileUpload()->addFiles($item_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updFilesInDb($item_id,$aFiles);
	}

	/**
	 * Modification de fichier(s) d'un élément donné
	 *
	 * @param $item_id
	 * @return boolean
	 */
	public function updFiles($item_id)
	{
		$aCurrentFiles = $this->getFilesFromDb($item_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aFiles = $this->getFileUpload()->updFiles($item_id,$aCurrentFiles);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updFilesInDb($item_id,$aFiles);
	}

	/**
	 * Suppression d'un fichier donné d'un élément donné
	 *
	 * @param $item_id
	 * @param $file_id
	 * @return boolean
	 */
	public function deleteFile($item_id,$file_id)
	{
		$aCurrentFiles = $this->getFilesFromDb($item_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aNewFiles = $this->getFileUpload()->deleteFile($item_id,$aCurrentFiles,$file_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updFilesInDb($item_id,$aNewFiles);
	}

	/**
	 * Suppression des fichiers d'un élément donné
	 *
	 * @param $item_id
	 * @return boolean
	 */
	public function deleteFiles($item_id)
	{
		$aCurrentFiles = $this->getFilesFromDb($item_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->getFileUpload()->deleteAllFiles($aCurrentFiles);

		return $this->updFilesInDb($item_id);
	}

	/**
	 * Récupère la liste des fichiers d'un élément donné
	 *
	 * @param $item_id
	 * @return array
	 */
	public function getFilesFromDb($item_id)
	{
		if (!$this->itemExists($item_id)) {
			$this->error->set(sprintf(__('m_##module_id##_item_%s_not_exists'),$item_id));
			return false;
		}

		$rsItem = $this->getItem($item_id);
		$aFiles = $rsItem->files ? unserialize($rsItem->files) : array();

		return $aFiles;
	}

	/**
	 * Met à jours la liste des fichiers d'un élément donné
	 *
	 * @param integer $item_id
	 * @param array $aFiles
	 * @return boolean
	 */
	public function updFilesInDb($item_id, $aFiles=array())
	{
		if (!$this->itemExists($item_id)) {
			$this->error->set(sprintf(__('m_##module_id##_item_%s_not_exists'),$item_id));
			return false;
		}

		$aFiles = !empty($aFiles) ? serialize($aFiles) : NULL;

		$query =
		'UPDATE '.$this->table.' SET '.
			'files='.(!is_null($aFiles) ? '\''.$this->db->escapeStr($aFiles).'\'' : 'NULL').' '.
		'WHERE id='.(integer)$item_id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}


	/* Utilitaires
	----------------------------------------------------------*/

	/**
	 * Création du slug d'un élément donné
	 *
	 * @param $id
	 * @return boolean
	 */
	protected function setItemSlug($id)
	{
		if (!$this->itemExists($id)) {
			$this->error->set(sprintf(__('m_##module_id##_item_%s_not_exists'),$id));
			return false;
		}

		$rs = $this->getItem($id);

		$slug = $this->buildItemSlug($rs->title,$rs->slug,$id);

		$query =
		'UPDATE '.$this->table.' SET '.
			'slug=\''.$this->db->escapeStr($slug).'\' '.
		'WHERE id='.(integer)$id;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Construit le slug d'un élément donné
	 *
	 * @param string $title
	 * @param string $url
	 * @param integer $id
	 * @return string
	 */
	protected function buildItemSlug($title,$url,$id)
	{
		if (empty($url)) {
			$url = $title;
		}

		$url = Utilities::strToSlug($url, false);

		# Let's check if URL is taken
		$query =
		'SELECT slug FROM '.$this->table.' '.
		'WHERE slug=\''.$this->db->escapeStr($url).'\' '.
		'AND id <> '.(integer)$id. ' '.
		'ORDER BY slug DESC';

		$rs = $this->db->select($query);

		if (!$rs->isEmpty())
		{
			$query =
			'SELECT slug FROM '.$this->table.' '.
			'WHERE slug LIKE \''.$this->db->escapeStr($url).'%\' '.
			'AND id <> '.(integer)$id. ' '.
			'ORDER BY slug DESC ';

			$rs = $this->db->select($query);
			$a = array();
			while ($rs->fetch()) {
				$a[] = $rs->slug;
			}

			$url = Utilities::getIncrementedString($a, $url, '-');
		}

		# URL is empty?
		if ($url == '') {
			throw new \Exception(__('Empty item URL'));
		}

		return $url;
	}

}
