<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Pages;

use Okatea\Tao\Html\Modifiers;
use Okatea\Website\Controller as BaseController;
use Okatea\Website\Pager;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{

	/**
	 * Affichage de la liste des pages classique.
	 */
	public function pagesList()
	{
		# permission de lecture ?
		if (! $this->okt->module('Pages')->isPublicAccessible())
		{
			if ($this->okt['visitor']->is_guest)
			{
				return $this->redirect($this->okt['router']->generateLoginUrl($this->generateUrl('pagesList')));
			}
			else
			{
				return $this->serve404();
			}
		}

		# initialisation paramètres
		$aPagesParams = array(
			'active' => 1,
			'language' => $this->okt['visitor']->language
		);

		$sSearch = $this->okt['request']->query->get('search');

		if ($sSearch)
		{
			$aPagesParams['search'] = $sSearch;
		}

		# initialisation des filtres
		$this->okt->module('Pages')->filtersStart('public');

		# ré-initialisation filtres
		if ($this->okt['request']->query->has('init_pages_filters'))
		{
			$this->okt->module('Pages')->filters->initFilters();
			return $this->redirect($this->generateUrl('pagesList'));
		}

		# initialisation des filtres
		$this->okt->module('Pages')->filters->setPagesParams($aPagesParams);
		$this->okt->module('Pages')->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredPages = $this->okt->module('Pages')->pages->getPagesCount($aPagesParams);

		$oPagesPager = new Pager($this->okt, $this->okt->module('Pages')->filters->params->page, $iNumFilteredPages, $this->okt->module('Pages')->filters->params->nb_per_page);

		$iNumPages = $oPagesPager->getNbPages();

		$this->okt->module('Pages')->filters->normalizePage($iNumPages);

		$aPagesParams['limit'] = (($this->okt->module('Pages')->filters->params->page - 1) * $this->okt->module('Pages')->filters->params->nb_per_page) . ',' . $this->okt->module('Pages')->filters->params->nb_per_page;

		# récupération des pages
		$this->rsPagesList = $this->okt->module('Pages')->pages->getPages($aPagesParams);

		# meta description
		if (! empty($this->okt->module('Pages')->config->meta_description[$this->okt['visitor']->language]))
		{
			$this->page->meta_description = $this->okt->module('Pages')->config->meta_description[$this->okt['visitor']->language];
		}
		else
		{
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (! empty($this->okt->module('Pages')->config->meta_keywords[$this->okt['visitor']->language]))
		{
			$this->page->meta_keywords = $this->okt->module('Pages')->config->meta_keywords[$this->okt['visitor']->language];
		}
		else
		{
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# ajout du numéro de page au title
		if ($this->okt->module('Pages')->filters->params->page > 1)
		{
			$this->page->addTitleTag(sprintf(__('c_c_Page_%s'), $this->okt->module('Pages')->filters->params->page));
		}

		# title tag du module
		$this->page->addTitleTag($this->okt->module('Pages')
			->getTitle());

		# titre de la page
		$this->page->setTitle($this->okt->module('Pages')
			->getName());

		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->module('Pages')
			->getNameSeo());

		# raccourcis
		$this->rsPagesList->numPages = $iNumPages;
		$this->rsPagesList->pager = $oPagesPager;

		# affichage du template
		return $this->render($this->okt->module('Pages')
			->getListTplPath(), array(
			'rsPagesList' => $this->rsPagesList
		));
	}

	/**
	 * Affichage du flux RSS des pages.
	 */
	public function pagesFeed()
	{
		$this->rsPagesList = $this->okt->module('Pages')->pages->getPages(array(
			'active' => 1,
			'limit' => 20
		));

		$response = Response::create();
		$response->headers->set('Content-Type', 'application/rss+xml');

		return $this->render($this->okt->module('Pages')
			->getFeedTplPath(), array(
			'rsPagesList' => $this->rsPagesList
		), $response);
	}

	/**
	 * Affichage de la liste des pages d'une rubrique.
	 */
	public function pagesCategory()
	{
		# si les rubriques ne sont pas actives -> 404
		if (! $this->okt->module('Pages')->config->categories['enable'])
		{
			return $this->serve404();
		}

		# récupération de la rubrique en fonction du slug
		if (! $sCategorySlug = $this->okt['request']->attributes->get('slug'))
		{
			return $this->serve404();
		}

		# récupération de la rubrique
		$this->rsCategory = $this->okt->module('Pages')->categories->getCategories(array(
			'active' => 1,
			'language' => $this->okt['visitor']->language,
			'slug' => $sCategorySlug
		));

		if ($this->rsCategory->isEmpty())
		{
			return $this->serve404();
		}

		# permission de lecture ?
		if (! $this->okt->module('Pages')->isPublicAccessible())
		{
			if ($this->okt['visitor']->is_guest)
			{
				return $this->redirect($this->okt['router']->generateLoginUrl($this->generateUrl('pagesCategory', array(
					'slug' => $this->rsCategory->slug
				))));
			}
			else
			{
				return $this->serve404();
			}
		}

		# formatage description rubrique
		if (! $this->okt->module('Pages')->config->categories['rte'])
		{
			$this->rsCategory->content = Modifiers::nlToP($this->rsCategory->content);
		}

		# initialisation paramètres
		$aPagesParams = array(
			'active' => 1,
			'language' => $this->okt['visitor']->language,
			'category_id' => $this->rsCategory->id
		);

		# initialisation des filtres
		$this->okt->module('Pages')->filtersStart('public');

		# ré-initialisation filtres
		if ($this->okt['request']->query->has('init_pages_filters'))
		{
			$this->okt->module('Pages')->filters->initFilters();
			return $this->redirect($this->generateUrl('pagesList'));
		}

		# initialisation des filtres
		$this->okt->module('Pages')->filters->setPagesParams($aPagesParams);
		$this->okt->module('Pages')->filters->getFilters();

		# initialisation de la pagination
		$iNumFilteredPages = $this->okt->module('Pages')->pages->getPagesCount($aPagesParams);

		$oPagesPager = new Pager($this->okt, $this->okt->module('Pages')->filters->params->page, $iNumFilteredPages, $this->okt->module('Pages')->filters->params->nb_per_page);

		$iNumPages = $oPagesPager->getNbPages();

		$this->okt->module('Pages')->filters->normalizePage($iNumPages);

		$aPagesParams['limit'] = (($this->okt->module('Pages')->filters->params->page - 1) * $this->okt->module('Pages')->filters->params->nb_per_page) . ',' . $this->okt->module('Pages')->filters->params->nb_per_page;

		# récupération des pages
		$this->rsPagesList = $this->okt->module('Pages')->pages->getPages($aPagesParams);

		# meta description
		if (! empty($this->rsCategory->meta_description))
		{
			$this->page->meta_description = $this->rsCategory->meta_description;
		}
		elseif (! empty($this->okt->module('Pages')->config->meta_description[$this->okt['visitor']->language]))
		{
			$this->page->meta_description = $this->okt->module('Pages')->config->meta_description[$this->okt['visitor']->language];
		}
		else
		{
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (! empty($this->rsCategory->meta_keywords))
		{
			$this->page->meta_keywords = $this->rsCategory->meta_keywords;
		}
		elseif (! empty($this->okt->module('Pages')->config->meta_keywords[$this->okt['visitor']->language]))
		{
			$this->page->meta_keywords = $this->okt->module('Pages')->config->meta_keywords[$this->okt['visitor']->language];
		}
		else
		{
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# ajout du numéro de page au title
		if ($this->okt->module('Pages')->filters->params->page > 1)
		{
			$this->page->addTitleTag(sprintf(__('c_c_Page_%s'), $this->okt->module('Pages')->filters->params->page));
		}

		# title tag du module
		$this->page->addTitleTag((! empty($this->rsCategory->title_tag) ? $this->rsCategory->title_tag : $this->rsCategory->title));

		# ajout de la hiérarchie des rubriques au fil d'ariane et au title tag
		$rsPath = $this->okt->module('Pages')->categories->getPath($this->rsCategory->id, true, $this->okt['visitor']->language);

		while ($rsPath->fetch())
		{
			$this->page->breadcrumb->add($rsPath->title, $this->generateUrl('pagesCategory', array(
				'slug' => $rsPath->slug
			)));
		}

		# titre de la page
		$this->page->setTitle($this->rsCategory->title);

		# titre SEO de la page
		$this->page->setTitleSeo($this->rsCategory->title_seo);

		# raccourcis
		$this->rsPagesList->numPages = $iNumPages;
		$this->rsPagesList->pager = $oPagesPager;

		# affichage du template
		return $this->render($this->okt->module('Pages')
			->getCategoryTplPath($this->rsCategory['tpl']), array(
			'rsPagesList' => $this->rsPagesList,
			'rsCategory' => $this->rsCategory
		));
	}

	/**
	 * Affichage d'une page.
	 */
	public function pagesItem()
	{
		# récupération de la page en fonction du slug
		if (! $sPageSlug = $this->okt['request']->attributes->get('slug'))
		{
			return $this->serve404();
		}

		# récupération de la page
		$this->rsPage = $this->okt->module('Pages')->pages->getPage($sPageSlug, 1);

		if ($this->rsPage->isEmpty())
		{
			return $this->serve404();
		}

		# permission de lecture ?
		if (! $this->okt->module('Pages')->isPublicAccessible() || ! $this->rsPage->isReadable())
		{
			if ($this->okt['visitor']->is_guest)
			{
				return $this->redirect($this->okt['router']->generateLoginUrl($this->rsPage->url));
			}
			else
			{
				return $this->serve404();
			}
		}

		# meta description
		if (! empty($this->rsPage->meta_description))
		{
			$this->page->meta_description = $this->rsPage->meta_description;
		}
		elseif (! empty($this->okt->module('Pages')->config->meta_description[$this->okt['visitor']->language]))
		{
			$this->page->meta_description = $this->okt->module('Pages')->config->meta_description[$this->okt['visitor']->language];
		}
		else
		{
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}

		# meta keywords
		if (! empty($this->rsPage->meta_keywords))
		{
			$this->page->meta_keywords = $this->rsPage->meta_keywords;
		}
		elseif (! empty($this->okt->module('Pages')->config->meta_keywords[$this->okt['visitor']->language]))
		{
			$this->page->meta_keywords = $this->okt->module('Pages')->config->meta_keywords[$this->okt['visitor']->language];
		}
		else
		{
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}

		# si les rubriques sont activées
		if ($this->okt->module('Pages')->config->categories['enable'] && $this->rsPage->category_id)
		{
			# title tag de la rubrique
			$this->page->addTitleTag($this->rsPage->category_title);

			# ajout de la hiérarchie des rubriques au fil d'ariane
			$rsPath = $this->okt->module('Pages')->categories->getPath($this->rsPage->category_id, true, $this->okt['visitor']->language);
			while ($rsPath->fetch())
			{
				$this->page->breadcrumb->add($rsPath->title, $this->generateUrl('pagesCategory', array(
					'slug' => $rsPath->slug
				)));
			}
		}

		# title tag de la page
		$this->page->addTitleTag(($this->rsPage->title_tag == '' ? $this->rsPage->title : $this->rsPage->title_tag));

		# titre de la page
		$this->page->setTitle($this->rsPage->title);

		# titre SEO de la page
		$this->page->setTitleSeo($this->rsPage->title_seo);

		# fil d'ariane de la page
		$this->page->breadcrumb->add($this->rsPage->title, $this->rsPage->url);

		# affichage du template
		return $this->render($this->okt->module('Pages')
			->getItemTplPath($this->rsPage['tpl'], $this->rsPage->category_items_tpl), array(
			'rsPage' => $this->rsPage
		));
	}

	public function pagesItemForHomePage($mPageId = null)
	{
		# récupération de la page en fonction du slug
		if (empty($mPageId))
		{
			return $this->serve404();
		}

		# récupération de la page
		$this->rsPage = $this->okt->module('Pages')->pages->getPage($mPageId, 1);

		if ($this->rsPage->isEmpty())
		{
			return $this->serve404();
		}

		# permission de lecture ?
		if (! $this->okt->module('Pages')->isPublicAccessible() || ! $this->rsPage->isReadable())
		{
			if ($this->okt['visitor']->is_guest)
			{
				return $this->redirect($this->okt['router']->generateLoginUrl($this->rsPage->url));
			}
			else
			{
				return $this->serve404();
			}
		}

		# title tag de la page
		$this->page->addTitleTag(($this->rsPage->title_tag == '' ? $this->rsPage->title : $this->rsPage->title_tag));

		# titre de la page
		$this->page->setTitle($this->rsPage->title);

		# titre SEO de la page
		$this->page->setTitleSeo($this->rsPage->title_seo);

		# affichage du template
		return $this->render($this->okt->module('Pages')
			->getItemTplPath($this->rsPage['tpl'], $this->rsPage->category_items_tpl), array(
			'rsPage' => $this->rsPage
		));
	}
}
