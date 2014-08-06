<?php
/**
 * @ingroup okt_module_lbl_nyromodal_2
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
	$p_modal = ! empty($_POST['p_modal']) ? true : false;
	$p_closeOnEscape = ! empty($_POST['p_closeOnEscape']) ? true : false;
	$p_closeOnClick = ! empty($_POST['p_closeOnClick']) ? true : false;
	$p_galleryLoop = ! empty($_POST['p_galleryLoop']) ? true : false;
	$p_galleryCounts = ! empty($_POST['p_galleryCounts']) ? true : false;
	
	if ($okt->error->isEmpty())
	{
		$aNewConf = array(
			'modal' => $p_modal,
			'closeOnEscape' => $p_closeOnEscape,
			'closeOnClick' => $p_closeOnClick,
			'galleryLoop' => $p_galleryLoop,
			'galleryCounts' => $p_galleryCounts
		);
		
		$okt->lbl_nyromodal_2->config->write($aNewConf);
		
		$okt['flashMessages']->success(__('c_c_confirm_configuration_updated'));
		
		http::redirect('module.php?m=lbl_nyromodal_2&action=config');
	}
}

/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_lbl_nyromodal_2_config_title'));

# LightBox Like
$okt->page->applyLbl('nyromodal2');

# En-tête
require OKT_ADMIN_HEADER_FILE;
?>

<p class="modal-box">
	<a class="modal" rel="test_images"
		title="<?php printf(__('c_c_Example_%s'), 1) ?>"
		href="<?php echo $okt['public_url'] ?>/img/sample/chutes_la_nuit.jpg">
		<img width="60" height="60" alt=""
		src="<?php echo $okt['public_url'] ?>/img/sample/sq-chutes_la_nuit.jpg" />
	</a> <a class="modal" rel="test_images"
		title="<?php printf(__('c_c_Example_%s'), 2) ?>"
		href="<?php echo $okt['public_url'] ?>/img/sample/les_chutes.jpg">
		<img width="60" height="60" alt=""
		src="<?php echo $okt['public_url'] ?>/img/sample/sq-les_chutes.jpg" />
	</a> <a class="modal" rel="test_images"
		title="<?php printf(__('c_c_Example_%s'), 3) ?>"
		href="<?php echo $okt['public_url'] ?>/img/sample/chutes.jpg">
		<img width="60" height="60" alt=""
		src="<?php echo $okt['public_url'] ?>/img/sample/sq-chutes.jpg" />
	</a>
</p>

<form action="module.php" method="post">

	<p class="field">
		<label><?php echo form::checkbox('p_modal',1,$okt->lbl_nyromodal_2->config->modal)?>
	<?php _e('m_lbl_nyromodal_2_config_modal') ?></label>
	</p>

	<p class="field">
		<label><?php echo form::checkbox('p_closeOnEscape',1,$okt->lbl_nyromodal_2->config->closeOnEscape)?>
	<?php _e('m_lbl_nyromodal_2_config_close_on_esc') ?></label>
	</p>

	<p class="field">
		<label><?php echo form::checkbox('p_closeOnClick',1,$okt->lbl_nyromodal_2->config->closeOnClick)?>
	<?php _e('m_lbl_nyromodal_2_config_close_on_click') ?></label>
	</p>

	<p class="field">
		<label><?php echo form::checkbox('p_showCloseButton',1,$okt->lbl_nyromodal_2->config->showCloseButton)?>
	<?php _e('m_lbl_nyromodal_2_config_show_close_button') ?></label>
	</p>

	<p class="field col">
		<label><?php echo form::checkbox('p_galleryLoop',1,$okt->lbl_nyromodal_2->config->galleryLoop)?>
	<?php _e('m_lbl_nyromodal_2_config_gallery_loop') ?></label>
	</p>

	<p class="field col">
		<label><?php echo form::checkbox('p_galleryCounts',1,$okt->lbl_nyromodal_2->config->galleryCounts)?>
	<?php _e('m_lbl_nyromodal_2_config_gallery_counts') ?></label>
	</p>

	<p><?php echo form::hidden('m','lbl_nyromodal_2'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" />
	</p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
