<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Core;

use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RequestContext;

use Tao\Cache\SingleFileCache;
use Tao\Modules\Collection as ModulesCollection;
use Tao\Navigation\Menus\Menus;
use Tao\Routing\Router;
use Tao\Themes\SimpleReplacements;

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
	/**
	 * L'instance de l'autoloader.
	 *
	 * @var Composer\Autoload\ClassLoader
	 */
	public $autoloader;

	/**
	 * Le gestionnaire du fichier cache de configuration.
	 *
	 * @var Tao\Cache\SingleFileCache
	 */
	public $cache;

	/**
	 * Le gestionnaire de configuration.
	 *
	 * @var Tao\Core\Config
	 */
	public $config;

	/**
	 * Le gestionnaire de base de données.
	 *
	 * @var Tao\Database\MySqli
	 */
	public $db;

	/**
	 * Le gestionnaire d'erreurs.
	 *
	 * @var Tao\Core\Errors
	 */
	public $error;

	/**
	 * Le gestionnaire de langues.
	 *
	 * @var Tao\Core\Languages
	 */
	public $languages;

	/**
	 * Le gestionnaire de langues.
	 *
	 * @var Tao\Core\Localisation
	 */
	public $l10n;

	/**
	 * Le gestionnaire de log admin.
	 *
	 * @var Tao\Core\LogAdmin
	 */
	public $logAdmin;

	/**
	 * Le gestionnaire de modules.
	 *
	 * @var Tao\Core\Modules\Collection
	 */
	public $modules;

	/**
	 * Les menus de navigation.
	 *
	 * @var Tao\Navigation\Menus\Menus
	 */
	public $navigation;

	/**
	 * L'utilitaire de contenu de page.
	 *
	 * @var Tao\Html\Page
	 */
	public $page;

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
	 * @var Tao\Core\Router
	 */
	public $router;

	/**
	 * Le gestionnaire de session.
	 *
	 * @var Tao\Core\Session
	 */
	public $session;

	/**
	 * L'identifiant tu theme à afficher.
	 *
	 * @var string
	 */
	public $theme;

	/**
	 * Le moteur de templates.
	 *
	 * @var Tao\Core\Templating
	 */
	public $tpl;

	/**
	 * Le gestionnaire de déclencheurs.
	 *
	 * @var Tao\Core\Triggers
	 */
	public $triggers;

	/**
	 * Le gestionnaire d'utilisateur en cours.
	 *
	 * @var Tao\Core\Authentification
	 */
	public $user;

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
	 * Constructeur. Initialise toute la mécanique Okatea.
	 *
	 * @return void
	 */
	public function __construct($autoloader)
	{
		# Autoloader shortcut
		$this->autoloader = $autoloader;

		$this->start();

		$this->db = $this->database();

		$this->triggers = new Triggers();

		$this->cache = new SingleFileCache(OKT_GLOBAL_CACHE_FILE);

		$this->config = $this->loadConfig();

		$this->request = Request::createFromGlobals();

		$this->response = new Response();

		$this->requestContext = new RequestContext();
		$this->requestContext->fromRequest($this->request);

		$this->session = new Session();
		$this->request->setSession($this->session);

		$this->languages = new Languages($this);

		$this->router = new Router($this, OKT_CONFIG_PATH.'/routes', OKT_CACHE_PATH.'/routing', OKT_DEBUG);

		$this->user = new Authentification($this, OKT_COOKIE_AUTH_NAME, OKT_COOKIE_AUTH_FROM, $this->config->app_path, '', $this->request->isSecure());

		$this->l10n = new Localisation($this->user->language, $this->user->timezone);

		$this->navigation = new Menus($this);

		$this->modules = new ModulesCollection($this, OKT_MODULES_PATH, OKT_MODULES_URL);

		$this->theme = $this->getTheme();

		$this->tpl = $this->initTplEngine();
	}

	protected function start()
	{
		# Register start time
		define('OKT_START_TIME', microtime(true));

		# Init MB ext
		mb_internal_encoding('UTF-8');

		# Default timezone (crushed later by user settings)
		date_default_timezone_set('Europe/Paris');

		$this->error = new Errors();

		if (OKT_DEBUG)
		{
			Debug::enable();
			ErrorHandler::register();
			ExceptionHandler::register();
		}
	}

	protected function database()
	{
		if (file_exists(OKT_CONFIG_PATH.'/connexion.php')) {
			require_once OKT_CONFIG_PATH.'/connexion.php';
		}
		else {
			$this->error->fatal('Fatal error: unable to find database connexion file !');
		}

		$db = Connexion::getInstance();

		if ($db->hasError()) {
			$this->error->fatal('Unable to connect to database', $db->error());
		}

		return $db;
	}

	protected function getTheme()
	{
		$sOktTheme = $this->config->theme;

		if ($this->session->has('okt_theme')) {
			$sOktTheme = $this->session->get('okt_theme');
		}
		elseif (!empty($this->config->theme_mobile) || !empty($this->config->theme_tablet))
		{
			$oMobileDetect = new \Mobile_Detect();
			$isMobile = $oMobileDetect->isMobile() && !empty($this->config->theme_mobile);
			$isTablet = $oMobileDetect->isTablet() && !empty($this->config->theme_tablet);

			if ($isMobile && !$isTablet) {
				$sOktTheme = $this->config->theme_mobile;
			}
			elseif ($isTablet) {
				$sOktTheme = $this->config->theme_tablet;
			}

			$this->session->set('okt_theme', $sOktTheme);
		}

		# URL du thème
		define('OKT_THEME', $this->config->app_path.OKT_THEMES_DIR.'/'.$sOktTheme);

		# Chemin du thème
		define('OKT_THEME_PATH', OKT_THEMES_PATH.'/'.$sOktTheme);

		return $sOktTheme;
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
		else if ($this->user->is_superadmin) {
			return true;
		}

		return in_array($permissions,$this->user->perms);
	}


	/* Templates engine
	----------------------------------------------------------*/

	/**
	 * Initialise le moteur de templates.
	 *
	 * @return void
	 */
	public function initTplEngine()
	{
		# enregistrement des répertoires de templates
		$this->setTplDirectory(OKT_THEME_PATH.'/templates/%name%.php');
		$this->setTplDirectory(OKT_THEMES_PATH.'/default/templates/%name%.php');

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
	 * Chargement de la configuration du site.
	 *
	 * @return void
	 */
	public function loadConfig()
	{
		$config = $this->newConfig('conf_site');

		# URL du dossier modules
		define('OKT_MODULES_URL', $config->app_path.OKT_MODULES_DIR);

		# URL du dossier des fichiers publics
		define('OKT_PUBLIC_URL', $config->app_path.OKT_PUBLIC_DIR);

		# URL du dossier upload depuis la racine
		define('OKT_UPLOAD_URL', $config->app_path.OKT_PUBLIC_DIR.'/'.OKT_UPLOAD_DIR);

		return $config;
	}

	/**
	 * Créer et retourne un objet de configuration
	 *
	 * @param string $file
	 * @return object oktConfig
	 */
	public function newConfig($file)
	{
		return new Config($this->cache, OKT_CONFIG_PATH.'/'.$file);
	}


	/* Raccourcis modules
	----------------------------------------------------------*/

	/**
	 * Magic get retourne un objet module
	 *
	 * @param $module_id
	 * @return object Tao\Modules\Module
	 */
	public function __get($module_id)
	{
		return $this->modules->getModuleObject($module_id);
	}

	/**
	 * Retourne un objet module
	 *
	 * @param $module_id
	 * @return object Tao\Modules\Module
	 */
	public function module($module_id)
	{
		return $this->modules->getModuleObject($module_id);
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
			'theme_url' => OKT_THEME,
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
			$sCacheFile = OKT_CACHE_PATH.'/htmlpurifier';

			try
			{
				if (!file_exists($sCacheFile))
				{
					$fs = new Filesystem();

					$fs->mkdir($sCacheFile);
				}
			}
			catch (IOExceptionInterface $e) {
				$this->error->set('An error occurred while creating your directory at '.$e->getPath());
				return $str;
			}

			$config = \HTMLPurifier_Config::createDefault();

			$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
			$config->set('Cache.SerializerPath', OKT_CACHE_PATH.'/HTMLPurifier');

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
