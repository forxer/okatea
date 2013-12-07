<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Core;

use Tao\Cache\SingleFileCache;
use Tao\Routing\Router;
use Tao\Themes\SimpleReplacements;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;

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
	public $cache = null; /**< Le gestionnaire de cache, instance de \ref Tao\Cache\SingleFileCache */
	public $config = null; /**< Le gestionnaire de configuration, instance de \ref Tao\Core\Config */
	public $db = null; /**< Le gestionnaire de base de données, instance de \ref Tao\Database\MySqli */
	public $error = null; /**< Le gestionnaire d'erreurs, instance de \ref Tao\Core\Errors */
	public $languages = null; /**< Le gestionnaire de langues, instance de \ref Tao\Core\Languages */
	public $logAdmin = null; /**< Le gestionnaire de log admin, instance de \ref Tao\Core\LogAdmin */
	public $modules = null; /**< Le gestionnaire de modules, instance de \ref Tao\Core\Modules\Collection */
	public $page = null; /**< L'utilitaire de contenu de page, instance de \ref Tao\Html\Page */
	public $router = null; /**< Le routeur interne pour gérer les URL, instance de \ref Tao\Routing\Router */
	public $tpl = null; /**< Le moteur de templates, instance de \ref Tao\Core\Templating */
	public $triggers = null; /**< Le gestionnaire de déclencheurs, instance de \ref Tao\Core\Triggers */
	public $user = null; /**< Le gestionnaire d'utilisateur en cours, instance de \ref Tao\Core\Authentification */
	public $autoloader = null;

	protected $permsStack = array(); /**< La pile qui contient les permissions. */
	protected $htmlpurifier = null; /**< L'objet HTMLPurifier si il est instancié, sinon null */

	protected $aTplDirectories = array(); /**< la liste des répertoires où le moteur de templates doit chercher le template à interpréter */

	/**
	 * Constructeur. Initialise les gestionnaires d'erreurs et de base de données.
	 *
	 * @return void
	 */
	public function __construct($autoloader)
	{
		$this->autoloader = $autoloader;

		# initialisation du gestionnaire d'erreurs
		$this->error = new Errors;

		# initialisation du gestionnaire de base de données
		$this->db = Connexion::getInstance();

		if ($this->db->hasError()) {
			$this->error->fatal('Unable to connect to database',$this->db->error());
		}

		/*
		$connectionParams = array(
			'dbname' => OKT_DB_NAME,
			'user' => OKT_DB_USER,
			'password' => OKT_DB_PWD,
			'host' => OKT_DB_HOST,
			'driver' => OKT_DB_DRIVER,
			'charset' => 'UTF8'
		);

		$this->conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, new \Doctrine\DBAL\Configuration());

		*/

		//OKT_DB_PREFIX

		$this->cache = new SingleFileCache(OKT_GLOBAL_CACHE_FILE);

		$this->triggers = new Triggers();

		$this->router = new Router();

		$this->loadConfig();
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
		$this->tpl = new Templating($this->aTplDirectories);
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
		$this->config = $this->newConfig('conf_site');

		$this->config->app_host = \http::getHost();
		$this->config->app_url = $this->config->app_host.$this->config->app_path;
		$this->config->self_uri = $this->config->app_host.$_SERVER['REQUEST_URI'];
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
			$sCacheFile = OKT_CACHE_PATH.'/HTMLPurifier';

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

} # class
