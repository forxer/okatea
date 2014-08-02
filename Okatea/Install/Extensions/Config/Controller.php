<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Extensions\Config;

use Okatea\Install\Controller as BaseController;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Misc\Utilities;

class Controller extends BaseController
{

	public function page()
	{
		$this->okt->startLanguages();

		# locales
		$this->okt->l10n->loadFile($this->okt['locales_dir'] . '/%s/admin/site');
		$this->okt->l10n->loadFile($this->okt['locales_dir'] . '/%s/admin/advanced');

		$aValues = [
			'title' => $this->okt['config']->title,
			'desc' => $this->okt['config']->desc,
			'email' => $this->okt['config']->email
		];

		$aValues['app_path'] = str_replace('install', '', dirname($this->okt['request']->getRequestUri()));
		$aValues['domain'] = $this->okt['request']->getHttpHost();

		if ($this->okt['request']->request->has('sended'))
		{
			$p_title = $this->okt['request']->request->get('p_title', array());

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

			$p_email_to = $this->okt['request']->request->get('p_email_to');
			if (empty($p_email_to))
			{
				$this->okt->error->set(__('c_a_config_please_enter_email_to'));
			}
			elseif (! Utilities::isEmail($p_email_to))
			{
				$this->okt->error->set(sprintf(__('c_c_error_invalid_email'), Escaper::html($p_email_to)));
			}

			$p_email_from = $this->okt['request']->request->get('p_email_from');
			if (empty($p_email_from))
			{
				$this->okt->error->set(__('c_a_config_please_enter_email_from'));
			}
			elseif (! Utilities::isEmail($p_email_from))
			{
				$this->okt->error->set(sprintf(__('c_c_error_invalid_email'), Escaper::html($p_email_from)));
			}

			$aValues = [
				'title' 	=> $p_title,
				'desc' 		=> $this->okt['request']->request->get('p_desc', array()),
				'email' => array(
					'to' => $p_email_to,
					'from' => $p_email_from,
					'name' => $this->okt['request']->request->get('p_email_name'),
					'transport' => 'mail',
					'smtp' => [
						'host' => '',
						'port' => 25,
						'username' => '',
						'password' => ''
					],
					'sendmail' => ''
				),
				'app_path' 	=> Utilities::formatAppPath($this->okt['request']->request->get('p_app_path', '/')),
				'domain' 	=> Utilities::formatAppPath($this->okt['request']->request->get('p_domain', ''), false, false)
			];

			# save configuration
			if (! $this->okt['flash']->hasError())
			{
				$this->okt['config']->write($aValues);

				return $this->redirect($this->generateUrl($this->okt->stepper->getNextStep()));
			}
		}

		return $this->render('Config/Template', [
			'title' => __('i_config_title'),
			'aValues' => $aValues
		]);
	}
}
