<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules;

use Okatea\Core\Authentification;
use Okatea\Database\XmlSql;
use Okatea\Html\CheckList;
use Okatea\Themes\Collection as ThemesCollection;
use Forxer\Diff\Engine as DiffEngine;
use Forxer\Diff\Renderer\Html\SideBySide as DiffRenderer;

/**
 * Installation d'un module Okatea.
 *
 */
class ModuleInstall extends Module
{
	/**
	 * Une checklist
	 * @var object
	 */
	public $checklist;

	/**
	 * Constructeur
	 *
	 * @param core $okt
	 * @param string $id
	 * @return void
	 */
	public function __construct($okt, $modules_path, $id)
	{
		parent::__construct($okt,$modules_path);

		$this->checklist = new CheckList();

		# get infos from define file
		$this->setInfo('id', $id);
		$this->setInfosFromDefineFile();
	}


	/* Méthodes d'installation
	----------------------------------------------------------*/

	/**
	 * Perform module install
	 *
	 * @return void
	 */
	public function doInstall()
	{
		if (!$this->preInstall())
		{
			$this->checklist->addItem(
				'install_aborted',
				false,
				'Install aborted...',
				'Install aborted...'
			);

			return false;
		}

		# opérations communes à l'installation et la mise à jour
		$this->commonInstallUpdate('install');

		# ajout d'éventuelles données par défaut à la base de données
		$this->doInstallDefaultData();

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'install')) {
			$this->install();
		}

		# ajout du module à la base de données
		$this->checklist->addItem(
			'add_module_to_db',
			$this->okt->modules->addModule($this->id(),$this->version(),$this->name(),$this->desc(),$this->author(),$this->priority(),0),
			'Add module to database',
			'Cannot add module to database'
		);

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'installEnd')) {
			$this->installEnd();
		}
	}


	/* Méthodes de mise à jour
	----------------------------------------------------------*/

	/**
	 * Perform update
	 *
	 * @return void
	 */
	public function doUpdate()
	{
		# opérations communes à l'installation et la mise à jour
		$this->commonInstallUpdate('update');

		# merge des configuration
		$this->mergingConfigFiles();

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'update')) {
			$this->update();
		}

		# ajout du module à la base de données
		$this->checklist->addItem(
			'update_module_in_db',
			$this->okt->modules->updModule($this->id(),$this->version(),$this->name(),$this->desc(),$this->author(),$this->priority()),
			'Update module into database',
			'Cannot update module into database'
		);

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'updateEnd')) {
			$this->updateEnd();
		}
	}


	/* Méthodes de désinstallation
	----------------------------------------------------------*/

	/**
	 * Perform uninstall
	 *
	 * @return void
	 */
	public function doUninstall()
	{
		if (method_exists($this,'uninstall')) {
			$this->uninstall();
		}

		# désinstallation de la base de données
		$this->loadDbFile($this->root().'/_install/db-uninstall.xml');

		# suppression des fichiers templates
		if (is_dir($this->root().'/_install/tpl'))
		{
			$d = dir($this->root().'/_install/tpl');
			while (false !== ($entry = $d->read()))
			{
				if (!is_dir($entry) && $entry != '.' && $entry != '..' && $entry != '.svn') {
					$this->uninstallTplFile($this->root().'/_install/tpl/'.$entry);
				}
			}
			$d->close();
		}

		# suppression des fichiers public
		if (is_dir($this->root().'/_install/public'))
		{
			$d = dir($this->root().'/_install/public');
			while (false !== ($entry = $d->read()))
			{
				if (!is_dir($entry) && $entry != '.' && $entry != '..' && $entry != '.svn') {
					$this->uninstallPublicFile($this->root().'/_install/public/'.$entry);
				}
			}
			$d->close();
		}

		# suppression des fichiers d'upload
		$sUploadDir = OKT_UPLOAD_PATH.'/'.$this->id().'/';
		if (file_exists($sUploadDir))
		{
			$this->checklist->addItem(
				'remove_upload_dir',
				\files::deltree($sUploadDir),
				'Remove upload dir',
				'Cannot remove upload dir'
			);
		}

		# suppression des fichiers assets
		foreach (ThemesCollection::getThemes() as $sThemeId=>$sTheme)
		{
			$sAssetsDir = OKT_THEMES_PATH.'/'.$sThemeId.'/modules/'.$this->id().'/';

			if (file_exists($sAssetsDir))
			{
				$this->checklist->addItem(
					'remove_assets_dir_'.$sThemeId,
					$this->removeAssetsFiles($sAssetsDir, ThemesCollection::getLockedFiles($sThemeId)),
					'Remove assets dir in '.$sTheme.' theme',
					'Cannot remove assets dir '.$sTheme.' theme'
				);
			}
		}

		# suppression des fichiers de config
		$this->deleteConfigFiles();

		# suppression du module de la base de données
		$this->checklist->addItem(
			'remove_module_from_db',
			$this->okt->modules->deleteModule($this->id()),
			'Remove module from database',
			'Cannot remove module from database'
		);

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'uninstallEnd')) {
			$this->uninstallEnd();
		}
	}


	/* Méthodes de vidage du module
	----------------------------------------------------------*/

	/**
	 * Perform empty module
	 *
	 * @return void
	 */
	public function doEmpty()
	{
		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'truncate')) {
			$this->truncate();
		}

		# vidange de la base de données
		$this->loadDbFile($this->root().'/_install/db-truncate.xml');

		# suppression des fichiers d'upload
		$sUploadDir = OKT_UPLOAD_PATH.'/'.$this->id().'/';
		if (file_exists($sUploadDir))
		{
			$this->checklist->addItem(
				'remove_upload_dir',
				\files::deltree($sUploadDir),
				'Remove upload dir',
				'Cannot remove upload dir'
			);
		}

		# utilisation d'une méthode personnalisée si elle existe
		if (method_exists($this,'truncateEnd')) {
			$this->truncateEnd();
		}
	}


	/* Méthodes d'installation des jeux de test
	----------------------------------------------------------*/

	/**
	 * Perform install test set
	 *
	 * @return void
	 */
	public function doInstallTestSet()
	{
		# ajout d'éventuelles données à la base de données
		$this->loadDbFile($this->root().'/_install/test_set/db-data.xml');

		# création d'un répertoire upload
		$this->copyUploadFiles();

		if (method_exists($this,'installTestSet')) {
			$this->installTestSet();
		}
	}

	protected function copyUploadFiles()
	{
		if (is_dir($this->root().'/_install/test_set/upload/'))
		{
			$this->checklist->addItem(
				'upload_dir',
				$this->forceReplaceUploads(),
				'Create upload dir',
				'Cannot create upload dir'
			);
		}
	}


	/* Méthodes d'installation des jeux de test
	----------------------------------------------------------*/

	/**
	 * Perform install default data
	 *
	 * @return void
	 */
	public function doInstallDefaultData()
	{
		# ajout d'éventuelles données à la base de données
		$this->loadDbFile($this->root().'/_install/db-data.xml');

		if (method_exists($this,'installDefaultData')) {
			$this->installDefaultData();
		}
	}


	/* Méthodes sur les fichiers
	----------------------------------------------------------*/

	public function compareFiles()
	{
		# prevent error from compare lib...
		@ini_set('display_errors', 'Off');

		# compare templates
		$this->compareFolder($this->root().'/_install/tpl/', OKT_THEMES_PATH.'/default/templates/');

		foreach (ThemesCollection::getThemes() as $sThemeId=>$sTheme)
		{
			if ($sThemeId == 'default') {
				continue;
			}

			$this->compareFolder($this->root().'/_install/tpl/', OKT_THEMES_PATH.'/'.$sThemeId.'/templates/', true);
		}

		# compare assets
		$this->compareFolder($this->root().'/_install/assets/', OKT_THEMES_PATH.'/default/modules/'.$this->id().'/');

		foreach (ThemesCollection::getThemes() as $sThemeId=>$sTheme)
		{
			if ($sThemeId == 'default') {
				continue;
			}

			$this->compareFolder($this->root().'/_install/assets/', OKT_THEMES_PATH.'/'.$sThemeId.'/modules/'.$this->id().'/', true);
		}

		# compare publics
		$this->compareFolder($this->root().'/_install/public/', OKT_ROOT_PATH.'/');
	}

	/**
	 * Force le remplacement des fichiers de template
	 *
	 */
	public function forceReplaceTpl()
	{
		return $this->forceReplaceFiles(
			$this->root().'/_install/tpl/',
			OKT_THEMES_PATH.'/default/templates/'
		);
	}

	/**
	 * Force le remplacement des fichiers actifs
	 *
	 */
	public function forceReplaceAssets($sBaseDir, $aLockedFiles=array())
	{
		return $this->forceReplaceFiles(
			$this->root().'/_install/assets/',
			$sBaseDir.'/modules/'.$this->id().'/',
			$aLockedFiles
		);
	}

	/**
	 * Force le remplacement des fichiers public
	 *
	 */
	public function forceReplacePublic()
	{
		return $this->forceReplaceFiles(
			$this->root().'/_install/public/',
			OKT_ROOT_PATH.'/'
		);
	}

	/**
	 * Force le remplacement des fichiers d'upload
	 *
	 */
	public function forceReplaceUploads()
	{
		return $this->forceReplaceFiles(
			$this->root().'/_install/test_set/upload/',
			OKT_UPLOAD_PATH.'/'.$this->id().'/'
		);
	}

	/**
	 * Désinstallation d'un fichier de template
	 *
	 * @param string $file
	 * @return void
	 */
	protected function uninstallTplFile($file)
	{
		static $count;

		if (empty($count)) {
			$count = 1;
		}

		$filename = basename($file);

		# suppression du fichier .bak de façon silencieuse
		if (file_exists(OKT_THEMES_PATH.'/default/templates/'.$filename.'.bak')) {
			@unlink(OKT_THEMES_PATH.'/default/templates/'.$filename.'.bak');
		}

		# si le fichier existe on le supprime
		if (file_exists(OKT_THEMES_PATH.'/default/templates/'.$filename))
		{
			$this->checklist->addItem(
				'tpl_file_'.$count,
				(is_dir(OKT_THEMES_PATH.'/default/templates/'.$filename) ? \files::deltree(OKT_THEMES_PATH.'/default/templates/'.$filename) : unlink(OKT_THEMES_PATH.'/default/templates/'.$filename)),
				'Remove template file '.$filename,
				'Cannot remove template file '.$filename
			);
		}
		else {
			$this->checklist->addItem(
				'tpl_file_'.$count,
				null,
				'Template file '.$filename.' doesn\'t exists',
				'Template file '.$filename.' doesn\'t exists'
			);
		}

		$count++;
	}

	/**
	 * Désinstallation d'un fichier public
	 *
	 * @param string $file
	 * @return void
	 */
	protected function uninstallPublicFile($file)
	{
		static $count;

		if (empty($count)) {
			$count = 1;
		}

		$filename = basename($file);

		# suppression du fichier .bak de façon silencieuse
		if (file_exists(OKT_ROOT_PATH.'/'.$filename.'.bak')) {
			@unlink(OKT_ROOT_PATH.'/'.$filename.'.bak');
		}

		# si le fichier existe on le supprime
		if (file_exists(OKT_ROOT_PATH.'/'.$filename))
		{
			$this->checklist->addItem(
				'public_file_'.$count,
				unlink(OKT_ROOT_PATH.'/'.$filename),
				'Remove public file '.$filename,
				'Cannot remove public file '.$filename
			);
		}
		else {
			$this->checklist->addItem(
				'public_file_'.$count,
				null,
				'Public file '.$filename.' doesn\'t exists',
				'Public file '.$filename.' doesn\'t exists'
			);
		}

		$count++;
	}

	protected function getConfigFiles()
	{
		$aFiles = array();

		if (is_dir($this->root().'/_install/'))
		{
			$d = dir($this->root().'/_install/');
			while (false !== ($entry = $d->read()))
			{
				$sExt = pathinfo($entry,PATHINFO_EXTENSION);

				if (($sExt === 'ini' || $sExt === 'yaml') && substr($entry,0,5) === 'conf_')
				{
					$aFiles[] = array(
						'filename' => $entry,
						'ext' => $sExt,
						'basename' => str_replace('.'.$sExt, '', $entry)
					);
				}
			}
			$d->close();
		}

		return $aFiles;
	}

	/**
	 * Copie des fichiers de configuration
	 *
	 */
	protected function copyConfigFiles()
	{
		$aFiles = $this->getConfigFiles();

		foreach ($aFiles as $aFile)
		{
			$this->checklist->addItem(
				'config_file_'.$aFile['basename'],
				copy(
					$this->root().'/_install/'.$aFile['filename'],
					OKT_CONFIG_PATH.'/'.$aFile['filename']
				),
				'Copy config file '.$aFile['filename'],
				'Cannot copy config file '.$aFile['filename']
			);
		}
	}

	private function mergingConfigFiles()
	{
		$aFiles = $this->getConfigFiles();

		foreach ($aFiles as $aFile)
		{
			$this->checklist->addItem(
				'merging_config_file_'.$aFile['basename'],
				$this->doConfigMerging($aFile['basename']),
				'Merging config file '.$aFile['filename'],
				'Cannot merging config file '.$aFile['filename']
			);
		}
	}

	private function doConfigMerging($filename)
	{
		$oConfig = $this->okt->newConfig($filename);
		$oConfig->write($oConfig->get());

		return true;
	}

	/**
	 * Copie des fichiers de configuration
	 *
	 */
	protected function deleteConfigFiles()
	{
		$aFiles = $this->getConfigFiles();

		foreach ($aFiles as $aFile)
		{
			# si le fichier cache existe on le supprime
			if (file_exists(OKT_CACHE_PATH.'/'.$aFile['filename'].'.php'))
			{
				$this->checklist->addItem(
					'cached_config_file_'.$aFile['basename'],
					unlink(OKT_CACHE_PATH.'/'.$aFile['filename'].'.php'),
					'Remove cached config file '.$aFile['filename'].'.php',
					'Cannot remove cached config file '.$aFile['filename'].'.php'
				);
			}
			else {
				$this->checklist->addItem(
					'config_file_'.$aFile['basename'],
					null,
					'Cached config file '.$aFile['filename'].' doesn\'t exists',
					'Cached config file '.$aFile['filename'].' doesn\'t exists'
				);
			}

			# si le fichier config existe on le supprime
			if (file_exists(OKT_CONFIG_PATH.'/'.$aFile['filename']))
			{
				$this->checklist->addItem(
					'config_file_'.$aFile['basename'],
					unlink(OKT_CONFIG_PATH.'/'.$aFile['filename']),
					'Remove config file '.$aFile['filename'],
					'Cannot remove config file '.$aFile['filename']
				);
			}
			else {
				$this->checklist->addItem(
					'config_file_'.$aFile['basename'],
					null,
					'Config file '.$aFile['filename'].' doesn\'t exists',
					'Config file '.$aFile['filename'].' doesn\'t exists'
				);
			}
		}
	}

	/**
	 * Force le remplacement des fichiers de façon récursive dans les fichiers
	 *
	 */
	protected function forceReplaceFiles($sSourceDir, $sDestDir, $aLockedFiles=array())
	{
		if (!is_dir($sSourceDir)) {
			return false;
		}

		$aSources = \files::getDirList($sSourceDir);
		$aDests = array();

		foreach ($aSources['files'] as $file) {
			$aDests[] = str_replace($sSourceDir,'',$file);
		}

		if (!is_dir($sDestDir)) {
			\files::makeDir($sDestDir,true);
		}

		foreach ($aDests as $file)
		{
			$parent_dir = dirname($sDestDir.$file);

			if (!is_dir($parent_dir)) {
				\files::makeDir($parent_dir,true);
			}

			if (in_array($sDestDir.$file, $aLockedFiles)) {
				continue;
			}

			if (file_exists($sDestDir.$file))
			{
				if (file_exists($sDestDir.$file.'.bak')) {
					unlink($sDestDir.$file.'.bak');
				}

				rename($sDestDir.$file, $sDestDir.$file.'.bak');
			}

			copy($sSourceDir.$file, $sDestDir.$file);
		}

		return true;
	}

	/**
	 * Comparaison des fichiers des deux dossiers donnés
	 *
	 * @param string $sSourceDir
	 * @param string $sDestDir
	 * @param boolean $bOptional
	 */
	protected function compareFolder($sSourceDir,$sDestDir,$bOptional=false)
	{
		if (!is_dir($sSourceDir)) {
			return false;
		}

		$aSources = \files::getDirList($sSourceDir);
		$aDests = array();

		foreach ($aSources['files'] as $file) {
			$aDests[] = str_replace($sSourceDir,'',$file);
		}

		$return = array();

		foreach ($aDests as $sFile)
		{
			$this->compareFile($sFile,$sSourceDir,$sDestDir,false,$bOptional);
			$this->compareFile($sFile,$sSourceDir,$sDestDir,true,$bOptional);
		}

		return true;
	}

	/**
	 * Comparaison de deux fichiers
	 *
	 * @param string $sFile
	 * @param string $sSourceDir
	 * @param string $sDestDir
	 * @param boolean $bTestBackup
	 * @param boolean $bOptional
	 * @return void
	 */
	protected function compareFile($sFile,$sSourceDir,$sDestDir,$bTestBackup=false,$bOptional=false)
	{
		$sSourceFile = $sSourceDir.$sFile;

		$sSourceBase = str_replace(OKT_ROOT_PATH, '', $sSourceDir);
		$sDestBase = str_replace(OKT_ROOT_PATH, '', $sDestDir);

		$sBaseSourceFile = $sSourceBase.$sFile;

		if ($bTestBackup) {
			$sFile .= '.bak';
		}

		$sBaseDestFile = $sDestBase.$sFile;

		if (!file_exists($sDestDir.$sFile))
		{
			if (!$bTestBackup)
			{
				$this->checklist->addItem(
					'file_exists_'.$sFile,
					($bOptional ? null : false),
					sprintf(__('c_a_modules_file_%s_not_exists'), '<code>'.$sBaseDestFile.'</code>'),
					sprintf(__('c_a_modules_file_%s_not_exists'), '<code>'.$sBaseDestFile.'</code>')
				);
			}
		}
		else
		{
			$l_text = file_get_contents($sSourceFile);
			$r_text = file_get_contents($sDestDir.$sFile);

			// Include two sample files for comparison
			$a = explode("\n", file_get_contents($sSourceFile));
			$b = explode("\n", file_get_contents($sDestDir.$sFile));

			// Options for generating the diff
			$options = array(
				//'ignoreWhitespace' => true,
				//'ignoreCase' => true,
			);

			$diff = new DiffEngine($a, $b, $options);
			$opCodes = $diff->getGroupedOpcodes();

			if (!empty($opCodes))
			{
				$renderer = new DiffRenderer();
				$renderer->diff = $diff;

				$ze_string = sprintf(
					__('c_a_modules_file_%s_different_%s'),
					'<code>'.$sBaseDestFile.'</code>',
					$renderer->render($sBaseSourceFile,$sBaseDestFile)
				);

				$this->checklist->addItem(
					'file_'.$sFile.'_different',
					null,
					$ze_string,
					$ze_string
				);
			}
			else
			{
				$this->checklist->addItem(
					'files_'.$sFile.'_identical',
					true,
					sprintf(__('c_a_modules_file_%s_identical'), '<code>'.$sDestBase.$sFile.'</code>'),
					sprintf(__('c_a_modules_file_%s_identical'), '<code>'.$sDestBase.$sFile.'</code>')
				);
			}
		}
	}

	/**
	 * Retourne le tableau HTML d'une comparaison de fichier.
	 *
	 * @param string $th1
	 * @param string $th2
	 * @param string $body
	 */
	protected static function getComparaisonTable($th1,$th2,$body)
	{
		return sprintf(
		'<table class="diff diff_sidebyside">'.PHP_EOL.
			"\t".'<tr>'.PHP_EOL.
				"\t\t".'<th colspan="2">'.PHP_EOL.
					"\t\t\t".'%s'.PHP_EOL.
				"\t\t".'</th>'.PHP_EOL.
				"\t\t".'<th colspan="2">'.PHP_EOL.
					"\t\t\t".'%s'.PHP_EOL.
				"\t\t".'</th>'.PHP_EOL.
			"\t".'</tr>'.PHP_EOL.
			"\t".'%s'.PHP_EOL.
		'</table>'.PHP_EOL
		,$th1,$th2,$body);
	}

	protected function copyTplFiles()
	{
		if (is_dir($this->root().'/_install/tpl/'))
		{
			$this->checklist->addItem(
				'tpl_dir',
				$this->forceReplaceTpl(),
				'Create templates files',
				'Cannot create templates files'
			);
		}
	}

	protected function copyAssetsFiles()
	{
		if (is_dir($this->root().'/_install/assets/'))
		{
			foreach (ThemesCollection::getThemes() as $sThemeId=>$sTheme)
			{
				$this->checklist->addItem(
					'assets_dir_'.$sThemeId,
					$this->forceReplaceAssets(OKT_THEMES_PATH.'/'.$sThemeId, ThemesCollection::getLockedFiles($sThemeId)),
					'Create assets dir in '.$sTheme.' theme',
					'Cannot create assets dir in '.$sTheme.' theme'
				);
			}
		}
	}

	protected function removeAssetsFiles($sAssetsDir, $aLockedFiles=array())
	{
		$aFiles = \files::getDirList($sAssetsDir);

		foreach ($aFiles['files'] as $sFiles)
		{
			if (!in_array($sFiles, $aLockedFiles)) {
				unlink($sFiles);
			}
		}

		foreach (array_reverse($aFiles['dirs']) as $sDir)
		{
			if (!\util::dirHasFiles($sDir)) {
				\files::deltree($sDir);
			}
		}

		return true;
	}


	/* Méthodes utilitaires
	----------------------------------------------------------*/

	/**
	 * Installation de la base de données depuis un fichier
	 *
	 * @param string $db_file
	 */
	protected function loadDbFile($db_file, $process=null)
	{
		static $count;

		if (empty($count)) {
			$count = 1;
		}

		if (file_exists($db_file))
		{
			$xsql = new XmlSql($this->db, file_get_contents($db_file), $this->checklist, $process);
			$xsql->replace('{{PREFIX}}',$this->okt->db->prefix);
			$xsql->execute();
		}
		else
		{
			$this->checklist->addItem(
				'db_file_'.$count,
				null,
				'DB file '.$db_file.' doesn\'t exists',
				'DB file '.$db_file.' doesn\'t exists'
			);
		}

		$count++;
	}

	/**
	 * Cette fonction test les pré-requis pour installer un module.
	 *
	 * @return boolean
	 */
	private function preInstall()
	{
		# présence du fichier /module_handler.php
		$this->checklist->addItem(
			'module_handler_file',
			file_exists($this->root().'/module_handler.php'),
			'Module handler file exists',
			'Module handler file doesn\'t exists'
		);

		# existence de la class module_<id_module>
		if ($this->checklist->checkItem('module_handler_file'))
		{
			include $this->root().'/module_handler.php';

			$this->checklist->addItem(
				'module_handler_class',
				class_exists('module_'.$this->id()),
				'Module handler class "module_'.$this->id().'" exists',
				'Module handler class "module_'.$this->id().'" doesn\'t exists'
			);

			$this->checklist->addItem(
				'module_handler_class_valide',
				is_subclass_of('module_'.$this->id(),'\Okatea\Modules\Module'),
				'Module handler class "module_'.$this->id().'" is a valid module class',
				'Module handler class "module_'.$this->id().'" is not a valid module class'
			);
		}

		return
			$this->checklist->checkItem('module_handler_file')
			&& $this->checklist->checkItem('module_handler_class');
	}

	/**
	 * Action communes à l'installation et la mise à jour
	 *
	 */
	private function commonInstallUpdate($process)
	{
		# installation/mise à jour de la base de données
		$this->loadDbFile($this->root().'/_install/db-install.xml',$process);

		# copie des éventuels fichiers templates
		$this->copyTplFiles();

		# création d'un répertoire common
		$this->copyAssetsFiles();

		# copie des éventuels fichiers de configurations
		$this->copyConfigFiles();
	}

	/**
	 * Ajout de permission par défaut au groupe admin.
	 *
	 * @param array $aDefaultPerms
	 */
	protected function setDefaultAdminPerms($aDefaultPerms=array())
	{
		$sTgroups = $this->db->prefix.'core_users_groups';

		$query =
		'SELECT perms '.
		'FROM '.$sTgroups.' '.
		'WHERE group_id='.(integer)Authentification::admin_group_id;

		$rsPerms = $this->db->select($query);

		$aCurrentPerms = array();
		if (!$rsPerms->isEmpty()) {
			$aCurrentPerms = unserialize($rsPerms->perms);
		}

		$aNewPerms = array_merge($aCurrentPerms,$aDefaultPerms);
		$aNewPerms = serialize($aNewPerms);

		$query =
		'UPDATE '.$sTgroups.' SET '.
		'perms=\''.$this->db->escapeStr($aNewPerms).'\' '.
		'WHERE group_id='.(integer)Authentification::admin_group_id;

		$this->db->execute($query);
	}

} # class
