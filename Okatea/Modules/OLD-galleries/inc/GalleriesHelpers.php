<?php

/**
 * @ingroup okt_module_galleries
 * @brief Helpers.
 *
 */
class GalleriesHelpers
{

	/**
	 * Retourne l'URL de la liste des galeries.
	 *
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getGalleriesUrl($sLanguage = null)
	{
		global $okt;
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt->user->language;
		}
		
		return $okt['router']->generate('galleriesList');
	}

	/**
	 * Retourne l'URL d'une galerie à partir de son slug et éventuellement d'une langue.
	 *
	 * @param string $sSlug        	
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getGalleryUrl($sSlug, $sLanguage = null)
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
		
		return $okt['router']->generate('galleriesGallery', array(
			'slug' => $sSlug
		));
	}

	/**
	 * Retourne l'URL d'un élément à partir de son slug et éventuellement d'une langue.
	 *
	 * @param string $sSlug        	
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getItemUrl($sSlug, $sLanguage = null)
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
		
		return $okt['router']->generate('galleriesItem', array(
			'slug' => $sSlug
		));
	}
}
