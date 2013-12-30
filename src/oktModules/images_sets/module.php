<?php
/**
 * @ingroup okt_module_images_sets
 * @brief
 *
 */

use Tao\Modules\Module;

class module_images_sets extends Module
{
	public $upload_dir;
	public $upload_url;

	protected $t_images_sets;

	//public $config;

	protected function prepend()
	{
		# autoload
	//	$this->okt->autoloader->addClassMap(array(
	//		'imagesSetsController' => __DIR__.'/inc/class.images_sets.controller.php'
	//	));

		# t_images_setss
		$this->t_images_sets = $this->db->prefix.'mod_images_sets';

		# config
		//$this->config = $this->okt->newConfig('conf_images_sets');
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->configSubMenu->add(
				__('Images sets'),
				'module.php?m=images_sets&amp;action=index',
				$this->bCurrentlyInUse && (!$this->okt->page->action || $this->okt->page->action === 'index'),
				42,
				$this->okt->checkPerm('is_superadmin'),
				null
			);
		}
	}

	protected function prepend_public()
	{
	}

	/**
	 * Retourne une liste de jeux d'images selon des paramètres donnés.
	 *
	 * @param	array	params			Paramètres de requete
	 * @return  object recordset
	 */
	public function getImagesSets($aParams=array())
	{
		$reqPlus = '';

		if (!empty($aParams['id'])) {
			$reqPlus .= ' AND id='.(integer)$aParams['id'].' ';
		}

		$query =
		'SELECT '.
			'id, title, number, width, height, resize_type, images, tpl '.
		'FROM '.$this->t_images_sets.'  '.
		'WHERE 1 '.
		$reqPlus;

		if (!empty($aParams['limit'])) {
			$query .= 'LIMIT '.$aParams['limit'].' ';
		}

		if (($rs = $this->db->select($query)) === false) {
			return new recordset(array());
		}

		return $rs;
	}

	/**
	 * Retourne un jeu d'images donné sous forme de recordset.
	 *
	 * @param integer $iSetId
	 * @return recordset
	 */
	public function getImagesSet($iSetId)
	{
		return $this->getImagesSets(array(
			'id' => $iSetId,
		));
	}

	/**
	 * Teste l'existence d'un jeu d'images.
	 *
	 * @param $iSetId
	 * @return boolean
	 */
	public function imagesSetExists($iSetId)
	{
		if (empty($iSetId) || $this->getEvent($iSetId)->isEmpty()) {
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
		$oCursor = $this->db->openCursor($this->t_images_sets);

		if (!empty($data) && is_array($data))
		{
			foreach ($data as $k=>$v) {
				$oCursor->$k = $v;
			}
		}

		return $oCursor;
	}

	/**
	 * Ajout d'un jeu d'images.
	 *
	 * @param cursor $oCursor
	 * @return integer
	 */
	public function addImagesSet($oCursor)
	{
		if (!$oCursor->insert()) {
			throw new Exception('Unable to insert images set into database');
		}

		# récupération de l'ID
		$iNewId = $this->db->getLastID();

		# ajout des images
		if ($this->addImages($iNewId) === false) {
			throw new Exception('Unable to insert images of images set');
		}

		return $iNewId;
	}

	/**
	 * Mise à jour d'un jeu d'images.
	 *
	 * @param integer $iSetId
	 * @param cursor $oCursor
	 * @return boolean
	 */
	public function updImagesSet($iSetId, $oCursor)
	{
		if (!$this->imagesSetExists($iSetId)) {
			throw new Exception(sprintf(__('m_images_sets_%s_not_exists'), $iSetId));
		}

		if (!$oCursor->update('WHERE id='.(integer)$iSetId.' ')) {
			throw new Exception('Unable to update images set into database');
		}

		# modification des images
		if ($this->updImages($iSetId) === false) {
			throw new Exception('Unable to update images of images set');
		}

		return true;
	}


}
