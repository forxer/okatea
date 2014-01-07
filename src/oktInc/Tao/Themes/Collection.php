<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Themes;

use Tao\Core\HttpClient;
use Tao\Misc\Utilities;

/**
 * Classe de gestion des thèmes.
 *
 */
class Collection
{
	/**
	 * Le chemin du répertoire des thèmes
	 * @var string
	 */
	protected $sPath;

	/**
	 * La liste des thèmes
	 * @var array
	 */
	protected $aThemes = null;

	/**
	 * L'objet gestionnaire de cache
	 * @var object
	 */
	protected $cache;

	/**
	 * L'identifiant du cache des thèmes
	 * @var string
	 */
	protected $cache_id;

	/**
	 * L'identifiant du cache des dépots
	 * @var string
	 */
	protected $cache_repo_id;

	/**
	 * L'identifiant du theme en cours d'inscription
	 * @var string
	 */
	private $_id = null;

	/**
	 * Constructor.
	 *
	 * @param oktCore $okt
	 * @param string $sPath
	 */
	public function __construct($okt, $sPath)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		$this->cache = $okt->cache;
		$this->cache_id = 'themes';
		$this->cache_repo_id = 'themes_repositories';

		$this->sPath = $sPath;
	}

	/**
	 * Retourne la liste des thèmes disponibles.
	 *
	 * @param boolean $bForce
	 * @return array
	 */
	public static function getThemes($bForce=false)
	{
		static $aThemes=null;

		if (is_array($aThemes) && !$bForce) {
			return $aThemes;
		}
		else
		{
			global $okt;

			$oThemes = new self($okt, $okt->options->get('themes_dir'));

			$aList = $oThemes->getThemesList();

			$aThemes = array();
			foreach ($aList as $id=>$infos) {
				$aThemes[$id] = $infos['name'];
			}

			return $aThemes;
		}
	}

	/**
	 * Ré-initialise la liste des thèmes.
	 *
	 * @return void
	 */
	public function resetThemesList()
	{
		$this->aThemes = null;
	}

	/**
	 * Calcul la liste des thèmes disponibles.
	 *
	 * @param boolean $bForce
	 * @return array
	 */
	public function getThemesList($bForce=false)
	{
		if (is_array($this->aThemes) && !$bForce) {
			return $this->aThemes;
		}
		else
		{
			foreach (new \DirectoryIterator($this->sPath) as $oFileInfo)
			{
				if ($oFileInfo->isDot() || !$oFileInfo->isDir() || !file_exists($oFileInfo->getPathname().'/_define.php')) {
					continue;
				}

				$this->_id = $oFileInfo->getFilename();

				require $oFileInfo->getPathname().'/_define.php';

				$this->_id = null;
			}

			return $this->aThemes;
		}
	}

	/**
	 * Retourne la liste des thèmes disponibles avec les infos de l'administration.
	 *
	 * @return array
	 */
	public function getThemesAdminList()
	{
		$aThemes = $this->getThemesList();

		foreach ($aThemes as $iThemeId=>$aTheme)
		{
			# search indexes
			$aThemes[$iThemeId]['index'] = \text::splitWords($aThemes[$iThemeId]['name'].' '.$aThemes[$iThemeId]['desc'].' '.$aThemes[$iThemeId]['tags']);
			$aThemes[$iThemeId]['index'] = array_map('strtolower', $aThemes[$iThemeId]['index']);
			$aThemes[$iThemeId]['index'] = array_unique($aThemes[$iThemeId]['index']);
			array_unshift($aThemes[$iThemeId]['index'], $iThemeId);

			# is active ?
			if ($aTheme['id'] == $this->okt->config->theme)
			{
				$aThemes[$iThemeId]['is_active'] = true;
				array_unshift($aThemes[$iThemeId]['index'], 'active');
				array_unshift($aThemes[$iThemeId]['index'], 'actif');
			}
			else {
				$aThemes[$iThemeId]['is_active'] = false;
			}

			# is mobile ?
			if ($aTheme['id'] == $this->okt->config->theme_mobile)
			{
				$aThemes[$iThemeId]['is_mobile'] = true;
				array_unshift($aThemes[$iThemeId]['index'], 'mobile');
				array_unshift($aThemes[$iThemeId]['index'], 'mobil');
				array_unshift($aThemes[$iThemeId]['index'], 'active');
				array_unshift($aThemes[$iThemeId]['index'], 'actif');
			}
			else {
				$aThemes[$iThemeId]['is_mobile'] = false;
			}

			# is tablet ?
			if ($aTheme['id'] == $this->okt->config->theme_tablet)
			{
				$aThemes[$iThemeId]['is_tablet'] = true;
				array_unshift($aThemes[$iThemeId]['index'], 'tablet');
				array_unshift($aThemes[$iThemeId]['index'], 'tablette');
				array_unshift($aThemes[$iThemeId]['index'], 'active');
				array_unshift($aThemes[$iThemeId]['index'], 'actif');
			}
			else {
				$aThemes[$iThemeId]['is_tablet'] = false;
			}

			# has screenshot ?
			if (file_exists($this->sPath.'/'.$aTheme['id'].'/screenshot.jpg'))  {
				$aThemes[$iThemeId]['screenshot'] = true;
			}
			else {
				$aThemes[$iThemeId]['screenshot'] = false;
			}

			# has config ?
			if (file_exists($this->sPath.'/'.$aTheme['id'].'/admin/config.php'))  {
				$aThemes[$iThemeId]['has_config'] = true;
			}
			else {
				$aThemes[$iThemeId]['has_config'] = false;
			}
		}

		return $aThemes;
	}


	/**
	 * Enregistrement d'un thème.
	 *
	 * @param array $aParams
	 * @return void
	 */
	public function registerTheme($aParams=array())
	{
		if ($this->_id)
		{
			$this->aThemes[$this->_id] = array(
				'id' 			=> $this->_id,
				'name' 			=> (!empty($aParams['name']) ? $aParams['name'] : $this->_id),
				'desc' 			=> (!empty($aParams['desc']) ? $aParams['desc'] : null),
				'version' 		=> (!empty($aParams['version']) ? $aParams['version'] : null),
				'author' 		=> (!empty($aParams['author']) ? $aParams['author'] : null),
				'tags' 			=> (!empty($aParams['tags']) ? $aParams['tags'] : null),
			);
		}
	}

	public function requireDefine($dir,$id)
	{
		if (file_exists($dir.'/_define.php'))
		{
			$this->_id = $id;
			require $dir.'/_define.php';
			$this->_id = null;
		}
	}

	/**
	 * Permet de créer un thème vierge.
	 *
	 * @param string $sId
	 */
	public function bootstrapTheme($sName, $sId=null)
	{
		if (empty($sId)) {
			$sId = Utilities::strToLowerURL($sName,false);
		}

		$this->getThemesList();

		if (isset($this->aThemes[$sId])) {
			return $sId;
		}

		$sThemePath = $this->sPath.'/'.$sId;

		$aSearch = array(
			'{{theme_id}}',
			'{{theme_name}}'
		);
		$aReplace = array(
			$sId,
			\html::escapeHTML($sName)
		);

		try
		{
			# required files
			\files::makeDir($sThemePath);
				file_put_contents($sThemePath.'/_define.php', str_replace($aSearch, $aReplace, file_get_contents($this->okt->options->get('inc_dir').'/admin/configuration/themes/templates/_define.tpl')));
				file_put_contents($sThemePath.'/index.php', str_replace($aSearch, $aReplace, file_get_contents($this->okt->options->get('inc_dir').'/admin/configuration/themes/templates/index.tpl')));
				file_put_contents($sThemePath.'/oktTheme.php', str_replace($aSearch, $aReplace, file_get_contents($this->okt->options->get('inc_dir').'/admin/configuration/themes/templates/oktTheme.tpl')));
				copy($this->okt->options->get('inc_dir').'/admin/configuration/themes/templates/locked_files.txt', $sThemePath.'/locked_files.txt');

			# css files
			\files::makeDir($sThemePath.'/css');
				copy($this->okt->options->get('inc_dir').'/admin/configuration/themes/templates/definitions.less.tpl', $sThemePath.'/css/definitions.less');
				copy($this->okt->options->get('inc_dir').'/admin/configuration/themes/templates/index.html.tpl', $sThemePath.'/css/index.html');
				copy($this->okt->options->get('inc_dir').'/admin/configuration/themes/templates/overload.less.tpl', $sThemePath.'/css/overload.less');

			# images files
			\files::makeDir($sThemePath.'/images');
				copy($this->okt->options->get('inc_dir').'/admin/configuration/themes/templates/index.html.tpl', $sThemePath.'/images/index.html');

			# js
			\files::makeDir($sThemePath.'/js');
				copy($this->okt->options->get('inc_dir').'/admin/configuration/themes/templates/index.html.tpl', $sThemePath.'/js/index.html');

			# locales files
			\files::makeDir($sThemePath.'/locales');
				\files::makeDir($sThemePath.'/locales/fr');
					copy($this->okt->options->get('inc_dir').'/admin/configuration/themes/templates/index.html.tpl', $sThemePath.'/locales/fr/index.html');

			# modules files
			\files::makeDir($sThemePath.'/modules');
				copy($this->okt->options->get('inc_dir').'/admin/configuration/themes/templates/index.html.tpl', $sThemePath.'/modules/index.html');

			# templates files
			\files::makeDir($sThemePath.'/templates');
				copy($this->okt->options->get('inc_dir').'/admin/configuration/themes/templates/index.html.tpl', $sThemePath.'/templates/index.html');
				copy($this->okt->options->get('themes_dir').'/default/templates/layout.php', $sThemePath.'/templates/layout.php');

			return $sId;

		} catch (Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	/**
	 * Install a theme from a zip file
	 *
	 * @param string $zip_file
	 * @param Tao\Themes\Collection $oThemes
	 */
	public static function installPackage($zip_file, Tao\Themes\Collection $oThemes)
	{
		$zip = new \fileUnzip($zip_file);
		$zip->getList(false,'#(^|/)(__MACOSX|\.svn|\.DS_Store|Thumbs\.db)(/|$)#');

		$zip_root_dir = $zip->getRootDir();

		if ($zip_root_dir !== false)
		{
			$target = dirname($zip_file);
			$destination = $target.'/'.$zip_root_dir;
			$define = $zip_root_dir.'/_define.php';
			$has_define = $zip->hasFile($define);
		}
		else {
			$target = dirname($zip_file).'/'.preg_replace('/\.([^.]+)$/','',basename($zip_file));
			$destination = $target;
			$define = '_define.php';
			$has_define = $zip->hasFile($define);
		}

		if ($zip->isEmpty())
		{
			$zip->close();
			unlink($zip_file);
			throw new \Exception(__('Empty theme zip file.'));
		}

		if (!$has_define)
		{
			$zip->close();
			unlink($zip_file);
			throw new \Exception(__('The zip file does not appear to be a valid theme.'));
		}

		$ret_code = 1;

		if (is_dir($destination))
		{
			throw new \Exception(__('The theme allready exists, you can not update a theme.'));
			/*
			copy($target.'/_define.php', $target.'/_define.php.bak');

			# test for update
			$sandbox = clone $oThemes;
			$zip->unzip($define, $target.'/_define.php');

			$sandbox->resetThemesList();
			$sandbox->requireDefine($target,basename($destination));
			unlink($target.'/_define.php');
			$new_themes = $sandbox->getThemesList();
			$old_themes = $oThemes->getThemesList();

			if (!empty($new_themes))
			{
				$tmp = array_keys($new_themes);
				$id = $tmp[0];
				$cur_theme = $old_themes[$id];

				if (!empty($cur_theme) && $new_themes[$id]['version'] != $cur_theme['version'])
				{
					# delete old theme
					if (!\files::deltree($destination)) {
						throw new \Exception(__('An error occurred during theme deletion.'));
					}
					$ret_code = 2;
				}
				else
				{
					$zip->close();
					unlink($zip_file);

					if (file_exists($target.'/_define.php.bak')) {
						rename($target.'/_define.php.bak', $target.'/_define.php');
					}

					throw new \Exception(sprintf(__('Unable to upgrade "%s". (same version)'),basename($destination)));
				}
			}
			else
			{
				$zip->close();
				unlink($zip_file);

				if (file_exists($target.'/_define.php.bak')) {
					rename($target.'/_define.php.bak', $target.'/_define.php');
				}

				throw new \Exception(sprintf(__('Unable to read new _define.php file')));
			}
			*/
		}

		$zip->unzipAll($target);
		$zip->close();
		unlink($zip_file);

		return $ret_code;
	}


	/* Méthodes utilitaires.
	----------------------------------------------------------*/

	/**
	 * Tri les thèmes par ordre alphabétique.
	 *
	 * @param array $aThemes
	 * @return void
	 */
	public static function sortThemes(array &$aThemes)
	{
		uasort($aThemes, 'self::sortThemesListCallable');
	}

	/**
	 * Fonction de callback de tri des thèmes
	 *
	 * @param string $a
	 * @param string $b
	 */
	protected static function sortThemesListCallable($a,$b)
	{
		return strcasecmp($a['name'],$b['name']);
	}

	/**
	 * Fonction de "pluralisation" des thèmes
	 *
	 * @param integer $count
	 * @return string
	 */
	public static function pluralizethemesCount($iCount)
	{
		if ($iCount == 1) {
			return __('c_a_themes_one_theme');
		}
		else if ($iCount > 1) {
			return sprintf(__('c_a_themes_%s_themes'),$iCount);
		}

		return __('c_a_themes_no_theme');
	}


	public static function getLockedFiles($sThemeId)
	{
		global $okt;

		$aLockedFiles = array();

		$sThemePath = $okt->options->get('themes_dir').'/'.$sThemeId.'/';

		if (!file_exists($sThemePath.'locked_files.txt')) {
			return $aLockedFiles;
		}

		$aFiles = file($sThemePath.'locked_files.txt',FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

		foreach ($aFiles as $sFile)
		{
			if (!file_exists($sThemePath.$sFile)) {
				continue;
			}
			elseif (is_dir($sThemePath.$sFile))
			{
				# @TODO : prise en charge des répertoires
			}
			else {
				$aLockedFiles[] = $sThemePath.$sFile;
			}
		}

		return $aLockedFiles;
	}


	/* Méthodes de gestion des dépôts de thèmes.
	----------------------------------------------------------*/

	/**
	 * Retourne les informations concernant les dépôts de thèmes.
	 *
	 * @param array $aRepositories
	 * @return array
	 */
	public function getRepositoriesInfos($aRepositories=array())
	{
		if (!$this->cache->contains($this->cache_repo_id)) {
			$this->saveRepositoriesInfosCache($aRepositories);
		}

		return $this->cache->fetch($this->cache_repo_id);
	}

	/**
	 * Enregistre les infos des dépôts dans le cache
	 *
	 * @param array $aRepositories
	 * @return boolean
	 */
	protected function saveRepositoriesInfosCache($aRepositories)
	{
		return $this->cache->save($this->cache_repo_id, $this->readRepositoriesInfos($aRepositories));
	}

	/**
	 * Lit les informations concernant les dépôts de thème et les retournes.
	 *
	 * @param array $aRepositories
	 * @return array
	 */
	protected function readRepositoriesInfos($aRepositories)
	{
		$aThemesRepositories = array();
		foreach ($aRepositories as $repository_id=>$repository_url)
		{
			if (($infos = $this->getRepositoryInfos($repository_url)) !== false) {
				$aThemesRepositories[$repository_id] = $infos;
			}
		}

		return $aThemesRepositories;
	}

	/**
	 * Retourne les informations d'un dépôt de themes donné.
	 *
	 * @param array $repository_url
	 * @return array
	 */
	protected function getRepositoryInfos($repository_url)
	{
		try
		{
			$repository_url = str_replace('%VERSION%', Utilities::getVersion(),$repository_url);

			$client = new HttpClient();
			$response = $client->get($repository_url)->send();

			if ($response->isSuccessful()) {
				return $this->readRepositoryInfos($response->getBody(true));
			}
			else {
				return false;
			}
		}
		catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Lit les informations XML d'un dépôt de themes donné et les retournes.
	 *
	 * @param sting $str
	 * @return array
	 */
	protected function readRepositoryInfos($str)
	{
		try
		{
			$xml = new \SimpleXMLElement($str,LIBXML_NOERROR);

			$return = array();
			foreach ($xml->theme as $theme)
			{
				if (isset($theme['id']))
				{
					$return[(string)$theme['id']] = array(
						'id' => (string)$theme['id'],
						'name' => (string)$theme['name'],
						'version' => (string)$theme['version'],
						'href' => (string)$theme['href'],
						'checksum' => (string)$theme['checksum'],
						'info' => (string)$theme['info']
					);
				}
			}

			if (empty($return)) {
				return false;
			}

			return $return;
		}
		catch (Exception $e) {
			throw $e;
		}
	}
}
