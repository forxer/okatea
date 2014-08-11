<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Development\Bootstrap\Module;

use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Html\Modifiers;

class Module
{
	protected $id;

	protected $urlId;

	protected $urlIdFr;

	protected $upperId;

	protected $camelCaseId;

	protected $name;

	protected $nameFr;

	protected $description;

	protected $descriptionFr;

	protected $author;

	protected $version;

	protected $licence;

	protected $locales;

	protected $templates_dir;

	protected $templates = array(
		'config' => 'Install/conf.yml',
		'db-install' => 'Install/db-install.tpl',
		'db-truncate' => 'Install/db-truncate.tpl',
		'db-uninstall' => 'Install/db-uninstall.tpl',

		'public_list' => 'Install/public/list.tpl',
		'public_item' => 'Install/public/item.tpl',

		'tpl_base' => 'Install/Templates/base.tpl',
		'tpl_list' => 'Install/Templates/list.tpl',
		'tpl_item' => 'Install/Templates/item.tpl',

		'preview_icon' => 'Install/Assets/preview.png',
		'common_css' => 'Install/Assets/styles.tpl',

		'admin_index' => 'inc/admin/index.tpl',
		'admin_display' => 'inc/admin/display.tpl',
		'admin_config' => 'inc/admin/config.tpl',
		'admin_item' => 'inc/admin/item.tpl',

		'public_prepend_list' => 'inc/public/list.tpl',
		'public_prepend_item' => 'inc/public/item.tpl',

		'controller' => 'inc/class.controller.tpl',
		'filters' => 'inc/class.filters.tpl',
		'recordset' => 'inc/recordset.tpl',

		'locales_main_en' => 'Locales/locales_main_en.tpl',
		'locales_main_fr' => 'Locales/locales_main_fr.tpl',
		'locales_admin_en' => 'Locales/locales_admin_en.tpl',
		'locales_admin_fr' => 'Locales/locales_admin_fr.tpl',

		'define' => '_define.tpl',
		'admin' => 'admin.tpl',
		'changelog' => 'changelog.tpl',
		'header' => 'header.tpl',
		'index' => 'index.tpl',
		'licence_block' => 'licence_block.tpl',
		'module' => 'module.tpl'
	);

	protected $modules_dir;

	protected $dir;

	protected $common_replacements = [];

	protected $header;

	protected $licence_block;

	protected static $licencesList = array(
		//		'asf20' 	=> 'Apache License 2.0',
		//		'art' 		=> 'Artistic License/GPL',
		//	 	'epl' 		=> 'Eclipse Public License 1.0',
		'gpl2' => 'GNU General Public License v2',
		'gpl3' => 'GNU General Public License v3',
		'lgpl' => 'GNU Lesser General Public License v3',
		'mit' => 'MIT License',
		//	 	'mpl11' 	=> 'Mozilla Public License 1.1',
		//	 	'bsd' 		=> 'New BSD License',
		'none' => 'None'
	);

	/* Building methods
	----------------------------------------------------------*/

	/**
	 * Bootstrap the module
	 */
	public function build()
	{
		$this->initBuilding();
		$this->makeDirs();
		$this->makeFiles();
	}

	protected function initBuilding()
	{
		$this->id = $this->getId();
		$this->urlId = Modifiers::strToLowerUrl($this->name);
		$this->urlIdFr = Modifiers::strToLowerUrl($this->nameFr);
		$this->upperId = strtoupper($this->id);
		$this->camelCaseId = Modifiers::strToCamelCase($this->id);

		$this->dir = $this['modules']_dir . '/' . $this->id;

		$this->makeHeader();

		$this->common_replacements = array(
			'##header##' => $this->header,

			'##module_id##' => $this->id,
			'##module_url_id##' => $this->urlId,
			'##module_url_id_fr##' => $this->urlIdFr,
			'##module_upper_id##' => $this->upperId,
			'##module_camel_case_id##' => $this->camelCaseId,

			'##module_name##' => $this->name,
			'##module_description##' => $this->description,
			'##module_name_fr##' => $this->nameFr,
			'##module_description_fr##' => $this->descriptionFr,

			'##module_author##' => $this->author,
			'##module_version##' => $this->version,

			'##date##' => date('Y-m-d'),
			'##year##' => date('Y'),

			'##l10n_en_1##' => $this->locales['en'][1],
			'##l10n_en_2##' => $this->locales['en'][2],
			'##l10n_en_3##' => $this->locales['en'][3],
			'##l10n_en_4##' => $this->locales['en'][4],
			'##l10n_en_5##' => $this->locales['en'][5],
			'##l10n_en_6##' => $this->locales['en'][6],
			'##l10n_en_7##' => $this->locales['en'][7],
			'##l10n_en_8##' => $this->locales['en'][8],
			'##l10n_en_9##' => $this->locales['en'][9],
			'##l10n_en_10##' => $this->locales['en'][10],

			'##l10n_fr_1##' => $this->locales['fr'][1],
			'##l10n_fr_2##' => $this->locales['fr'][2],
			'##l10n_fr_3##' => $this->locales['fr'][3],
			'##l10n_fr_4##' => $this->locales['fr'][4],
			'##l10n_fr_5##' => $this->locales['fr'][5],
			'##l10n_fr_6##' => $this->locales['fr'][6],
			'##l10n_fr_7##' => $this->locales['fr'][7],
			'##l10n_fr_8##' => $this->locales['fr'][8],
			'##l10n_fr_9##' => $this->locales['fr'][9],
			'##l10n_fr_10##' => $this->locales['fr'][10],

			'##l10n_fr_fem##' => ($this->locales['fem'] ? 'e' : '')
		);
	}

	protected function makeHeader()
	{
		$this->makeLicenceBlock();

		$replacements = $this->getReplacements(array(
			'##licence_bloc##' => ($this->licence == 'none' ? '' : $this->licence_block)
		));

		$this->header = $this->replace($this->getTpl('header'), $replacements);
	}

	/**
	 * Build the Licence Block of the module and store it
	 */
	protected function makeLicenceBlock()
	{
		$replacements = $this->getReplacements(array(
			'##licence##' => $this->getLicenceBlock()
		));

		$this->licence_block = $this->replace($this->getTpl('licence_block'), $replacements);
	}

	/**
	 * Make basis directories
	 */
	protected function makeDirs()
	{
		if (file_exists($this->dir))
		{
			throw new \Exception(sprintf(__('m_development_bootstrap_module_allready_exists'), $this->id));
		}

		\files::makeDir($this->dir);
		\files::makeDir($this->dir . '/Install', true);
		\files::makeDir($this->dir . '/Install/Assets', true);
		\files::makeDir($this->dir . '/Install/public', true);
		\files::makeDir($this->dir . '/Install/Templates', true);
		//		\files::makeDir($this->dir.'/Install/TestSet',true);


		\files::makeDir($this->dir . '/inc', true);
		\files::makeDir($this->dir . '/inc/admin', true);
		\files::makeDir($this->dir . '/inc/public', true);

		\files::makeDir($this->dir . '/Locales', true);
		\files::makeDir($this->dir . '/Locales/fr', true);
		\files::makeDir($this->dir . '/Locales/en', true);
	}

	/**
	 * Make files
	 */
	protected function makeFiles()
	{
		$replacements = $this->getReplacements();

		$this->makeFile('db-install', $this->dir . '/Install/db-install.xml', $replacements);
		$this->makeFile('db-truncate', $this->dir . '/Install/db-truncate.xml', $replacements);
		$this->makeFile('db-uninstall', $this->dir . '/Install/db-uninstall.xml', $replacements);
		$this->makeFile('config', $this->dir . '/Install/conf_' . $this->id . '.yml', $replacements);

		copy($this->getTplPath('preview_icon'), $this->dir . '/Install/Assets/preview.png');
		$this->makeFile('common_css', $this->dir . '/Install/Assets/styles.css', $replacements);

		$this->makeFile('public_list', $this->dir . '/Install/public/oktPublic_' . $this->id . '_list.php', $replacements);
		$this->makeFile('public_item', $this->dir . '/Install/public/oktPublic_' . $this->id . '_item.php', $replacements);

		$this->makeFile('tpl_list', $this->dir . '/Install/Templates/' . $this->id . '_list_tpl.php', $replacements);
		$this->makeFile('tpl_item', $this->dir . '/Install/Templates/' . $this->id . '_item_tpl.php', $replacements);

		$this->makeFile('admin_index', $this->dir . '/Admin/index.php', $replacements);
		$this->makeFile('admin_display', $this->dir . '/Admin/display.php', $replacements);
		$this->makeFile('admin_config', $this->dir . '/Admin/config.php', $replacements);
		$this->makeFile('admin_item', $this->dir . '/Admin/item.php', $replacements);

		$this->makeFile('public_prepend_list', $this->dir . '/inc/public/list.php', $replacements);
		$this->makeFile('public_prepend_item', $this->dir . '/inc/public/item.php', $replacements);

		$this->makeFile('filters', $this->dir . '/inc/class.' . $this->id . '.filters.php', $replacements);
		$this->makeFile('recordset', $this->dir . '/inc/class.' . $this->id . '.recordset.php', $replacements);

		$this->makeFile('locales_main_en', $this->dir . '/Locales/en/main.lang.php', $replacements);
		$this->makeFile('locales_main_fr', $this->dir . '/Locales/fr/main.lang.php', $replacements);
		$this->makeFile('locales_admin_en', $this->dir . '/Locales/en/admin.lang.php', $replacements);
		$this->makeFile('locales_admin_fr', $this->dir . '/Locales/fr/admin.lang.php', $replacements);

		$this->makeFile('define', $this->dir . '/_define.php', $replacements);
		$this->makeFile('admin', $this->dir . '/admin.php', $replacements);
		$this->makeFile('changelog', $this->dir . '/CHANGELOG', $replacements);
		$this->makeFile('index', $this->dir . '/index.php', $replacements);
		$this->makeFile('module', $this->dir . '/module.php', $replacements);
	}

	/* Licences methods
	----------------------------------------------------------*/
	static public function getLicencesList($reverse = false)
	{
		return ($reverse ? array_flip(self::$licencesList) : self::$licencesList);
	}

	public function getLicenceBlock()
	{
		if ($this->licence == 'none')
		{
			return '#';
		}

		$block_replacements = array(
			//		'asf20' 	=> [],
			//		'art' 		=> [],
			//	 	'epl' 		=> [],
			'gpl2' => [],
			'gpl3' => [],
			'lgpl' => [],
			'mit' => []
		//	 	'mpl11' 	=> [],
		//	 	'bsd' 		=> [],

		);

		$this->templates[$this->licence . '_block'] = 'licences/' . $this->licence . '/block.tpl';
		return $this['tpl']Replace($this->licence . '_block', $block_replacements[$this->licence]);
	}

	public function makeLicenceFile()
	{
		if ($this->licence == 'none')
		{
			return null;
		}

		$licence_replacements = array(
			//		'asf20' 	=> [],
			//		'art' 		=> [],
			//	 	'epl' 		=> [],
			'gpl2' => [],
			'gpl3' => [],
			'lgpl' => [],
			'mit' => array(
				'#year#' => date('Y'),
				'#author#' => $this->author
			)
		//	 	'mpl11' 	=> [],
		//	 	'bsd' 		=> [],

		);

		$this->templates[$this->licence] = 'licences/' . $this->licence . '/licence.tpl';
		$this->makeFile($this->licence, $this->dir . '/LICENCE', $licence_replacements[$this->licence]);
	}

	/* getters/setters
	----------------------------------------------------------*/
	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getNameFr()
	{
		return $this->nameFr;
	}

	public function setNameFr($name)
	{
		$this->nameFr = $name;
		return $this;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	public function getDescriptionFr()
	{
		return $this->descriptionFr;
	}

	public function setDescriptionFr($description)
	{
		$this->descriptionFr = $description;
		return $this;
	}

	public function getAuthor()
	{
		return $this->author;
	}

	public function setAuthor($author)
	{
		$this->author = $author;
		return $this;
	}

	public function getVersion()
	{
		return $this->version;
	}

	public function setVersion($version)
	{
		$this->version = $version;
		return $this;
	}

	public function getLicence()
	{
		return $this->licence;
	}

	public function setLicence($licence)
	{
		if (array_key_exists($licence, self::$licencesList))
		{
			$this->licence = $licence;
		}
		else
		{
			$this->licence = 'other';
		}

		return $this;
	}

	public function getLocales()
	{
		return $this->locales;
	}

	public function setLocales($locales)
	{
		$this->locales = $locales;
		return $this;
	}

	public function getTemplatesDir()
	{
		return $this->templates_dir;
	}

	public function setTemplatesDir($templates_dir)
	{
		$this->templates_dir = $templates_dir;
		return $this;
	}

	public function getModulesDir()
	{
		return $this['modules']_dir;
	}

	public function setModulesDir($modules_dir)
	{
		$this['modules']_dir = $modules_dir;
		return $this;
	}

	/* Templates and replacement methods
	----------------------------------------------------------*/

	/**
	 * Return content of a template file
	 *
	 * @param string $tpl
	 *        	ID
	 * @return string
	 */
	protected function getTpl($tpl)
	{
		if (!$this['tpl']Exists($tpl))
		{
			throw new \Exception(sprintf(__('m_development_bootstrap_tpl_not_exists'), $this->templates[$tpl], $this->templates_dir));
		}

		return file_get_contents($this->getTplPath($tpl));
	}

	protected function tplExists($tpl)
	{
		return file_exists($this->getTplPath($tpl));
	}

	protected function getTplPath($tpl)
	{
		return $this->templates_dir . '/' . $this->templates[$tpl];
	}

	/**
	 * Make replacements
	 *
	 * @param string $str
	 * @param array $replacements
	 * @return string
	 */
	protected function replace($str, $replacements)
	{
		return str_replace(array_keys($replacements), array_values($replacements), $str);
	}

	/**
	 * Make replacement in a template file
	 *
	 * @param string $template_name
	 *        	ID
	 * @param array $replacements
	 * @return string
	 */
	protected function tplReplace($template_name, $replacements = [])
	{
		return $this->replace($this->getTpl($template_name), $replacements);
	}

	/**
	 * Make a file base on a template
	 *
	 * @param string $template_name
	 * @param string $destination
	 * @param array $replacements
	 * @return integer
	 */
	protected function makeFile($template_name, $destination, $replacements = [])
	{
		return file_put_contents($destination, $this['tpl']Replace($template_name, $replacements));
	}

	/**
	 * Merge common replacement with other and return it
	 *
	 * @param array $other_replacements
	 * @return array
	 */
	protected function getReplacements($other_replacements = [])
	{
		return array_merge($this->common_replacements, $other_replacements);
	}

	protected function getId()
	{
		$id = Modifiers::strToUnderscored($this->name);

		$id = preg_replace('/^([0-9_])*([a-zA-Z0-9_\x7f-\xff]+)$/', '$2', $id);

		return $id;
	}
}

