<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin\Controller\Config;

use Tao\Admin\Controller;
use Tao\Misc\Utilities;

class General extends Controller
{
	protected $aPageData;

	public function page()
	{
		if (!$this->okt->checkPerm('configsite')) {
			return $this->serve401();
		}

		# Locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$this->okt->user->language.'/admin.site');

		# Données de la page
		$this->aPageData = new \ArrayObject();
		$this->aPageData['aNewConf'] = array();

		# -- TRIGGER CORE CONFIG SITE PAGE : adminConfigSiteInit
		$this->okt->triggers->callTrigger('adminConfigSiteInit', $this->okt, $this->aPageData);

		$this->generalHandleRequest();

		$this->companyHandleRequest();

		$this->emailsHandleRequest();

		$this->seoHandleRequest();

		# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminConfigSiteHandleRequest
		$this->okt->triggers->callTrigger('adminConfigSiteHandleRequest', $this->okt, $this->aPageData);

		# save configuration
		if ($this->request->request->has('form_sent') && $this->okt->error->isEmpty())
		{
			try
			{
				$this->okt->config->write($this->aPageData['aNewConf']);

				$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('config_general'));
			}
			catch (InvalidArgumentException $e)
			{
				$this->okt->error->set(__('c_c_error_writing_configuration'));
				$this->okt->error->set($e->getMessage());
			}
		}

		# Construction des onglets
		$this->aPageData['tabs'] = new \ArrayObject;

		# onglet général
		$this->aPageData['tabs'][10] = array(
			'id' => 'tab_general',
			'title' => __('c_a_config_tab_general'),
			'content' => $this->renderView('Config/General/Tabs/General')
		);

		# onglet société
		$this->aPageData['tabs'][20] = array(
			'id' => 'tab_company',
			'title' => __('c_a_config_tab_company'),
			'content' => $this->renderView('Config/General/Tabs/Company')
		);

		# onglet emails
		$this->aPageData['tabs'][30] = array(
			'id' => 'tab_emails',
			'title' => __('c_a_config_tab_email'),
			'content' => $this->renderView('Config/General/Tabs/Emails')
		);

		# onglet seo
		$this->aPageData['tabs'][40] = array(
			'id' => 'tab_seo',
			'title' => __('c_a_config_tab_seo'),
			'content' => $this->renderView('Config/General/Tabs/Seo')
		);

		# -- TRIGGER CORE ADVANCED CONFIG PAGE : adminConfigSiteBuildTabs
		$this->okt->triggers->callTrigger('adminConfigSiteBuildTabs', $this->okt, $this->aPageData);

		$this->aPageData['tabs']->ksort();

		return $this->render('Config/General/Page', array(
			'aPageData' => $this->aPageData
		));
	}

	protected function generalHandleRequest()
	{
		if (!$this->request->request->has('form_sent')) {
			return null;
		}

		$p_title = !empty($_POST['p_title']) && is_array($_POST['p_title']) ? $_POST['p_title'] : array();
		$p_desc = !empty($_POST['p_desc']) && is_array($_POST['p_desc'])  ? $_POST['p_desc'] : array();

		foreach ($p_title as $sLanguageCode=>$sTitle)
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

		$this->aPageData['aNewConf'] = array_merge($this->aPageData['aNewConf'], array(
			'title' => $p_title,
			'desc' => $p_desc
		));
	}

	protected function companyHandleRequest()
	{
		if (!$this->request->request->has('form_sent')) {
			return null;
		}

		$p_company_name = !empty($_POST['p_company_name']) ? $_POST['p_company_name'] : '';
		$p_company_com_name = !empty($_POST['p_company_com_name']) ? $_POST['p_company_com_name'] : '';
		$p_company_siret = !empty($_POST['p_company_siret']) ? $_POST['p_company_siret'] : '';

		$p_schedule = !empty($_POST['p_schedule']) && is_array($_POST['p_schedule']) ? $_POST['p_schedule'] : array();

		$p_leader_name = !empty($_POST['p_leader_name']) ? $_POST['p_leader_name'] : '';
		$p_leader_firstname = !empty($_POST['p_leader_firstname']) ? $_POST['p_leader_firstname'] : '';

		$p_address_street = !empty($_POST['p_address_street']) ? $_POST['p_address_street'] : '';
		$p_address_street_2 = !empty($_POST['p_address_street']) ? $_POST['p_address_street_2'] : '';
		$p_address_code = !empty($_POST['p_address_code']) ? $_POST['p_address_code'] : '';
		$p_address_city = !empty($_POST['p_address_city']) ? $_POST['p_address_city'] : '';
		$p_address_country = !empty($_POST['p_address_country']) ? $_POST['p_address_country'] : '';
		$p_address_tel = !empty($_POST['p_address_tel']) ? $_POST['p_address_tel'] : '';
		$p_address_mobile = !empty($_POST['p_address_mobile']) ? $_POST['p_address_mobile'] : '';
		$p_address_fax = !empty($_POST['p_address_fax']) ? $_POST['p_address_fax'] : '';

		$p_gps_lat = !empty($_POST['p_gps_lat']) ? $_POST['p_gps_lat'] : '';
		$p_gps_long = !empty($_POST['p_gps_long']) ? $_POST['p_gps_long'] : '';

		$this->aPageData['aNewConf'] = array_merge($this->aPageData['aNewConf'], array(
			'company' 			 => array(
					'name' 				=> $p_company_name,
					'com_name' 			=> $p_company_com_name,
					'siret' 			=> $p_company_siret
			),
			'schedule' 			 => $p_schedule,
			'leader' 			 => array(
					'name' 				=> $p_leader_name,
					'firstname' 		=> $p_leader_firstname
			),
			'address' 			 => array(
					'street' 			=> $p_address_street,
					'street_2' 			=> $p_address_street_2,
					'code' 				=> $p_address_code,
					'city' 				=> $p_address_city,
					'country'			=> $p_address_country,
					'tel'				=> $p_address_tel,
					'mobile'			=> $p_address_mobile,
					'fax'				=> $p_address_fax
			),
			'gps' 			 	=> array(
					'lat' 			=> $p_gps_lat,
					'long' 			=> $p_gps_long,
			)
		));
	}

	protected function emailsHandleRequest()
	{
		if (!$this->request->request->has('form_sent')) {
			return null;
		}

		$p_email_to = !empty($_POST['p_email_to']) ? $_POST['p_email_to'] : '';
		if (empty($p_email_to)) {
			$this->okt->error->set(__('c_a_config_please_enter_email_to'));
		}
		elseif (!Utilities::isEmail($p_email_to)) {
			$this->okt->error->set(sprintf(__('c_c_error_invalid_email'), \html::escapeHTML($p_email_to)));
		}

		$p_email_from = !empty($_POST['p_email_from']) ? $_POST['p_email_from'] : '';
		if (empty($p_email_from)) {
			$this->okt->error->set(__('c_a_config_please_enter_email_from'));
		}
		elseif (!Utilities::isEmail($p_email_from)) {
			$this->okt->error->set(sprintf(__('c_c_error_invalid_email'), \html::escapeHTML($p_email_from)));
		}

		$p_email_name = !empty($_POST['p_email_name']) ? $_POST['p_email_name'] : '';
		$p_email_transport = !empty($_POST['p_email_transport']) ? $_POST['p_email_transport'] : 'mail';

		$p_email_smtp_host = !empty($_POST['p_email_smtp_host']) ? $_POST['p_email_smtp_host'] : '';
		$p_email_smtp_port = !empty($_POST['p_email_smtp_port']) ? intval($_POST['p_email_smtp_port']) : 25;
		$p_email_smtp_username = !empty($_POST['p_email_smtp_username']) ? $_POST['p_email_smtp_username'] : '';
		$p_email_smtp_password = !empty($_POST['p_email_smtp_password']) ? $_POST['p_email_smtp_password'] : '';

		$p_email_sendmail = !empty($_POST['p_email_sendmail']) ? $_POST['p_email_sendmail'] : '';

		$this->aPageData['aNewConf'] = array_merge($this->aPageData['aNewConf'], array(
			'email' => array(
				'to' => $p_email_to,
				'from' => $p_email_from,
				'name' => $p_email_name,
				'transport' => $p_email_transport,
				'smtp' => array(
					'host' => $p_email_smtp_host,
					'port' => (integer)$p_email_smtp_port,
					'username' => $p_email_smtp_username,
					'password' => $p_email_smtp_password
				),
				'sendmail' => $p_email_sendmail
			)
		));
	}

	protected function seoHandleRequest()
	{
		if (!$this->request->request->has('form_sent')) {
			return null;
		}

		$p_title_tag = !empty($_POST['p_title_tag']) && is_array($_POST['p_title_tag'])  ? $_POST['p_title_tag'] : array();
		$p_meta_description = !empty($_POST['p_meta_description']) && is_array($_POST['p_meta_description'])  ? $_POST['p_meta_description'] : array();
		$p_meta_keywords = !empty($_POST['p_meta_keywords']) && is_array($_POST['p_meta_keywords'])  ? $_POST['p_meta_keywords'] : array();

		$this->aPageData['aNewConf'] = array_merge($this->aPageData['aNewConf'], array(
			'title_tag' 		 => $p_title_tag,
			'meta_description' 	 => $p_meta_description,
			'meta_keywords' 	 => $p_meta_keywords
		));
	}
}
