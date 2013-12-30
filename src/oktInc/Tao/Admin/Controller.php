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
		$okt->page->css->addFile($okt->options->public_url.'/ui-themes/'.$okt->config->admin_theme.'/jquery-ui.css');
		$okt->page->css->addFile($okt->options->public_url.'/css/init.css');
		$okt->page->css->addFile($okt->options->public_url.'/css/admin.css');
		$okt->page->css->addFile($okt->options->public_url.'/css/famfamfam.css');

		# Ajout des fichiers JS de l'admin
		$okt->page->js->addFile($okt->options->public_url.'/js/jquery/jquery.min.js');
		$okt->page->js->addFile($okt->options->public_url.'/js/jquery/cookie/jquery.cookie.min.js');
		$okt->page->js->addFile($okt->options->public_url.'/js/jquery/metadata/jquery.metadata.min.js');
		$okt->page->js->addFile($okt->options->public_url.'/js/jquery/ui/jquery-ui.min.js');
		$okt->page->js->addFile($okt->options->public_url.'/js/jquery/validate/jquery.validate.min.js');
		$okt->page->js->addFile($okt->options->public_url.'/js/jquery/validate/additional-methods.min.js');
		$okt->page->js->addFile($okt->options->public_url.'/js/common_admin.js');

		# Title tag
		$okt->page->addTitleTag($okt->page->getSiteTitleTag(null,$okt->page->getSiteTitle()));

		# Fil d'ariane administration
		$okt->page->addAriane(__('Administration'),'index.php');
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
