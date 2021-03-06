<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Pages;

use Okatea\Tao\Html\Escaper;

class Helpers
{

	/**
	 * Retourne sous forme de liste HTML l'arbre des rubriques.
	 *
	 * @return string
	 */
	public static function getCategories()
	{
		global $okt;

		if (!$okt->module('Pages')->config->categories['enable'])
		{
			return null;
		}

		# on récupèrent l'éventuel identifiant de la catégorie en cours
		$iCurrentCat = null;
		if (isset($okt->page->module) && $okt->page->module == 'pages' && isset($okt->page->action))
		{
			if ($okt->page->action == 'category' && isset($okt->controller->rsCategory))
			{
				$iCurrentCat = $okt->controller->rsCategory->id;
			}
			elseif ($okt->page->action == 'item' && isset($okt->controller->rsPage))
			{
				$iCurrentCat = $okt->controller->rsPage->category_id;
			}

			unset($aVars);
		}

		$rsCategories = $okt->module('Pages')->categories->getCategories(array(
			'active' => 1,
			'language' => $okt['visitor']->language,
			'with_count' => false
		));

		$iRefLevel = $iLevel = $rsCategories->level - 1;

		$return = '';

		while ($rsCategories->fetch())
		{
			# ouverture niveau
			if ($rsCategories->level > $iLevel)
			{
				$return .= str_repeat('<ul><li id="cat-' . $rsCategories->id . '">', $rsCategories->level - $iLevel);
			}
			# fermeture niveau
			elseif ($rsCategories->level < $iLevel)
			{
				$return .= str_repeat('</li></ul>', - ($rsCategories->level - $iLevel));
			}

			# nouvelle ligne
			if ($rsCategories->level <= $iLevel)
			{
				$return .= '</li><li id="rub' . $rsCategories->id . '">';
			}

			$return .= '<a href="' . $okt['router']->generate('pagesCategory', array(
				'slug' => $rsCategories->slug
			)) . '">';

			if ($iCurrentCat == $rsCategories->id)
			{
				$return .= '<strong>' . Escaper::html($rsCategories->title) . '</strong>';
			}
			else
			{
				$return .= Escaper::html($rsCategories->title);
			}

			$return .= '</a>';

			$iLevel = $rsCategories->level;
		}

		if ($iRefLevel - $iLevel < 0)
		{
			$return .= str_repeat('</li></ul>', - ($iRefLevel - $iLevel));
		}

		return $return;
	}

	/**
	 * Retourne sous forme de liste HTML les pages d'une rubrique donnée.
	 *
	 * @param integer $iCatId
	 *        	de la rubrique a lister.
	 * @param string $sBlockFormat
	 *        	Masque de formatage du bloc ('<ul>%s</ul>')
	 * @param string $sItemFormat
	 *        	Masque de formatage d'un élément ('<li>%s</li>')
	 * @param string $sItemActiveFormat
	 *        	Masque de formatage d'un élément actif ('<li class="active"><strong>%s</strong></li>')
	 * @param string $sLinkFormat
	 *        	Masque de formatage d'un lien ('<a href="%s">%s</a>')
	 * @param string $sItemsGlue
	 *        	Liant entre les différents éléments ('')
	 * @param array $aCustomParams
	 *        	Paramètres de sélection personnalisés ([])
	 * @return string
	 */
	public static function getPagesByCatId($iCatId, $sBlockFormat = '<ul>%s</ul>', $sItemFormat = '<li>%s</li>', $sItemActiveFormat = '<li class="active"><strong>%s</strong></li>', $sLinkFormat = '<a href="%s">%s</a>', $sItemsGlue = '', $aCustomParams = [])
	{
		global $okt;

		# on récupèrent l'éventuel identifiant de la page en cours
		$iCurrentPage = null;
		if (isset($okt->page->module) && $okt->page->module == 'pages' && isset($okt->page->action) && $okt->page->action == 'item' && isset($okt->controller->rsPage))
		{
			$iCurrentPage = $okt->controller->rsPage->id;
		}

		$aParams = array_merge(array(
			'active' => 1,
			'language' => $okt['visitor']->language,
			'category_id' => $iCatId
		), $aCustomParams);

		# on récupèrent les pages
		$rsPages = $okt->module('Pages')->pages->getPages($aParams);

		# on construient le HTML avec les données
		$aItems = [];

		while ($rsPages->fetch())
		{
			$sItem = sprintf($sLinkFormat, Escaper::html($rsPages->url), Escaper::html($rsPages->title));

			if ($rsPages->id == $iCurrentPage)
			{
				$aItems[] = sprintf($sItemActiveFormat, $sItem);
			}
			else
			{
				$aItems[] = sprintf($sItemFormat, $sItem);
			}
		}

		return sprintf($sBlockFormat, implode($sItemsGlue, $aItems));
	}

	/**
	 * Retourne sous forme de liste HTML les sous-catégories d'une rubrique donnée.
	 *
	 * @param integer $iCatId
	 *        	de la rubrique a lister.
	 * @param string $sBlockFormat
	 *        	de formatage du bloc de la liste.
	 * @param string $sItemFormat
	 *        	Masque de formatage d'un élément de la liste.
	 * @param string $sItemActiveFormat
	 *        	Masque de formatage de l'élément actif de la liste.
	 * @param string $sLinkFormat
	 *        	Masque de formatage d'un lien de la liste.
	 * @param string $sItemsGlue
	 *        	Chaine de liaison entre les éléments.
	 * @return string
	 */
	public static function getSubCatsByCatId($iCatId, $sBlockFormat = '<ul>%s</ul>', $sItemFormat = '<li>%s</li>', $sItemActiveFormat = '<li class="active"><strong>%s</strong></li>', $sLinkFormat = '<a href="%s">%s</a>', $sItemsGlue = '')
	{
		global $okt;

		if (!$okt->module('Pages')->config->categories['enable'])
		{
			return null;
		}

		# on récupèrent l'éventuel identifiant de la catégorie en cours
		$iCurrentCat = null;
		if (isset($okt->page->module) && $okt->page->module == 'pages' && isset($okt->page->action))
		{
			if ($okt->page->action == 'category' && isset($okt->controller->rsCategory))
			{
				$iCurrentCat = $okt->controller->rsCategory->id;
			}
			elseif ($okt->page->action == 'item' && isset($okt->controller->rsPage))
			{
				$iCurrentCat = $okt->controller->rsPage->category_id;
			}
		}

		# on récupèrent les sous-catégories
		$rsChildren = $okt->module('Pages')->categories->getChildren($iCatId, false, $okt['visitor']->language);

		# on construient le HTML avec les données
		$aChildren = [];

		while ($rsChildren->fetch())
		{
			$sChildren = sprintf($sLinkFormat, $okt['router']->generate('pagesCategory', array(
				'slug' => $rsChildren->slug
			)), Escaper::html($rsChildren->title));

			if ($rsChildren->id == $iCurrentCat)
			{
				$aChildren[] = sprintf($sItemActiveFormat, $sChildren);
			}
			else
			{
				$aChildren[] = sprintf($sItemFormat, $sChildren);
			}
		}

		return sprintf($sBlockFormat, implode($sItemsGlue, $aChildren));
	}
}
