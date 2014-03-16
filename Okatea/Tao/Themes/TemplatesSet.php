<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Themes;

use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Misc\Utilities;

/**
 * Classe de gestion de jeux de templates
 *
 */
class TemplatesSet
{
	/**
	 * Okatea application instance.
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * Le chemin de base du template
	 * @var string
	 */
	protected $sBase;

	/**
	 * La famille du template
	 * @var string
	 */
	protected $sTplFamily;

	/**
	 * L'URL de base de la page de configuration.
	 * @var string
	 */
	protected $sBaseUrl;

	/**
	 * Le prefixe des noms des champs du formulaire.
	 * @var string
	 */
	protected $sFormPrefix='p_';

	/**
	 * La configuration du template
	 * @var array
	 */
	protected $aConfig;


	protected $aTemplatesPath;
	protected $aTemplatesInfos;
	protected $aCurrent;


	/**
	 * Constructor.
	 *
	 * @param Okatea\Tao\Application $okt
	 * @param array $aConfig
	 * @param string $sBase
	 * @param string $sTplFamily
	 * @param string $sBaseUrl
	 * @return void
	 */
	public function __construct($okt, $aConfig, $sBase, $sTplFamily, $sBaseUrl='/')
	{
		$this->okt = $okt;

		$this->aConfig = $aConfig;

		$this->sTplFamily = $sTplFamily;

		$this->sBase = Utilities::formatAppPath($sBase, false, false);

		$this->sBaseUrl = $sBaseUrl;

		$this->loadTemplatesPaths();

		$this->loadTemplatesInfos();

		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$this->okt->user->language.'/admin/templates.config');

		# get template id from query
		$sTtplId = null;
		if ($this->okt->request->query->has('tpl_id'))
		{
			$sTtplId = rawurldecode($this->okt->request->query->get('tpl_id'));

			if (!array_key_exists($sTtplId, $this->getTplInfos())) {
				$sTtplId = null;
			}
		}

		# get template family from query
		$sTtplFamily = null;
		if ($this->okt->request->query->has('tpl_family'))
		{
			$sTtplFamily = rawurldecode($this->okt->request->query->get('tpl_family'));

			if ($sTtplFamily != $this->sTplFamily) {
				$sTtplFamily = null;
			}
		}

		# téléchargement d'un template
		if ($this->okt->request->query->has('tpl_download') && $sTtplId && $sTtplFamily) {
			$this->dowloadTemplate($sTtplId);
		}

		# suppression d'un template
		if ($this->okt->request->query->has('tpl_delete') && $sTtplId && $sTtplFamily)
		{
			$this->deleteTemplate($sTtplId);

			$this->okt->page->flash->success(__('c_a_tpl_config_tpl_deleted'));

			\http::redirect($this->sBaseUrl.'tpl_family='.rawurlencode($this->sTplFamily).'&tpl_deleted=1');
		}
	}

	/**
	 * Définit l'URL de base de la page de configuration.
	 *
	 * @param string $sBaseUrl
	 * @return void
	 */
	public function setBaseUrl($sBaseUrl)
	{
		$this->sBaseUrl = $sBaseUrl;
	}

	/**
	 * Définit le prefixe des noms des champs du formulaire.
	 *
	 * @param string $sFormPrefix
	 * @return void
	 */
	public function setFormPrefix($sFormPrefix='p_')
	{
		$this->sFormPrefix = $sFormPrefix;
	}

	/**
	 * Retourne la liste des chemins des templates correspondants à la base.
	 *
	 * @return array
	 */
	public function getTplPaths()
	{
		return $this->aTemplatesPath;
	}

	/**
	 * Retourne les informations des templates correspondants à la base.
	 *
	 * @return array
	 */
	public function getTplInfos()
	{
		return $this->aTemplatesInfos;
	}

	/**
	 * Retourne les templates pour l'utilisation dans un select.
	 *
	 * @return array
	 */
	public function getTemplatesForSelect()
	{
		$aTemplatesList = array();
		foreach ($this->aTemplatesInfos as $aTemplateInfos) {
			$aTemplatesList[$aTemplateInfos['name']] = $aTemplateInfos['id'];
		}

		return $aTemplatesList;
	}

	/**
	 * Retourne les templates utilisables pour l'utilisation dans un select.
	 *
	 * @return array
	 */
	public function getUsablesTemplatesForSelect($aUsablesTemplates)
	{
		$aTemplatesList = array();

		foreach ($aUsablesTemplates as $sTemplateId)
		{
			if (isset($this->aTemplatesInfos[$sTemplateId])) {
				$aTemplatesList[$this->aTemplatesInfos[$sTemplateId]['name']] = $this->aTemplatesInfos[$sTemplateId]['id'];
			}
		}

		return $aTemplatesList;
	}

	/**
	 * Charge la liste des chemins des templates correspondants à la base.
	 *
	 * @return void
	 */
	protected function loadTemplatesPaths()
	{
		$this->aTemplatesPath = array();

		# first, get default theme templates
		if ($this->okt->config->themes['desktop'] != 'default') {
			$this->aTemplatesPath = (array)glob($this->okt->options->get('themes_dir').'/default/Templates/'.$this->sBase.'/*/template.php');
		}

		# then, get current theme templates
		$aThemeTemplates = (array)glob($this->okt->options->get('themes_dir').'/'.$this->okt->config->themes['desktop'].'/Templates/'.$this->sBase.'/*/template.php');

		foreach ($aThemeTemplates as $sTemplatePath) {
			$this->aTemplatesPath[] = $sTemplatePath;
		}

		# if we have a mobile theme, search in it
		if (!empty($this->okt->config->themes['mobile']))
		{
			$aThemeTemplates = (array)glob($this->okt->options->get('themes_dir').'/'.$this->okt->config->themes['mobile'].'/Templates/'.$this->sBase.'/*/template.php');

			foreach ($aThemeTemplates as $sTemplatePath) {
				$this->aTemplatesPath[] = $sTemplatePath;
			}
		}

		# finaly, search for templates in tablet theme
		if (!empty($this->okt->config->themes['tablet']))
		{
			$aThemeTemplates = (array)glob($this->okt->options->get('themes_dir').'/'.$this->okt->config->themes['tablet'].'/templates/'.$this->sBase.'/*/template.php');

			foreach ($aThemeTemplates as $sTemplatePath) {
				$this->aTemplatesPath[] = $sTemplatePath;
			}
		}
	}

	/**
	 * Charge les informations détaillées des templates correspondants à la base.
	 *
	 * @return void
	 */
	protected function loadTemplatesInfos()
	{
		$this->aTemplatesInfos = array();

		foreach ($this->aTemplatesPath as $sTplPath)
		{
			$sId = basename(dirname($sTplPath));

			$sDir = dirname($sTplPath);

			$sThemeId = self::getThemeIdFromTplPath($sTplPath);
			$sThemePath = $this->okt->options->get('themes_dir').'/'.$sThemeId;

			$sTplPathInTheme = str_replace($sThemePath, '', $sTplPath);

			$this->getTemplateInfos($sDir);

			$this->aTemplatesInfos[$sId] = array(
				'id' => $sId,
				'name' => (!empty($this->aCurrent['name']) ? $this->aCurrent['name'] : self::tplIdToName($sId)),
				'desc' => (!empty($this->aCurrent['desc']) ? $this->aCurrent['desc'] : null),
				'version' => (!empty($this->aCurrent['version']) ? $this->aCurrent['version'] : null),
				'author' => (!empty($this->aCurrent['author']) ? $this->aCurrent['author'] : null),
				'tags' => (!empty($this->aCurrent['tags']) ? $this->aCurrent['tags'] : null),
				'dir' => $sDir,
				'path' => $sTplPath,
				'path_in_theme' => $sTplPathInTheme,
				'theme' => $sThemeId,
				'theme_path' => $sThemePath,
				'is_in_default' => ($sThemeId == 'default')
			);
		}
	}

	/**
	 * Lit les infos d'un template et les retournes.
	 *
	 * @param string $sDir Chemin du répertoir du template
	 * @return array
	 */
	public function getTemplateInfos($sDir)
	{
		$this->aCurrent = array();

		if (file_exists($sDir.'/_define.php')) {
			include $sDir.'/_define.php';
		}

		return $this->aCurrent;
	}

	/**
	 * Retourne le thème d'un fichier de template donné.
	 *
	 * @param string $sTplPath
	 * @return string
	 */
	public static function getThemeIdFromTplPath($sTplPath)
	{
		global $okt;

		return Utilities::getNextSubDir($sTplPath, $okt->options->get('themes_dir'));
	}

	/**
	 * Formate les noms de templates pour l'affichage.
	 *
	 * @param array $aList
	 * @return array
	 */
	public static function tplIdToName($sName)
	{
		$sName = str_replace(array('_','-'), array(' ',' '), $sName);

		return ucfirst($sName);
	}

	/**
	 * Retourne un tableau avec les données de configuration en vue d'un enregistrement.
	 *
	 * @return array
	 */
	public function getPostConfig()
	{
		$p_tpl_default = $this->okt->request->request->get($this->sFormPrefix.'tpl_default_'.$this->sTplFamily);
		$p_tpl_usables = $this->okt->request->request->get($this->sFormPrefix.'tpl_usables_'.$this->sTplFamily, array());

		return array(
			'default' => $p_tpl_default,
			'usables' => $p_tpl_usables
		);
	}

	/**
	 * Retourne le tableau de configuration.
	 *
	 * @return string
	 */
	public function getHtmlConfigUsablesTemplates($bUsableField=true)
	{
		$sReturn =

		'<table class="common">'.
			'<thead><tr>'.
				'<th colspan="2" scope="col">'.__('c_a_tpl_config_name_infos').'</th>'.
				'<th scope="col">'.__('c_a_tpl_config_version').'</th>'.
				'<th scope="col">'.__('c_a_tpl_config_author').'</th>'.
				'<th scope="col">'.__('c_a_tpl_config_theme').'</th>'.
				'<th scope="col" class="nowrap">'.__('c_a_tpl_config_default_tpl').'</th>'.
				($bUsableField ? '<th scope="col" class="nowrap">'.__('c_a_tpl_config_usable_tpl').'</th>' : '').
				'<th scope="col">'.__('c_c_Actions').'</th>'.
			'</tr></thead>'.
			'<tbody>';

			foreach ($this->getTplInfos() as $aTplInfos)
			{
				$sReturn .=

				'<tr>'.
					'<td class="fake-th"><p><label for="'.$this->sFormPrefix.'tpl_default_'.$this->sTplFamily.'_'.$aTplInfos['id'].'">'.$aTplInfos['name'].'</label></p></td>'.
					'<td>';

						if (!empty($aTplInfos['desc'])) {
							$sReturn .= '<p>'.$aTplInfos['desc'].'</p>';
						}

						if (!empty($aTplInfos['tags'])) {
							$sReturn .= '<p><em>'.$aTplInfos['tags'].'</em></p>';
						}

					$sReturn .=

					'</td>'.
					'<td><p>'.$aTplInfos['version'].'</p></td>'.
					'<td><p>'.$aTplInfos['author'].'</p></td>'.
					'<td><p>'.$aTplInfos['theme'].'</p></td>'.
					'<td class="center small"><p>'.form::radio(array($this->sFormPrefix.'tpl_default_'.$this->sTplFamily, $this->sFormPrefix.'tpl_default_'.$this->sTplFamily.'_'.$aTplInfos['id']),
							$aTplInfos['id'], ($aTplInfos['id'] == $this->aConfig['default'])).'</p></td>';

					if ($bUsableField)
					{
						$sReturn .=
						'<td class="center small">'.form::checkbox(array($this->sFormPrefix.'tpl_usables_'.$this->sTplFamily.'[]', $this->sFormPrefix.'tpl_usables_'.$this->sTplFamily.'_'.$aTplInfos['id']),
							$aTplInfos['id'], in_array($aTplInfos['id'],$this->aConfig['usables'])).'</td>';
					}

					$sReturn .=

					'<td class="nowrap small">'.
						'<ul class="actions">';

							if ($aTplInfos['is_in_default'])
							{
								$sReturn .=
								'<li><a href="configuration.php?action=theme_editor&amp;theme='.$this->okt->config->themes['desktop'].'&amp;new_template=1&amp;basic_template='.
								rawurlencode('/'.$aTplInfos['theme'].$aTplInfos['path_in_theme']).'" '.
								'class="icon pencil">'.__('c_a_tpl_config_Customize').'</a></li>';
							}
							else
							{
								$sReturn .=
								'<li><a href="configuration.php?action=theme_editor&amp;theme='.$aTplInfos['theme'].'&amp;file='.
								rawurlencode($aTplInfos['path_in_theme']).'" '.
								'class="icon pencil">'.__('c_c_action_Edit').'</a></li>';
							}

							$sReturn .=
							'<li>'.
								'<a href="'.$this->sBaseUrl.'tpl_download=1'.
								'&amp;tpl_family='.rawurlencode($this->sTplFamily).
								'&amp;tpl_id='.rawurlencode($aTplInfos['id']).'" '.
								'class="icon package_go">'.__('c_c_action_Download').'</a>'.
							'</li>';

							$sReturn .=
							'<li>'.
								'<a href="'.$this->sBaseUrl.'tpl_delete=1'.
								'&amp;tpl_family='.rawurlencode($this->sTplFamily).
								'&amp;tpl_id='.rawurlencode($aTplInfos['id']).'" '.
								'onclick="return window.confirm(\''.Escaper::js(__('c_a_tpl_config_delete_confirm')).'\')" '.
								'class="icon package_delete">'.__('c_c_action_Delete').'</a>'.
							'</li>';

							$sReturn .=
						'</ul>'.
					'</td>'.
				'</tr>';
			}

			$sReturn .=
			'</tbody>'.
		'</table>';

		return $sReturn;
	}

	/**
	 * Make a package of a template
	 *
	 * @param string $sId
	 * @return boolean
	 */
	protected function dowloadTemplate($sId)
	{
		$aTemplatesInfos = $this->getTplInfos();
		$aTplInfos = $aTemplatesInfos[$sId];

		$sFilename = $aTplInfos['id'].'.zip';

		if (!is_dir($aTplInfos['dir']) || !is_readable($aTplInfos['dir'])) {
			return false;
		}

		try
		{
			set_time_limit(0);
			$fp = fopen('php://output','wb');
			$zip = new \fileZip($fp);
			$zip->addExclusion('#(^|/).svn$#');
			$zip->addDirectory($aTplInfos['dir'],'',true);

			header('Content-Disposition: attachment;filename='.$sFilename);
			header('Content-Type: application/x-zip');
			$zip->write();
			unset($zip);
			exit;
		}
		catch (Exception $e)
		{
			$this->okt->error->set($e->getMessage());
			return false;
		}
	}

	/**
	 * Delete a template
	 *
	 * @param string $sId
	 * @return boolean
	 */
	protected function deleteTemplate($sId)
	{
		$aTemplatesInfos = $this->getTplInfos();
		$aTplInfos = $aTemplatesInfos[$sId];

		if (!is_dir($aTplInfos['dir']) || !is_writable($aTplInfos['dir'])) {
			return false;
		}

		\files::deltree($aTplInfos['dir']);
	}
}
