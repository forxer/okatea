<?php
/**
 * @ingroup okt_module_estimate
 * @brief Controller public.
 *
 */
use Okatea\Tao\Misc\Utilities;
use Okatea\Website\Controller;
use Okatea\Tao\Misc\Mailer;

class EstimateController extends Controller
{

	protected $aFormData = array();

	/**
	 * Affichage de la page de récapitulatif de demande de devis.
	 */
	public function estimateSummary()
	{
		# module actuel
		$this->page->module = 'estimate';
		$this->page->action = 'summary';
		
		# si on as pas de données en session on renvoi sur le formulaire
		if (empty($_SESSION['okt_mod_estimate_form_data']))
		{
			return $this->redirect(EstimateHelpers::getFormUrl());
		}
		
		# récupération des produits et des accessoires
		$rsProducts = $this->okt->estimate->products->getProducts();
		$aProducts = array();
		$aProductsAccessories = array();
		
		while ($rsProducts->fetch())
		{
			$aProducts[$rsProducts->id] = html::escapeHTML($rsProducts->title);
			
			if ($this->okt->estimate->config->enable_accessories)
			{
				$rsAccessories = $this->okt->estimate->accessories->getAccessories(array(
					'product_id' => $rsProducts->id
				));
				
				if (! $rsAccessories->isEmpty())
				{
					$aProductsAccessories[$rsProducts->id] = array();
					while ($rsAccessories->fetch())
					{
						$aProductsAccessories[$rsProducts->id][$rsAccessories->id] = html::escapeHTML($rsAccessories->title);
					}
				}
				
				unset($rsAccessories);
			}
		}
		
		unset($rsProducts);
		
		# formatage des données
		$aFormatedData = $_SESSION['okt_mod_estimate_form_data'];
		
		unset($aFormatedData['products'], $aFormatedData['product_quantity'], $aFormatedData['accessories'], $aFormatedData['accessory_quantity']);
		
		foreach ($_SESSION['okt_mod_estimate_form_data']['products'] as $iProductCounter => $iProductId)
		{
			$aFormatedData['products'][$iProductCounter] = array(
				'title' => $aProducts[$iProductId],
				'quantity' => $_SESSION['okt_mod_estimate_form_data']['product_quantity'][$iProductCounter],
				'accessories' => array()
			);
			
			if (! empty($_SESSION['okt_mod_estimate_form_data']['accessories'][$iProductCounter]))
			{
				foreach ($_SESSION['okt_mod_estimate_form_data']['accessories'][$iProductCounter] as $iAccessoryCounter => $iAccessoryId)
				{
					$aFormatedData['products'][$iProductCounter]['accessories'][$iAccessoryCounter] = array(
						'title' => $aProductsAccessories[$iProductId][$iAccessoryId],
						'quantity' => $_SESSION['okt_mod_estimate_form_data']['accessory_quantity'][$iProductCounter][$iAccessoryCounter]
					);
				}
			}
		}
		
		# enregistrement de la demande
		if (! empty($_GET['send']))
		{
			if (($iEstimateId = $this->okt->estimate->addEstimate($aFormatedData)) !== false)
			{
				unset($_SESSION['okt_mod_estimate_form_data']);
				
				# notifications
				if ($this->okt->estimate->config->enable_notifications)
				{
					$aRecipients = array();
					
					if (! empty($this->okt->estimate->config->notifications_recipients))
					{
						$aRecipients = array_map('trim', explode(',', $this->okt->estimate->config->notifications_recipients));
					}
					
					if (empty($aRecipients))
					{
						if (! empty($this->config->email['name']))
						{
							$aRecipients = array(
								$this->okt['config']->email['to'] => html::escapeHTML($this->config->email['name'])
							);
						}
						else
						{
							$aRecipients = array(
								$this->okt['config']->email['to']
							);
						}
					}
					
					# construction du mail
					$sEstimateUrl = $this->okt['request']->getSchemeAndHttpHost() . $this->okt['app_url'] . 'admin/module.php?m=estimate&action=estimate&estimate_id=' . $iEstimateId;
					
					$oMail = new Mailer($this->okt);
					$oMail->setFrom();
					$oMail->message->setTo($aRecipients);
					
					$oMail->useFile(dirname(__FILE__) . '/../Locales/' . $this->okt['visitor']->language . '/mails_tpl/admin_notification.tpl', array(
						'SITE_TITLE' => html::escapeHTML($this->page->getSiteTitle()),
						'USER_FIRSTNAME' => $aFormatedData['firstname'],
						'USER_LASTNAME' => $aFormatedData['lastname'],
						'ADMIN_ESTIMATE_URL' => html::escapeHTML($sEstimateUrl)
					));
					
					$oMail->send();
				}
				
				return $this->redirect(EstimateHelpers::getFormUrl() . '?added=1');
			}
		}
		
		# meta description
		if (! empty($this->okt->estimate->config->meta_description[$this->okt['visitor']->language]))
		{
			$this->page->meta_description = $this->okt->estimate->config->meta_description[$this->okt['visitor']->language];
		}
		else
		{
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}
		
		# meta keywords
		if (! empty($this->okt->estimate->config->meta_keywords[$this->okt['visitor']->language]))
		{
			$this->page->meta_keywords = $this->okt->estimate->config->meta_keywords[$this->okt['visitor']->language];
		}
		else
		{
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}
		
		# title tag du module
		$this->page->addTitleTag($this->okt->estimate->getTitle());
		
		# fil d'ariane
		if (! $this->isHomePageRoute())
		{
			$this->page->breadcrumb->add($this->okt->estimate->getName(), EstimateHelpers::getFormUrl());
		}
		
		# titre de la page
		$this->page->setTitle($this->okt->estimate->getName());
		
		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->estimate->getNameSeo());
		
		# affichage du template
		return $this->render('Estimate/summary/' . $this->okt->estimate->config->templates['summary']['default'] . '/template', array(
			'aEstimateData' => $aFormatedData
		));
	}

	/**
	 * Affichage de la page du formulaire de demande de devis.
	 */
	public function estimateForm()
	{
		# module actuel
		$this->page->module = 'estimate';
		$this->page->action = 'form';
		
		# -- CORE TRIGGER : publicModuleEstimateControllerStart
		$this->okt['triggers']->callTrigger('publicModuleEstimateControllerStart', $this->okt->estimate->config->captcha);
		
		# récupération des produits et des accessoires
		$rsProducts = $this->okt->estimate->products->getProducts();
		
		$aProductsSelect = array(
			' ' => null
		);
		$aProductsAccessories = array();
		
		while ($rsProducts->fetch())
		{
			$aProductsSelect[html::escapeHTML($rsProducts->title)] = $rsProducts->id;
			
			if ($this->okt->estimate->config->enable_accessories)
			{
				$rsAccessories = $this->okt->estimate->accessories->getAccessories(array(
					'product_id' => $rsProducts->id
				));
				
				if (! $rsAccessories->isEmpty())
				{
					$aProductsAccessories[$rsProducts->id] = array();
					$aProductsAccessories[$rsProducts->id][0] = ' ';
					while ($rsAccessories->fetch())
					{
						$aProductsAccessories[$rsProducts->id][$rsAccessories->id] = html::escapeHTML($rsAccessories->title);
					}
				}
				
				unset($rsAccessories);
			}
		}
		
		# données de formulaire envoyées
		$this->aFormData = array(
			'lastname' => '',
			'firstname' => '',
			'email' => '',
			'phone' => '',
			'start_date' => '',
			'end_date' => '',
			'products' => array(),
			'product_quantity' => array(),
			'accessories' => array(),
			'accessory_quantity' => array(),
			'comment' => ''
		);
		
		# retour de la page de récapitulatif ?
		if (! empty($_SESSION['okt_mod_estimate_form_data']))
		{
			$this->aFormData = $_SESSION['okt_mod_estimate_form_data'];
			unset($_SESSION['okt_mod_estimate_form_data']);
		}
		# ou formulaire envoyé ?
		elseif (! empty($_POST['sended']))
		{
			$this->aFormData = array(
				'lastname' => ! empty($_POST['p_lastname']) ? $_POST['p_lastname'] : '',
				'firstname' => ! empty($_POST['p_firstname']) ? $_POST['p_firstname'] : '',
				'email' => ! empty($_POST['p_email']) ? $_POST['p_email'] : '',
				'phone' => ! empty($_POST['p_phone']) ? $_POST['p_phone'] : '',
				'start_date' => ! empty($_POST['p_start_date']) ? $_POST['p_start_date'] : '',
				'end_date' => ! empty($_POST['p_end_date']) ? $_POST['p_end_date'] : '',
				'products' => ! empty($_POST['p_product']) && is_array($_POST['p_product']) ? $_POST['p_product'] : array(),
				'product_quantity' => ! empty($_POST['p_product_quantity']) && is_array($_POST['p_product_quantity']) ? $_POST['p_product_quantity'] : array(),
				'accessories' => ! empty($_POST['p_accessory']) && is_array($_POST['p_accessory']) ? $_POST['p_accessory'] : array(),
				'accessory_quantity' => ! empty($_POST['p_accessory_quantity']) && is_array($_POST['p_accessory_quantity']) ? $_POST['p_accessory_quantity'] : array(),
				'comment' => ! empty($_POST['p_comment']) ? $_POST['p_comment'] : ''
			);
			
			# rebuild products and accessories arrays
			$aTempData = array(
				'products' => array(),
				'product_quantity' => array(),
				'accessories' => array(),
				'accessory_quantity' => array()
			);
			
			$iTempProductCounter = 1;
			foreach ($this->aFormData['products'] as $iProductCounter => $iProductId)
			{
				if (! empty($iProductId) && ! empty($this->aFormData['product_quantity'][$iProductCounter]))
				{
					$aTempData['products'][$iTempProductCounter] = $iProductId;
					$aTempData['product_quantity'][$iTempProductCounter] = $this->aFormData['product_quantity'][$iProductCounter];
					
					if (! empty($this->aFormData['accessories'][$iProductCounter]))
					{
						$iTempAccessoryCounter = 1;
						
						foreach ($this->aFormData['accessories'][$iProductCounter] as $iAccessoryCounter => $iAccessoryId)
						{
							if (! empty($iAccessoryId) && ! empty($this->aFormData['accessory_quantity'][$iProductCounter][$iAccessoryCounter]))
							{
								$aTempData['accessories'][$iTempProductCounter][$iTempAccessoryCounter] = $iAccessoryId;
								$aTempData['accessory_quantity'][$iTempProductCounter][$iTempAccessoryCounter] = $this->aFormData['accessory_quantity'][$iProductCounter][$iAccessoryCounter];
								
								$iTempAccessoryCounter ++;
							}
						}
					}
					
					$iTempProductCounter ++;
				}
			}
			
			$this->aFormData['products'] = $aTempData['products'];
			$this->aFormData['product_quantity'] = $aTempData['product_quantity'];
			$this->aFormData['accessories'] = $aTempData['accessories'];
			$this->aFormData['accessory_quantity'] = $aTempData['accessory_quantity'];
			
			if (empty($this->aFormData['lastname']))
			{
				$this->okt->error->set(__('m_estimate_must_enter_lastname'));
			}
			
			if (empty($this->aFormData['firstname']))
			{
				$this->okt->error->set(__('m_estimate_must_enter_firstname'));
			}
			
			if (empty($this->aFormData['email']))
			{
				$this->okt->error->set(__('m_estimate_must_enter_email'));
			}
			elseif (! Utilities::isEmail($this->aFormData['email']))
			{
				$this->okt->error->set(__('m_estimate_must_enter_validate_email'));
			}
			
			if (empty($this->aFormData['start_date']))
			{
				$this->okt->error->set(__('m_estimate_must_enter_start_date'));
			}
			
			if (empty($this->aFormData['products']))
			{
				$this->okt->error->set(__('m_estimate_must_enter_at_least_one_product'));
			}
			
			# -- CORE TRIGGER : publicModuleEstimateControllerFormCheckValues
			$this->okt['triggers']->callTrigger('publicModuleEstimateControllerFormCheckValues', $this->okt->estimate->config->captcha);
			
			if (! $this->okt['flash']->hasError())
			{
				$_SESSION['okt_mod_estimate_form_data'] = $this->aFormData;
				return $this->redirect(EstimateHelpers::getSummaryUrl());
			}
		}
		
		# pré-remplissage des données utilisateur si loggué
		if (! $this->okt['visitor']->is_guest)
		{
			if (empty($this->aFormData['lastname']))
			{
				$this->aFormData['lastname'] = $this->okt['visitor']->lastname;
			}
			
			if (empty($this->aFormData['firstname']))
			{
				$this->aFormData['firstname'] = $this->okt['visitor']->firstname;
			}
			
			if (empty($this->aFormData['email']))
			{
				$this->aFormData['email'] = $this->okt['visitor']->email;
			}
		}
		
		# meta description
		if (! empty($this->okt->estimate->config->meta_description[$this->okt['visitor']->language]))
		{
			$this->page->meta_description = $this->okt->estimate->config->meta_description[$this->okt['visitor']->language];
		}
		else
		{
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}
		
		# meta keywords
		if (! empty($this->okt->estimate->config->meta_keywords[$this->okt['visitor']->language]))
		{
			$this->page->meta_keywords = $this->okt->estimate->config->meta_keywords[$this->okt['visitor']->language];
		}
		else
		{
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}
		
		# title tag du module
		$this->page->addTitleTag($this->okt->estimate->getTitle());
		
		# fil d'ariane
		if (! $this->isHomePageRoute())
		{
			$this->page->breadcrumb->add($this->okt->estimate->getName(), EstimateHelpers::getFormUrl());
		}
		
		# titre de la page
		$this->page->setTitle($this->okt->estimate->getName());
		
		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->estimate->getNameSeo());
		
		# affichage du template
		return $this->render('Estimate/form/' . $this->okt->estimate->config->templates['form']['default'] . '/template', array(
			'aFormData' => $this->aFormData,
			'rsProducts' => $rsProducts,
			'aProductsSelect' => $aProductsSelect,
			'aProductsAccessories' => $aProductsAccessories,
			'iNumProducts' => $this->getFormNumProducts()
		));
	}

	protected function getFormNumProducts()
	{
		$iNumProducts = count($this->aFormData['products']);
		
		if ($iNumProducts < $this->okt->estimate->config->default_products_number)
		{
			$iNumProducts = $this->okt->estimate->config->default_products_number;
		}
		
		return $iNumProducts;
	}
}
