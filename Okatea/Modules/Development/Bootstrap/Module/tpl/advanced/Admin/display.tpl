<?php
##header##

use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;



/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_admin_filters_style = !empty($_POST['p_admin_filters_style']) ? $_POST['p_admin_filters_style'] : 'dialog';

	$p_public_default_order_by = !empty($_POST['p_public_default_order_by']) ? $_POST['p_public_default_order_by'] : 'id';
	$p_public_default_order_direction = !empty($_POST['p_public_default_order_direction']) ? $_POST['p_public_default_order_direction'] : 'DESC';
	$p_public_default_nb_per_page = !empty($_POST['p_public_default_nb_per_page']) ? intval($_POST['p_public_default_nb_per_page']) : 10;

	$p_admin_default_order_by = !empty($_POST['p_admin_default_order_by']) ? $_POST['p_admin_default_order_by'] : 'id';
	$p_admin_default_order_direction = !empty($_POST['p_admin_default_order_direction']) ? $_POST['p_admin_default_order_direction'] : 'DESC';
	$p_admin_default_nb_per_page = !empty($_POST['p_admin_default_nb_per_page']) ? intval($_POST['p_admin_default_nb_per_page']) : 10;

	$p_lightbox_type = !empty($_POST['p_lightbox_type']) ? $_POST['p_lightbox_type'] : '';

	if ($okt->error->isEmpty())
	{
		$aNewConf = array(
			'admin_filters_style' => $p_admin_filters_style,

			'admin_default_order_by' => $p_admin_default_order_by,
			'admin_default_order_direction' => $p_admin_default_order_direction,
			'admin_default_nb_per_page' => (integer)$p_admin_default_nb_per_page,

			'public_default_order_by' => $p_public_default_order_by,
			'public_default_order_direction' => $p_public_default_order_direction,
			'public_default_nb_per_page' => (integer)$p_public_default_nb_per_page,

			'lightbox_type' => $p_lightbox_type
		);

		$okt->##module_id##->config->write($aNewConf);

		$okt['flash']->success(__('c_c_confirm_configuration_updated'));

		http::redirect('module.php?m=##module_id##&action=display');
	}
}


/* Affichage
----------------------------------------------------------*/

$field_choice_order_by = array(
	'date de création' => 'created_at',
	'date de dernière modification' => 'updated_at',
	'titre' => 'title'
);

$field_choice_order_direction = array(
	'croissant' => 'ASC',
	'décroissant' => 'DESC'
);

# Titre de la page
$okt->page->addGlobalTitle('Affichage');

# Tabs
$okt->page->tabs();

# LightBox Like
$okt->page->applyLbl($okt->##module_id##->config->lightbox_type);


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_public"><span>Côté site</span></a></li>
			<li><a href="#tab_admin"><span>Interface d’administration</span></a></li>
			<?php if ($okt->##module_id##->config->images['enable']) : ?>
			<li><a href="#tab_images"><span>Images</span></a></li>
			<?php endif; ?>
		</ul>

		<div id="tab_public">
			<h3>Affichage côté site</h3>

			<fieldset>
				<legend>Affichage des listes d'élements</legend>

				<div class="two-cols">
				<p class="field col"><label for="p_public_default_order_by">Ordre d’affichage par défaut sur la partie publique</label>
				<?php echo form::select('p_public_default_order_by',$field_choice_order_by,$okt->##module_id##->config->public_default_order_by)?></p>

				<p class="field col"><label for="p_public_default_order_direction">Sens d’affichage par défaut sur la partie publique</label>
				<?php echo form::select('p_public_default_order_direction',$field_choice_order_direction,$okt->##module_id##->config->public_default_order_direction)?></p>

				<p class="field col"><label for="p_public_default_nb_per_page">Nombre par défaut d'éléments par page sur la partie publique</label>
				<?php echo form::text('p_public_default_nb_per_page', 3, 3, $okt->##module_id##->config->public_default_nb_per_page) ?></p>
				</div>
			</fieldset>


		</div><!-- #tab_public -->

		<div id="tab_admin">
			<h3>Affichage sur l’interface d’administration</h3>

			<fieldset>
				<legend>Affichage des listes des éléments</legend>

				<div class="two-cols">
				<p class="field col"><label for="p_admin_default_order_by">Ordre d’affichage par défaut sur l’interface d’administration</label>
				<?php echo form::select('p_admin_default_order_by',$field_choice_order_by,$okt->##module_id##->config->admin_default_order_by)?></p>

				<p class="field col"><label for="p_admin_default_order_direction">Sens d’affichage par défaut sur l’interface d’administration</label>
				<?php echo form::select('p_admin_default_order_direction',$field_choice_order_direction,$okt->##module_id##->config->admin_default_order_direction)?></p>

				<p class="field col"><label for="p_admin_default_nb_per_page">Nombre par défaut d'éléments par page sur l’interface d’administration</label>
				<?php echo form::text('p_admin_default_nb_per_page', 3, 3, $okt->##module_id##->config->admin_default_nb_per_page) ?></p>
				</div>
			</fieldset>

			<fieldset>
				<legend>Filtres d’affichage de la liste des éléments</legend>

				<p class="field">Afficher les filtres
					<label for="p_admin_filters_style_dialog">dans une boite de dialogue <?php echo form::radio(array('p_admin_filters_style','p_admin_filters_style_dialog'),'dialog',($okt->##module_id##->config->admin_filters_style=='dialog'))?></label>
					<label for="p_admin_filters_style_slide">dans la page <?php echo form::radio(array('p_admin_filters_style','p_admin_filters_style_slide'),'slide',($okt->##module_id##->config->admin_filters_style=='slide'))?></label>
				</p>
			</fieldset>

		</div><!-- #tab_admin -->

		<?php if ($okt->##module_id##->config->images['enable']) : ?>
		<div id="tab_images">
			<h3>Affichage des images</h3>
			<fieldset>
				<legend>Interface d’agrandissement des images</legend>

				<?php if ($okt->page->hasLbl()) : ?>
					<p class="field"><label for="p_lightbox_type">Choisissez l’interface d’affichage des images</label>
					<?php echo form::select('p_lightbox_type',array_merge(array('Désactivé'=>0),$okt->page->getLblList(true)),$okt->##module_id##->config->lightbox_type) ?></p>

					<p>Actuellement utilisé : <em><?php $aChoices = array_merge(array(''=>'aucune'),$okt->page->getLblList());
					echo $aChoices[$okt->##module_id##->config->lightbox_type] ?></em></p>
				<?php else : ?>
					<p><span class="icon error"></span>Il n’y a aucune interface d’affichage des images de disponible.
					<?php echo form::hidden('p_lightbox_type',0); ?></p>
				<?php endif;?>

				<p class="modal-box">
					<a class="modal" rel="test_images" title="Exemple 1" href="<?php echo $okt['public_url'] ?>/img/sample/chutes_la_nuit.jpg">
					<img width="60" height="60" alt="" src="<?php echo $okt['public_url'] ?>/img/sample/sq-chutes_la_nuit.jpg"/></a>

					<a class="modal" rel="test_images" title="Exemple 2" href="<?php echo $okt['public_url'] ?>/img/sample/les_chutes.jpg">
					<img width="60" height="60" alt="" src="<?php echo $okt['public_url'] ?>/img/sample/sq-les_chutes.jpg"/></a>

					<a class="modal" rel="test_images" title="Exemple 3" href="<?php echo $okt['public_url'] ?>/img/sample/chutes.jpg">
					<img width="60" height="60" alt="" src="<?php echo $okt['public_url'] ?>/img/sample/sq-chutes.jpg"/></a>
				</p>
			</fieldset>
		</div><!-- #tab_images -->
		<?php endif; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','##module_id##'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'display'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

