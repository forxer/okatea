<?php

/**
 * @ingroup okt_module_faq
 * @brief Helpers.
 *
 */
class FaqHelpers
{

	/**
	 * Retourne l'URL de la FAQ.
	 *
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getFaqUrl($sLanguage = null)
	{
		global $okt;
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt['visitor']->language;
		}
		
		return $okt['router']->generate('faqList');
	}

	/**
	 * Retourne l'URL de la page d'une question.
	 *
	 * @param string $sSlug        	
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getQuestionUrl($sSlug, $sLanguage = null)
	{
		global $okt;
		
		if (empty($sSlug))
		{
			return null;
		}
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt['visitor']->language;
		}
		
		return $okt['router']->generate('faqQuestion', array(
			'slug' => $sSlug
		));
	}
}
