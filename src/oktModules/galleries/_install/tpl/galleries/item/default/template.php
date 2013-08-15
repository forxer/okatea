
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__.'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


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


<?php # début Okatea : affichage du fil d'ariane
if ($okt->galleries->config->enable_ariane) :
$okt->page->breadcrumb->setHtmlSeparator(' &rsaquo; ');
$okt->page->breadcrumb->display('<p id="ariane"><em>'.__('c_c_user_you_are_here').'</em> %s</p>');
endif; # fin Okatea : affichage du fil d'ariane ?>


<p class="goBack"><a href="<?php echo html::escapeHTML($rsItem->getGalleryUrl()) ?>">Retour</a></p>


<div id="item">

	<?php # début Okatea : affichage du titre ?>
	<!-- <h1 class="item-title"><?php echo html::escapeHTML($rsItem->title) ?></h1> -->
	<?php # fin Okatea : affichage du titre ?>

	<?php # début Okatea : affichage image
	if (!empty($rsItem->image) && isset($rsItem->image['img_url'])) : ?>

	<p>
		<img src="<?php echo $rsItem->image['img_url'] ?>"
		<?php echo $rsItem->image['img_attr']?>
		alt="<?php echo util::escapeAttrHTML((isset($rsItem->image['alt']) && isset($rsItem->image['alt'][$okt->user->language]) ? $rsItem->image['alt'][$okt->user->language] : $rsItem->title)) ?>" />
	</p>

	<?php endif; # fin Okatea : affichage image ?>

	<?php # début : si il y a un contenu à afficher
	if ($rsItem->content != '') : ?>
		<?php echo $rsItem->content ?>
	<?php endif; # fin : si il y a un contenu à afficher ?>

</div><!-- #item -->


<p class="scrollTop-wrapper"><a href="#" class="scrollTop"><?php _e('c_c_action_Go_top') ?></a></p>

