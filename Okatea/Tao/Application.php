<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao;

use Monolog\ErrorHandler;
use Okatea\Tao\Config\Config;
use Okatea\Tao\Config\ConfigServiceProvider;
use Okatea\Tao\Database\MySqli;
use Okatea\Tao\Database\DatabaseServiceProvider;
use Okatea\Tao\Extensions\ExtensionsServiceProvider;
use Okatea\Tao\L10n\L10nServiceProvider;
use Okatea\Tao\L10n\Localization;
use Okatea\Tao\Logger\LoggerServiceProvider;
use Okatea\Tao\Messages\MessagesServiceProvider;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Navigation\NavigationServiceProvider;
use Okatea\Tao\RequestServiceProvider;
use Okatea\Tao\Routing\RouterServiceProvider;
use Okatea\Tao\Session\SessionServiceProvider;
use Okatea\Tao\Templating\TemplatingServiceProvider;
use Okatea\Tao\Themes\SimpleReplacements;
use Okatea\Tao\Triggers\TriggersServiceProvider;
use Okatea\Tao\Users\UsersServiceProvider;
use Patchwork\Utf8\Bootup as Utf8Bootup;
use Pimple\Container;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler as DebugErrorHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

abstract class Application extends Container
{
	const VERSION = '2.0-beta6';

	/**
	 * The instance of the autoloader.
	 *
	 * @var Composer\Autoload\ClassLoader
	 */
	public $autoloader;

	/**
	 * The invoked controller.
	 *
	 * @var Okatea\Tao\Controller
	 */
	public $controllerInstance;

	/**
	 * Le gestionnaire de base de données.
	 *
	 * @deprecated
	 * @var Okatea\Tao\Database\MySqli
	 */
	public $db;

	/**
	 * Le préfix des tables de la base de données.
	 *
	 * @deprecated
	 * @var string
	 */
	public $db_prefix;

	/**
	 * The response will be returned.
	 *
	 * @var Symfony\Component\HttpFoundation\Response
	 */
	public $response;

	/**
	 * L'objet HTMLPurifier si il est instancié, sinon null.
	 *
	 * @var mixed
	 */
	protected $htmlpurifier;

	/**
	 * Constructor.
	 *
	 * @param Composer\Autoload\ClassLoader $autoloader
	 * @param array $aOptions
	 *
	 * @return void
	 */
	public function __construct($autoloader, array $aOptions = [])
	{
		define('OKT_START_TIME', microtime(true));

		parent::__construct($aOptions);

		$this->autoloader = $autoloader;

		# Registers common services provider
		$this->register(new ConfigServiceProvider());
		$this->register(new DatabaseServiceProvider());
		$this->register(new ExtensionsServiceProvider());
		$this->register(new L10nServiceProvider());
		$this->register(new LoggerServiceProvider());
		$this->register(new MessagesServiceProvider());
		$this->register(new NavigationServiceProvider());
		$this->register(new RequestServiceProvider());
		$this->register(new RouterServiceProvider());
		$this->register(new SessionServiceProvider());
		$this->register(new TemplatingServiceProvider());
		$this->register(new TriggersServiceProvider());
		$this->register(new UsersServiceProvider());

		# Enables the portablity layer and configures PHP for UTF-8
		Utf8Bootup::initAll();

		# Redirects to an UTF-8 encoded URL if it's not already the case
		Utf8Bootup::filterRequestUri();

		# Normalizes HTTP inputs to UTF-8 NFC
		Utf8Bootup::filterRequestInputs();

		# Start request and session
		$this['request']->setSession($this['session']);

		# URL du dossier des fichiers publics
		$this['public_url'] = $this['config']->getData('app_url') . basename($this['public_path']);

		# URL du dossier upload depuis la racine
		$this['upload_url'] = $this['config']->getData('app_url') . basename($this['public_path']) . '/' . basename($this['upload_path']);

		# Print errors in debug mode
		if ($this['debug']) {
			Debug::enable();
			DebugErrorHandler::setLogger($this['logger']);
		}
		# otherwise log them
		else {
			ErrorHandler::register($this['phpLogger']);
		}
	}

	/**
	 * Run application.
	 */
	abstract public function run();

	/**
	 * Return application version.
	 *
	 * @return string
	 */
	public function getVersion()
	{
		return self::VERSION;
	}

	/**
	 * Créer et retourne un objet de configuration
	 *
	 * @param string $file
	 * @return object oktConfig
	 */
	public function newConfig($file)
	{
		return new Config($this['cacheConfig'], $this['config_path'] . '/' . $file);
	}

	/**
	 * Return a given module instance.
	 *
	 * @param string $sModuleId
	 * @return object Okatea\Tao\Extensions\Modules\Module
	 */
	public function module($sModuleId)
	{
		return $this['modules']->getInstance($sModuleId);
	}

	public function performCommonContentReplacements($string)
	{
		return SimpleReplacements::parse($string, $this->getCommonContentReplacementsVariables());
	}

	public function getCommonContentReplacementsVariables()
	{
		return [
			'app_path' => $this['config']->app_url,
			'user_language' => $this['visitor']->language,
			//	'theme_url' => $this->theme->url,
			'website_title' => $this['config']->title[$this['visitor']->language],
			'website_desc' => $this['config']->desc[$this['visitor']->language],

			'address_street' => $this['config']->address['street'],
			'address_street_2' => $this['config']->address['street_2'],
			'address_code' => $this['config']->address['code'],
			'address_city' => $this['config']->address['city'],
			'address_country' => $this['config']->address['country'],
			'address_phone' => (!empty($this['config']->address['tel']) ? $this['config']->address['tel'] : $this['config']->address['mobile']),
			'address_tel' => $this['config']->address['tel'],
			'address_mobile' => $this['config']->address['mobile'],
			'address_fax' => $this['config']->address['fax'],

			'gps_lat' => $this['config']->gps['lat'],
			'gps_long' => $this['config']->gps['long'],

			'company_name' => $this['config']->company['name'],
			'company_com_name' => $this['config']->company['com_name'],
			'company_siret' => $this['config']->company['siret'],

			'leader_name' => $this['config']->leader['name'],
			'leader_firstname' => $this['config']->leader['firstname'],

			'email_to' => $this['config']->email['to'],
			'email_from' => $this['config']->email['from'],
			'email_name' => $this['config']->email['name']
		];
	}

	public function getImagesReplacementsVariables($aImages)
	{
		$aReplacements = [];

		foreach ($aImages as $iImageId => $aImageInfos)
		{
			foreach ($aImageInfos as $sImageKeyInfo => $sImageValueInfo)
			{
				if ($sImageKeyInfo == 'alt' && isset($sImageValueInfo[$this['visitor']->language]))
				{
					$aReplacements['image_' . $iImageId . '_' . $sImageKeyInfo] = $sImageValueInfo[$this['visitor']->language];
					continue;
				}

				if ($sImageKeyInfo == 'title' && isset($sImageValueInfo[$this['visitor']->language]))
				{
					$aReplacements['image_' . $iImageId . '_' . $sImageKeyInfo] = $sImageValueInfo[$this['visitor']->language];
					continue;
				}

				$aReplacements['image_' . $iImageId . '_' . $sImageKeyInfo] = $sImageValueInfo;
			}
		}

		return $aReplacements;
	}

	/**
	 * Invoque le filtre HTML qui permet de supprimer
	 * le code potentiellement malveillant, les mauvaises
	 * balises et produire du XHTML valide.
	 *
	 * @param
	 *        	string str Chaine à filtrer
	 * @return string Chaine filtrée
	 */
	public function HTMLfilter($str)
	{
		if ($this['config']->htmlpurifier_disabled)
		{
			return $str;
		}

		if ($this->htmlpurifier === null)
		{
			$sCacheFile = $this['cache_path'] . '/htmlpurifier';

			(new Filesystem())->mkdir($sCacheFile);

			$config = \HTMLPurifier_Config::createDefault();

			$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
			$config->set('Cache.SerializerPath', $this['cache_path'] . '/HTMLPurifier');

			$config->set('HTML.SafeEmbed', true);
			$config->set('HTML.SafeObject', true);
			$config->set('Output.FlashCompat', true);

			$config->set('HTML.SafeIframe', true);
			$config->set('URI.SafeIframeRegexp', '%^http://(www.youtube.com/embed/|player.vimeo.com/video/)%');

			# autorise les ID
			# http://htmlpurifier.org/docs/enduser-id.html
			$config->set('Attr.EnableID', true);

			# modification de la définition
			# http://htmlpurifier.org/docs/enduser-customize.html
			$config->set('HTML.DefinitionID', 'okatea');
			$config->set('HTML.DefinitionRev', 1);
			if ($def = $config->maybeGetRawHTMLDefinition())
			{
				# autorise l'attribut target sur les liens
				$def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');

				# autorise l'attribut usemap sur les images
				$def->addAttribute('img', 'usemap', 'CDATA');

				# autorise l'élément map
				$map = $def->addElement(
					'map', 					// name
					'Block', 				// content set
					'Flow', 				// allowed children
					'Common', 				// attribute collection
					[ // attributes
						'name' => 'CDATA',
						'id' => 'ID',
						'title' => 'CDATA'
					]
				);
				$map->excludes = [
					'map' => true
				];

				# autorise l'élément area
				$area = $def->addElement(
					'area', 				// name
					'Block', 				// content set
					'Empty', 				// don't allow children
					'Common', 				// attribute collection
					[
						// attributes
						'name' => 'CDATA',
						'id' => 'ID',
						'alt' => 'Text',
						'coords' => 'CDATA',
						'accesskey' => 'Character',
						'nohref' => new \HTMLPurifier_AttrDef_Enum([
							'nohref'
						]),
						'href' => 'URI',
						'shape' => new \HTMLPurifier_AttrDef_Enum([
							'rect',
							'circle',
							'poly',
							'default'
						]),
						'tabindex' => 'Number',
						'target' => new \HTMLPurifier_AttrDef_Enum([
							'_blank',
							'_self',
							'_target',
							'_top'
						])
					]
				);
				$area->excludes = [
					'area' => true
				];
			}

			# get it now !
			$this->htmlpurifier = new \HTMLPurifier($config);
		}

		$str = $this->htmlpurifier->purify($str);

		return $str;
	}
}
