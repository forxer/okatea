<?php /*

Utilisation de ce template d'encart :
------------------------------------------------------------

Vous devez sélectionner ce template dans la configuration.

Puis coller le code pour effectuer le rendu de ce template,
là où vous souhaitez afficher l'encart.

Il est possible de passer un tableau de paramètres
pour personnaliser la liste des pages à afficher.

Les paramètres par défaut sont les suivants :

	'active' : seulement les pages visibles
	'limit' : limitation du nombre de pages à 10
	'language' dans la langue de l'utilisateur en cours


Exemples :

- Pour afficher l'encart avec les paramètres par défaut :

	<?php # début Okatea : affichage encart pages
	echo $okt->tpl->render($okt->Pages->getInsertTplPath());
	# fin Okatea : affichage encart pages ?>


- Pour afficher l'encart avec seulement 5 pages dans l'encart :

	<?php # début Okatea : affichage encart pages
	echo $okt->tpl->render($okt->Pages->getInsertTplPath(), array(
		'aParams' => array(
			'limit' => 5,
		)
	)); # fin Okatea : affichage encart pages ?>


- Pour afficher l'encart avec seulement 5 pages dans l'encart et ce en anglais :

	<?php # début Okatea : affichage encart pages
	echo $okt->tpl->render($okt->Pages->getInsertTplPath(), array(
		'aParams' => array(
			'limit' => 5,
			'language' => 'en'
		)
	)); # fin Okatea : affichage encart pages ?>


*/ ?>

<?php # début Okatea : traitements avant affichage

	# paramètres par défaut
	$aDefaultParams = array(
		'active' => 1, 		# pages visibles
		'limit' => 10, 		# limitation du nombre de pages
		'language' => $okt->user->language, # langue de l'utilisateur en cours
	);

	# prise en compte des éventuels paramètres personnalisés
	$aParams = !empty($aParams) ? array_merge($aDefaultParams, $aParams) : $aDefaultParams;

	# récupération des pages pour l'encart
	$rsInsertPages = $okt->Pages->getPages($aParams, $okt->Pages->config->insert_truncat_char);

# fin Okatea : traitements avant affichage ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__.'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile($okt->options->public_url.'/components/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout du modal
$okt->page->applyLbl($okt->Pages->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<?php # début Okatea : si il n'y a PAS de page à afficher on peut indiquer un message
if ($rsInsertPages->isEmpty()) : ?>

<p><em><?php _e('m_pages_there_is_no_pages') ?></em></p>

<?php endif; # fin Okatea : si il n'y a PAS de page à afficher on peut indiquer un message ?>


<?php # début Okatea : si il y a des page à afficher
if (!$rsInsertPages->isEmpty()) : ?>
<div id="pages_list_insert">

	<?php # début Okatea : boucle sur la liste des pages
	while ($rsInsertPages->fetch()) : ?>

	<?php # début Okatea : affichage d'une page ?>
	<div class="page <?php echo $rsInsertPages->odd_even ?>">

		<?php # début Okatea : affichage du titre ?>
		<h2 class="page-title"><a href="<?php echo $view->escapeHtmlAttr($rsInsertPages->url) ?>"><?php echo $view->escape($rsInsertPages->title) ?></a></h2>
		<?php # fin Okatea : affichage du titre ?>

		<?php # début Okatea : affichage du sous-titre
		if ($rsInsertPages->subtitle != '') : ?>
		<p class="page-subtitle"><strong><?php echo $view->escape($rsInsertPages->subtitle) ?></strong></p>
		<?php endif; # fin Okatea : affichage du sous-titre ?>

		<?php # début Okatea : affichage du contenu ?>
		<div class="page-content">

		<?php # début Okatea : si on as PAS accès en lecture à la page
		if (!$rsInsertPages->isReadable()) : ?>

			<p><?php _e('m_pages_restricted_access') ?></p>

		<?php endif; # début Okatea : si on as PAS accès en lecture à la page ?>


		<?php # début Okatea : si on as accès en lecture à la page
		if ($rsInsertPages->isReadable()) : ?>

			<?php # début Okatea : si les images sont activées
			if ($okt->Pages->config->images['enable'] && !empty($rsInsertPages->images)) : ?>
			<p class="page-images modal-box">

				<?php # début Okatea : boucle sur les images
				foreach ($rsInsertPages->images as $i=>$image) : ?>

					<?php # début Okatea : affichage de la première image uniquement, et ce au format square
					if ($i == 1 && isset($image['min_url'])) : ?>

					<a href="<?php echo $image['img_url'] ?>"
					title="<?php echo $view->escapeHtmlAttr((isset($image['title']) && isset($image['title'][$okt->user->language]) ? $image['title'][$okt->user->language] : $rsInsertPages->title)) ?>"
					class="modal center" rel="page-images">
					<img src="<?php echo $image['square_url'] ?>"
					<?php echo $image['square_attr'] ?>
					alt="<?php echo $view->escapeHtmlAttr((isset($image['alt']) && isset($image['alt'][$okt->user->language]) ? $image['alt'][$okt->user->language] : $rsInsertPages->title)) ?>" /></a>

					<?php endif; # fin Okatea : affichage de la première image uniquement, et ce au format square ?>

				<?php endforeach; # fin Okatea : boucle sur les images ?>

			</p><!-- .page-images -->
			<?php endif; # fin Okatea : si les images sont activées ?>


			<?php # début Okatea : affichage texte tronqué
			if ($okt->Pages->config->insert_truncat_char > 0) : ?>

			<p><?php echo $rsInsertPages->content ?>…</p>

			<p class="read-more-link-wrapper"><a href="<?php echo $view->escape($rsInsertPages->url) ?>"
			title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_pages_read_more_of_%s'), $rsInsertPages->title)) ?>"
			class="read-more-link" rel="nofollow"><?php _e('m_pages_read_more') ?></a></p>

			<?php endif; # fin Okatea : affichage texte tronqué ?>


			<?php # début Okatea : affichage texte pas tronqué
			if (!$okt->Pages->config->insert_truncat_char) : ?>

			<?php echo $rsInsertPages->content ?>

			<?php endif; # fin Okatea : affichage texte pas tronqué ?>

		<?php endif; # fin Okatea : si on as accès en lecture à la page ?>

		</div><!-- .page-content -->
		<?php # fin Okatea : affichage du contenu ?>

	</div><!-- .page -->
	<?php # fin Okatea : affichage d'une page ?>

	<?php endwhile; # fin Okatea : boucle sur la liste des pages ?>

</div><!-- #pages_list -->

<?php endif; # fin Okatea : si il y a des page à afficher ?>
