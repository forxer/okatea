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
		# locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/%s/admin/advanced');

		$aValues= array(
			'app_path'   => str_replace('install', '', dirname($this->request->getRequestUri())),
			'domain'     => $this->request->getHttpHost()
		);

		if ($this->request->request->has('sended'))
		{
			$aValues = array(
				'app_path'   => Utilities::formatAppPath($this->request->request->get('p_app_path', '/')),
				'domain'     => Utilities::formatAppPath($this->request->request->get('p_domain', ''), false, false)
			);

			# save configuration
			if ($this->okt->error->isEmpty())
			{
				try
				{
					$this->okt->getConfig();
					$this->okt->config->write($aValues);

					return $this->redirect($this->generateUrl($this->okt->stepper->getNextStep()));
				}
				catch (InvalidArgumentException $e)
				{
					$this->okt->error->set(__('c_c_error_writing_configuration'));
					$this->okt->error->set($e->getMessage());
				}
			}
		}

		return $this->render('Config/Template', array(
			'title' => __('i_config_title'),
			'aValues' => $aValues
		));
	}
}
