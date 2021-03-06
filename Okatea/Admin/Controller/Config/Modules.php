<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Config;

use GuzzleHttp\Client;
use Okatea\Admin\Controller;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Extensions\Modules\Collection as ModulesCollection;
use Okatea\Tao\Extensions\Themes\Collection as ThemesCollection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Modules extends Controller
{
	protected $aAllModules;

	protected $aInstalledModules;

	protected $aUninstalledModules;

	protected $aUpdatablesModules;

	protected $aModulesRepositories;

	public function page()
	{
		if (!$this->okt['visitor']->checkPerm('modules')) {
			return $this->serve401();
		}

		$this->init();

		# Show changelog
		if (($showChangelog = $this->showChangelog()) !== false) {
			return $showChangelog;
		}

		# Enable a module
		if (($enableModule = $this->enableModule()) !== false) {
			return $enableModule;
		}

		# Disable a module
		if (($disableModule = $this->disableModule()) !== false) {
			return $disableModule;
		}

		# Install a module
		if (($installModule = $this->installModule()) !== false) {
			return $installModule;
		}

		# Update a module
		if (($updateModule = $this->updateModule()) !== false) {
			return $updateModule;
		}

		# Uninstall a module
		if (($uninstallModule = $this->uninstallModule()) !== false) {
			return $uninstallModule;
		}

		# Re-install a module
		if (($reinstallModule = $this->reinstallModule()) !== false) {
			return $reinstallModule;
		}

		# Install test set of a module
		if (($installTestSet = $this->installTestSet()) !== false) {
			return $installTestSet;
		}

		# Install default data of a module
		if (($installDefaultData = $this->installDefaultData()) !== false) {
			return $installDefaultData;
		}

		# Remove content of a module
		if (($removeModuleContent = $this->removeModuleContent()) !== false) {
			return $removeModuleContent;
		}

		# Remove a module
		if (($removeModule = $this->removeModule()) !== false) {
			return $removeModule;
		}

		# Replace templates files of a module by its default ones
		if (($replaceTemplatesFiles = $this->replaceTemplatesFiles()) !== false) {
			return $replaceTemplatesFiles;
		}

		# Replace assets files of a module by its default ones
		if (($replaceAssetsFiles = $this->replaceAssetsFiles()) !== false) {
			return $replaceAssetsFiles;
		}

		# Package and send a module
		if (($packageAndSendModule = $this->packageAndSendModule()) !== false) {
			return $packageAndSendModule;
		}

		# Compare module files
		if (($compareFiles = $this->compareFiles()) !== false) {
			return $compareFiles;
		}

		# Add a module to the system
		if (($moduleUpload = $this->moduleUpload()) !== false) {
			return $moduleUpload;
		}

		return $this->render('Config/Modules/List', [
			'aAllModules'            => $this->aAllModules,
			'aInstalledModules'      => $this->aInstalledModules,
			'aUninstalledModules'    => $this->aUninstalledModules,
			'aUpdatablesModules'     => $this->aUpdatablesModules,
			'aModulesRepositories'   => $this->aModulesRepositories
		]);
	}

	protected function init()
	{
		# Modules management locales
		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/modules');

		# Retrieving the list of modules in the file system (all modules)
		$this->aAllModules = $this->okt['modules']->getManager()->getAll();

		# Retrieving the list of modules in the database (installed modules)
		$this->aInstalledModules = $this->okt['modules']->getManager()->getInstalled();

		# Computing the list of uninstalled modules
		$this->aUninstalledModules = array_diff_key($this->aAllModules, $this->aInstalledModules);

		# Load uninstalled modules main locales files
		foreach ($this->aUninstalledModules as $sModuleId => $aModuleInfos)
		{
			$this->okt['l10n']->loadFile($aModuleInfos['root'] . '/Locales/%s/main');

			$this->aUninstalledModules[$sModuleId]['name_l10n'] = __($aModuleInfos['name']);
		}

		# Modules repositories list
		$this->aModulesRepositories = [];
		if ($this->okt['config']->repositories['modules']['enabled']) {
			$this->aModulesRepositories = $this->okt['modules']->getRepositoriesData($this->okt['config']->repositories['modules']['list']);
		}

		# List of updates available on any repositories
		$this->aUpdatablesModules = [];
		foreach ($this->aModulesRepositories as $repo_name => $modules)
		{
			foreach ($modules as $module)
			{
				$this->aModulesRepositories[$repo_name][$module['id']]['name_l10n'] = $module['name'];

				if (isset($this->aAllModules[$module['id']]) && $this->aAllModules[$module['id']]['updatable'] && version_compare($this->aAllModules[$module['id']]['version'], $module['version'], '<'))
				{
					$this->aUpdatablesModules[$module['id']] = [
						'id'          => $module['id'],
						'name'        => $module['name'],
						'version'     => $module['version'],
						'info'        => $module['info'],
						'repository'  => $repo_name
					];
				}
			}
		}

		# Sorting alphabetically lists
		ModulesCollection::sort($this->aInstalledModules);
		ModulesCollection::sort($this->aUninstalledModules);

		foreach ($this->aModulesRepositories as $repo_name => $modules) {
			ModulesCollection::sort($this->aModulesRepositories[$repo_name]);
		}
	}

	protected function showChangelog()
	{
		$sModuleId = $this->okt['request']->query->get('show_changelog');
		$sChangelogFile = $this->okt['modules_path'] . '/' . $sModuleId . '/CHANGELOG';

		if (!$sModuleId || !file_exists($sChangelogFile)) {
			return false;
		}

		$sChangelogContent = '<pre class="changelog">' . file_get_contents($sChangelogFile) . '</pre>';

		return (new Response())->setContent($sChangelogContent);
	}

	protected function enableModule()
	{
		$sModuleId = $this->okt['request']->query->get('enable');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aInstalledModules)) {
			return false;
		}

		$this->okt['modules']->getManager()->enableExtension($sModuleId);

		# vidange du cache global
		Utilities::deleteOktCacheFiles();

		# log admin
		$this->okt['logAdmin']->warning([
			'code'       => 30,
			'message'    => $sModuleId
		]);

		return $this->redirect($this->generateUrl('config_modules'));
	}

	protected function disableModule()
	{
		$sModuleId = $this->okt['request']->query->get('disable');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aInstalledModules)) {
			return false;
		}

		$this->okt['modules']->getManager()->disableExtension($sModuleId);

		# vidange du cache global
		Utilities::deleteOktCacheFiles();

		# log admin
		$this->okt['logAdmin']->warning([
			'code'       => 31,
			'message'    => $sModuleId
		]);

		return $this->redirect($this->generateUrl('config_modules'));
	}

	protected function installModule()
	{
		$sModuleId = $this->okt['request']->query->get('install');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aUninstalledModules)) {
			return false;
		}

		@ini_set('memory_limit', - 1);
		set_time_limit(0);

		$oInstallModule = $this->okt['modules']->getInstaller($sModuleId);
		$oInstallModule->doInstall();

		# activation du module
		$oInstallModule->checklist->addItem(
			'enable_module',
			$this->okt['modules']->getManager()->enableExtension($sModuleId),
			'Enable module',
			'Cannot enable module'
		);

		# vidange du cache global
		Utilities::deleteOktCacheFiles();

		if ($oInstallModule->checklist->checkAll()) {
			$this->okt['flashMessages']->success(__('c_a_modules_correctly_installed'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_modules_not_installed'));
		}

		# log admin
		$this->okt['logAdmin']->warning([
			'code'       => 20,
			'message'    => $sModuleId
		]);

		return $this->render('Config/Modules/Install', [
			'oInstallModule' => $oInstallModule
		]);
	}

	protected function updateModule()
	{
		$sModuleId = $this->okt['request']->query->get('update');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aInstalledModules)) {
			return false;
		}

		# D'abord on active le module
		if (!$this->okt['modules']->isLoaded($sModuleId))
		{
			$this->okt['modules']->getManager()->enableExtension($sModuleId);

			$this->okt['modules']->generateCacheList();

			return $this->redirect($this->generateUrl('config_modules') . '?update=' . $sModuleId);
		}

		# Ensuite on met à jour
		$oInstallModule = $this->okt['modules']->getInstaller($sModuleId);
		$oInstallModule->doUpdate();

		if ($oInstallModule->checklist->checkAll()) {
			$this->okt['flashMessages']->success(__('c_a_modules_correctly_updated'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_modules_not_updated'));
		}

		Utilities::deleteOktCacheFiles();

		$this->okt['logAdmin']->critical([
			'code'       => 21,
			'message'    => $sModuleId
		]);

		$sNextUrl = $this->generateUrl('config_modules');

		if (file_exists($oInstallModule->root() . '/Install/Templates/')
			|| file_exists($oInstallModule->root() . '/Install/Assets/'))
		{
			$sNextUrl .= '?compare=' . $oInstallModule->id();
		}

		return $this->render('Config/Modules/Update', [
			'oInstallModule' => $oInstallModule,
			'sNextUrl'       => $sNextUrl
		]);
	}

	protected function uninstallModule()
	{
		$sModuleId = $this->okt['request']->query->get('uninstall');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aInstalledModules)) {
			return false;
		}

		@ini_set('memory_limit', - 1);
		set_time_limit(0);

		$oInstallModule = $this->okt['modules']->getInstaller($sModuleId);
		$oInstallModule->doUninstall();

		Utilities::deleteOktCacheFiles();

		if ($oInstallModule->checklist->checkAll()) {
			$this->okt['flashMessages']->success(__('c_a_modules_correctly_uninstalled'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_modules_not_uninstalled'));
		}

		$this->okt['logAdmin']->critical([
			'code'       => 22,
			'message'    => $sModuleId
		]);

		return $this->render('Config/Modules/Uninstall', [
			'oInstallModule' => $oInstallModule
		]);
	}

	protected function reinstallModule()
	{
		$sModuleId = $this->okt['request']->query->get('reinstall');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aInstalledModules)) {
			return false;
		}

		@ini_set('memory_limit', - 1);
		set_time_limit(0);

		# il faut d'abord désactiver le module
		if ($this->okt['modules']->isLoaded($sModuleId))
		{
			$this->okt['modules']->getManager()->disableExtension($sModuleId);

			$this->okt['modules']->generateCacheList();

			return $this->redirect($this->generateUrl('config_modules') . '?reinstall=' . $sModuleId);
		}

		$oInstallModule = $this->okt['modules']->getInstaller($sModuleId);

		# désinstallation
		$oInstallModule->doUninstall();

		# installation
		$oInstallModule->doInstall();

		# activation du module
		$oInstallModule->checklist->addItem(
			'enable_module',
			$this->okt['modules']->getManager()->enableExtension($sModuleId),
			'Enable module',
			'Cannot enable module'
		);

		# vidange du cache global
		Utilities::deleteOktCacheFiles();

		if ($oInstallModule->checklist->checkAll()) {
			$this->okt['flashMessages']->success(__('c_a_modules_correctly_reinstalled'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_modules_not_correctly_reinstalled.'));
		}

		# log admin
		$this->okt['logAdmin']->critical([
			'code'       => 23,
			'message'    => $sModuleId
		]);

		return $this->render('Config/Modules/Reinstall', [
			'oInstallModule' => $oInstallModule
		]);
	}

	protected function installTestSet()
	{
		$sModuleId = $this->okt['request']->query->get('testset');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aInstalledModules)) {
			return false;
		}

		@ini_set('memory_limit', - 1);
		set_time_limit(0);

		$oInstallModule = $this->okt['modules']->getInstaller($sModuleId);

		# d'abord on vident le module
		$oInstallModule->doEmpty();

		# ensuite on installent les données par défaut
		$oInstallModule->doInstallDefaultData();

		# et ensuite on installent le jeu de test
		$oInstallModule->doInstallTestSet();

		if ($oInstallModule->checklist->checkAll()) {
			$this->okt['flashMessages']->success(__('c_a_modules_test_set_correctly_installed'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_modules_test_set_not_correctly_installed'));
		}

		$this->okt['logAdmin']->critical([
			'message' => 'install test set ' . $sModuleId
		]);

		return $this->render('Config/Modules/InstallTestSet', [
			'oInstallModule' => $oInstallModule
		]);
	}

	protected function installDefaultData()
	{
		$sModuleId = $this->okt['request']->query->get('defaultdata');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aInstalledModules)) {
			return false;
		}

		@ini_set('memory_limit', - 1);
		set_time_limit(0);

		$oInstallModule = $this->okt['modules']->getInstaller($sModuleId);

		$oInstallModule->doInstallDefaultData();

		if ($oInstallModule->checklist->checkAll()) {
			$this->okt['flashMessages']->success(__('c_a_modules_test_set_correctly_installed'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_modules_test_set_not_correctly_installed'));
		}

		$this->okt['logAdmin']->warning([
			'message' => 'install default data ' . $sModuleId
		]);

		return $this->render('Config/Modules/InstallDefaultData', [
			'oInstallModule' => $oInstallModule
		]);
	}

	protected function removeModuleContent()
	{
		$sModuleId = $this->okt['request']->query->get('empty');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aInstalledModules)) {
			return false;
		}

		@ini_set('memory_limit', - 1);
		set_time_limit(0);

		$oInstallModule = $this->okt['modules']->getInstaller($sModuleId);
		$oInstallModule->doEmpty();

		if ($oInstallModule->checklist->checkAll()) {
			$this->okt['flashMessages']->success(__('c_a_modules_correctly_emptied'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_modules_not_correctly_emptied'));
		}

		$this->okt['logAdmin']->critical([
			'message' => 'remove content of module ' . $sModuleId
		]);

		return $this->render('Config/Modules/RemoveContent', [
			'oInstallModule' => $oInstallModule
		]);
	}

	protected function removeModule()
	{
		$sModuleId = $this->okt['request']->query->get('delete');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aUninstalledModules)) {
			return false;
		}

		$fs = new Filesystem();

		if ($fs->remove($this->okt['modules_path'] . '/' . $sModuleId))
		{
			$this->okt['flashMessages']->success(__('c_a_modules_successfully_deleted'));

			$this->okt['logAdmin']->warning([
				'code'      => 42,
				'message'   => $sModuleId
			]);

			return $this->redirect($this->generateUrl('config_modules'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_modules_not_deleted.'));
		}
	}

	protected function replaceTemplatesFiles()
	{
		$sModuleId = $this->okt['request']->query->get('templates');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aInstalledModules)) {
			return false;
		}

		$oInstallModule = $this->okt['modules']->getInstaller($sModuleId);
		$oInstallModule->forceReplaceTpl();

		$this->okt['modules']->generateCacheList();

		$this->okt['flashMessages']->success(__('c_a_modules_templates_files_replaced'));

		return $this->redirect($this->generateUrl('config_modules'));
	}

	protected function replaceAssetsFiles()
	{
		$sModuleId = $this->okt['request']->query->get('assets');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aInstalledModules)) {
			return false;
		}

		$oInstallModule = $this->okt['modules']->getInstaller($sModuleId);
		$oInstallModule->forceReplaceAssets();

		$this->okt['modules']->generateCacheList();

		$this->okt['flashMessages']->success(__('c_a_modules_assets_files_replaced'));

		return $this->redirect($this->generateUrl('config_modules'));
	}

	protected function packageAndSendModule()
	{
		$sModuleId = $this->okt['request']->query->get('download');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aAllModules)) {
			return false;
		}

		$sModulePath = $this->okt['modules_path'] . '/' . $sModuleId;

		if (!is_readable($sModulePath)) {
			return false;
		}

		$sFilename = 'module-' . $sModuleId . '-' . date('Y-m-d-H-i') . '.zip';

		ob_start();

		$fp = fopen('php://output', 'wb');

		$zip = new \fileZip($fp);
		$zip->addDirectory($sModulePath, '', true);

		$zip->write();

		$this->response->headers->set('Content-Disposition', $this->response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $sFilename));

		$this->response->setContent(ob_get_clean());

		return $this->response;
	}

	protected function compareFiles()
	{
		$sModuleId = $this->okt['request']->query->get('compare');

		if (!$sModuleId || !array_key_exists($sModuleId, $this->aInstalledModules)) {
			return false;
		}

		$oInstallModule = $this->okt['modules']->getInstaller($sModuleId);
		$oInstallModule->compareFiles();

		return $this->render('Config/Modules/Compare', [
			'oInstallModule' => $oInstallModule
		]);
	}

	protected function moduleUpload()
	{
		$upload_pkg = $this->okt['request']->request->get('upload_pkg');
		$pkg_file = $this->okt['request']->files->get('pkg_file');

		$fetch_pkg = $this->okt['request']->request->get('fetch_pkg');
		$pkg_url = $this->okt['request']->request->get('pkg_url');

		$repository = $this->okt['request']->query->get('repository');
		$module = $this->okt['request']->query->get('module');

		# Plugin upload
		if (($upload_pkg && $pkg_file) || ($fetch_pkg && $pkg_url) || ($repository && $module && $this->okt['config']->repositories['modules']['enabled']))
		{
			try
			{
				if ($upload_pkg)
				{
					if (array_key_exists($pkg_file->getClientOriginalName(), $this->aUninstalledModules)) {
						throw new \Exception(__('c_a_modules_module_already_exists_not_installed_install_before_update'));
					}

					$pkg_file->move($this->okt['modules_path']);
				}
				else
				{
					if ($repository && $module)
					{
						$repository = urldecode($repository);
						$module = urldecode($module);
						$url = urldecode($this->aModulesRepositories[$repository][$module]['href']);
					}
					else {
						$url = urldecode($pkg_url);
					}

					$dest = $this->okt['modules_path'] . '/' . basename($url);

					if (array_key_exists(basename($url), $this->aUninstalledModules)) {
						throw new \Exception(__('c_a_modules_module_already_exists_not_installed_install_before_update'));
					}

					try
					{
						$response = (new Client())->get($url, [
							'exceptions' => false,
							'save_to'    => $dest
						]);
					}
					catch (\Exception $e) {
						throw new \Exception(__('An error occurred while downloading the file.'));
					}
				}

				$ret_code = $this->okt['modules']->installPackage($dest);

				if ($ret_code == 2) {
					$this->okt['flashMessages']->success(__('c_a_modules_module_successfully_upgraded'));
				}
				else {
					$this->okt['flashMessages']->success(__('c_a_modules_module_successfully_added'));
				}

				return $this->redirect($this->generateUrl('config_modules'));
			}
			catch (\Exception $e)
			{
				$this->okt['instantMessages']->error($e->getMessage());
				return false;
			}
		}

		return false;
	}
}
