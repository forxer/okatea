<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Images;

use Tao\Forms\Statics\FormElements as form;

/**
 * Aide à la configuration de l'upload des images.
 *
 */
class ImageUploadConfig
{
	/**
	 * L'objet core.
	 * @var object oktCore
	 */
	protected $okt;

	/**
	 * L'objet gestionnaire d'erreurs.
	 * @var object
	 */
	protected $error;

	/**
	 * L'objet oktImageUpload.
	 * @var object
	 */
	protected $oImageUpload;

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
	 * Gestion d'une seule image ou non.
	 * @var boolean
	 */
	protected $bUnique = false;

	/**
	 * Avec filigrane ?
	 * @var boolean
	 */
	protected $bWithWatermark = true;

	/**
	 * Nombre max d'image
	 * @var integer
	 */
	protected $iMaxFileUploads = null;


	/**
	 * Constructor.
	 *
	 * @param oktCore $okt
	 * @param oktImageUpload $oImageUpload
	 * @return void
	 */
	public function __construct($okt, $oImageUpload)
	{
		$this->okt = $okt;
		$this->error = $okt->error;

		$this->oImageUpload = $oImageUpload;

		$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$okt->user->language.'/admin.images.config');

		# Store upload_max_filesize in bytes
		$u_max_size = \files::str2bytes(ini_get('upload_max_filesize'));
		$p_max_size = \files::str2bytes(ini_get('post_max_size'));

		if ($p_max_size < $u_max_size) {
			$u_max_size = $p_max_size;
		}

		$this->iMaxFileUploads = $u_max_size;
	}

	/**
	 * Définit l'URL de base de la page de configuration.
	 *
	 * @param string $sBaseUrl
	 * @return void
	 */
	public function setBaseUrl($sBaseUrl='/')
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
	 * Définit si on as la gestion que d'une seule image ou non.
	 *
	 * @param boolean $bUnique
	 * @return void
	 */
	public function setUnique($bUnique=false)
	{
		$this->bUnique = (boolean)$bUnique;
	}

	/**
	 * Définit si active le filigrane ou non.
	 *
	 * @param boolean $bWithWatermark
	 * @return void
	 */
	public function setWithWatermark($bWithWatermark=true)
	{
		$this->bWithWatermark = (boolean)$bWithWatermark;
	}

	/**
	 * Retourne un tableau avec les données de configuration en vue d'un enregistrement.
	 *
	 * @return array
	 */
	public function getPostConfig()
	{
		$bEnableImages = $this->okt->request->request->has($this->sFormPrefix.'enable_images') ? true : false;

		if ($this->bUnique) {
			$iNumberImages = 1;
		}
		else
		{
			$iNumberImages = $this->okt->request->request->getInt($this->sFormPrefix.'number_images', 0);

			if (!empty($this->iMaxFileUploads) && $iNumberImages > $this->iMaxFileUploads) {
				$iNumberImages = $this->iMaxFileUploads;
			}
		}

		$iWidth = $this->okt->request->request->getInt($this->sFormPrefix.'width', 0);
		$iHeight = $this->okt->request->request->getInt($this->sFormPrefix.'height', 0);
		$sResizeType = $this->okt->request->request->get($this->sFormPrefix.'resize_type', 'ratio');

		$iWidthMin = $this->okt->request->request->getInt($this->sFormPrefix.'width_min', 0);
		$iHeightMin = $this->okt->request->request->getInt($this->sFormPrefix.'height_min', 0);
		$sResizeTypeMin = $this->okt->request->request->get($this->sFormPrefix.'resize_type_min', 'ratio');

		$iWidthMin2 = $this->okt->request->request->getInt($this->sFormPrefix.'width_min_2', 0);
		$iHeightMin2 = $this->okt->request->request->getInt($this->sFormPrefix.'height_min_2', 0);
		$sResizeTypeMin2 = $this->okt->request->request->get($this->sFormPrefix.'resize_type_min_2', 'ratio');

		$iWidthMin3 = $this->okt->request->request->getInt($this->sFormPrefix.'width_min_3', 0);
		$iHeightMin3 = $this->okt->request->request->getInt($this->sFormPrefix.'height_min_3', 0);
		$sResizeTypeMin3 = $this->okt->request->request->get($this->sFormPrefix.'resize_type_min_3', 'ratio');

		$iWidthMin4 = $this->okt->request->request->getInt($this->sFormPrefix.'width_min_4', 0);
		$iHeightMin4 = $this->okt->request->request->getInt($this->sFormPrefix.'height_min_4', 0);
		$sResizeTypeMin4 = $this->okt->request->request->get($this->sFormPrefix.'resize_type_min_4', 'ratio');

		$iWidthMin5 = $this->okt->request->request->getInt($this->sFormPrefix.'width_min_5', 0);
		$iHeightMin5 = $this->okt->request->request->getInt($this->sFormPrefix.'height_min_5', 0);
		$sResizeTypeMin5 = $this->okt->request->request->get($this->sFormPrefix.'resize_type_min_5', 'ratio');

		$sSquareSize = $this->okt->request->request->getInt($this->sFormPrefix.'square_size', 0);

		if ($this->bWithWatermark)
		{
			$sUploadedWatermarkFile = $this->oImageUpload->uploadWatermark($this->sFormPrefix.'watermark_file', 'watermark');
			$sWatermarkFile = ($sUploadedWatermarkFile != '' ? $sUploadedWatermarkFile : $this->oImageUpload->aConfig['watermark_file']);

			$sWatermarkPosition = $this->okt->request->request->get($this->sFormPrefix.'watermark_position', 'cc');
		}
		else {
			$sWatermarkFile = '';
			$sWatermarkPosition = 'cc';
		}

		return array(
			'enable' => (boolean)$bEnableImages,
			'number' => (integer)$iNumberImages,

			'width' => (integer)$iWidth,
			'height' => (integer)$iHeight,
			'resize_type' => $sResizeType,

			'width_min' => (integer)$iWidthMin,
			'height_min' => (integer)$iHeightMin,
			'resize_type_min' => $sResizeTypeMin,

			'width_min_2' => (integer)$iWidthMin2,
			'height_min_2' => (integer)$iHeightMin2,
			'resize_type_min_2' => $sResizeTypeMin2,

			'width_min_3' => (integer)$iWidthMin3,
			'height_min_3' => (integer)$iHeightMin3,
			'resize_type_min_3' => $sResizeTypeMin3,

			'width_min_4' => (integer)$iWidthMin4,
			'height_min_4' => (integer)$iHeightMin4,
			'resize_type_min_4' => $sResizeTypeMin4,

			'width_min_5' => (integer)$iWidthMin5,
			'height_min_5' => (integer)$iHeightMin5,
			'resize_type_min_5' => $sResizeTypeMin5,

			'square_size' => (integer)$sSquareSize,

			'watermark_file' => $sWatermarkFile,
			'watermark_position' => $sWatermarkPosition
		);
	}

	/**
	 * Remove watermak file and return new image config array
	 *
	 * @return array
	 */
	public function removeWatermak()
	{
		$sWatermarkPath = $this->oImageUpload->getWatermarkUploadDir();

		if (\files::isDeletable($sWatermarkPath.$this->oImageUpload->aConfig['watermark_file'])) {
			unlink($sWatermarkPath.$this->oImageUpload->aConfig['watermark_file']);
		}

		if (\files::isDeletable($sWatermarkPath.'min-'.$this->oImageUpload->aConfig['watermark_file'])) {
			unlink($sWatermarkPath.'min-'.$this->oImageUpload->aConfig['watermark_file']);
		}

		if (\files::isDeletable($sWatermarkPath.'min2-'.$this->oImageUpload->aConfig['watermark_file'])) {
			unlink($sWatermarkPath.'min2-'.$this->oImageUpload->aConfig['watermark_file']);
		}

		if (\files::isDeletable($sWatermarkPath.'sq-'.$this->oImageUpload->aConfig['watermark_file'])) {
			unlink($sWatermarkPath.'sq-'.$this->oImageUpload->aConfig['watermark_file']);
		}

		return array_merge($this->oImageUpload->aConfig, array('watermark_file' => ''));
	}

	/**
	 * Retourne le formulaire de configuration de l'upload d'images.
	 *
	 * @return string HTML du formulaire
	 */
	public function getForm()
	{
		$return = '';

		# configuration de base
		$return .= $this->getConfigFormBase();

		# redimensionnement
		$return .= $this->getConfigFormResize();

		# miniature
		$return .= $this->getConfigFormThumbnails();

		# filigrane
		if ($this->bWithWatermark) {
			$return .= $this->getConfigFormWatermark();
		}

		return $return;
	}

	/**
	 * Retourne le formulaire de configuration de base.
	 *
	 * @return string
	 */
	protected function getConfigFormBase()
	{
		$return =
		'<fieldset>'.
			'<legend>'.__('a_image_config_legend_base').'</legend>'.

			'<div class="three-cols">'.
				'<p class="field col"><label for="'.$this->sFormPrefix.'enable_images">'.form::checkbox($this->sFormPrefix.'enable_images',1,$this->oImageUpload->aConfig['enable']).
				__('a_image_config_enable').'</label></p>';

				if (!$this->bUnique)
				{
					$return .=
					'<p class="field col"><label for="'.$this->sFormPrefix.'number_images">'.__('a_image_config_number').'</label>'.
					form::text($this->sFormPrefix.'number_images', 10, 255, $this->oImageUpload->aConfig['number']).
					(!empty($this->iMaxFileUploads) ? '<span class="note">'.sprintf(__('a_image_config_number_note_%s').'</span>',$this->iMaxFileUploads) : '').
					'</p>';
				}

				$return .=
				'<p class="col"><a href="'.$this->sBaseUrl.'minregen=1" class="icon arrow_refresh_small lazy-load">'.
				__('a_image_config_regenerate_thumbnails').'</a></p>'.
			'</div>'.

		'</fieldset>';

		return $return;
	}

	/**
	 * Retourne le formulaire de configuration de redimensionnement.
	 *
	 * @return string
	 */
	protected function getConfigFormResize()
	{
		return
		'<fieldset>'.
			'<legend>'.__('a_image_config_legend_resize').'</legend>'.

			'<div class="three-cols">'.
				'<p class="field col"><label for="'.$this->sFormPrefix.'width">'.__('a_image_config_maximum_width').'</label>'.
				form::text($this->sFormPrefix.'width', 10, 255, $this->oImageUpload->aConfig['width']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'height">'.__('a_image_config_maximum_height').'</label>'.
				form::text($this->sFormPrefix.'height', 10, 255, $this->oImageUpload->aConfig['height']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'resize_type">'.__('a_image_config_type_resize').'</label>'.
				form::select($this->sFormPrefix.'resize_type', self::getResizeTypes(), $this->oImageUpload->aConfig['resize_type']).'</p>'.

			'</div>'.

			'<p class="note">'.__('a_image_config_disable_resize').'</p>'.

		'</fieldset>';
	}

	/**
	 * Retourne le formulaire de configuration des miniatures.
	 *
	 * @return string
	 */
	protected function getConfigFormThumbnails()
	{
		return
		'<fieldset>'.
			'<legend>'.__('a_image_config_legend_thumbnails').'</legend>'.

			'<div class="three-cols">'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'width_min">'.__('a_image_config_thumbnails_width').'</label>'.
				form::text($this->sFormPrefix.'width_min', 10, 255, $this->oImageUpload->aConfig['width_min']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'height_min">'.__('a_image_config_thumbnails_height').'</label>'.
				form::text($this->sFormPrefix.'height_min', 10, 255, $this->oImageUpload->aConfig['height_min']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'resize_type_min">'.__('a_image_config_type_resize').'</label>'.
				form::select($this->sFormPrefix.'resize_type_min', self::getResizeTypes(), $this->oImageUpload->aConfig['resize_type_min']).'</p>'.

			'</div>'.

			'<div class="three-cols">'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'width_min_2">'.__('a_image_config_thumbnails_width_2').'</label>'.
				form::text($this->sFormPrefix.'width_min_2', 10, 255, $this->oImageUpload->aConfig['width_min_2']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'height_min_2">'.__('a_image_config_thumbnails_height_2').'</label>'.
				form::text($this->sFormPrefix.'height_min_2', 10, 255, $this->oImageUpload->aConfig['height_min_2']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'resize_type_min_2">'.__('a_image_config_type_resize').'</label>'.
				form::select($this->sFormPrefix.'resize_type_min_2', self::getResizeTypes(), $this->oImageUpload->aConfig['resize_type_min_2']).'</p>'.

			'</div>'.

			'<div class="three-cols">'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'width_min_3">'.__('a_image_config_thumbnails_width_3').'</label>'.
				form::text($this->sFormPrefix.'width_min_3', 10, 255, $this->oImageUpload->aConfig['width_min_3']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'height_min_3">'.__('a_image_config_thumbnails_height_3').'</label>'.
				form::text($this->sFormPrefix.'height_min_3', 10, 255, $this->oImageUpload->aConfig['height_min_3']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'resize_type_min_3">'.__('a_image_config_type_resize').'</label>'.
				form::select($this->sFormPrefix.'resize_type_min_3', self::getResizeTypes(), $this->oImageUpload->aConfig['resize_type_min_3']).'</p>'.

			'</div>'.

			'<div class="three-cols">'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'width_min_4">'.__('a_image_config_thumbnails_width_4').'</label>'.
				form::text($this->sFormPrefix.'width_min_4', 10, 255, $this->oImageUpload->aConfig['width_min_4']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'height_min_4">'.__('a_image_config_thumbnails_height_4').'</label>'.
				form::text($this->sFormPrefix.'height_min_4', 10, 255, $this->oImageUpload->aConfig['height_min_4']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'resize_type_min_4">'.__('a_image_config_type_resize').'</label>'.
				form::select($this->sFormPrefix.'resize_type_min_4', self::getResizeTypes(), $this->oImageUpload->aConfig['resize_type_min_4']).'</p>'.

			'</div>'.

			'<div class="three-cols">'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'width_min_5">'.__('a_image_config_thumbnails_width_5').'</label>'.
				form::text($this->sFormPrefix.'width_min_5', 10, 255, $this->oImageUpload->aConfig['width_min_5']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'height_min_5">'.__('a_image_config_thumbnails_height_5').'</label>'.
				form::text($this->sFormPrefix.'height_min_5', 10, 255, $this->oImageUpload->aConfig['height_min_5']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'resize_type_min_5">'.__('a_image_config_type_resize').'</label>'.
				form::select($this->sFormPrefix.'resize_type_min_5', self::getResizeTypes(), $this->oImageUpload->aConfig['resize_type_min_5']).'</p>'.

			'</div>'.

			'<p class="field"><label for="'.$this->sFormPrefix.'square_size">'.__('a_image_config_dimensions_square').'</label>'.
			form::text($this->sFormPrefix.'square_size', 10, 255, $this->oImageUpload->aConfig['square_size']).' '.__('c_c_unit_px_s').'</p>'.

			'<p class="note">'.__('a_image_config_disable_min_resize').'</p>'.

		'</fieldset>';
	}

	/**
	 * Retourne le formulaire de configuration du filigrane.
	 *
	 * @return string
	 */
	protected function getConfigFormWatermark()
	{
		$return =
		'<fieldset>'.
			'<legend>'.__('a_image_config_legend_watermark').'</legend>'.

			'<div class="two-cols">'.
				'<div class="col">'.
					'<p class="field"><label for="'.$this->sFormPrefix.'watermark_file">'.__('a_image_config_watermark_image').'</label>'.
					form::file($this->sFormPrefix.'watermark_file').'</p>';

					if (is_file($this->oImageUpload->getWatermarkUploadDir().$this->oImageUpload->aConfig['watermark_file']))
					{
						$return .=
						'<p><img src="'.$this->oImageUpload->getWatermarkUploadUrl().$this->oImageUpload->aConfig['watermark_file'].'" alt="" '.
						'style="background: transparent url('.$this->okt->options->public_url.'/img/admin/bg-transparency-symbol.png) repeat 0 0" /></p>'.

						'<p><a href="'.$this->sBaseUrl.'delete_watermark=1" '.
						'onclick="return window.confirm(\''.\html::escapeJS(__('a_image_config_watermark_confirm')).'\')" '.
						'class="icon delete">'.__('a_image_config_watermark_delete').'</a></p>';
					}

				$return .= '</div>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'watermark_position">'.__('a_image_config_watermark_position').'</label>'.
				form::select($this->sFormPrefix.'watermark_position', self::getWatermarkPositions(), $this->oImageUpload->aConfig['watermark_position']).'</p>'.
			'</div>'.

		'</fieldset>';

		return $return;
	}

	/**
	 * Retourne les types de redimensionnement.
	 *
	 * @param boolean $bFlip
	 * @return array
	 */
	public static function getResizeTypes($bFlip=false)
	{
		$aResizeTypes = array(
			__('c_c_resize_proportional') => 'ratio',
			__('c_c_resize_crop') => 'crop'
		);

		if ($bFlip) {
			$aResizeTypes = array_flip($aResizeTypes);
		}

		return $aResizeTypes;
	}

	/**
	 * Retourne les types de positionnement du filigrane.
	 *
	 * @param boolean $bFlip
	 * @return array
	 */
	public static function getWatermarkPositions($bFlip=false)
	{
		$aPositions = array(
			__('c_c_direction_center').' '.__('c_c_direction_center') => 'cc',
			__('c_c_direction_top').' '.__('c_c_direction_center') => 'lt',
			__('c_c_direction_top').' '.__('c_c_direction_right') => 'rt',
			__('c_c_direction_bottom').' '.__('c_c_direction_left') => 'lb',
			__('c_c_direction_bottom').' '.__('c_c_direction_right') => 'rb',
			__('c_c_direction_bottom').' '.__('c_c_direction_center') => 'cb'
		);

		if ($bFlip) {
			$aPositions = array_flip($aPositions);
		}

		return $aPositions;
	}
}
