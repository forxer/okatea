<?php
/**
 * @ingroup okt_module_galleries
 * @brief La page de configuration de l'affichage
 *
 */


# Accès direct interdit
if (!defined('ON_GALLERIES_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
l10n::set(__DIR__.'/../../locales/'.$okt->user->language.'/admin.display');


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_dysplay_clic_gal_image = !empty($_POST['p_dysplay_clic_gal_image']) ? $_POST['p_dysplay_clic_gal_image'] : '';
	$p_dysplay_clic_items_image = !empty($_POST['p_dysplay_clic_items_image']) ? $_POST['p_dysplay_clic_items_image'] : '';
	$p_lightbox_type = !empty($_POST['p_lightbox_type']) ? $_POST['p_lightbox_type'] : '';

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'dysplay_clic_gal_image' => $p_dysplay_clic_gal_image,
			'dysplay_clic_items_image' => $p_dysplay_clic_items_image,
			'lightbox_type' => $p_lightbox_type
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
			<li><a href="#tab_public"><span><?php _e('m_galleries_display_tab_public') ?></span></a></li>
			<li><a href="#tab_images"><span><?php _e('m_galleries_display_tab_images')?></span></a></li>
		</ul>

		<div id="tab_public">
			<h3><?php _e('m_galleries_display_tab_title_public') ?></h3>

			<p class="fake-label"><?php _e('m_galleries_dysplay_image_gallery_list') ?></p>

			<ul class="checklist">
				<li><label><?php echo form::radio(array('p_dysplay_clic_gal_image'),'enter', $okt->galleries->config->dysplay_clic_gal_image == 'enter') ?>
				<?php _e('m_galleries_dysplay_clic_enter_gallery') ?></label></li>

				<li><label><?php echo form::radio(array('p_dysplay_clic_gal_image'),'image', $okt->galleries->config->dysplay_clic_gal_image == 'image') ?>
				<?php _e('m_galleries_dysplay_clic_extend_gallery_image') ?></label></li>
			</ul>

			<p class="fake-label"><?php _e('m_galleries_dysplay_item_galleries') ?></p>

			<ul class="checklist">
				<li><label><?php echo form::radio(array('p_dysplay_clic_items_image'),'details', $okt->galleries->config->dysplay_clic_items_image == 'details') ?>
				<?php _e('m_galleries_dysplay_clic_item_details') ?></label></li>

				<li><label><?php echo form::radio(array('p_dysplay_clic_items_image'),'image', $okt->galleries->config->dysplay_clic_items_image == 'image') ?>
				<?php _e('m_galleries_dysplay_clic_extend_item_image') ?></label></li>
			</ul>
		</div><!-- #tab_public -->

		<div id="tab_images">
			<h3><?php _e('m_galleries_display_tab_title_images') ?></h3>

			<fieldset>
				<legend><?php _e('m_galleries_display_interface_enlarging_images') ?></legend>

				<?php if ($okt->page->hasLbl()) : ?>
				<p class="field"><label for="p_lightbox_type"><?php _e('m_galleries_display_select_interface_display_images') ?></label>
				<?php echo form::select('p_lightbox_type',array_merge(array(__('c_c_action_Disable')=>0),$okt->page->getLblList(true)),$okt->galleries->config->lightbox_type) ?></p>

				<p><?php _e('m_galleries_display_currently_used') ?> : <em><?php $aChoices = array_merge(array(''=>__('c_c_none_f')),$okt->page->getLblList());
				echo $aChoices[$okt->galleries->config->lightbox_type] ?></em></p>

				<?php else : ?>
				<p><span class="span_sprite ss_error"></span> <?php _e('m_galleries_display_no_interface_display_images') ?>
				<?php echo form::hidden('p_lightbox_type', 0); ?></p>
				<?php endif;?>

				<p class="modal-box">
					<a class="modal" rel="test_images" href="<?php echo OKT_PUBLIC_URL ?>/img/sample/chutes_la_nuit.jpg">
					<img width="60" height="60" alt="" src="<?php echo OKT_PUBLIC_URL ?>/img/sample/sq-chutes_la_nuit.jpg" /></a>

					<a class="modal" rel="test_images" href="<?php echo OKT_PUBLIC_URL ?>/img/sample/les_chutes.jpg">
					<img width="60" height="60" alt="" src="<?php echo OKT_PUBLIC_URL ?>/img/sample/sq-les_chutes.jpg" /></a>

					<a class="modal" rel="test_images" href="<?php echo OKT_PUBLIC_URL ?>/img/sample/chutes.jpg">
					<img width="60" height="60" alt="" src="<?php echo OKT_PUBLIC_URL ?>/img/sample/sq-chutes.jpg" /></a>
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
