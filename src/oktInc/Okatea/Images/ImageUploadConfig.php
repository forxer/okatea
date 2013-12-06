<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Images;

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
	protected $imageUpload;

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

		$this->imageUpload = $oImageUpload;

		\l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.images.config');

		if (defined('OKT_MAX_FILE_UPLOADS')) {
			$this->iMaxFileUploads = OKT_MAX_FILE_UPLOADS;
		}
		else {
			$this->iMaxFileUploads = ini_get('max_file_uploads');
		}
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
		$enable_images = !empty($_POST[$this->sFormPrefix.'enable_images']) ? true : false;

		if ($this->bUnique) {
			$number_images = 1;
		}
		else {
			$number_images = !empty($_POST[$this->sFormPrefix.'number_images']) ? intval($_POST[$this->sFormPrefix.'number_images']) : 0;

			if (!empty($this->iMaxFileUploads) && $number_images > $this->iMaxFileUploads) {
				$number_images = $this->iMaxFileUploads;
			}
		}

		$width = !empty($_POST[$this->sFormPrefix.'width']) ? intval($_POST[$this->sFormPrefix.'width']) : 0;
		$height = !empty($_POST[$this->sFormPrefix.'height']) ? intval($_POST[$this->sFormPrefix.'height']) : 0;
		$resize_type = !empty($_POST[$this->sFormPrefix.'resize_type']) ? $_POST[$this->sFormPrefix.'resize_type'] : 'ratio';

		$width_min = !empty($_POST[$this->sFormPrefix.'width_min']) ? intval($_POST[$this->sFormPrefix.'width_min']) : 0;
		$height_min = !empty($_POST[$this->sFormPrefix.'height_min']) ? intval($_POST[$this->sFormPrefix.'height_min']) : 0;
		$resize_type_min = !empty($_POST[$this->sFormPrefix.'resize_type_min']) ? $_POST[$this->sFormPrefix.'resize_type_min'] : 'ratio';

		$width_min_2 = !empty($_POST[$this->sFormPrefix.'width_min_2']) ? intval($_POST[$this->sFormPrefix.'width_min_2']) : 0;
		$height_min_2 = !empty($_POST[$this->sFormPrefix.'height_min_2']) ? intval($_POST[$this->sFormPrefix.'height_min_2']) : 0;
		$resize_type_min_2 = !empty($_POST[$this->sFormPrefix.'resize_type_min_2']) ? $_POST[$this->sFormPrefix.'resize_type_min_2'] : 'ratio';

		$width_min_3 = !empty($_POST[$this->sFormPrefix.'width_min_3']) ? intval($_POST[$this->sFormPrefix.'width_min_3']) : 0;
		$height_min_3 = !empty($_POST[$this->sFormPrefix.'height_min_3']) ? intval($_POST[$this->sFormPrefix.'height_min_3']) : 0;
		$resize_type_min_3 = !empty($_POST[$this->sFormPrefix.'resize_type_min_3']) ? $_POST[$this->sFormPrefix.'resize_type_min_3'] : 'ratio';

		$width_min_4 = !empty($_POST[$this->sFormPrefix.'width_min_4']) ? intval($_POST[$this->sFormPrefix.'width_min_4']) : 0;
		$height_min_4 = !empty($_POST[$this->sFormPrefix.'height_min_4']) ? intval($_POST[$this->sFormPrefix.'height_min_4']) : 0;
		$resize_type_min_4 = !empty($_POST[$this->sFormPrefix.'resize_type_min_4']) ? $_POST[$this->sFormPrefix.'resize_type_min_4'] : 'ratio';

		$width_min_5 = !empty($_POST[$this->sFormPrefix.'width_min_5']) ? intval($_POST[$this->sFormPrefix.'width_min_5']) : 0;
		$height_min_5 = !empty($_POST[$this->sFormPrefix.'height_min_5']) ? intval($_POST[$this->sFormPrefix.'height_min_5']) : 0;
		$resize_type_min_5 = !empty($_POST[$this->sFormPrefix.'resize_type_min_5']) ? $_POST[$this->sFormPrefix.'resize_type_min_5'] : 'ratio';

		$square_size = !empty($_POST[$this->sFormPrefix.'square_size']) ? intval($_POST[$this->sFormPrefix.'square_size']) : 0;

		if ($this->bWithWatermark)
		{
			$sUploadedWatermarkFile = $this->imageUpload->uploadWatermark($this->sFormPrefix.'watermark_file','watermark');
			$watermark_file = ($sUploadedWatermarkFile != '' ? $sUploadedWatermarkFile : $this->imageUpload->aConfig['watermark_file']);

			$watermark_position = !empty($_POST[$this->sFormPrefix.'watermark_position']) ? $_POST[$this->sFormPrefix.'watermark_position'] : 'cc';
		}
		else {
			$watermark_file = '';
			$watermark_position = 'cc';
		}

		return array(
			'enable' => (boolean)$enable_images,
			'number' => (integer)$number_images,

			'width' => (integer)$width,
			'height' => (integer)$height,
			'resize_type' => $resize_type,

			'width_min' => (integer)$width_min,
			'height_min' => (integer)$height_min,
			'resize_type_min' => $resize_type_min,

			'width_min_2' => (integer)$width_min_2,
			'height_min_2' => (integer)$height_min_2,
			'resize_type_min_2' => $resize_type_min_2,

			'width_min_3' => (integer)$width_min_3,
			'height_min_3' => (integer)$height_min_3,
			'resize_type_min_3' => $resize_type_min_3,

			'width_min_4' => (integer)$width_min_4,
			'height_min_4' => (integer)$height_min_4,
			'resize_type_min_4' => $resize_type_min_4,

			'width_min_5' => (integer)$width_min_5,
			'height_min_5' => (integer)$height_min_5,
			'resize_type_min_5' => $resize_type_min_5,

			'square_size' => (integer)$square_size,

			'watermark_file' => $watermark_file,
			'watermark_position' => $watermark_position
		);
	}

	/**
	 * Remove watermak file and return new image config array
	 *
	 * @return array
	 */
	public function removeWatermak()
	{
		$sWatermarkPath = $this->imageUpload->getWatermarkUploadDir();

		if (\files::isDeletable($sWatermarkPath.$this->imageUpload->aConfig['watermark_file'])) {
			unlink($sWatermarkPath.$this->imageUpload->aConfig['watermark_file']);
		}

		if (\files::isDeletable($sWatermarkPath.'min-'.$this->imageUpload->aConfig['watermark_file'])) {
			unlink($sWatermarkPath.'min-'.$this->imageUpload->aConfig['watermark_file']);
		}

		if (\files::isDeletable($sWatermarkPath.'min2-'.$this->imageUpload->aConfig['watermark_file'])) {
			unlink($sWatermarkPath.'min2-'.$this->imageUpload->aConfig['watermark_file']);
		}

		if (\files::isDeletable($sWatermarkPath.'sq-'.$this->imageUpload->aConfig['watermark_file'])) {
			unlink($sWatermarkPath.'sq-'.$this->imageUpload->aConfig['watermark_file']);
		}

		return array_merge($this->imageUpload->aConfig,array('watermark_file' => ''));
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

	protected function getConfigFormBase()
	{
		$return =
		'<fieldset>'.
			'<legend>'.__('a_image_config_legend_base').'</legend>'.

			'<div class="three-cols">'.
				'<p class="field col"><label for="'.$this->sFormPrefix.'enable_images">'.\form::checkbox($this->sFormPrefix.'enable_images',1,$this->imageUpload->aConfig['enable']).
				__('a_image_config_enable').'</label></p>';

				if (!$this->bUnique)
				{
					$return .=
					'<p class="field col"><label for="'.$this->sFormPrefix.'number_images">'.__('a_image_config_number').'</label>'.
					\form::text($this->sFormPrefix.'number_images', 10, 255, $this->imageUpload->aConfig['number']).
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

	protected function getConfigFormResize()
	{
		return
		'<fieldset>'.
			'<legend>'.__('a_image_config_legend_resize').'</legend>'.

			'<div class="three-cols">'.
				'<p class="field col"><label for="'.$this->sFormPrefix.'width">'.__('a_image_config_maximum_width').'</label>'.
				\form::text($this->sFormPrefix.'width', 10, 255, $this->imageUpload->aConfig['width']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'height">'.__('a_image_config_maximum_height').'</label>'.
				\form::text($this->sFormPrefix.'height', 10, 255, $this->imageUpload->aConfig['height']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'resize_type">'.__('a_image_config_type_resize').'</label>'.
				\form::select($this->sFormPrefix.'resize_type', self::getResizeTypes(), $this->imageUpload->aConfig['resize_type']).'</p>'.

			'</div>'.

			'<p class="note">'.__('a_image_config_disable_resize').'</p>'.

		'</fieldset>';
	}

	protected function getConfigFormThumbnails()
	{
		return
		'<fieldset>'.
			'<legend>'.__('a_image_config_legend_thumbnails').'</legend>'.

			'<div class="three-cols">'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'width_min">'.__('a_image_config_thumbnails_width').'</label>'.
				\form::text($this->sFormPrefix.'width_min', 10, 255, $this->imageUpload->aConfig['width_min']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'height_min">'.__('a_image_config_thumbnails_height').'</label>'.
				\form::text($this->sFormPrefix.'height_min', 10, 255, $this->imageUpload->aConfig['height_min']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'resize_type_min">'.__('a_image_config_type_resize').'</label>'.
				\form::select($this->sFormPrefix.'resize_type_min', self::getResizeTypes(), $this->imageUpload->aConfig['resize_type_min']).'</p>'.

			'</div>'.

			'<div class="three-cols">'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'width_min_2">'.__('a_image_config_thumbnails_width_2').'</label>'.
				\form::text($this->sFormPrefix.'width_min_2', 10, 255, $this->imageUpload->aConfig['width_min_2']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'height_min_2">'.__('a_image_config_thumbnails_height_2').'</label>'.
				\form::text($this->sFormPrefix.'height_min_2', 10, 255, $this->imageUpload->aConfig['height_min_2']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'resize_type_min_2">'.__('a_image_config_type_resize').'</label>'.
				\form::select($this->sFormPrefix.'resize_type_min_2', self::getResizeTypes(), $this->imageUpload->aConfig['resize_type_min_2']).'</p>'.

			'</div>'.

			'<div class="three-cols">'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'width_min_3">'.__('a_image_config_thumbnails_width_3').'</label>'.
				\form::text($this->sFormPrefix.'width_min_3', 10, 255, $this->imageUpload->aConfig['width_min_3']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'height_min_3">'.__('a_image_config_thumbnails_height_3').'</label>'.
				\form::text($this->sFormPrefix.'height_min_3', 10, 255, $this->imageUpload->aConfig['height_min_3']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'resize_type_min_3">'.__('a_image_config_type_resize').'</label>'.
				\form::select($this->sFormPrefix.'resize_type_min_3', self::getResizeTypes(), $this->imageUpload->aConfig['resize_type_min_3']).'</p>'.

			'</div>'.

			'<div class="three-cols">'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'width_min_4">'.__('a_image_config_thumbnails_width_4').'</label>'.
				\form::text($this->sFormPrefix.'width_min_4', 10, 255, $this->imageUpload->aConfig['width_min_4']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'height_min_4">'.__('a_image_config_thumbnails_height_4').'</label>'.
				\form::text($this->sFormPrefix.'height_min_4', 10, 255, $this->imageUpload->aConfig['height_min_4']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'resize_type_min_4">'.__('a_image_config_type_resize').'</label>'.
				\form::select($this->sFormPrefix.'resize_type_min_4', self::getResizeTypes(), $this->imageUpload->aConfig['resize_type_min_4']).'</p>'.

			'</div>'.

			'<div class="three-cols">'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'width_min_5">'.__('a_image_config_thumbnails_width_5').'</label>'.
				\form::text($this->sFormPrefix.'width_min_5', 10, 255, $this->imageUpload->aConfig['width_min_5']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'height_min_5">'.__('a_image_config_thumbnails_height_5').'</label>'.
				\form::text($this->sFormPrefix.'height_min_5', 10, 255, $this->imageUpload->aConfig['height_min_5']).' '.__('c_c_unit_px_s').'</p>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'resize_type_min_5">'.__('a_image_config_type_resize').'</label>'.
				\form::select($this->sFormPrefix.'resize_type_min_5', self::getResizeTypes(), $this->imageUpload->aConfig['resize_type_min_5']).'</p>'.

			'</div>'.

			'<p class="field"><label for="'.$this->sFormPrefix.'square_size">'.__('a_image_config_dimensions_square').'</label>'.
			\form::text($this->sFormPrefix.'square_size', 10, 255, $this->imageUpload->aConfig['square_size']).' '.__('c_c_unit_px_s').'</p>'.

			'<p class="note">'.__('a_image_config_disable_min_resize').'</p>'.

		'</fieldset>';
	}

	protected function getConfigFormWatermark()
	{
		$return =
		'<fieldset>'.
			'<legend>'.__('a_image_config_legend_watermark').'</legend>'.

			'<div class="two-cols">'.
				'<div class="col">'.
					'<p class="field"><label for="'.$this->sFormPrefix.'watermark_file">'.__('a_image_config_watermark_image').'</label>'.
					\form::file($this->sFormPrefix.'watermark_file').'</p>';

					if (is_file($this->imageUpload->getWatermarkUploadDir().$this->imageUpload->aConfig['watermark_file']))
					{
						$return .=
						'<p><img src="'.$this->imageUpload->getWatermarkUploadUrl().$this->imageUpload->aConfig['watermark_file'].'" alt="" '.
						'style="background: transparent url('.OKT_PUBLIC_URL.'/img/admin/bg-transparency-symbol.png) repeat 0 0" /></p>'.

						'<p><a href="'.$this->sBaseUrl.'delete_watermark=1" '.
						'onclick="return window.confirm(\''.\html::escapeJS(__('a_image_config_watermark_confirm')).'\')" '.
						'class="icon delete">'.__('a_image_config_watermark_delete').'</a></p>';
					}

				$return .= '</div>'.

				'<p class="field col"><label for="'.$this->sFormPrefix.'watermark_position">'.__('a_image_config_watermark_position').'</label>'.
				\form::select($this->sFormPrefix.'watermark_position', self::getWatermarkPositions(), $this->imageUpload->aConfig['watermark_position']).'</p>'.
			'</div>'.

		'</fieldset>';

		return $return;
	}

	public static function getResizeTypes($flip=false)
	{
		$aResizeTypes = array(
			__('c_c_resize_proportional') => 'ratio',
			__('c_c_resize_crop') => 'crop'
		);

		if ($flip) {
			$aResizeTypes = array_flip($aResizeTypes);
		}

		return $aResizeTypes;
	}

	public static function getWatermarkPositions($flip=false)
	{
		$positions = array(
			__('c_c_direction_center').' '.__('c_c_direction_center') => 'cc',
			__('c_c_direction_top').' '.__('c_c_direction_center') => 'lt',
			__('c_c_direction_top').' '.__('c_c_direction_right') => 'rt',
			__('c_c_direction_bottom').' '.__('c_c_direction_left') => 'lb',
			__('c_c_direction_bottom').' '.__('c_c_direction_right') => 'rb',
			__('c_c_direction_bottom').' '.__('c_c_direction_center') => 'cb'
		);

		if ($flip) {
			$positions = array_flip($positions);
		}

		return $positions;
	}

} # class
