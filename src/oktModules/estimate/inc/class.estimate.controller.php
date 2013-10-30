<?php
/**
 * @ingroup okt_module_estimate
 * @brief Controller public.
 *
 */

class estimateController extends oktController
{
	/**
	 * Affichage de la page contact.
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
		$aFormData = array(
			'lastname' => '',
			'firstname' => '',
			'email' => '',
			'phone' => '',
			'start_date' => '',
			'end_date' => '',
			'products' => array(),
			'comment' => ''
		);

		# formulaire envoyé
		if (!empty($_POST['sended']))
		{
			$aFormData = array(
				'lastname' => !empty($_POST['p_lastname']) ? $_POST['p_lastname'] : '',
				'firstname' => !empty($_POST['p_firstname']) ? $_POST['p_firstname'] : '',
				'email' => !empty($_POST['p_email']) ? $_POST['p_email'] : '',
				'phone' => !empty($_POST['p_phone']) ? $_POST['p_phone'] : '',
				'start_date' => !empty($_POST['p_start_date']) ? $_POST['p_start_date'] : '',
				'end_date' => !empty($_POST['p_end_date']) ? $_POST['p_end_date'] : '',
				'comment' => !empty($_POST['p_comment']) ? $_POST['p_comment'] : ''
			);

			if (empty($aFormData['lastname'])) {
				$this->okt->error->set('Veuillez saisir votre nom.');
			}

			if (empty($aFormData['firstname'])) {
				$this->okt->error->set('Veuillez saisir votre prénom.');
			}

			if (empty($aFormData['email'])) {
				$this->okt->error->set('Veuillez saisir votre adresse de courrier électronique.');
			}

			if (empty($aFormData['start_date'])) {
				$this->okt->error->set('Veuillez saisir une date de début.');
			}

			if (empty($aFormData['end_date'])) {
				$this->okt->error->set('Veuillez saisir une date de fin.');
			}
		}

		# pré-remplissage des données utilisateur si loggué
		if (!$this->okt->user->is_guest)
		{
			if (empty($aFormData['lastname'])) {
				$aFormData['lastname'] = $this->okt->user->lastname;
			}

			if (empty($aFormData['firstname'])) {
				$aFormData['firstname'] = $this->okt->user->firstname;
			}

			if (empty($aFormData['email'])) {
				$aFormData['email'] = $this->okt->user->email;
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
			'aFormData' => $aFormData,
			'rsProducts' => $rsProducts,
			'aProductsSelect' => $aProductsSelect,
			'aProductsAccessories' => $aProductsAccessories
		));
	}

} # class
