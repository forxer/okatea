<?php
/**
 * @ingroup okt_module_recaptcha
* @brief La classe principale du module recaptcha
*
*/


use Tao\Modules\Module;

class module_recaptcha extends Module
{
	protected function prepend()
	{
		# permissions
		$this->okt->addPerm('recaptcha_config', __('m_recaptacha_perm_config'), 'configuration');

		# config
		$this->config = $this->okt->newConfig('conf_recaptcha');

		# enregistrement dans la pile de captcha disponibles
		$this->okt->page->addCaptcha('recaptcha',__('reCaptcha'), array(

			# behaviors page contact
			'publicModuleContactControllerFormCheckValues' => array('module_recaptcha','publicControllerFormCheckValues'),
			'publicModuleContactTplFormBottom' => array('module_recaptcha','publicTplFormBottom'),

			# behaviors estimate
			'publicModuleEstimateControllerFormCheckValues' => array('module_recaptcha','publicControllerFormCheckValues'),
			'publicModuleEstimateTplFormBottom' => array('module_recaptcha','publicTplFormBottom'),

			# behaviors livre d'or
			'publicModuleGuestbookControllerFormCheckValues' => array('module_recaptcha','publicControllerFormCheckValues'),
			'publicModuleGuestbookTplFormBottom' => array('module_recaptcha','publicTplFormBottom')
		));
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->configSubMenu->add(
				__('reCaptcha'),
				'module.php?m=recaptcha&amp;action=index',
				$this->bCurrentlyInUse && (!$this->okt->page->action || $this->okt->page->action === 'index'),
				30,
				$this->okt->checkPerm('recaptcha_config'),
				null
			);
		}
	}


	/*
	 * Behaviors
	 */

	/**
	 * Vérification du captcha
	 *
	 * @param object $okt
	 * @param string $sCaptchaId
	 */
	public static function publicControllerFormCheckValues($okt, $sCaptchaId)
	{
		if ($sCaptchaId == 'recaptcha')
		{
			require_once __DIR__.'/recaptcha-php-1.11/recaptchalib.php';

			$resp = recaptcha_check_answer(
				html::escapeHTML($okt->recaptcha->config->privatekey),
				$_SERVER["REMOTE_ADDR"],
				$_POST["recaptcha_challenge_field"],
				$_POST["recaptcha_response_field"]
			);

			if (!$resp->is_valid) {
				$okt->error->set(__('m_recaptcha_error'));
				return false;
			}
			else {
				return true;
			}
		}
		else {
			return true;
		}
	}

	/**
	 * Affichage du captcha côté public
	 *
	 * @param object $okt
	 * @param string $sCaptchaId
	 */
	public static function publicTplFormBottom($okt, $sCaptchaId)
	{
		if ($sCaptchaId == 'recaptcha')
		{
			$aAcceptedLanguages = array('en','nl','fr','de','pt','ru','es','tr');

			if (in_array($okt->user->language, $aAcceptedLanguages)) {
				$sLanguage = $okt->user->language;
			}
			elseif (in_array($okt->config->language, $aAcceptedLanguages)) {
				$sLanguage = $okt->config->language;
			}
			else {
				$sLanguage = 'en';
			}

			echo '<script type="text/javascript">
			//<![CDATA[

			var RecaptchaOptions = {
				theme: "'.$okt->recaptcha->config->theme.'",
				lang: "'.$sLanguage.'"
			};

			//]]>
			</script>';

			require_once __DIR__.'/recaptcha-php-1.11/recaptchalib.php';

			echo recaptcha_get_html(html::escapeHTML($okt->recaptcha->config->publickey));
		}
	}


}
