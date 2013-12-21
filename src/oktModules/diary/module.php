<?php
/**
 * @ingroup okt_module_diary
 * @brief
 *
 */

use Tao\Admin\Menu as AdminMenu;
use Tao\Admin\Page;
use Tao\Database\MySqli;
use Tao\Images\ImageUpload;
use Tao\Misc\FileUpload;
use Tao\Misc\Utilities as util;
use Tao\Modules\Module;
use Tao\Routing\Route;

class module_diary extends Module
{
	public $config = null;
	protected $locales = null;
	public $filters = null;

	public $upload_dir;
	public $upload_url;

	protected $table;

	protected function prepend()
	{
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'DiaryController' => __DIR__.'/inc/DiaryController.php',
			'DiaryHelpers' => __DIR__.'/inc/DiaryHelpers.php',
			'DiaryRecordset' => __DIR__.'/inc/DiaryRecordset.php',
			'DiaryMonthlyCalendar' => __DIR__.'/inc/DiaryMonthlyCalendar.php',
			'DiaryFilters' => __DIR__.'/inc/DiaryFilters.php'
		));

		# permissions
		$this->okt->addPermGroup('diary', __('m_diary_perm_group'));
			$this->okt->addPerm('diary', __('m_diary_perm_global'), 'diary');
			$this->okt->addPerm('diary_add', __('m_diary_perm_add'), 'diary');
			$this->okt->addPerm('diary_remove', __('m_diary_perm_remove'), 'diary');
			$this->okt->addPerm('diary_display', __('m_diary_perm_display'), 'diary');
			$this->okt->addPerm('diary_config', __('m_diary_perm_config'), 'diary');

		# tables
		$this->table = $this->db->prefix.'mod_diary';

		# configuration
		$this->config = $this->okt->newConfig('conf_diary');

		# répertoire upload
		$this->upload_dir = OKT_UPLOAD_PATH.'/diary/';
		$this->upload_url = OKT_UPLOAD_URL.'/diary/';
	}

	protected function prepend_admin()
	{
		# on ajoutent un élément au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->diarySubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);

			$this->okt->page->mainMenu->add(
				$this->getName(),
				'module.php?m=diary',
				$this->bCurrentlyInUse,
				20,
				$this->okt->checkPerm('diary'),
				null,
				$this->okt->page->diarySubMenu,
				$this->url().'/icon.png'
			);

				$this->okt->page->diarySubMenu->add(
					__('m_diary_menu_management'),
					'module.php?m=diary&amp;action=index',
					$this->bCurrentlyInUse && (!$this->okt->page->action || $this->okt->page->action === 'index' || $this->okt->page->action === 'edit'),
					10
				);

				$this->okt->page->diarySubMenu->add(
					__('m_diary_menu_add_event'),
					'module.php?m=diary&amp;action=add',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'add'),
					20,
					$this->okt->checkPerm('diary_add')
				);

				$this->okt->page->diarySubMenu->add(
					__('m_diary_menu_display'),
					'module.php?m=diary&amp;action=display',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'display'),
					200,
					$this->okt->checkPerm('diary_display')
				);

				$this->okt->page->diarySubMenu->add(
					__('m_diary_menu_config'),
					'module.php?m=diary&amp;action=config',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'config'),
					300,
					$this->okt->checkPerm('diary_config')
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
		if ($this->filters === null || !($this->filters instanceof DiaryFilters)) {
			$this->filters = new DiaryFilters($this->okt, $this->config, $part);
		}
	}


	/* Gestion des éléments
	----------------------------------------------------------*/

	/**
	 * Retourne une liste d'évènements selon des paramètres donnés.
	 *
	 * @param	array	params			Paramètres de requete
	 * @param	boolean	count_only		Ne renvoi qu'un nombre d'éléments
	 * @return  object recordset/integer
	 */
	public function getEvents($aParams=array(), $bCountOnly=false)
	{
		$sReqPlus = '';

		if (!empty($aParams['id'])) {
			$sReqPlus .= ' AND id='.(integer)$aParams['id'].' ';
		}

		if (!empty($aParams['slug'])) {
			$sReqPlus .= ' AND slug=\''.$this->db->escapeStr($aParams['slug']).'\' ';
		}

		if (!empty($aParams['after'])) {
			$sReqPlus .= ' AND date>=\''.$this->db->escapeStr($aParams['after']).'\' ';
		}

		if (!empty($aParams['before'])) {
			$sReqPlus .= ' AND date<=\''.$this->db->escapeStr($aParams['before']).'\' ';
		}

		if (!empty($aParams['between'])) {
			$sReqPlus .= ' AND date>=\''.$this->db->escapeStr($aParams['between']).'\' '.
				' AND date_end<=\''.$this->db->escapeStr($aParams['between']).'\' ';
		}

		if (!empty($aParams['disponibility']) || !empty($aParams['disponibility'])) {
			$sReqPlus .= ' AND disponibility='.(integer)$aParams['disponibility'].' ';
		}

		if (isset($aParams['visibility']))
		{
			if ($aParams['visibility'] == 0) {
				$sReqPlus .= 'AND visibility=0 ';
			}
			elseif ($aParams['visibility'] == 1) {
				$sReqPlus .= 'AND visibility=1 ';
			}
			elseif ($aParams['visibility'] == 2) {
				$sReqPlus .= '';
			}
		}
		else {
			$sReqPlus .= 'AND visibility=1 ';
		}

		if ($bCountOnly)
		{
			$query =
			'SELECT COUNT(id) AS num_events '.
			'FROM '.$this->table.' '.
			'WHERE 1 '.
			$sReqPlus;
		}
		else {
			$query =
			'SELECT '.
				'id, visibility, title, date, date_end, slug, title_tag, title_seo, description, disponibility, color, '.
				'created_at, updated_at, images, files, meta_description, meta_keywords '.
			'FROM '.$this->table.'  '.
			'WHERE 1 '.
			$sReqPlus;

			if (!empty($aParams['limit'])) {
				$query .= 'LIMIT '.$aParams['limit'].' ';
			}
		}

		if (($rs = $this->db->select($query,'DiaryRecordset')) === false)
		{
			if ($bCountOnly) {
				return 0;
			}
			else {
				$rs = new DiaryRecordset(array());
				$rs->setCore($this->okt);
				return $rs;
			}
		}

		if ($bCountOnly) {
			return (integer)$rs->num_events;
		}
		else {
			$rs->setCore($this->okt);
			return $rs;
		}
	}

	/**
	 * Retourne un évènement donné sous forme de recordset.
	 *
	 * @param integer $iEventId
	 * @param integer $visibility
	 * @return recordset
	 */
	public function getEvent($iEventId, $visibility=2)
	{
		return $this->getEvents(array(
			'id' => $iEventId,
			'visibility' => $visibility
		));
	}

	/**
	 * Teste l'existence d'un évènement.
	 *
	 * @param $iEventId
	 * @return boolean
	 */
	public function eventExists($iEventId)
	{
		if (empty($iEventId) || $this->getEvent($iEventId)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Retourne les dates d'évènements dans un interval donné.
	 *
	 * @param string $sAfter
	 * @param string $sBefore
	 * @param integer $iVisibility
	 * @return array
	 */
	public function getDatesEventsByInterval($sAfter, $sBefore, $iVisibility=2)
	{
		$rsEvents = $this->getEvents(array(
			'after' => $sAfter,
			'before' => $sBefore,
			'visibility' => $iVisibility
		));

		$aDates = array();
		while ($rsEvents->fetch())
		{
			if (!empty($rsEvents->date_end))
			{
				# @TODO : PHP 5.3
				//$days = new DatePeriod(new DateTime($rsEvents->date), DateInterval::createFromDateString('1 day'), new DateTime($rsEvents->date_end));

				$oBeginDate = new DateTime($rsEvents->date);
				$oEndDate = new DateTime(date("Y-m-d", strtotime("+1 day", strtotime($rsEvents->date_end))));

				while ($oBeginDate < $oEndDate)
				{
					$aDates[$oBeginDate->format('Y-m-d')][] = array(
						'id' => $rsEvents->id,
						'title' => $rsEvents->title,
						'color' => $rsEvents->color,
						'disponibility' => $rsEvents->disponibility,
						'url' => $rsEvents->getEventUrl()
					);

					$oBeginDate->modify('+1 day');
				}
			}
			else
			{
				$aDates[$rsEvents->date][] = array(
					'id' => $rsEvents->id,
					'title' => $rsEvents->title,
						'color' => $rsEvents->color,
					'disponibility' => $rsEvents->disponibility,
					'url' => $rsEvents->getEventUrl()
				);
			}
		}

		return $aDates;
	}

	/**
	 * Créer une instance de cursor et la retourne
	 *
	 * @param array $data
	 * @return object cursor
	 */
	public function openCursor($data=null)
	{
		$oCursor = $this->db->openCursor($this->table);

		if (!empty($data) && is_array($data))
		{
			foreach ($data as $k=>$v) {
				$oCursor->$k = $v;
			}
		}

		return $oCursor;
	}

	/**
	 * Ajout d'un évènement
	 *
	 * @param cursor $oCursor
	 * @return integer
	 */
	public function addEvent($oCursor)
	{
		$date = date('Y-m-d H:i:s');
		$oCursor->created_at = $date;
		$oCursor->updated_at = $date;

		$oCursor->date = MySqli::formatDateTime($oCursor->date);

		if ($oCursor->date_end != '') {
			$oCursor->date_end = MySqli::formatDateTime($oCursor->date_end);
		}
		else {
			$oCursor->date_end = null;
		}

		$oCursor->description = $this->okt->HTMLfilter($oCursor->description);

		$oCursor->disponibility = $oCursor->disponibility;

		$oCursor->meta_description = html::clean($oCursor->meta_description);
		$oCursor->meta_keywords = html::clean($oCursor->meta_keywords);

		if (!$oCursor->insert()) {
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
		if ($this->setEventSlug($iNewId) === false) {
			return false;
		}

		return $iNewId;
	}

	/**
	 * Mise à jour d'un évènement.
	 *
	 * @param integer $iEventId
	 * @param cursor $oCursor
	 * @return boolean
	 */
	public function updEvent($iEventId, $oCursor)
	{
		if (!$this->eventExists($iEventId)) {
			$this->error->set(sprintf(__('m_diary_event_%s_not_exists'), $iEventId));
			return false;
		}

		$oCursor->updated_at = date('Y-m-d H:i:s');

		$oCursor->date = MySqli::formatDateTime($oCursor->date);

		if ($oCursor->date_end != '') {
			$oCursor->date_end = MySqli::formatDateTime($oCursor->date_end);
		}
		else {
			$oCursor->date_end = null;
		}

		$oCursor->description = $this->okt->HTMLfilter($oCursor->description);

		$oCursor->disponibility = $oCursor->disponibility;

		$oCursor->meta_description = html::clean($oCursor->meta_description);
		$oCursor->meta_keywords = html::clean($oCursor->meta_keywords);

		if (!$oCursor->update('WHERE id='.(integer)$iEventId.' ')) {
			return false;
		}

		# modification des images
		if ($this->config->images['enable'] && $this->updImages($iEventId) === false) {
			return false;
		}

		# modification des fichiers
		if ($this->config->files['enable'] && $this->updFiles($iEventId) === false) {
			return false;
		}

		# modification du slug
		if ($this->setEventSlug($iEventId) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Suppression d'un évènement donné
	 *
	 * @param integer $iEventId
	 * @return boolean
	 */
	public function delEvent($iEventId)
	{
		if (!$this->eventExists($iEventId)) {
			$this->error->set(sprintf(__('m_diary_event_%s_not_exists'), $iEventId));
			return false;
		}

		if ($this->deleteImages($iEventId) === false) {
			return false;
		}

		if ($this->deleteFiles($iEventId) === false) {
			return false;
		}

		$query =
		'DELETE FROM '.$this->table.' '.
		'WHERE id='.(integer)$iEventId;

		if (!$this->db->execute($query)) {
			return false;
		}

		$this->db->optimize($this->table);

		return true;
	}

	/**
	 * Switch le statut de visibilité d'un évènement donné.
	 *
	 * @param integer $iEventId
	 * @return boolean
	 */
	public function switchEventStatus($iEventId)
	{
		if (!$this->eventExists($iEventId)) {
			$this->error->set(sprintf(__('m_diary_event_%s_not_exists'), $iEventId));
			return false;
		}

		$query =
		'UPDATE '.$this->table.' SET '.
			'updated_at=NOW(), '.
			'visibility = 1-visibility '.
		'WHERE id='.(integer)$iEventId;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Définit le statut de visibilité d'un évènement donné.
	 *
	 * @param integer $iEventId
	 * @param integer $visibility
	 * @return boolean
	 */
	public function setEventStatus($iEventId, $visibility)
	{
		if (!$this->eventExists($iEventId)) {
			$this->error->set(sprintf(__('m_diary_event_%s_not_exists'), $iEventId));
			return false;
		}

		$query =
		'UPDATE '.$this->table.' SET '.
			'updated_at=NOW(), '.
			'visibility = '.($visibility == 1 ? 1 : 0).' '.
		'WHERE id='.(integer)$iEventId;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Vérifie les données envoyées en fonction de la configuration.
	 *
	 * @param array $aData
	 * @return boolean
	 */
	public function checkPostData($aData)
	{
		if (empty($aData['title'])) {
			$this->error->set('Vous devez saisir un titre.');
		}

		if (empty($aData['date'])) {
			$this->error->set('Vous devez saisir une date.');
		}
		elseif (!empty($aData['date_end']) && (strtotime($aData['date_end']) <= strtotime($aData['date']))) {
			$this->error->set('Vous devez saisir une date de fin postérieure à la date.');
		}

		if ($this->config->fields['color'] == 2 && empty($aData['color'])) {
			$this->error->set('Vous devez choisir une couleur.');
		}

		if ($this->config->fields['disponibility'] == 2 && empty($aData['disponibility'])) {
			$this->error->set('Vous devez choisir une disponibilité.');
		}

		return $this->error->isEmpty();
	}

	/**
	 * Retourne la liste des disponibilités.
	 *
	 * @param boolean $flip
	 * @return array
	 */
	public static function getDisponibility($flip=false)
	{
		$a = array(
			1 => __('m_diary_disponibility_1'),
			2 => __('m_diary_disponibility_2'),
		);

		if ($flip) {
			$a = array_flip($a);
		}

		return $a;
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
	 * @param integer $iEventId
	 * @return boolean
	 */
	public function addImages($iEventId)
	{
		$aImages = $this->getImageUpload()->addImages($iEventId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updImagesInDb($iEventId,$aImages);
	}

	/**
	 * Modification d'image(s) d'un élément donné
	 *
	 * @param integer $iEventId
	 * @return boolean
	 */
	public function updImages($iEventId)
	{
		$aCurrentImages = $this->getImagesFromDb($iEventId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aImages = $this->getImageUpload()->updImages($iEventId,$aCurrentImages);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updImagesInDb($iEventId,$aImages);
	}

	/**
	 * Suppression d'une image donnée d'un élément donné
	 *
	 * @param integer $iEventId
	 * @param integer $img_id
	 * @return boolean
	 */
	public function deleteImage($iEventId,$img_id)
	{
		$aCurrentImages = $this->getImagesFromDb($iEventId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aNewImages = $this->getImageUpload()->deleteImage($iEventId,$aCurrentImages,$img_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updImagesInDb($iEventId,$aNewImages);
	}

	/**
	 * Suppression des images d'un élément donné
	 *
	 * @param integer $iEventId
	 * @return boolean
	 */
	public function deleteImages($iEventId)
	{
		$aCurrentImages = $this->getImagesFromDb($iEventId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->getImageUpload()->deleteAllImages($iEventId,$aCurrentImages);

		return $this->updImagesInDb($iEventId);
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

		$rsEvents = $this->getEvents(array('visibility'=>2));

		while ($rsEvents->fetch())
		{
			$aImages = $rsEvents->getImagesArray();
			$aImagesList = array();

			foreach ($aImages as $key=>$image)
			{
				$this->getImageUpload()->buildThumbnails($rsEvents->id, $image['img_name']);

				$aImagesList[$key] = array_merge(
					$aImages[$key],
					$this->getImageUpload()->buildImageInfos($rsEvents->id, $image['img_name'])
				);
			}

			$this->updImagesInDb($rsEvents->id, $aImagesList);
		}
	}

	/**
	 * Récupère la liste des images d'un élément donné
	 *
	 * @param integer $iEventId
	 * @return array
	 */
	public function getImagesFromDb($iEventId)
	{
		if (!$this->eventExists($iEventId)) {
			$this->error->set(sprintf(__('m_diary_event_%s_not_exists'), $iEventId));
			return false;
		}

		$rsEvents = $this->getEvent($iEventId);
		$aImages = $rsEvents->images ? unserialize($rsEvents->images) : array();

		return $aImages;
	}

	/**
	 * Met à jours la liste des images d'un élément donné
	 *
	 * @param array $iEventId
	 * @param $aImages
	 * @return boolean
	 */
	public function updImagesInDb($iEventId, $aImages=array())
	{
		if (!$this->eventExists($iEventId)) {
			$this->error->set(sprintf(__('m_diary_event_%s_not_exists'), $iEventId));
			return false;
		}

		$aImages = !empty($aImages) ? serialize($aImages) : NULL;

		$query =
		'UPDATE '.$this->table.' SET '.
			'images='.(!is_null($aImages) ? '\''.$this->db->escapeStr($aImages).'\'' : 'NULL').' '.
		'WHERE id='.(integer)$iEventId;

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
	 * @param integer $iEventId
	 * @return boolean
	 */
	public function addFiles($iEventId)
	{
		$aFiles = $this->getFileUpload()->addFiles($iEventId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updFilesInDb($iEventId,$aFiles);
	}

	/**
	 * Modification de fichier(s) d'un élément donné
	 *
	 * @param integer $iEventId
	 * @return boolean
	 */
	public function updFiles($iEventId)
	{
		$aCurrentFiles = $this->getFilesFromDb($iEventId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aFiles = $this->getFileUpload()->updFiles($iEventId,$aCurrentFiles);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updFilesInDb($iEventId,$aFiles);
	}

	/**
	 * Suppression d'un fichier donné d'un élément donné
	 *
	 * @param integer $iEventId
	 * @param integer $file_id
	 * @return boolean
	 */
	public function deleteFile($iEventId,$file_id)
	{
		$aCurrentFiles = $this->getFilesFromDb($iEventId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$aNewFiles = $this->getFileUpload()->deleteFile($iEventId,$aCurrentFiles,$file_id);

		if (!$this->error->isEmpty()) {
			return false;
		}

		return $this->updFilesInDb($iEventId,$aNewFiles);
	}

	/**
	 * Suppression des fichiers d'un élément donné
	 *
	 * @param integer $iEventId
	 * @return boolean
	 */
	public function deleteFiles($iEventId)
	{
		$aCurrentFiles = $this->getFilesFromDb($iEventId);

		if (!$this->error->isEmpty()) {
			return false;
		}

		$this->getFileUpload()->deleteAllFiles($aCurrentFiles);

		return $this->updFilesInDb($iEventId);
	}

	/**
	 * Récupère la liste des fichiers d'un élément donné
	 *
	 * @param integer $iEventId
	 * @return array
	 */
	public function getFilesFromDb($iEventId)
	{
		if (!$this->eventExists($iEventId)) {
			$this->error->set(sprintf(__('m_diary_event_%s_not_exists'), $iEventId));
			return false;
		}

		$rsEvents = $this->getEvent($iEventId);
		$aFiles = $rsEvents->files ? unserialize($rsEvents->files) : array();

		return $aFiles;
	}

	/**
	 * Met à jours la liste des fichiers d'un élément donné
	 *
	 * @param integer $iEventId
	 * @param array $aFiles
	 * @return boolean
	 */
	public function updFilesInDb($iEventId, $aFiles=array())
	{
		if (!$this->eventExists($iEventId)) {
			$this->error->set(sprintf(__('m_diary_event_%s_not_exists'), $iEventId));
			return false;
		}

		$aFiles = !empty($aFiles) ? serialize($aFiles) : NULL;

		$query =
		'UPDATE '.$this->table.' SET '.
			'files='.(!is_null($aFiles) ? '\''.$this->db->escapeStr($aFiles).'\'' : 'NULL').' '.
		'WHERE id='.(integer)$iEventId;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}


	/* Utilitaires
	----------------------------------------------------------*/

	/**
	 * Création du slug d'un évènement donné.
	 *
	 * @param integer $iEventId
	 * @return boolean
	 */
	protected function setEventSlug($iEventId)
	{
		if (!$this->eventExists($iEventId)) {
			$this->error->set(sprintf(__('m_diary_event_%s_not_exists'), $iEventId));
			return false;
		}

		$rs = $this->getEvent($iEventId);

		$slug = $this->buildEventSlug($rs->title,$rs->slug,$iEventId);

		$query =
		'UPDATE '.$this->table.' SET '.
			'slug=\''.$this->db->escapeStr($slug).'\' '.
		'WHERE id='.(integer)$iEventId;

		if (!$this->db->execute($query)) {
			return false;
		}

		return true;
	}

	/**
	 * Construit le slug d'un évènement donné.
	 *
	 * @param string $title
	 * @param string $url
	 * @param integer $iEventId
	 * @return string
	 */
	protected function buildEventSlug($title,$url,$iEventId)
	{
		if (empty($url)) {
			$url = $title;
		}

		$url = util::strToSlug($url, false);

		# Let's check if URL is taken
		$query =
		'SELECT slug FROM '.$this->table.' '.
		'WHERE slug=\''.$this->db->escapeStr($url).'\' '.
		'AND id <> '.(integer)$iEventId. ' '.
		'ORDER BY slug DESC';

		$rs = $this->db->select($query);

		if (!$rs->isEmpty())
		{
			$query =
			'SELECT slug FROM '.$this->table.' '.
			'WHERE slug LIKE \''.$this->db->escapeStr($url).'%\' '.
			'AND id <> '.(integer)$iEventId. ' '.
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
			throw new Exception(__('Empty event URL'));
		}

		return $url;
	}

}
