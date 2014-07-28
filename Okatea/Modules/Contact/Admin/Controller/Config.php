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
		if (! $this->okt->checkPerm('contact_usage') || ! $this->okt->checkPerm('contact_config'))
		{
			return $this->serve401();
		}
		
		$this->okt->l10n->loadFile(__DIR__ . '/../../Locales/%s/admin.config');
		
		$bGoogleMapNotEnablable = ($this->okt->config->address['street'] == '' || $this->okt->config->address['code'] == '' || $this->okt->config->address['city'] == '');
		
		$oTemplatesContact = new TemplatesSet($this->okt, $this->okt->module('Contact')->config->templates['contact'], 'Contact/contact', 'contact', $this->generateUrl('Contact_config') . '?');
		
		$oTemplatesMap = new TemplatesSet($this->okt, $this->okt->module('Contact')->config->templates['map'], 'Contact/map', 'map', $this->generateUrl('Contact_config') . '?');
		
		if ($this->request->request->has('form_sent'))
		{
			// ...
			

			if (! $this->flash->hasError())
			{
				$aNewConf = array(
					'captcha' => $this->request->request->get('p_captcha'),
					'from_to' => $this->request->request->get('p_from_to'),
					
					'email_color' => $this->request->request->get('p_email_color', '000000'),
					'email_size' => $this->request->request->getInt('p_email_size', 12),
					
					'templates' => array(
						'contact' => $oTemplatesContact->getPostConfig(),
						'map' => $oTemplatesMap->getPostConfig()
					),
					
					'name' => $this->request->request->get('p_name', array()),
					'name_seo' => $this->request->request->get('p_name_seo', array()),
					'title' => $this->request->request->get('p_title', array()),
					'meta_description' => $this->request->request->get('p_meta_description', array()),
					'meta_keywords' => $this->request->request->get('p_meta_keywords', array()),
					
					'name_map' => $this->request->request->get('p_name_map', array()),
					'name_seo_map' => $this->request->request->get('p_name_seo_map', array()),
					'title_map' => $this->request->request->get('p_title_map', array()),
					'meta_description_map' => $this->request->request->get('p_meta_description_map', array()),
					'meta_keywords_map' => $this->request->request->get('p_meta_keywords_map', array()),
					
					'google_map' => array(
						'enable' => $this->request->request->has('p_enable_google_map'),
						'display' => $this->request->request->get('p_google_map_display', 'inside'),
						'options' => array(
							'zoom' => $this->request->request->getInt('p_google_map_zoom', 14),
							'mode' => $this->request->request->get('p_google_map_mode', 'SATELLITE')
						)
					)
				);
				
				$this->okt->module('Contact')->config->write($aNewConf);
				
				$this->okt->flash->success(__('c_c_confirm_configuration_updated'));
				
				return $this->redirect($this->generateUrl('Contact_config'));
			}
		}
		
		return $this->render('Contact/Admin/Templates/Config', array(
			'bGoogleMapNotEnablable' => $bGoogleMapNotEnablable,
			'oTemplatesContact' => $oTemplatesContact,
			'oTemplatesMap' => $oTemplatesMap
		));
	}
}
