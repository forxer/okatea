<?php
/**
 * @ingroup okt_module_news
 * @brief News categories management.
 *
 */

use Tao\Database\Recordset;
use Tao\Misc\NestedTreei18n;
use Tao\Misc\Utilities as util;

class NewsCategories extends NestedTreei18n
{
	protected $t_news;
	protected $t_news_locales;
	protected $t_categories;
	protected $t_categories_locales;

	public function __construct($okt, $t_news, $t_news_locales, $t_categories, $t_categories_locales, $idField, $parentField, $sSortField, $sJoinField, $sLanguageField, $addFields, $addLocalesFields)
	{
		parent::__construct($okt, $t_categories, $t_categories_locales, $idField, $parentField, $sSortField, $sJoinField, $sLanguageField, $addFields, $addLocalesFields);

		# raccourcis des noms de tables
		$this->t_news = $t_news;
		$this->t_news_locales = $t_news_locales;
		$this->t_categories = $t_categories;
		$this->t_categories_locales = $t_categories_locales;
	}

	/**
	 * Retourne une liste de rubriques selon des paramètres donnés.
	 *
	 * @param array $aParams Paramètres de requete
	 * @param boolean $bCountOnly Ne renvoi qu'un compte de rubrique
	 * @return object recordset/integer
	 */
	public function getCategories($aParams=array(), $bCountOnly=false)
	{
		$sReqPlus = '';

		$with_count = isset($aParams['with_count']) ? (boolean)$aParams['with_count'] : false;

		if (!empty($aParams['id'])) {
			$sReqPlus .= 'AND r.id='.(integer)$aParams['id'].' ';
			$with_count = false;
		}

		if (!empty($aParams['slug'])) {
			$sReqPlus .= 'AND rl.slug=\''.$this->db->escapeStr($aParams['slug']).'\' ';
			$with_count = false;
		}

		if (!empty($aParams['language'])) {
			$sReqPlus .= 'AND rl.language=\''.$this->db->escapeStr($aParams['language']).'\' ';
		}

		if (!empty($aParams['parent_id'])) {
		$sReqPlus .= 'AND r.parent_id='.(integer)$aParams['parent_id'].' ';
		}


		if (isset($aParams['active']))
		{
			if ($aParams['active'] == 0) {
				$sReqPlus .= 'AND r.active=0 ';
				$with_count = false;
			}
			elseif ($aParams['active'] == 1) {
				$sReqPlus .= 'AND r.active=1 ';
				$with_count = false;
			}
			elseif ($aParams['active'] == 2) {
				$sReqPlus .= '';
			}
		}
		else {
			$sReqPlus .= 'AND r.active=1 ';
			$with_count = false;
		}

		if ($bCountOnly)
		{
			$sQuery =
			'SELECT COUNT(r.id) AS num_rubriques '.
			'FROM '.$this->t_categories.' AS r '.
				'LEFT JOIN '.$this->t_categories_locales.' AS rl ON r.id=rl.category_id '.
				'LEFT JOIN '.$this->t_news.' AS p ON r.id=p.category_id '.
			'WHERE 1 '.
			$sReqPlus.' ';
		}
		else {
			$sQuery =
			'SELECT r.*, rl.*, COUNT(p.id) AS num_posts '.
			'FROM '.$this->t_categories.' AS r '.
				'LEFT JOIN '.$this->t_categories_locales.' AS rl ON r.id=rl.category_id '.
				'LEFT JOIN '.$this->t_news.' AS p ON r.id=p.category_id '.
			'WHERE 1 '.
			$sReqPlus.' '.
			'GROUP BY r.id '.
			'ORDER BY nleft asc ';

			if (!empty($aParams['limit'])) {
				$sQuery .= 'LIMIT '.$aParams['limit'].' ';
			}
		}

		if (($rs = $this->db->select($sQuery)) === false) {
			return new recordset(array());
		}

		if ($bCountOnly) {
			return (integer)$rs->num_rubriques;
		}
		else
		{
			if ($with_count)
			{
				$aData = array();
				$stack = array();
				$level = 0;
				foreach(array_reverse($rs->getData()) as $rubrique)
				{
					$num_posts = (integer) $rubrique['num_posts'];

					if ($rubrique['level'] > $level) {
						$nb_total = $num_posts;
						$stack[$rubrique['level']] = $num_posts;
					} elseif ($rubrique['level'] == $level) {
						$nb_total = $num_posts;
						$stack[$rubrique['level']] += $num_posts;
					} else {
						$nb_total = $stack[$rubrique['level']+1] + $num_posts;
						if (isset($stack[$rubrique['level']])) {
							$stack[$rubrique['level']] += $nb_total;
						} else {
							$stack[$rubrique['level']] = $nb_total;
						}
						unset($stack[$rubrique['level']+1]);
					}

					$level = $rubrique['level'];

					$rubrique['num_posts'] = $num_posts;
					$rubrique['num_total'] = $nb_total;

					array_unshift($aData,$rubrique);
				}

				return new recordset($aData);
			}
			else {
				return $rs;
			}
		}
	}

	/**
	 * Retourne une rubrique donnée sous forme de recordset.
	 *
	 * @param integer $iCategoryId
	 * @param integer $iActive
	 * @return object recordset
	 */
	public function getCategory($iCategoryId,$iActive=2)
	{
		return $this->getCategories(array('id'=>$iCategoryId,'active'=>$iActive));
	}

	/**
	 * Indique si une rubrique donnée existe.
	 *
	 * @param $iCategoryId
	 * @return boolean
	 */
	public function categoryExists($iCategoryId)
	{
		if ($this->getCategory($iCategoryId)->isEmpty()) {
			return false;
		}

		return true;
	}

	/**
	 * Retourne les localisations d'une rubrique donnée.
	 *
	 * @param integer $iCategoryId
	 * @return recordset
	 */
	public function getCategoryI18n($iCategoryId)
	{
		$query =
		'SELECT * '.
		'FROM '.$this->t_categories_locales.' '.
		'WHERE category_id='.(integer)$iCategoryId;

		if (($rs = $this->db->select($query)) === false) {
			$rs = new recordset(array());
			return $rs;
		}

		return $rs;
	}

	/**
	 * Créer une instance de cursor pour une rubrique et la retourne.
	 *
	 * @param ArrayObject $aCategoryData
	 * @return object cursor
	 */
	public function openCategoryCursor($aCategoryData=null)
	{
		$oCursor = $this->db->openCursor($this->t_categories);

		if (!empty($aCategoryData))
		{
			foreach ($aCategoryData as $k=>$v) {
				$oCursor->$k = $v;
			}
		}

		return $oCursor;
	}

	/**
	 * Ajout/modification des textes internationnalisés de la rubrique.
	 *
	 * @param integer $iCategoryId
	 * @param ArrayObject $aCategoryLocalesData
	 */
	protected function setCategoryI18n($iCategoryId, $aCategoryLocalesData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			$oCursor = $this->db->openCursor($this->t_categories_locales);

			$oCursor->category_id = $iCategoryId;

			$oCursor->language = $aLanguage['code'];

			foreach ($aCategoryLocalesData[$aLanguage['code']] as $k=>$v) {
				$oCursor->$k = $v;
			}

			$oCursor->content = $this->okt->HTMLfilter($oCursor->content);

			$oCursor->meta_description = html::clean($oCursor->meta_description);

			$oCursor->meta_keywords = html::clean($oCursor->meta_keywords);

			if (!$oCursor->insertUpdate()) {
				throw new Exception('Unable to insert category locales in database for '.$aLanguage['code'].' language.');
			}

			if (!$this->setCategorySlug($iCategoryId, $aLanguage['code'])) {
				throw new Exception('Unable to insert category slug in database.');
			}
		}
	}

	/**
	 * Création du slug d'une rubrique donnée dans une langue donnée.
	 *
	 * @param integer $iCategoryId
	 * @param string $sLanguage
	 * @return boolean
	 */
	protected function setCategorySlug($iCategoryId, $sLanguage)
	{
		$rsCategory = $this->getCategories(array(
			'id' => $iCategoryId,
			'language' => $sLanguage,
			'active' => 2
		));

		if ($rsCategory->isEmpty()) {
			throw new Exception(sprintf(__('m_news_cat_%s_not_exists'), $iCategoryId));
		}

		if (empty($rsCategory->slug))
		{
			$rsParent = $this->getCategories(array(
				'id' => $rsCategory->parent_id,
				'language' => $sLanguage,
				'active' => 2
			));

			$sSlug = $rsParent->slug.'/'.$rsCategory->title;
		}
		else {
			$sSlug = $rsCategory->slug;
		}

		$sSlug = util::strToSlug($sSlug, true);

		# Let's check if URL is taken…
		$query =
		'SELECT slug FROM '.$this->t_categories_locales.' '.
		'WHERE slug=\''.$this->db->escapeStr($sSlug).'\' '.
		'AND category_id <> '.(integer)$iCategoryId.' '.
		'AND language=\''.$this->db->escapeStr($sLanguage). '\' '.
		'ORDER BY slug DESC';

		$rsTakenSlugs = $this->db->select($query);

		if (!$rsTakenSlugs->isEmpty())
		{
			$query =
			'SELECT slug FROM '.$this->t_categories_locales.' '.
			'WHERE slug LIKE \''.$this->db->escapeStr($sSlug).'%\' '.
			'AND category_id <> '.(integer)$iCategoryId. ' '.
			'AND language=\''.$this->db->escapeStr($sLanguage). '\' '.
			'ORDER BY slug DESC ';

			$rsCurrentSlugs = $this->db->select($query);
			$a = array();
			while ($rsCurrentSlugs->fetch()) {
				$a[] = $rsCurrentSlugs->slug;
			}

			$sSlug = util::getIncrementedString($a, $sSlug, '-');
		}

		$query =
		'UPDATE '.$this->t_categories_locales.' SET '.
			'slug=\''.$this->db->escapeStr($sSlug).'\' '.
		'WHERE category_id='.(integer)$iCategoryId.' '.
		'AND language=\''.$this->db->escapeStr($sLanguage).'\' ';

		if (!$this->db->execute($query)) {
			throw new Exception('Unable to update category in database.');
		}

		return true;
	}

	/**
	 * Vérifie les données envoyées par formulaire.
	 *
	 * @param ArrayObject $aCategoryData
	 * @param ArrayObject $aCategoryLocalesData
	 * @return boolean
	 */
	public function checkPostData($aCategoryData, $aCategoryLocalesData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (empty($aCategoryLocalesData[$aLanguage['code']]['title']))
			{
				if ($this->okt->languages->unique) {
					$this->error->set(__('m_news_cat_must_enter_title'));
				}
				else {
					$this->error->set(sprintf(__('m_news_cat_must_enter_title_in_%s'),$aLanguage['title']));
				}
			}
		}

		return $this->error->isEmpty();
	}

	/**
	 * Ajout d'une rubrique.
	 *
	 * @param cursor $oCursor
	 * @param ArrayObject $aCategoryLocalesData
	 * @return integer
	 */
	public function addCategory($oCursor, $aCategoryLocalesData)
	{
		$iMaxOrder = $this->numChildren($oCursor->parent_id);
		$oCursor->ord = $iMaxOrder+1;

		if ($oCursor->parent_id > 0)
		{
			$rsParent = $this->getCategory($oCursor->parent_id);

			if ($rsParent->active == 0) {
				$oCursor->active = 0;
			}
		}

		if (!$oCursor->insert()) {
			throw new Exception('Unable to insert category in database.');
		}

		$iNewId =  $this->db->getLastID();

		$this->setCategoryI18n($iNewId, $aCategoryLocalesData);

		$this->rebuild();

		return $iNewId;
	}

	/**
	 * Modification d'une rubrique.
	 *
	 * @param cursor $oCursor
	 * @param ArrayObject $aCategoryLocalesData
	 * @return boolean
	 */
	public function updCategory($oCursor, $aCategoryLocalesData)
	{
		if (!$this->categoryExists($oCursor->id)) {
			throw new Exception(sprintf(__('m_news_cat_%s_not_exists'), $oCursor->id));
		}

		if ($oCursor->parent_id > 0)
		{
			if ($this->isDescendantOf($oCursor->parent_id, $oCursor->id)) {
				throw new Exception(__('m_news_cat_error_put_in_childs'));
			}

			$rsParent = $this->getCategory($oCursor->parent_id);

			if ($rsParent->active == 0) {
				$oCursor->active = 0;
			}
		}

		if (!$oCursor->update('WHERE id='.(integer)$oCursor->id.' ')) {
			throw new Exception('Unable to update category in database.');
		}

		if ($oCursor->active == 0)
		{
			$rsChildrens = $this->getDescendants($oCursor->id);
			while ($rsChildrens->fetch()) {
				$this->setCategoryStatus($rsChildrens->id,0);
			}
		}

		$this->setCategoryI18n($oCursor->id, $aCategoryLocalesData);

		$this->rebuild();

		return true;
	}

	/**
	 * Définit l'ordre d'une rubrique donnée.
	 *
	 * @param integer $iCategoryId
	 * @param integer $iOrder
	 * @return boolean
	 */
	public function setCategoryOrder($iCategoryId, $iOrder)
	{
		if (!$this->categoryExists($iCategoryId)) {
			throw new Exception(sprintf(__('m_news_cat_%s_not_exists'), $iCategoryId));
		}

		$sQuery =
		'UPDATE '.$this->t_categories.' SET '.
			'ord='.(integer)$iOrder.' '.
		'WHERE id='.(integer)$iCategoryId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to update category in database.');
		}

		return true;
	}

	/**
	 * Switch le statut de visibilité d'une rubrique donnée.
	 *
	 * @param integer $iCategoryId
	 * @return boolean
	 */
	public function switchCategoryStatus($iCategoryId)
	{
		$rsCategory = $this->getCategory($iCategoryId);

		if ($rsCategory->isEmpty()) {
			throw new Exception(sprintf(__('m_news_cat_%s_not_exists'), $iCategoryId));
		}

		$iStatus = $rsCategory->active ? 0 : 1;

		if ($iStatus == 0)
		{
			$rsChildrens = $this->getDescendants($iCategoryId);

			while ($rsChildrens->fetch()) {
				$this->setCategoryStatus($rsChildrens->id, 0);
			}
		}

		if ($rsCategory->parent_id != 0)
		{
			$rsParent = $this->getCategory($rsCategory->parent_id);

			if ($rsParent->active == 0) {
				throw new Exception(__('m_news_cat_error_parent_hidden'));
			}
		}

		return $this->setCategoryStatus($iCategoryId,$iStatus);
	}

	/**
	 * Définit le statut de visibilité d'une rubrique donnée.
	 *
	 * @param integer $iCategoryId
	 * @param integer $iStatus
	 * @return boolean
	 */
	public function setCategoryStatus($iCategoryId, $iStatus)
	{
		if (!$this->categoryExists($iCategoryId)) {
			throw new Exception(sprintf(__('m_news_cat_%s_not_exists'), $iCategoryId));
		}

		$sQuery =
		'UPDATE '.$this->t_categories.' SET '.
			'active='.(integer)$iStatus.' '.
		'WHERE id='.(integer)$iCategoryId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to update category in database.');
		}

		return true;
	}

	/**
	 * Suppression d'une rubrique.
	 *
	 * @param integer $iCategoryId
	 * @return boolean
	 */
	public function delCategory($iCategoryId)
	{
		$rsCategory = $this->getCategory($iCategoryId);

		if ($rsCategory->isEmpty()) {
			throw new Exception(sprintf(__('m_news_cat_%s_not_exists'), $iCategoryId));
		}

		$rsChildrens = $this->getChildren($iCategoryId);
		while ($rsChildrens->fetch()) {
			$this->setParentId($rsChildrens->id, $rsCategory->parent_id);
		}

		$sQuery =
		'DELETE FROM '.$this->t_categories.' '.
		'WHERE id='.(integer)$iCategoryId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to remove category from database.');
		}

		$this->db->optimize($this->t_categories);

		$query =
		'DELETE FROM '.$this->t_categories_locales.' '.
		'WHERE category_id='.(integer)$iCategoryId;

		if (!$this->db->execute($query)) {
			throw new Exception('Unable to remove category locales from database.');
		}

		$this->db->optimize($this->t_categories_locales);

		$this->rebuild();

		return true;
	}

	/**
	 * Définit le parent d'une rubrique donnée.
	 *
	 * @param integer $iCategoryId
	 * @param integer $iParentId
	 * @return boolean
	 */
	public function setParentId($iCategoryId, $iParentId)
	{
		if (!$this->categoryExists($iCategoryId)) {
			throw new Exception(sprintf(__('m_news_cat_%s_not_exists'), $iCategoryId));
		}

		$sQuery =
		'UPDATE '.$this->t_categories.' SET '.
			'parent_id='.(integer)$iParentId.' '.
		'WHERE id='.(integer)$iCategoryId;

		if (!$this->db->execute($sQuery)) {
			throw new Exception('Unable to update category in database.');
		}

		return true;
	}


}
