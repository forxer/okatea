<?php

/**
 * @ingroup okt_module_catalog
 * @brief Helpers.
 *
 */
class CatalogHelpers
{

	/**
	 * Retourne l'URL de la liste des produits.
	 *
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getCatalogUrl($sLanguage = null)
	{
		global $okt;
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt->user->language;
		}
		
		return $okt->router->generate('catalogList');
	}

	/**
	 * Retourne l'URL d'un produit à partir de son slug et éventuellement d'une langue.
	 *
	 * @param string $sSlug        	
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getProductUrl($sSlug, $sLanguage = null)
	{
		global $okt;
		
		if (empty($sSlug))
		{
			return null;
		}
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt->user->language;
		}
		
		return $okt->router->generate('catalogProduct', array(
			'slug' => $sSlug
		));
	}

	/**
	 * Retourne l'URL d'une rubrique à partir de son slug et éventuellement d'une langue.
	 *
	 * @param string $sSlug        	
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getCategoryUrl($sSlug, $sLanguage = null)
	{
		global $okt;
		
		if (empty($sSlug))
		{
			return null;
		}
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt->user->language;
		}
		
		return $okt->router->generate('catalogCategory', array(
			'slug' => $sSlug
		));
	}
}
