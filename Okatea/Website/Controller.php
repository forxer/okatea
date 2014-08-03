<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Website;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Okatea\Tao\Controller as BaseController;

class Controller extends BaseController
{

	/**
	 * Constructor.
	 */
	public function __construct($okt)
	{
		parent::__construct($okt);
		
		# Title tag
		$this->okt->page->addTitleTag($this->okt->page->getSiteTitleTag(null, $this->okt->page->getSiteTitle()));
	}

	/**
	 * Generates a URL from the given parameters.
	 *
	 * @param string $route
	 *        	The name of the route
	 * @param mixed $parameters
	 *        	An array of parameters
	 * @param Boolean|string $referenceType
	 *        	The type of reference (one of the constants in UrlGeneratorInterface)
	 *        	
	 * @return string The generated URL
	 *        
	 * @see UrlGeneratorInterface
	 */
	public function generateUrl($route, $parameters = array(), $language = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		return $this->okt['router']->generate($route, $parameters, $language = null, $referenceType);
	}

	/**
	 * Indique si le controlleur est appelé par la route de la page d'accueil.
	 *
	 * @return boolean
	 */
	public function isHomePageRoute()
	{
		return substr($this->okt['request']->attributes->get('_route'), 0, 8) === 'homePage';
	}

	public function homePage()
	{
		# Special case : user lang switch
		if (! $this->okt['languages']->unique)
		{
			# recherche d'un code ISO de langue
			if (preg_match('#^(?:/?([a-zA-Z]{2}(?:-[a-zA-Z]{2})*?)/?)#', $this->okt['request']->getPathInfo(), $m))
			{
				$sLanguage = $m[1];
			}
			
			if ($sLanguage != $this->okt->user->language)
			{
				$this->okt->user->setUserLang($sLanguage);
				return $this->redirect($this->generateUrl('homePage', array(), $sLanguage));
			}
		}
		
		$item = null;
		if (! empty($this->okt['config']->home_page['item'][$this->okt->user->language]))
		{
			$item = $this->okt['config']->home_page['item'][$this->okt->user->language];
		}
		elseif (! empty($this->okt['config']->home_page['item'][$this->okt['config']->language]))
		{
			$item = $this->okt['config']->home_page['item'][$this->okt['config']->language];
		}
		else
		{
			return $this->serve404();
		}
		
		$details = null;
		if (! empty($this->okt['config']->home_page['details'][$this->okt->user->language]))
		{
			$details = $this->okt['config']->home_page['details'][$this->okt->user->language];
		}
		elseif (! empty($this->okt['config']->home_page['details'][$this->okt['config']->language]))
		{
			$details = $this->okt['config']->home_page['details'][$this->okt['config']->language];
		}
		
		# reset title tag because we will recall the main controller
		$this->okt->page->resetTitleTag();
		
		# -- TRIGGER : handleWebsiteHomePage
		$this->okt['triggers']->callTrigger('handleWebsiteHomePage', $item, $details);
		
		if (null === $this->okt->response || false === $this->okt->response)
		{
			$this->okt->response = new Response();
			$this->okt->response->headers->set('Content-Type', 'text/plain');
			$this->okt->response->setStatusCode(Response::HTTP_NOT_IMPLEMENTED);
			$this->okt->response->setContent('Unable to load homePage controller for item "' . $item . '", please check your website configuration.');
		}
		
		return $this->okt->response;
	}

	public function serve401()
	{
		return parent::serve401();
	}

	public function serve404()
	{
		# Special case : language not specified in URL
		if (! $this->okt['languages']->unique)
		{
			$sLanguage = null;
			# recherche d'un code ISO de langue
			if (preg_match('#^(?:/?([a-zA-Z]{2}(?:-[a-zA-Z]{2})*?)/?)#', $this->okt['request']->getPathInfo(), $m))
			{
				$sLanguage = $m[1];
			}
			
			if (null === $sLanguage)
			{
				return $this->redirect($this->generateUrl('homePage', array(), $this->okt->user->language), 301);
			}
		}
		
		return parent::serve404();
	}

	public function serve503()
	{
		return parent::serve503();
	}
}
