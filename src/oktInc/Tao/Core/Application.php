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
use Symfony\Component\Routing\RequestContext;

use Tao\Cache\SingleFileCache;
use Tao\Misc\FlashMessages;
use Tao\Misc\Utilities;
use Tao\Modules\Collection as ModulesCollection;
use Tao\Navigation\Menus\Menus;
use Tao\Routing\Router;
use Tao\Themes\SimpleReplacements;



#-----------------------------------------------------------------
# TO DELETE

	define('OKT_XDEBUG', function_exists('xdebug_is_enabled'));

	define('OKT_FILENAME' , 'truc');

# TO DELETE
#-----------------------------------------------------------------




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
	 * Les menus de navigation.
	 *
	 * @var Tao\Core\ApplicationOptions
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
	 * @var Tao\Routing\Router
	 */
	public $router;

	/**
	 * Le gestionnaire de session.
	 *
	 * @var Tao\Core\Session
	 */
	public $session;

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
	 * Constructor.
	 *
	 * @param Composer\Autoload\ClassLoader $autoloader
	 * @param string $sRootPath
	 * @param string $sEnv
	 * @param boolean $bDebug
	 *
	 * @return void
	 */
	public function __construct($autoloader, $sRootPath, $sEnv = 'prod', $bDebug = false, array $aOptions = array())
	{
		# Autoloader shortcut
		$this->autoloader = $autoloader;

		$this->options = new ApplicationOptions($sRootPath, $aOptions);

		$this->start($bDebug);

		$this->db = $this->database();

		$this->triggers = new Triggers();

		$this->cache = new SingleFileCache($this->options->get('cache_dir').'/static.php');

		$this->config = $this->loadConfig();

		$this->request = Request::createFromGlobals();

		$this->response = new Response();

		$this->requestContext = new RequestContext();
		$this->requestContext->fromRequest($this->request);

		$this->session = new Session(null, null, new FlashMessages('okt_flashes'), $this->options->get('csrf_token_name'));
		$this->request->setSession($this->session);

		$this->languages = new Languages($this);

		$this->router = new Router($this, $this->options->get('config_dir').'/routes', $this->options->get('cache_dir').'/routing', $bDebug);

		$this->user = new Authentification($this, $this->options->get('cookie_auth_name'), $this->options->get('cookie_auth_from'), $this->config->app_path, '', $this->request->isSecure());

		$this->l10n = new Localisation($this->options->get('locales_dir'), $this->user->language, $this->user->timezone);

		$this->navigation = new Menus($this);

		$this->modules = new ModulesCollection($this, $this->options->get('modules_dir'), $this->options->modules_url);
	}

	/**
	 * Make common operations on start.
	 *
	 */
	protected function start($bDebug = false)
	{
		# Register start time
		define('OKT_START_TIME', microtime(true));

		# Init MB ext
		mb_internal_encoding('UTF-8');

		# Default timezone (crushed later by user settings)
		date_default_timezone_set('Europe/Paris');

		$this->error = new Errors();

		if ($bDebug)
		{
			Debug::enable();
			ErrorHandler::register();
			ExceptionHandler::register();
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
			$this->error->fatal('Fatal error: unable to find database connexion file !');
		}

		require $this->options->get('config_dir').'/connexion.php';

		$db = Connexion::getInstance();

		if ($db->hasError()) {
			$this->error->fatal('Unable to connect to database', $db->error());
		}

		return $db;
	}

	/**
	 * Load modules public or admin part.
	 *
	 * @return void
	 */
	protected function loadModules($sPart)
	{
		$this->modules->loadModules($sPart, $this->user->language);
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
	 * Chargement de la configuration du site.
	 *
	 * @return void
	 */
	public function loadConfig()
	{
		$config = $this->newConfig('conf_site');

		# URL du dossier modules
		$this->options->set('modules_url', $config->app_path.'/oktModules');

		# URL du dossier des fichiers publics
		$this->options->set('public_url', $config->app_path.'/oktPublic');

		# URL du dossier upload depuis la racine
		$this->options->set('upload_url', $config->app_path.'/oktPublic/upload');

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
		return new Config($this->cache, $this->options->get('config_dir').'/'.$file);
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
