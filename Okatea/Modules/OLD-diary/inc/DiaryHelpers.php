<?php

/**
 * @ingroup okt_module_diary
 * @brief Helpers.
 *
 */
class DiaryHelpers
{

	/**
	 * Retourne l'URL de la page agenda.
	 *
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getDiaryUrl($sLanguage = null)
	{
		global $okt;
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt['visitor']->language;
		}
		
		return $okt['router']->generate('diaryList');
	}

	/**
	 * Retourne l'URL de la page d'un évenement.
	 *
	 * @param string $sSlug        	
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getEventUrl($sSlug, $sLanguage = null)
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
		
		return $okt['router']->generate('diaryEvent', array(
			'slug' => $sSlug
		));
	}
}
