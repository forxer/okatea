<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tao\Core\Controller as BaseController;

class Controller extends BaseController
{
	/**
	 * Constructor.
	 *
	 */
	public function __construct($okt)
	{
		parent::__construct($okt);

		# Ajout des fichiers CSS de l'admin
		$this->okt->page->css->addFile($this->okt->options->public_url.'/ui-themes/'.$this->okt->config->admin_theme.'/jquery-ui.css');
		$this->okt->page->css->addFile($this->okt->options->public_url.'/css/init.css');
		$this->okt->page->css->addFile($this->okt->options->public_url.'/css/admin.css');
		$this->okt->page->css->addFile($this->okt->options->public_url.'/css/famfamfam.css');

		# Ajout des fichiers JS de l'admin
		$this->okt->page->js->addFile($this->okt->options->public_url.'/js/jquery/jquery.min.js');
		$this->okt->page->js->addFile($this->okt->options->public_url.'/js/jquery/cookie/jquery.cookie.min.js');
		$this->okt->page->js->addFile($this->okt->options->public_url.'/js/jquery/metadata/jquery.metadata.min.js');
		$this->okt->page->js->addFile($this->okt->options->public_url.'/js/jquery/ui/jquery-ui.min.js');
		$this->okt->page->js->addFile($this->okt->options->public_url.'/js/jquery/validate/jquery.validate.min.js');
		$this->okt->page->js->addFile($this->okt->options->public_url.'/js/jquery/validate/additional-methods.min.js');
		$this->okt->page->js->addFile($this->okt->options->public_url.'/js/common_admin.js');

		# Title tag
		$this->okt->page->addTitleTag($this->okt->page->getSiteTitleTag(null, $this->okt->page->getSiteTitle()));

		# Fil d'ariane administration
		$this->okt->page->addAriane(__('Administration'), $this->generateUrl('home'));
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
	public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
	{
		return $this->okt->adminRouter->generate($route, $parameters, $referenceType);
	}

	public function serve401()
	{
		parent::serve401();
	}

	public function serve404()
	{
		parent::serve404();
	}

	public function serve503()
	{
		parent::serve503();
	}
}
