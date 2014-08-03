<?php

/**
 * @ingroup okt_module_estimate
 * @brief Helpers.
 *
 */
class EstimateHelpers
{

	/**
	 * Retourne l'URL de la page du formulaire de demande de devis.
	 *
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getFormUrl($sLanguage = null)
	{
		global $okt;
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt['visitor']->language;
		}
		
		return $okt['router']->generate('estimateForm');
	}

	/**
	 * Retourne l'URL de récapitulatif de demande de devis.
	 *
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getSummaryUrl($sLanguage = null)
	{
		global $okt;
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt['visitor']->language;
		}
		
		return $okt['router']->generate('estimateSummary');
	}
}
