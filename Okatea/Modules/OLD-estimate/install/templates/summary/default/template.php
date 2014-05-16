<?php
use Okatea\Tao\Html\Modifiers;

?>

<?php 
# début Okatea : ce template étend le layout
$view->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php 
# début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__ . '/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php 
# début Okatea : ajout de jQuery
$okt->page->js->addFile($okt->options->public_url . '/components/jquery/dist/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php 
# début Okatea : ajout de jQuery UI
$okt->page->js->addFile($okt->options->public_url . '/plugins/jquery-ui/jquery-ui.min.js');
$okt->page->css->addFile($okt->options->public_url . '/plugins/jquery-ui/themes/' . $okt->config->jquery_ui['public'] . '/jquery-ui.css');
# fin Okatea : ajout de jQuery UI ?>


<?php 
# début Okatea : ajout du JS propre à la page
$okt->page->js->addReady('
	$("#send_estimate").button({
		icons: {
			primary: "ui-icon-check"
		}
	});

	$("#update_estimate").button({
		icons: {
			primary: "ui-icon-pencil"
		}
	});
');
# fin Okatea : ajout du JS propre à la page ?>


<p>Votre demande de devis n'est pas encore envoyée.</p>
<p>Veuillez vérifier les informations ci-dessous, et les valider ou les
	modifier si besoin.</p>

<div class="two-cols">
	<div class="col">
		<h2>Informations vous concernant</h2>

		<p><?php echo $view->escape($aEstimateData['firstname'].' '.$aEstimateData['lastname']) ?></p>
		<p><?php echo $view->escape($aEstimateData['email']) ?></p>
		<p><?php echo $view->escape($aEstimateData['phone']) ?></p>
	</div>
	<div class="col">
		<h2>Dates prévisionnelles</h2>

		<?php if (empty($aEstimateData['end_date']) || $aEstimateData['start_date'] == $aEstimateData['end_date']) : ?>
		<p><?php printf(__('On %s'), dt::dt2str(__('%A, %B %d, %Y'), $view->escape($aEstimateData['start_date']))) ?></p>

		<?php else : ?>
		<p><?php
			
printf(__('From %s to %s'), dt::dt2str(__('%A, %B %d, %Y'), $view->escape($aEstimateData['start_date'])), dt::dt2str(__('%A, %B %d, %Y'), $view->escape($aEstimateData['end_date'])));
			?></p>

		<?php endif; ?>
	</div>
</div>

<?php if ($okt->estimate->config->enable_accessories) : ?>
<h2>Produits et accessoires</h2>
<?php else : ?>
<h2>Produits</h2>
<?php endif; ?>

	<?php foreach ($aEstimateData['products'] as $aProduct) : ?>
<div class="product_wrapper">
	<div class="product_line">
		<div class="product_title"><?php echo $view->escape($aProduct['title']) ?></div>
		<div class="product_quantity"><?php echo $view->escape($aProduct['quantity']) ?></div>
	</div>

		<?php if ($okt->estimate->config->enable_accessories && !empty($aProduct['accessories'])) : ?>
		<div class="accessories_wrapper">
			<?php foreach ($aProduct['accessories'] as $aAccessory) : ?>
			<div class="accessory_line">
			<div class="accessory_title"><?php echo $view->escape($aAccessory['title']) ?></div>
			<div class="accessory_quantity"><?php echo $view->escape($aAccessory['quantity']) ?></div>
		</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
<?php endforeach; ?>


<h2>Commentaire</h2>

<p><?php echo Modifiers::nlToP($view->escape($aEstimateData['comment'])) ?></p>

<p id="buttons">
	<a
		href="<?php echo $view->escapeHtmlAttr(EstimateHelpers::getSummaryUrl()) ?>?send=1"
		id="send_estimate">Valider et envoyer</a> <a
		href="<?php echo $view->escapeHtmlAttr(EstimateHelpers::getFormUrl()) ?>"
		id="update_estimate">Modifier</a>
</p>
