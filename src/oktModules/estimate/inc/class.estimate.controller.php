<?php
/**
 * @ingroup okt_module_estimate
 * @brief Controller public.
 *
 */

class estimateController extends oktController
{
	protected $aFormData = array();

	/**
	 * Affichage de la page du formulaire de devis.
	 *
	 */
	public function estimatePage()
	{
		# module actuel
		$this->okt->page->module = 'estimate';
		$this->okt->page->action = 'form';

		# est-ce qu'on demande une langue demandée
		if (($sRequestLanguage = $this->setUserRequestLanguage()) !== false) {
			http::redirect($this->okt->page->getBaseUrl($sRequestLanguage).$this->okt->estimate->config->public_estimate_url[$sRequestLanguage]);
		}

		# récupération des produits et des accessoires
		$rsProducts = $this->okt->estimate->products->getProducts();

		$aProductsSelect = array(' ' => null);
		$aProductsAccessories = array();

		while ($rsProducts->fetch())
		{
			$aProductsSelect[html::escapeHTML($rsProducts->title)] = $rsProducts->id;

			$rsAccessories = $this->okt->estimate->accessories->getAccessories(array(
				'product_id' => $rsProducts->id
			));

			if (!$rsAccessories->isEmpty())
			{
				$aProductsAccessories[$rsProducts->id] = array();
				$aProductsAccessories[$rsProducts->id][0] = ' ';
				while ($rsAccessories->fetch()) {
					$aProductsAccessories[$rsProducts->id][$rsAccessories->id] = html::escapeHTML($rsAccessories->title);
				}
			}

			unset($rsAccessories);
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

		# formulaire envoyé
		if (!empty($_POST['sended']))
		{
			$this->aFormData = array(
				'lastname' => !empty($_POST['p_lastname']) ? $_POST['p_lastname'] : '',
				'firstname' => !empty($_POST['p_firstname']) ? $_POST['p_firstname'] : '',
				'email' => !empty($_POST['p_email']) ? $_POST['p_email'] : '',
				'phone' => !empty($_POST['p_phone']) ? $_POST['p_phone'] : '',
				'start_date' => !empty($_POST['p_start_date']) ? $_POST['p_start_date'] : '',
				'end_date' => !empty($_POST['p_end_date']) ? $_POST['p_end_date'] : '',
				'products' => !empty($_POST['p_product']) && is_array($_POST['p_product']) ? $_POST['p_product'] : array(),
				'product_quantity' => !empty($_POST['p_product_quantity']) && is_array($_POST['p_product_quantity']) ? $_POST['p_product_quantity'] : array(),
				'accessories' => !empty($_POST['p_accessory']) && is_array($_POST['p_accessory']) ? $_POST['p_accessory'] : array(),
				'accessory_quantity' => !empty($_POST['p_accessory_quantity']) && is_array($_POST['p_accessory_quantity']) ? $_POST['p_accessory_quantity'] : array(),
				'comment' => !empty($_POST['p_comment']) ? $_POST['p_comment'] : ''
			);


			# rebuild products and accessories arrays
			$aTempData = array(
				'products' => array(),
				'product_quantity' => array(),
				'accessories' => array(),
				'accessory_quantity' => array()
			);

			$iTempProductCounter = 1;
			foreach ($this->aFormData['products'] as $iProductCounter=>$iProductId)
			{
				if (!empty($iProductId) && !empty($this->aFormData['product_quantity'][$iProductCounter]))
				{
					$aTempData['products'][$iTempProductCounter] = $iProductId;
					$aTempData['product_quantity'][$iTempProductCounter] = $this->aFormData['product_quantity'][$iProductCounter];

					if (!empty($this->aFormData['accessories'][$iProductCounter]))
					{
						$iTempAccessoryCounter = 1;

						foreach ($this->aFormData['accessories'][$iProductCounter] as $iAccessoryCounter=>$iAccessoryId)
						{
							if (!empty($iAccessoryId) && !empty($this->aFormData['accessory_quantity'][$iProductCounter][$iAccessoryCounter]))
							{
								$aTempData['accessories'][$iTempProductCounter][$iTempAccessoryCounter] = $iAccessoryId;
								$aTempData['accessory_quantity'][$iTempProductCounter][$iTempAccessoryCounter] = $this->aFormData['accessory_quantity'][$iProductCounter][$iAccessoryCounter];

								$iTempAccessoryCounter++;
							}
						}
					}

					$iTempProductCounter++;
				}
			}

			$this->aFormData['products'] = $aTempData['products'];
			$this->aFormData['product_quantity'] = $aTempData['product_quantity'];
			$this->aFormData['accessories'] = $aTempData['accessories'];
			$this->aFormData['accessory_quantity'] = $aTempData['accessory_quantity'];


			if (empty($this->aFormData['lastname'])) {
				$this->okt->error->set('Veuillez saisir votre nom.');
			}

			if (empty($this->aFormData['firstname'])) {
				$this->okt->error->set('Veuillez saisir votre prénom.');
			}

			if (empty($this->aFormData['email'])) {
				$this->okt->error->set('Veuillez saisir votre adresse de courrier électronique.');
			}

			if (empty($this->aFormData['start_date'])) {
				$this->okt->error->set('Veuillez saisir une date de début.');
			}

			if (empty($this->aFormData['end_date'])) {
				$this->okt->error->set('Veuillez saisir une date de fin.');
			}

			if (empty($this->aFormData['products'])) {
				$this->okt->error->set('Veuillez choisir au moins un produit.');
			}


		}

		# pré-remplissage des données utilisateur si loggué
		if (!$this->okt->user->is_guest)
		{
			if (empty($this->aFormData['lastname'])) {
				$this->aFormData['lastname'] = $this->okt->user->lastname;
			}

			if (empty($this->aFormData['firstname'])) {
				$this->aFormData['firstname'] = $this->okt->user->firstname;
			}

			if (empty($this->aFormData['email'])) {
				$this->aFormData['email'] = $this->okt->user->email;
			}
		}

		# meta description
		if ($this->okt->estimate->config->meta_description[$this->okt->user->language] != '') {
			$this->okt->page->meta_description = $this->okt->estimate->config->meta_description[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_description = util::getSiteMetaDesc();
		}

		# meta keywords
		if ($this->okt->estimate->config->meta_keywords[$this->okt->user->language] != '') {
			$this->okt->page->meta_keywords = $this->okt->estimate->config->meta_keywords[$this->okt->user->language];
		}
		else {
			$this->okt->page->meta_keywords = util::getSiteMetaKeywords();
		}

		# title tag du module
		$this->okt->page->addTitleTag($this->okt->estimate->getTitle());

		# fil d'ariane
		if (!$this->isDefaultRoute(__CLASS__, __FUNCTION__)) {
			$this->okt->page->breadcrumb->add($this->okt->estimate->getName(), $this->okt->estimate->config->url);
		}

		# titre de la page
		$this->okt->page->setTitle($this->okt->estimate->getName());

		# titre SEO de la page
		$this->okt->page->setTitleSeo($this->okt->estimate->getNameSeo());

		# affichage du template
		echo $this->okt->tpl->render('estimate/form/'.$this->okt->estimate->config->templates['form']['default'].'/template', array(
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

		if ($iNumProducts < 2) {
			$iNumProducts = 2;
		}

		return $iNumProducts;
	}

} # class
