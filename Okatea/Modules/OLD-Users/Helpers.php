<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Users;

class Helpers
{

	/**
	 * Retourne l'URL de la page de connexion.
	 *
	 * @param string $sRedirectUrl        	
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getLoginUrl($sRedirectUrl = null, $sLanguage = null)
	{
		global $okt;
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt->user->language;
		}
		
		$sLoginUrl = $okt->page->getBaseUrl();
		
		if ($okt->users->config->enable_log_reg_page)
		{
			$sLoginUrl .= $okt['router']->generate('usersLoginRegister');
		}
		else
		{
			$sLoginUrl .= $okt['router']->generate('usersLogin');
		}
		
		if (! is_null($sRedirectUrl))
		{
			$sLoginUrl .= '?redirect=' . rawurlencode($sRedirectUrl);
		}
		
		return $sLoginUrl;
	}

	/**
	 * Retourne l'URL de la page de profil de l'utilisateur.
	 *
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getProfileUrl($sLanguage = null)
	{
		global $okt;
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt->user->language;
		}
		
		return $okt['router']->generate('usersProfile');
	}

	/**
	 * Retourne l'URL de la page du formulaire d'inscription.
	 *
	 * @param string $sRedirectUrl        	
	 * @param string $sLanguage        	
	 * @return string
	 */
	public static function getRegisterUrl($sRedirectUrl = null, $sLanguage = null)
	{
		global $okt;
		
		if (is_null($sLanguage))
		{
			$sLanguage = $okt->user->language;
		}
		
		$sRegisterUrl = $okt->page->getBaseUrl();
		
		if ($okt->users->config->enable_log_reg_page)
		{
			$sRegisterUrl .= $okt['router']->generate('usersLoginRegister');
		}
		else
		{
			$sRegisterUrl .= $okt['router']->generate('usersRegister');
		}
		
		if (! is_null($sRedirectUrl))
		{
			$sRegisterUrl .= '?redirect=' . rawurlencode($sRedirectUrl);
		}
		
		return $sRegisterUrl;
	}
}
