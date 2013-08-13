
<?php
$okt->page->galleriffic($okt->galleries->config->galleriffic_options);
$okt->page->js->addReady("
	$('div.navigation').css({'width' : '200px', 'float' : 'left'});
	$('div.content').css('display', 'block');
");
?>

<?php # début Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message
if ($rsItems->isEmpty() && $rsSubGalleries->isEmpty()) : ?>

<p><em><?php _e('m_galleries_no_item')?></em></p>

<?php endif; # fin Okatea : si il n'y a PAS d'élément à afficher on peut indiquer un message ?>


<?php # début Okatea : si il y a des éléments on affiche la liste de ces éléments
if (!$rsItems->isEmpty()) : ?>
<div id="galleriffic_container">
	<div id="gallery" class="content">
		<div id="controls" class="controls"></div>
		<div class="slideshow-container">
			<div id="loading" class="loader"></div>
			<div id="slideshow" class="slideshow"></div>
		</div>
		<div id="caption" class="caption-container"></div>
	</div>
	<div id="thumbs" class="navigation">
		<ul class="thumbs noscript">
		<?php # début Okatea : boucle sur la liste des éléments
		while ($rsItems->fetch()) : ?>
		<?php # début Okatea : affichage d'un élément ?>

			<li>
			<?php # début Okatea : affichage image
			$item_image = $rsItems->getImagesInfo();
			if (!empty($item_image) && isset($item_image['min_url'])) : ?>

				<a class="thumb" href="<?php echo $item_image['img_url']?>" title="<?php echo util::escapeAttrHTML($rsItems->title) ?>">
					<img src="<?php echo $item_image['square_url']?>"
					alt="<?php echo util::escapeAttrHTML((isset($item_image['alt']) ? $item_image['alt'] : $rsItems->title)) ?>" />
				</a>
				<div class="caption">
					<div class="image-title"><?php echo util::escapeAttrHTML($rsItems->title) ?></div>
					<div class="image-desc"><?php echo util::escapeAttrHTML($rsItems->legend) ?></div>
				</div>

			<?php endif; # fin Okatea : affichage image ?>

		</li>
		<?php # fin Okatea : affichage d'un élément ?>

		<?php endwhile; # fin Okatea : boucle sur la liste des éléments ?>
		</ul>
	</div>
</div>
<?php endif; # début Okatea : si il y a des éléments on affiche la liste de ces éléments ?>