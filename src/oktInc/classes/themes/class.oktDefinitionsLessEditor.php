<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class oktDefinitionsLessEditor
 * @ingroup okt_classes_themes
 * @brief Classe de l'éditeur de fichier de définitions LESS
 *
 */
class oktDefinitionsLessEditor
{
	protected $sPostPrefix;

	/**
	 * Constructor.
	 *
	 */
	public function __construct($okt, $sPostPrefix='p_')
	{
		$this->okt = $okt;

		# POST prefix
		$this->sPostPrefix = $sPostPrefix;

		# locales
		l10n::set(OKT_LOCALES_PATH.'/'.$this->okt->user->language.'/definitions.less.editor');
	}

	/**
	 * Ecrit un fichier avec les données envoyées en POST.
	 *
	 * @param string $sFilename
	 * @return void
	 */
	public function writeFileFromPost($sFilename)
	{
		if (!is_dir(dirname($sFilename))) {
			files::makeDir(dirname($sFilename), true);
		}

		$aValues = array_merge($this->getDefaultValues(), $this->getPostValues());

		$sFileContent = $this->getFileContent($aValues);

		file_put_contents($sFilename, $sFileContent);
	}

	/**
	 * Retourne le contenu du fichier.
	 *
	 * @param array $aValues Un tableau indexé de valeurs
	 * @return string
	 */
	public function getFileContent($aValues=array())
	{
		$aValues = array_merge($this->getDefaultValues(), $aValues);

		return "\n".

		'/* Définitions des couleurs du thème'."\n".
		'------------------------------------------------------------*/'."\n\n\n".


		'/* Couleurs de la charte : background */'."\n\n".

		'@graphics_body_background_color: '.$aValues['graphics_body_background_color'].';'."\n".
		'@graphics_main_background_color: '.$aValues['graphics_main_background_color'].';'."\n".
		'@graphics_footer_address_background_color: '.$aValues['graphics_footer_address_background_color'].';'."\n".
		'@graphics_footer_links_background_color: '.$aValues['graphics_footer_links_background_color'].';'."\n\n\n".


		'//* Couleurs de la charte : menus */'."\n\n".

		'@graphics_menu_link_color: '.$aValues['graphics_menu_link_color'].';'."\n".
		'@graphics_menu_link_hover_color: '.$aValues['graphics_menu_link_hover_color'].';'."\n".
		'@graphics_menu_background_color: '.$aValues['graphics_menu_background_color'].';'."\n".
		'@graphics_menu_background_hover_color: '.$aValues['graphics_menu_background_hover_color'].';'."\n\n\n".


		'//* Couleurs de la charte : sous menus */'."\n\n".

		'@graphics_sub_menu_link_color: '.$aValues['graphics_sub_menu_link_color'].';'."\n".
		'@graphics_sub_menu_link_hover_color: '.$aValues['graphics_sub_menu_link_hover_color'].';'."\n".
		'@graphics_sub_menu_background_color: '.$aValues['graphics_sub_menu_background_color'].';'."\n".
		'@graphics_sub_menu_background_hover_color: '.$aValues['graphics_sub_menu_background_hover_color'].';'."\n\n\n".


		'//* Couleurs de la charte : footer & divers */'."\n\n".

		'@graphics_footer_address: '.$aValues['graphics_footer_address'].';'."\n".
		'@graphics_footer_address_first_child: '.$aValues['graphics_footer_address_first_child'].';'."\n".
		'@graphics_footer_link: '.$aValues['graphics_footer_link'].';'."\n".
		'@graphics_rubric_title: '.$aValues['graphics_rubric_title'].';'."\n".
		'@graphics_text_shadow: '.$aValues['graphics_text_shadow'].';'."\n\n\n".


		'/* Couleurs des textes */'."\n\n".

		'@main_text_color: '.$aValues['main_text_color'].';'."\n".
		'@second_text_color: '.$aValues['second_text_color'].';'."\n".
		'@third_text_color: '.$aValues['third_text_color'].';'."\n".
		'@fourth_text_color: '.$aValues['fourth_text_color'].';'."\n\n\n".


		'/* Couleurs des arrières plans */'."\n\n".

		'@main_background_color: '.$aValues['main_background_color'].';'."\n".
		'@second_background_color: '.$aValues['second_background_color'].';'."\n".
		'@third_background_color: '.$aValues['third_background_color'].';'."\n".
		'@fourth_background_color: '.$aValues['fourth_background_color'].';'."\n\n\n".


		'/* Couleurs des bordures */'."\n\n".

		'@main_border_color: '.$aValues['main_border_color'].';'."\n".
		'@second_border_color: '.$aValues['second_border_color'].';'."\n".
		'@third_border_color: '.$aValues['third_border_color'].';'."\n".
		'@fourth_border_color: '.$aValues['fourth_border_color'].';'."\n\n\n".


		'/* Couleurs des titres */'."\n\n".

		'@main_title_color: '.$aValues['main_title_color'].';'."\n".
		'@second_title_color: '.$aValues['second_title_color'].';'."\n".
		'@third_title_color: '.$aValues['third_title_color'].';'."\n".
		'@fourth_title_color: '.$aValues['fourth_title_color'].';'."\n\n\n".


		'/* Éléments cliquables (liens, boutons, etc.) */'."\n\n".

		'@clickable_color: '.$aValues['clickable_color'].';'."\n".
		'@clickable_background_color: '.$aValues['clickable_background_color'].';'."\n".
		'@clickable_border_color: '.$aValues['clickable_border_color'].';'."\n\n".

		'@clickable_hover_color: '.$aValues['clickable_hover_color'].';'."\n".
		'@clickable_hover_background_color: '.$aValues['clickable_hover_background_color'].';'."\n".
		'@clickable_hover_border_color: '.$aValues['clickable_hover_border_color'].';'."\n\n".

		'@clickable_active_color: '.$aValues['clickable_active_color'].';'."\n".
		'@clickable_active_background_color: '.$aValues['clickable_active_background_color'].';'."\n".
		'@clickable_active_border_color: '.$aValues['clickable_active_border_color'].';'."\n\n\n".


		'/* Champs de formulaires */'."\n\n".

		'@input_color: '.$aValues['input_color'].';'."\n".
		'@input_background_color: '.$aValues['input_background_color'].';'."\n".
		'@input_border_color: '.$aValues['input_border_color'].';'."\n".
		'@input_border_radius: '.$aValues['input_border_radius'].';'."\n\n".

		"\n";
	}

	/**
	 * Retourne les valeurs par défaut.
	 *
	 * @return array
	 */
	public function getDefaultValues()
	{
		return array(
			'graphics_body_background_color' => '#fff',
			'graphics_main_background_color' => '#fff',
			'graphics_footer_address_background_color' => '#fff',
			'graphics_footer_links_background_color' => '#fff',

			'graphics_menu_link_color' => '#fff',
			'graphics_menu_link_hover_color' => '#fff',
			'graphics_menu_background_color' => '#fff',
			'graphics_menu_background_hover_color' => '#fff',

			'graphics_sub_menu_link_color' => '#fff',
			'graphics_sub_menu_link_hover_color' => '#fff',
			'graphics_sub_menu_background_color' => '#fff',
			'graphics_sub_menu_background_hover_color' => '#fff',

			'graphics_footer_address' => '#fff',
			'graphics_footer_address_first_child' => '#fff',
			'graphics_footer_link' => '#fff',
			'graphics_rubric_title' => '#fff',
			'graphics_text_shadow' => '#fff',

			'main_text_color' => '#333',
			'second_text_color' => '#666',
			'third_text_color' => '#999',
			'fourth_text_color' => '#fff',

			'main_background_color' => '#fff',
			'second_background_color' => '#999',
			'third_background_color' => '#666',
			'fourth_background_color' => '#333',

			'main_border_color' => '#1d5987',
			'second_border_color' => '#c5dbec',
			'third_border_color' => '#79b7e7',
			'fourth_border_color' => '#333',

			'main_title_color' => '#2e6e9e',
			'second_title_color' => '#2e6e9e',
			'third_title_color' => '#2e6e9e',
			'fourth_title_color' => '#2e6e9e',

			'clickable_color' => '#2e6e9e',
			'clickable_background_color' => '#dfeffc',
			'clickable_border_color' => '#c5dbec',

			'clickable_hover_color' => '#1d5987',
			'clickable_hover_background_color' => '#d0e5f5',
			'clickable_hover_border_color' => '#79b7e7',

			'clickable_active_color' => '#e17009',
			'clickable_active_background_color' => '#fff',
			'clickable_active_border_color' => '#79b7e7',

			'input_color' => '#000',
			'input_background_color' => 'transparent',
			'input_border_color' => '#c5dbec',
			'input_border_radius' => '6px',
		);
	}

	/**
	 * Définit les outils JS/CSS du formulaire.
	 *
	 * @param object $oPage
	 * @param string $sThemeId
	 * @return void
	 */
	public function setFormAssets($oPage, $sThemeId)
	{
		# Tableau de couleurs actuellement utilisées
		$aPaletteColors = self::getPaletteFromFileset(array(
			OKT_THEMES_PATH.'/'.$sThemeId.'/css/style.css',
			OKT_THEMES_PATH.'/'.$sThemeId.'/css/styles.css',
			OKT_THEMES_PATH.'/'.$sThemeId.'/css/definitions.less'
		));

		# Qtip
		$oPage->css->addFile(OKT_COMMON_URL.'/js-plugins/qtip/jquery.qtip.min.css');
		$oPage->js->addFile(OKT_COMMON_URL.'/js-plugins/qtip/jquery.qtip.min.js');

		# Color picker
		$oPage->css->addFile(OKT_COMMON_URL.'/js-plugins/spectrum/spectrum.css');
		$oPage->js->addFile(OKT_COMMON_URL.'/js-plugins/spectrum/spectrum.js');

		$oPage->js->addReady('
			$(".colorpicker").spectrum({

				preferredFormat: "hex",

				showInitial: true,
				showInput: true,
				showAlpha: false,

				cancelText: "'.html::escapeJS(__('c_c_action_cancel')).'",
				chooseText: "'.html::escapeJS(__('c_c_action_choose')).'",

				showPalette: true,
				showSelectionPalette: true,
				//maxPaletteSize: 5,
				palette: [ '.implode(',', array_map('json_encode', $aPaletteColors)).' ],
				localStorageKey: "spectrum.oktTeme.'.$sThemeId.'"

			})
			.show()
			.css("width","45%");
			$("label").qtip();
		');
	}

	/**
	 * Retourne le formulaire d'édition.
	 *
	 * @param array $aValues Un tableau indexé de valeurs
	 * @return string
	 */
	public function getHtmlFields($aValues=array(), $iHeadStart=3)
	{
		if (empty($aValues)) {
			$aValues = $this->getDefaultValues();
		}

		$sReturn =

		'<h'.$iHeadStart.'>'.__('c_a_def_less_editor_Graphics').'</h'.$iHeadStart.'>'.

		'<div class="four-cols">'.

			'<fieldset class="col">'.
				'<legend>'.__('c_a_def_less_editor_Main__graphics_colors').'</legend>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_body_background_color">* '.__('c_a_def_less_editor_body_background').'</label>'.
				form::text($this->sPostPrefix.'graphics_body_background_color', 7, 128, $aValues['graphics_body_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_main_background_color">'.__('c_a_def_less_editor_main_background').'</label>'.
				form::text($this->sPostPrefix.'graphics_main_background_color', 7, 128, $aValues['graphics_main_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_footer_address_background_color">* '.__('c_a_def_less_editor_footer_address_background').'</label>'.
				form::text($this->sPostPrefix.'graphics_footer_address_background_color', 7, 128, $aValues['graphics_footer_address_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_footer_links_background_color">'.__('c_a_def_less_editor_footer_links_background').'</label>'.
				form::text($this->sPostPrefix.'graphics_footer_links_background_color', 7, 128, $aValues['graphics_footer_links_background_color'], 'colorpicker').'</p>'.

			'</fieldset>'.

			'<fieldset class="col">'.
				'<legend>'.__('c_a_def_less_editor_Secondary_graphics_colors').'</legend>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_menu_link_color">* '.__('c_a_def_less_editor_menu_link').'</label>'.
				form::text($this->sPostPrefix.'graphics_menu_link_color', 7, 128, $aValues['graphics_menu_link_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_menu_link_hover_color">'.__('c_a_def_less_editor_menu_link_hover').'</label>'.
				form::text($this->sPostPrefix.'graphics_menu_link_hover_color', 7, 128, $aValues['graphics_menu_link_hover_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_menu_background_color">'.__('c_a_def_less_editor_menu_background').'</label>'.
				form::text($this->sPostPrefix.'graphics_menu_background_color', 7, 128, $aValues['graphics_menu_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_menu_background_hover_color">'.__('c_a_def_less_editor_menu_background_hover').'</label>'.
				form::text($this->sPostPrefix.'graphics_menu_background_hover_color', 7, 128, $aValues['graphics_menu_background_hover_color'], 'colorpicker').'</p>'.

			'</fieldset>'.

			'<fieldset class="col">'.
				'<legend>'.__('c_a_def_less_editor_Third_graphics_color').'</legend>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_sub_menu_link_color">'.__('c_a_def_less_editor_menu_link').'</label>'.
				form::text($this->sPostPrefix.'graphics_sub_menu_link_color', 7, 128, $aValues['graphics_sub_menu_link_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_sub_menu_link_hover_color">'.__('c_a_def_less_editor_menu_link_hover').'</label>'.
				form::text($this->sPostPrefix.'graphics_sub_menu_link_hover_color', 7, 128, $aValues['graphics_sub_menu_link_hover_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_sub_menu_background_color">'.__('c_a_def_less_editor_menu_background').'</label>'.
				form::text($this->sPostPrefix.'graphics_sub_menu_background_color', 7, 128, $aValues['graphics_sub_menu_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_sub_menu_background_hover_color">'.__('c_a_def_less_editor_menu_background_hover').'</label>'.
				form::text($this->sPostPrefix.'graphics_sub_menu_background_hover_color', 7, 128, $aValues['graphics_sub_menu_background_hover_color'], 'colorpicker').'</p>'.

			'</fieldset>'.

			'<fieldset class="col">'.
				'<legend>'.__('c_a_def_less_editor_Fourth_graphics_colors').'</legend>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_footer_address">'.__('c_a_def_less_editor_footer_address').'</label>'.
				form::text($this->sPostPrefix.'graphics_footer_address', 7, 128, $aValues['graphics_footer_address'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_footer_address_first_child">'.__('c_a_def_less_editor_footer_address_first_child').'</label>'.
				form::text($this->sPostPrefix.'graphics_footer_address_first_child', 7, 128, $aValues['graphics_footer_address_first_child'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_footer_link">'.__('c_a_def_less_editor_footer_link').'</label>'.
				form::text($this->sPostPrefix.'graphics_footer_link', 7, 128, $aValues['graphics_footer_link'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_rubric_title">* '.__('c_a_def_less_editor_rubric_title').'</label>'.
				form::text($this->sPostPrefix.'graphics_rubric_title', 7, 128, $aValues['graphics_rubric_title'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'graphics_text_shadow">'.__('c_a_def_less_editor_text_shadow').'</label>'.
				form::text($this->sPostPrefix.'graphics_text_shadow', 7, 128, $aValues['graphics_text_shadow'], 'colorpicker').'</p>'.

			'</fieldset>'.

		'</div>';

		$sReturn .=

		'<h'.$iHeadStart.'>'.__('c_a_def_less_editor_General').'</h'.$iHeadStart.'>'.

		'<div class="four-cols">'.

			'<fieldset class="col">'.
				'<legend>'.__('c_a_def_less_editor_Main_colors').'</legend>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'main_title_color" title="'.__('c_a_def_less_editor_Title_main_legend').'">* '.__('c_a_def_less_editor_Title').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'main_title_color', 7, 128, $aValues['main_title_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'main_text_color" title="'.__('c_a_def_less_editor_Text_main_legend').'">* '.__('c_a_def_less_editor_Text').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'main_text_color', 7, 128, $aValues['main_text_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'main_background_color">'.__('c_a_def_less_editor_Background').'</label>'.
				form::text($this->sPostPrefix.'main_background_color', 7, 128, $aValues['main_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'main_border_color">'.__('c_a_def_less_editor_Border').'</label>'.
				form::text($this->sPostPrefix.'main_border_color', 7, 128, $aValues['main_border_color'], 'colorpicker').'</p>'.

			'</fieldset>'.

			'<fieldset class="col">'.
				'<legend>'.__('c_a_def_less_editor_Secondary_colors').'</legend>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'second_title_color" title="'.__('c_a_def_less_editor_Title_second_legend').'">* '.__('c_a_def_less_editor_Title').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'second_title_color', 7, 128, $aValues['second_title_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'second_text_color" title="'.__('c_a_def_less_editor_Text_second_legend').'">* '.__('c_a_def_less_editor_Text').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'second_text_color', 7, 128, $aValues['second_text_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'second_background_color">'.__('c_a_def_less_editor_Background').'</label>'.
				form::text($this->sPostPrefix.'second_background_color', 7, 128, $aValues['second_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'second_border_color" title="'.__('c_a_def_less_editor_Border_second_legend').'">* '.__('c_a_def_less_editor_Border').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'second_border_color', 7, 128, $aValues['second_border_color'], 'colorpicker').'</p>'.

			'</fieldset>'.

			'<fieldset class="col">'.
				'<legend>'.__('c_a_def_less_editor_Third_color').'</legend>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'third_title_color">'.__('c_a_def_less_editor_Title').'</label>'.
				form::text($this->sPostPrefix.'third_title_color', 7, 128, $aValues['third_title_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'third_text_color">'.__('c_a_def_less_editor_Text').'</label>'.
				form::text($this->sPostPrefix.'third_text_color', 7, 128, $aValues['third_text_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'third_background_color">'.__('c_a_def_less_editor_Background').'</label>'.
				form::text($this->sPostPrefix.'third_background_color', 7, 128, $aValues['third_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'third_border_color" title="'.__('c_a_def_less_editor_Border_third_legend').'">* '.__('c_a_def_less_editor_Border').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'third_border_color', 7, 128, $aValues['third_border_color'], 'colorpicker').'</p>'.

			'</fieldset>'.

			'<fieldset class="col">'.
				'<legend>'.__('c_a_def_less_editor_Fourth_colors').'</legend>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'fourth_title_color">'.__('c_a_def_less_editor_Title').'</label>'.
				form::text($this->sPostPrefix.'fourth_title_color', 7, 128, $aValues['fourth_title_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'fourth_text_color" title="'.__('c_a_def_less_editor_Text_fourth_legend').'">'.__('c_a_def_less_editor_Text').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'fourth_text_color', 7, 128, $aValues['fourth_text_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'fourth_background_color" title="'.__('c_a_def_less_editor_Background_fourth_legend').'">'.__('c_a_def_less_editor_Background').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'fourth_background_color', 7, 128, $aValues['fourth_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'fourth_border_color" title="'.__('c_a_def_less_editor_Border_fourth_legend').'">'.__('c_a_def_less_editor_Border').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'fourth_border_color', 7, 128, $aValues['fourth_border_color'], 'colorpicker').'</p>'.

			'</fieldset>'.

		'</div>';


		$sReturn .=

		'<h'.$iHeadStart.'>'.__('c_a_def_less_editor_Clickable_elements').'</h'.$iHeadStart.'>'.

		'<div class="four-cols">'.
			'<fieldset class="col">'.
				'<legend>'.__('c_a_def_less_editor_Default').'</legend>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'clickable_color">* '.__('c_a_def_less_editor_Text').'</label>'.
				form::text($this->sPostPrefix.'clickable_color', 7, 128, $aValues['clickable_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'clickable_background_color" title="'.__('c_a_def_less_editor_Background_clickable_legend').'">* '.__('c_a_def_less_editor_Background').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'clickable_background_color', 7, 128, $aValues['clickable_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'clickable_border_color" title="'.__('c_a_def_less_editor_Border_clickable_default_legend').'">* '.__('c_a_def_less_editor_Border').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'clickable_border_color', 7, 128, $aValues['clickable_border_color'], 'colorpicker').'</p>'.

			'</fieldset>'.

			'<fieldset class="col">'.
				'<legend>'.__('c_a_def_less_editor_Hover').'</legend>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'clickable_hover_color">* '.__('c_a_def_less_editor_Text').'</label>'.
				form::text($this->sPostPrefix.'clickable_hover_color', 7, 128, $aValues['clickable_hover_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'clickable_hover_background_color" title="'.__('c_a_def_less_editor_Background_clickable_hover_legend').'">* '.__('c_a_def_less_editor_Background').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'clickable_hover_background_color', 7, 128, $aValues['clickable_hover_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'clickable_hover_border_color" title="'.__('c_a_def_less_editor_Border_clickable_hover_legend').'">* '.__('c_a_def_less_editor_Border').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'clickable_hover_border_color', 7, 128, $aValues['clickable_hover_border_color'], 'colorpicker').'</p>'.

			'</fieldset>'.

			'<fieldset class="col">'.
				'<legend>'.__('c_a_def_less_editor_Active').'</legend>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'clickable_active_color">* '.__('c_a_def_less_editor_Text').'</label>'.
				form::text($this->sPostPrefix.'clickable_active_color', 7, 128, $aValues['clickable_active_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'clickable_active_background_color" title="'.__('c_a_def_less_editor_Background_clickable_active_legend').'">* '.__('c_a_def_less_editor_Background').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'clickable_active_background_color', 7, 128, $aValues['clickable_active_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'clickable_active_border_color" title="'.__('c_a_def_less_editor_Border_clickable_active_legend').'">* '.__('c_a_def_less_editor_Border').' <img src="'.OKT_COMMON_URL.'/img/ico/help.png" alt="help" ></label>'.
				form::text($this->sPostPrefix.'clickable_active_border_color', 7, 128, $aValues['clickable_active_border_color'], 'colorpicker').'</p>'.

			'</fieldset>'.

			'<fieldset class="col">'.
				'<legend>'.__('c_a_def_less_editor_Forms_fields').'</legend>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'input_color">'.__('c_a_def_less_editor_Text').'</label>'.
				form::text($this->sPostPrefix.'input_color', 7, 128, $aValues['input_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'input_background_color">'.__('c_a_def_less_editor_Background').'</label>'.
				form::text($this->sPostPrefix.'input_background_color', 7, 128, $aValues['input_background_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'input_border_color">'.__('c_a_def_less_editor_Border').'</label>'.
				form::text($this->sPostPrefix.'input_border_color', 7, 128, $aValues['input_border_color'], 'colorpicker').'</p>'.

				'<p class="field"><label for="'.$this->sPostPrefix.'input_border_radius">'.__('c_a_def_less_editor_Border_radius').'</label>'.
				form::text($this->sPostPrefix.'input_border_radius', 7, 128, $aValues['input_border_radius'], '').'</p>'.

			'</fieldset>'.

		'</div>';

		return $sReturn;
	}

	public function getPostValues()
	{
		$aValues = array();
		foreach (array_keys($this->getDefaultValues()) as $sKey) {
			$aValues[$sKey] = !empty($_POST[$this->sPostPrefix.$sKey]) ?  $_POST[$this->sPostPrefix.$sKey] : '';
		}

		return $aValues;
	}

	public function getValuesFromFile($sFilename)
	{
		if (!file_exists($sFilename)) {
			return false;
		}

		$aFileContent = file($sFilename);


		$aValues = array();

		foreach ($aFileContent as $sLine)
		{
			if (preg_match('/^@(.*): (.*);$/', $sLine, $aMatches)) {
				$aValues[$aMatches[1]] = $aMatches[2];
			}
		}

		return $aValues;
	}

	public static function getPaletteFromFileset($aFileset)
	{
		$aPaletteColors = array();

		foreach ($aFileset as $sFilename)
		{
			if (file_exists($sFilename)) {
				$aPaletteColors[] = self::findColorsFromFile($sFilename);
			}
		}

		return $aPaletteColors;
	}

	public static function findColorsFromFile($sFilename)
	{
		$aFileContent = file($sFilename);

		$aColors = array();

		foreach ($aFileContent as $sLine)
		{
			if (preg_match_all('/#(?:(?:[a-fA-F0-9]{3}){1,2})/i', $sLine, $aMatches))
			{
				foreach ($aMatches[0] as $sColor) {
					$aColors[] = $sColor;
				}
			}
		}

		return array_values(array_unique($aColors));
	}

} # class
