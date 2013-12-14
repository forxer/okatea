<?php
/**
 * @ingroup okt_module_pages
 * @brief Helpers.
 *
 */


class pagesHelpers
{
	/**
	 * Retourne l'URL de la liste des pages.
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getPagesUrl($sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		if (!isset($okt->pages->config->routes['list'][$sLanguage])) {
			return null;
		}

		return $okt->router->generate('pagesList');
	}

	/**
	 * Retourne l'URL d'une page à partir de son slug et éventuellement d'une langue.
	 *
	 * @param string $sSlug
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getPageUrl($sSlug, $sLanguage=null)
	{
		global $okt;

		if (empty($sSlug)) {
			return null;
		}

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		if (!isset($okt->pages->config->routes['post'][$sLanguage])) {
			return null;
		}

		return $okt->router->generate('pagesItem', array('slug' => $sSlug));
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

		if (!isset($okt->pages->config->routes['category'][$sLanguage])) {
			return null;
		}

		return $okt->router->generate('pagesCategory', array('slug' => $sSlug));
	}

	/**
	 * Retourne sous forme de liste HTML l'arbre des rubriques.
	 *
	 * @return string
	 */
	public static function getCategories()
	{
		global $okt;

		if (!$okt->pages->config->categories['enable']) {
			return null;
		}

		# on récupèrent l'éventuel identifiant de la catégorie en cours
		$iCurrentCat = null;
		if (isset($okt->page->module) && $okt->page->module == 'pages' && isset($okt->page->action))
		{
			if ($okt->page->action == 'category' && isset($okt->controller->rsCategory)) {
				$iCurrentCat = $okt->controller->rsCategory->id;
			}
			elseif ($okt->page->action == 'item' && isset($okt->controller->rsPage)) {
				$iCurrentCat = $okt->controller->rsPage->category_id;
			}

			unset($aVars);
		}

		$rsCategories = $okt->pages->categories->getCategories(array(
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
	 * Retourne sous forme de liste HTML les pages d'une rubrique donnée.
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
	public static function getPagesByCatId($iCatId, $sBlockFormat='<ul>%s</ul>', $sItemFormat='<li>%s</li>', $sItemActiveFormat='<li class="active"><strong>%s</strong></li>', $sLinkFormat='<a href="%s">%s</a>', $sItemsGlue='', $aCustomParams=array())
	{
		global $okt;

		# on récupèrent l'éventuel identifiant de la page en cours
		$iCurrentPage = null;
		if (isset($okt->page->module) && $okt->page->module == 'pages'
			&& isset($okt->page->action) && $okt->page->action == 'item'
			&& isset($okt->controller->rsPage))
		{
			$iCurrentPage = $okt->controller->rsPage->id;
		}

		$aParams = array_merge(array(
			'active' => 1,
			'language' => $okt->user->language,
			'category_id' => $iCatId
		),$aCustomParams);

		# on récupèrent les pages
		$rsPages = $okt->pages->getPages($aParams);

		# on construient le HTML avec les données
		$aItems = array();

		while ($rsPages->fetch())
		{
			$sItem = sprintf($sLinkFormat, html::escapeHTML($rsPages->getPageUrl()), html::escapeHTML($rsPages->title));

			if ($rsPages->id == $iCurrentPage) {
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

		if (!$okt->pages->config->categories['enable']) {
			return null;
		}

		# on récupèrent l'éventuel identifiant de la catégorie en cours
		$iCurrentCat = null;
		if (isset($okt->page->module) && $okt->page->module == 'pages' && isset($okt->page->action))
		{
			if ($okt->page->action == 'category' && isset($okt->controller->rsCategory)) {
				$iCurrentCat = $okt->controller->rsCategory->id;
			}
			elseif ($okt->page->action == 'item' && isset($okt->controller->rsPage)) {
				$iCurrentCat = $okt->controller->rsPage->category_id;
			}
		}

		# on récupèrent les sous-catégories
		$rsChildren = $okt->pages->categories->getChildren($iCatId, false, $okt->user->language);

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
