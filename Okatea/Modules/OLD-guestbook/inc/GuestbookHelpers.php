<?php

/**
 * @ingroup okt_module_guestbook
 * @brief Helpers.
 *
 */
class GuestbookHelpers
{

	/**
	 * Retourne l'URL de la page du livre d'or.
	 *
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getGuestbookUrl($sLanguage = null)
	{
		global $okt;
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt->user->language;
		}
		
		return $okt['router']->generate('guestbook');
	}
}
