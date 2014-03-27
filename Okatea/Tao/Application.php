<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao;

use Monolog\Logger;
use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;

use Okatea\Admin\Router as adminRouter;
use Okatea\Tao\Cache\SingleFileCache;
use Okatea\Tao\Database\MySqli;
use Okatea\Tao\Misc\DebugBar\DebugBar;
use Okatea\Tao\Misc\FlashMessages;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Extensions\Modules\Collection as ModulesCollection;
use Okatea\Tao\Extensions\Themes\Collection as ThemesCollection;
use Okatea\Tao\Navigation\Menus\Menus;
use Okatea\Tao\Themes\SimpleReplacements;
use Okatea\Tao\Users\Authentification;
use Okatea\Tao\Users\Groups;
use Okatea\Tao\Users\Users;
use Okatea\Website\Router;

use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler as DebugErrorHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Routing\RequestContext;


/**
 * Classe définissant le coeur de l'application (core).
 *
 * La classe principale de l'application se charge d'initialiser les
 * autres classes requises au bon fonctionnement. C'est en quelque
 * sorte une "super-classe" qui gère les autres pour un accès plus facile
 * à travers l'application.
 */
class Application
{
	const VERSION = '2.0-beta5';

	/**
	 * L'instance de l'autoloader.
	 *
	 * @var Composer\Autoload\ClassLoader
	 */
	public $autoloader;

	/**
	 * Le gestionnaire du fichier cache de configuration.
	 *
	 * @var Okatea\Tao\Cache\SingleFileCache
	 */
	public $cacheConfig;

	/**
	 * Le gestionnaire de configuration.
	 *
	 * @var Okatea\Tao\Config
	 */
	public $config;

	/**
	 * Le controller invoqué.
	 *
	 * @var Okatea\Tao\Controller
	 */
	public $controllerInstance;

	/**
	 * Le gestionnaire de base de données.
	 *
	 * @var Okatea\Tao\Database\MySqli
	 */
	public $db;

	/**
	 * Le gestionnaire de base de données.
	 *
	 * @var Okatea\Tao\Misc\DebugBar\DebugBar
	 */
	public $debugbar;

	/**
	 * Le gestionnaire d'erreurs.
	 *
	 * @var Okatea\Tao\Errors
	 */
	public $error;

	/**
	 * Le gestionnaire des groupes utilisateurs.
	 *
	 * @var Okatea\Tao\Users\Groups
	 */
	public $groups;

	/**
	 * Le gestionnaire de langues.
	 *
	 * @var Okatea\Tao\Localization
	 */
	public $l10n;

	/**
	 * Le gestionnaire de langues.
	 *
	 * @var Okatea\Tao\Languages
	 */
	public $languages;

	/**
	 * Logger instance.
	 *
	 * @var Psr\Log\LoggerInterface
	 */
	public $logger;

	/**
	 * Le gestionnaire de modules.
	 *
	 * @var Okatea\Tao\Extensions\Modules\Collection
	 */
	public $modules;

	/**
	 * Le gestionnaire de themes.
	 *
	 * @var Okatea\Tao\Extensions\Themes\Collection
	 */
	public $themes;

	/**
	 * Les menus de navigation.
	 *
	 * @var Okatea\Tao\Navigation\Menus\Menus
	 */
	public $navigation;

	/**
	 * Les menus de navigation.
	 *
	 * @var Okatea\Tao\ApplicationOptions
	 */
	public $options;

	/**
	 * La requete en cours.
	 *
	 * @var Symfony\Component\HttpFoundation\Request
	 */
	public $request;

	/**
	 * Le contexte de le requete en cours.
	 *
	 * @var Symfony\Component\Routing\RequestContext
	 */
	public $requestContext;

	/**
	 * La réponse qui va être renvoyée.
	 *
	 * @var Symfony\Component\HttpFoundation\Response
	 */
	public $response;

	/**
	 * Le routeur interne.
	 *
	 * @var Okatea\Tao\Routing\Router
	 */
	public $router;

	/**
	 * Le routeur interne de l'administration.
	 *
	 * @var Okatea\Tao\Routing\AdminRouter
	 */
	public $adminRouter;

	/**
	 * Le gestionnaire de session.
	 *
	 * @var Okatea\Tao\Session
	 */
	public $session;

	/**
	 * Le moteur de templates.
	 *
	 * @var Okatea\Tao\Templating
	 */
	public $tpl;

	/**
	 * Le gestionnaire de déclencheurs.
	 *
	 * @var Okatea\Tao\Triggers
	 */
	public $triggers;

	/**
	 * Le gestionnaire d'utilisateur en cours.
	 *
	 * @var Okatea\Tao\Authentification
	 */
	public $user;

	/**
	 * Le gestionnaire des utilisateurs.
	 *
	 * @var Okatea\Tao\Users\Users
	 */
	public $users;

	/**
	 * L'objet HTMLPurifier si il est instancié, sinon null.
	 *
	 * @var mixed
	 */
	protected $htmlpurifier;

	/**
	 * La pile qui contient les permissions.
	 *
	 * @var array
	 */
	protected $permsStack = array();

	/**
	 * La liste des répertoires où le moteur de templates
	 * doit chercher le template à interpréter.
	 *
	 * @var array
	 */
	protected $aTplDirectories = array();


	/**
	 * Constructor.
	 *
	 * @param Composer\Autoload\ClassLoader $autoloader
	 * @param string $sRootPath
	 *
	 * @return void
	 */
	public function __construct($autoloader, array $aOptions = array())
	{
		# Autoloader shortcut
		$this->autoloader = $autoloader;

		$this->options = new ApplicationOptions($aOptions);

		$this->getLogger();

		$this->getConfig();

		$this->httpFoundation();

		$this->start();
	}

	public function run()
	{
		$this->db = $this->database();

		$this->languages = new Languages($this);

		$this->router = new Router(
			$this,
			$this->options->get('config_dir').'/Routes',
			$this->options->get('cache_dir').'/routing',
			$this->options->get('debug'),
			$this->logger
		);

		$this->user = new Authentification(
			$this,
			$this->options->get('cookie_auth_name'),
			$this->options->get('cookie_auth_from'),
			$this->config->app_path,
			'',
			$this->request->isSecure()
		);

		$this->l10n = new Localization(
			$this->user->language,
			$this->config->language,
			$this->user->timezone
		);

		$this->l10n->loadFile($this->options->get('locales_dir').'/%s/main');
		$this->l10n->loadFile($this->options->get('locales_dir').'/%s/date');
		$this->l10n->loadFile($this->options->get('locales_dir').'/%s/users');

		$this->modules = new ModulesCollection($this, $this->options->get('modules_dir'));

		$this->themes = new ThemesCollection($this, $this->options->get('themes_dir'));

		$this->triggers = new Triggers();

		$this->navigation = new Menus($this);
	}

	public function getVersion()
	{
		return self::VERSION;
	}

	/**
	 * Make common operations on start.
	 *
	 */
	protected function start()
	{
		# Register start time
		define('OKT_START_TIME', microtime(true));

		# Init MB ext
		mb_internal_encoding('UTF-8');
		mb_regex_encoding('UTF-8');

		# Default timezone (crushed later by user settings)
//		date_default_timezone_set('Europe/Paris');

		$this->error = new Errors();

		# print errors in debug mode
		if ($this->options->get('debug'))
		{
			Debug::enable();
			DebugErrorHandler::setLogger($this->logger);

//			$this->debugbar = new DebugBar($this);
		}

		# otherwise log them
		else
		{
			$phpLoggerAll = new Logger('php_error',array(
				new FingersCrossedHandler(
					new StreamHandler($this->options->get('logs_dir').'/php_errors.log', Logger::INFO),
					Logger::WARNING
				)
			),array(
				new IntrospectionProcessor(),
				new WebProcessor(),
				new MemoryUsageProcessor(),
				new MemoryPeakUsageProcessor()
			));
			ErrorHandler::register($phpLoggerAll);
		}
	}

	/**
	 * Make database connexion.
	 *
	 * @return object
	 */
	protected function database()
	{
		if (!file_exists($this->options->get('config_dir').'/connexion.php')) {
			throw new \RuntimeException('Unable to find database connexion file !');
		}

		require $this->options->get('config_dir').'/connexion.php';

		$db = new MySqli($sDbUser, $sDbPassword, $sDbHost, $sDbName, $sDbPrefix);

		if ($db->hasError()) {
			throw new \RuntimeException('Unable to connect to database. '.$db->error());
		}

		return $db;
	}

	protected function httpFoundation()
	{
		$this->request = Request::createFromGlobals();

		$this->session = new Session(
			new NativeSessionStorage(
				array(
					'cookie_lifetime' 	=> 0,
					'cookie_path' 		=> $this->config->app_path,
					'cookie_secure' 	=> $this->request->isSecure(),
					'cookie_httponly' 	=> true,
					'use_trans_sid' 	=> false,
					'use_only_cookies' 	=> true
				),
				new \SessionHandler()
			),
			null,
			new FlashMessages('okt_flashes'), $this->options->get('csrf_token_name')
		);

		$this->request->setSession($this->session);
	}

	/**
	 * Load modules public or admin part.
	 *
	 * @return void
	 */
	protected function loadModules($sPart)
	{
		$this->modules->load($sPart);
	}

	/**
	 * Load themes public or admin part.
	 *
	 * @return void
	 */
	protected function loadThemes($sPart)
	{
		$this->themes->load($sPart);
	}

	public function loadAdminRouter()
	{
		$this->adminRouter = new adminRouter(
			$this,
			$this->options->get('config_dir').'/RoutesAdmin',
			$this->options->get('cache_dir').'/routing/admin',
			$this->options->get('debug'),
			$this->logger
		);
	}

	public function getRequestContext()
	{
		if (null === $this->requestContext)
		{
			$this->requestContext = new RequestContext();
			$this->requestContext->fromRequest($this->request);
		}

		return $this->requestContext;
	}

	public function getLogger()
	{
		if (null === $this->logger)
		{
			$this->logger = new Logger('okatea',array(
				new FirePHPHandler()
			),array(
				new IntrospectionProcessor(),
				new WebProcessor(),
				new MemoryUsageProcessor(),
				new MemoryPeakUsageProcessor()
			));
		}

		return $this->logger;
	}

	public function getUsers()
	{
		if (null === $this->users) {
			$this->users = new Users($this);
		}

		return $this->users;
	}

	public function getGroups()
	{
		if (null === $this->groups) {
			$this->groups = new Groups($this);
		}

		return $this->groups;
	}


	/* Permissions
	----------------------------------------------------------*/

	/**
	 * Retourne la pile de permissions
	 *
	 * @return array
	 */
	public function getPerms()
	{
		return $this->permsStack;
	}

	/**
	 * Ajout d'une permission
	 *
	 * @param $perm Identifiant de la permission
	 * @param $libelle Intitulé de la permission
	 * @param $group Groupe de la permission (null)
	 * @return void
	 */
	public function addPerm($perm, $libelle, $group=null)
	{
		if ($group) {
			$this->permsStack[$group]['perms'][$perm] = $libelle;
		}
		else {
			$this->permsStack[$perm] = $libelle;
		}
	}

	/**
	 * Ajout d'un groupe de permissions
	 *
	 * @param $group
	 * @param $libelle
	 * @return void
	 */
	public function addPermGroup($group, $libelle)
	{
		$this->permsStack[$group] = array(
			'libelle' => $libelle,
			'perms' => array()
		);
	}

	/**
	 * Vérifie que l'utilisateur courant a la permission demandée.
	 *
	 * @param string $permissions
	 * @return boolean
	 */
	public function checkPerm($permissions)
	{
		if ($permissions == 'is_superadmin') {
			return $this->user->is_superadmin;
		}
		elseif ($this->user->is_superadmin) {
			return true;
		}

		return in_array($permissions, $this->user->perms);
	}

	public function getPermsForDisplay()
	{
		$aPermissions = array();

		foreach ($this->getPerms() as $k=>$v)
		{
			if (!is_array($v))
			{
				if (!isset($aPermissions['others']))
				{
					$aPermissions['others'] = array(
						'libelle' => '',
						'perms' => array()
					);
				}

				if ($this->checkPerm($k)) {
					$aPermissions['others']['perms'][$k] = $v;
				}
			}
			else
			{
				$aPermissions[$k] = array(
					'libelle' => $v['libelle'],
					'perms' => array()
				);

				foreach ($v['perms'] as $perm=>$libelle)
				{
					if ($this->checkPerm($perm)) {
						$aPermissions[$k]['perms'][$perm] = $libelle;
					}
				}
			}
		}

		asort($aPermissions);

		return $aPermissions;
	}


	/* Templates engine
	----------------------------------------------------------*/

	/**
	 * Retourne le moteur de templates.
	 *
	 * @return void
	 */
	public function getTplEngine()
	{
		# initialisation
		$tpl = new Templating($this->aTplDirectories);

		# assignation par défaut
		$tpl->addGlobal('okt', $this);

		return $tpl;
	}

	/**
	 * Ajoute à la pile un répertoire de templates.
	 *
	 * @param string $sDirectoryPath 	Le chemin du répertoire
	 * @param boolean $bPriority 	Ajoute en haut de la pile
	 * @return void
	 */
	public function setTplDirectory($sDirectoryPath, $bPriority=false)
	{
		if ($bPriority) {
			return array_unshift($this->aTplDirectories, $sDirectoryPath);
		}
		else {
			return array_push($this->aTplDirectories, $sDirectoryPath);
		}
	}

	/**
	 * Retourne la pile des répertoires de templates.
	 *
	 * @return array
	 */
	public function getTplDirectories()
	{
		return $this->aTplDirectories;
	}


	/* Utilitaires fichiers de configuration
	----------------------------------------------------------*/

	/**
	 * Retourne la configuration du site.
	 *
	 * @return void
	 */
	public function getConfig()
	{
		$this->cacheConfig = new SingleFileCache($this->options->get('cache_dir').'/static.php');

		$this->config = $this->newConfig('conf_site');

		# URL du dossier des fichiers publics
		$this->options->set('public_url', $this->config->getData('app_path').'oktPublic');

		# URL du dossier upload depuis la racine
		$this->options->set('upload_url', $this->config->getData('app_path').'oktPublic/upload');
	}

	/**
	 * Créer et retourne un objet de configuration
	 *
	 * @param string $file
	 * @return object oktConfig
	 */
	public function newConfig($file)
	{
		return new Config($this->cacheConfig, $this->options->get('config_dir').'/'.$file);
	}

	/**
	 * Retourne un objet module
	 *
	 * @param $sModuleId
	 * @return object Okatea\Tao\Extensions\Modules\Module
	 */
	public function module($sModuleId)
	{
		return $this->modules->getInstance($sModuleId);
	}


	/* Divers...
	----------------------------------------------------------*/

	public function performCommonContentReplacements($string)
	{
		return SimpleReplacements::parse($string, $this->getCommonContentReplacementsVariables());
	}

	public function getCommonContentReplacementsVariables()
	{
		return array(
			'app_path' => $this->config->app_path,
			'user_language' => $this->user->language,
		//	'theme_url' => $this->theme->url,
			'website_title' => $this->config->title[$this->user->language],
			'website_desc' => $this->config->desc[$this->user->language],

			'address_street' => $this->config->address['street'],
			'address_street_2' => $this->config->address['street_2'],
			'address_code' => $this->config->address['code'],
			'address_city' => $this->config->address['city'],
			'address_country' => $this->config->address['country'],
			'address_phone' => (!empty($this->config->address['tel']) ? $this->config->address['tel'] : $this->config->address['mobile']),
			'address_tel' => $this->config->address['tel'],
			'address_mobile' => $this->config->address['mobile'],
			'address_fax' => $this->config->address['fax'],

			'gps_lat' => $this->config->gps['lat'],
			'gps_long' => $this->config->gps['long'],

			'company_name' => $this->config->company['name'],
			'company_com_name' => $this->config->company['com_name'],
			'company_siret' => $this->config->company['siret'],

			'leader_name' => $this->config->leader['name'],
			'leader_firstname' => $this->config->leader['firstname'],

			'email_to' => $this->config->email['to'],
			'email_from' => $this->config->email['from'],
			'email_name' => $this->config->email['name']
		);
	}

	public function getImagesReplacementsVariables($aImages)
	{
		$aReplacements = array();

		foreach ($aImages as $iImageId=>$aImageInfos)
		{
			foreach ($aImageInfos as $sImageKeyInfo=>$sImageValueInfo)
			{
				if ($sImageKeyInfo  == 'alt' && isset($sImageValueInfo[$this->user->language]))
				{
					$aReplacements['image_'.$iImageId.'_'.$sImageKeyInfo] = $sImageValueInfo[$this->user->language];
					continue;
				}

				if ($sImageKeyInfo  == 'title' && isset($sImageValueInfo[$this->user->language]))
				{
					$aReplacements['image_'.$iImageId.'_'.$sImageKeyInfo] = $sImageValueInfo[$this->user->language];
					continue;
				}

				$aReplacements['image_'.$iImageId.'_'.$sImageKeyInfo] = $sImageValueInfo;
			}
		}

		return $aReplacements;
	}

	/**
	 * Invoque le filtre HTML qui permet de supprimer
	 * le code potentiellement malveillant, les mauvaises
	 * balises et produire du XHTML valide.
	 *
	 * @param    string    str        Chaine à filtrer
	 * @return    string Chaine filtrée
	 */
	public function HTMLfilter($str)
	{
		if ($this->config->htmlpurifier_disabled) {
			return $str;
		}

		if ($this->htmlpurifier === null)
		{
			$sCacheFile = $this->options->get('cache_dir').'/htmlpurifier';

			if (!file_exists($sCacheFile))
			{
				$fs = new Filesystem();

				$fs->mkdir($sCacheFile);
			}

			$config = \HTMLPurifier_Config::createDefault();

			$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
			$config->set('Cache.SerializerPath', $this->options->get('cache_dir').'/HTMLPurifier');

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
					'map', // name
					'Block', // content set
					'Flow', // allowed children
					'Common', // attribute collection
					array( // attributes
						'name' => 'CDATA',
						'id' => 'ID',
						'title' => 'CDATA',
					)
				);
				$map->excludes = array('map' => true);

				# autorise l'élément area
				$area = $def->addElement(
					'area', // name
					'Block', // content set
					'Empty', // don't allow children
					'Common', // attribute collection
					array( // attributes
						'name' => 'CDATA',
						'id' => 'ID',
						'alt' => 'Text',
						'coords' => 'CDATA',
						'accesskey' => 'Character',
						'nohref' => new \HTMLPurifier_AttrDef_Enum(array('nohref')),
						'href' => 'URI',
						'shape' => new \HTMLPurifier_AttrDef_Enum(array('rect','circle','poly','default')),
						'tabindex' => 'Number',
						'target' => new \HTMLPurifier_AttrDef_Enum(array('_blank','_self','_target','_top'))
					)
				);
				$area->excludes = array('area' => true);
			}

			# get it now !
			$this->htmlpurifier = new \HTMLPurifier($config);
		}

		$str = $this->htmlpurifier->purify($str);

		return $str;
	}
}
