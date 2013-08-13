
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout de la CSS spécifique au module
$okt->page->css->addFile(OKT_THEME.'/modules/galleries/styles.css');
# fin Okatea : ajout de la CSS spécifique au module ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_COMMON_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout du modal
$okt->page->applyLbl($okt->galleries->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<?php # début Okatea : affichage du fil d'ariane
if ($okt->galleries->config->enable_ariane) :
$okt->page->breadcrumb->setHtmlSeparator(' &rsaquo; ');
$okt->page->breadcrumb->display('<p id="ariane"><em>'.__('c_c_user_you_are_here').'</em> %s</p>');
endif; # fin Okatea : affichage du fil d'ariane ?>


<p class="right italic"><a href="<?php echo html::escapeHTML($rsItem->getGalleryUrl()) ?>">Retour</a></p>

<div id="item">

	<?php # début Okatea : affichage du titre ?>
	<!-- <h1 class="item-title"><?php echo html::escapeHTML($rsItem->title) ?></h1> -->
	<?php # fin Okatea : affichage du titre ?>

	<?php # début Okatea : affichage image
	if (!empty($rsItem->image) && isset($rsItem->image['min_url'])) : ?>

	<p class="modal-box">
		<a href="<?php echo $rsItem->image['img_url']?>"
		title="<?php echo util::escapeAttrHTML($rsItem->title) ?>"
		class="modal"><img
		src="<?php echo $rsItem->image['min_url'] ?>"
		<?php echo $rsItem->image['min_attr']?>
		alt="<?php echo util::escapeAttrHTML((isset($rsItem->image['alt']) ? $rsItem->image['alt'] : $rsItem->title)) ?>" /></a>
	</p>

	<?php endif; # fin Okatea : affichage image ?>

	<?php # début : si il y a une légende à afficher
	if ($rsItem->legend != '') : ?>
		<?php echo $rsItem->legend ?>
	<?php endif; # fin : si il y a une légende à afficher ?>

	<?php # début : si il y a un auteur à afficher
	if ($rsItem->author != '') : ?>
	<p><span class="label"><?php _e('m_galleries_Author')?> :</span> <?php echo html::escapeHTML($rsItem->author) ?></p>
	<?php endif; # fin : si il y a un auteur à afficher ?>

	<?php # début : si il y a un lieu à afficher
	if ($rsItem->place != '') : ?>
	<p><span class="label"><?php _e('m_galleries_Place')?> :</span> <?php echo html::escapeHTML($rsItem->place) ?></p>
	<?php endif; # fin : si il y a un lieu à afficher ?>

</div><!-- #item -->

