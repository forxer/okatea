
<?php use Tao\Misc\Utilities as util; ?>

<?php $this->extend('layout'); ?>


<?php # début Okatea : ajout de la CSS spécifique au module
$okt->page->css->addFile(OKT_THEME.'/modules/diary/styles.css');
# fin Okatea : ajout de la CSS spécifique au module ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout du modal
$okt->page->applyLbl($okt->diary->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<div id="diary">

	<?php # début Okatea : affichage du titre de l'évènement ?>
	<h2><?php echo html::escapeHTML($rsEvent->title) ?></h2>
	<?php # fin Okatea : affichage du titre de l'évènement ?>


	<?php # début Okatea : affichage de la date de l'évènement ?>
	<p><?php echo dt::dt2str(__('%A, %B %d, %Y'),$rsEvent->date) ?>
	<?php if (!empty($rsEvent->date_end)) : ?> au <?php echo dt::dt2str(__('%A, %B %d, %Y'),$rsEvent->date_end) ?><?php endif; ?></p>
	<?php # fin Okatea : affichage de la date de l'évènement ?>


	<?php # début Okatea : si les images sont activées
	if ($okt->diary->config->images['enable'] && !empty($rsEvent->images)) : ?>
	<p id="diary-images" class="modal-box">

		<?php # début Okatea : boucle sur les images
		foreach ($rsEvent->images as $i=>$image) : ?>

			<?php # si c'est la première image on affiche la miniature
			if ($i == 1 && isset($image['min_url'])) : ?>

			<a href="<?php echo $image['img_url'] ?>"
			title="<?php echo util::escapeAttrHTML($rsEvent->title) ?>, image <?php echo $i ?>" class="modal center" rel="images">
			<img src="<?php echo $image['min_url'] ?>"
			<?php echo $image['min_attr'] ?>
			alt="<?php echo util::escapeAttrHTML((isset($image['alt']) ? $image['alt'] : $rsEvent->title)) ?>" /></a>

			<br />

			<?php # si c'est pas la première image on affiche le square
			elseif (isset($image['square_url'])) : ?>

			<a href="<?php echo $image['img_url'] ?>"
			title="<?php echo util::escapeAttrHTML($rsEvent->title) ?>, image <?php echo $i ?>" class="modal" rel="images">
			<img src="<?php echo $image['square_url'] ?>"
			<?php echo $image['square_attr'] ?>
			alt="<?php echo util::escapeAttrHTML((isset($image['alt']) ? $image['alt'] : $rsEvent->title)) ?>" /></a>

			<?php endif; ?>

		<?php endforeach; # fin Okatea : boucle sur les images ?>

	</p><!-- #diary-images -->
	<?php endif; # fin Okatea : si les images sont activées ?>


	<?php # début Okatea : affichage de la disponibilité ?>
		<?php if (!empty($rsEvent->disponibility)) : ?>
		<p>Disponibilité :
			<?php if ($rsEvent->disponibility == 1) : ?>
				Option prise
			<?php elseif ($rsEvent->disponibility == 2) : ?>
				Indisponible
			<?php endif; ?>
		</p>
		<?php endif; ?>
	<?php # fin Okatea : affichage de la disponibilité ?>

	<?php # début Okatea : affichage du contenu ?>
	<div id="diary-content">
		<?php echo $rsEvent->description ?>
	</div><!-- #diary-content -->
	<?php # fin Okatea : affichage du contenu ?>


	<?php # début Okatea : si les fichiers sont activées
	if ($okt->diary->config->files['enable']) : ?>
	<div id="diary-files" class="three-cols">

		<?php # début Okatea : boucle sur les fichiers
		foreach ($rsEvent->files as $i=>$file) : ?>

		<p class="col"><a href="<?php echo $file['url'] ?>"><img src="<?php echo OKT_PUBLIC_URL.'/img/media/'.$file['type'].'.png' ?>" alt="<?php echo html::escapeHTML($file['title']) ?>" /></a>
		<?php echo html::escapeHTML($file['title']) ?> (<?php echo $file['mime'] ?>)
		- <?php echo util::l10nFileSize($file['size']) ?></p>

		<?php endforeach; # fin Okatea : boucle sur les fichiers ?>

	</div><!-- #diary-files -->
	<?php endif; # fin Okatea : si les fichiers sont activées ?>


</div><!-- #diary -->
