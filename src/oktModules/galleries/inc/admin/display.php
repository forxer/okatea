<?php
/**
 * @ingroup okt_module_galleries
 * @brief La page de configuration de l'affichage
 *
 */


# Accès direct interdit
if (!defined('ON_GALLERIES_MODULE')) die;


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_dysplay_clic_gal_image = !empty($_POST['p_dysplay_clic_gal_image']) ? $_POST['p_dysplay_clic_gal_image'] : '';
	$p_dysplay_clic_items_image = !empty($_POST['p_dysplay_clic_items_image']) ? $_POST['p_dysplay_clic_items_image'] : '';
	$p_lightbox_type = !empty($_POST['p_lightbox_type']) ? $_POST['p_lightbox_type'] : '';

	# Skitter
	$p_velocity = !empty($_POST['p_velocity']) ? $_POST['p_velocity'] : 1;
	$p_interval = !empty($_POST['p_interval']) ? intval($_POST['p_interval']) : 2500;
	$p_animation = !empty($_POST['p_animation']) ? $_POST['p_animation'] : 'random';
	$p_numbers = !empty($_POST['p_navigation_type']) ? $_POST['p_navigation_type'] == 'numbers': true;
	$p_dots = !empty($_POST['p_navigation_type']) ? $_POST['p_navigation_type'] == 'dots': false;
	$p_thumbs = !empty($_POST['p_navigation_type']) ? $_POST['p_navigation_type'] == 'thumbs': false;
	$p_navigation = !empty($_POST['p_enable_navigation']) ? true : false;
	$p_label = !empty($_POST['p_label']) ? true : false;
	$p_hide_tools = !empty($_POST['p_hide_tools']) ? $_POST['p_hide_tools']: false;

	$p_number_out_bg_color = !empty($_POST['p_number_out_bg_color']) ? $_POST['p_number_out_bg_color'] : '333333';
	$p_number_out_color = !empty($_POST['p_number_out_color']) ? $_POST['p_number_out_color'] : 'FFFFFF';
	$p_number_over_bg_color = !empty($_POST['p_number_over_bg_color']) ? $_POST['p_number_over_bg_color'] : '000000';
	$p_number_over_color = !empty($_POST['p_number_over_color']) ? $_POST['p_number_over_color'] : 'FFFFFF';
	$p_number_active_bg_color = !empty($_POST['p_number_active_bg_color']) ? $_POST['p_number_active_bg_color'] : 'cc3333';
	$p_number_active_color = !empty($_POST['p_number_active_color']) ? $_POST['p_number_active_color'] : 'FFFFFF';

	# Galleria
	$p_autoplay = !empty($_POST['p_autoplay']) ? intval($_POST['p_autoplay']) : 0;
	$p_lightbox = !empty($_POST['p_lightbox']) ? true : false;
	$p_overlay_opacity = !empty($_POST['p_overlay_opacity']) ? floatval($_POST['p_overlay_opacity']) : 0.85;
	$p_overlay_background = !empty($_POST['p_overlay_background']) ? $_POST['p_overlay_background'] : '0b0b0b';
	$p_transition = !empty($_POST['p_transition']) ? $_POST['p_transition'] : 'slide';
	$p_transition_speed = !empty($_POST['p_transition_speed']) ? intval($_POST['p_transition_speed']) : 200;
	$p_show_info = !empty($_POST['p_show_info']) ? true : false;
	$p_show_imagenav = !empty($_POST['p_show_imagenav']) ? true : false;
	$p_show_counter = !empty($_POST['p_show_counter']) ? true : false;
	$p_width = !empty($_POST['p_width']) ? intval($_POST['p_width']) : 620;
	$p_height = !empty($_POST['p_height']) ? intval($_POST['p_height']) : 320;

	# Galleriffic
	$p_delay = !empty($_POST['p_delay']) ? intval($_POST['p_delay']) : 3000;
	$p_num_thumbs = !empty($_POST['p_num_thumbs']) ? intval($_POST['p_num_thumbs']) : 20;
	$p_max_pages_to_show = !empty($_POST['p_max_pages_to_show']) ? intval($_POST['p_max_pages_to_show']) : 7;
	$p_enable_top_pager = !empty($_POST['p_enable_top_pager']) ? true : false;
	$p_enable_bottom_pager = !empty($_POST['p_enable_bottom_pager']) ? true : false;
	$p_render_ss_controls = !empty($_POST['p_render_ss_controls']) ? true : false;
	$p_render_nav_controls = !empty($_POST['p_render_nav_controls']) ? true : false;
	$p_enable_keyboard_navigation = !empty($_POST['p_enable_keyboard_navigation']) ? true : false;
	$p_auto_start = !empty($_POST['p_auto_start']) ? true : false;
	$p_default_transition_duration = !empty($_POST['p_default_transition_duration']) ? intval($_POST['p_default_transition_duration']) : 1000;

	# Flexslider
	$p_animation = !empty($_POST['p_animation']) ? $_POST['p_animation'] : 'fade';
	$p_slideDirection = !empty($_POST['p_slideDirection']) ? $_POST['p_slideDirection'] : 'horizontal';
	$p_slideshow = !empty($_POST['p_slideshow']) ? true : false;
	$p_slideshowSpeed = !empty($_POST['p_slideshowSpeed']) ? intval($_POST['p_slideshowSpeed']) : 7000;
	$p_animationDuration = !empty($_POST['p_animationDuration']) ? intval($_POST['p_animationDuration']) : 600;
	$p_directionNav = !empty($_POST['p_directionNav']) ? true : false;
	$p_controlNav = !empty($_POST['p_controlNav']) ? true : false;
	$p_keyboardNav = !empty($_POST['p_keyboardNav']) ? true : false;
	$p_mousewheel = !empty($_POST['p_mousewheel']) ? true : false;
	$p_pausePlay = !empty($_POST['p_pausePlay']) ? true : false;
	$p_randomize = !empty($_POST['p_randomize']) ? true : false;
	$p_slideToStart = !empty($_POST['p_slideToStart']) ? intval($_POST['p_slideToStart']) : 0;
	$p_animationLoop = !empty($_POST['p_animationLoop']) ? true : false;
	$p_pauseOnAction = !empty($_POST['p_pauseOnAction']) ? true : false;
	$p_pauseOnHover = !empty($_POST['p_pauseOnHover']) ? true : false;

	# BxSlider
	$p_bxslider_mode = !empty($_POST['p_bxslider_mode']) ? $_POST['p_bxslider_mode'] : 'horizontal';
	$p_bxslider_speed = !empty($_POST['p_bxslider_speed']) ? intval($_POST['p_bxslider_speed']) : 500;
	$p_bxslider_infiniteLoop = !empty($_POST['p_bxslider_infiniteLoop']) ? true : false;
	$p_bxslider_controls = !empty($_POST['p_bxslider_controls']) ? true : false;
	$p_bxslider_startingSlide = !empty($_POST['p_bxslider_startingSlide']) ? intval($_POST['p_bxslider_startingSlide']) : 0;
	$p_bxslider_randomStart = !empty($_POST['p_bxslider_randomStart']) ? true : false;
	$p_bxslider_hideControlOnEnd = !empty($_POST['p_bxslider_hideControlOnEnd']) ? true : false;
	$p_bxslider_captions = !empty($_POST['p_bxslider_captions']) ? true : false;
	$p_bxslider_easing = !empty($_POST['p_bxslider_easing']) ? $_POST['p_bxslider_easing'] : '';
	$p_bxslider_auto = !empty($_POST['p_bxslider_auto']) ? true : false;
	$p_bxslider_pause = !empty($_POST['p_bxslider_pause']) ? intval($_POST['p_bxslider_pause']) : 3000;
	$p_bxslider_autoControls = !empty($_POST['p_bxslider_autoControls']) ? true : false;
	$p_bxslider_autoDelay = !empty($_POST['p_bxslider_autoDelay']) ? intval($_POST['p_bxslider_autoDelay']) : 0;
	$p_bxslider_autoDirection = !empty($_POST['p_bxslider_autoDirection']) ? $_POST['p_bxslider_autoDirection'] : 'next';
	$p_bxslider_autoHover = !empty($_POST['p_bxslider_autoHover']) ? true : false;
	$p_bxslider_autoStart = !empty($_POST['p_bxslider_autoStart']) ? true : false;
	$p_bxslider_pager = !empty($_POST['p_bxslider_pager']) ? true : false;
	$p_bxslider_pagerType = !empty($_POST['p_bxslider_pagerType']) ? $_POST['p_bxslider_pagerType'] : 'full';
	$p_bxslider_pagerLocation = !empty($_POST['p_bxslider_pagerLocation']) ? $_POST['p_bxslider_pagerLocation'] : 'bottom';
	$p_bxslider_pagerShortSeparator = !empty($_POST['p_bxslider_pagerShortSeparator']) ? $_POST['p_bxslider_pagerShortSeparator'] : '/';
	$p_bxslider_displaySlideQty = !empty($_POST['p_bxslider_displaySlideQty']) ? intval($_POST['p_bxslider_displaySlideQty']) : 1;
	$p_bxslider_moveSlideQty = !empty($_POST['p_bxslider_moveSlideQty']) ? intval($_POST['p_bxslider_moveSlideQty']) : 1;
	$p_bxslider_ticker = !empty($_POST['p_bxslider_ticker']) ? true : false;
	$p_bxslider_tickerSpeed = !empty($_POST['p_bxslider_tickerSpeed']) ? intval($_POST['p_bxslider_tickerSpeed']) : 5000;
	$p_bxslider_tickerDirection  = !empty($_POST['p_bxslider_tickerDirection']) ? $_POST['p_bxslider_tickerDirection'] : 'next';
	$p_bxslider_tickerHover = !empty($_POST['p_bxslider_tickerHover']) ? true : false;


	if ($okt->error->isEmpty())
	{
		$new_conf = array(
				'dysplay_clic_gal_image' => $p_dysplay_clic_gal_image,
				'dysplay_clic_items_image' => $p_dysplay_clic_items_image,
				'lightbox_type' => $p_lightbox_type,

				'skitter_options' => array(
					'velocity' => $p_velocity,
					'interval' => $p_interval,
					'animation' => $p_animation,
					'numbers' => (boolean)$p_numbers,
					'dots' => (boolean)$p_dots,
					'thumbs' => (boolean)$p_thumbs,
					'navigation' => $p_navigation,
					'label' => $p_label,
					'hideTools' => $p_hide_tools,
					'animateNumberOut' => array(
						'backgroundColor' => '#'.$p_number_out_bg_color,
						'color' => '#'.$p_number_out_color
					),
					'animateNumberOver' => array(
						'backgroundColor' => '#'.$p_number_over_bg_color,
						'color' => '#'.$p_number_over_color
					),
					'animateNumberActive' => array(
						'backgroundColor' => '#'.$p_number_active_bg_color,
						'color' => '#'.$p_number_active_color
					)
				),

				'galleria_options' => array(
					'autoplay' => $p_autoplay,
					'lightbox' => $p_lightbox,
					'overlayOpacity' => $p_overlay_opacity,
					'overlayBackground' => '#'.$p_overlay_background,
					'transition' => $p_transition,
					'transitionSpeed' => $p_transition_speed,
					'showInfo' => $p_show_info,
					'showCounter' => $p_show_counter,
					'showImagenav' => $p_show_imagenav,
					'height' => $p_height,
					'width' => $p_width
				),

				'galleriffic_options' => array(
					'delay' => $p_delay,
					'numThumbs' => $p_num_thumbs,
					'enableTopPager' => $p_enable_top_pager,
					'enableBottomPager' => $p_enable_bottom_pager,
					'maxPagesToShow' => $p_max_pages_to_show,
					'renderSSControls' => $p_render_ss_controls,
					'renderNavControls' => $p_render_nav_controls,
					'enableKeyboardNavigation' => $p_enable_keyboard_navigation,
					'autoStart' => $p_auto_start,
					'defaultTransitionDuration' => $p_default_transition_duration
				),

				'flexslider_options' => array(
					'animation' => $p_animation,
					'slideDirection' => $p_slideDirection,
					'slideshow' => (boolean)$p_slideshow,
					'slideshowSpeed' => $p_slideshowSpeed,
					'animationDuration' => $p_animationDuration,
					'directionNav' => (boolean)$p_directionNav,
					'controlNav' => (boolean)$p_controlNav,
					'keyboardNav' => (boolean)$p_keyboardNav,
					'mousewheel' => (boolean)$p_mousewheel,
					'pausePlay' => (boolean)$p_pausePlay,
					'randomize' => (boolean)$p_randomize,
					'slideToStart' => (integer)$p_slideToStart,
					'animationLoop' => (boolean)$p_animationLoop,
					'pauseOnAction' => (boolean)$p_pauseOnAction,
					'pauseOnHover' => (boolean)$p_pauseOnHover
				),

				'bxslider_options' => array(
					'mode' => $p_bxslider_mode,
					'speed' => $p_bxslider_speed,
					'infiniteLoop' => $p_bxslider_infiniteLoop,
					'controls' => (boolean)$p_bxslider_controls,
					'startingSlide' => $p_bxslider_startingSlide,
					'randomStart' => (boolean)$p_bxslider_randomStart,
					'hideControlOnEnd' => (boolean)$p_bxslider_hideControlOnEnd,
					'captions' => (boolean)$p_bxslider_captions,
					'easing' => $p_bxslider_easing,
					'auto' => (boolean)$p_bxslider_auto,
					'pause' => $p_bxslider_pause,
					'autoControls' => (boolean)$p_bxslider_autoControls,
					'autoDelay' => $p_bxslider_autoDelay,
					'autoDirection' => $p_bxslider_autoDirection,
					'autoHover' => (boolean)$p_bxslider_autoHover ,
					'autoStart' => 	(boolean)$p_bxslider_autoStart,
					'pager' => 	(boolean)$p_bxslider_pager,
					'pagerType' => $p_bxslider_pagerType,
					'pagerLocation' => $p_bxslider_pagerLocation,
					'pagerShortSeparator' => $p_bxslider_pagerShortSeparator,
					'displaySlideQty' => $p_bxslider_displaySlideQty,
					'moveSlideQty' => $p_bxslider_moveSlideQty,
					'ticker' => 	(boolean)$p_bxslider_ticker,
					'tickerSpeed' => $p_bxslider_tickerSpeed,
					'tickerDirection' => $p_bxslider_tickerDirection,
					'tickerHover' => $p_bxslider_tickerHover
				)
		);

		try
		{
			$okt->galleries->config->write($new_conf);
			$okt->redirect('module.php?m=galleries&action=display&updated=1');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Liste des animations
$skitter_animation = array(
	'cube' => 'cube',
	'cubeRandom' => 'cubeRandom',
	'cubeStop' => 'cubeStop',
	'cubeHide' => 'cubeHide',
	'cubeSize' => 'cubeSize',
	'cubeStopRandom' => 'cubeStopRandom',
	'cubeSpread' => 'cubeSpread',
	'cubeJelly' => 'cubeJelly',
	'glassCube' => 'glassCube',
	'glassBlock' => 'glassBlock',
	'showBars' => 'showBars',
	'showBarsRandom' => 'showBarsRandom',
	'blind' => 'blind',
	'blindHeight' => 'blindHeight',
	'blindWidth' => 'blindWidth',
	'directionTop' => 'directionTop',
	'directionBottom' => 'directionBottom',
	'directionRight' => 'directionRight',
	'directionLeft' => 'directionLeft',
	'circles' => 'circles',
	'circlesInside' => 'circlesInside',
	'circlesRotate' => 'circlesRotate',
	'fade' => 'fade',
	'fadeFour' => 'fadeFour',
	'block' => 'block',
	'horizontal' => 'horizontal',
	'tube' => 'tube',
	'random' => 'random',
	'randomSmart' => 'randomSmart'
);

$galleria_animation = array(
	'fade' => 'fade',
	'flash' => 'flash',
	'pulse' => 'pulse',
	'slide' => 'slide',
	'fadeslide' => 'fadeslide'
);


$flexslider_animation = array(
	'fade' => 'fade',
	'slide' => 'slide'
);

$flexslider_direction = array(
	__('c_c_direction_horizontal') => 'horizontal',
	__('c_c_direction_vertical') => 'vertical'
);


$bxslider_mode = array(
	__('c_c_direction_horizontal') => 'horizontal',
	__('c_c_direction_vertical') => 'vertical',
	'fade' => 'fade'
);

$bxslider_easing = array(
	'&nbsp;' => null,
	'linear' => 'linear',
	'swing' => 'swing',
	'easeInQuad' => 'easeInQuad',
	'easeOutQuad' => 'easeOutQuad',
	'easeInOutQuad' => 'easeInOutQuad',
	'easeInCubic' => 'easeInCubic',
	'easeOutCubic' => 'easeOutCubic',
	'easeInOutCubic' => 'easeInOutCubic',
	'easeInQuart' => 'easeInQuart',
	'easeOutQuart' => 'easeOutQuart',
	'easeInOutQuart' => 'easeInOutQuart',
	'easeInQuint' => 'easeInQuint',
	'easeOutQuint' => 'easeOutQuint',
	'easeInOutQuint' => 'easeInOutQuint',
	'easeInSine' => 'easeInSine',
	'easeOutSine' => 'easeOutSine',
	'easeInOutSine' => 'easeInOutSine',
	'easeInExpo' => 'easeInExpo',
	'easeOutExpo' => 'easeOutExpo',
	'easeInOutExpo' => 'easeInOutExpo',
	'easeInCirc' => 'easeInCirc',
	'easeOutCirc' => 'easeOutCirc',
	'easeInOutCirc' => 'easeInOutCirc',
	'easeInElastic' => 'easeInElastic',
	'easeOutElastic' => 'easeOutElastic',
	'easeInOutElastic' => 'easeInOutElastic',
	'easeInBack' => 'easeInBack',
	'easeOutBack' => 'easeOutBack',
	'easeInOutBack' => 'easeInOutBack',
	'easeInBounce' => 'easeInBounce',
	'easeOutBounce' => 'easeOutBounce',
	'easeInOutBounce' => 'easeInOutBounce'
);

$bxslider_pagerType = array(
	'full' => 'full',
	'short' => 'short'
);

$bxslider_pagerLocation = array(
	__('c_c_direction_bottom') => 'bottom',
	__('c_c_direction_top') => 'top'
);

$bxslider_autoDirection = $bxslider_tickerDirection  = array(
	__('c_c_next') => 'next',
	__('c_c_previous') => 'prev'
);



# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_display'));


# Tabs
$okt->page->tabs();


# Modal
$okt->page->applyLbl($okt->galleries->config->lightbox_type);

# Confirmations
$okt->page->messages->success('updated',__('c_c_confirm_configuration_updated'));



# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_public"><span><?php _e('m_galleries_website_view') ?></span></a></li>
			<li><a href="#tab_images"><span><?php _e('Images')?></span></a></li>
		</ul>

		<div id="tab_public">
			<h3><?php _e('m_galleries_display_website') ?></h3>

			<p class="fake-label"><?php _e('m_galleries_image_gallery_list') ?></p>

			<ul class="checklist">
				<li><label><?php echo form::radio(array('p_dysplay_clic_gal_image'),'enter', $okt->galleries->config->dysplay_clic_gal_image == 'enter') ?>
				<?php _e('m_galleries_enter_gallery') ?></label></li>

				<li><label><?php echo form::radio(array('p_dysplay_clic_gal_image'),'image', $okt->galleries->config->dysplay_clic_gal_image == 'image') ?>
				<?php _e('m_galleries_extend_image_gallery') ?></label></li>
			</ul>

			<p class="fake-label"><?php _e('m_galleries_item_galleries') ?></p>

			<ul class="checklist">
				<li><label><?php echo form::radio(array('p_dysplay_clic_items_image'),'details', $okt->galleries->config->dysplay_clic_items_image == 'details') ?>
				<?php _e('m_galleries_show_iem_details') ?></label></li>

				<li><label><?php echo form::radio(array('p_dysplay_clic_items_image'),'image', $okt->galleries->config->dysplay_clic_items_image == 'image') ?>
				<?php _e('m_galleries_extend_image_item') ?></label></li>
			</ul>
		</div><!-- #tab_public -->

		<div id="tab_images">
			<h3><?php _e('m_galleries_display_images') ?></h3>

			<fieldset>
				<legend><?php _e('m_galleries_expansion_images') ?></legend>

				<?php if ($okt->page->hasLbl()) : ?>
				<p class="field"><label for="p_lightbox_type"><?php _e('m_galleries_choose_display') ?></label>
				<?php echo form::select('p_lightbox_type',array_merge(array(__('c_c_action_Disable')=>0),$okt->page->getLblList(true)),$okt->galleries->config->lightbox_type) ?></p>

				<p><?php _e('m_galleries_currently_used') ?> : <em><?php $aChoices = array_merge(array(''=>__('c_c_none_f')),$okt->page->getLblList());
				echo $aChoices[$okt->galleries->config->lightbox_type] ?></em></p>

				<?php else : ?>
				<p><span class="span_sprite ss_error"></span> <?php _e('m_galleries_no_interface_images')?>
				<?php echo form::hidden('p_lightbox_type',0); ?></p>
				<?php endif;?>

				<p class="modal-box">
					<a class="modal" rel="test_images" href="<?php echo OKT_COMMON_URL ?>/img/sample/chutes_la_nuit.jpg">
					<img width="60" height="60" alt="" src="<?php echo OKT_COMMON_URL ?>/img/sample/sq-chutes_la_nuit.jpg" /></a>

					<a class="modal" rel="test_images" href="<?php echo OKT_COMMON_URL ?>/img/sample/les_chutes.jpg">
					<img width="60" height="60" alt="" src="<?php echo OKT_COMMON_URL ?>/img/sample/sq-les_chutes.jpg" /></a>

					<a class="modal" rel="test_images" href="<?php echo OKT_COMMON_URL ?>/img/sample/chutes.jpg">
					<img width="60" height="60" alt="" src="<?php echo OKT_COMMON_URL ?>/img/sample/sq-chutes.jpg" /></a>
				</p>
			</fieldset>
		</div><!-- #tab_images -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','galleries'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'display'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save')?>" /></p>

</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
