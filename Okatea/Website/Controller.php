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
	 *
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
	 * @param string         $route         The name of the route
	 * @param mixed          $parameters    An array of parameters
	 * @param Boolean|string $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
	 *
	 * @return string The generated URL
	 *
	 * @see UrlGeneratorInterface
	 */
	public function generateUrl($route, $parameters = array(), $language = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		return $this->okt->router->generate($route, $parameters, $language = null, $referenceType);
	}

	/**
	 * Indique si le controlleur est appelé par la route de la page d'accueil.
	 *
	 * @return boolean
	 */
	public function isHomePageRoute()
	{
		return substr($this->request->attributes->get('_route'), 0, 8) === 'homePage';
	}

	public function homePage()
	{
		# Special case : user lang switch
		if (!$this->okt->languages->unique)
		{
			# recherche d'un code ISO de langue
			if (preg_match('#^(?:/?([a-zA-Z]{2}(?:-[a-zA-Z]{2})*?)/?)#', $this->request->getPathInfo(), $m)) {
				$sLanguage = $m[1];
			}

			if ($sLanguage != $this->okt->user->language)
			{
				$this->okt->user->setUserLang($sLanguage);
				return $this->redirect($this->generateUrl('homePage', array(), $sLanguage));
			}
		}

		return $this->render('homePage');
	}

	public function serve401()
	{
		return parent::serve401();
	}

	public function serve404()
	{
		return parent::serve404();
	}

	public function serve503()
	{
		return parent::serve503();
	}
}
