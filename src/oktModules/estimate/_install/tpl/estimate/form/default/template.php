
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(dirname(__FILE__).'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout de jQuery UI
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/ui/jquery-ui.min.js');
$okt->page->css->addFile(OKT_COMMON_URL.'/ui-themes/'.$okt->config->public_theme.'/jquery-ui.css');
# fin Okatea : ajout de jQuery UI ?>

<?php # début Okatea : ajout du datepicker
$okt->page->datePicker();
# fin Okatea : ajout du datepicker ?>

<?php
$okt->page->js->addReady('
	$(".spinner" ).spinner();
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

	<fieldset>
		<legend>Choix des produits et des accessoires</legend>

		<p class="infos">Veuillez choisir les matériels pour lequel porte ce devis. Vous pouvez ajouter des accessoires pour chacun des matériels.</p>

		<?php for ($i=1; $i<=3; $i++) : ?>

		<div class="product-line">

			<p class="field product"><label for="p_product_<?php echo $i ?>"><?php printf(__('m_estimate_form_product_%s'), $i) ?></label>
			<?php echo form::select(array('p_product['.$i.']', 'p_product_'.$i), $aProductsSelect) ?></p>

			<p class="field quantity"><label for="p_quantity_<?php echo $i ?>"><?php _e('m_estimate_form_quantity') ?></label>
			<?php echo form::text(array('p_quantity['.$i.']', 'p_quantity_'.$i), 10, 255, '', 'spinner') ?></p>

			<p class="field accessories"><label for="p_accessories_<?php echo $i ?>">Accessoire</label>
			<?php echo form::select(array('p_accessories['.$i.']', 'p_accessories_'.$i), array()) ?></p>
		</div>

		<?php endfor; ?>

		<?php debug($aProductsAccessories) ?>

	</fieldset>

	<p class="submit-wrapper"><input type="submit" value="<?php _e('m_estimate_send') ?>" name="sended" id="submit-estimate-form" /></p>
</form>
