<?php
/**
 * @ingroup okt_module_faq
 * @brief La page de configuration de l'affichage
 *
 */
use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE'))
	die();
	
	/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_public_default_order_by = !empty($_POST['p_public_default_order_by']) ? $_POST['p_public_default_order_by'] : 'id';
	$p_public_default_order_direction = !empty($_POST['p_public_default_order_direction']) ? $_POST['p_public_default_order_direction'] : 'desc';
	$p_public_default_nb_per_page = !empty($_POST['p_public_default_nb_per_page']) ? intval($_POST['p_public_default_nb_per_page']) : 10;
	
	$p_public_truncat_char = !empty($_POST['p_public_truncat_char']) ? intval($_POST['p_public_truncat_char']) : 0;
	
	$p_admin_default_order_by = !empty($_POST['p_admin_default_order_by']) ? $_POST['p_admin_default_order_by'] : 'id';
	$p_admin_default_order_direction = !empty($_POST['p_admin_default_order_direction']) ? $_POST['p_admin_default_order_direction'] : 'desc';
	$p_admin_default_nb_per_page = !empty($_POST['p_admin_default_nb_per_page']) ? intval($_POST['p_admin_default_nb_per_page']) : 10;
	
	$p_admin_filters_style = !empty($_POST['p_admin_filters_style']) ? $_POST['p_admin_filters_style'] : 'dialog';
	
	$p_lightbox_type = !empty($_POST['p_lightbox_type']) ? $_POST['p_lightbox_type'] : '';
	
	if ($okt->error->isEmpty())
	{
		$question_conf = array(
			'public_default_order_by' => $p_public_default_order_by,
			'public_default_order_direction' => $p_public_default_order_direction,
			'public_default_nb_per_page' => (integer) $p_public_default_nb_per_page,
			'public_truncat_char' => (integer) $p_public_truncat_char,
			
			'admin_default_order_by' => $p_admin_default_order_by,
			'admin_default_order_direction' => $p_admin_default_order_direction,
			'admin_default_nb_per_page' => (integer) $p_admin_default_nb_per_page,
			'admin_filters_style' => $p_admin_filters_style,
			
			'lightbox_type' => $p_lightbox_type
		);
		
		$okt->faq->config->write($question_conf);
		
		$okt['flashMessages']->success(__('c_c_confirm_configuration_updated'));
		
		http::redirect('module.php?m=faq&action=display');
	}
}

/* Affichage
----------------------------------------------------------*/

$field_choice_order_by = array(
	__('m_faq_title') => 'title',
	__('m_faq_id') => 'id'
);

$field_choice_order_direction = array(
	__('m_faq_ascending') => 'ASC',
	__('m_faq_descending') => 'DESC'
);

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_display'));

# Tabs
$okt->page->tabs();

# Modal
$okt->page->applyLbl($okt->faq->config->lightbox_type);

# En-tête
require OKT_ADMIN_HEADER_FILE;
?>

<form action="module.php" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_public"><span><?php _e('m_faq_display_website')?></span></a></li>
			<li><a href="#tab_admin"><span><?php _e('m_faq_display_admin')?></span></a></li>
			<?php if ($okt->faq->config->images['enable']) : ?>
			<li><a href="#tab_images"><span><?php _e('Image')?></span></a></li>
			<?php endif; ?>
		</ul>

		<div id="tab_public">
			<h3><?php _e('m_faq_display_website')?></h3>

			<fieldset>
				<legend><?php _e('m_faq_display_questions_list')?></legend>

				<p class="field col">
					<label for="p_public_default_nb_per_page"><?php _e('m_faq_public_number_questions')?></label>
				<?php echo form::text('p_public_default_nb_per_page', 3, 3, $okt->faq->config->public_default_nb_per_page) ?></p>

				<p class="field">
					<label for="p_public_default_order_by"><?php _e('m_faq_public_order_display') ?></label>
				<?php echo form::select('p_public_default_order_by',$field_choice_order_by,$okt->faq->config->public_default_order_by) ?></p>

				<p class="field">
					<label for="p_public_default_order_direction"><?php _e('m_faq_public_display_direction') ?></label>
				<?php echo form::select('p_public_default_order_direction',$field_choice_order_direction,$okt->faq->config->public_default_order_direction) ?></p>
			</fieldset>

			<fieldset>
				<legend><?php _e('m_faq_truncate_questions')?></legend>

				<p class="info"><?php _e('m_faq_public_trucate_message')?></p>

				<p class="field">
					<label for="p_public_truncat_char"><?php _e('m_faq_public_trucate_number')?></label>
				<?php echo form::text('p_public_truncat_char', 5, 5, $okt->faq->config->public_truncat_char) ?></p>
			</fieldset>
		</div>
		<!-- #tab_public -->

		<div id="tab_admin">
			<h3><?php _e('m_faq_display_admin')?></h3>

			<fieldset>
				<legend><?php _e('m_faq_display_questions_list')?></legend>

				<p class="field col">
					<label for="p_admin_default_nb_per_page"><?php _e('m_faq_admin_number_questions') ?></label>
				<?php echo form::text('p_admin_default_nb_per_page', 3, 3, $okt->faq->config->admin_default_nb_per_page) ?></p>

				<p class="field">
					<label for="p_admin_default_order_by"><?php _e('m_faq_admin_order_display') ?></label>
				<?php echo form::select('p_admin_default_order_by',$field_choice_order_by,$okt->faq->config->admin_default_order_by) ?></p>

				<p class="field">
					<label for="p_admin_default_order_direction"><?php _e('m_faq_admin_display_direction') ?></label>
				<?php echo form::select('p_admin_default_order_direction',$field_choice_order_direction,$okt->faq->config->admin_default_order_direction) ?></p>

			</fieldset>

			<fieldset>
				<legend><?php _e('m_faq_filters_display_questions_list')?></legend>

				<p class="field"><?php _e('m_faq_filters_display')?>
					<label><?php _e('m_faq_dialog_box')?> <?php echo form::radio(array('p_admin_filters_style'),'dialog',($okt->faq->config->admin_filters_style=='dialog'))?></label>
					<label><?php _e('m_faq_in_page')?> <?php echo form::radio(array('p_admin_filters_style'),'slide',($okt->faq->config->admin_filters_style=='slide'))?></label>
				</p>
			</fieldset>

		</div>
		<!-- #tab_admin -->


		<?php if ($okt->faq->config->images['enable']) : ?>
		<div id="tab_images">
			<h3><?php _e('m_faq_display_images')?></h3>
			<fieldset>
				<legend><?php _e('m_faq_expansion_images')?></legend>

				<?php if ($okt->page->hasLbl()) : ?>
					<p class="field">
					<label for="p_lightbox_type"><?php _e('m_faq_choose_display')?></label>
					<?php echo form::select('p_lightbox_type',array_merge(array(__('c_c_action_Disable')=>0),$okt->page->getLblList(true)),$okt->faq->config->lightbox_type) ?></p>

				<p><?php _e('m_faq_currently_used')?> <em><?php
				
$aChoices = array_merge(array(
					'' => __('c_c_none_f')
				), $okt->page->getLblList());
				echo $aChoices[$okt->faq->config->lightbox_type]?></em>
				</p>
				<?php else : ?>
					<p>
					<span class="icon error"></span><?php _e('m_faq_no_interface_images')?>
					<?php echo form::hidden('p_lightbox_type',0); ?></p>
				<?php endif;?>

				<p class="modal-box">
					<a class="modal" rel="test_images"
						href="<?php echo $okt['public_url'] ?>/img/sample/chutes_la_nuit.jpg">
						<img width="60" height="60" alt=""
						src="<?php echo $okt['public_url'] ?>/img/sample/sq-chutes_la_nuit.jpg" />
					</a> <a class="modal" rel="test_images"
						href="<?php echo $okt['public_url'] ?>/img/sample/les_chutes.jpg">
						<img width="60" height="60" alt=""
						src="<?php echo $okt['public_url'] ?>/img/sample/sq-les_chutes.jpg" />
					</a> <a class="modal" rel="test_images"
						href="<?php echo $okt['public_url'] ?>/img/sample/chutes.jpg">
						<img width="60" height="60" alt=""
						src="<?php echo $okt['public_url'] ?>/img/sample/sq-chutes.jpg" />
					</a>
				</p>
			</fieldset>
		</div>
		<!-- #tab_images -->
		<?php endif; ?>

	</div>
	<!-- #tabered -->

	<p><?php echo form::hidden('m','faq'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'display'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save')?>" />
	</p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
