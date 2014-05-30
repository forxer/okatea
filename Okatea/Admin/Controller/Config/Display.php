<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Config;

use Okatea\Admin\Controller;
use Okatea\Admin\Page;
use Okatea\Tao\Misc\Utilities;
use Symfony\Component\Filesystem\Filesystem;

class Display extends Controller
{

	public function page()
	{
		if (! $this->okt->checkPerm('display'))
		{
			return $this->serve401();
		}

		# Locales
		$this->okt->l10n->loadFile($this->okt->options->get('locales_dir') . '/%s/admin/display');

		# Liste des thèmes
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
			if ($theme == $this->okt->config->jquery_ui['admin'])
			{
				$aAllowedAdminThemes[$theme] = $theme . __('c_a_config_display_current_theme');
			}
		}

		$aAllowedAdminThemes = array_flip($aAllowedAdminThemes);

		if ($this->request->request->has('form_sent'))
		{
			# traitement d'un éventuel theme uploadé
			if (isset($_FILES['p_upload_theme']) && ! empty($_FILES['p_upload_theme']['tmp_name']))
			{
				$sUploadedFile = $_FILES['p_upload_theme'];
				$sTempDir = $this->okt->options->get('root_dir') . '/temp/';
				$sZipFilename = $sTempDir . $sUploadedFile['name'];

				try
				{
					$fs = new Filesystem();

					# on supprime l'éventuel répertoire temporaire s'il existe déjà
					if (is_dir($sTempDir))
					{
						$fs->remove($sTempDir);
					}

					$sExtension = pathinfo($sUploadedFile['name'], PATHINFO_EXTENSION);

					# des erreurs d'upload ?
					Utilities::uploadStatus($sUploadedFile);

					# vérification de l'extension
					if ($sExtension != 'zip')
					{
						throw new \Exception(__('c_a_config_display_not_zip_file'));
					}

					# création répertoire temporaire
					$fs->mkdir($sTempDir);

					if (! move_uploaded_file($sUploadedFile['tmp_name'], $sZipFilename))
					{
						throw new \Exception(__('c_a_config_display_unable_move_file'));
					}

					$oZip = new \fileUnzip($sZipFilename);
					$oZip->getList(false, '#(^|/)(__MACOSX|\.svn|\.DS_Store|Thumbs\.db|development-bundle|js)(/|$)#');

					$zip_root_dir = $oZip->getRootDir();

					if ($zip_root_dir !== false)
					{
						$sTargetDir = dirname($sZipFilename);
						$sDestinationDir = $sTargetDir . '/' . $zip_root_dir;
						$sCssFilename = $zip_root_dir . '/css/custom-theme/' . basename($sTargetDir) . '.css';
						$hasCssFile = $oZip->hasFile($sCssFilename);
					}
					else
					{
						$zip_root_dir = preg_replace('/\.([^.]+)$/', '', basename($sZipFilename));
						$sTargetDir = dirname($sZipFilename) . '/' . $zip_root_dir;
						$sDestinationDir = $sTargetDir;
						$sCssFilename = $zip_root_dir . '/css/custom-theme/' . basename($sTargetDir) . '.css';
						$hasCssFile = $oZip->hasFile($sCssFilename);
					}

					if ($oZip->isEmpty())
					{
						$oZip->close();
						$fs->remove($sTempDir);
						throw new \Exception(__('c_a_config_display_empty_zip_file'));
					}

					if (! $hasCssFile)
					{
						$oZip->close();
						$fs->remove($sTempDir);
						throw new \Exception(__('c_a_config_display_not_valid_theme'));
					}

					$oZip->unzipAll($sTempDir);
					$oZip->close();

					$sFinalPath = $this->okt->options->public_dir . '/components/jquery-ui/themes/custom';

					$fs->mirror($sTempDir . $zip_root_dir . '/css/custom-theme', $sFinalPath);
					$fs->rename($sFinalPath . '/' . basename($sTargetDir) . '.css', $sFinalPath . '/jquery-ui.css');
					$fs->rename($sFinalPath . '/' . basename($sTargetDir) . '.min.css', $sFinalPath . '/jquery-ui.min.css');

					$fs->remove($sTempDir);

					$this->request->request->set('p_jquery_ui_admin_theme', 'custom');
				}
				catch (Exception $e)
				{
					$fs->remove($sTempDir);
					$this->okt->error->set($e->getMessage());
				}
			}

			# enregistrement de la configuration
			$p_jquery_ui_admin_theme = $this->request->request->get('p_jquery_ui_admin_theme', 'base');

			if (! in_array($p_jquery_ui_admin_theme, $aAllowedAdminThemes) && $p_jquery_ui_admin_theme != 'custom')
			{
				$p_jquery_ui_admin_theme = $this->okt->config->jquery_ui['admin'];
			}

			if ($this->okt->error->isEmpty())
			{
				$aNewConfig = array(
					'jquery_ui' => array(
						'public' => $this->request->request->get('p_jquery_ui_public_theme', 'base'),
						'admin' => $p_jquery_ui_admin_theme
					),
					'enable_admin_bar' => $this->request->request->has('p_enable_admin_bar'),
					'admin_menu_position' => $this->request->request->get('p_admin_menu_position', 'top')
				);

				$this->okt->config->write($aNewConfig);

				$this->page->flash->success(__('c_c_confirm_configuration_updated'));

				return $this->redirect($this->generateUrl('config_display'));
			}
		}

		return $this->render('Config/Display', array(
			'aUiThemes' => $aUiThemes,
			'aAllowedAdminThemes' => $aAllowedAdminThemes
		));
	}
}
