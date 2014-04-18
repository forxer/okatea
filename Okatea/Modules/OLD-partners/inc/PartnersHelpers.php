<?php
/**
 * @ingroup okt_module_partners
 * @brief Helpers.
 *
 */

class PartnersHelpers
{
	/**
	 * Retourne l'URL de la page partenaires.
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getPartnersUrl($sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		return $okt->router->generate('partners');
	}
}
