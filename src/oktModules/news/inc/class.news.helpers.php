<?php
/**
 * @ingroup okt_module_news
 * @brief Helpers.
 *
 */


class newsHelpers
{
	/**
	 * Retourne l'URL de la liste des articles.
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getNewsUrl($sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		return $okt->router->generate('newsList');
	}

	/**
	 * Retourne l'URL du flux RSS.
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getNewsFeedUrl($sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		return $okt->router->generate('newsFeed');
	}

	/**
	 * Retourne l'URL d'un article à partir de son slug et éventuellement d'une langue.
	 *
	 * @param string $sSlug
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getPostUrl($sSlug, $sLanguage=null)
	{
		global $okt;

		if (empty($sSlug)) {
			return null;
		}

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		return $okt->router->generate('newsItem', array('slug' => $sSlug));
	}

	/**
	 * Retourne l'URL d'une rubrique à partir de son slug et éventuellement d'une langue.
	 *
	 * @param string $sSlug
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getCategoryUrl($sSlug, $sLanguage=null)
	{
		global $okt;

		if (empty($sSlug)) {
			return null;
		}

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		return $okt->router->generate('newsCategory', array('slug' => $sSlug));
	}

	/**
	 * Retourne sous forme de liste HTML l'arbre des rubriques.
	 *
	 * @return string
	 */
	public static function getCategories()
	{
		global $okt;

		if (!$okt->news->config->categories['enable']) {
			return null;
		}

		# on récupèrent l'éventuel identifiant de la catégorie en cours
		$iCurrentCat = null;
		if (isset($okt->page->module) && $okt->page->module == 'news' && isset($okt->page->action))
		{
			if ($okt->page->action == 'category' && isset($okt->controller->rsCategory)) {
				$iCurrentCat = $okt->controller->rsCategory->id;
			}
			elseif ($okt->page->action == 'item' && isset($okt->controller->rsPost)) {
				$iCurrentCat = $okt->controller->rsPost->category_id;
			}
		}

		$rsCategories = $okt->news->categories->getCategories(array(
			'active' => 1,
			'language' => $okt->user->language,
			'with_count' => false
		));

		$iRefLevel = $iLevel = $rsCategories->level-1;

		$return = '';

		while ($rsCategories->fetch())
		{
			# ouverture niveau
			if ($rsCategories->level > $iLevel) {
				$return .= str_repeat('<ul><li id="cat-'.$rsCategories->id.'">', $rsCategories->level - $iLevel);
			}
			# fermeture niveau
			elseif ($rsCategories->level < $iLevel) {
				$return .= str_repeat('</li></ul>', -($rsCategories->level - $iLevel));
			}

			# nouvelle ligne
			if ($rsCategories->level <= $iLevel) {
				$return .= '</li><li id="rub'.$rsCategories->id.'">';
			}

			$return .= '<a href="'.html::escapeHTML(self::getCategoryUrl($rsCategories->slug)).'">';

			if ($iCurrentCat == $rsCategories->id) {
				$return .= '<strong>'.html::escapeHTML($rsCategories->title).'</strong>';
			}
			else {
				$return .= html::escapeHTML($rsCategories->title);
			}


			$return .= '</a>';

			$iLevel = $rsCategories->level;
		}

		if ($iRefLevel - $iLevel < 0) {
			$return .= str_repeat('</li></ul>', -($iRefLevel - $iLevel));
		}

		return $return;
	}

	/**
	 * Retourne sous forme de liste HTML les articles d'une rubrique donnée.
	 *
	 * @param integer $iCatId				L'identifiant de la rubrique a lister.
	 * @param string $sBlockFormat 			Masque de formatage du bloc ('<ul>%s</ul>')
	 * @param string $sItemFormat 			Masque de formatage d'un élément ('<li>%s</li>')
	 * @param string $sItemActiveFormat 	Masque de formatage d'un élément actif ('<li class="active"><strong>%s</strong></li>')
	 * @param string $sLinkFormat 			Masque de formatage d'un lien ('<a href="%s">%s</a>')
	 * @param string $sItemsGlue 			Liant entre les différents éléments ('')
	 * @param array $aCustomParams 			Paramètres de sélection personnalisés (array())
	 * @return string
	 */
	public static function getPostsByCatId($iCatId, $sBlockFormat='<ul>%s</ul>', $sItemFormat='<li>%s</li>', $sItemActiveFormat='<li class="active"><strong>%s</strong></li>', $sLinkFormat='<a href="%s">%s</a>', $sItemsGlue='', $aCustomParams=array())
	{
		global $okt;

		# on récupèrent l'éventuel identifiant de l'article en cours
		$iCurrentPage = null;
		if (isset($okt->page->module) && $okt->page->module == 'news'
			&& isset($okt->page->action) && $okt->page->action == 'item'
			&& isset($okt->controller->rsPost))
		{
			$iCurrentPage = $okt->controller->rsPost->id;
		}

		$aParams = array_merge(array(
			'active' => 1,
			'language' => $okt->user->language,
			'category_id' => $iCatId
		),$aCustomParams);

		# on récupèrent les articles
		$rsPosts = $okt->news->getPosts($aParams);

		# on construient le HTML avec les données
		$aItems = array();

		while ($rsPosts->fetch())
		{
			$sItem = sprintf($sLinkFormat, html::escapeHTML($rsPosts->url), html::escapeHTML($rsPosts->title));

			if ($rsPosts->id == $iCurrentPage) {
				$aItems[] = sprintf($sItemActiveFormat, $sItem);
			}
			else {
				$aItems[] = sprintf($sItemFormat, $sItem);
			}
		}

		return sprintf($sBlockFormat, implode($sItemsGlue, $aItems));
	}

	/**
	 * Retourne sous forme de liste HTML les sous-catégories d'une rubrique donnée.
	 *
	 * @param integer $iCatId				L'identifiant de la rubrique a lister.
	 * @param string $sBlockFormat			Masque de formatage du bloc de la liste.
	 * @param string $sItemFormat 			Masque de formatage d'un élément de la liste.
	 * @param string $sItemActiveFormat 	Masque de formatage de l'élément actif de la liste.
	 * @param string $sLinkFormat 			Masque de formatage d'un lien de la liste.
	 * @param string $sItemsGlue 			Chaine de liaison entre les éléments.
	 * @return string
	 */
	public static function getSubCatsByCatId($iCatId, $sBlockFormat='<ul>%s</ul>', $sItemFormat='<li>%s</li>', $sItemActiveFormat='<li class="active"><strong>%s</strong></li>', $sLinkFormat='<a href="%s">%s</a>', $sItemsGlue='')
	{
		global $okt;

		if (!$okt->news->config->categories['enable']) {
			return null;
		}

		# on récupèrent l'éventuel identifiant de la catégorie en cours
		$iCurrentCat = null;
		if (isset($okt->page->module) && $okt->page->module == 'news' && isset($okt->page->action))
		{
			if ($okt->page->action == 'category' && isset($okt->controller->rsCategory)) {
				$iCurrentCat = $okt->controller->rsCategory->id;
			}
			elseif ($okt->page->action == 'item' && isset($okt->controller->rsPost)) {
				$iCurrentCat = $okt->controller->rsPost->category_id;
			}
		}

		# on récupèrent les sous-catégories
		$rsChildren = $okt->news->categories->getChildren($iCatId, false, $okt->user->language);

		# on construient le HTML avec les données
		$aChildren = array();

		while ($rsChildren->fetch())
		{
			$sChildren = sprintf($sLinkFormat, html::escapeHTML(self::getCategoryUrl($rsChildren->slug)), html::escapeHTML($rsChildren->title));

			if ($rsChildren->id == $iCurrentCat) {
				$aChildren[] = sprintf($sItemActiveFormat, $sChildren);
			}
			else {
				$aChildren[] = sprintf($sItemFormat, $sChildren);
			}
		}

		return sprintf($sBlockFormat, implode($sItemsGlue, $aChildren));
	}

}
