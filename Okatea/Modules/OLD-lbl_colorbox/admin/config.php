<?php
/**
 * @ingroup okt_module_lbl_colorbox
 * @brief La page de configuration
 *
 */
use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (! defined('ON_MODULE'))
	die();
	
	/* Traitements
----------------------------------------------------------*/

if (! empty($_POST['form_sent']))
{
	$p_theme = ! empty($_POST['p_theme']) ? $_POST['p_theme'] : '';
	
	$p_loop = ! empty($_POST['p_loop']) ? true : false;
	$p_transition = ! empty($_POST['p_transition']) ? $_POST['p_transition'] : '';
	$p_speed = ! empty($_POST['p_speed']) ? $_POST['p_speed'] : '';
	
	$p_slideshow = ! empty($_POST['p_slideshow']) ? true : false;
	$p_slideshowauto = ! empty($_POST['p_slideshowauto']) ? true : false;
	$p_slideshowspeed = ! empty($_POST['p_slideshowspeed']) ? $_POST['p_slideshowspeed'] : '';
	
	if ($okt->error->isEmpty())
	{
		$aNewConf = array(
			'theme' => $p_theme,
			
			'loop' => (boolean) $p_loop,
			'transition' => $p_transition,
			'speed' => (integer) $p_speed,
			
			'slideshow' => (boolean) $p_slideshow,
			'slideshowauto' => (integer) $p_slideshowauto,
			'slideshowspeed' => (integer) $p_slideshowspeed
		);
		
		$okt->lbl_colorbox->config->write($aNewConf);
		
		$okt->flash->success(__('c_c_confirm_configuration_updated'));
		
		http::redirect('module.php?m=lbl_colorbox&action=config');
	}
}

/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_lbl_colorbox_config_title'));

$aThemes = array(
	__('m_lbl_colorbox_theme_1') => 1,
	__('m_lbl_colorbox_theme_3') => 3,
	__('m_lbl_colorbox_theme_2') => 2,
	__('m_lbl_colorbox_theme_4') => 4,
	__('m_lbl_colorbox_theme_5') => 5
);

$aTransitions = array(
	__('m_lbl_colorbox_transisition_elastic') => 'elastic',
	__('m_lbl_colorbox_transisition_fade') => 'fade',
	__('m_lbl_colorbox_transisition_none') => 'none'
);

# LightBox Like
$okt->page->applyLbl('colorbox');

# En-tête
require OKT_ADMIN_HEADER_FILE;
?>

<p class="modal-box">
	<a class="modal" rel="test_images"
		title="<?php printf(__('c_c_Example_%s'), 1) ?>"
		href="<?php echo $okt->options->public_url ?>/img/sample/chutes_la_nuit.jpg">
		<img width="60" height="60" alt=""
		src="<?php echo $okt->options->public_url ?>/img/sample/sq-chutes_la_nuit.jpg" />
	</a> <a class="modal" rel="test_images"
		title="<?php printf(__('c_c_Example_%s'), 2) ?>"
		href="<?php echo $okt->options->public_url ?>/img/sample/les_chutes.jpg">
		<img width="60" height="60" alt=""
		src="<?php echo $okt->options->public_url ?>/img/sample/sq-les_chutes.jpg" />
	</a> <a class="modal" rel="test_images"
		title="<?php printf(__('c_c_Example_%s'), 3) ?>"
		href="<?php echo $okt->options->public_url ?>/img/sample/chutes.jpg">
		<img width="60" height="60" alt=""
		src="<?php echo $okt->options->public_url ?>/img/sample/sq-chutes.jpg" />
	</a>
</p>

<form action="module.php" method="post">

	<fieldset>
		<legend><?php _e('m_lbl_colorbox_appearance') ?></legend>

		<p class="field">
			<label for="p_theme"><?php _e('m_lbl_colorbox_theme') ?></label>
		<?php echo form::select('p_theme',$aThemes,$okt->lbl_colorbox->config->theme)?></p>

	</fieldset>

	<fieldset>
		<legend><?php _e('m_lbl_colorbox_transitions') ?></legend>

		<div class="three-cols">
			<p class="field col">
				<label><?php echo form::checkbox('p_loop',1,$okt->lbl_colorbox->config->loop)?>
		<?php _e('m_lbl_colorbox_loop') ?></label>
			</p>

			<p class="field col">
				<label for="p_transition"><?php _e('m_lbl_colorbox_transitions_type') ?></label>
		<?php echo form::select('p_transition',$aTransitions,$okt->lbl_colorbox->config->transition)?></p>

			<p class="field col">
				<label for="p_speed"><?php _e('m_lbl_colorbox_transitions_speed') ?></label>
		<?php echo form::text('p_speed', 10, 255, html::escapeHTML($okt->lbl_colorbox->config->speed)) ?></p>

		</div>
	</fieldset>

	<fieldset>
		<legend><?php _e('m_lbl_colorbox_slideshow') ?></legend>

		<div class="three-cols">
			<p class="field col">
				<label><?php echo form::checkbox('p_slideshow',1,$okt->lbl_colorbox->config->slideshow)?>
		<?php _e('m_lbl_colorbox_enable_slideshow') ?></label>
			</p>

			<p class="field col">
				<label><?php echo form::checkbox('p_slideshowauto',1,$okt->lbl_colorbox->config->slideshowauto)?>
		<?php _e('m_lbl_colorbox_slideshow_autostart') ?></label>
			</p>

			<p class="field col">
				<label for="p_slideshowspeed"><?php _e('m_lbl_colorbox_slideshow_speed') ?></label>
		<?php echo form::text('p_slideshowspeed', 10, 255, html::escapeHTML($okt->lbl_colorbox->config->slideshowspeed)) ?></p>

		</div>
	</fieldset>

	<p><?php echo form::hidden('m','lbl_colorbox'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" />
	</p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
