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
use Okatea\Tao\Extensions\Themes\Collection as ThemesCollection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Themes extends Controller
{
	protected $aAllThemes;

	protected $aInstalledThemes;

	protected $aUninstalledThemes;

	protected $aUpdatablesThemes;

	protected $aThemesRepositories;

	public function page()
	{
		if (!$this->okt['visitor']->checkPerm('themes')) {
			return $this->serve401();
		}

		$this->init();

		# Show changelog
		if (($showChangelog = $this->showChangelog()) !== false) {
			return $showChangelog;
		}

		# Show notes
		if (($showNotes = $this->showNotes()) !== false) {
			return $showNotes;
		}

		# Enable a theme
		if (($enableTheme = $this->enableTheme()) !== false) {
			return $enableTheme;
		}

		# Disable a theme
		if (($disableTheme = $this->disableTheme()) !== false) {
			return $disableTheme;
		}

		# Install a theme
		if (($installTheme = $this->installTheme()) !== false) {
			return $installTheme;
		}

		# Update a theme
		if (($updateTheme = $this->updateTheme()) !== false) {
			return $updateTheme;
		}

		# Uninstall a theme
		if (($uninstallTheme = $this->uninstallTheme()) !== false) {
			return $uninstallTheme;
		}

		# Re-install a theme
		if (($reinstallTheme = $this->reinstallTheme()) !== false) {
			return $reinstallTheme;
		}

		# Remove a theme
		if (($removeTheme = $this->removeTheme()) !== false) {
			return $removeTheme;
		}

		# Replace assets files of a theme by its default ones
		if (($replaceAssetsFiles = $this->replaceAssetsFiles()) !== false) {
			return $replaceAssetsFiles;
		}

		# Package and send a theme
		if (($packageAndSendTheme = $this->packageAndSendTheme()) !== false) {
			return $packageAndSendTheme;
		}

		# Compare theme files
		if (($compareFiles = $this->compareFiles()) !== false) {
			return $compareFiles;
		}

		# Add a theme to the system
		if (($themeUpload = $this->themeUpload()) !== false) {
			return $themeUpload;
		}

		# Use a theme
		if (($useTheme = $this->useTheme()) !== false) {
			return $useTheme;
		}

		# Use a mobile theme
		if (($useMobileTheme = $this->useMobileTheme()) !== false) {
			return $useMobileTheme;
		}

		# Use a tablet theme
		if (($useTabletTheme = $this->useTabletTheme()) !== false) {
			return $useTabletTheme;
		}

		return $this->render('Config/Themes/List', [
			'aAllThemes'             => $this->aAllThemes,
			'aInstalledThemes'       => $this->aInstalledThemes,
			'aUninstalledThemes'     => $this->aUninstalledThemes,
			'aUpdatablesThemes'      => $this->aUpdatablesThemes,
			'aThemesRepositories'    => $this->aThemesRepositories
		]);
	}

	protected function init()
	{
		# Themes management locales
		$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin/themes');

		# Retrieving the list of themes in the file system (all themes)
		$this->aAllThemes = $this->okt['themes']->getManager()->getAll();

		# Retrieving the list of themes in the database (the themes installed)
		$this->aInstalledThemes = $this->okt['themes']->getManager()->getInstalled();

		foreach ($this->aInstalledThemes as $sThemeId => $aThemeInfos)
		{
			# Searching for icons
			$this->aInstalledThemes[$sThemeId]['icon'] = null;

			$sInstallDirPath = $this->okt['public_path'] . '/themes/' . $sThemeId;

			if (!is_dir($sInstallDirPath)) {
				continue;
			}

			$this->aInstalledThemes[$sThemeId]['icon'] = ThemesCollection::findIcon($sInstallDirPath);
		}

		# Computing the list of uninstalled themes
		$this->aUninstalledThemes = array_diff_key($this->aAllThemes, $this->aInstalledThemes);

		foreach ($this->aUninstalledThemes as $sThemeId => $aThemeInfos)
		{
			# Load uninstalled themes main locales files
			$this->okt['l10n']->loadFile($aThemeInfos['root'] . '/Locales/%s/main');

			$this->aUninstalledThemes[$sThemeId]['name_l10n'] = __($aThemeInfos['name']);

			# Searching for icons
			$this->aUninstalledThemes[$sThemeId]['icon'] = null;

			$sInstallDirPath = $aThemeInfos['root'] . '/Install/Assets';

			if (is_dir($sInstallDirPath)) {
				$this->aUninstalledThemes[$sThemeId]['icon'] = ThemesCollection::findIcon($sInstallDirPath);
			}
		}

		# Themes repositories list
		$this->aThemesRepositories = [];

		if ($this->okt['config']->repositories['themes']['enabled']) {
			$this->aThemesRepositories = $this->okt['themes']->getRepositoriesData($this->okt['config']->repositories['themes']['list']);
		}

		# List of updates available on any repositories
		$this->aUpdatablesThemes = [];

		foreach ($this->aThemesRepositories as $repo_name => $themes)
		{
			foreach ($themes as $theme)
			{
				$this->aThemesRepositories[$repo_name][$theme['id']]['name_l10n'] = $theme['name'];

				if (isset($this->aAllThemes[$theme['id']]) && $this->aAllThemes[$theme['id']]['updatable'] && version_compare($this->aAllThemes[$theme['id']]['version'], $theme['version'], '<'))
				{
					$this->aUpdatablesThemes[$theme['id']] = [
						'id'          => $theme['id'],
						'name'        => $theme['name'],
						'version'     => $theme['version'],
						'info'        => $theme['info'],
						'repository'  => $repo_name
					];
				}
			}
		}

		# Sorting alphabetically lists
		ThemesCollection::sort($this->aInstalledThemes);
		ThemesCollection::sort($this->aUninstalledThemes);

		foreach ($this->aThemesRepositories as $repo_name => $themes) {
			ThemesCollection::sort($this->aThemesRepositories[$repo_name]);
		}
	}

	protected function showChangelog()
	{
		$sThemeId = $this->okt['request']->query->get('show_changelog');
		$sChangelogFile = $this->okt['themes_path'] . '/' . $sThemeId . '/CHANGELOG';

		if (!$sThemeId || !file_exists($sChangelogFile)) {
			return false;
		}

		$sChangelogContent = '<pre class="changelog">' . file_get_contents($sChangelogFile) . '</pre>';

		return (new Response())->setContent($sChangelogContent);
	}

	protected function showNotes()
	{
		$sThemeId = $this->okt['request']->query->get('show_notes');
		$sNotesFile = $this->okt['themes_path'] . '/' . $sThemeId . '/notes.md';

		if (!$sThemeId || !file_exists($sNotesFile)) {
			return false;
		}

		$sNotesContent = \Parsedown::instance()->parse(file_get_contents($this->okt['themes_path'] . '/' . $sThemeId . '/notes.md'));

		return (new Response())->setContent($sNotesContent);
	}

	protected function enableTheme()
	{
		$sThemeId = $this->okt['request']->query->get('enable');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)) {
			return false;
		}

		$this->okt['themes']->getManager()->enableExtension($sThemeId);

		# vidange du cache global
		Utilities::deleteOktCacheFiles();

		# log admin
		$this->okt['logAdmin']->warning([
			'code'       => 30,
			'message'    => $sThemeId
		]);

		return $this->redirect($this->generateUrl('config_themes'));
	}

	protected function disableTheme()
	{
		$sThemeId = $this->okt['request']->query->get('disable');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)
			|| ThemesCollection::DEFAULT_THEME == $sThemeId)
		{
			return false;
		}

		$this->okt['themes']->getManager()->disableExtension($sThemeId);

		# vidange du cache global
		Utilities::deleteOktCacheFiles();

		# log admin
		$this->okt['logAdmin']->warning([
			'code'       => 31,
			'message'    => $sThemeId
		]);

		return $this->redirect($this->generateUrl('config_themes'));
	}

	protected function installTheme()
	{
		$sThemeId = $this->okt['request']->query->get('install');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aUninstalledThemes)) {
			return false;
		}

		@ini_set('memory_limit', - 1);
		set_time_limit(0);

		$oInstallTheme = $this->okt['themes']->getInstaller($sThemeId);
		$oInstallTheme->doInstall();

		# activation du theme
		$oInstallTheme->checklist->addItem(
			'enable_theme',
			$this->okt['themes']->getManager()->enableExtension($sThemeId),
			'Enable theme',
			'Cannot enable theme'
		);

		# vidange du cache global
		Utilities::deleteOktCacheFiles();

		if ($oInstallTheme->checklist->checkAll()) {
			$this->okt['flashMessages']->success(__('c_a_themes_correctly_installed'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_themes_not_installed'));
		}

		# log admin
		$this->okt['logAdmin']->warning([
			'code'       => 20,
			'message'    => $sThemeId
		]);

		return $this->render('Config/Themes/Install', [
			'oInstallTheme' => $oInstallTheme
		]);
	}

	protected function updateTheme()
	{
		$sThemeId = $this->okt['request']->query->get('update');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)) {
			return false;
		}

		# D'abord on active le theme
		if (!$this->okt['themes']->isLoaded($sThemeId))
		{
			$this->okt['themes']->getManager()->enableExtension($sThemeId);

			$this->okt['themes']->generateCacheList();

			return $this->redirect($this->generateUrl('config_themes') . '?update=' . $sThemeId);
		}

		# Ensuite on met à jour
		$oInstallTheme = $this->okt['themes']->getInstaller($sThemeId);
		$oInstallTheme->doUpdate();

		if ($oInstallTheme->checklist->checkAll()) {
			$this->okt['flashMessages']->success(__('c_a_themes_correctly_updated'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_themes_not_updated'));
		}

		Utilities::deleteOktCacheFiles();

		$this->okt['logAdmin']->critical([
			'code'       => 21,
			'message'    => $sThemeId
		]);

		$sNextUrl = $this->generateUrl('config_themes');

		if (file_exists($oInstallTheme->root() . '/Install/Templates/')
			|| file_exists($oInstallTheme->root() . '/Install/Assets/'))
		{
			$sNextUrl .= '?compare=' . $oInstallTheme->id();
		}

		return $this->render('Config/Themes/Update', [
			'oInstallTheme'  => $oInstallTheme,
			'sNextUrl'       => $sNextUrl
		]);
	}

	protected function uninstallTheme()
	{
		$sThemeId = $this->okt['request']->query->get('uninstall');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)
			|| ThemesCollection::DEFAULT_THEME == $sThemeId)
		{
			return false;
		}

		@ini_set('memory_limit', - 1);
		set_time_limit(0);

		$oInstallTheme = $this->okt['themes']->getInstaller($sThemeId);
		$oInstallTheme->doUninstall();

		Utilities::deleteOktCacheFiles();

		if ($oInstallTheme->checklist->checkAll()) {
			$this->okt['flashMessages']->success(__('c_a_themes_correctly_uninstalled'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_themes_not_uninstalled'));
		}

		$this->okt['logAdmin']->critical([
			'code'       => 22,
			'message'    => $sThemeId
		]);

		return $this->render('Config/Themes/Uninstall', [
			'oInstallTheme' => $oInstallTheme
		]);
	}

	protected function reinstallTheme()
	{
		$sThemeId = $this->okt['request']->query->get('reinstall');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)
			|| ThemesCollection::DEFAULT_THEME == $sThemeId)
		{
			return false;
		}

		@ini_set('memory_limit', - 1);
		set_time_limit(0);

		# il faut d'abord désactiver le theme
		if ($this->okt['themes']->isLoaded($sThemeId))
		{
			$this->okt['themes']->getManager()->disableExtension($sThemeId);

			$this->okt['themes']->generateCacheList();

			return $this->redirect($this->generateUrl('config_themes') . '?reinstall=' . $sThemeId);
		}

		$oInstallTheme = $this->okt['themes']->getInstaller($sThemeId);

		# désinstallation
		$oInstallTheme->doUninstall();

		# installation
		$oInstallTheme->doInstall();

		# activation du theme
		$oInstallTheme->checklist->addItem(
			'enable_theme',
			$this->okt['themes']->getManager()->enableExtension($sThemeId),
			'Enable theme',
			'Cannot enable theme'
		);

		# vidange du cache global
		Utilities::deleteOktCacheFiles();

		if ($oInstallTheme->checklist->checkAll()) {
			$this->okt['flashMessages']->success(__('c_a_themes_correctly_reinstalled'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_themes_not_correctly_reinstalled.'));
		}

		# log admin
		$this->okt['logAdmin']->critical([
			'code'       => 23,
			'message'    => $sThemeId
		]);

		return $this->render('Config/Themes/Reinstall', [
			'oInstallTheme' => $oInstallTheme
		]);
	}

	protected function removeTheme()
	{
		$sThemeId = $this->okt['request']->query->get('delete');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aUninstalledThemes)
			|| ThemesCollection::DEFAULT_THEME == $sThemeId)
		{
			return false;
		}

		$fs = new Filesystem();

		if ($fs->remove($this->okt['themes_path'] . '/' . $sThemeId))
		{
			$this->okt['flashMessages']->success(__('c_a_themes_successfully_deleted'));

			$this->okt['logAdmin']->warning([
				'code'      => 42,
				'message'   => $sThemeId
			]);

			return $this->redirect($this->generateUrl('config_themes'));
		}
		else {
			$this->okt['instantMessages']->error(__('c_a_themes_not_deleted.'));
		}
	}

	protected function replaceAssetsFiles()
	{
		$sThemeId = $this->okt['request']->query->get('common');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)) {
			return false;
		}

		$oInstallTheme = $this->okt['themes']->getInstaller($sThemeId);
		$oInstallTheme->forceReplaceAssets();

		$this->okt['themes']->generateCacheList();

		$this->okt['flashMessages']->success(__('c_a_themes_common_files_replaced'));

		return $this->redirect($this->generateUrl('config_themes'));
	}

	protected function packageAndSendTheme()
	{
		$sThemeId = $this->okt['request']->query->get('download');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aAllThemes)) {
			return false;
		}

		$sThemePath = $this->okt['themes_path'] . '/' . $sThemeId;

		if (!is_readable($sThemePath)) {
			return false;
		}

		$sFilename = 'theme-' . $sThemeId . '-' . date('Y-m-d-H-i') . '.zip';

		ob_start();

		$fp = fopen('php://output', 'wb');

		$zip = new \fileZip($fp);
		$zip->addDirectory($sThemePath, '', true);

		$zip->write();

		$this->response->headers->set(
			'Content-Disposition',
			$this->response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $sFilename)
		);

		$this->response->setContent(ob_get_clean());

		return $this->response;
	}

	protected function compareFiles()
	{
		$sThemeId = $this->okt['request']->query->get('compare');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)) {
			return false;
		}

		$oInstallTheme = $this->okt['themes']->getInstaller($sThemeId);
		$oInstallTheme->compareFiles();

		return $this->render('Config/Themes/Compare', [
			'oInstallTheme' => $oInstallTheme
		]);
	}

	protected function themeUpload()
	{
		$upload_pkg = $this->okt['request']->request->get('upload_pkg');
		$pkg_file = $this->okt['request']->files->get('pkg_file');

		$fetch_pkg = $this->okt['request']->request->get('fetch_pkg');
		$pkg_url = $this->okt['request']->request->get('pkg_url');

		$repository = $this->okt['request']->query->get('repository');
		$theme = $this->okt['request']->query->get('theme');

		# Plugin upload
		if (($upload_pkg && $pkg_file) || ($fetch_pkg && $pkg_url) || ($repository && $theme && $this->okt['config']->repositories['themes']['enabled']))
		{
			try
			{
				if ($upload_pkg)
				{
					if (array_key_exists($pkg_file->getClientOriginalName(), $this->aUninstalledThemes)) {
						throw new \Exception(__('c_a_themes_theme_already_exists_not_installed_install_before_update'));
					}

					$pkg_file->move($this->okt['themes_path']);
				}
				else
				{
					if ($repository && $theme)
					{
						$repository = urldecode($repository);
						$theme = urldecode($theme);
						$url = urldecode($this->aThemesRepositories[$repository][$theme]['href']);
					}
					else {
						$url = urldecode($pkg_url);
					}

					$dest = $this->okt['themes_path'] . '/' . basename($url);

					if (array_key_exists(basename($url), $this->aUninstalledThemes)) {
						throw new \Exception(__('c_a_themes_theme_already_exists_not_installed_install_before_update'));
					}

					try
					{
						(new Client())->get($url, [
							'exceptions' => false,
							'save_to'    => $dest
						]);
					}
					catch (\Exception $e) {
						throw new \Exception(__('An error occurred while downloading the file.'));
					}
				}

				$ret_code = $this->okt['themes']->installPackage($dest);

				if ($ret_code == 2) {
					$this->okt['flashMessages']->success(__('c_a_themes_theme_successfully_upgraded'));
				}
				else {
					$this->okt['flashMessages']->success(__('c_a_themes_theme_successfully_added'));
				}

				return $this->redirect($this->generateUrl('config_themes'));
			}
			catch (\Exception $e)
			{
				$this->okt['instantMessages']->error($e->getMessage());
				return false;
			}
		}

		return false;
	}

	protected function useTheme()
	{
		$sUseThemeId = $this->okt['request']->query->get('use');

		if (!$sUseThemeId) {
			return false;
		}

		$aThemesConfig = $this->okt['config']->themes;

		$aThemesConfig['desktop'] = $sUseThemeId;

		# write config
		$this->okt['config']->write([
			'themes' => $aThemesConfig
		]);

		# modules config sheme
		$sTplScheme = $this->okt['themes_path'] . '/' . $sUseThemeId . '/modules_config_scheme.php';

		if (file_exists($sTplScheme)) {
			include $sTplScheme;
		}

		$this->okt['flashMessages']->success(__('c_c_confirm_configuration_updated'));

		return $this->redirect($this->generateUrl('config_themes'));
	}

	protected function useMobileTheme()
	{
		$sUseMobileThemeId = $this->okt['request']->query->get('use_mobile');

		if (!$sUseMobileThemeId) {
			return false;
		}

		$aThemesConfig = $this->okt['config']->themes;

		if ($sUseMobileThemeId == $this->okt['config']->themes['mobile']) {
			$sUseMobileThemeId = '';
		}

		$aThemesConfig['mobile'] = $sUseMobileThemeId;
		$this->okt['config']->write([
			'themes' => $aThemesConfig
		]);

		$this->okt['flashMessages']->success(__('c_c_confirm_configuration_updated'));

		return $this->redirect($this->generateUrl('config_themes'));
	}

	protected function useTabletTheme()
	{
		$sUseTabletThemeId = $this->okt['request']->query->get('use_tablet');

		if (!$sUseTabletThemeId) {
			return false;
		}

		$aThemesConfig = $this->okt['config']->themes;

		if ($sUseTabletThemeId == $this->okt['config']->themes['tablet']) {
			$sUseTabletThemeId = '';
		}

		$aThemesConfig['tablet'] = $sUseTabletThemeId;
		$this->okt['config']->write([
			'themes' => $aThemesConfig
		]);

		$this->okt['flashMessages']->success(__('c_c_confirm_configuration_updated'));

		return $this->redirect($this->generateUrl('config_themes'));
	}
}
