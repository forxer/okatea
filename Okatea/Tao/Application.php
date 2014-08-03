<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao;

use Monolog\ErrorHandler;
use Okatea\Admin\Router as adminRouter;
use Okatea\Tao\Config\Config;
use Okatea\Tao\Config\ConfigServiceProvider;
use Okatea\Tao\Database\MySqli;
use Okatea\Tao\Database\DatabaseServiceProvider;
use Okatea\Tao\Extensions\ExtensionsServiceProvider;
use Okatea\Tao\L10n\L10nServiceProvider;
use Okatea\Tao\L10n\Localization;
use Okatea\Tao\LoggerServiceProvider;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Navigation\Menus\Menus;
use Okatea\Tao\RequestServiceProvider;
use Okatea\Tao\Routing\RouterServiceProvider;
use Okatea\Tao\Session\SessionServiceProvider;
use Okatea\Tao\Themes\SimpleReplacements;
use Okatea\Tao\Triggers\TriggersServiceProvider;
use Okatea\Tao\Users\UsersServiceProvider;
use Patchwork\Utf8\Bootup as Utf8Bootup;
use Pimple\Container;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\ErrorHandler as DebugErrorHandler;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

class Application extends Container
{
	const VERSION = '2.0-beta6';

	/**
	 * L'instance de l'autoloader.
	 *
	 * @var Composer\Autoload\ClassLoader
	 */
	public $autoloader;

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
	 * Le préfix des tables de la base de données.
	 *
	 * @var string
	 */
	public $db_prefix;

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
	 * La réponse qui va être renvoyée.
	 *
	 * @var Symfony\Component\HttpFoundation\Response
	 */
	public $response;

	/**
	 * Le routeur interne de l'administration.
	 *
	 * @var Okatea\Tao\Routing\AdminRouter
	 */
	public $adminRouter;

	/**
	 * Le moteur de templates.
	 *
	 * @var Okatea\Tao\Templating
	 */
	public $tpl;

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
	protected $aPermsStack = [];

	/**
	 * La liste des répertoires où le moteur de templates
	 * doit chercher le template à interpréter.
	 *
	 * @var array
	 */
	protected $aTplDirectories = [];

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
		# Register start time
		define('OKT_START_TIME', microtime(true));

		parent::__construct($aOptions);

		$this->autoloader = $autoloader;

		$this->register(new ConfigServiceProvider());
		$this->register(new DatabaseServiceProvider());
		$this->register(new ExtensionsServiceProvider());
		$this->register(new L10nServiceProvider());
		$this->register(new LoggerServiceProvider());
		$this->register(new RequestServiceProvider());
		$this->register(new RouterServiceProvider());
		$this->register(new SessionServiceProvider());
		$this->register(new TriggersServiceProvider());
		$this->register(new UsersServiceProvider());

		$this->Utf8Bootup();

		$this['request']->setSession($this['session']);

		# URL du dossier des fichiers publics
		$this['public_url'] = $this['config']->getData('app_path') . 'oktPublic';

		# URL du dossier upload depuis la racine
		$this['upload_url'] = $this['config']->getData('app_path') . 'oktPublic/upload';

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
	 * Return application version.
	 *
	 * @return string
	 */
	public function getVersion()
	{
		return self::VERSION;
	}

	/**
	 * Run main application.
	 *
	 * @return void
	 */
	public function run()
	{
		$this->startDatabase();

		$this['l10n']->loadFile($this['locales_dir'] . '/%s/main');
		$this['l10n']->loadFile($this['locales_dir'] . '/%s/users');

		$this->navigation = new Menus($this);
	}

	protected function Utf8Bootup()
	{
		# Enables the portablity layer and configures PHP for UTF-8
		Utf8Bootup::initAll();

		# Redirects to an UTF-8 encoded URL if it's not already the case
		Utf8Bootup::filterRequestUri();

		# Normalizes HTTP inputs to UTF-8 NFC
		Utf8Bootup::filterRequestInputs();
	}

	/**
	 * Make database connexion.
	 *
	 * @return object
	 */
	public function startDatabase()
	{
		if (null === $this->db)
		{
			$sConnectionFilename = $this['config_dir'] . '/connection.php';

			if (! file_exists($sConnectionFilename)) {
				throw new \RuntimeException('Unable to find database connection file !');
			}

			require $sConnectionFilename;

			$this->db = new MySqli($sDbUser, $sDbPassword, $sDbHost, $sDbName, $sDbPrefix);

			if ($this->db->hasError()) {
				throw new \RuntimeException('Unable to connect to database. ' . $this->db->error());
			}
		}
	}

	public function startAdminRouter()
	{
		if (null === $this->adminRouter)
		{
			$this->adminRouter = new adminRouter(
				$this,
				$this['config_dir'] . '/RoutesAdmin',
				$this['cache_dir'] . '/routing/admin',
				$this['debug'],
				$this['logger']
			);
		}
	}

	/**
	 * Load public or admin modules parts.
	 *
	 * @return void
	 */
	protected function loadModules($sPart)
	{
		$this['modules']->load($sPart);
	}

	/**
	 * Load public or admin themes parts.
	 *
	 * @return void
	 */
	protected function loadThemes($sPart)
	{
		$this['themes']->load($sPart);
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
		return $this->aPermsStack;
	}

	/**
	 * Ajout d'une permission
	 *
	 * @param string $perm Identifiant de la permission
	 * @param string $libelle Intitulé de la permission
	 * @param string $group Groupe de la permission (null)
	 * @return void
	 */
	public function addPerm($perm, $libelle, $group = null)
	{
		if ($group) {
			$this->aPermsStack[$group]['perms'][$perm] = $libelle;
		}
		else {
			$this->aPermsStack[$perm] = $libelle;
		}
	}

	/**
	 * Ajout d'un groupe de permissions.
	 *
	 * @param string $group
	 * @param string $libelle
	 * @return void
	 */
	public function addPermGroup($group, $libelle)
	{
		$this->aPermsStack[$group] = [
			'libelle' => $libelle,
			'perms' => []
		];
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
			return $this['visitor']->is_superadmin;
		}
		elseif ($this['visitor']->is_superadmin) {
			return true;
		}

		return in_array($permissions, $this['visitor']->perms);
	}

	public function getPermsForDisplay()
	{
		$aPermissions = [];

		foreach ($this->getPerms() as $k => $v)
		{
			if (! is_array($v))
			{
				if (! isset($aPermissions['others']))
				{
					$aPermissions['others'] = [
						'libelle' => '',
						'perms' => []
					];
				}

				if ($this->checkPerm($k)) {
					$aPermissions['others']['perms'][$k] = $v;
				}
			}
			else
			{
				$aPermissions[$k] = [
					'libelle' => $v['libelle'],
					'perms' => []
				];

				foreach ($v['perms'] as $perm => $libelle)
				{
					if ($this->checkPerm($perm))
					{
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
	 * @param string $sDirectoryPath
	 *        	Le chemin du répertoire
	 * @param boolean $bPriority
	 *        	Ajoute en haut de la pile
	 * @return void
	 */
	public function setTplDirectory($sDirectoryPath, $bPriority = false)
	{
		if ($bPriority)
		{
			return array_unshift($this->aTplDirectories, $sDirectoryPath);
		}
		else
		{
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

	/* Divers...
	----------------------------------------------------------*/

	/**
	 * Créer et retourne un objet de configuration
	 *
	 * @param string $file
	 * @return object oktConfig
	 */
	public function newConfig($file)
	{
		return new Config($this['cacheConfig'], $this['config_dir'] . '/' . $file);
	}

	/**
	 * Retourne un objet module.
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
			'app_path' => $this['config']->app_path,
			'user_language' => $this['visitor']->language,
			//	'theme_url' => $this->theme->url,
			'website_title' => $this['config']->title[$this['visitor']->language],
			'website_desc' => $this['config']->desc[$this['visitor']->language],

			'address_street' => $this['config']->address['street'],
			'address_street_2' => $this['config']->address['street_2'],
			'address_code' => $this['config']->address['code'],
			'address_city' => $this['config']->address['city'],
			'address_country' => $this['config']->address['country'],
			'address_phone' => (! empty($this['config']->address['tel']) ? $this['config']->address['tel'] : $this['config']->address['mobile']),
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
			$sCacheFile = $this['cache_dir'] . '/htmlpurifier';

			(new Filesystem())->mkdir($sCacheFile);

			$config = \HTMLPurifier_Config::createDefault();

			$config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
			$config->set('Cache.SerializerPath', $this['cache_dir'] . '/HTMLPurifier');

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
						'nohref' => new \HTMLPurifier_AttrDef_Enum(array(
							'nohref'
						)),
						'href' => 'URI',
						'shape' => new \HTMLPurifier_AttrDef_Enum(array(
							'rect',
							'circle',
							'poly',
							'default'
						)),
						'tabindex' => 'Number',
						'target' => new \HTMLPurifier_AttrDef_Enum(array(
							'_blank',
							'_self',
							'_target',
							'_top'
						))
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
