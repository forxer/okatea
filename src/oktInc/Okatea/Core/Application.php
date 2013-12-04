<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Core;

use Okatea\Cache\SingleFileCache;
use Okatea\Routing\Router;

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
	public $cache = null; /**< Le gestionnaire de cache, instance de \ref Okatea\Cache\SingleFileCache */
	public $config = null; /**< Le gestionnaire de configuration, instance de \ref oktConfig */
	public $db = null; /**< Le gestionnaire de base de données, instance de \ref mysql */
	public $error = null; /**< Le gestionnaire d'erreurs, instance de \ref oktErrors */
	public $help = null; /**< Le gestionnaire des fichiers d'aide */
	public $languages = null; /**< Le gestionnaire de langues, instance de \ref oktLanguages */
	public $logAdmin = null; /**< Le gestionnaire de log admin, instance de \ref oktLogAdmin */
	public $modules = null; /**< Le gestionnaire de modules, instance de \ref oktModules */
	public $page = null; /**< L'utilitaire de contenu de page, instance de \ref htmlPage */
	public $router = null; /**< Le routeur interne pour gérer les URL, instance de \ref Okatea\Routing\Router */
	public $tpl = null; /**< Le moteur de templates, instance de \ref Templating */
	public $triggers = null; /**< Le gestionnaire de déclencheurs, instance de \ref oktTriggers */
	public $user = null; /**< Le gestionnaire d'utilisateur en cours, instance de \ref oktAuth */
	public $autoloader = null;

	protected $permsStack = array(); /**< La pile qui contient les permissions. */
	protected $behaviorsStack = array(); /**< La pile qui contient les comportements. */
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

		$this->cache = new SingleFileCache(OKT_GLOBAL_CACHE_FILE);

		# Chargement de la configuration du site
		$this->loadConfig();

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

		$this->triggers = new \oktTriggers();

		$this->router = new Router();
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


	/* Gestion des comportements (behaviors/hooks/etc.)
	----------------------------------------------------------*/

	/**
	 * Ajoute un nouveau comportement à la pile de comportements.
	 *
	 * @param string $behavior Nom du comportement
	 * @param mixed  $func Fonction à appeller, doit être un callback valide
	 * @return void
	 * @deprecated use $okt->triggers->registerTrigger() instead
	 */
	public function addBehaviors($behavior, $func)
	{
		$this->triggers->registerTrigger($behavior, $func);
	}

	/**
	 * Test si un comportement particulier existe dans la pile de comportements.
	 *
	 * @param string $behavior Nom du comportement
	 * @return boolean
	 * @deprecated use $okt->triggers->hasTrigger() instead
	 */
	public function hasBehaviors($behavior)
	{
		return $this->triggers->hasTrigger($behavior);
	}

	/**
	 * Permet d'obtenir la pile des comportements (ou une partie si le paramètre est précisé).
	 *
	 * @param     string    $behavior    Nom du comportement
	 * @return array
	 * @deprecated use $okt->triggers->getTriggers() instead
	 */
	public function getBehaviors($behavior='')
	{
		return $this->triggers->getTriggers($behavior);
	}

	/**
	 * Appelle chaque fonction dans la pile de comportements pour
	 * un comportement donné et les retourne les résultats concaténés
	 * de chaque fonction.
	 *
	 * @param     string    $behavior    Nom du comportement
	 * @return string
	 * @deprecated use $okt->triggers->callTrigger() instead
	 */
	public function callBehavior($behavior)
	{
		$args = func_get_args();
		return call_user_func_array(array($this->triggers,'callTrigger'), $args);
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
	 * @return object Okatea\Modules\Module
	 */
	public function __get($module_id)
	{
		return $this->modules->getModuleObject($module_id);
	}

	/**
	 * Retourne un objet module
	 *
	 * @param $module_id
	 * @return object Okatea\Modules\Module
	 */
	public function module($module_id)
	{
		return $this->modules->getModuleObject($module_id);
	}


	/* Divers...
	----------------------------------------------------------*/

	public function performCommonContentReplacements($string)
	{
		return templateReplacement::parse($string, $this->getCommonContentReplacementsVariables());
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
			if (!file_exists(OKT_CACHE_PATH.'/HTMLPurifier')) {
				files::makeDir(OKT_CACHE_PATH.'/HTMLPurifier', true);
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
