<?php
/**
 * @ingroup okt_module_catalog
 * @brief La page de configuration de l'affichage
 *
 */


# Accès direct interdit
if (!defined('ON_CATALOG_MODULE')) die;


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	$p_admin_dysplay_style = !empty($_POST['p_admin_dysplay_style']) ? $_POST['p_admin_dysplay_style'] : 'list';
	$p_admin_filters_style = !empty($_POST['p_admin_filters_style']) ? $_POST['p_admin_filters_style'] : 'dialog';

	$p_public_default_nb_per_page = !empty($_POST['p_public_default_nb_per_page']) ? intval($_POST['p_public_default_nb_per_page']) : 10;
	$p_admin_default_nb_per_page = !empty($_POST['p_admin_default_nb_per_page']) ? intval($_POST['p_admin_default_nb_per_page']) : 10;

	$p_public_truncat_char = !empty($_POST['p_public_truncat_char']) ? intval($_POST['p_public_truncat_char']) : 0;

	$p_lightbox_type = !empty($_POST['p_lightbox_type']) ? $_POST['p_lightbox_type'] : '';

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'admin_dysplay_style' => $p_admin_dysplay_style,
			'admin_filters_style' => $p_admin_filters_style,
			'admin_default_nb_per_page' => $p_admin_default_nb_per_page,
			'public_default_nb_per_page' => $p_public_default_nb_per_page,
			'public_truncat_char' => $p_public_truncat_char,
			'lightbox_type' => $p_lightbox_type,
/*			'filters' => array(
				'admin' => array(
					'promo' => (boolean)$p_fltr_admin_promo,
					'nouvo' => (boolean)$p_fltr_admin_nouvo,
					'favo' => (boolean)$p_fltr_admin_favo
				),
				'public' => array(
					'promo' => (boolean)$p_fltr_public_promo,
					'nouvo' => (boolean)$p_fltr_public_nouvo,
					'favo' => (boolean)$p_fltr_public_favo
				)
			)
*/
		);

		try
		{
			$okt->catalog->config->write($new_conf);
			$okt->redirect('module.php?m=catalog&action=display&updated=1');
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
$okt->page->addGlobalTitle('Affichage');

# Tabs
$okt->page->tabs();

# Modal
$okt->page->applyLbl($okt->catalog->config->lightbox_type);

# Confirmations
$okt->page->messages->success('updated',__('c_c_confirm_configuration_updated'));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_public"><span>Côté site</span></a></li>
			<li><a href="#tab_admin"><span>Interface d’administration</span></a></li>
			<?php if ($okt->catalog->config->images['enable']) : ?>
			<li><a href="#tab_images"><span>Images</span></a></li>
			<?php endif; ?>
		</ul>

		<div id="tab_public">
			<h3>Affichage côté site</h3>

			<fieldset>
				<legend>Affichage des listes de produits</legend>

				<p class="field"><label for="p_public_default_nb_per_page">Nombre par défaut de produits par page sur la partie publique</label>
				<?php echo form::text('p_public_default_nb_per_page', 3, 3, $okt->catalog->config->public_default_nb_per_page) ?></p>
			</fieldset>

			<fieldset>
				<legend>Tronquer les descriptions des produits</legend>

				<p class="info">Vous pouvez choisir de tronquer les descriptions des produits sur les listes de produits et d'afficher un lien "En savoir plus".</p>

				<p class="field"><label for="p_public_truncat_char">Nombre de caractères avant troncature</label>
				<?php echo form::text('p_public_truncat_char', 5, 5, $okt->catalog->config->public_truncat_char) ?></p>
			</fieldset>
		</div><!-- #tab_public -->

		<div id="tab_admin">
			<h3>Affichage sur l’interface d’administration</h3>

			<fieldset>
				<legend>Affichage des listes de produits</legend>

				<p class="field">Style d’affichage par défaut des listes de produits sur l’interface d’administration
					<label><span class="icon application_view_list"></span>Liste
					<?php echo form::radio(array('p_admin_dysplay_style'),
					'list', $okt->catalog->config->admin_dysplay_style == 'list') ?></label>

					<label><span class="icon application_view_tile"></span>Mosaïque
					<?php echo form::radio(array('p_admin_dysplay_style'),
					'mosaic', $okt->catalog->config->admin_dysplay_style == 'mosaic') ?></label>
				</p>

				<p class="field"><label for="p_admin_default_nb_per_page">Nombre par défaut de produits par page sur l’interface d’administration</label>
				<?php echo form::text('p_admin_default_nb_per_page', 3, 3, $okt->catalog->config->admin_default_nb_per_page) ?></p>
			</fieldset>

			<fieldset>
				<legend>Filtres d’affichage de la liste de produits</legend>

				<p class="field">Afficher les filtres
					<label>dans une boite de dialogue <?php echo form::radio(array('p_admin_filters_style'),'dialog',($okt->catalog->config->admin_filters_style=='dialog'))?></label>
					<label>dans la page <?php echo form::radio(array('p_admin_filters_style'),'slide',($okt->catalog->config->admin_filters_style=='slide'))?></label>
				</p>
			</fieldset>

		</div><!-- #tab_admin -->

		<?php if ($okt->catalog->config->images['enable']) : ?>
		<div id="tab_images">
			<h3>Affichage des images</h3>
			<fieldset>
				<legend>Interface d’agrandissement des images</legend>

				<?php if ($okt->page->hasLbl()) : ?>
					<p class="field"><label for="p_lightbox_type">Choisissez l’interface d’affichage des images</label>
					<?php echo form::select('p_lightbox_type',array_merge(array('Désactivé'=>0),$okt->page->getLblList(true)),$okt->catalog->config->lightbox_type) ?></p>

					<p>Actuellement utilisé : <em><?php $aChoices = array_merge(array(''=>'aucune'),$okt->page->getLblList());
					echo $aChoices[$okt->catalog->config->lightbox_type] ?></em></p>
				<?php else : ?>
					<p><span class="icon error"></span>Il n’y a aucune interface d’affichage des images de disponible.
					<?php echo form::hidden('p_lightbox_type',0); ?></p>
				<?php endif;?>

				<p class="modal-box">
					<a class="modal" rel="test_images" title="Exemple 1" href="<?php echo OKT_PUBLIC_URL ?>/img/sample/chutes_la_nuit.jpg">
					<img width="60" height="60" alt="" src="<?php echo OKT_PUBLIC_URL ?>/img/sample/sq-chutes_la_nuit.jpg"/></a>

					<a class="modal" rel="test_images" title="Exemple 2" href="<?php echo OKT_PUBLIC_URL ?>/img/sample/les_chutes.jpg">
					<img width="60" height="60" alt="" src="<?php echo OKT_PUBLIC_URL ?>/img/sample/sq-les_chutes.jpg"/></a>

					<a class="modal" rel="test_images" title="Exemple 3" href="<?php echo OKT_PUBLIC_URL ?>/img/sample/chutes.jpg">
					<img width="60" height="60" alt="" src="<?php echo OKT_PUBLIC_URL ?>/img/sample/sq-chutes.jpg"/></a>
				</p>
			</fieldset>
		</div><!-- #tab_images -->
		<?php endif; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','catalog'); ?>
	<?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo form::hidden(array('action'), 'display'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="enregistrer" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
