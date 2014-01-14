<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin\Controller\Config;

use Tao\Admin\Controller;
use Tao\Admin\Filters\Themes as ThemesFilters;
use Tao\Admin\Pager;
use Tao\Themes\Collection as ThemesCollection;
use Tao\Themes\Editor\DefinitionsLess;

class Themes extends Controller
{
	protected $oThemes;

	protected $aInstalledThemes;

	protected $oFilters;

	public function index()
	{
		$this->init();

		# Initialisation des filtres
		$this->oFilters = new ThemesFilters($this->okt, array());

		# json themes list for autocomplete
		if (!empty($_REQUEST['json']))
		{
			$aResults = array();
			foreach ($this->aInstalledThemes as $aTheme)
			{
				foreach ($aTheme['index'] as $s)
				{
					if (strpos($s, $_GET['term']) !== false) {
						$aResults[] = $s;
					}
				}
			}

			header('Content-type: application/json');
			echo json_encode(array_unique($aResults));

			exit;
		}

		# affichage des notes d'un thème
		$sNotesThemeId = $this->request->query->get('notes');
		if ($sNotesThemeId && file_exists($this->okt->options->get('themes_dir').'/'.$sNotesThemeId.'/notes.md'))
		{
			echo \Parsedown::instance()->parse(file_get_contents($this->okt->options->get('themes_dir').'/'.$sNotesThemeId.'/notes.md'));

			exit;
		}

		# Ré-initialisation filtres
		if ($this->request->query->has('init_filters'))
		{
			$this->oFilters->initFilters();
			return $this->redirect($this->generateUrl('config_themes'));
		}

		# Suppression d'un thème
		$sDeleteThemeId = $this->request->query->get('delete');
		if ($sDeleteThemeId && isset($this->aInstalledThemes[$sDeleteThemeId]) && !$this->aInstalledThemes[$sDeleteThemeId]['is_active'])
		{
			if (\files::deltree($this->okt->options->get('themes_dir').'/'.$sDeleteThemeId))
			{
				$this->okt->page->flash->success(__('c_a_themes_successfully_deleted'));

				return $this->redirect($this->generateUrl('config_themes'));
			}
		}

		# Utilisation d'un thème
		$sUseThemeId = $this->request->query->get('use');
		if ($sUseThemeId)
		{
			try
			{
				# write config
				$this->okt->config->write(array(
					'theme' => $sUseThemeId
				));

				# modules config sheme
				$sTplScheme = $this->okt->options->get('themes_dir').'/'.$sUseThemeId.'/modules_config_scheme.php';

				if (file_exists($sTplScheme)) {
					include $sTplScheme;
				}

				$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('config_themes'));
			}
			catch (InvalidArgumentException $e)
			{
				$this->okt->error->set(__('c_c_error_writing_configuration'));
				$this->okt->error->set($e->getMessage());
			}
		}

		# Utilisation d'un thème mobile
		$sUseMobileThemeId = $this->request->query->get('use_mobile');
		if ($sUseMobileThemeId)
		{
			try
			{
				# switch ?
				if ($sUseMobileThemeId == $this->okt->config->theme_mobile) {
					$sUseMobileThemeId = '';
				}

				$this->okt->config->write(array(
					'theme_mobile' => $sUseMobileThemeId
				));

				$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('config_themes'));
			}
			catch (InvalidArgumentException $e)
			{
				$this->okt->error->set(__('c_c_error_writing_configuration'));
				$this->okt->error->set($e->getMessage());
			}
		}

		# Utilisation d'un thème tablette
		$sUseTabletThemeId = $this->request->query->get('use_tablet');
		if ($sUseTabletThemeId)
		{
			try
			{
				# switch ?
				if ($sUseTabletThemeId == $this->okt->config->theme_tablet) {
					$sUseTabletThemeId = '';
				}

				$this->okt->config->write(array(
					'theme_tablet' => $sUseTabletThemeId
				));

				$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('config_themes'));
			}
			catch (InvalidArgumentException $e)
			{
				$this->okt->error->set(__('c_c_error_writing_configuration'));
				$this->okt->error->set($e->getMessage());
			}
		}

		# Initialisation des filtres
		$sSearch = $this->request->query->get('search');

		if ($sSearch)
		{
			$sSearch = strtolower(trim($sSearch));

			foreach ($this->aInstalledThemes as $iThemeId=>$aTheme)
			{
				if (!in_array($sSearch, $aTheme['index'])) {
					unset($this->aInstalledThemes[$iThemeId]);
				}
			}
		}

		# Création des filtres
		$this->oFilters->getFilters();

		# Initialisation de la pagination
		$oPager = new Pager($this->okt, $this->oFilters->params->page, count($this->aInstalledThemes), $this->oFilters->params->nb_per_page);

		$iNumPages = $oPager->getNbPages();

		$this->oFilters->normalizePage($iNumPages);

		$this->aInstalledThemes = array_slice($this->aInstalledThemes, (($this->oFilters->params->page-1)*$this->oFilters->params->nb_per_page), $this->oFilters->params->nb_per_page);

		return $this->render('Config/Themes/Index', array(
			'oFilters' => $this->oFilters,
			'aInstalledThemes' => $this->aInstalledThemes,
			'iNumPages' => $iNumPages,
			'sSearch' => $sSearch,
			'oPager' => $oPager
		));
	}

	public function theme()
	{
		$this->init();

		# Theme infos
		$sThemeId = $this->request->attributes->get('theme_id');

		if (!isset($this->aInstalledThemes[$sThemeId])) {
			return $this->redirect($this->generateUrl('config_themes'));
		}

		$aThemeInfos = $this->aInstalledThemes[$sThemeId];

		# Notes de développement
		$sDevNotesFilename = $this->okt->options->get('themes_dir').'/'.$sThemeId.'/notes.md';
		$bHasDevNotes = $bEditDevNotes = false;
		$sDevNotesMd = $sDevNotesHtml = null;
		if (file_exists($sDevNotesFilename))
		{
			$bHasDevNotes = true;

			$bEditDevNotes = $this->request->query->has('edit_notes');

			$sDevNotesMd = file_get_contents($sDevNotesFilename);

			$sDevNotesHtml = \Parsedown::instance()->parse($sDevNotesMd);
		}


		# Definitions LESS
		$sDefinitionsLessFilename = $this->okt->options->get('themes_dir').'/'.$sThemeId.'/css/definitions.less';
		$oDefinitionsLessEditor = null;
		$bHasDefinitionsLess = false;
		if (file_exists($sDefinitionsLessFilename))
		{
			$bHasDefinitionsLess = true;

			$oDefinitionsLessEditor = new DefinitionsLess($this->okt);
			$aCurrentDefinitionsLess = $oDefinitionsLessEditor->getValuesFromFile($sDefinitionsLessFilename);
		}

		# enregistrement notes
		if (!empty($_POST['save_notes']))
		{
			if ($bHasDevNotes) {
				file_put_contents($sDevNotesFilename, $_POST['notes_content']);
			}

			return $this->redirect($this->generateUrl('config_theme', array('theme_id' => $sThemeId)));
		}

		# enregistrement definitions less
		if (!empty($_POST['save_def_less']))
		{
			if ($bHasDefinitionsLess) {
				$oDefinitionsLessEditor->writeFileFromPost($sDefinitionsLessFilename);
			}

			return $this->redirect($this->generateUrl('config_theme', array('theme_id' => $sThemeId)));
		}

		return $this->render('Config/Themes/Theme', array(
			'sThemeId' => $sThemeId,
			'aThemeInfos' => $aThemeInfos,
			'bHasDevNotes' => $bHasDevNotes,
			'bEditDevNotes' => $bEditDevNotes,
			'sDevNotesMd' => $sDevNotesMd,
			'sDevNotesHtml' => $sDevNotesHtml,
			'bHasDefinitionsLess' => $bHasDefinitionsLess,
			'oDefinitionsLessEditor' => $oDefinitionsLessEditor
		));
	}

	public function add()
	{
		$this->init();

		# Liste de thèmes des dépôts de thèmes
		$aThemesRepositories = array();
		if ($this->okt->config->themes_repositories_enabled)
		{
			$aRepositories = $this->okt->config->themes_repositories;
			$aThemesRepositories = $this->oThemes->getRepositoriesInfos($aRepositories);
		}

		# Tri par ordre alphabétique des listes de thèmes des dépots
		foreach ($aThemesRepositories as $repo_name=>$themes) {
			ThemesCollection::sortThemes($aThemesRepositories[$repo_name]);
		}

		# Theme upload
		$upload_pkg = $this->request->request->get('upload_pkg');
		$pkg_file = $this->request->files->get('pkg_file');

		$fetch_pkg = $this->request->request->get('fetch_pkg');
		$pkg_url = $this->request->request->get('pkg_url');

		$repository = $this->request->query->get('repository');
		$theme = $this->request->query->get('theme');

		if (($upload_pkg && $pkg_file) || ($fetch_pkg && $pkg_url) ||
			($repository && $theme && $this->okt->config->themes_repositories_enabled))
		{
			try
			{
				if (!empty($_POST['upload_pkg']))
				{
					Utilities::uploadStatus($_FILES['pkg_file']);

					$dest = $this->okt->options->get('themes_dir').'/'.$_FILES['pkg_file']['name'];
					if (!move_uploaded_file($_FILES['pkg_file']['tmp_name'],$dest)) {
						throw new Exception(__('Unable to move uploaded file.'));
					}
				}
				else
				{
					if (!empty($_GET['repository']) && !empty($_GET['theme']))
					{
						$repository = urldecode($_GET['repository']);
						$theme = urldecode($theme);
						$url = urldecode($aThemesRepositories[$repository][$theme]['href']);
					}
					else {
						$url = urldecode($_POST['pkg_url']);
					}

					$dest = $this->okt->options->get('themes_dir').'/'.basename($url);

					try
					{
						$client = new HttpClient();

						$request = $client->get($url, array(), array(
							'save_to' => $dest
						));

						$request->send();
					}
					catch( Exception $e) {
						throw new Exception(__('An error occurred while downloading the file.'));
					}

					unset($client);
				}

				$iReturnedCode = $this->oThemes->installPackage($dest, $this->oThemes);

				if ($iReturnedCode == 2) {
					$this->okt->page->flash->success(__('c_a_themes_successfully_upgraded'));
				}
				else {
					$this->okt->page->flash->success(__('c_a_themes_successfully_added'));
				}

				return $this->redirect($this->generateUrl('config_themes'));
			}
			catch (Exception $e) {
				$this->okt->error->set($e->getMessage());
			}
		}

		# Bootstrap a theme
		else if (!empty($_POST['bootstrap']))
		{
			try {
				$this->oThemes->bootstrapTheme($_POST['bootstrap_theme_name'], (!empty($_POST['bootstrap_theme_id']) ? $_POST['bootstrap_theme_id'] : null));

				$this->okt->page->flash->success(__('c_a_themes_bootstrap_success'));

				return $this->redirect($this->generateUrl('config_themes'));
			}
			catch (Exception $e) {
				$this->okt->error->set($e->getMessage());
			}
		}


		return $this->render('Config/Themes/Add', array(
//			'' => $,
		));
	}

	protected function init()
	{
		if (!$this->okt->checkPerm('themes')) {
			return $this->serve401();
		}

		# Locales
		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$this->okt->user->language.'/admin.themes');

		# Themes object
		$this->oThemes = new ThemesCollection($this->okt, $this->okt->options->get('themes_dir'));

		# Liste des thèmes présents
		$this->aInstalledThemes = $this->oThemes->getThemesAdminList();

		# Tri par ordre alphabétique des listes de thème
		ThemesCollection::sortThemes($this->aInstalledThemes);
	}
}
