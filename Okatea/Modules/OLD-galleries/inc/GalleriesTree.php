<?php
/**
 * @ingroup okt_module_galleries
 * @brief Galleries categories management.
 *
 */
use Okatea\Tao\Images\ImageUpload;
use Okatea\Tao\Html\Modifiers;
use Okatea\Tao\Misc\NestedTreei18n;
use Okatea\Tao\Misc\Utilities;

class GalleriesTree extends NestedTreei18n
{

	protected $t_items;

	protected $t_items_locales;

	protected $t_galleries;

	protected $t_galleries_locales;

	/**
	 * Constructor.
	 *
	 * @param object $okt
	 *        	of oktCore
	 * @param string $t_items
	 *        	Name of the items database table
	 * @param string $t_items_locales
	 *        	Name of the items locales database table
	 * @param string $t_galleries
	 *        	Name of the tree database table
	 * @param string $t_galleries_locales
	 *        	Name of the tree locales database table
	 * @param string $idField
	 *        	Name of the primary key ID field
	 * @param string $parentField
	 *        	Name of the parent ID field
	 * @param string $sSortField
	 *        	Name of the field to sort data
	 * @param string $sJoinField
	 *        	Name of the join field
	 * @param string $sLanguageField
	 *        	Name of the language field
	 * @param array $addFields
	 *        	Others fields to be selecteds
	 * @param array $addLocalesFields
	 *        	Others localized fields
	 */
	public function __construct($okt, $t_items, $t_items_locales, $t_galleries, $t_galleries_locales, $idField, $parentField, $sSortField, $sJoinField, $sLanguageField, $addFields, $addLocalesFields)
	{
		parent::__construct($okt, $t_galleries, $t_galleries_locales, $idField, $parentField, $sSortField, $sJoinField, $sLanguageField, $addFields, $addLocalesFields);
		
		# raccourcis des noms de tables
		$this->t_items = $t_items;
		$this->t_items_locales = $t_items_locales;
		
		$this->t_galleries = $t_galleries;
		$this->t_galleries_locales = $t_galleries_locales;
	}

	/**
	 * Retourne une liste de galeries selon des paramètres donnés.
	 *
	 * @param array $aParams
	 *        	Paramètres de requete
	 * @param boolean $bCountOnly
	 *        	Ne renvoi qu'un compte de galeries
	 * @return object GalleriesRecordset/integer
	 */
	public function getGalleries($aParams = array(), $bCountOnly = false)
	{
		$sReqPlus = '';
		
		$with_count = isset($aParams['with_count']) ? (boolean) $aParams['with_count'] : false;
		
		if (! empty($aParams['id']))
		{
			$sReqPlus .= 'AND r.id=' . (integer) $aParams['id'] . ' ';
			$with_count = false;
		}
		
		if (! empty($aParams['slug']))
		{
			$sReqPlus .= 'AND rl.slug=\'' . $this->db->escapeStr($aParams['slug']) . '\' ';
			$with_count = false;
		}
		
		if (! empty($aParams['language']))
		{
			$sReqPlus .= 'AND rl.language=\'' . $this->db->escapeStr($aParams['language']) . '\' ';
		}
		
		if (isset($aParams['parent_id']))
		{
			$sReqPlus .= 'AND r.parent_id=' . (integer) $aParams['parent_id'] . ' ';
			$with_count = false;
		}
		
		if (isset($aParams['active']))
		{
			if ($aParams['active'] == 0)
			{
				$sReqPlus .= 'AND r.active=0 ';
				$with_count = false;
			}
			elseif ($aParams['active'] == 1)
			{
				$sReqPlus .= 'AND r.active=1 ';
				$with_count = false;
			}
			elseif ($aParams['active'] == 2)
			{
				$sReqPlus .= '';
			}
		}
		else
		{
			$sReqPlus .= 'AND r.active=1 ';
			$with_count = false;
		}
		
		if ($bCountOnly)
		{
			$sQuery = 'SELECT COUNT(r.id) AS num_galleries ' . 'FROM ' . $this->t_galleries . ' AS r ' . 'LEFT JOIN ' . $this->t_galleries_locales . ' AS rl ON r.id=rl.gallery_id ' . 'LEFT JOIN ' . $this->t_items . ' AS p ON r.id=p.gallery_id ' . 'WHERE 1 ' . $sReqPlus . ' ';
		}
		else
		{
			$sQuery = 'SELECT r.*, rl.*, COUNT(p.id) AS num_items ' . 'FROM ' . $this->t_galleries . ' AS r ' . 'LEFT JOIN ' . $this->t_galleries_locales . ' AS rl ON r.id=rl.gallery_id ' . 'LEFT JOIN ' . $this->t_items . ' AS p ON r.id=p.gallery_id ' . 'WHERE 1 ' . $sReqPlus . ' ' . 'GROUP BY r.id ' . 'ORDER BY nleft asc ';
			
			if (! empty($aParams['limit']))
			{
				$sQuery .= 'LIMIT ' . $aParams['limit'] . ' ';
			}
		}
		
		if (($rs = $this->db->select($sQuery, 'GalleriesRecordset')) === false)
		{
			$rs = new GalleriesRecordset(array());
			$rs->setCore($this->okt);
			return $rs;
		}
		
		if ($bCountOnly)
		{
			return (integer) $rs->num_galleries;
		}
		else
		{
			if ($with_count)
			{
				$aData = array();
				$aStack = array();
				$iLevel = 0;
				
				foreach (array_reverse($rs->getData()) as $aGalleryData)
				{
					$iNumItems = (integer) $aGalleryData['num_items'];
					
					if ($aGalleryData['level'] > $iLevel)
					{
						$iNumTotal = $iNumItems;
						$aStack[$aGalleryData['level']] = $iNumItems;
					}
					elseif ($aGalleryData['level'] == $iLevel)
					{
						$iNumTotal = $iNumItems;
						$aStack[$aGalleryData['level']] += $iNumItems;
					}
					else
					{
						$iNumTotal = $aStack[$aGalleryData['level'] + 1] + $iNumItems;
						
						if (isset($aStack[$aGalleryData['level']]))
						{
							$aStack[$aGalleryData['level']] += $iNumTotal;
						}
						else
						{
							$aStack[$aGalleryData['level']] = $iNumTotal;
						}
						
						unset($aStack[$aGalleryData['level'] + 1]);
					}
					
					$iLevel = $aGalleryData['level'];
					
					$aGalleryData['num_items'] = $iNumItems;
					$aGalleryData['num_total'] = $iNumTotal;
					
					array_unshift($aData, $aGalleryData);
				}
				
				$rs = new GalleriesRecordset($aData);
				$rs->setCore($this->okt);
				return $rs;
			}
			else
			{
				$rs->setCore($this->okt);
				return $rs;
			}
		}
	}

	/**
	 * Retourne une galerie donnée sous forme de recordset.
	 *
	 * @param integer $iGalleryId        	
	 * @param integer $iActive        	
	 * @return object GalleriesRecordset
	 */
	public function getGallery($iGalleryId, $iActive = 2)
	{
		return $this->getGalleries(array(
			'id' => $iGalleryId,
			'active' => $iActive
		));
	}

	/**
	 * Indique si une galerie donnée existe.
	 *
	 * @param
	 *        	$iGalleryId
	 * @return boolean
	 */
	public function galleryExists($iGalleryId)
	{
		if ($this->getGallery($iGalleryId)->isEmpty())
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Retourne les localisations d'une galerie donnée.
	 *
	 * @param integer $iGalleryId        	
	 * @return recordset
	 */
	public function getGalleryL10n($iGalleryId)
	{
		$query = 'SELECT * ' . 'FROM ' . $this->t_galleries_locales . ' ' . 'WHERE gallery_id=' . (integer) $iGalleryId;
		
		if (($rs = $this->db->select($query)) === false)
		{
			$rs = new recordset(array());
			return $rs;
		}
		
		return $rs;
	}

	/**
	 * Formatage des données d'un GalleriesRecordset en vue d'un affichage d'une liste de galeries.
	 *
	 * @param GalleriesRecordset $rs        	
	 * @return void
	 */
	public function prepareGalleries(GalleriesRecordset $rs)
	{
		$iCountLine = 0;
		while ($rs->fetch())
		{
			# odd/even
			$rs->odd_even = ($iCountLine % 2 == 0 ? 'even' : 'odd');
			$iCountLine ++;
			
			# formatages génériques
			$this->commonPreparation($rs);
		}
	}

	/**
	 * Formatage des données d'un GalleriesRecordset en vue d'un affichage d'une galerie.
	 *
	 * @param GalleriesRecordset $rs        	
	 * @return void
	 */
	public function prepareGallery(GalleriesRecordset $rs)
	{
		# formatages génériques
		$this->commonPreparation($rs);
	}

	/**
	 * Formatages des données d'un GalleriesRecordset communs aux listes et aux éléments.
	 *
	 * @param GalleriesRecordset $rs        	
	 * @return void
	 */
	protected function commonPreparation(GalleriesRecordset $rs)
	{
		# url page
		$rs->url = $rs->getGalleryUrl();
		
		# récupération des images
		$rs->image = $rs->getImagesInfo();
		
		# contenu
		if (! $this->okt->galleries->config->enable_gal_rte)
		{
			$rs->content = Modifiers::nlToP($rs->content);
		}
		
		$rs->content = $this->okt->performCommonContentReplacements($rs->content);
	}

	/**
	 * Créer une instance de cursor pour une galerie et la retourne.
	 *
	 * @param ArrayObject $aGalleryData        	
	 * @return object cursor
	 */
	public function openGalleryCursor($aGalleryData = null)
	{
		$oCursor = $this->db->openCursor($this->t_galleries);
		
		if (! empty($aGalleryData))
		{
			foreach ($aGalleryData as $k => $v)
			{
				$oCursor->$k = $v;
			}
		}
		
		return $oCursor;
	}

	/**
	 * Ajout/modification des textes internationnalisés d'une galerie.
	 *
	 * @param integer $iGalleryId        	
	 * @param ArrayObject $aGalleryLocalesData        	
	 */
	protected function setGalleryL10n($iGalleryId, $aGalleryLocalesData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$oCursor = $this->db->openCursor($this->t_galleries_locales);
			
			$oCursor->gallery_id = $iGalleryId;
			
			$oCursor->language = $aLanguage['code'];
			
			foreach ($aGalleryLocalesData[$aLanguage['code']] as $k => $v)
			{
				$oCursor->$k = $v;
			}
			
			$oCursor->content = $this->okt->HTMLfilter($oCursor->content);
			
			$oCursor->meta_description = strip_tags($oCursor->meta_description);
			
			$oCursor->meta_keywords = strip_tags($oCursor->meta_keywords);
			
			if (! $oCursor->insertUpdate())
			{
				throw new Exception('Unable to insert gallery locales in database for ' . $aLanguage['code'] . ' language.');
			}
			
			if (! $this->setGallerySlug($iGalleryId, $aLanguage['code']))
			{
				throw new Exception('Unable to insert gallery slug in database for ' . $aLanguage['code'] . ' language.');
			}
		}
	}

	/**
	 * Création du slug d'une galerie donnée dans une langue donnée.
	 *
	 * @param integer $iGalleryId        	
	 * @param string $sLanguage        	
	 * @return boolean
	 */
	protected function setGallerySlug($iGalleryId, $sLanguage)
	{
		$rsGallery = $this->getGalleries(array(
			'id' => $iGalleryId,
			'language' => $sLanguage,
			'active' => 2
		));
		
		if ($rsGallery->isEmpty())
		{
			throw new Exception(sprintf(__('m_galleries_error_gallery_%s_doesnt_exist'), $iGalleryId));
		}
		
		if (empty($rsGallery->slug))
		{
			$rsParent = $this->getGalleries(array(
				'id' => $rsGallery->parent_id,
				'language' => $sLanguage,
				'active' => 2
			));
			
			$sSlug = $rsParent->slug . '/' . $rsGallery->title;
		}
		else
		{
			$sSlug = $rsGallery->slug;
		}
		
		$sSlug = Modifiers::strToSlug($sSlug, true);
		
		# Let's check if URL is taken…
		$query = 'SELECT slug FROM ' . $this->t_galleries_locales . ' ' . 'WHERE slug=\'' . $this->db->escapeStr($sSlug) . '\' ' . 'AND gallery_id <> ' . (integer) $iGalleryId . ' ' . 'AND language=\'' . $this->db->escapeStr($sLanguage) . '\' ' . 'ORDER BY slug DESC';
		
		$rsTakenSlugs = $this->db->select($query);
		
		if (! $rsTakenSlugs->isEmpty())
		{
			$query = 'SELECT slug FROM ' . $this->t_galleries_locales . ' ' . 'WHERE slug LIKE \'' . $this->db->escapeStr($sSlug) . '%\' ' . 'AND gallery_id <> ' . (integer) $iGalleryId . ' ' . 'AND language=\'' . $this->db->escapeStr($sLanguage) . '\' ' . 'ORDER BY slug DESC ';
			
			$rsCurrentSlugs = $this->db->select($query);
			$a = array();
			while ($rsCurrentSlugs->fetch())
			{
				$a[] = $rsCurrentSlugs->slug;
			}
			
			$sSlug = Utilities::getIncrementedString($a, $sSlug, '-');
		}
		
		$query = 'UPDATE ' . $this->t_galleries_locales . ' SET ' . 'slug=\'' . $this->db->escapeStr($sSlug) . '\' ' . 'WHERE gallery_id=' . (integer) $iGalleryId . ' ' . 'AND language=\'' . $this->db->escapeStr($sLanguage) . '\' ';
		
		if (! $this->db->execute($query))
		{
			throw new Exception('Unable to update gallery in database.');
		}
		
		return true;
	}

	/**
	 * Vérifie les données envoyées par formulaire.
	 *
	 * @param ArrayObject $aGalleryData        	
	 * @param ArrayObject $aGalleryLocalesData        	
	 * @return boolean
	 */
	public function checkPostData($aGalleryData, $aGalleryLocalesData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (empty($aGalleryLocalesData[$aLanguage['code']]['title']))
			{
				if ($this->okt->languages->unique)
				{
					$this->error->set(__('m_galleries_error_you_must_enter_title'));
				}
				else
				{
					$this->error->set(sprintf(__('m_galleries_error_you_must_enter_title_in_%s'), $aLanguage['title']));
				}
			}
		}
		
		return $this->error->isEmpty();
	}

	/**
	 * Ajout d'une galerie.
	 *
	 * @param cursor $oCursor        	
	 * @param ArrayObject $aGalleryLocalesData        	
	 * @return integer
	 */
	public function addGallery($oCursor, $aGalleryLocalesData)
	{
		$iMaxOrder = $this->numChildren($oCursor->parent_id);
		$oCursor->ord = $iMaxOrder + 1;
		
		if ($oCursor->parent_id > 0)
		{
			$rsParent = $this->getGallery($oCursor->parent_id);
			
			if ($rsParent->active == 0)
			{
				$oCursor->active = 0;
			}
		}
		
		$sDate = date('Y-m-d H:i:s');
		$oCursor->created_at = $sDate;
		$oCursor->updated_at = $sDate;
		
		if (! $oCursor->insert())
		{
			throw new Exception('Unable to insert gallery in database.');
		}
		
		$iNewId = $this->db->getLastID();
		
		$this->setGalleryL10n($iNewId, $aGalleryLocalesData);
		
		$this->rebuild();
		
		if (! $this->addImage($iNewId))
		{
			return false;
		}
		
		return $iNewId;
	}

	/**
	 * Modification d'une galerie.
	 *
	 * @param cursor $oCursor        	
	 * @param ArrayObject $aGalleryLocalesData        	
	 * @return boolean
	 */
	public function updGallery($oCursor, $aGalleryLocalesData)
	{
		if (! $this->galleryExists($oCursor->id))
		{
			throw new Exception(sprintf(__('m_galleries_error_gallery_%s_doesnt_exist'), $oCursor->id));
		}
		
		if ($oCursor->parent_id > 0)
		{
			if ($this->isDescendantOf($oCursor->parent_id, $oCursor->id))
			{
				throw new Exception(__('m_galleries_error_gallery_in_children'));
			}
			
			$rsParent = $this->getGallery($oCursor->parent_id);
			
			if ($rsParent->active == 0)
			{
				$oCursor->active = 0;
			}
		}
		
		$oCursor->updated_at = date('Y-m-d H:i:s');
		
		if (! $oCursor->update('WHERE id=' . (integer) $oCursor->id . ' '))
		{
			throw new Exception('Unable to update gallery in database.');
		}
		
		if ($oCursor->active == 0)
		{
			$rsChildrens = $this->getDescendants($oCursor->id);
			while ($rsChildrens->fetch())
			{
				$this->setGalleryStatus($rsChildrens->id, 0);
			}
		}
		
		$this->setGalleryL10n($oCursor->id, $aGalleryLocalesData);
		
		$this->rebuild();
		
		if ($this->updImage($oCursor->id) === false)
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Définit la position d'une galerie donnée.
	 *
	 * @param integer $iGalleryId        	
	 * @param integer $iOrder        	
	 * @return boolean
	 */
	public function setGalleryPosition($iGalleryId, $iOrder)
	{
		if (! $this->galleryExists($iGalleryId))
		{
			throw new Exception(sprintf(__('m_galleries_error_gallery_%s_doesnt_exist'), $iGalleryId));
		}
		
		$sQuery = 'UPDATE ' . $this->t_galleries . ' SET ' . 'ord=' . (integer) $iOrder . ' ' . 'WHERE id=' . (integer) $iGalleryId;
		
		if (! $this->db->execute($sQuery))
		{
			throw new Exception('Unable to update gallery in database.');
		}
		
		return true;
	}

	/**
	 * Switch le statut de visibilité d'une galerie donnée.
	 *
	 * @param integer $iGalleryId        	
	 * @return boolean
	 */
	public function switchGalleryStatus($iGalleryId)
	{
		$rsGallery = $this->getGallery($iGalleryId);
		
		if ($rsGallery->isEmpty())
		{
			throw new Exception(sprintf(__('m_galleries_error_gallery_%s_doesnt_exist'), $iGalleryId));
		}
		
		$iStatus = $rsGallery->active ? 0 : 1;
		
		if ($iStatus == 0)
		{
			$rsChildrens = $this->getDescendants($iGalleryId);
			
			while ($rsChildrens->fetch())
			{
				$this->setGalleryStatus($rsChildrens->id, 0);
			}
		}
		
		if ($rsGallery->parent_id != 0)
		{
			$rsParent = $this->getGallery($rsGallery->parent_id);
			
			if ($rsParent->active == 0)
			{
				throw new Exception(__('m_galleries_error_parent_gallery_hidden'));
			}
		}
		
		return $this->setGalleryStatus($iGalleryId, $iStatus);
	}

	/**
	 * Définit le statut de visibilité d'une galerie donnée.
	 *
	 * @param integer $iGalleryId        	
	 * @param integer $iStatus        	
	 * @return boolean
	 */
	public function setGalleryStatus($iGalleryId, $iStatus)
	{
		if (! $this->galleryExists($iGalleryId))
		{
			throw new Exception(sprintf(__('m_galleries_error_gallery_%s_doesnt_exist'), $iGalleryId));
		}
		
		$sQuery = 'UPDATE ' . $this->t_galleries . ' SET ' . 'active=' . (integer) $iStatus . ' ' . 'WHERE id=' . (integer) $iGalleryId;
		
		if (! $this->db->execute($sQuery))
		{
			throw new Exception('Unable to update gallery in database.');
		}
		
		return true;
	}

	/**
	 * Suppression d'une galerie.
	 *
	 * @param integer $iGalleryId        	
	 * @return boolean
	 */
	public function deleteGallery($iGalleryId)
	{
		$rsGallery = $this->getGallery($iGalleryId);
		
		if ($rsGallery->isEmpty())
		{
			throw new Exception(sprintf(__('m_galleries_error_gallery_%s_doesnt_exist'), $iGalleryId));
		}
		
		$rsChildrens = $this->getChildren($iGalleryId);
		while ($rsChildrens->fetch())
		{
			$this->setParentId($rsChildrens->id, $rsGallery->parent_id);
		}
		
		$this->deleteImages($iGalleryId);
		
		$sQuery = 'DELETE FROM ' . $this->t_galleries . ' ' . 'WHERE id=' . (integer) $iGalleryId;
		
		if (! $this->db->execute($sQuery))
		{
			throw new Exception('Unable to remove gallery from database.');
		}
		
		$this->db->optimize($this->t_galleries);
		
		$query = 'DELETE FROM ' . $this->t_galleries_locales . ' ' . 'WHERE gallery_id=' . (integer) $iGalleryId;
		
		if (! $this->db->execute($query))
		{
			throw new Exception('Unable to remove gallery locales from database.');
		}
		
		$this->db->optimize($this->t_galleries_locales);
		
		$this->rebuild();
		
		return true;
	}

	/**
	 * Définit le parent d'une rubrique donnée.
	 *
	 * @param integer $iGalleryId        	
	 * @param integer $iParentId        	
	 * @return boolean
	 */
	public function setParentId($iGalleryId, $iParentId)
	{
		if (! $this->galleryExists($iGalleryId))
		{
			throw new Exception(sprintf(__('m_galleries_error_gallery_%s_doesnt_exist'), $iGalleryId));
		}
		
		$sQuery = 'UPDATE ' . $this->t_galleries . ' SET ' . 'parent_id=' . (integer) $iParentId . ' ' . 'WHERE id=' . (integer) $iGalleryId;
		
		if (! $this->db->execute($sQuery))
		{
			throw new Exception('Unable to update parent ID gallery in database.');
		}
		
		$this->rebuild();
		
		return true;
	}
	
	/* Gestion des images des galeries
	----------------------------------------------------------*/
	
	/**
	 * Retourne une instance de la classe oktImageUpload
	 *
	 * @return object
	 */
	public function getImageUploadInstance()
	{
		$o = new ImageUpload($this->okt, $this->okt->galleries->config->images_gal);
		$o->setConfig(array(
			'upload_dir' => $this->okt->galleries->upload_dir . '/img/galleries',
			'upload_url' => $this->okt->galleries->upload_url . '/img/galleries'
		));
		
		return $o;
	}

	/**
	 * Ajout de l'image à une galerie donnée
	 *
	 * @param
	 *        	$iGalleryId
	 * @return boolean
	 */
	public function addImage($iGalleryId)
	{
		$aImages = $this->getImageUploadInstance()->addImages($iGalleryId);
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$image = ! empty($aImages[1]) ? $aImages[1] : null;
		
		return $this->updImages($iGalleryId, $image);
	}

	/**
	 * Modification de l'image d'une galerie donnée
	 *
	 * @param
	 *        	$iGalleryId
	 * @return boolean
	 */
	public function updImage($iGalleryId)
	{
		$aCurrentImages = $this->getImages($iGalleryId);
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$aImages = $this->getImageUploadInstance()->updImages($iGalleryId, $aCurrentImages);
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$image = ! empty($aImages[1]) ? $aImages[1] : null;
		
		return $this->updImages($iGalleryId, $image);
	}

	/**
	 * Suppression d'une image donnée d'une galerie donnée
	 *
	 * @param
	 *        	$iGalleryId
	 * @param
	 *        	$img_id
	 * @return boolean
	 */
	public function deleteImage($iGalleryId, $img_id)
	{
		$aCurrentImages = $this->getImages($iGalleryId);
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$aNewImages = $this->getImageUploadInstance()->deleteImage($iGalleryId, $aCurrentImages, $img_id);
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$image = ! empty($aNewImages[1]) ? $aNewImages[1] : null;
		
		return $this->updImages($iGalleryId, $image);
	}

	/**
	 * Suppression des images d'une galerie donnée
	 *
	 * @param
	 *        	$iGalleryId
	 * @return boolean
	 */
	public function deleteImages($iGalleryId)
	{
		$aCurrentImages = $this->getImages($iGalleryId);
		
		if (! $this->error->isEmpty())
		{
			return false;
		}
		
		$this->getImageUploadInstance()->deleteAllImages($iGalleryId, $aCurrentImages);
		
		return $this->updImages($iGalleryId);
	}

	/**
	 * Régénération de toutes les miniatures des images
	 *
	 * @return void
	 */
	public function regenMinImages()
	{
		@ini_set('memory_limit', - 1);
		set_time_limit(0);
		
		$rsGalleries = $this->getGalleries(array(
			'active' => 2
		));
		
		while ($rsGalleries->fetch())
		{
			$aImages = $rsGalleries->getImagesArray();
			$aImagesList = array();
			
			if (! empty($aImages['img_name']))
			{
				$this->getImageUploadInstance()->buildThumbnails($rsGalleries->id, $aImages['img_name']);
				
				$aImagesList = array_merge($aImages, $this->getImageUploadInstance()->buildImageInfos($rsGalleries->id, $aImages['img_name']));
			}
			
			$this->updImages($rsGalleries->id, $aImagesList);
		}
		
		return true;
	}

	/**
	 * Récupère l'image d'une galerie donnée
	 *
	 * @param
	 *        	$iGalleryId
	 * @return array
	 */
	public function getImages($iGalleryId)
	{
		if (! $this->galleryExists($iGalleryId))
		{
			$this->error->set(sprintf(__('m_galleries_error_gallery_%s_doesnt_exist'), $iGalleryId));
			return false;
		}
		
		$rsGallery = $this->getGallery($iGalleryId);
		
		if ($rsGallery->image)
		{
			$aItemImages = unserialize($rsGallery->image);
			return array(
				1 => $aItemImages
			);
		}
		else
		{
			return array();
		}
	}

	/**
	 * Met à jours l'image d'une galerie donnée
	 *
	 * @param integer $iGalleryId        	
	 * @param arraz $aImage        	
	 * @return boolean
	 */
	public function updImages($iGalleryId, $aImage = null)
	{
		if (! $this->galleryExists($iGalleryId))
		{
			$this->error->set(sprintf(__('m_galleries_error_gallery_%s_doesnt_exist'), $iGalleryId));
			return false;
		}
		
		$aImage = ! empty($aImage) ? serialize($aImage) : NULL;
		
		$query = 'UPDATE ' . $this->t_galleries . ' SET ' . 'image=' . (! is_null($aImage) ? '\'' . $this->db->escapeStr($aImage) . '\'' : 'NULL') . ' ' . 'WHERE id=' . (integer) $iGalleryId;
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		return true;
	}
}
