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
		if (!$this->okt['visitor']->checkPerm('configsite')) {
			return $this->serve401();
		}

		# Locales
		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/site');

		# page data
		$this->aPageData = new ArrayObject();
		$this->aPageData['values'] = [];

		$this->generalInit();

		$this->companyInit();

		$this->emailsInit();

		$this->seoInit();

		# -- TRIGGER CORE : adminConfigSiteInit
		$this->okt['triggers']->callTrigger('adminConfigSiteInit', $this->aPageData);

		if ($this->okt['request']->request->has('form_sent'))
		{
			$this->generalHandleRequest();

			$this->companyHandleRequest();

			$this->emailsHandleRequest();

			$this->seoHandleRequest();

			# -- TRIGGER CORE : adminConfigSiteHandleRequest
			$this->okt['triggers']->callTrigger('adminConfigSiteHandleRequest', $this->aPageData);

			# save configuration
			if (!$this->okt['instantMessages']->hasError())
			{
				$this->okt['config']->write($this->aPageData['values']);

				$this->okt['flashMessages']->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('config_general'));
			}
		}

		# build tabs
		$this->aPageData['tabs'] = new ArrayObject();

		# general tab
		$this->aPageData['tabs'][10] = [
			'id' => 'tab_general',
			'title' => __('c_a_config_tab_general'),
			'content' => $this->renderView('Config/General/Tabs/General', [
				'aPageData' => $this->aPageData
			])
		];

		# company tab
		$this->aPageData['tabs'][20] = [
			'id' => 'tab_company',
			'title' => __('c_a_config_tab_company'),
			'content' => $this->renderView('Config/General/Tabs/Company', [
				'aPageData' => $this->aPageData
			])
		];

		# emails tab
		$this->aPageData['tabs'][30] = [
			'id' => 'tab_emails',
			'title' => __('c_a_config_tab_email'),
			'content' => $this->renderView('Config/General/Tabs/Emails', [
				'aPageData' => $this->aPageData
			])
		];

		# seo tab
		$this->aPageData['tabs'][40] = [
			'id' => 'tab_seo',
			'title' => __('c_a_config_tab_seo'),
			'content' => $this->renderView('Config/General/Tabs/Seo', [
				'aPageData' => $this->aPageData
			])
		];

		# -- TRIGGER CORE : adminConfigSiteBuildTabs
		$this->okt['triggers']->callTrigger('adminConfigSiteBuildTabs', $this->aPageData);

		$this->aPageData['tabs']->ksort();

		return $this->render('Config/General/Page', [
			'aPageData' => $this->aPageData
		]);
	}

	protected function generalInit()
	{
		$this->aPageData['home_page_items'] = [
			' ' => null
		];
		$this->aPageData['home_page_details'] = [];

		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'title' => $this->okt['config']->title,
			'desc' => $this->okt['config']->desc,
			'home_page' => [
				'item' => $this->okt['config']->home_page['item'],
				'details' => $this->okt['config']->home_page['details']
			]
		]);
	}

	protected function companyInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'company' => [
				'name' => $this->okt['config']->company['name'],
				'com_name' => $this->okt['config']->company['com_name'],
				'siret' => $this->okt['config']->company['siret']
			],
			'schedule' => $this->okt['config']->schedule,
			'leader' => [
				'name' => $this->okt['config']->leader['name'],
				'firstname' => $this->okt['config']->leader['firstname']
			],
			'address' => [
				'street' => $this->okt['config']->address['street'],
				'street_2' => $this->okt['config']->address['street_2'],
				'code' => $this->okt['config']->address['code'],
				'city' => $this->okt['config']->address['city'],
				'country' => $this->okt['config']->address['country'],
				'tel' => $this->okt['config']->address['tel'],
				'mobile' => $this->okt['config']->address['mobile'],
				'fax' => $this->okt['config']->address['fax']
			],
			'gps' => [
				'lat' => $this->okt['config']->gps['lat'],
				'long' => $this->okt['config']->gps['long']
			]
		]);
	}

	protected function emailsInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'email' => [
				'to' => $this->okt['config']->email['to'],
				'from' => $this->okt['config']->email['from'],
				'name' => $this->okt['config']->email['name'],
				'transport' => $this->okt['config']->email['transport'],
				'smtp' => [
					'host' => $this->okt['config']->email['smtp']['host'],
					'port' => $this->okt['config']->email['smtp']['port'],
					'username' => $this->okt['config']->email['smtp']['username'],
					'password' => $this->okt['config']->email['smtp']['password']
				],
				'sendmail' => $this->okt['config']->email['sendmail']
			]
		]);
	}

	protected function seoInit()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'title_tag' => $this->okt['config']->title_tag,
			'meta_description' => $this->okt['config']->meta_description,
			'meta_keywords' => $this->okt['config']->meta_keywords
		]);
	}

	protected function generalHandleRequest()
	{
		$p_title = $this->okt['request']->request->get('p_title', []);

		foreach ($p_title as $sLanguageCode => $sTitle)
		{
			if (empty($sTitle))
			{
				if ($this->okt['languages']->hasUniqueLanguage()) {
					$this->okt['instantMessages']->error(__('c_a_config_please_enter_website_title'));
				}
				else {
					$this->okt['instantMessages']->error(sprintf(__('c_a_config_please_enter_website_title_in_%s'), $this->okt['languages']->getList()[$sLanguageCode]['title']));
				}
			}
		}

		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'title' => $p_title,
			'desc' => $this->okt['request']->request->get('p_desc', []),
			'home_page' => [
				'item' => $this->okt['request']->request->get('p_home_page_item', []),
				'details' => $this->okt['request']->request->get('p_home_page_details', [])
			]
		]);
	}

	protected function companyHandleRequest()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'company' => [
				'name' => $this->okt['request']->request->get('p_company_name'),
				'com_name' => $this->okt['request']->request->get('p_company_com_name'),
				'siret' => $this->okt['request']->request->get('p_company_siret')
			],
			'schedule' => $this->okt['request']->request->get('p_schedule', []),
			'leader' => [
				'name' => $this->okt['request']->request->get('p_leader_name'),
				'firstname' => $this->okt['request']->request->get('p_leader_firstname')
			],
			'address' => [
				'street' => $this->okt['request']->request->get('p_address_street'),
				'street_2' => $this->okt['request']->request->get('p_address_street_2'),
				'code' => $this->okt['request']->request->get('p_address_code'),
				'city' => $this->okt['request']->request->get('p_address_city'),
				'country' => $this->okt['request']->request->get('p_address_country'),
				'tel' => $this->okt['request']->request->get('p_address_tel'),
				'mobile' => $this->okt['request']->request->get('p_address_mobile'),
				'fax' => $this->okt['request']->request->get('p_address_fax')
			],
			'gps' => [
				'lat' => $this->okt['request']->request->get('p_gps_lat'),
				'long' => $this->okt['request']->request->get('p_gps_long')
			]
		]);
	}

	protected function emailsHandleRequest()
	{
		$p_email_to = $this->okt['request']->request->get('p_email_to');
		if (empty($p_email_to)) {
			$this->okt['instantMessages']->error(__('c_a_config_please_enter_email_to'));
		}
		elseif (!Utilities::isEmail($p_email_to)) {
			$this->okt['instantMessages']->error(sprintf(__('c_c_error_invalid_email'), Escaper::html($p_email_to)));
		}

		$p_email_from = $this->okt['request']->request->get('p_email_from');
		if (empty($p_email_from)) {
			$this->okt['instantMessages']->error(__('c_a_config_please_enter_email_from'));
		}
		elseif (!Utilities::isEmail($p_email_from)) {
			$this->okt['instantMessages']->error(sprintf(__('c_c_error_invalid_email'), Escaper::html($p_email_from)));
		}

		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'email' => [
				'to' => $p_email_to,
				'from' => $p_email_from,
				'name' => $this->okt['request']->request->get('p_email_name'),
				'transport' => $this->okt['request']->request->get('p_email_transport', 'mail'),
				'smtp' => [
					'host' => $this->okt['request']->request->get('p_email_smtp_host'),
					'port' => $this->okt['request']->request->getInt('p_email_smtp_port', 25),
					'username' => $this->okt['request']->request->get('p_email_smtp_username'),
					'password' => $this->okt['request']->request->get('p_email_smtp_password')
				],
				'sendmail' => $this->okt['request']->request->get('p_email_sendmail')
			]
		]);
	}

	protected function seoHandleRequest()
	{
		$this->aPageData['values'] = array_merge($this->aPageData['values'], [
			'title_tag' => $this->okt['request']->request->get('p_title_tag', []),
			'meta_description' => $this->okt['request']->request->get('p_meta_description', []),
			'meta_keywords' => $this->okt['request']->request->get('p_meta_keywords', [])
		]);
	}
}
