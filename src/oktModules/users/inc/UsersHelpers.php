<?php
/**
 * @ingroup okt_module_users
 * @brief Helpers.
 *
 */


class UsersHelpers
{
	/**
	 * Retourne l'URL de la page du formulaire de mot de passe oublié.
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getForgetPasswordUrl($sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		if (!isset($okt->users->config->public_forget_password_url[$sLanguage])) {
			return null;
		}

		return $okt->page->getBaseUrl().$okt->users->config->public_forget_password_url[$sLanguage];
	}

	/**
	 * Retourne l'URL de la page de déconnexion.
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getLogoutUrl($sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		if (!isset($okt->users->config->public_logout_url[$sLanguage])) {
			return null;
		}

		return $okt->page->getBaseUrl().$okt->users->config->public_logout_url[$sLanguage];
	}

	/**
	 * Retourne l'URL de la page de connexion.
	 *
	 * @param string $sRedirectUrl
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getLoginUrl($sRedirectUrl=null, $sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		$sLoginUrl = $okt->page->getBaseUrl();

		if ($okt->users->config->enable_log_reg_page && isset($okt->users->config->public_log_reg_url[$sLanguage])) {
			$sLoginUrl .= $okt->users->config->public_log_reg_url[$sLanguage];
		}
		elseif (isset($okt->users->config->public_login_url[$sLanguage])) {
			$sLoginUrl .= $okt->users->config->public_login_url[$sLanguage];
		}
		else {
			return null;
		}

		if (!is_null($sRedirectUrl)) {
			$sLoginUrl .= '?redirect='.rawurlencode($sRedirectUrl);
		}

		return $sLoginUrl;
	}

	/**
	 * Retourne l'URL de la page de profil de l'utilisateur.
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getProfileUrl($sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		if (!isset($okt->users->config->public_profile_url[$sLanguage])) {
			return null;
		}

		return $okt->page->getBaseUrl().$okt->users->config->public_profile_url[$sLanguage];
	}

	/**
	 * Retourne l'URL de la page du formulaire d'inscription.
	 *
	 * @param string $sRedirectUrl
	 * @param string $sLanguage
	 * @return string
	 */
	public static function getRegisterUrl($sRedirectUrl=null, $sLanguage=null)
	{
		global $okt;

		if (is_null($sLanguage)) {
			$sLanguage = $okt->user->language;
		}

		$sRegisterUrl = $okt->page->getBaseUrl();

		if ($okt->users->config->enable_log_reg_page && isset($okt->users->config->public_log_reg_url[$sLanguage])) {
			$sRegisterUrl .= $okt->users->config->public_log_reg_url[$sLanguage];
		}
		elseif (isset($okt->users->config->public_login_url[$sLanguage])) {
			$sRegisterUrl .= $okt->users->config->public_register_url[$sLanguage];
		}
		else {
			return null;
		}

		if (!is_null($sRedirectUrl)) {
			$sRegisterUrl .= '?redirect='.rawurlencode($sRedirectUrl);
		}

		return $sRegisterUrl;
	}

}
