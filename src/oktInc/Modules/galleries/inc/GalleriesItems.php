<?php
/**
 * @ingroup okt_module_galleries
 * @brief Galleries items management.
 *
 */

use Tao\Images\ImageUpload;
use Tao\Misc\Utilities;

class GalleriesItems
{
	protected $okt;
	protected $error;
	protected $db;

	protected $config;
	protected $triggers;
	protected $tree;

	protected $t_items;
	protected $t_items_locales;

	protected $t_galleries;
	protected $t_galleries_locales;

	/**
	 * Constructor.
	 *
	 * @param object $okt					Instance of oktCore
	 * @param string $t_items 				Name of the items database table
	 * @param string $t_items_locales 		Name of the items locales database table
	 * @param string $t_galleries 			Name of the tree database table
	 * @param string $t_galleries_locales 	Name of the tree locales database table
	 */
	public function __construct($okt, $t_items, $t_items_locales, $t_galleries, $t_galleries_locales)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		$this->t_items = $t_items;
		$this->t_items_locales = $t_items_locales;

		$this->t_galleries = $t_galleries;
		$this->t_galleries_locales = $t_galleries_locales;

		$this->config = $this->okt->galleries->config;
		$this->triggers = $this->okt->galleries->triggers;
		$this->tree = $this->okt->galleries->tree;
	}

	/**
	 * Retourne une liste d'éléments sous forme de recordset selon des paramètres donnés.
	 *
	 * @param array $aParams 			Paramètres de requete
	 * @param boolean $bCountOnly 		Ne renvoi qu'un nombre d'élément
	 * @return integer/GalleriesItemsRecordset
	 */
	public function getItemsRecordset($aParams=array(), $bCountOnly=false)
	{
		$sReqPlus = '';

		if (!empty($aParams['id'])) {
			$sReqPlus .= ' AND i.id='.(integer)$aParams['id'].' ';
		}

		if (!empty($aParams['gallery_id'])) {
			$sReqPlus .= ' AND i.gallery_id='.(integer)$aParams['gallery_id'].' ';
		}

		if (!empty($aParams['slug'])) {
			$sReqPlus .= ' AND il.slug=\''.$this->db->escapeStr($aParams['slug']).'\' ';
		}

		if (isset($aParams['active']))
		{
			if ($aParams['active'] == 0) {
				$sReqPlus .= 'AND i.active=0 ';
			}
			elseif ($aParams['active'] == 1) {
				$sReqPlus .= 'AND i.active=1 ';
			}
			elseif ($aParams['active'] == 2) {
				$sReqPlus .= '';
			}
		}
		else {
			$sReqPlus .= 'AND i.active=1 ';
		}

		if (!empty($aParams['search']))
		{
			$aWords = text::splitWords($aParams['search']);

			if (!empty($aWords))
			{
				foreach ($aWords as $i => $w) {
					$aWords[$i] = 'il.words LIKE \'%'.$this->db->escapeStr($w).'%\' ';
				}
				$sReqPlus .= ' AND '.implode(' AND ',$aWords).' ';
			}
		}

		if ($bCountOnly)
		{
			$sQuery =
			'SELECT COUNT(i.id) AS num_items '.
			$this->getSqlFrom($aParams).
			'WHERE 1 '.$sReqPlus;
		}
		else
		{
			$sQuery =
			'SELECT '.$this->getSelectFields($aParams).' '.
			$this->getSqlFrom($aParams).
			'WHERE 1 '.$sReqPlus;

			$sDirection = 'DESC';
			if (!empty($aParams['order_direction']) && strtoupper($aParams['order_direction']) == 'ASC') {
				$sDirection = 'ASC';
			}

			if (!empty($aParams['order'])) {
				$sQuery .= 'ORDER BY '.$aParams['order'].' '.$sDirection.' ';
			}
			else {
				$sQuery .= 'ORDER BY i.ord ASC ';
			}

			if (!empty($aParams['limit'])) {
				$sQuery .= 'LIMIT '.$aParams['limit'].' ';
			}
		}

		if (($rs = $this->db->select($sQuery, 'GalleriesItemsRecordset')) === false)
		{
			if ($bCountOnly) {
				return 0;
			}
			else {
				$rs = new GalleriesItemsRecordset(array());
				$rs->setCore($this->okt);
				return $rs;
			}
		}

		if ($bCountOnly) {
			return (integer)$rs->num_items;
		}
		else {
			$rs->setCore($this->okt);
			return $rs;
		}
	}

	/**
	 * Retourne la chaine des champs pour le SELECT.
	 *
	 * @return string
	 */
	protected function getSelectFields($aParams)
	{
		$aFields = array(
			'i.id', 'i.user_id', 'i.gallery_id', 'i.active', 'i.created_at', 'i.updated_at', 'i.image', 'i.tpl', 'i.ord',
			'il.language', 'il.title', 'il.subtitle', 'il.title_tag', 'il.title_seo', 'il.slug', 'il.content',
			'il.meta_description', 'il.meta_keywords', 'il.words',
			'gl.title AS gallery_title', 'gl.slug AS gallery_slug', 'g.items_tpl AS gallery_items_tpl'
		);

		$oFields = new ArrayObject($aFields);

		# -- TRIGGER MODULE GALLERIES : getGalleriesItemsSelectFields
		$this->triggers->callTrigger('getGalleriesItemsSelectFields', $oFields);

		return implode(', ', (array)$oFields);
	}

	/**
	 * Retourne la chaine FROM en fonction de paramètres.
	 *
	 * @param array $aParams
	 * @return string
	 */
	protected function getSqlFrom($aParams)
	{
		if (empty($aParams['language']))
		{
			$aFrom = array(
				'FROM '.$this->t_items.' AS i ',
				'LEFT OUTER JOIN '.$this->t_items_locales.' AS il ON i.id=il.item_id ',
				'LEFT OUTER JOIN '.$this->t_galleries.' AS g ON g.id=i.gallery_id ',
				'LEFT OUTER JOIN '.$this->t_galleries_locales.' AS gl ON g.id=gl.gallery_id '
			);
		}
		else
		{
			$aFrom = array(
				'FROM '.$this->t_items.' AS i ',
				'INNER JOIN '.$this->t_items_locales.' AS il ON i.id=il.item_id '.
					'AND il.language=\''.$this->db->escapeStr($aParams['language']).'\' ',
				'LEFT OUTER JOIN '.$this->t_galleries.' AS g ON g.id=i.gallery_id ',
				'LEFT OUTER JOIN '.$this->t_galleries_locales.' AS gl ON g.id=gl.gallery_id '.
					'AND gl.language=\''.$this->db->escapeStr($aParams['language']).'\' '
			);
		}

		$oFrom = new ArrayObject($aFrom);

		# -- TRIGGER MODULE GALLERIES : getGalleriesItemsSqlFrom
		$this->triggers->callTrigger('getGalleriesItemsSqlFrom', $oFrom);

		return implode(' ', (array)$oFrom);
	}

	/**
	 * Retourne une liste d'éléments sous forme de recordset selon des paramètres donnés
	 * et les prépares en vue d'un affichage.
	 *
	 * @param array $aParams 		Paramètres de requete
	 * @return object GalleriesItemsRecordset
	 */
	public function getItems($aParams=array())
	{
		$rs = $this->getItemsRecordset($aParams);

		$this->prepareItems($rs);

		return $rs;
	}

	/**
	 * Retourne un compte du nombre d'éléments selon des paramètres donnés.
	 *
	 * @param array $aParams 		Paramètres de requete
	 * @return integer
	 */
	public function getItemsCount($aParams=array())
	{
		return $this->getItemsRecordset($aParams, true);
	}

	/**
	 * Retourne, sous forme de recordset, un élément donné dans la langue de l'utilisateur
	 * et le prépare en vue d'un affichage.
	 *
	 * @param integer $mItemId 		Identifiant numérique ou slug de l'élément.
	 * @param integer $iActive
	 * @return object GalleriesItemsRecordset
	 */
	public function getItem($mItemId, $iActive=2)
	{
		$aParams = array(
			'language' => $this->okt->user->language,
			'active' => $iActive
		);

		if (Utilities::isInt($mItemId)) {
			$aParams['id'] = $mItemId;
		}
		else {
			$aParams['slug'] = $mItemId;
		}

		$rs = $this->getItemsRecordset($aParams);

		$this->prepareItem($rs);

		return $rs;
	}

	/**
	 * Indique si un élément donné existe.
	 *
	 * @param $iItemId
	 * @return boolean
	 */
	public function itemExists($iItemId)
	{
		if (empty($iItemId) || $this->getItemsRecordset(array('id' => $iItemId, 'active' => 2))->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Retourne les localisations d'un élément donné.
	 *
	 * @param integer $iItemId
	 * @return recordset
	 */
	public function getItemI18n($iItemId)
	{
		$query =
		'SELECT * FROM '.$this->t_items_locales.' '.
		'WHERE item_id='.(integer)$iItemId;

		if (($rs = $this->db->select($query)) === false) {
			$rs = new recordset(array());
			return $rs;
		}

		return $rs;
	}

	/**
	 * Formatage des données d'un GalleriesItemsRecordset en vue d'un affichage d'une liste d'éléments.
	 *
	 * @param GalleriesItemsRecordset $rs
	 * @return void
	 */
	public function prepareItems(GalleriesItemsRecordset $rs)
	{
		$iCountLine = 0;
		while ($rs->fetch())
		{
			# odd/even
			$rs->odd_even = ($iCountLine%2 == 0 ? 'even' : 'odd');
			$iCountLine++;

			# formatages génériques
			$this->commonPreparation($rs);
		}
	}

	/**
	 * Formatage des données d'un GalleriesItemsRecordset en vue d'un affichage élément.
	 *
	 * @param GalleriesItemsRecordset $rs
	 * @return void
	 */
	public function prepareItem(GalleriesItemsRecordset $rs)
	{
		# formatages génériques
		$this->commonPreparation($rs);
	}

	/**
	 * Formatages des données d'un GalleriesItemsRecordset communs aux listes et aux éléments.
	 *
	 * @param GalleriesItemsRecordset $rs
	 * @return void
	 */
	protected function commonPreparation(GalleriesItemsRecordset $rs)
	{
		# url élément
		$rs->url = $rs->getItemUrl();

		# url galerie
		$rs->gallery_url = $rs->getGalleryUrl();

		# récupération des images
		$rs->image = $rs->getImagesInfo();

		# contenu
		if (!$this->config->enable_rte) {
			$rs->content = Utilities::nlToP($rs->content);
		}

		$rs->content = $this->okt->performCommonContentReplacements($rs->content);
	}

	/**
	 * Créer une instance de cursor pour un élément et la retourne.
	 *
	 * @param array $aItemData
	 * @return object cursor
	 */
	public function openItemCursor($aItemData=null)
	{
		$oCursor = $this->db->openCursor($this->t_items);

		if (!empty($aItemData))
		{
			foreach ($aItemData as $k=>$v) {
				$oCursor->$k = $v;
			}
		}

		return $oCursor;
	}

	/**
	 * Ajout/modification des textes internationnalisés d'un élément.
	 *
	 * @param integer $iItemId
	 * @param array $aItemLocalesData
	 */
	protected function setItemI18n($iItemId, $aItemLocalesData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (empty($aItemLocalesData[$aLanguage['code']]['title'])) {
				continue;
			}

			$oCursor = $this->db->openCursor($this->t_items_locales);

			$oCursor->item_id = $iItemId;

			$oCursor->language = $aLanguage['code'];

			foreach ($aItemLocalesData[$aLanguage['code']] as $k=>$v) {
				$oCursor->$k = $v;
			}

			$oCursor->content = $this->okt->HTMLfilter($oCursor->content);

			$oCursor->words = implode(' ',array_unique(text::splitWords($oCursor->title.' '.$oCursor->subtitle.' '.$oCursor->content.' '.$oCursor->author.' '.$oCursor->place)));

			$oCursor->meta_description = html::clean($oCursor->meta_description);

			$oCursor->meta_keywords = html::clean($oCursor->meta_keywords);

			$oCursor->insertUpdate();

			$this->setItemSlug($iItemId, $aLanguage['code']);
		}
	}

	/**
	 * Création du slug d'un élément donné dans une langue donnée.
	 *
	 * @param integer $iItemId
	 * @param string $sLanguage
	 * @return boolean
	 */
	protected function setItemSlug($iItemId, $sLanguage)
	{
		$rsItem = $this->getItems(array(
			'id' => $iItemId,
			'language' => $sLanguage,
			'active' => 2
		));

		if ($rsItem->isEmpty()) {
			throw new Exception(sprintf(__('m_galleries_error_item_%s_doesnt_exist'), $iItemId));
		}

		if (empty($rsItem->slug)) {
			$sUrl = $rsItem->title;
		}
		else {
			$sUrl = $rsItem->slug;
		}

		$sUrl = Utilities::strToSlug($sUrl, false);

		# Let's check if URL is taken…
		$rsTakenSlugs = $this->db->select(
			'SELECT slug FROM '.$this->t_items_locales.' '.
			'WHERE slug=\''.$this->db->escapeStr($sUrl).'\' '.
			'AND item_id <> '.(integer)$iItemId.' '.
			'AND language=\''.$this->db->escapeStr($sLanguage).'\' '.
			'ORDER BY slug DESC'
		);

		if (!$rsTakenSlugs->isEmpty())
		{
			$rsCurrentSlugs = $this->db->select(
				'SELECT slug FROM '.$this->t_items_locales.' '.
				'WHERE slug LIKE \''.$this->db->escapeStr($sUrl).'%\' '.
				'AND item_id <> '.(integer)$iItemId.' '.
				'AND language=\''.$this->db->escapeStr($sLanguage).'\' '.
				'ORDER BY slug DESC '
			);

			$a = array();
			while ($rsCurrentSlugs->fetch()) {
				$a[] = $rsCurrentSlugs->slug;
			}

			$sUrl = Utilities::getIncrementedString($a, $sUrl, '-');
		}


		$sQuery =
		'UPDATE '.$this->t_items_locales.' SET '.
		'slug=\''.$this->db->escapeStr($sUrl).'\' '.
		'WHERE item_id='.(integer)$iItemId. ' '.
		'AND language=\''.$this->db->escapeStr($sLanguage).'\' ';

		if (!$this->db->execute($sQuery)) {
			return false;
		}

		return true;
	}

	/**
	 * Ajout d'un élément.
	 *
	 * @param cursor $oCursor
	 * @param array $aItemLocalesData
	 * @return integer
	 */
	public function addItem($oCursor, $aItemLocalesData)
	{
		$sDate = date('Y-m-d H:i:s');
		$oCursor->created_at = $sDate;
		$oCursor->updated_at = $sDate;

		if (!$oCursor->insert()) {
			throw new Exception('Unable to insert item into database');
		}

		# récupération de l'ID
		$iNewId = $this->db->getLastID();

		# ajout des textes internationnalisés
		$this->setItemI18n($iNewId, $aItemLocalesData);

		# ajout des images
		if ($this->config->images['enable'] && $this->addImage($iNewId) === false) {
			throw new Exception('Unable to insert images item');
		}

		return $iNewId;
	}

	/**
	 * Mise à jour d'un élément.
	 *
	 * @param cursor $oCursor
	 * @param array $aItemLocalesData
	 * @return boolean
	 */
	public function updItem($oCursor, $aItemLocalesData)
	{
		if (!$this->itemExists($oCursor->id)) {
			throw new Exception(sprintf(__('m_galleries_error_item_%s_doesnt_exist'), $oCursor->id));
		}

		# modification dans la DB
		$oCursor->updated_at = date('Y-m-d H:i:s');

		if (!$oCursor->update('WHERE id='.(integer)$oCursor->id.' ')) {
			throw new Exception('Unable to update item into database');
		}

		# modification des images
		if ($this->config->images['enable'] && $this->updImage($oCursor->id) === false) {
			throw new Exception('Unable to update images item');
		}

		# modification des textes internationnalisés
		$this->setItemI18n($oCursor->id, $aItemLocalesData);

		return true;
	}

	/**
	 * Vérifie les données envoyées par formulaire.
	 *
	 * @param array $aItemData 				Le tableau de données de l'élément.
	 * @return boolean
	 */
	public function checkPostData($aItemData)
	{
		$bHasAtLeastOneTitle = false;
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (empty($aItemData['locales'][$aLanguage['code']]['title'])) {
				continue;
			}
			else {
				$bHasAtLeastOneTitle = true;
				break;
			}
		}

		if (!$bHasAtLeastOneTitle)
		{
			if ($this->okt->languages->unique) {
				$this->error->set(__('m_galleries_item_must_enter_title'));
			}
			else {
				$this->error->set(__('m_galleries_item_must_enter_at_least_one_title'));
			}
		}

		if (empty($aItemData['item']['gallery_id'])) {
			$this->error->set(__('m_galleries_item_must_choose_gallery'));
		}

		# -- TRIGGER MODULE GALLERIES : checkItemData
		$this->triggers->callTrigger('checkItemData', $aItemData);


		return $this->error->isEmpty();
	}

	/**
	 * Switch le statut de visibilité d'un élément donné.
	 *
	 * @param integer $iItemId
	 * @return boolean
	 */
	public function switchItemStatus($iItemId)
	{
		if (!$this->itemExists($iItemId)) {
			throw new Exception(sprintf(__('m_galleries_error_item_%s_doesnt_exist'), $iItemId));
		}

		$sQuery =
		'UPDATE '.$this->t_items.' SET '.
			'updated_at=NOW(), '.
			'active = 1-active '.
		'WHERE id='.(integer)$iItemId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to update item in database.');
		}

		return true;
	}

	/**
	 * Définit le statut de visibilité d'un élément donné.
	 *
	 * @param integer $iItemId
	 * @param integer $status
	 * @return boolean
	 */
	public function setItemStatus($iItemId, $status)
	{
		if (!$this->itemExists($iItemId)) {
			throw new Exception(sprintf(__('m_galleries_error_item_%s_doesnt_exist'), $iItemId));
		}

		$sQuery =
		'UPDATE '.$this->t_items.' SET '.
			'updated_at=NOW(), '.
			'active = '.($status == 1 ? 1 : 0).' '.
		'WHERE id='.(integer)$iItemId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to update item in database.');
		}

		return true;
	}

	/**
	 * Définit la position d'un élément donné.
	 *
	 * @param integer $iItemId
	 * @param integer $iPosition
	 * @return boolean
	 */
	public function setItemPosition($iItemId, $iPosition)
	{
		if (!$this->itemExists($iItemId)) {
			throw new Exception(sprintf(__('m_galleries_error_item_%s_doesnt_exist'), $iItemId));
		}

		$query =
		'UPDATE '.$this->t_items.' SET '.
			'ord='.(integer)$iPosition.' '.
		'WHERE id='.(integer)$iItemId;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Suppression d'un élément donné.
	 *
	 * @param integer $iItemId
	 * @return boolean
	 */
	public function deleteItem($iItemId)
	{
		if (!$this->itemExists($iItemId)) {
			throw new Exception(sprintf(__('m_galleries_error_item_%s_doesnt_exist'), $iItemId));
		}

		if ($this->deleteImages($iItemId) === false) {
			throw new Exception('Unable to delete images item.');
		}

		$sQuery =
		'DELETE FROM '.$this->t_items.' '.
		'WHERE id='.(integer)$iItemId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to remove item from database.');
		}

		$this->db->optimize($this->t_items);

		$sQuery =
		'DELETE FROM '.$this->t_items_locales.' '.
		'WHERE item_id='.(integer)$iItemId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to remove item locales from database.');
		}

		$this->db->optimize($this->t_items_locales);

		return true;
	}


	/* Gestion des images des éléments
	----------------------------------------------------------*/

	/**
	 * Retourne une instance de la classe oktImageUpload
	 *
	 * @return object
	 */
	public function getImageUploadInstance()
	{
		$o = new ImageUpload($this->okt, $this->config->images);
		$o->setConfig(array(
			'upload_dir' => $this->okt->galleries->upload_dir.'img/items/',
			'upload_url' => $this->okt->galleries->upload_url.'img/items/'
		));

		return $o;
	}

	/**
	 * Ajout de l'image à un élément donné.
	 *
	 * @param $iItemId
	 * @return boolean
	 */
	public function addImage($iItemId)
	{
		$aImages = $this->getImageUploadInstance()->addImages($iItemId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$image = !empty($aImages[1]) ? $aImages[1] : null;

		return $this->updImages($iItemId, $image);
	}

	/**
	 * Modification de l'image d'un élément donné.
	 *
	 * @param $iItemId
	 * @return boolean
	 */
	public function updImage($iItemId)
	{
		$aCurrentImages = $this->getImages($iItemId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aImages = $this->getImageUploadInstance()->updImages($iItemId, $aCurrentImages);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$image = !empty($aImages[1]) ? $aImages[1] : null;

		return $this->updImages($iItemId, $image);
	}

	/**
	 * Suppression d'une image donnée d'un élément donné.
	 *
	 * @param $iItemId
	 * @param $img_id
	 * @return boolean
	 */
	public function deleteImage($iItemId, $img_id)
	{
		$aCurrentImages = $this->getImages($iItemId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aNewImages = $this->getImageUploadInstance()->deleteImage($iItemId, $aCurrentImages, $img_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$image = !empty($aNewImages[1]) ? $aNewImages[1] : null;

		return $this->updImages($iItemId, $image);
	}

	/**
	 * Suppression des images d'un élément donné.
	 *
	 * @param $iItemId
	 * @return boolean
	 */
	public function deleteImages($iItemId)
	{
		$aCurrentImages = $this->getImages($iItemId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->getImageUploadInstance()->deleteAllImages($iItemId, $aCurrentImages);

		return $this->updImages($iItemId);
	}

	/**
	 * Régénération de toutes les miniatures des images
	 *
	 * @return void
	 */
	public function regenMinImages($gallery_id=null)
	{
		@ini_set('memory_limit',-1);
		set_time_limit(0);

		$aParams = array(
			'active' => 2,
		);

		if (!is_null($gallery_id)) {
			$aParams['gallery_id'] = $gallery_id;
		}

		$rsItems = $this->getItemsRecordset($aParams);

		while ($rsItems->fetch())
		{
			$aImages = $rsItems->getImagesArray();
			$aImagesList = array();

			if (!empty($aImages['img_name']))
			{
				$this->getImageUploadInstance()->buildThumbnails($rsItems->id, $aImages['img_name']);

				$aImagesList = array_merge(
						$aImages,
						$this->getImageUploadInstance()->buildImageInfos($rsItems->id, $aImages['img_name'])
				);
			}

			$this->updImages($rsItems->id, $aImagesList);
		}

		return true;
	}

	/**
	 * Récupère l'image d'un élément donné.
	 *
	 * @param $iItemId
	 * @return array
	 */
	public function getImages($iItemId)
	{
		if (!$this->itemExists($iItemId)) {
			throw new Exception(sprintf(__('m_galleries_error_item_%s_doesnt_exist'), $oCursor->id));
		}

		$rsItem = $this->getItemsRecordset(array(
			'id' => $iItemId
		));

		if ($rsItem->image)
		{
			$aItemImages = unserialize($rsItem->image);
			return array(1=>$aItemImages);
		}
		else {
			return array();
		}
	}

	/**
	 * Met à jours l'image d'un élément donné.
	 *
	 * @param integer $iItemId
	 * @param arraz $aImage
	 * @return boolean
	 */
	public function updImages($iItemId, $aImage=null)
	{
		if (!$this->itemExists($iItemId)) {
			throw new Exception(sprintf(__('m_galleries_error_item_%s_doesnt_exist'), $oCursor->id));
		}

		$aImage = !empty($aImage) ? serialize($aImage) : NULL;

		$query =
		'UPDATE '.$this->t_items.' SET '.
		'image='.(!is_null($aImage) ? '\''.$this->db->escapeStr($aImage).'\'' : 'NULL').' '.
		'WHERE id='.(integer)$iItemId;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}


}
