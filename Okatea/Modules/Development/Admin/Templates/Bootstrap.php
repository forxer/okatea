<?php

use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Modules\Development\Bootstrap\Module\Module as BootstrapModule;

$view->extend('layout');

# Module title tag
$okt->page->addTitleTag(__('Development'));

# Start breadcrumb
$okt->page->addAriane(__('Development'), $view->generateUrl('Development_index'));

# Titre de la page
$okt->page->addGlobalTitle(__('m_development_bootstrap_title'));

# Tabs
$okt->page->tabs();

# Loader
$okt->page->loader('.lazy-load');

?>

<p><?php _e('m_development_bootstrap_feature_description') ?></p>

<div id="tabered">
	<ul>
		<li><a href="#tab-simple"><span><?php _e('m_development_bootstrap_tab_simple') ?></span></a></li>
		<li><a href="#tab-advanced"><span><?php _e('m_development_bootstrap_tab_advanced') ?></span></a></li>
	</ul>

	<div id="tab-simple">
		<h4 id="add_module_bootstrap_title"><?php _e('m_development_bootstrap_tab_simple_title') ?></h4>

		<form class="col" action="<?php echo $view->generateUrl('Development_bootstrap') ?>" method="post">

			<fieldset>
				<legend><?php _e('m_development_bootstrap_module_definition') ?></legend>

				<div class="two-cols">
					<p class="field col"><label for="bootstrap_module_name" class="required" title="<?php _e('c_c_required_field') ?>"><?php _e('m_development_bootstrap_en_name'); ?></label>
					<?php echo form::text('bootstrap_module_name', 60, 255, html::escapeHTML($aBootstrapData['name'])); ?></p>

					<p class="field col"><label for="bootstrap_module_name_fr" class="required" title="<?php _e('c_c_required_field') ?>"><?php _e('m_development_bootstrap_fr_name'); ?></label>
					<?php echo form::text('bootstrap_module_name_fr', 60, 255, html::escapeHTML($aBootstrapData['name_fr'])); ?></p>
				</div>

				<p class="field"><label for="bootstrap_module_version" class="required" title="<?php _e('c_c_required_field') ?>"><?php _e('Version'); ?></label>
				<?php echo form::text('bootstrap_module_version', 10, 255, html::escapeHTML($aBootstrapData['version'])); ?></p>

				<div class="two-cols">
					<p class="field col"><label for="bootstrap_module_description"><?php _e('m_development_bootstrap_en_description'); ?></label>
					<?php echo form::text('bootstrap_module_description', 60, 255, html::escapeHTML($aBootstrapData['description'])); ?></p>

					<p class="field col"><label for="bootstrap_module_description_fr"><?php _e('m_development_bootstrap_fr_description'); ?></label>
					<?php echo form::text('bootstrap_module_description_fr', 60, 255, html::escapeHTML($aBootstrapData['description_fr'])); ?></p>
				</div>

				<div class="two-cols">
					<p class="field col"><label for="bootstrap_module_author"><?php _e('m_development_bootstrap_author'); ?></label>
					<?php echo form::text('bootstrap_module_author', 60, 255, html::escapeHTML($aBootstrapData['author'])); ?></p>

					<p class="field col"><label for="bootstrap_module_licence"><?php _e('m_development_bootstrap_license'); ?></label>
					<?php echo form::select('bootstrap_module_licence', BootstrapModule::getLicencesList(true), $aBootstrapData['licence']) ?></p>
				</div>

			</fieldset>

			<p><?php echo form::hidden('simple', 1) ?>
			<?php echo $okt->page->formtoken() ?>
			<input type="submit" value="<?php _e('m_development_bootstrap_submit_value') ?>" /></p>
		</form>

	</div><!-- #tab-simple -->

	<div id="tab-advanced">
		<h4 id="add_module_bootstrap_title"><?php _e('m_development_bootstrap_tab_advanced_title') ?></h4>

		<form class="col" action="<?php echo $view->generateUrl('Development_bootstrap') ?>" method="post">

			<fieldset>
				<legend><?php _e('m_development_bootstrap_module_definition') ?></legend>

				<div class="two-cols">
					<p class="field col"><label for="bootstrap_module_name" class="required" title="<?php _e('c_c_required_field') ?>"><?php _e('m_development_bootstrap_en_name'); ?></label>
					<?php echo form::text('bootstrap_module_name', 60, 255, html::escapeHTML($aBootstrapData['name'])); ?></p>

					<p class="field col"><label for="bootstrap_module_name_fr" class="required" title="<?php _e('c_c_required_field') ?>"><?php _e('m_development_bootstrap_fr_name'); ?></label>
					<?php echo form::text('bootstrap_module_name_fr', 60, 255, html::escapeHTML($aBootstrapData['name_fr'])); ?></p>
				</div>

				<p class="field"><label for="bootstrap_module_version" class="required" title="<?php _e('c_c_required_field') ?>"><?php _e('Version'); ?></label>
				<?php echo form::text('bootstrap_module_version', 10, 255, html::escapeHTML($aBootstrapData['version'])); ?></p>

				<div class="two-cols">
					<p class="field col"><label for="bootstrap_module_description"><?php _e('m_development_bootstrap_en_description'); ?></label>
					<?php echo form::text('bootstrap_module_description', 60, 255, html::escapeHTML($aBootstrapData['description'])); ?></p>

					<p class="field col"><label for="bootstrap_module_description_fr"><?php _e('m_development_bootstrap_fr_description'); ?></label>
					<?php echo form::text('bootstrap_module_description_fr', 60, 255, html::escapeHTML($aBootstrapData['description_fr'])); ?></p>
				</div>

				<div class="two-cols">
					<p class="field col"><label for="bootstrap_module_author"><?php _e('m_development_bootstrap_author'); ?></label>
					<?php echo form::text('bootstrap_module_author', 60, 255, html::escapeHTML($aBootstrapData['author'])); ?></p>

					<p class="field col"><label for="bootstrap_module_licence"><?php _e('m_development_bootstrap_license'); ?></label>
					<?php echo form::select('bootstrap_module_licence', BootstrapModule::getLicencesList(true), $aBootstrapData['licence']) ?></p>
				</div>

			</fieldset>

			<fieldset>
				<legend><?php _e('m_development_bootstrap_localization') ?></legend>

				<fieldset>
					<legend><?php _e('m_development_bootstrap_l10n_1_legend') ?></legend>
					<div class="two-cols">
						<p class="field col"><label for="bootstrap_module_l10n_1_en"><?php _e('m_development_bootstrap_l10n_1_en') ?></label>
						<?php echo form::text('bootstrap_module_l10n_1_en', 60, 255, html::escapeHTML($aBootstrapData['locales']['en'][1])) ?></p>

						<p class="field col"><label for="bootstrap_module_l10n_1_fr"><?php _e('m_development_bootstrap_l10n_1_fr') ?></label>
						<?php echo form::text('bootstrap_module_l10n_1_fr', 60, 255, html::escapeHTML($aBootstrapData['locales']['fr'][1])) ?></p>
					</div>
					<p class="note"><?php _e('m_development_bootstrap_l10n_1_desc') ?></p>
				</fieldset>

				<fieldset>
					<legend><?php _e('m_development_bootstrap_l10n_2_legend') ?></legend>
					<div class="two-cols">
						<p class="field col"><label for="bootstrap_module_l10n_2_en"><?php _e('m_development_bootstrap_l10n_2_en') ?></label>
						<?php echo form::text('bootstrap_module_l10n_2_en', 60, 255, html::escapeHTML($aBootstrapData['locales']['en'][2])) ?></p>

						<p class="field col"><label for="bootstrap_module_l10n_2_fr"><?php _e('m_development_bootstrap_l10n_2_fr') ?></label>
						<?php echo form::text('bootstrap_module_l10n_2_fr', 60, 255, html::escapeHTML($aBootstrapData['locales']['fr'][2])) ?></p>
					</div>
					<p class="note"><?php _e('m_development_bootstrap_l10n_2_desc') ?></p>
				</fieldset>

				<fieldset>
					<legend><?php _e('m_development_bootstrap_l10n_3_legend') ?></legend>
					<div class="two-cols">
						<p class="field col"><label for="bootstrap_module_l10n_3_en"><?php _e('m_development_bootstrap_l10n_3_en') ?></label>
						<?php echo form::text('bootstrap_module_l10n_3_en', 60, 255, html::escapeHTML($aBootstrapData['locales']['en'][3])) ?></p>

						<p class="field col"><label for="bootstrap_module_l10n_3_fr"><?php _e('m_development_bootstrap_l10n_3_fr') ?></label>
						<?php echo form::text('bootstrap_module_l10n_3_fr', 60, 255, html::escapeHTML($aBootstrapData['locales']['fr'][3])) ?></p>
					</div>
					<p class="note"><?php _e('m_development_bootstrap_l10n_3_desc') ?></p>
				</fieldset>

				<fieldset>
					<legend><?php _e('m_development_bootstrap_l10n_4_legend') ?></legend>
					<div class="two-cols">
						<p class="field col"><label for="bootstrap_module_l10n_4_en"><?php _e('m_development_bootstrap_l10n_4_en') ?></label>
						<?php echo form::text('bootstrap_module_l10n_4_en', 60, 255, html::escapeHTML($aBootstrapData['locales']['en'][4])) ?></p>

						<p class="field col"><label for="bootstrap_module_l10n_4_fr"><?php _e('m_development_bootstrap_l10n_4_fr') ?></label>
						<?php echo form::text('bootstrap_module_l10n_4_fr',60,255,html::escapeHTML($aBootstrapData['locales']['fr'][4])) ?></p>
					</div>
					<p class="note"><?php _e('m_development_bootstrap_l10n_4_desc') ?></p>
				</fieldset>

				<fieldset>
					<legend><?php _e('m_development_bootstrap_l10n_5_legend') ?></legend>
					<div class="two-cols">
						<p class="field col"><label for="bootstrap_module_l10n_5_en"><?php _e('m_development_bootstrap_l10n_5_en') ?></label>
						<?php echo form::text('bootstrap_module_l10n_5_en',60,255,html::escapeHTML($aBootstrapData['locales']['en'][5])) ?></p>

						<p class="field col"><label for="bootstrap_module_l10n_5_fr"><?php _e('m_development_bootstrap_l10n_5_fr') ?></label>
						<?php echo form::text('bootstrap_module_l10n_5_fr',60,255,html::escapeHTML($aBootstrapData['locales']['fr'][5])) ?></p>
					</div>
					<p class="note"><?php _e('m_development_bootstrap_l10n_5_desc') ?></p>
				</fieldset>

				<fieldset>
					<legend><?php _e('m_development_bootstrap_l10n_6_legend') ?></legend>
					<div class="two-cols">
						<p class="field col"><label for="bootstrap_module_l10n_6_en"><?php _e('m_development_bootstrap_l10n_6_en'); ?></label>
						<?php echo form::text('bootstrap_module_l10n_6_en',60,255,html::escapeHTML($aBootstrapData['locales']['en'][6])); ?></p>

						<p class="field col"><label for="bootstrap_module_l10n_6_fr"><?php _e('m_development_bootstrap_l10n_6_fr'); ?></label>
						<?php echo form::text('bootstrap_module_l10n_6_fr',60,255,html::escapeHTML($aBootstrapData['locales']['fr'][6])); ?></p>
					</div>
					<p class="note"><?php _e('m_development_bootstrap_l10n_6_desc'); ?></p>
				</fieldset>

				<fieldset>
					<legend><?php _e('m_development_bootstrap_l10n_7_legend') ?></legend>
					<div class="two-cols">
						<p class="field col"><label for="bootstrap_module_l10n_7_en"><?php _e('m_development_bootstrap_l10n_7_en'); ?></label>
						<?php echo form::text('bootstrap_module_l10n_7_en',60,255,html::escapeHTML($aBootstrapData['locales']['en'][7])); ?></p>

						<p class="field col"><label for="bootstrap_module_l10n_7_fr"><?php _e('m_development_bootstrap_l10n_7_fr'); ?></label>
						<?php echo form::text('bootstrap_module_l10n_7_fr',60,255,html::escapeHTML($aBootstrapData['locales']['fr'][7])); ?></p>
					</div>
					<p class="note"><?php _e('m_development_bootstrap_l10n_7_desc'); ?></p>
				</fieldset>

				<fieldset>
					<legend><?php _e('m_development_bootstrap_l10n_8_legend') ?></legend>
					<div class="two-cols">
						<p class="field col"><label for="bootstrap_module_l10n_8_en"><?php _e('m_development_bootstrap_l10n_8_en'); ?></label>
						<?php echo form::text('bootstrap_module_l10n_8_en',60,255,html::escapeHTML($aBootstrapData['locales']['en'][8])); ?></p>

						<p class="field col"><label for="bootstrap_module_l10n_8_fr"><?php _e('m_development_bootstrap_l10n_8_fr'); ?></label>
						<?php echo form::text('bootstrap_module_l10n_8_fr',60,255,html::escapeHTML($aBootstrapData['locales']['fr'][8])); ?></p>
					</div>
					<p class="note"><?php _e('m_development_bootstrap_l10n_8_desc'); ?></p>
				</fieldset>

				<fieldset>
					<legend><?php _e('m_development_bootstrap_l10n_9_legend') ?></legend>
					<div class="two-cols">
						<p class="field col"><label for="bootstrap_module_l10n_9_en"><?php _e('m_development_bootstrap_l10n_9_en'); ?></label>
						<?php echo form::text('bootstrap_module_l10n_9_en', 60, 255, html::escapeHTML($aBootstrapData['locales']['en'][9])); ?></p>

						<p class="field col"><label for="bootstrap_module_l10n_9_fr"><?php _e('m_development_bootstrap_l10n_9_fr'); ?></label>
						<?php echo form::text('bootstrap_module_l10n_9_fr', 60, 255, html::escapeHTML($aBootstrapData['locales']['fr'][9])); ?></p>
					</div>
					<p class="note"><?php _e('m_development_bootstrap_l10n_9_desc'); ?></p>
				</fieldset>

				<fieldset>
					<legend><?php _e('m_development_bootstrap_l10n_10_legend') ?></legend>
					<div class="two-cols">
						<p class="field col"><label for="bootstrap_module_l10n_10_en"><?php _e('m_development_bootstrap_l10n_10_en'); ?></label>
						<?php echo form::text('bootstrap_module_l10n_10_en', 60, 255, html::escapeHTML($aBootstrapData['locales']['en'][10])); ?></p>

						<p class="field col"><label for="bootstrap_module_l10n_10_fr"><?php _e('m_development_bootstrap_l10n_10_fr'); ?></label>
						<?php echo form::text('bootstrap_module_l10n_10_fr', 60, 255, html::escapeHTML($aBootstrapData['locales']['fr'][10])); ?></p>
					</div>
					<p class="note"><?php _e('m_development_bootstrap_l10n_10_desc'); ?></p>
				</fieldset>

				<p class="field"><label for="bootstrap_module_l10n_fem"><?php echo form::checkbox('bootstrap_module_l10n_fem', 1, $aBootstrapData['l10n_fem']) ?>
				Les éléments sont du genre féminin (par exemple "page")</label></p>

			</fieldset>

			<p><?php echo form::hidden('advanced', 1) ?>
			<?php echo $okt->page->formtoken() ?>
			<input type="submit" value="<?php _e('m_development_bootstrap_submit_value') ?>" /></p>
		</form>
	</div><!-- #tab-advanced -->

</div><!-- #tabered -->

