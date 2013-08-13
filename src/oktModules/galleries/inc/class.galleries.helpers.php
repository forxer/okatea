<?php
/**
 * @ingroup okt_module_galleries
 * @brief Helpers.
 *
 */


class galleriesHelpers
{
	/**
	 * Retourne l'URL de la liste des galeries.
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getGalleriesUrl($sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		if (!isset($okt->galleries->config->public_list_url[$sLanguage])) {
			return null;
		}

		return $okt->page->getBaseUrl().$okt->galleries->config->public_list_url[$sLanguage];
	}

	/**
	 * Retourne l'URL d'une galerie à partir de son slug et éventuellement d'une langue.
	 *
	 * @param string $sSlug
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getGalleryUrl($sSlug, $sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		if (!isset($okt->galleries->config->public_gallery_url[$sLanguage])) {
			return null;
		}

		return $okt->page->getBaseUrl($sLanguage).$okt->galleries->config->public_gallery_url[$sLanguage].'/'.$sSlug;
	}

	/**
	 * Retourne l'URL d'un élément à partir de son slug et éventuellement d'une langue.
	 *
	 * @param string $sSlug
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getItemUrl($sSlug, $sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		if (!isset($okt->galleries->config->public_item_url[$sLanguage])) {
			return null;
		}

		return $okt->page->getBaseUrl($sLanguage).$okt->galleries->config->public_item_url[$sLanguage].'/'.$sSlug;
	}


} # class
