<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

# Module title tag
$okt->page->addTitleTag($okt->module('News')
	->getTitle());

# Module start breadcrumb
$okt->page->addAriane($okt->module('News')
	->getName(), $view->generateUrl('News_index'));

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_display'));

# Tabs
$okt->page->tabs();

# LightBox Like
$okt->page->applyLbl($okt->module('News')->config->lightbox_type);

?>

<form action="<?php echo $view->generateUrl('News_display') ?>"
	method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_public"><span><?php _e('m_news_display_tab_public') ?></span></a></li>
			<li><a href="#tab_admin"><span><?php _e('m_news_display_tab_admin') ?></span></a></li>
			<?php if ($okt->module('News')->config->images['enable']) : ?>
			<li><a href="#tab_images"><span><?php _e('m_news_display_tab_images') ?></span></a></li>
			<?php endif; ?>
		</ul>

		<div id="tab_public">
			<h3><?php _e('m_news_display_tab_title_public') ?></h3>

			<fieldset>
				<legend><?php _e('m_news_display_posts_list') ?></legend>

				<div class="three-cols">
					<p class="field col">
						<label for="p_public_default_order_by"><?php _e('m_news_display_public_order_display') ?></label>
				<?php echo form::select('p_public_default_order_by', $aFieldChoiceOrderBy, $okt->module('News')->config->public_default_order_by) ?></p>

					<p class="field col">
						<label for="p_public_default_order_direction"><?php _e('m_news_display_public_display_direction') ?></label>
				<?php echo form::select('p_public_default_order_direction', $aFieldChoiceOrderDirection, $okt->module('News')->config->public_default_order_direction) ?></p>

					<p class="field col">
						<label for="p_public_default_nb_per_page"><?php _e('m_news_display_public_number_page') ?></label>
				<?php echo form::text('p_public_default_nb_per_page', 3, 3, $okt->module('News')->config->public_default_nb_per_page) ?></p>
				</div>
			</fieldset>

			<fieldset>
				<legend><?php _e('m_news_display_posts')?></legend>

				<p class="field">
					<label for="p_public_display_date"><?php echo form::checkbox('p_public_display_date', 1, $okt->module('News')->config->public_display_date)?>
				<?php _e('m_news_display_show_date') ?></label>
				</p>

				<p class="field">
					<label for="p_public_display_author"><?php echo form::checkbox('p_public_display_author', 1, $okt->module('News')->config->public_display_author)?>
				<?php _e('m_news_display_show_author') ?></label>
				</p>

			</fieldset>

			<fieldset>
				<legend><?php _e('m_news_display_truncate_posts') ?></legend>

				<p class="note"><?php _e('m_news_display_truncate_message') ?></p>

				<div class="two-cols">
					<p class="field col">
						<label for="p_public_truncat_char"><?php _e('m_news_display_truncate_char_number_on_list') ?></label>
					<?php echo form::text('p_public_truncat_char', 5, 5, $okt->module('News')->config->public_truncat_char) ?></p>

					<p class="field col">
						<label for="p_insert_truncat_char"><?php _e('m_news_display_truncate_char_number_on_insert') ?></label>
					<?php echo form::text('p_insert_truncat_char', 5, 5, $okt->module('News')->config->insert_truncat_char) ?></p>
				</div>
			</fieldset>

		</div>
		<!-- #tab_public -->

		<div id="tab_admin">
			<h3><?php _e('m_news_display_tab_title_admin')?></h3>

			<fieldset>
				<legend><?php _e('m_news_display_posts_list')?></legend>

				<div class="three-cols">
					<p class="field col">
						<label for="p_admin_default_order_by"><?php _e('m_news_display_admin_order_display') ?></label>
				<?php echo form::select('p_admin_default_order_by', $aFieldChoiceOrderBy, $okt->module('News')->config->admin_default_order_by) ?></p>

					<p class="field col">
						<label for="p_admin_default_order_direction"><?php _e('m_news_display_admin_display_direction') ?></label>
				<?php echo form::select('p_admin_default_order_direction', $aFieldChoiceOrderDirection, $okt->module('News')->config->admin_default_order_direction) ?></p>

					<p class="field col">
						<label for="p_admin_default_nb_per_page"><?php _e('m_news_display_admin_number_page') ?></label>
				<?php echo form::text('p_admin_default_nb_per_page', 3, 3, $okt->module('News')->config->admin_default_nb_per_page) ?></p>
				</div>
			</fieldset>

			<fieldset>
				<legend><?php _e('m_news_display_filters_style') ?></legend>

				<ul class="checklist">
					<li><label for="p_admin_filters_style_dialog"><?php echo form::radio(array('p_admin_filters_style','p_admin_filters_style_dialog'),'dialog',($okt->module('News')->config->admin_filters_style=='dialog')) ?> <?php _e('m_news_display_filters_dialog') ?></label></li>
					<li><label for="p_admin_filters_style_slide"><?php echo form::radio(array('p_admin_filters_style','p_admin_filters_style_slide'),'slide',($okt->module('News')->config->admin_filters_style=='slide')) ?> <?php _e('m_news_display_filters_slide') ?></label></li>
				</ul>

			</fieldset>

		</div>
		<!-- #tab_admin -->

		<?php if ($okt->module('News')->config->images['enable']) : ?>
		<div id="tab_images">
			<h3><?php _e('m_news_display_tab_title_images')?></h3>
			<fieldset>
				<legend><?php _e('m_news_display_interface_enlarging_images')?></legend>

				<?php if ($okt->page->hasLbl()) : ?>
					<p class="field">
					<label for="p_lightbox_type"><?php _e('m_news_display_select_interface_display_images') ?></label>
					<?php echo form::select('p_lightbox_type', array_merge(array(__('c_c_action_Disable')=>0), $okt->page->getLblList(true)), $okt->module('News')->config->lightbox_type) ?></p>

				<p><?php _e('m_news_display_currently_used')?> : <em><?php
				
				$aChoices = array_merge(array(
					'' => __('c_c_none_f')
				), $okt->page->getLblList());
				echo $aChoices[$okt->module('News')->config->lightbox_type]?></em>
				</p>
				<?php else : ?>
					<p>
					<span class="icon error"></span><?php _e('m_news_display_no_interface_display_images')?>
					<?php echo form::hidden('p_lightbox_type', 0) ?></p>
				<?php endif; ?>

				<p class="modal-box">
					<a class="modal" rel="test_images"
						title="<?php _e('m_news_display_example_1') ?>"
						href="<?php echo $okt->options->public_url ?>/img/sample/chutes_la_nuit.jpg">
						<img width="60" height="60" alt=""
						src="<?php echo $okt->options->public_url ?>/img/sample/sq-chutes_la_nuit.jpg" />
					</a> <a class="modal" rel="test_images"
						title="<?php _e('m_news_display_example_2') ?>"
						href="<?php echo $okt->options->public_url ?>/img/sample/les_chutes.jpg">
						<img width="60" height="60" alt=""
						src="<?php echo $okt->options->public_url ?>/img/sample/sq-les_chutes.jpg" />
					</a> <a class="modal" rel="test_images"
						title="<?php _e('m_news_display_example_3') ?>"
						href="<?php echo $okt->options->public_url ?>/img/sample/chutes.jpg">
						<img width="60" height="60" alt=""
						src="<?php echo $okt->options->public_url ?>/img/sample/sq-chutes.jpg" />
					</a>
				</p>
			</fieldset>
		</div>
		<!-- #tab_images -->
		<?php endif; ?>

	</div>
	<!-- #tabered -->

	<p><?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" />
	</p>
</form>
