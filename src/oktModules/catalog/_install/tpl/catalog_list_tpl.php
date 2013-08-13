
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout de la CSS spécifique au module
$okt->page->css->addFile(OKT_THEME.'/modules/catalog/styles.css');
# fin Okatea : ajout de la CSS spécifique au module ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout du JS de scrollToTopOfPage
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/easing/jquery.easing.min.js');
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/scrollToTopOfPage/jquery.scrollToTopOfPage.min.js');
$okt->page->js->addReady('
	$("a.scrollTop").scrollToTopOfPage({
		"top": 300,					// hauteur avant affichage du lien
		"duration": 1700,			// durée animation retour en haut
		"easing": "easeOutQuint"	// type animation retour en haut
	});
');
# fin Okatea : ajout du JS de scrollToTopOfPage ?>


<?php # début Okatea : ajout du modal
$okt->page->applyLbl($okt->catalog->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<?php # début Okatea : javascript pour afficher les filtres s'ils sont repliés
if ($okt->catalog->config->enable_filters && !$okt->catalog->filters->params->show_filters)
{
	$okt->page->js->addReady('
		var c = $("#catalog-filter-control").html("<a href=\"#\">'.html::escapeJS(__('m_catalog_display_filters')).'</a>");

		c.css("display","block");

		$("#'.$okt->catalog->filters->getFilterFormId().'").hide();

		c.click(function() {
			$("#'.$okt->catalog->filters->getFilterFormId().'").slideDown("slow");
			$(this).hide();
			return false;
		});
	');
}
# fin Okatea : javascript pour afficher les filtres s'ils sont repliés ?>


<?php # début Okatea : affichage du fil d'ariane
if ($okt->catalog->config->enable_ariane) :
$okt->page->breadcrumb->setHtmlSeparator(' &rsaquo; ');
$okt->page->breadcrumb->display('<p id="ariane"><em>'.__('c_c_user_you_are_here').'</em> %s</p>');
endif; # fin Okatea : affichage du fil d'ariane ?>


<?php # début Okatea : si les filtres sont activés
if ($okt->catalog->config->enable_filters) : ?>

	<?php # début Okatea : lien d’affichage des filtres
	if (!$okt->catalog->filters->params->show_filters) : ?>
	<p id="catalog-filter-control"></p>
	<?php endif; # fin Okatea : lien d’affichage des filtres ?>


	<?php # début Okatea : affichage des filtres ?>
	<form action="<?php echo html::escapeHTML($okt->catalog->config->url) ?>" method="get" id="<?php echo $okt->catalog->filters->getFilterFormId() ?>" class="catalog-filters-form">
		<fieldset>
		<legend><?php _e('m_catalog_display_filters') ?></legend>

		<?php echo $okt->catalog->filters->getFiltersFields(); ?>

		<p class="center"><input type="submit" value="<?php _e('c_c_action_display') ?>" name="<?php echo $okt->catalog->filters->getFilterSubmitName() ?>" />
		<a href="<?php echo html::escapeHTML($okt->catalog->config->url) ?>?catalog_init_filters=1" class="italic"><?php _e('m_catalog_display_filters_init') ?></a></p>

		</fieldset>
	</form>
	<?php # fin Okatea : affichage des filtres ?>

<?php endif; # fin Okatea : si les filtres sont activés ?>


<?php # début Okatea : si il n’y a PAS de produit à afficher on peut indiquer un message
if ($productsList->isEmpty()) : ?>

<p><em><?php _e('m_catalog_there_is_no_product') ?></em></p>

<?php endif;
# fin Okatea : si il n’y a PAS de produit à afficher on peut indiquer un message ?>


<?php # début Okatea : si il y a des produits on affiche la liste
if (!$productsList->isEmpty()) : ?>

<div id="products_list">

	<?php # début Okatea : boucle sur la liste des produits
	while ($productsList->fetch()) : ?>

	<?php # début Okatea : affichage d'un produit ?>
	<div class="product <?php echo $productsList->odd_even ?>">


		<?php # début Okatea : affichage du titre ?>
		<h2 class="product-title"><a href="<?php echo html::escapeHTML($productsList->url) ?>"><?php echo html::escapeHTML($productsList->title) ?></a></h2>
		<?php # fin Okatea : affichage du titre ?>

		<?php # début Okatea : affichage du sous-titre
		if ($productsList->subtitle != ''  && $okt->catalog->config->fields['subtitle'] != 0) : ?>
			<p class="product-subtitle"><strong><?php echo html::escapeHTML($productsList->subtitle) ?></strong></p>
		<?php endif; # fin Okatea : affichage du sous-titre ?>

		<?php # début Okatea : affichage du contenu ?>
		<div class="product-content">

			<?php # début Okatea : affichage image
			$prod_image = $productsList->getFirstImageInfo();
			if (!empty($prod_image) && isset($prod_image['square_url'])) : ?>

			<div class="modal-box">
				<a href="<?php echo $prod_image['img_url']?>"
				title="<?php echo util::escapeAttrHTML($productsList->title) ?>"
				class="modal"><img src="<?php
				echo $prod_image['square_url']?>"
				<?php echo $prod_image['square_attr']?>
				alt="<?php echo util::escapeAttrHTML((isset($prod_image['alt']) ? $prod_image['alt'] : $productsList->title)) ?>" /></a>
			</div>
			<?php endif; # fin Okatea : affichage image ?>

			<?php # début Okatea : si il y a une mention "promotion" à afficher
			if ($okt->catalog->config->fields['promo'] && $productsList->is_promo) : ?>
				<p class="promo">Promotion</p>
			<?php endif; # fin Okatea : si il y a une mention "promotion" à afficher ?>

			<?php # début Okatea : si il y a une mention "nouveauté" à afficher
			if ($okt->catalog->config->fields['nouvo'] && $productsList->is_nouvo) : ?>
				<p class="nouveaute">Nouveauté</p>
			<?php endif; # fin Okatea : si il y a une mention "nouveauté" à afficher ?>

			<?php # début Okatea : si il y a une mention "favoris" à afficher
			if ($okt->catalog->config->fields['favo'] && $productsList->is_favo) : ?>
				<p class="favoris">Favoris</p>
			<?php endif; # fin Okatea : si il y a une mention "favoris" à afficher ?>

			<?php # début Okatea : affichage texte tronqué
			if ($okt->catalog->config->public_truncat_char > 0) : ?>

				<?php if ($productsList->content_short != ''  && $okt->catalog->config->fields['content_short'] != 0) : ?>

					<p><?php echo $productsList->content_short ?>… <a href="<?php echo html::escapeHTML($productsList->url) ?>"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_catalog_more_details_on_%s'),$productsList->title)) ?>"><?php _e('m_catalog_more_details') ?></a></p>

				<?php elseif ($productsList->content != '') : ?>

					<p><?php echo $productsList->content ?>… <a href="<?php echo html::escapeHTML($productsList->url) ?>"
					title="<?php echo util::escapeAttrHTML(sprintf(__('m_catalog_more_details_on_%s'),$productsList->title)) ?>"><?php _e('m_catalog_more_details') ?></a></p>

				<?php endif; ?>

			<?php endif; # fin Okatea : affichage texte tronqué ?>


			<?php # début Okatea : affichage texte pas tronqué
			if (!$okt->catalog->config->public_truncat_char) : ?>

				<?php if ($productsList->content_short != ''  && $okt->catalog->config->fields['content_short'] != 0) : ?>

					<p><?php echo $productsList->content_short ?></p>

				<?php elseif ($productsList->content != '') : ?>

					<p><?php echo $productsList->content ?></p>

				<?php endif; ?>

			<?php endif; # fin Okatea : affichage texte pas tronqué ?>

		</div><!-- .product-content -->
		<?php # fin Okatea : affichage du contenu ?>

	</div><!-- .product -->
	<?php # fin Okatea : affichage d'un produit ?>

	<?php endwhile;
	# fin Okatea : boucle sur la liste des produits ?>

</div><!-- #products_list -->

<?php # début Okatea : affichage pagination
if ($productsList->numPages > 1) : ?>

<ul class="pagination">
	<?php echo $productsList->pager->getLinks(); ?>
</ul>

<?php endif;
# fin Okatea : affichage pagination ?>

<p class="scrollTop-wrapper"><a href="#" class="scrollTop"><?php _e('c_c_action_Go_top') ?></a></p>

<?php endif;
# fin Okatea : si il y a des produits on affiche la liste ?>
