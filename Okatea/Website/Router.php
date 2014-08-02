<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Website;

use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Router as BaseRouter;
use Okatea\Tao\Application;
use Okatea\Tao\Routing\Loader\YamlDirectoryLoaderLocalizer;
use Okatea\Tao\Routing\ControllerResolverTrait;

class Router extends BaseRouter
{
	use ControllerResolverTrait;

	/**
	 *
	 * @var Application
	 */
	protected $okt;

	public function __construct(Application $okt, $ressources_dir, $cache_dir = null, $debug = false, LoggerInterface $logger = null)
	{
		$this->okt = $okt;

		# restrict to the default language if we have only one language
		if ($this->okt->languages->unique)
		{
			$ressources_dir .= '/' . $this->okt['config']->language;
		}

		parent::__construct(new YamlDirectoryLoaderLocalizer($okt, new FileLocator($ressources_dir)), $ressources_dir, array(
			'cache_dir' => $cache_dir,
			'debug' => $debug,
			'generator_cache_class' => 'OkateaUrlGenerator',
			'matcher_cache_class' => 'OkateaUrlMatcher'
		), $okt['requestContext'], $logger);
	}

	/**
	 *
	 * @ERROR!!!
	 *
	 */
	public function generate($name, $parameters = array(), $language = null, $referenceType = self::ABSOLUTE_PATH)
	{
		if (! $this->okt->languages->unique)
		{
			if (null === $language)
			{
				$name = $name . '-' . $this->okt->user->language;
			}
			else
			{
				$name = $name . '-' . $language;
			}
		}

		return $this->getGenerator()->generate($name, $parameters, $referenceType);
	}

	/**
	 * This is a quicky dirty hack to link from the website to the admin.
	 *
	 * @TODO : need to extends Symfony\Component\Routing\Generator\UrlGenerator
	 *
	 * @param string $name
	 * @param mixed $parameters
	 * @param string $language
	 * @param Boolean|string $referenceType
	 * @return string
	 */
	public function generateFromAdmin($name, $parameters = array(), $language = null, $referenceType = self::ABSOLUTE_PATH)
	{
		return str_replace('/admin/', '/', $this->generate($name, $parameters, $language, $referenceType));
	}

	/**
	 * Retourne l'URL de la page de connexion.
	 *
	 * @param string $sRedirectUrl
	 * @param mixed $parameters
	 * @param string $language
	 * @param Boolean|string $referenceType
	 * @return string
	 */
	public function generateLoginUrl($sRedirectUrl = null, $parameters = array(), $language = null, $referenceType = self::ABSOLUTE_PATH)
	{
		if ($this->okt['config']->users['pages']['log_reg'])
		{
			$sLoginUrl = $this->generate('usersLoginRegister');
		}
		else
		{
			$sLoginUrl = $this->generate('usersLogin');
		}

		if (! is_null($sRedirectUrl))
		{
			$this->okt->session->set('okt_redirect_url', $sRedirectUrl);
		}

		return $sLoginUrl;
	}

	/**
	 * Retourne l'URL de la page d'inscription.
	 *
	 * @param string $sRedirectUrl
	 * @param mixed $parameters
	 * @param string $language
	 * @param Boolean|string $referenceType
	 * @return string
	 */
	public function generateRegisterUrl($sRedirectUrl = null, $parameters = array(), $language = null, $referenceType = self::ABSOLUTE_PATH)
	{
		if ($this->okt['config']->users['pages']['log_reg'])
		{
			$sRegisterUrl = $this->generate('usersLoginRegister');
		}
		else
		{
			$sRegisterUrl = $this->generate('usersRegister');
		}

		if (! is_null($sRedirectUrl))
		{
			$this->okt->session->set('okt_redirect_url', $sRedirectUrl);
		}

		return $sRegisterUrl;
	}

	/**
	 * Touch collection resources to force cache regenerating.
	 */
	public function touchResources()
	{
		$aResources = array();
		foreach ($this->getRouteCollection()->getResources() as $oResource)
		{
			$aResources[] = (string) $oResource;
		}

		$fs = new Filesystem();
		$fs->touch($aResources);
	}

	/**
	 * Check if a named route exists.
	 *
	 * @return boolean
	 */
	public function routeExists($sRouteName)
	{
		return (null === $this->getRouteCollection()->get($sRouteName)) ? false : true;
	}
}
