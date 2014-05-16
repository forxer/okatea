<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Images;

use Imagine\Gd\Imagine;
use Imagine\Image;
use Okatea\Tao\Misc\Utilities;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Outil pour l'upload des images.
 */
class ImageUpload
{

	/**
	 * Okatea application instance.
	 * 
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * Référence de l'objet gestionnaire d'erreurs.
	 * 
	 * @var object oktError
	 */
	protected $error;

	/**
	 * Le tableau des données de configuration.
	 * 
	 * @var array
	 */
	public $aConfig = array(
		'enable' => true,
		'number' => 5,
		
		'width' => 800,
		'height' => 600,
		'resize_type' => 'ratio',
		
		'width_min' => 150,
		'height_min' => 100,
		'resize_type_min' => 'ratio',
		
		'width_min_2' => 0,
		'height_min_2' => 0,
		'resize_type_min_2' => 'ratio',
		
		'width_min_3' => 0,
		'height_min_3' => 0,
		'resize_type_min_3' => 'ratio',
		
		'width_min_4' => 0,
		'height_min_4' => 0,
		'resize_type_min_4' => 'ratio',
		
		'width_min_5' => 0,
		'height_min_5' => 0,
		'resize_type_min_5' => 'ratio',
		
		'square_size' => 80,
		
		'watermark_file' => '',
		'watermark_position' => '',
		
		'files_patern' => 'p_images_%s',
		'files_alt_patern' => 'p_images_alt_%s',
		'files_title_patern' => 'p_images_title_%s',
		
		'upload_dir' => '/',
		'upload_url' => '/'
	);

	/**
	 * Liste des extensions autorisées.
	 * 
	 * @var array
	 */
	protected static $aAllowedExts = array(
		'gif',
		'jpg',
		'jpeg',
		'png'
	);

	/**
	 * Liste des types d'images autorisés.
	 * 
	 * @var array
	 */
	protected static $aAllowedTypes = array(
		'image/gif',
		'image/jpeg',
		'image/pjpeg',
		'image/png',
		'image/x-png'
	);

	/**
	 * Constructeur.
	 *
	 * @param object $okt        	
	 * @param array $aConfig        	
	 * @return void
	 */
	public function __construct($okt, $aConfig = array())
	{
		$this->okt = $okt;
		$this->error = $okt->error;
		
		$this->setConfig($aConfig);
	}

	/**
	 * Définit la configuration.
	 *
	 * @param array $aConfig        	
	 * @return void
	 */
	public function setConfig($aConfig)
	{
		$this->aConfig = $aConfig + $this->aConfig;
	}

	/**
	 * Ajout des images d'un élément donné à partir d'un tableau de chemins de fichiers.
	 *
	 * @param array $aFilenames        	
	 * @param integer $iItemId        	
	 * @return array
	 */
	public function addImagesFromArray($iItemId, $aFilenames)
	{
		$aImages = array();
		
		$j = 1;
		
		for ($i = 0; $i <= $this->aConfig['number']; $i ++)
		{
			$aImages[$j] = '';
			
			$sFilename = $aFilenames[$i];
			
			if (! file_exists($sFilename))
			{
				continue;
			}
			
			try
			{
				$sExtension = pathinfo($sFilename, PATHINFO_EXTENSION);
				
				# vérification de l'extension
				self::checkExtension($sExtension);
				
				# vérification du type
				//self::checkType($sFilename);
				

				# répertoire des images
				$sCurrentImagesDir = $this->getCurrentUploadDir($iItemId);
				$sCurrentImagesUrl = $this->getCurrentUploadUrl($iItemId);
				
				# création du répertoire s'il existe pas
				(new Filesystem())->mkdir($sCurrentImagesDir);
				
				$sOutput = $j . '.' . $sExtension;
				
				if (! copy($sFilename, $sCurrentImagesDir . '/' . $sOutput))
				{
					throw new \Exception('Impossible de copier le fichier image.');
				}
				
				# copie de l'originale
				copy($sCurrentImagesDir . '/' . $sOutput, $sCurrentImagesDir . '/o-' . $sOutput);
				
				# création des miniatures
				$this->buildThumbnails($iItemId, $sOutput, $sExtension);
				
				# récupération des infos des images
				$aImages[$j] = self::getImagesFilesInfos($sCurrentImagesDir, $sCurrentImagesUrl, $sOutput);
				
				$j ++;
			}
			catch (\Exception $e)
			{
				$this->error->set('Problème avec l’image ' . $i . ' : ' . $e->getMessage());
			}
		}
		
		return array_filter($aImages);
	}

	/**
	 * Réalise l'upload d'une simple image et retourne son chemin.
	 *
	 * Par exemple utilisé pour l'upload des filigrane.
	 *
	 * Il n'y a PAS de création de miniature.
	 *
	 * @param $form_input_name Le
	 *        	nom du champs du formulaire
	 * @param $sCurrentImageDir Le
	 *        	chemin du répertoire destination
	 * @param $sFilename Le
	 *        	nom du fichier destination sans l'extension
	 * @return string Le nom de l'image
	 */
	public static function getSingleUploadedFile($form_input_name = 'p_file', $sCurrentImageDir, $sFilename)
	{
		global $okt;
		
		$return = '';
		
		if (isset($_FILES[$form_input_name]) && ! empty($_FILES[$form_input_name]['tmp_name']))
		{
			$sUploadedFile = $_FILES[$form_input_name];
			
			try
			{
				# extension du fichier
				$sExtension = pathinfo($sUploadedFile['name'], PATHINFO_EXTENSION);
				
				# des erreurs d'upload ?
				Utilities::uploadStatus($sUploadedFile);
				
				# vérification de l'extension
				self::checkExtension($sExtension);
				
				# vérification du type
				self::checkType($sUploadedFile['type']);
				
				# création du répertoire s'il existe pas
				(new Filesystem())->mkdir($sCurrentImageDir);
				
				# nom du fichier
				$sOutput = $sFilename . '.' . $sExtension;
				
				# suppression de l'éventuel ancien fichier
				(new Filesystem())->remove($sCurrentImageDir . '/' . $sOutput);
				
				if (! move_uploaded_file($sUploadedFile['tmp_name'], $sCurrentImageDir . $sOutput))
				{
					throw new \Exception('Impossible de déplacer sur le serveur le fichier téléchargé.');
				}
				
				$return = $sOutput;
			}
			catch (\Exception $e)
			{
				$okt->error->set('Problème avec l’image : ' . $e->getMessage());
			}
		}
		
		return $return;
	}

	/**
	 * Ajout des images d'un élément donné.
	 *
	 * @param integer $iItemId        	
	 * @return array
	 */
	public function addImages($iItemId)
	{
		$aImages = array();
		
		$j = 1;
		
		for ($i = 1; $i <= $this->aConfig['number']; $i ++)
		{
			$aImages[$j] = '';
			
			if (! $this->okt->request->files->has(sprintf($this->aConfig['files_patern'], $i)))
			{
				continue;
			}
			
			try
			{
				$oUploadedFile = $this->okt->request->files->get(sprintf($this->aConfig['files_patern'], $i));
				
				if (null === $oUploadedFile)
				{
					continue;
				}
				
				$sExtension = $oUploadedFile->guessExtension();
				
				# vérification de l'extension
				self::checkExtension($sExtension);
				
				# vérification du type
				self::checkType($oUploadedFile->getMimeType());
				
				# répertoire des images
				$sCurrentImagesDir = $this->getCurrentUploadDir($iItemId);
				$sCurrentImagesUrl = $this->getCurrentUploadUrl($iItemId);
				
				$sOutput = $j . '.' . $sExtension;
				
				$oUploadedFile->move($sCurrentImagesDir, $sOutput);
				
				# copie de l'originale
				copy($sCurrentImagesDir . '/' . $sOutput, $sCurrentImagesDir . '/o-' . $sOutput);
				
				# création des miniatures
				$this->buildThumbnails($iItemId, $sOutput, $sExtension);
				
				# récupération des infos des images
				$aImages[$j] = self::getImagesFilesInfos($sCurrentImagesDir, $sCurrentImagesUrl, $sOutput);
				
				# stockage du nom original
				$aImages[$j]['original_name'] = $sUploadedFile['name'];
				
				# ajout d'un éventuel texte alternatif
				$aImages[$j]['alt'] = $this->okt->request->request->get(sprintf($this->aConfig['files_alt_patern'], $i), '');
				
				# ajout d'un éventuel titre
				$aImages[$j]['title'] = $this->okt->request->request->get(sprintf($this->aConfig['files_title_patern'], $i), '');
				
				$j ++;
			}
			catch (\Exception $e)
			{
				$this->error->set('Problème avec l’image ' . $i . ' : ' . __($e->getMessage()));
			}
		}
		
		return array_filter($aImages);
	}

	/**
	 * Modification des images d'un élément donné.
	 *
	 * @param integer $iItemId        	
	 * @param array $aCurrentImages        	
	 * @return array
	 */
	public function updImages($iItemId, $aCurrentImages = array())
	{
		$aNewImages = array();
		
		$j = 1;
		
		for ($i = 1; $i <= $this->aConfig['number']; $i ++)
		{
			if (! $this->okt->request->files->has(sprintf($this->aConfig['files_patern'], $i)))
			{
				if (isset($aCurrentImages[$i]))
				{
					$aNewImages[$j] = $aCurrentImages[$i];
					
					$aNewImages[$j]['alt'] = $this->okt->request->request->get(sprintf($this->aConfig['files_alt_patern'], $i), '');
					
					$aNewImages[$j]['title'] = $this->okt->request->request->get(sprintf($this->aConfig['files_title_patern'], $i), '');
					
					$j ++;
				}
				continue;
			}
			
			try
			{
				$oUploadedFile = $this->okt->request->files->get(sprintf($this->aConfig['files_patern'], $i));
				
				if (null === $oUploadedFile)
				{
					if (isset($aCurrentImages[$i]))
					{
						$aNewImages[$j] = $aCurrentImages[$i];
						
						$aNewImages[$j]['alt'] = $this->okt->request->request->get(sprintf($this->aConfig['files_alt_patern'], $i), '');
						
						$aNewImages[$j]['title'] = $this->okt->request->request->get(sprintf($this->aConfig['files_title_patern'], $i), '');
						
						$j ++;
					}
					continue;
				}
				
				$sExtension = $oUploadedFile->guessExtension();
				
				# vérification de l'extension
				self::checkExtension($sExtension);
				
				# vérification du type
				self::checkType($oUploadedFile->getMimeType());
				
				# répertoire des images
				$sCurrentImagesDir = $this->getCurrentUploadDir($iItemId);
				$sCurrentImagesUrl = $this->getCurrentUploadUrl($iItemId);
				
				# suppression des éventuels ancien fichier et ancien original
				if (isset($aCurrentImages[$i]['img_name']))
				{
					(new Filesystem())->remove(array(
						$sCurrentImagesDir . '/' . $aCurrentImages[$i]['img_name'],
						$sCurrentImagesDir . '/o-' . $aCurrentImages[$i]['img_name']
					));
				}
				
				$sOutput = $j . '.' . $sExtension;
				
				$oUploadedFile->move($sCurrentImagesDir, $sOutput);
				
				# copie de l'originale
				copy($sCurrentImagesDir . '/' . $sOutput, $sCurrentImagesDir . '/o-' . $sOutput);
				
				# création des miniatures et du square
				$this->buildThumbnails($iItemId, $sOutput, $sExtension);
				
				# récupération des infos des images
				$aNewImages[$j] = self::getImagesFilesInfos($sCurrentImagesDir, $sCurrentImagesUrl, $sOutput);
				
				# ajout d'un éventuel texte alternatif
				$aNewImages[$j]['alt'] = $this->okt->request->request->get(sprintf($this->aConfig['files_alt_patern'], $i), '');
				
				# ajout d'un éventuel title
				$aNewImages[$j]['title'] = $this->okt->request->request->get(sprintf($this->aConfig['files_title_patern'], $i), '');
				
				$j ++;
			}
			catch (\Exception $e)
			{
				$this->okt->error->set('Problème avec l’image ' . $i . ' : ' . $e->getMessage());
			}
		}
		
		return array_filter($aNewImages);
	}

	/**
	 * Suppression de toutes les images d'un élément
	 *
	 * @param integer $iItemId        	
	 * @return void
	 */
	public function deleteAllImages($iItemId)
	{
		(new Filesystem())->remove($this->getCurrentUploadDir($iItemId));
	}

	/**
	 * Suppression d'une image donnée d'un élément donné.
	 *
	 * @param integer $iItemId        	
	 * @param array $aCurrentImages        	
	 * @param integer $iImgId        	
	 * @return array
	 */
	public function deleteImage($iItemId, $aCurrentImages, $iImgId)
	{
		if (! isset($aCurrentImages[$iImgId]))
		{
			$this->error->set('L’image n’existe pas.');
			return false;
		}
		
		$sCurrentImagesDir = $this->getCurrentUploadDir($iItemId);
		$sCurrentImagesUrl = $this->getCurrentUploadUrl($iItemId);
		
		# suppression des fichiers sur le disque
		

		(new Filesystem())->remove(array(
			$sCurrentImagesDir . '/' . $aCurrentImages[$iImgId]['img_name'],
			$sCurrentImagesDir . '/o-' . $aCurrentImages[$iImgId]['img_name'],
			$sCurrentImagesDir . '/min-' . $aCurrentImages[$iImgId]['img_name'],
			$sCurrentImagesDir . '/min2-' . $aCurrentImages[$iImgId]['img_name'],
			$sCurrentImagesDir . '/min3-' . $aCurrentImages[$iImgId]['img_name'],
			$sCurrentImagesDir . '/min4-' . $aCurrentImages[$iImgId]['img_name'],
			$sCurrentImagesDir . '/min5-' . $aCurrentImages[$iImgId]['img_name'],
			$sCurrentImagesDir . '/sq-' . $aCurrentImages[$iImgId]['img_name']
		));
		
		# suppression du nom pour les infos de la BDD
		unset($aCurrentImages[$iImgId]);
		
		$aNewImages = array();
		
		$j = 1;
		for ($i = 1; $i <= $this->aConfig['number']; $i ++)
		{
			if (! isset($aCurrentImages[$i]))
			{
				continue;
			}
			
			$sExtension = pathinfo($aCurrentImages[$i]['img_name'], PATHINFO_EXTENSION);
			
			$sNewName = $j . '.' . $sExtension;
			
			if (file_exists($sCurrentImagesDir . '/' . $aCurrentImages[$i]['img_name']))
			{
				rename($sCurrentImagesDir . '/' . $aCurrentImages[$i]['img_name'], $sCurrentImagesDir . '/' . $sNewName);
			}
			
			if (file_exists($sCurrentImagesDir . '/o-' . $aCurrentImages[$i]['img_name']))
			{
				rename($sCurrentImagesDir . '/o-' . $aCurrentImages[$i]['img_name'], $sCurrentImagesDir . '/o-' . $sNewName);
			}
			
			if (file_exists($sCurrentImagesDir . '/min-' . $aCurrentImages[$i]['img_name']))
			{
				rename($sCurrentImagesDir . '/min-' . $aCurrentImages[$i]['img_name'], $sCurrentImagesDir . '/min-' . $sNewName);
			}
			
			if (file_exists($sCurrentImagesDir . '/min2-' . $aCurrentImages[$i]['img_name']))
			{
				rename($sCurrentImagesDir . '/min2-' . $aCurrentImages[$i]['img_name'], $sCurrentImagesDir . '/min2-' . $sNewName);
			}
			
			if (file_exists($sCurrentImagesDir . '/min3-' . $aCurrentImages[$i]['img_name']))
			{
				rename($sCurrentImagesDir . '/min3-' . $aCurrentImages[$i]['img_name'], $sCurrentImagesDir . '/min3-' . $sNewName);
			}
			
			if (file_exists($sCurrentImagesDir . '/min4-' . $aCurrentImages[$i]['img_name']))
			{
				rename($sCurrentImagesDir . '/min4-' . $aCurrentImages[$i]['img_name'], $sCurrentImagesDir . '/min4-' . $sNewName);
			}
			
			if (file_exists($sCurrentImagesDir . '/min5-' . $aCurrentImages[$i]['img_name']))
			{
				rename($sCurrentImagesDir . '/min5-' . $aCurrentImages[$i]['img_name'], $sCurrentImagesDir . '/min5-' . $sNewName);
			}
			
			if (file_exists($sCurrentImagesDir . '/sq-' . $aCurrentImages[$i]['img_name']))
			{
				rename($sCurrentImagesDir . '/sq-' . $aCurrentImages[$i]['img_name'], $sCurrentImagesDir . '/sq-' . $sNewName);
			}
			
			# récupération des infos des images
			$aNewImages[$j] = self::getImagesFilesInfos($sCurrentImagesDir, $sCurrentImagesUrl, $sNewName);
			
			$j ++;
		}
		
		if (! Utilities::dirHasFiles($sCurrentImagesDir))
		{
			(new Filesystem())->remove($sCurrentImagesDir);
		}
		
		return array_filter($aNewImages);
	}

	/**
	 * Création des miniatures d'un élément donné.
	 *
	 * @param integer $iItemId
	 *        	de l'élément
	 * @param string $sOutput
	 *        	fichier de sortie
	 * @param string $sExtension
	 *        	du fichier
	 * @return void
	 */
	public function buildThumbnails($iItemId, $sOutput, $sExtension = null)
	{
		# répertoire des images
		$sCurrentImagesDir = $this->getCurrentUploadDir($iItemId);
		
		# extension du fichier
		if (is_null($sExtension))
		{
			$sExtension = pathinfo($sOutput, PATHINFO_EXTENSION);
		}
		
		# fichier source ?
		if (file_exists($sCurrentImagesDir . '/o-' . $sOutput))
		{
			$sSourceFile = $sCurrentImagesDir . '/o-' . $sOutput;
		}
		else
		{
			$sSourceFile = $sCurrentImagesDir . '/' . $sOutput;
		}
		
		# si l'image est trop grande on la redimensionne
		$this->imageResize($sSourceFile, $sCurrentImagesDir . '/' . $sOutput, $this->aConfig['resize_type'], $this->aConfig['width'], $this->aConfig['height'], $this->aConfig['watermark_file'], $this->aConfig['watermark_position']);
		
		# miniature
		$this->imageResize($sSourceFile, $sCurrentImagesDir . '/min-' . $sOutput, $this->aConfig['resize_type_min'], $this->aConfig['width_min'], $this->aConfig['height_min'], 'min-' . $this->aConfig['watermark_file'], $this->aConfig['watermark_position']);
		
		# miniature 2
		$this->imageResize($sSourceFile, $sCurrentImagesDir . '/min2-' . $sOutput, $this->aConfig['resize_type_min_2'], $this->aConfig['width_min_2'], $this->aConfig['height_min_2'], 'min2-' . $this->aConfig['watermark_file'], $this->aConfig['watermark_position']);
		
		# miniature 3
		$this->imageResize($sSourceFile, $sCurrentImagesDir . '/min3-' . $sOutput, $this->aConfig['resize_type_min_3'], $this->aConfig['width_min_3'], $this->aConfig['height_min_3'], 'min3-' . $this->aConfig['watermark_file'], $this->aConfig['watermark_position']);
		
		# miniature 4
		$this->imageResize($sSourceFile, $sCurrentImagesDir . '/min4-' . $sOutput, $this->aConfig['resize_type_min_4'], $this->aConfig['width_min_4'], $this->aConfig['height_min_4'], 'min4-' . $this->aConfig['watermark_file'], $this->aConfig['watermark_position']);
		
		# miniature 5
		$this->imageResize($sSourceFile, $sCurrentImagesDir . '/min5-' . $sOutput, $this->aConfig['resize_type_min_5'], $this->aConfig['width_min_5'], $this->aConfig['height_min_5'], 'min5-' . $this->aConfig['watermark_file'], $this->aConfig['watermark_position']);
		
		# square
		$this->imageResize($sSourceFile, $sCurrentImagesDir . '/sq-' . $sOutput, 'crop', $this->aConfig['square_size'], $this->aConfig['square_size'], 'sq-' . $this->aConfig['watermark_file'], $this->aConfig['watermark_position']);
	}

	/**
	 * Upload d'un filigrane.
	 *
	 * @param string $sFormInputName
	 *        	Le nom du champs du formulaire
	 * @param string $sFilename
	 *        	Le nom du fichier destination sans l'extension
	 * @return string Le nom de l'image
	 */
	public function uploadWatermark($sFormInputName, $sFilename)
	{
		$sDirectory = $this->getWatermarkUploadDir();
		$sOutput = self::getSingleUploadedFile($sFormInputName, $sDirectory, $sFilename);
		
		if ($sOutput != '')
		{
			# si l'image est trop grande on la redimensionne
			$this->imageResize($sDirectory . '/' . $sOutput, $sDirectory . '/' . $sOutput, $this->aConfig['resize_type'], $this->aConfig['width'], $this->aConfig['height'], null);
			
			# miniature
			$this->imageResize($sDirectory . '/' . $sOutput, $sDirectory . '/min-' . $sOutput, $this->aConfig['resize_type_min'], $this->aConfig['width_min'], $this->aConfig['height_min'], null);
			
			# miniature 2
			$this->imageResize($sDirectory . '/' . $sOutput, $sDirectory . '/min2-' . $sOutput, $this->aConfig['resize_type_min_2'], $this->aConfig['width_min_2'], $this->aConfig['height_min_2'], null);
			
			# miniature 3
			$this->imageResize($sDirectory . '/' . $sOutput, $sDirectory . '/min3-' . $sOutput, $this->aConfig['resize_type_min_3'], $this->aConfig['width_min_3'], $this->aConfig['height_min_3'], null);
			
			# miniature 4
			$this->imageResize($sDirectory . '/' . $sOutput, $sDirectory . '/min4-' . $sOutput, $this->aConfig['resize_type_min_4'], $this->aConfig['width_min_4'], $this->aConfig['height_min_4'], null);
			
			# miniature 5
			$this->imageResize($sDirectory . '/' . $sOutput, $sDirectory . '/min5-' . $sOutput, $this->aConfig['resize_type_min_5'], $this->aConfig['width_min_5'], $this->aConfig['height_min_5'], null);
			
			# square
			$this->imageResize($sDirectory . '/' . $sOutput, $sDirectory . '/sq-' . $sOutput, 'crop', $this->aConfig['square_size'], $this->aConfig['square_size'], null);
		}
		
		return $sOutput;
	}

	/**
	 * Redimensionnement d'une image.
	 *
	 * @param string $sSourceFile
	 *        	Chemin du fichier source
	 * @param string $sOutput
	 *        	Chemin du fichier destination
	 * @param string $sResizeType
	 *        	Type de redimensionnement (ratio ou crop)
	 * @param integer $iWidth
	 *        	Largeur
	 * @param integer $iHeight
	 *        	Hauteur
	 * @param string $sWatermarkFile        	
	 * @param string $sWatermarkPosition        	
	 * @return void
	 */
	public function imageResize($sSourceFile, $sOutput, $sResizeType, $iWidth, $iHeight, $sWatermarkFile = null, $sWatermarkPosition = 'cc')
	{
		if ($iWidth <= 0)
		{
			return null;
		}
		
		$imagine = new Imagine();
		
		$size = new Image\Box($iWidth, $iHeight);
		
		$mode = $sResizeType === 'ratio' ? Image\ImageInterface::THUMBNAIL_INSET : Image\ImageInterface::THUMBNAIL_OUTBOUND;
		
		$image = $imagine->open($sSourceFile)->thumbnail($size, $mode);
		
		if (! empty($sWatermarkFile) && file_exists($this->getWatermarkUploadDir() . '/' . $sWatermarkFile))
		{
			$watermark = $imagine->open($this->getWatermarkUploadDir() . '/' . $sWatermarkFile);
			
			$size = $image->getSize();
			$wSize = $watermark->getSize();
			
			$x = $size->getWidth() - $wSize->getWidth();
			$y = $size->getHeight() - $wSize->getHeight();
			
			if ($x >= 0 && $y >= 0 && $size->getWidth() > $wSize->getWidth() && $size->getHeight() > $wSize->getHeight())
			{
				$bottomRight = new Image\Point($x, $y);
				$image->paste($watermark, $bottomRight);
			}
		}
		
		$image->save($sOutput);
	}

	/**
	 * Construit le tableau d'informations complètes
	 * des images d'un élément donné et le retourne.
	 *
	 * @param integer $iItemId        	
	 * @param array $aImages        	
	 * @return array
	 */
	public function buildImagesInfos($iItemId, $aImagesDb)
	{
		return self::getImagesInfos($this->getCurrentUploadDir($iItemId), $this->getCurrentUploadUrl($iItemId), $aImagesDb, $this->aConfig['number']);
	}

	/**
	 * Retourne un tableau contenant les informations détaillées des images d'un élément.
	 *
	 * @param string $sImagesPath        	
	 * @param string $sImagesUrl        	
	 * @param string $aImagesDb        	
	 * @param integer $iNum        	
	 * @return array
	 */
	public static function getImagesInfos($sImagesPath, $sImagesUrl, $aImagesDb, $iNum)
	{
		$aImages = array();
		
		$j = 1;
		for ($i = 1; $i <= $iNum; $i ++)
		{
			if (isset($aImagesDb[$i]))
			{
				$aImages[$j] = self::getImagesFilesInfos($sImagesPath, $sImagesUrl, $aImagesDb[$i]);
				$j ++;
			}
		}
		
		return $aImages;
	}

	/**
	 * Construit le tableau d'informations complètes
	 * d'une image d'un élément donné et le retourne.
	 *
	 * @param integer $iItemId        	
	 * @param array $aImages        	
	 * @return array
	 */
	public function buildImageInfos($iItemId, $sImagesName)
	{
		return self::getImagesFilesInfos($this->getCurrentUploadDir($iItemId), $this->getCurrentUploadUrl($iItemId), $sImagesName);
	}

	/**
	 * Récupère les informations des images.
	 *
	 * @param string $sImagesPath        	
	 * @param string $sImagesUrl        	
	 * @param string $sImage        	
	 * @return array
	 */
	public static function getImagesFilesInfos($sImagesPath, $sImagesUrl, $sImage)
	{
		$aImages = self::getImageFileInfos($sImagesPath, $sImagesUrl, $sImage);
		
		if (! empty($aImages))
		{
			$aImages = array_merge($aImages, self::getImageFileInfos($sImagesPath, $sImagesUrl, $sImage, 'min'));
			
			$aImages = array_merge($aImages, self::getImageFileInfos($sImagesPath, $sImagesUrl, $sImage, 'min2'));
			
			$aImages = array_merge($aImages, self::getImageFileInfos($sImagesPath, $sImagesUrl, $sImage, 'min3'));
			
			$aImages = array_merge($aImages, self::getImageFileInfos($sImagesPath, $sImagesUrl, $sImage, 'min4'));
			
			$aImages = array_merge($aImages, self::getImageFileInfos($sImagesPath, $sImagesUrl, $sImage, 'min5'));
			
			$aImages = array_merge($aImages, self::getImageFileInfos($sImagesPath, $sImagesUrl, $sImage, 'sq'));
		}
		
		return $aImages;
	}

	/**
	 * Retourne un tableau contenant les informations détaillées d'une image donnée.
	 *
	 * @param string $sImagesPath        	
	 * @param string $sImagesUrl        	
	 * @param string $sImage        	
	 * @param string $sType        	
	 */
	protected static function getImageFileInfos($sImagesPath, $sImagesUrl, $sImage, $sType = null)
	{
		if ($sType === 'min')
		{
			$sImage = 'min-' . $sImage;
			$sKeyPrefix = 'min_';
		}
		elseif ($sType === 'min2')
		{
			$sImage = 'min2-' . $sImage;
			$sKeyPrefix = 'min2_';
		}
		elseif ($sType === 'min3')
		{
			$sImage = 'min3-' . $sImage;
			$sKeyPrefix = 'min3_';
		}
		elseif ($sType === 'min4')
		{
			$sImage = 'min4-' . $sImage;
			$sKeyPrefix = 'min4_';
		}
		elseif ($sType === 'min5')
		{
			$sImage = 'min5-' . $sImage;
			$sKeyPrefix = 'min5_';
		}
		elseif ($sType === 'sq' || $sType === 'square')
		{
			$sImage = 'sq-' . $sImage;
			$sKeyPrefix = 'square_';
		}
		else
		{
			$sKeyPrefix = 'img_';
		}
		
		if (! file_exists($sImagesPath . '/' . $sImage))
		{
			return array();
		}
		
		$aInfos = getimagesize($sImagesPath . '/' . $sImage);
		
		return array(
			$sKeyPrefix . 'name' => $sImage,
			$sKeyPrefix . 'file' => $sImagesPath . '/' . $sImage,
			$sKeyPrefix . 'url' => $sImagesUrl . '/' . $sImage,
			$sKeyPrefix . 'width' => $aInfos[0],
			$sKeyPrefix . 'height' => $aInfos[1],
			$sKeyPrefix . 'type' => image_type_to_mime_type($aInfos[2]),
			$sKeyPrefix . 'attr' => $aInfos[3]
		);
	}

	/**
	 * Vérifie l'extension d'un fichier.
	 *
	 * @param
	 *        	$sExtension
	 * @return void
	 */
	public static function checkExtension($sExtension)
	{
		if (! in_array($sExtension, self::$aAllowedExts))
		{
			throw new \Exception('Type de fichier non-autorisé.');
		}
	}

	/**
	 * Vérifie le type d'un fichier.
	 *
	 * @param
	 *        	$sType
	 * @return void
	 */
	public static function checkType($sType)
	{
		if (! in_array($sType, self::$aAllowedTypes))
		{
			throw new \Exception('Type de fichier non-autorisé.');
		}
	}

	/**
	 * Retourne le chemin du répertoire courant.
	 *
	 * @param string $iItemId        	
	 * @return string
	 */
	public function getCurrentUploadDir($iItemId)
	{
		return $this->aConfig['upload_dir'] . '/' . $iItemId;
	}

	/**
	 * Retourne l'URL du répertoire courant.
	 *
	 * @param string $iItemId        	
	 * @return string
	 */
	public function getCurrentUploadUrl($iItemId)
	{
		return $this->aConfig['upload_url'] . '/' . $iItemId;
	}

	/**
	 * Retourne le chemin du répertoire du filigrane.
	 *
	 * @return string
	 */
	public function getWatermarkUploadDir()
	{
		return $this->aConfig['upload_dir'] . '/watermark';
	}

	/**
	 * Retourne l'URL du répertoire du filigrane.
	 *
	 * @return string
	 */
	public function getWatermarkUploadUrl()
	{
		return $this->aConfig['upload_url'] . '/watermark';
	}
}
