<?php
/**
 * @ingroup okt_module_news
 * @brief La page de configuration de l'affichage
 *
 */


# Accès direct interdit
if (!defined('ON_NEWS_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
l10n::set(__DIR__.'/../../locales/'.$okt->user->language.'/admin.display');


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_admin_dysplay_style = !empty($_POST['p_admin_dysplay_style']) ? $_POST['p_admin_dysplay_style'] : 'list';
	$p_admin_filters_style = !empty($_POST['p_admin_filters_style']) ? $_POST['p_admin_filters_style'] : 'dialog';

	$p_public_default_order_by = !empty($_POST['p_public_default_order_by']) ? $_POST['p_public_default_order_by'] : 'id';
	$p_public_default_order_direction = !empty($_POST['p_public_default_order_direction']) ? $_POST['p_public_default_order_direction'] : 'DESC';
	$p_public_default_nb_per_page = !empty($_POST['p_public_default_nb_per_page']) ? intval($_POST['p_public_default_nb_per_page']) : 10;

	$p_public_display_date = !empty($_POST['p_public_display_date']) ? true : false;
	$p_public_display_author = !empty($_POST['p_public_display_author']) ? true : false;

	$p_public_truncat_char = !empty($_POST['p_public_truncat_char']) ? intval($_POST['p_public_truncat_char']) : 0;
	$p_insert_truncat_char = !empty($_POST['p_insert_truncat_char']) ? intval($_POST['p_insert_truncat_char']) : 0;

	$p_admin_default_order_by = !empty($_POST['p_admin_default_order_by']) ? $_POST['p_admin_default_order_by'] : 'id';
	$p_admin_default_order_direction = !empty($_POST['p_admin_default_order_direction']) ? $_POST['p_admin_default_order_direction'] : 'DESC';
	$p_admin_default_nb_per_page = !empty($_POST['p_admin_default_nb_per_page']) ? intval($_POST['p_admin_default_nb_per_page']) : 10;

	$p_lightbox_type = !empty($_POST['p_lightbox_type']) ? $_POST['p_lightbox_type'] : '';

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'admin_dysplay_style' => $p_admin_dysplay_style,
			'admin_filters_style' => $p_admin_filters_style,

			'admin_default_order_by' => $p_admin_default_order_by,
			'admin_default_order_direction' => $p_admin_default_order_direction,
			'admin_default_nb_per_page' => (integer)$p_admin_default_nb_per_page,

			'public_default_order_by' => $p_public_default_order_by,
			'public_default_order_direction' => $p_public_default_order_direction,
			'public_default_nb_per_page' => (integer)$p_public_default_nb_per_page,

			'public_display_date' => (boolean)$p_public_display_date,
			'public_display_author' => (boolean)$p_public_display_author,

			'public_truncat_char' => (integer)$p_public_truncat_char,
			'insert_truncat_char' => (integer)$p_insert_truncat_char,

			'lightbox_type' => $p_lightbox_type
		);

		try
		{
			$okt->news->config->write($new_conf);

			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));

			http::redirect('module.php?m=news&action=display');
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

$field_choice_order_by = array(
	__('m_news_display_order_by_created') => 'created_at',
	__('m_news_display_order_by_updated') => 'updated_at',
	__('m_news_display_order_by_title') => 'title',
	__('m_news_display_order_by_category') => 'rubrique'
);

$field_choice_order_direction = array(
	__('c_c_sorting_Ascending') => 'ASC',
	__('c_c_sorting_Descending') => 'DESC'
);

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_display'));

# Tabs
$okt->page->tabs();

# LightBox Like
$okt->page->applyLbl($okt->news->config->lightbox_type);


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_public"><span><?php _e('m_news_display_tab_public') ?></span></a></li>
			<li><a href="#tab_admin"><span><?php _e('m_news_display_tab_admin') ?></span></a></li>
			<?php if ($okt->news->config->images['enable']) : ?>
			<li><a href="#tab_images"><span><?php _e('m_news_display_tab_images') ?></span></a></li>
			<?php endif; ?>
		</ul>

		<div id="tab_public">
			<h3><?php _e('m_news_display_tab_title_public') ?></h3>

			<fieldset>
				<legend><?php _e('m_news_display_posts_list') ?></legend>

				<div class="three-cols">
				<p class="field col"><label for="p_public_default_order_by"><?php _e('m_news_display_public_order_display') ?></label>
				<?php echo form::select('p_public_default_order_by',$field_choice_order_by,$okt->news->config->public_default_order_by) ?></p>

				<p class="field col"><label for="p_public_default_order_direction"><?php _e('m_news_display_public_display_direction') ?></label>
				<?php echo form::select('p_public_default_order_direction',$field_choice_order_direction,$okt->news->config->public_default_order_direction) ?></p>

				<p class="field col"><label for="p_public_default_nb_per_page"><?php _e('m_news_display_public_number_page') ?></label>
				<?php echo form::text('p_public_default_nb_per_page', 3, 3, $okt->news->config->public_default_nb_per_page) ?></p>
				</div>
			</fieldset>

			<fieldset>
				<legend><?php _e('m_news_display_posts')?></legend>

				<p class="field"><label for="p_public_display_date"><?php echo form::checkbox('p_public_display_date', 1, $okt->news->config->public_display_date) ?>
				<?php _e('m_news_display_show_date') ?></label></p>

				<p class="field"><label for="p_public_display_author"><?php echo form::checkbox('p_public_display_author', 1, $okt->news->config->public_display_author) ?>
				<?php _e('m_news_display_show_author') ?></label></p>

			</fieldset>

			<fieldset>
				<legend><?php _e('m_news_display_truncate_posts') ?></legend>

				<p class="note"><?php _e('m_news_display_truncate_message') ?></p>

				<div class="two-cols">
					<p class="field col"><label for="p_public_truncat_char"><?php _e('m_news_display_truncate_char_number_on_list') ?></label>
					<?php echo form::text('p_public_truncat_char', 5, 5, $okt->news->config->public_truncat_char) ?></p>

					<p class="field col"><label for="p_insert_truncat_char"><?php _e('m_news_display_truncate_char_number_on_insert') ?></label>
					<?php echo form::text('p_insert_truncat_char', 5, 5, $okt->news->config->insert_truncat_char) ?></p>
				</div>
			</fieldset>

		</div><!-- #tab_public -->

		<div id="tab_admin">
			<h3><?php _e('m_news_display_tab_title_admin')?></h3>

			<fieldset>
				<legend><?php _e('m_news_display_posts_list')?></legend>

				<div class="three-cols">
				<p class="field col"><label for="p_admin_default_order_by"><?php _e('m_news_display_admin_order_display') ?></label>
				<?php echo form::select('p_admin_default_order_by',$field_choice_order_by,$okt->news->config->admin_default_order_by) ?></p>

				<p class="field col"><label for="p_admin_default_order_direction"><?php _e('m_news_display_admin_display_direction') ?></label>
				<?php echo form::select('p_admin_default_order_direction',$field_choice_order_direction,$okt->news->config->admin_default_order_direction) ?></p>

				<p class="field col"><label for="p_admin_default_nb_per_page"><?php _e('m_news_display_admin_number_page') ?></label>
				<?php echo form::text('p_admin_default_nb_per_page', 3, 3, $okt->news->config->admin_default_nb_per_page) ?></p>
				</div>
			</fieldset>

			<fieldset>
				<legend><?php _e('m_news_display_filters_style') ?></legend>

				<ul class="checklist">
					<li><label for="p_admin_filters_style_dialog"><?php echo form::radio(array('p_admin_filters_style','p_admin_filters_style_dialog'),'dialog',($okt->news->config->admin_filters_style=='dialog')) ?> <?php _e('m_news_display_filters_dialog') ?></label></li>
					<li><label for="p_admin_filters_style_slide"><?php echo form::radio(array('p_admin_filters_style','p_admin_filters_style_slide'),'slide',($okt->news->config->admin_filters_style=='slide')) ?> <?php _e('m_news_display_filters_slide') ?></label></li>
				</ul>

			</fieldset>

		</div><!-- #tab_admin -->

		<?php if ($okt->news->config->images['enable']) : ?>
		<div id="tab_images">
			<h3><?php _e('m_news_display_tab_title_images')?></h3>
			<fieldset>
				<legend><?php _e('m_news_display_interface_enlarging_images')?></legend>

				<?php if ($okt->page->hasLbl()) : ?>
					<p class="field"><label for="p_lightbox_type"><?php _e('m_news_display_select_interface_display_images') ?></label>
					<?php echo form::select('p_lightbox_type', array_merge(array(__('c_c_action_Disable')=>0), $okt->page->getLblList(true)), $okt->news->config->lightbox_type) ?></p>

					<p><?php _e('m_news_display_currently_used')?> : <em><?php $aChoices = array_merge(array(''=>__('c_c_none_f')), $okt->page->getLblList());
					echo $aChoices[$okt->news->config->lightbox_type] ?></em></p>
				<?php else : ?>
					<p><span class="icon error"></span><?php _e('m_news_display_no_interface_display_images') ?>
					<?php echo form::hidden('p_lightbox_type', 0) ?></p>
				<?php endif; ?>

				<p class="modal-box">
					<a class="modal" rel="test_images" title="<?php _e('m_news_display_example_1') ?>" href="<?php echo OKT_PUBLIC_URL ?>/img/sample/chutes_la_nuit.jpg">
					<img width="60" height="60" alt="" src="<?php echo OKT_PUBLIC_URL ?>/img/sample/sq-chutes_la_nuit.jpg"/></a>

					<a class="modal" rel="test_images" title="<?php _e('m_news_display_example_2') ?>" href="<?php echo OKT_PUBLIC_URL ?>/img/sample/les_chutes.jpg">
					<img width="60" height="60" alt="" src="<?php echo OKT_PUBLIC_URL ?>/img/sample/sq-les_chutes.jpg"/></a>

					<a class="modal" rel="test_images" title="<?php _e('m_news_display_example_3') ?>" href="<?php echo OKT_PUBLIC_URL ?>/img/sample/chutes.jpg">
					<img width="60" height="60" alt="" src="<?php echo OKT_PUBLIC_URL ?>/img/sample/sq-chutes.jpg"/></a>
				</p>
			</fieldset>
		</div><!-- #tab_images -->
		<?php endif; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','news'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'display'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save')?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
