<?php
/**
 * @ingroup okt_module_lbl_nyromodal
 * @brief La page de configuration
 *
 */

# Accès direct interdit
if (!defined('ON_LBL_NYROMODAL_MODULE')) die;


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_bgColor = !empty($_POST['p_bgColor']) ? $_POST['p_bgColor'] : '';

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'bgColor' => $p_bgColor,
		);

		try
		{
			$okt->lbl_nyromodal->config->write($new_conf);

			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

			$okt->redirect('module.php?m=lbl_nyromodal&action=config');
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

# Titre de la page
$okt->page->addGlobalTitle(__('m_lbl_nyromodal_config_title'));

# color picker
$okt->page->colorpicker('#p_bgColor');

# LightBox Like
$okt->page->applyLbl('nyromodal');


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<p class="modal-box">
	<a class="modal" rel="test_images" title="<?php printf(__('c_c_Example_%s'), 1) ?>" href="<?php echo OKT_PUBLIC_URL ?>/img/sample/chutes_la_nuit.jpg">
	<img width="60" height="60" alt="" src="<?php echo OKT_PUBLIC_URL ?>/img/sample/sq-chutes_la_nuit.jpg"/></a>

	<a class="modal" rel="test_images" title="<?php printf(__('c_c_Example_%s'), 2) ?>" href="<?php echo OKT_PUBLIC_URL ?>/img/sample/les_chutes.jpg">
	<img width="60" height="60" alt="" src="<?php echo OKT_PUBLIC_URL ?>/img/sample/sq-les_chutes.jpg"/></a>

	<a class="modal" rel="test_images" title="<?php printf(__('c_c_Example_%s'), 3) ?>" href="<?php echo OKT_PUBLIC_URL ?>/img/sample/chutes.jpg">
	<img width="60" height="60" alt="" src="<?php echo OKT_PUBLIC_URL ?>/img/sample/sq-chutes.jpg"/></a>
</p>

<form action="module.php" method="post">

	<p class="field col"><label for="p_bgColor"><?php _e('m_lbl_nyromodal_config_bg_color') ?></label>
	#<?php echo form::text('p_bgColor', 10, 255, html::escapeHTML($okt->lbl_nyromodal->config->bgColor)) ?></p>

	<p><?php echo form::hidden('m','lbl_nyromodal'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
