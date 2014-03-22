<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Contact\Admin\Controller;

use Okatea\Admin\Controller;
use Okatea\Tao\Themes\TemplatesSet;

class Config extends Controller
{
	public function page()
	{
		if (!$this->okt->checkPerm('contact_config')) {
			return $this->serve401();
		}

		# Chargement des locales
		$this->okt->l10n->loadFile(__DIR__.'/../../Locales/%s/admin.config');

		# google map activable ?
		$bGoogleMapNotEnablable = ($this->okt->config->address['street'] == '' || $this->okt->config->address['code'] == '' || $this->okt->config->address['city'] == '');

		# Gestionnaires de templates
		$oTemplatesContact = new TemplatesSet($this->okt,
			$this->okt->module('Contact')->config->templates['contact'],
			'Contact/contact',
			'contact',
			$this->generateUrl('Contact_config').'?'
		);

		$oTemplatesMap = new TemplatesSet($this->okt,
			$this->okt->module('Contact')->config->templates['map'],
			'Contact/map',
			'map',
			$this->generateUrl('Contact_config').'?'
		);

		# enregistrement configuration
		if ($this->request->request->has('form_sent'))
		{
			$p_captcha = $this->request->request->get('p_captcha');
			$p_from_to = $this->request->request->get('p_from_to');
			$p_mail_color = $this->request->request->get('p_mail_color', '000000');
			$p_mail_size = $this->request->request->getInt('p_mail_size', 12);

			$p_tpl_contact = $oTemplatesContact->getPostConfig();
			$p_tpl_map = $oTemplatesMap->getPostConfig();

			$p_name = $this->request->request->get('p_name', array());
			$p_name_seo = $this->request->request->get('p_name_seo', array());
			$p_title = $this->request->request->get('p_title', array());
			$p_meta_description = $this->request->request->get('p_meta_description', array());
			$p_meta_keywords = $this->request->request->get('p_meta_keywords', array());

			$p_name_map = $this->request->request->get('p_name_map', array());
			$p_name_seo_map = $this->request->request->get('p_name_seo_map', array());
			$p_title_map = $this->request->request->get('p_title_map', array());
			$p_meta_description_map = $this->request->request->get('p_meta_description_map', array());
			$p_meta_keywords_map = $this->request->request->get('p_meta_keywords_map', array());

			$p_enable_google_map = $this->request->request->has('p_enable_google_map');
			$p_google_map_display = $this->request->request->get('p_google_map_display', 'inside');
			$p_google_map_zoom = $this->request->request->getInt('p_google_map_zoom', 14);
			$p_google_map_mode = $this->request->request->get('p_google_map_mode', 'SATELLITE');

			if ($this->okt->error->isEmpty())
			{
				$aNewConf = array(
					'captcha' => $p_captcha,
					'from_to' => $p_from_to,

					'mail_color' => $p_mail_color,
					'mail_size' => $p_mail_size,

					'templates' => array(
						'contact' => $p_tpl_contact,
						'map' => $p_tpl_map
					),

					'name' => $p_name,
					'name_seo' => $p_name_seo,
					'title' => $p_title,

					'meta_description' => $p_meta_description,
					'meta_keywords' => $p_meta_keywords,

					'name_map' => $p_name_map,
					'name_seo_map' => $p_name_seo_map,
					'title_map' => $p_title_map,

					'meta_description_map' => $p_meta_description_map,
					'meta_keywords_map' => $p_meta_keywords_map,

					'google_map' => array(
						'enable' => (boolean)$p_enable_google_map,
						'display' => $p_google_map_display,
						'options' => array(
							'zoom' => (integer)$p_google_map_zoom,
							'mode' => $p_google_map_mode
						)
					)
				);

				try
				{
					$this->okt->module('Contact')->config->write($aNewConf);

					$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

					return $this->redirect($this->generateUrl('Contact_config'));
				}
				catch (InvalidArgumentException $e)
				{
					$this->okt->error->set(__('c_c_error_writing_configuration'));
					$this->okt->error->set($e->getMessage());
				}
			}
		}

		return $this->render('Contact/Admin/Templates/Config', array(
			'bGoogleMapNotEnablable' 	=> $bGoogleMapNotEnablable,
			'oTemplatesContact' 		=> $oTemplatesContact,
			'oTemplatesMap' 			=> $oTemplatesMap
		));
	}
}
