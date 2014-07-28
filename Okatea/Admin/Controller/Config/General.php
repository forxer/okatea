<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Config;

use ArrayObject;
use Okatea\Admin\Controller;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Misc\Utilities;

class General extends Controller
{
	protected $aPageData;

	public function page()
	{
		if (! $this->okt->checkPerm('configsite')) {
			return $this->serve401();
		}

		# Locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir . '/%s/admin/site');

		# Données de la page
		$this->aPageData = new ArrayObject();
		$this->aPageData['values'] = array();

		$this->generalInit();

		$this->companyInit();

		$this->emailsInit();

		$this->seoInit();

		# -- TRIGGER CORE : adminConfigSiteInit
		$this->okt->triggers->callTrigger('adminConfigSiteInit', $this->aPageData);

		if ($this->request->request->has('form_sent'))
		{
			$this->generalHandleRequest();

			$this->companyHandleRequest();

			$this->emailsHandleRequest();

			$this->seoHandleRequest();

			# -- TRIGGER CORE : adminConfigSiteHandleRequest
			$this->okt->triggers->callTrigger('adminConfigSiteHandleRequest', $this->aPageData);

			# save configuration
			if (! $this->flash->hasError())
			{
				$this->okt->config->write($this->aPageData['values']);

				$this->flash->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('config_general'));
			}
		}

		# Construction des onglets
		$this->aPageData['tabs'] = new ArrayObject();

		# onglet général
		$this->aPageData['tabs'][10] = array(
			'id' => 'tab_general',
			'title' => __('c_a_config_tab_general'),
			'content' => $this->renderView('Config/General/Tabs/General', array(
				'aPageData' => $this->aPageData
			))
		);

		# onglet société
		$this->aPageData['tabs'][20] = array(
			'id' => 'tab_company',
			'title' => __('c_a_config_tab_company'),
			'content' => $this->renderView('Config/General/Tabs/Company', array(
				'aPageData' => $this->aPageData
			))
		);

		# onglet emails
		$this->aPageData['tabs'][30] = array(
			'id' => 'tab_emails',
			'title' => __('c_a_config_tab_email'),
			'content' => $this->renderView('Config/General/Tabs/Emails', array(
				'aPageData' => $this->aPageData
			))
		);

		# onglet seo
		$this->aPageData['tabs'][40] = array(
			'id' => 'tab_seo',
			'title' => __('c_a_config_tab_seo'),
			'content' => $this->renderView('Config/General/Tabs/Seo', array(
				'aPageData' => $this->aPageData
			))
		);

		# -- TRIGGER CORE : adminConfigSiteBuildTabs
		$this->okt->triggers->callTrigger('adminConfigSiteBuildTabs', $this->aPageData);

		$this->aPageData['tabs']->ksort();

		return $this->render('Config/General/Page', array(
			'aPageData' => $this->aPageData
		));
	}

	protected function generalInit()
	{
		$this->aPageData['home_page_items'] = array(
			' ' => null
		);
		$this->aPageData['home_page_details'] = array();

		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'title' => $this->okt->config->title,
			'desc' => $this->okt->config->desc,
			'home_page' => array(
				'item' => $this->okt->config->home_page['item'],
				'details' => $this->okt->config->home_page['details']
			)
		));
	}

	protected function companyInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'company' => array(
				'name' => $this->okt->config->company['name'],
				'com_name' => $this->okt->config->company['com_name'],
				'siret' => $this->okt->config->company['siret']
			),
			'schedule' => $this->okt->config->schedule,
			'leader' => array(
				'name' => $this->okt->config->leader['name'],
				'firstname' => $this->okt->config->leader['firstname']
			),
			'address' => array(
				'street' => $this->okt->config->address['street'],
				'street_2' => $this->okt->config->address['street_2'],
				'code' => $this->okt->config->address['code'],
				'city' => $this->okt->config->address['city'],
				'country' => $this->okt->config->address['country'],
				'tel' => $this->okt->config->address['tel'],
				'mobile' => $this->okt->config->address['mobile'],
				'fax' => $this->okt->config->address['fax']
			),
			'gps' => array(
				'lat' => $this->okt->config->gps['lat'],
				'long' => $this->okt->config->gps['long']
			)
		));
	}

	protected function emailsInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'email' => array(
				'to' => $this->okt->config->email['to'],
				'from' => $this->okt->config->email['from'],
				'name' => $this->okt->config->email['name'],
				'transport' => $this->okt->config->email['transport'],
				'smtp' => array(
					'host' => $this->okt->config->email['smtp']['host'],
					'port' => $this->okt->config->email['smtp']['port'],
					'username' => $this->okt->config->email['smtp']['username'],
					'password' => $this->okt->config->email['smtp']['password']
				),
				'sendmail' => $this->okt->config->email['sendmail']
			)
		));
	}

	protected function seoInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'title_tag' => $this->okt->config->title_tag,
			'meta_description' => $this->okt->config->meta_description,
			'meta_keywords' => $this->okt->config->meta_keywords
		));
	}

	protected function generalHandleRequest()
	{
		$p_title = $this->request->request->get('p_title', array());

		foreach ($p_title as $sLanguageCode => $sTitle)
		{
			if (empty($sTitle))
			{
				if ($this->okt->languages->unique) {
					$this->flash->error(__('c_a_config_please_enter_website_title'));
				}
				else {
					$this->flash->error(sprintf(__('c_a_config_please_enter_website_title_in_%s'), $this->okt->languages->list[$sLanguageCode]['title']));
				}
			}
		}

		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'title' => $p_title,
			'desc' => $this->request->request->get('p_desc', array()),
			'home_page' => array(
				'item' => $this->request->request->get('p_home_page_item', array()),
				'details' => $this->request->request->get('p_home_page_details', array())
			)
		));
	}

	protected function companyHandleRequest()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'company' => array(
				'name' => $this->request->request->get('p_company_name'),
				'com_name' => $this->request->request->get('p_company_com_name'),
				'siret' => $this->request->request->get('p_company_siret')
			),
			'schedule' => $this->request->request->get('p_schedule', array()),
			'leader' => array(
				'name' => $this->request->request->get('p_leader_name'),
				'firstname' => $this->request->request->get('p_leader_firstname')
			),
			'address' => array(
				'street' => $this->request->request->get('p_address_street'),
				'street_2' => $this->request->request->get('p_address_street_2'),
				'code' => $this->request->request->get('p_address_code'),
				'city' => $this->request->request->get('p_address_city'),
				'country' => $this->request->request->get('p_address_country'),
				'tel' => $this->request->request->get('p_address_tel'),
				'mobile' => $this->request->request->get('p_address_mobile'),
				'fax' => $this->request->request->get('p_address_fax')
			),
			'gps' => array(
				'lat' => $this->request->request->get('p_gps_lat'),
				'long' => $this->request->request->get('p_gps_long')
			)
		));
	}

	protected function emailsHandleRequest()
	{
		$p_email_to = $this->request->request->get('p_email_to');
		if (empty($p_email_to)) {
			$this->flash->error(__('c_a_config_please_enter_email_to'));
		}
		elseif (! Utilities::isEmail($p_email_to)) {
			$this->flash->error(sprintf(__('c_c_error_invalid_email'), Escaper::html($p_email_to)));
		}

		$p_email_from = $this->request->request->get('p_email_from');
		if (empty($p_email_from)) {
			$this->flash->error(__('c_a_config_please_enter_email_from'));
		}
		elseif (! Utilities::isEmail($p_email_from)) {
			$this->flash->error(sprintf(__('c_c_error_invalid_email'), Escaper::html($p_email_from)));
		}

		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'email' => array(
				'to' => $p_email_to,
				'from' => $p_email_from,
				'name' => $this->request->request->get('p_email_name'),
				'transport' => $this->request->request->get('p_email_transport', 'mail'),
				'smtp' => array(
					'host' => $this->request->request->get('p_email_smtp_host'),
					'port' => $this->request->request->getInt('p_email_smtp_port', 25),
					'username' => $this->request->request->get('p_email_smtp_username'),
					'password' => $this->request->request->get('p_email_smtp_password')
				),
				'sendmail' => $this->request->request->get('p_email_sendmail')
			)
		));
	}

	protected function seoHandleRequest()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], array(
			'title_tag' => $this->request->request->get('p_title_tag', array()),
			'meta_description' => $this->request->request->get('p_meta_description', array()),
			'meta_keywords' => $this->request->request->get('p_meta_keywords', array())
		));
	}
}
