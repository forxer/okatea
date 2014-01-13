
<?php # début Okatea : ce template étend le layout
$view->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__.'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile($okt->options->public_url.'/components/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout du JS de scrollToTopOfPage
$okt->page->js->addFile($okt->options->public_url.'/plugins/easing/jquery.easing.min.js');
$okt->page->js->addFile($okt->options->public_url.'/plugins/scrollToTopOfPage/jquery.scrollToTopOfPage.min.js');
$okt->page->js->addReady('
	$("a.scrollTop").scrollToTopOfPage({
		"top": 300,					// hauteur avant affichage du lien
		"duration": 1700,			// durée animation retour en haut
		"easing": "easeOutQuint"	// type animation retour en haut
	});
');
# fin Okatea : ajout du JS de scrollToTopOfPage ?>


<?php # début Okatea : ajout du modal
$okt->page->applyLbl($okt->News->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<div id="post">
	<div id="post-header">

		<?php # début Okatea : affichage du titre de l'article ?>
		<!-- <h1 id="post-title"><?php echo $view->escape($rsPost->title) ?></h1> -->
		<?php # fin Okatea : affichage du titre ?>

		<?php # début Okatea : affichage du sous-titre
		if ($rsPost->subtitle != '') : ?>
		<p id="post-subtitle"><strong><?php echo $view->escape($rsPost->subtitle) ?></strong></p>
		<?php endif; # fin Okatea : affichage du sous-titre ?>

		<?php # début Okatea : affichage des infos
		if ($okt->News->config->public_display_date || $okt->News->config->public_display_author || $okt->News->config->categories['enable']) : ?>
		<p id="post-infos">
			<?php _e('m_news_published') ?>

			<?php  # début Okatea : affichage date de l'article
			if ($okt->News->config->public_display_date) : ?>
			<?php printf(__('m_news_on_%s'),dt::dt2str(__('%A, %B %d, %Y, %H:%M'),$rsPost->created_at)) ?>
			<?php endif; # fin Okatea : affichage date de l'article ?>

			<?php # début Okatea : affichage l'auteur de l'article
			if ($okt->News->config->public_display_author) : ?>
			<?php printf(__('m_news_by_%s'),$view->escape($rsPost->author)) ?>
			<?php endif; # fin Okatea : affichage l'auteur de l'article ?>

			<?php # début Okatea : affichage rubrique
			if ($okt->News->config->categories['enable'] && $rsPost->category_title) : ?>
			<?php printf(__('m_news_in_%s'),'<a href="'.$view->escape($rsPost->category_url).'">'.$view->escape($rsPost->category_title).'</a>') ?>
			<?php endif; # fin Okatea : affichage rubrique ?>

		</p><!-- #post-infos -->
		<?php endif;  # fin Okatea : affichage des infos ?>

	</div><!-- #post-header -->

	<div id="post-body">

		<?php # début Okatea : si les images sont activées
		if ($okt->News->config->images['enable'] && !empty($rsPost->images)) : ?>
		<p id="post-images" class="modal-box">

			<?php # début Okatea : boucle sur les images
			foreach ($rsPost->images as $i=>$image) : ?>

				<?php # si c'est la première image on affiche la miniature
				if ($i == 1 && isset($image['min_url'])) : ?>

				<a href="<?php echo $image['img_url'] ?>" title="<?php echo $view->escapeHtmlAttr($rsPost->title) ?>, image <?php echo $i ?>" class="modal center" rel="news-images">
				<img src="<?php echo $image['min_url'] ?>" <?php echo $image['min_attr'] ?>
				alt="<?php echo $view->escapeHtmlAttr((isset($image['alt'][$okt->user->language]) ? $image['alt'][$okt->user->language] : $rsPost->title)) ?>" /></a>

				<br />

				<?php # si c'est pas la première image on affiche le square
				elseif (isset($image['square_url'])) : ?>

				<a href="<?php echo $image['img_url'] ?>" title="<?php echo $view->escapeHtmlAttr($rsPost->title) ?>, image <?php echo $i ?>" class="modal" rel="news-images">
				<img src="<?php echo $image['square_url'] ?>" <?php echo $image['square_attr'] ?>
				alt="<?php echo $view->escapeHtmlAttr((isset($image['alt'][$okt->user->language]) ? $image['alt'][$okt->user->language] : $rsPost->title)) ?>" /></a>

				<?php endif; ?>

			<?php endforeach; # fin Okatea : boucle sur les images ?>

		</p><!-- #post-images -->
		<?php endif; # fin Okatea : si les images sont activées ?>


		<?php # début Okatea : affichage du contenu ?>
		<div id="post-content">
			<?php echo $rsPost->content ?>
		</div><!-- #post-content -->
		<?php # fin Okatea : affichage du contenu ?>

	</div><!-- #post-body -->

	<div id="post-footer">

		<?php # début Okatea : si les fichiers sont activées
		if ($okt->News->config->files['enable']) : ?>
		<div id="post-files" class="three-cols">

			<?php # début Okatea : boucle sur les fichiers
			foreach ($rsPost->files as $i=>$file) : ?>

			<p class="col"><a href="<?php echo $file['url'] ?>"><img src="<?php echo $okt->options->public_url.'/img/media/'.$file['type'].'.png' ?>" alt="" /></a>
			<?php echo !empty($file['title'][$okt->user->language]) ? $view->escape($file['title'][$okt->user->language]) : ''; ?> (<?php echo $file['mime'] ?>)
			- <?php echo Tao\Misc\Utilities::l10nFileSize($file['size']) ?></p>

			<?php endforeach; # fin Okatea : boucle sur les fichiers ?>

		</div><!-- #post-files -->
		<?php endif; # fin Okatea : si les fichiers sont activées ?>

	</div><!-- #post-footer -->

</div><!-- #post -->

<p class="scrollTop-wrapper"><a href="#" class="scrollTop"><?php _e('c_c_action_Go_top') ?></a></p>

