<?php
/**
 * @ingroup okt_module_lbl_fancybox
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
	# titre
	$p_titleShow = ! empty($_POST['p_titleShow']) ? true : false;
	$p_titlePosition = ! empty($_POST['p_titlePosition']) ? $_POST['p_titlePosition'] : '';
	
	$p_hideOnOverlayClick = ! empty($_POST['p_hideOnOverlayClick']) ? true : false;
	$p_hideOnContentClick = ! empty($_POST['p_hideOnContentClick']) ? true : false;
	
	# arrière plan
	$p_overlayShow = ! empty($_POST['p_overlayShow']) ? true : false;
	$p_overlayOpacity = ! empty($_POST['p_overlayOpacity']) ? $_POST['p_overlayOpacity'] : 0.3;
	$p_overlayColor = ! empty($_POST['p_overlayColor']) ? $_POST['p_overlayColor'] : '#666666';
	
	# transitions
	$p_cyclic = ! empty($_POST['p_cyclic']) ? true : false;
	
	$p_transitionIn = ! empty($_POST['p_transitionIn']) ? $_POST['p_transitionIn'] : '';
	$p_speedIn = ! empty($_POST['p_speedIn']) ? $_POST['p_speedIn'] : '';
	
	$p_transitionOut = ! empty($_POST['p_transitionOut']) ? $_POST['p_transitionOut'] : '';
	$p_speedOut = ! empty($_POST['p_speedOut']) ? $_POST['p_speedOut'] : '';
	
	if ($okt->error->isEmpty())
	{
		$aNewConf = array(
			'titleShow' => $p_titleShow,
			'titlePosition' => $p_titlePosition,
			
			'hideOnOverlayClick' => $p_hideOnOverlayClick,
			'hideOnContentClick' => $p_hideOnContentClick,
			
			'overlayShow' => (boolean) $p_overlayShow,
			'overlayOpacity' => (float) $p_overlayOpacity,
			'overlayColor' => $p_overlayColor,
			
			'cyclic' => (boolean) $p_cyclic,
			
			'transitionIn' => $p_transitionIn,
			'speedIn' => (integer) $p_speedIn,
			
			'transitionOut' => $p_transitionOut,
			'speedOut' => (integer) $p_speedIn
		);
		
		$okt->lbl_fancybox->config->write($aNewConf);
		
		$okt->flash->success(__('c_c_confirm_configuration_updated'));
		
		http::redirect('module.php?m=lbl_fancybox&action=config');
	}
}

/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_lbl_fancybox_config_title'));

# liste des positions du titre
$aTitlePosition = array(
	'dehors' => 'float',
	'dedans' => 'inside',
	'dessus' => 'over'
);

# liste des types de transition
$aTransitions = array(
	'élastique' => 'elastic',
	'graduelle' => 'fade',
	'aucune' => 'none'
);

# color picker
$okt->page->colorpicker('#p_overlayColor');

# LightBox Like
$okt->page->applyLbl('fancybox');

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
		<legend><?php _e('m_lbl_fancybox_legend_title') ?></legend>

		<div class="three-cols">
			<p class="field col">
				<label><?php echo form::checkbox('p_titleShow',1,$okt->lbl_fancybox->config->titleShow)?>
		<?php _e('m_lbl_fancybox_show_title') ?></label>
			</p>

			<p class="field col">
				<label for="p_titlePosition"><?php _e('m_lbl_fancybox_show_title') ?></label>
		<?php echo form::select('p_titlePosition',$aTitlePosition,$okt->lbl_fancybox->config->titlePosition) ?></p>
		</div>

	</fieldset>

	<fieldset>
		<legend><?php _e('m_lbl_fancybox_legend_background') ?></legend>

		<div class="three-cols">
			<p class="field col">
				<label><?php echo form::checkbox('p_overlayShow',1,$okt->lbl_fancybox->config->overlayShow)?>
		<?php _e('m_lbl_fancybox_show_background') ?></label>
			</p>

			<p class="field col">
				<label for="p_overlayOpacity"><?php _e('m_lbl_fancybox_background_opacity') ?></label>
		<?php echo form::text('p_overlayOpacity', 10, 255, html::escapeHTML($okt->lbl_fancybox->config->overlayOpacity)) ?></p>

			<p class="field col">
				<label for="p_overlayColor"><?php _e('m_lbl_fancybox_background_color') ?></label>
		#<?php echo form::text('p_overlayColor', 10, 255, html::escapeHTML($okt->lbl_fancybox->config->overlayColor)) ?></p>

			<p class="field col">
				<label><?php echo form::checkbox('p_hideOnOverlayClick',1,$okt->lbl_fancybox->config->hideOnOverlayClick)?>
		<?php _e('m_lbl_fancybox_close_on_bg_click') ?></label>
			</p>

		</div>

	</fieldset>

	<fieldset>
		<legend><?php _e('m_lbl_fancybox_legend_transition') ?></legend>

		<div class="three-cols">
			<p class="field col">
				<label><?php echo form::checkbox('p_cyclic',1,$okt->lbl_fancybox->config->cyclic)?>
		<?php _e('m_lbl_fancybox_slideswhow_cycle') ?></label>
			</p>
		</div>

		<div class="three-cols">
			<p class="field col">
				<label for="p_transitionIn"><?php _e('m_lbl_fancybox_transition_in') ?></label>
		<?php echo form::select('p_transitionIn',$aTransitions,$okt->lbl_fancybox->config->transitionIn)?></p>

			<p class="field col">
				<label for="p_speedIn"><?php _e('m_lbl_fancybox_speed_in') ?></label>
		<?php echo form::text('p_speedIn', 10, 255, html::escapeHTML($okt->lbl_fancybox->config->speedIn)) ?></p>
		</div>

		<div class="three-cols">
			<p class="field col">
				<label for="p_transitionOut"><?php _e('m_lbl_fancybox_transition_out') ?></label>
		<?php echo form::select('p_transitionOut',$aTransitions,$okt->lbl_fancybox->config->transitionOut)?></p>

			<p class="field col">
				<label for="p_speedOut"><?php _e('m_lbl_fancybox_speed_out') ?></label>
		<?php echo form::text('p_speedOut', 10, 255, html::escapeHTML($okt->lbl_fancybox->config->speedOut)) ?></p>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php _e('m_lbl_fancybox_legend_other') ?></legend>

		<p class="field">
			<label><?php echo form::checkbox('p_hideOnContentClick',1,$okt->lbl_fancybox->config->hideOnContentClick)?>
		<?php _e('m_lbl_fancybox_close_on_content_click') ?></label>
		</p>

	</fieldset>

	<p><?php echo form::hidden('m','lbl_fancybox'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" />
	</p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
