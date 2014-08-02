
<?php 
# début Okatea : ce template étend le layout
$view->extend('Layout');
# fin Okatea : ce template étend le layout ?>


<?php 
# début Okatea : ajout de la CSS spécifique au module
$okt->page->css->addFile($okt->theme->url . '/modules/catalog/styles.css');
# fin Okatea : ajout de la CSS spécifique au module ?>


<?php 
# début Okatea : ajout de jQuery
$okt->page->js->addFile($okt['public_url'] . '/components/jquery/dist/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php 
# début Okatea : ajout du JS de scrollToTopOfPage
$okt->page->js->addFile($okt['public_url'] . '/plugins/easing/jquery.easing.min.js');
$okt->page->js->addFile($okt['public_url'] . '/plugins/scrollToTopOfPage/jquery.scrollToTopOfPage.min.js');
$okt->page->js->addReady('
	$("a.scrollTop").scrollToTopOfPage({
		"top": 300,					// hauteur avant affichage du lien
		"duration": 1700,			// durée animation retour en haut
		"easing": "easeOutQuint"	// type animation retour en haut
	});
');
# fin Okatea : ajout du JS de scrollToTopOfPage ?>


<?php 
# début Okatea : ajout du modal
$okt->page->applyLbl($okt->catalog->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<div id="product">
	<div id="product-header" class="two-cols">

		<?php # début Okatea : affichage du titre du produit ?>
		<h2 id="product-title" class="col"><?php echo $view->escape($product->title) ?></h2>
		<?php # fin Okatea : affichage du titre du produit ?>

		<p class="col right">
			<strong>
			<?php 
# si les prix promotionnels sont activés et qu'il y en as un
			if ($okt->catalog->config->fields['promo'] && $product->is_promo && $product->price_promo > 0)
			:
				?>

				<del><?php echo Okatea\Tao\Misc\Utilities::formatNumber($product->price) ?> €</del>
				<strong><?php echo Okatea\Tao\Misc\Utilities::formatNumber($product->price_promo) ?> €</strong>

			
			<?php 
# sinon on affiche le prix normal
			elseif ($product->price > 0)
			:
				?>

				<strong><?php echo Okatea\Tao\Misc\Utilities::formatNumber($product->price) ?> €</strong>

			<?php endif; ?>
		</strong>
		</p>

		<?php 
# début Okatea : affichage du sous-titre
		if ($product->subtitle != '' && $okt->catalog->config->fields['subtitle'] != 0)
		:
			?>
			<p class="product-subtitle">
			<strong><?php echo $view->escape($product->subtitle) ?></strong>
		</p>
		<?php endif; # fin Okatea : affichage du sous-titre ?>

	</div>
	<!-- #product-header -->

	<div id="product-body">

		<?php 
# début Okatea : si les images sont activées
		if ($okt->catalog->config->images['enable'] && ! empty($product->images))
		:
			?>
		<p id="product-images" class="modal-box">

			<?php 
# début Okatea : boucle sur les images
			foreach ($product->images as $i => $image)
			:
				?>

				<?php 
# si c'est la première image on affiche la miniature
				if ($i == 1 && isset($image['min_url']))
				:
					?>

				<a href="<?php echo $image['img_url'] ?>"
				title="<?php echo $view->escapeHtmlAttr($product->title) ?>, image <?php echo $i ?>"
				class="modal center" rel="product-images"> <img
				src="<?php echo $image['min_url'] ?>"
				<?php echo $image['min_attr']?>
				alt="<?php echo $view->escapeHtmlAttr((isset($image['alt']) ? $image['alt'] : $product->title)) ?>" /></a>

			<br />

				
				<?php 
# si c'est pas la première image on affiche le square
				elseif (isset($image['square_url']))
				:
					?>

				<a href="<?php echo $image['img_url'] ?>"
				title="<?php echo $view->escapeHtmlAttr($product->title) ?>, image <?php echo $i ?>"
				class="modal" rel="product-images"> <img
				src="<?php echo $image['square_url'] ?>"
				<?php echo $image['square_attr']?>
				alt="<?php echo $view->escapeHtmlAttr((isset($image['alt']) ? $image['alt'] : $product->title)) ?>" /></a>

				<?php endif; ?>

			<?php endforeach; # fin Okatea : boucle sur les images ?>

		</p>
		<!-- #product-images -->
		<?php endif; # fin Okatea : si les images sont activées ?>


		<?php # début Okatea : affichage du contenu ?>
		<div id="product-content">

			<?php 
# début Okatea : si il y a une mention "promotion" à afficher
			if ($okt->catalog->config->fields['promo'] && $product->is_promo)
			:
				?>
				<p class="promo">Promotion</p>
			<?php endif; # fin Okatea : si il y a une mention "promotion" à afficher ?>


			<?php 
# début Okatea : si il y a une mention "nouveauté" à afficher
			if ($okt->catalog->config->fields['nouvo'] && $product->is_nouvo)
			:
				?>
				<p class="nouveaute">Nouveauté</p>
			<?php endif; # fin Okatea : si il y a une mention "nouveauté" à afficher ?>


			<?php 
# début Okatea : si il y a une mention "favoris" à afficher
			if ($okt->catalog->config->fields['favo'] && $product->is_favo)
			:
				?>
				<p class="favoris">Favoris</p>
			<?php endif; # fin Okatea : si il y a une mention "favoris" à afficher ?>

			<?php echo $product->content?>
		</div>
		<!-- #product-content -->
		<?php # fin Okatea : affichage du contenu ?>

	</div>
	<!-- #product-body -->

	<div id="product-footer">

		<?php 
# début Okatea : si les fichiers sont activées
		if ($okt->catalog->config->files['enable'])
		:
			?>
		<div id="product-files" class="three-cols">

			<?php 
# début Okatea : boucle sur les fichiers
			foreach ($product->files as $i => $file)
			:
				?>

			<p class="col">
				<a href="<?php echo $file['url'] ?>"><img
					src="<?php echo $okt['public_url'].'/img/media/'.$file['type'].'.png' ?>"
					alt="<?php echo $view->escape($file['title']) ?>" /></a>
			<?php echo $view->escape($file['title']) ?> (<?php echo $file['mime'] ?>)
			- <?php echo Okatea\Tao\Misc\Utilities::l10nFileSize($file['size']) ?></p>

			<?php endforeach; # fin Okatea : boucle sur les fichiers ?>

		</div>
		<!-- #product-files -->
		<?php endif; # fin Okatea : si les fichiers sont activées ?>

	</div>
	<!-- #product-footer -->

</div>
<!-- #product -->


<p class="scrollTop-wrapper">
	<a href="#" class="scrollTop"><?php _e('c_c_action_Go_top') ?></a>
</p>

