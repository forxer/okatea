<?php
/**
 * @ingroup okt_module_galleries
 * @brief Controller public.
 *
 */
use Okatea\Tao\Html\Modifiers;
use Okatea\Website\Controller;

class GalleriesController extends Controller
{

	/**
	 * Affichage de la liste des galeries.
	 */
	public function galleriesList()
	{
		# module actuel
		$this->page->module = 'galleries';
		$this->page->action = 'list';
		
		# Récupération de la liste des galeries à la racine
		$this->rsGalleriesList = $this->okt->galleries->tree->getGalleries(array(
			'active' => 1,
			'parent_id' => 0,
			'language' => $this->okt->user->language
		));
		
		# formatage des données avant affichage
		$this->okt->galleries->tree->prepareGalleries($this->rsGalleriesList);
		
		# meta description
		if (! empty($this->okt->galleries->config->meta_description[$this->okt->user->language]))
		{
			$this->page->meta_description = $this->okt->galleries->config->meta_description[$this->okt->user->language];
		}
		else
		{
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}
		
		# meta keywords
		if (! empty($this->okt->galleries->config->meta_keywords[$this->okt->user->language]))
		{
			$this->page->meta_keywords = $this->okt->galleries->config->meta_keywords[$this->okt->user->language];
		}
		else
		{
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}
		
		# fil d'ariane
		if (! $this->isHomePageRoute())
		{
			$this->page->breadcrumb->add($this->okt->galleries->getName(), GalleriesHelpers::getGalleriesUrl());
		}
		
		# title tag du module
		$this->page->addTitleTag($this->okt->galleries->getTitle());
		
		# titre de la page
		$this->page->setTitle($this->okt->galleries->getName());
		
		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->galleries->getNameSeo());
		
		# affichage du template
		return $this->render('galleries/list/' . $this->okt->galleries->config->templates['list']['default'] . '/template', array(
			'rsGalleriesList' => $this->rsGalleriesList
		));
	}

	/**
	 * Affichage d'une galerie.
	 */
	public function galleriesGallery()
	{
		# module actuel
		$this->page->module = 'galleries';
		$this->page->action = 'gallery';
		
		# récupération de la galerie en fonction du slug
		if (! $slug = $this->request->attributes->get('slug'))
		{
			return $this->serve404();
		}
		
		# récupération de la galerie
		$this->rsGallery = $this->okt->galleries->tree->getGalleries(array(
			'slug' => $slug,
			'active' => 1,
			'language' => $this->okt->user->language
		));
		
		if ($this->rsGallery->isEmpty())
		{
			return $this->serve404();
		}
		
		# formatage des données avant affichage
		$this->okt->galleries->tree->prepareGallery($this->rsGallery);
		
		# un mot de passe ?
		$this->bGalleryRequirePassword = false;
		if (! empty($this->rsGallery->password))
		{
			# il y a un mot de passe en session
			if ($this->okt['session']->has('okt_gallery_password_' . $this->rsGallery->id))
			{
				if ($this->okt['session']->get('okt_gallery_password_' . $this->rsGallery->id) != $this->rsGallery->password)
				{
					$this->okt->error->set('Le mot de passe ne correspond pas à celui de la galerie.');
					$this->bGalleryRequirePassword = true;
				}
			}
			
			# ou il y a un mot de passe venant du formulaire
			elseif (! empty($_POST['okt_gallery_password']))
			{
				$p_password = trim($_POST['okt_gallery_password']);
				
				if ($p_password != $this->rsGallery->password)
				{
					$this->okt->error->set('Le mot de passe ne correspond pas à celui de la galerie.');
					$this->bGalleryRequirePassword = true;
				}
				else
				{
					$this->okt['session']->set('okt_gallery_password_' . $this->rsGallery->id, $p_password);
					return $this->redirect(html::escapeHTML($this->rsGallery->getGalleryUrl()));
				}
			}
			
			# sinon on doit afficher le formulaire
			else
			{
				$this->bGalleryRequirePassword = true;
			}
		}
		
		# Récupération de la liste des sous-galeries
		$this->rsSubGalleriesList = $this->okt->galleries->tree->getGalleries(array(
			'active' => 1,
			'parent_id' => $this->rsGallery->id,
			'language' => $this->okt->user->language
		));
		
		# formatage des données avant affichage
		$this->okt->galleries->tree->prepareGalleries($this->rsSubGalleriesList);
		
		# Récupération des éléments de la galerie
		$this->rsItems = $this->okt->galleries->items->getItems(array(
			'gallery_id' => $this->rsGallery->id,
			'active' => 1,
			'language' => $this->okt->user->language
		));
		
		# meta description
		if (! empty($this->rsGallery->meta_description))
		{
			$this->page->meta_description = $this->rsGallery->meta_description;
		}
		elseif (! empty($this->okt->galleries->config->meta_description[$this->okt->user->language]))
		{
			$this->page->meta_description = $this->okt->galleries->config->meta_description[$this->okt->user->language];
		}
		else
		{
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}
		
		# meta keywords
		if (! empty($this->rsGallery->meta_keywords))
		{
			$this->page->meta_description = $this->rsGallery->meta_keywords;
		}
		elseif (! empty($this->okt->galleries->config->meta_keywords[$this->okt->user->language]))
		{
			$this->page->meta_keywords = $this->okt->galleries->config->meta_keywords[$this->okt->user->language];
		}
		else
		{
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}
		
		# title tag
		$this->page->addTitleTag((! empty($this->rsGallery->title_tag) ? $this->rsGallery->title_tag : $this->rsGallery->title));
		
		# fil d'ariane
		if (! $this->isHomePageRoute())
		{
			$this->page->breadcrumb->add($this->okt->galleries->getName(), GalleriesHelpers::getGalleriesUrl());
			
			$rsPath = $this->okt->galleries->tree->getPath($this->rsGallery->id, true, $this->okt->user->language);
			while ($rsPath->fetch())
			{
				$this->page->breadcrumb->add($rsPath->title, GalleriesHelpers::getGalleryUrl($rsPath->slug));
			}
		}
		
		# titre de la page
		$this->page->setTitle($this->rsGallery->title);
		
		# titre SEO de la page
		$this->page->setTitleSeo($this->rsGallery->title_seo);
		
		# affichage du template
		return $this->render('galleries/gallery/' . $this->okt->galleries->config->templates['gallery']['default'] . '/template', array(
			'bGalleryRequirePassword' => $this->bGalleryRequirePassword,
			'rsGallery' => $this->rsGallery,
			'rsSubGalleries' => $this->rsSubGalleriesList,
			'rsItems' => $this->rsItems
		));
	}

	/**
	 * Affichage d'un élément.
	 */
	public function galleriesItem()
	{
		# récupération de l'élément en fonction du slug
		if (! $slug = $this->request->attributes->get('slug'))
		{
			return $this->serve404();
		}
		
		# récupération de l'élément
		$this->rsItem = $this->okt->galleries->items->getItems(array(
			'slug' => $slug,
			'active' => 1,
			'language' => $this->okt->user->language
		));
		
		if ($this->rsItem->isEmpty())
		{
			return $this->serve404();
		}
		
		# module actuel
		$this->page->module = 'galleries';
		$this->page->action = 'item';
		
		if (empty($this->okt->galleries->config->enable_rte) && ! empty($this->rsItem->legend))
		{
			$this->rsItem->legend = Modifiers::nlToP($this->rsItem->legend);
		}
		
		# title tag
		$this->page->addTitleTag($this->okt->galleries->getTitle());
		
		if ($this->rsItem->title_tag == '')
		{
			$this->rsItem->title_tag = $this->rsItem->title;
		}
		
		$this->page->addTitleTag($this->rsItem->title_tag);
		
		# meta description
		if (! empty($this->rsItem->meta_description))
		{
			$this->page->meta_description = $this->rsItem->meta_description;
		}
		elseif (! empty($this->okt->galleries->config->meta_description[$this->okt->user->language]))
		{
			$this->page->meta_description = $this->okt->galleries->config->meta_description[$this->okt->user->language];
		}
		else
		{
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}
		
		# meta keywords
		if (! empty($this->rsItem->meta_keywords))
		{
			$this->page->meta_keywords = $this->rsItem->meta_keywords;
		}
		elseif (! empty($this->okt->galleries->config->meta_keywords[$this->okt->user->language]))
		{
			$this->page->meta_keywords = $this->okt->galleries->config->meta_keywords[$this->okt->user->language];
		}
		else
		{
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}
		
		# fil d'ariane
		if (! $this->isHomePageRoute())
		{
			$this->page->breadcrumb->add($this->okt->galleries->getName(), GalleriesHelpers::getGalleriesUrl());
			
			$rsPath = $this->okt->galleries->tree->getPath($this->rsItem->gallery_id, true, $this->okt->user->language);
			while ($rsPath->fetch())
			{
				$this->page->addTitleTag($rsPath->title);
				
				$this->page->breadcrumb->add($rsPath->title, GalleriesHelpers::getGalleryUrl($rsPath->slug));
			}
			
			$this->page->breadcrumb->add($this->rsItem->title, $this->rsItem->getItemUrl());
		}
		
		# titre de la page
		$this->page->setTitle($this->rsItem->title);
		
		# titre SEO de la page
		$this->page->setTitleSeo(! empty($this->rsItem->title_seo) ? $this->rsItem->title_seo : $this->rsItem->title);
		
		# affichage du template
		return $this->render('galleries/item/' . $this->okt->galleries->config->templates['item']['default'] . '/template', array(
			'rsItem' => $this->rsItem
		));
	}
}
