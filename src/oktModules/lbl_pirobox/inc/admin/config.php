<?php
/**
 * @ingroup module_lbl_pirobox
 * @brief La page de configuration
 *
 */

use Tao\Forms\StaticFormElements as form;

# Accès direct interdit
if (!defined('ON_LBL_PIROBOX_MODULE')) die;


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_theme = !empty($_POST['p_theme']) ? $_POST['p_theme'] : '';

	$p_my_speed = !empty($_POST['p_my_speed']) ? $_POST['p_my_speed'] : '';
	$p_close_speed = !empty($_POST['p_close_speed']) ? $_POST['p_close_speed'] : '';

	$p_slideshow = !empty($_POST['p_slideshow']) ? true : false;
	$p_slideSpeed = !empty($_POST['p_slideSpeed']) ? $_POST['p_slideSpeed'] : '';

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'theme' => $p_theme,

			'my_speed' => (integer)$p_my_speed,
			'close_speed' => (integer)$p_close_speed,

			'slideShow' => (boolean)$p_slideshow,
			'slideSpeed' => (integer)$p_slideSpeed,
		);

		try
		{
			$okt->lbl_pirobox->config->write($new_conf);

			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

			http::redirect('module.php?m=lbl_pirobox&action=config');
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
$okt->page->addGlobalTitle(__('m_lbl_pirobox_config_title'));

$aThemes = array(
	__('m_lbl_pirobox_config_theme_1') => 1,
	__('m_lbl_pirobox_config_theme_5') => 5,
	__('m_lbl_pirobox_config_theme_2') => 2,
	__('m_lbl_pirobox_config_theme_3') => 3,
	__('m_lbl_pirobox_config_theme_4') => 4
);

# LightBox Like
$okt->page->applyLbl('pirobox');


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

	<p class="field"><label for="p_theme"><?php _e('m_lbl_pirobox_config_theme') ?></label>
	<?php echo form::select('p_theme',$aThemes, $okt->lbl_pirobox->config->theme) ?></p>

	<p class="field col"><label for="p_my_speed"><?php _e('m_lbl_pirobox_config_my_speed') ?></label>
	<?php echo form::text('p_my_speed', 10, 255, html::escapeHTML($okt->lbl_pirobox->config->my_speed)) ?></p>

	<p class="field col"><label for="p_close_speed"><?php _e('m_lbl_pirobox_config_close_speed') ?></label>
	<?php echo form::text('p_close_speed', 10, 255, html::escapeHTML($okt->lbl_pirobox->config->close_speed)) ?></p>

	<p class="field col"><label><?php echo form::checkbox('p_slideshow',1,$okt->lbl_pirobox->config->slideShow) ?>
	<?php _e('m_lbl_pirobox_config_slideshow') ?></label></p>

	<p class="field col"><label for="p_slideSpeed"><?php _e('m_lbl_pirobox_config_slidespeed') ?></label>
	<?php echo form::text('p_slideSpeed', 10, 255, html::escapeHTML($okt->lbl_pirobox->config->slideSpeed)) ?></p>

	<p><?php echo form::hidden('m','lbl_pirobox'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'config'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
