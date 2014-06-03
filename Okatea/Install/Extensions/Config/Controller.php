<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Extensions\Config;

use Okatea\Install\Controller as BaseController;
use Okatea\Tao\Misc\Utilities;

class Controller extends BaseController
{

	public function page()
	{
		$this->okt->startLanguages();

		# locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir . '/%s/admin/site');
		$this->okt->l10n->loadFile($this->okt->options->locales_dir . '/%s/admin/advanced');

		$aValues = [
			'title' => $this->okt->config->title,
			'desc' => $this->okt->config->desc,
			'email' => $this->okt->config->email
		];

		$aValues['app_path'] = str_replace('install', '', dirname($this->request->getRequestUri()));
		$aValues['domain'] = $this->request->getHttpHost();

		if ($this->request->request->has('sended'))
		{
			$p_title = $this->request->request->get('p_title', array());

			foreach ($p_title as $sLanguageCode => $sTitle)
			{
				if (empty($sTitle))
				{
					if ($this->okt->languages->unique) {
						$this->okt->error->set(__('c_a_config_please_enter_website_title'));
					}
					else {
						$this->okt->error->set(sprintf(__('c_a_config_please_enter_website_title_in_%s'), $this->okt->languages->list[$sLanguageCode]['title']));
					}
				}
			}

			$aValues = [
				'title' 	=> $p_title,
				'desc' 		=> $this->request->request->get('p_desc', array()),
				'app_path' 	=> Utilities::formatAppPath($this->request->request->get('p_app_path', '/')),
				'domain' 	=> Utilities::formatAppPath($this->request->request->get('p_domain', ''), false, false)
			];

			# save configuration
			if ($this->okt->error->isEmpty())
			{
				$this->okt->config->write($aValues);

				return $this->redirect($this->generateUrl($this->okt->stepper->getNextStep()));
			}
		}

		return $this->render('Config/Template', [
			'title' => __('i_config_title'),
			'aValues' => $aValues
		]);
	}
}
