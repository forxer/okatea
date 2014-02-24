<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Admin\Controller\Config;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Okatea\Admin\Controller;
use Okatea\Tao\HttpClient;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Extensions\Themes\Collection as ThemesCollection;

class Themes extends Controller
{
	protected $aAllThemes;

	protected $aInstalledThemes;

	protected $aUninstalledThemes;

	protected $aUpdatablesThemes;

	protected $aThemesRepositories;

	public function page()
	{
		if (!$this->okt->checkPerm('themes')) {
			return $this->serve401();
		}

		$this->init();

		# Affichage changelog
		if (($showChangelog = $this->showChangelog()) !== false) {
			return $showChangelog;
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

		return $this->render('Config/Themes', array(
			'aAllThemes'			=> $this->aAllThemes,
			'aInstalledThemes'		=> $this->aInstalledThemes,
			'aUninstalledThemes'	=> $this->aUninstalledThemes,
			'aUpdatablesThemes'		=> $this->aUpdatablesThemes,
			'aThemesRepositories'	=> $this->aThemesRepositories
		));
	}

	protected function init()
	{
		# Themes locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$this->okt->user->language.'/admin/themes');

		# Récupération de la liste des themes dans le système de fichiers (tous les themes)
		$this->aAllThemes = $this->okt->themes->getManager()->getAll();

		# Load all themes admin locales files
		foreach ($this->aAllThemes as $id=>$infos) {
			$this->okt->l10n->loadFile($infos['root'].'/locales/'.$this->okt->user->language.'/main');
		}

		# Récupération de la liste des themes dans la base de données (les themes installés)
		$this->aInstalledThemes = $this->okt->themes->getManager()->getInstalled();

		# Calcul de la liste des themes non-installés
		$this->aUninstalledThemes = array_diff_key($this->aAllThemes,$this->aInstalledThemes);

		foreach ($this->aUninstalledThemes as $sThemeId=>$aThemeInfos) {
			$this->aUninstalledThemes[$sThemeId]['name_l10n'] = __($aThemeInfos['name']);
		}

		# Liste des dépôts de themes
		$this->aThemesRepositories = array();
		if ($this->okt->config->repositories['themes']['enabled']) {
			$this->aThemesRepositories = $this->okt->themes->getRepositoriesData($this->okt->config->repositories['themes']['list']);
		}

		# Liste des éventuelles mise à jours disponibles sur les dépots
		$this->aUpdatablesThemes = array();
		foreach ($this->aThemesRepositories as $repo_name=>$themes)
		{
			foreach ($themes as $theme)
			{
				$this->aThemesRepositories[$repo_name][$theme['id']]['name_l10n'] = $theme['name'];

				if (isset($this->aAllThemes[$theme['id']]) && $this->aAllThemes[$theme['id']]['updatable'] && version_compare($this->aAllThemes[$theme['id']]['version'],$theme['version'], '<'))
				{
					$this->aUpdatablesThemes[$theme['id']] = array(
						'id' => $theme['id'],
						'name' => $theme['name'],
						'version' => $theme['version'],
						'info' => $theme['info'],
						'repository' => $repo_name
					);
				}
			}
		}

		# Tri par ordre alphabétique des listes de themes
		ThemesCollection::sort($this->aInstalledThemes);
		ThemesCollection::sort($this->aUninstalledThemes);

		foreach ($this->aThemesRepositories as $repo_name=>$themes) {
			ThemesCollection::sort($this->aThemesRepositories[$repo_name]);
		}
	}

	protected function showChangelog()
	{
		$sThemeId = $this->request->query->get('show_changelog');
		$sChangelogFile = $this->okt->options->get('themes_dir').'/'.$sThemeId.'/CHANGELOG';

		if (!$sThemeId || !file_exists($sChangelogFile)) {
			return false;
		}

		$sChangelogContent = '<pre class="changelog">'.file_get_contents($sChangelogFile).'</pre>';

		return (new Response())->setContent($sChangelogContent);
	}

	protected function enableTheme()
	{
		$sThemeId = $this->request->query->get('enable');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)) {
			return false;
		}

		$this->okt->themes->getManager()->enableExtension($sThemeId);

		# vidange du cache global
		Utilities::deleteOktCacheFiles();

		# log admin
		$this->okt->logAdmin->warning(array(
			'code' => 30,
			'message' => $sThemeId
		));

		return $this->redirect($this->generateUrl('config_themes'));
	}

	protected function disableTheme()
	{
		$sThemeId = $this->request->query->get('disable');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)) {
			return false;
		}

		$this->okt->themes->getManager()->disableExtension($sThemeId);

		# vidange du cache global
		Utilities::deleteOktCacheFiles();

		# log admin
		$this->okt->logAdmin->warning(array(
			'code' => 31,
			'message' => $sThemeId
		));

		return $this->redirect($this->generateUrl('config_themes'));
	}

	protected function installTheme()
	{
		$sThemeId = $this->request->query->get('install');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aUninstalledThemes)) {
			return false;
		}

		@ini_set('memory_limit',-1);
		set_time_limit(0);

		$oInstallTheme = $this->okt->themes->getInstaller($sThemeId);
		$oInstallTheme->doInstall();

		# activation du theme
		$oInstallTheme->checklist->addItem(
			'enable_theme',
			$this->okt->themes->getManager()->enableExtension($sThemeId),
			'Enable theme',
			'Cannot enable theme'
		);

		# vidange du cache global
		Utilities::deleteOktCacheFiles();

		if ($oInstallTheme->checklist->checkAll()) {
			$this->okt->page->success->set(__('c_a_themes_correctly_installed'));
		}
		else {
			$this->okt->error->set(__('c_a_themes_not_installed'));
		}

		# log admin
		$this->okt->logAdmin->warning(array(
			'code' => 20,
			'message' => $sThemeId
		));

		return $this->render('Config/Themes/Install', array(
			'oInstallTheme' => $oInstallTheme
		));
	}

	protected function updateTheme()
	{
		$sThemeId = $this->request->query->get('update');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)) {
			return false;
		}

		# D'abord on active le theme
		if (!$this->okt->themes->isLoaded($sThemeId))
		{
			$this->okt->themes->getManager()->enableExtension($sThemeId);

			$this->okt->themes->generateCacheList();

			return $this->redirect($this->generateUrl('config_themes').'?reinstall='.$sThemeId);
		}

		# Ensuite on met à jour
		$oInstallTheme = $this->okt->themes->getInstaller($sThemeId);
		$oInstallTheme->doUpdate();

		if ($oInstallTheme->checklist->checkAll()) {
			$this->okt->page->success->set(__('c_a_themes_correctly_updated'));
		}
		else {
			$this->okt->error->set(__('c_a_themes_not_updated'));
		}

		Utilities::deleteOktCacheFiles();

		$this->okt->logAdmin->critical(array(
			'code' => 21,
			'message' => $sThemeId
		));

		$sNextUrl = $this->generateUrl('config_themes');

		if (file_exists($oInstallTheme->root().'/install/tpl/') || file_exists($oInstallTheme->root().'/install/assets/')) {
			$sNextUrl .= '?compare='.$oInstallTheme->id();
		}

		return $this->render('Config/Themes/Update', array(
			'oInstallTheme' => $oInstallTheme,
			'sNextUrl' => $sNextUrl
		));
	}

	protected function uninstallTheme()
	{
		$sThemeId = $this->request->query->get('uninstall');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)) {
			return false;
		}

		@ini_set('memory_limit',-1);
		set_time_limit(0);

		$oInstallTheme = $this->okt->themes->getInstaller($sThemeId);
		$oInstallTheme->doUninstall();

		Utilities::deleteOktCacheFiles();

		if ($oInstallTheme->checklist->checkAll()) {
			$this->okt->page->success->set(__('c_a_themes_correctly_uninstalled'));
		}
		else {
			$this->okt->error->set(__('c_a_themes_not_uninstalled'));
		}

		$this->okt->logAdmin->critical(array(
			'code' => 22,
			'message' => $sThemeId
		));

		return $this->render('Config/Themes/Uninstall', array(
			'oInstallTheme' => $oInstallTheme
		));
	}

	protected function reinstallTheme()
	{
		$sThemeId = $this->request->query->get('reinstall');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)) {
			return false;
		}

		@ini_set('memory_limit',-1);
		set_time_limit(0);

		# il faut d'abord désactiver le theme
		if ($this->aInstalledThemes[$sThemeId]['status'])
		{
			$this->okt->themes->getManager()->disableExtension($sThemeId);

			# cache de la liste de theme
			$this->okt->themes->generateCacheList();

			return $this->redirect($this->generateUrl('config_themes').'?reinstall='.$sThemeId);
		}

		$oInstallTheme = $this->okt->themes->getInstaller($sThemeId);

		# désinstallation
		$oInstallTheme->doUninstall();

		# installation
		$oInstallTheme->doInstall();

		# activation du theme
		$oInstallTheme->checklist->addItem(
			'enable_theme',
			$this->okt->themes->getManager()->enableExtension($sThemeId),
			'Enable theme',
			'Cannot enable theme'
		);

		# vidange du cache global
		Utilities::deleteOktCacheFiles();

		if ($oInstallTheme->checklist->checkAll()) {
			$this->okt->page->success->set(__('c_a_themes_correctly_reinstalled'));
		}
		else {
			$this->okt->error->set(__('c_a_themes_not_correctly_reinstalled.'));
		}

		# log admin
		$this->okt->logAdmin->critical(array(
			'code' => 23,
			'message' => $sThemeId
		));

		return $this->render('Config/Themes/Reinstall', array(
			'oInstallTheme' => $oInstallTheme
		));
	}

	protected function removeTheme()
	{
		$sThemeId = $this->request->query->get('delete');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aUninstalledThemes)) {
			return false;
		}

		if (\files::deltree($this->okt->options->get('themes_dir').'/'.$sThemeId))
		{
			$this->okt->page->flash->success(__('c_a_themes_successfully_deleted'));

			$this->okt->logAdmin->warning(array(
				'code' => 42,
				'message' => $sThemeId
			));

			return $this->redirect($this->generateUrl('config_themes'));
		}
		else {
			$this->okt->error->set(__('c_a_themes_not_deleted.'));
		}
	}

	protected function replaceAssetsFiles()
	{
		$sThemeId = $this->request->query->get('common');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)) {
			return false;
		}

		$oInstallTheme = $this->okt->themes->getInstaller($sThemeId);
		$oInstallTheme->forceReplaceAssets();

		$this->okt->themes->generateCacheList();

		$this->okt->page->flash->success(__('c_a_themes_common_files_replaced'));

		return $this->redirect($this->generateUrl('config_themes'));
	}

	protected function packageAndSendTheme()
	{
		$sThemeId = $this->request->query->get('download');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aAllThemes)) {
			return false;
		}

		$sThemePath = $this->okt->options->get('themes_dir').'/'.$sThemeId;

		if (!is_readable($sThemePath) ) {
			return false;
		}

		$sFilename = 'theme-'.$sThemeId.'-'.date('Y-m-d-H-i').'.zip';

		ob_start();

		$fp = fopen('php://output', 'wb');

		$zip = new \fileZip($fp);
		$zip->addDirectory($sThemePath, '', true);

		$zip->write();

		$this->response->headers->set('Content-Disposition',
				$this->response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $sFilename));

		$this->response->setContent(ob_get_clean());

		return $this->response;
	}

	protected function compareFiles()
	{
		$sThemeId = $this->request->query->get('compare');

		if (!$sThemeId || !array_key_exists($sThemeId, $this->aInstalledThemes)) {
			return false;
		}

		$oInstallTheme = $this->okt->themes->getInstaller($sThemeId);
		$oInstallTheme->compareFiles();

		return $this->render('Config/Themes/Compare', array(
			'oInstallTheme' => $oInstallTheme
		));
	}

	protected function themeUpload()
	{
		$upload_pkg = $this->request->request->get('upload_pkg');
		$pkg_file = $this->request->files->get('pkg_file');

		$fetch_pkg = $this->request->request->get('fetch_pkg');
		$pkg_url = $this->request->request->get('pkg_url');

		$repository = $this->request->query->get('repository');
		$theme = $this->request->query->get('theme');

		# Plugin upload
		if (($upload_pkg && $pkg_file) || ($fetch_pkg && $pkg_url) ||
			($repository && $theme && $this->okt->config->repositories['themes']['enabled']))
		{
			try
			{
				if ($upload_pkg)
				{
					if (array_key_exists($pkg_file->getClientOriginalName(), $this->aUninstalledThemes)) {
						throw new \Exception(__('c_a_themes_theme_already_exists_not_installed_install_before_update'));
					}

					$pkg_file->move($this->okt->options->get('themes_dir'));
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

					$dest = $this->okt->options->get('themes_dir').'/'.basename($url);

					if (array_key_exists(basename($url), $aUninstalledThemes)) {
						throw new \Exception(__('c_a_themes_theme_already_exists_not_installed_install_before_update'));
					}

					try
					{
						$client = new HttpClient();

						$request = $client->get($url, array(), array(
							'save_to' => $dest
						));
					}
					catch( Exception $e) {
						throw new \Exception(__('An error occurred while downloading the file.'));
					}

					unset($client);
				}

				$ret_code = $this->okt->themes->installPackage($dest, $this->okt->themes);

				if ($ret_code == 2) {
					$this->okt->page->flash->success(__('c_a_themes_theme_successfully_upgraded'));
				}
				else {
					$this->okt->page->flash->success(__('c_a_themes_theme_successfully_added'));
				}

				return $this->redirect($this->generateUrl('config_themes'));
			}
			catch (Exception $e)
			{
				$this->okt->error->set($e->getMessage());
				return false;
			}
		}

		return false;
	}
}
