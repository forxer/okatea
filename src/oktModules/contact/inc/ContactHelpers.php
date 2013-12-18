<?php
/**
 * @ingroup okt_module_contact
 * @brief Helpers.
 *
 */

class ContactHelpers
{
	/**
	 * Retourne l'URL de la page contact.
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getContactUrl($sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		return $okt->router->generate('contactPage');
	}

	/**
	 * Retourne l'URL de la page de plan d'accès.
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getContactMapUrl($sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		return $okt->router->generate('contactMapPage');
	}
}
