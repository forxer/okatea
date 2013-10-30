
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__.'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout de jQuery UI
$okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/ui/jquery-ui.min.js');
$okt->page->css->addFile(OKT_PUBLIC_URL.'/ui-themes/'.$okt->config->public_theme.'/jquery-ui.css');
# fin Okatea : ajout de jQuery UI ?>


<?php # début Okatea : ajout du datepicker
$okt->page->datePicker();
# fin Okatea : ajout du datepicker ?>


<?php
$okt->page->js->addFile(OKT_THEME.'/modules/estimate/estimate.form.js');

$okt->page->js->addReady('

	$("#estimate-form").oktEstimateForm({
		text: {
			productTitle: "'.html::escapeJS(__('m_estimate_form_product_%s')).'",
			addProduct: "'.html::escapeJS(__('m_estimate_form_add_product')).'",
			removeProduct: "'.html::escapeJS(__('m_estimate_form_remove_product')).'",
			productLabel: "'.html::escapeJS(__('m_estimate_form_choose_product')).'",
			quantityLabel: "'.html::escapeJS(__('m_estimate_form_quantity')).'",
			accessoryLabel: "'.html::escapeJS(__('m_estimate_form_accessory_%s')).'",
			addAccessory: "'.html::escapeJS(__('m_estimate_form_add_accessory')).'",
			removeAccessory: "'.html::escapeJS(__('m_estimate_form_remove_accessory')).'"
		},
		html: {
			productsWrapper: "products_wrapper",
			productWrapper: "product_wrapper",
			productLine: "product_line",
			productField: "p_product",
			productQuantityField: "p_product_quantity",
			addProductWrapper: "add_product_wrapper",
			addProductLink: "add_product_link",
			removeProductWrapper: "remove_product_wrapper",
			removeProductLink: "remove_product_link",
			accessoriesWrapper: "accessories_wrapper",
			accessoryWrapper: "accessory_wrapper",
			accessoryField: "p_accessory",
			accessoryQuantityField: "p_accessory_quantity",
			addAccessoryWrapper: "add_accessory_wrapper",
			addAccessoryLink: "add_accessory_link",
			removeAccessoryWrapper: "remove_accessory_wrapper",
			removeAccessoryLink: "remove_accessory_link"
		},
		default_accessories_number: 2,
		products: '.json_encode($aProductsSelect).',
		accessories: '.json_encode($aProductsAccessories).',
		spinner: { min: 0 }
	});

');
?>

<!-- <h1><?php echo html::escapeHTML($okt->estimate->getName()) ?></h1> -->


<?php # début Okatea : affichage des éventuelles erreurs
if ($okt->error->notEmpty()) : ?>
	<div class="error_box">
		<?php echo $okt->error->get(); ?>
	</div>
<?php endif; # fin Okatea : affichage des éventuelles erreurs ?>


<form id="estimate-form" action="<?php echo html::escapeHTML($okt->estimate->config->url) ?>" method="post">

	<fieldset>
		<legend>Vous concernant</legend>

		<p class="infos">Merci d'indiquer les informations vous concernant afin que nous puissions vous répondre dans les plus bref délais.</p>

		<div class="two-cols">
			<p class="field col"><label for="p_lastname" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Last_name') ?></label>
			<?php echo form::text('p_lastname', 40, 255, html::escapeHTML($aFormData['lastname'])) ?></p>

			<p class="field col"><label for="p_firstname" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_First_name') ?></label>
			<?php echo form::text('p_firstname', 40, 255, html::escapeHTML($aFormData['firstname'])) ?></p>
		</div>

		<div class="two-cols">
			<p class="field col"><label for="p_email" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Email') ?></label>
			<?php echo form::text('p_email', 40, 255, html::escapeHTML($aFormData['email'])) ?></p>

			<p class="field col"><label for="p_phone" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Phone') ?></label>
			<?php echo form::text('p_phone', 40, 255, html::escapeHTML($aFormData['phone'])) ?></p>
		</div>

	</fieldset>

	<fieldset>
		<legend>Dates prévisionelles</legend>

		<p class="infos">Merci d'indiquer les dates pendant lesquelles vous souhaitez louer le matériel.</p>

		<div class="two-cols">
			<p class="field col"><label for="p_start_date" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_estimate_form_start_date') ?></label>
			<?php echo form::text('p_start_date', 40, 255, html::escapeHTML($aFormData['start_date']), 'datepicker') ?></p>

			<p class="field col"><label for="p_end_date" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_estimate_form_end_date') ?></label>
			<?php echo form::text('p_end_date', 40, 255, html::escapeHTML($aFormData['end_date']), 'datepicker') ?></p>
		</div>
	</fieldset>

	<fieldset id="products_wrapper">
		<legend>Choix des produits et des accessoires</legend>

		<p class="infos">Veuillez choisir les matériels pour lequel porte ce devis. Vous pouvez ajouter des accessoires pour chacun des matériels.</p>

		<?php for ($i=1; $i<=2; $i++) : ?>

		<fieldset id="product_wrapper_<?php echo $i ?>" class="product_wrapper">
			<legend><?php printf(__('m_estimate_form_product_%s'), $i) ?></legend>

			<div id="product_line_<?php echo $i ?>" class="product_line">
				<p class="field product"><label for="p_product_<?php echo $i ?>"><?php _e('m_estimate_form_choose_product') ?></label>
				<?php echo form::select(array('p_product['.$i.']', 'p_product_'.$i), $aProductsSelect, '', 'p_product') ?></p>

				<p class="field quantity"><label for="p_product_quantity_<?php echo $i ?>"><?php _e('m_estimate_form_quantity') ?></label>
				<?php echo form::text(array('p_product_quantity['.$i.']', 'p_product_quantity_'.$i), 10, 255, '', 'p_product_quantity spinner') ?></p>

				<p id="remove_product_wrapper_<?php echo $i ?>" class="remove_product_wrapper"></p>
			</div>

			<div id="accessories_wrapper_<?php echo $i ?>" class="accessories_wrapper">
				<?php /*for ($j=1; $j<=2; $j++) : ?>
				<div id="accessory_wrapper_<?php echo $i ?>_<?php echo $j ?>" class="accessory_wrapper">
					<p class="field accessory"><label for="p_accessory_<?php echo $i ?>_<?php echo $j ?>"><?php printf(__('m_estimate_form_accessory_%s'), $j) ?></label>
					<?php echo form::select(array('p_accessory['.$i.']['.$j.']', 'p_accessory_'.$i.'_'.$j), array(), '', 'p_accessory_'.$i) ?></p>

					<p class="field quantity"><label for="p_accessory_quantity_<?php echo $i ?>_<?php echo $j ?>"><?php _e('m_estimate_form_quantity') ?></label>
					<?php echo form::text(array('p_accessory_quantity['.$i.']['.$j.']', 'p_accessory_quantity_'.$i.'_'.$j), 10, 255, '', 'spinner p_accessory_quantity_'.$i) ?></p>

					<p class="remove_accessory_wrapper"><a href="#" id="remove_accessory_link_<?php echo $i ?>_<?php echo $j ?>" class="remove_accessory_link"><?php _e('m_estimate_form_remove_accessory') ?></a></p>
				</div>
				<?php endfor; */?>

				<!-- <p class="add_accessory_wrapper"><a href="#" id="add_accessory_link_<?php echo $i ?>" class="add_accessory_link"><?php _e('m_estimate_form_add_accessory') ?></a></p> -->
			</div>
		</fieldset>

		<?php endfor; ?>

	</fieldset>

	<p class="field col"><label for="p_comment">Commentaire</label>
	<?php echo form::textarea('p_comment', 60, 8, html::escapeHTML($aFormData['comment'])) ?></p>

	<p class="submit-wrapper"><input type="submit" value="<?php _e('c_c_action_send') ?>" name="sended" id="submit-estimate-form" /></p>
</form>
