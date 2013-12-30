<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin\Controller\Config;

use Tao\Admin\Controller;
use Tao\Admin\Page;

class Display extends Controller
{
	public function page()
	{
		$this->okt->l10n->loadFile($this->okt->options->get('locales_dir').'/'.$this->okt->user->language.'/admin.display');

		$aUiThemes = Page::getUiThemes();

		$aNotAllowedAdminThemes = array(
			'dark-hive',
			'dot-luv',
			'eggplant',
			'le-frog',
			'mint-choc',
			'swanky-purse',
			'trontastic',
			'ui-darkness',
			'vader'
		);

		$aAllowedAdminThemes = array_diff($aUiThemes, $aNotAllowedAdminThemes);

		$aAllowedAdminThemes = array_combine($aAllowedAdminThemes, $aAllowedAdminThemes);


		foreach ($aAllowedAdminThemes as $theme)
		{
			if ($theme == $this->okt->config->admin_theme) {
				$aAllowedAdminThemes[$theme] = $theme.__('c_a_config_display_current_theme');
			}
		}

		$aAllowedAdminThemes = array_flip($aAllowedAdminThemes);


		if ($this->request->request->has('form_sent'))
		{
			# traitement d'un éventuel theme uploadé
			if (isset($_FILES['p_upload_theme']) && !empty($_FILES['p_upload_theme']['tmp_name']))
			{
				$sUploadedFile = $_FILES['p_upload_theme'];
				$sTempDir = $this->okt->options->getRootPath().'/temp/';
				$sZipFilename = $sTempDir.$sUploadedFile['name'];

				try {

					# on supprime l'éventuel répertoire temporaire s'il existe déjà
					if (is_dir($sTempDir)) {
						files::deltree($sTempDir);
					}

					$sExtension = pathinfo($sUploadedFile['name'],PATHINFO_EXTENSION);

					# des erreurs d'upload ?
					util::uploadStatus($sUploadedFile);

					# vérification de l'extension
					if ($sExtension != 'zip') {
						throw new Exception(__('c_a_config_display_not_zip_file'));
					}

					# création répertoire temporaire
					files::makeDir($sTempDir);

					if (!move_uploaded_file($sUploadedFile['tmp_name'],$sZipFilename)) {
						throw new Exception(__('c_a_config_display_unable_move_file'));
					}

					$oZip = new fileUnzip($sZipFilename);
					$oZip->getList(false,'#(^|/)(__MACOSX|\.svn|\.DS_Store|Thumbs\.db|development-bundle|js)(/|$)#');

					$zip_root_dir = $oZip->getRootDir();

					if ($zip_root_dir !== false)
					{
						$sTargetDir = dirname($sZipFilename);
						$sDestinationDir = $sTargetDir.'/'.$zip_root_dir;
						$sCssFilename = $zip_root_dir.'/css/custom-theme/'.basename($sTargetDir).'.css';
						$hasCssFile = $oZip->hasFile($sCssFilename);
					}
					else
					{
						$zip_root_dir = preg_replace('/\.([^.]+)$/','',basename($sZipFilename));
						$sTargetDir = dirname($sZipFilename).'/'.$zip_root_dir;
						$sDestinationDir = $sTargetDir;
						$sCssFilename = $zip_root_dir.'/css/custom-theme/'.basename($sTargetDir).'.css';
						$hasCssFile = $oZip->hasFile($sCssFilename);
					}

					if ($oZip->isEmpty())
					{
						$oZip->close();
						files::deltree($sTempDir);
						throw new Exception(__('c_a_config_display_empty_zip_file'));
					}

					if (!$hasCssFile)
					{
						$oZip->close();
						files::deltree($sTempDir);
						throw new Exception(__('c_a_config_display_not_valid_theme'));
					}

					$oZip->unzipAll($sTempDir);
					$oZip->close();
					debug($sTempDir);

					$sFinalPath = $this->okt->options->public_dir.'/ui-themes/custom';

					util::rcopy($sTempDir.$zip_root_dir.'/css/custom-theme', $sFinalPath);

					rename($sFinalPath.'/'.basename($sTargetDir).'.css', $sFinalPath.'/jquery-ui.css');
					rename($sFinalPath.'/'.basename($sTargetDir).'.min.css', $sFinalPath.'/jquery-ui.min.css');

					files::deltree($sTempDir);

					$_POST['p_admin_theme'] = 'custom';
				}
				catch (Exception $e) {
					files::deltree($sTempDir);
					$this->okt->error->set($e->getMessage());
				}
			}

			# enregistrement de la configuration
			$p_public_theme = $this->request->request->get('p_public_theme', 'base');
			$p_enable_admin_bar = $this->request->request->has('p_enable_admin_bar') ? true : false;
			$p_admin_sidebar_position = $this->request->request->getInt('p_admin_sidebar_position', 0);
			$p_admin_theme = $this->request->request->get('p_admin_theme', 'base');
			$p_admin_compress_output = $this->request->request->has('p_admin_compress_output') ? true : false;

			if (!in_array($p_admin_theme, $aAllowedAdminThemes) && $p_admin_theme != 'custom') {
				$p_admin_theme = $this->okt->config->admin_theme;
			}

			if ($this->okt->error->isEmpty())
			{
				$aNewConfig = array(
					'public_theme' 				=> $p_public_theme,
					'enable_admin_bar' 			=> $p_enable_admin_bar,
					'admin_theme' 				=> $p_admin_theme,
					'admin_sidebar_position'	=> $p_admin_sidebar_position,
					'admin_compress_output' 	=> $p_admin_compress_output
				);

				try
				{
					$this->okt->config->write($aNewConfig);

					$this->okt->page->flash->success(__('c_c_confirm_configuration_updated'));

					return $this->redirect($this->generateUrl('config_display'));
				}
				catch (Exception $e)
				{
					$this->okt->error->set(__('c_c_error_writing_configuration'));
					$this->okt->error->set($e->getMessage());
				}
			}
		}

		return $this->render('Config/Display', array(
			'aUiThemes' => $aUiThemes,
			'aAllowedAdminThemes' => $aAllowedAdminThemes
		));
	}
}
