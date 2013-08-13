
	<?php # début Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message
	if ($rsItems->isEmpty() && $rsSubGalleries->isEmpty()) : ?>

	<p><em><?php _e('m_galleries_no_item')?></em></p>

	<?php endif; # fin Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message ?>


	<?php # début Okatea : si il y a des éléments on affiche la liste de ces éléments
	if (!$rsItems->isEmpty()) : ?>

	<div id="items_list">

		<?php # début Okatea : boucle sur la liste des éléments
		while ($rsItems->fetch()) : ?>

		<?php # début Okatea : affichage d'un élément ?>
		<div class="item">

			<?php # début Okatea : affichage du titre ?>
			<h2 class="item-title"><a href="<?php echo html::escapeHTML($rsItems->getItemUrl()) ?>"><?php echo html::escapeHTML($rsItems->title) ?></a></h2>
			<?php # fin Okatea : affichage du titre ?>

			<?php # début Okatea : affichage image
			$item_image = $rsItems->getImagesInfo();
			if (!empty($item_image) && isset($item_image['min_url'])) : ?>

				<?php if ($okt->galleries->config->dysplay_clic_items_image == 'details') : ?>
				<p>
					<a href="<?php echo html::escapeHTML($rsItems->getItemUrl()) ?>"
					title="<?php echo util::escapeAttrHTML($rsItems->title) ?>"><img
					src="<?php echo $item_image['min_url']?>"
					<?php echo $item_image['min_attr']?>
					alt="<?php echo util::escapeAttrHTML((isset($item_image['alt']) ? $item_image['alt'] : $rsItems->title)) ?>" /></a>
				</p>
				<?php else : ?>
				<p class="modal-box">
					<a href="<?php echo $item_image['img_url']?>"
					title="<?php echo util::escapeAttrHTML($rsItems->title) ?>"
					class="modal" rel="gallery"><img
					src="<?php echo $item_image['min_url']?>"
					<?php echo $item_image['min_attr']?>
					alt="<?php echo util::escapeAttrHTML((isset($item_image['alt']) ? $item_image['alt'] : $rsItems->title)) ?>" /></a>
				</p>
				<?php endif; ?>

			<?php endif; # fin Okatea : affichage image ?>

		</div><!-- .item -->
		<?php # fin Okatea : affichage d'un élément ?>

		<?php endwhile; # fin Okatea : boucle sur la liste des éléments ?>

	</div><!-- #items_list -->

	<?php endif; # début Okatea : si il y a des éléments on affiche la liste de ces éléments ?>

