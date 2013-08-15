
<?php # début Okatea : récupération des news pour l'encart
$rsInsertPosts = $okt->news->getPosts(array(
	'active' => 1, 		# articles visibles
//	'selected' => 1, 	# articles sélectionnés
//	'created_after' => date('Y-m-d H:i:s',strtotime('-1 month')), # articles créés il y a moins d'un mois
//	'created_before' => date('Y-m-d H:i:s',strtotime('-1 month')), # articles créés il y a plus d'un mois
	'limit' => 10, 		# limitation du nombre d'articles
	'language' => $okt->user->language, # langue de l'utilisateur en cours
), $okt->news->config->insert_truncat_char);
# fin Okatea : récupération des news pour l'encart ?>


<?php # début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__.'/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php # début Okatea : si il n'y a PAS d'actualité à afficher on peut indiquer un message
if ($rsInsertPosts->isEmpty()) : ?>

<p><em><?php _e('m_news_there_is_no_post') ?></em></p>

<?php endif; # fin Okatea : si il n'y a PAS d'actualité à afficher on peut indiquer un message ?>


<?php # début Okatea : si il y a des actualités on affiche la liste
if (!$rsInsertPosts->isEmpty()) : ?>

<div id="news_list_insert">

	<?php # début Okatea : boucle sur la liste des actualités
	while ($rsInsertPosts->fetch()) : ?>

	<?php # début Okatea : affichage d'un article ?>
	<div class="post <?php echo $rsInsertPosts->odd_even ?>">

		<?php # début Okatea : affichage du titre ?>
		<p class="post-title"><a href="<?php echo html::escapeHTML($rsInsertPosts->url) ?>"><?php echo html::escapeHTML($rsInsertPosts->title) ?></a></p>
		<?php # fin Okatea : affichage du titre ?>


		<?php # début Okatea : affichage du contenu ?>
		<div class="post-content">

		<?php # début Okatea : si on as PAS accès en lecture à l'article
		if (!$rsInsertPosts->isReadable()) : ?>

			<p><?php _e('m_news_restricted_access') ?></p>

		<?php endif; # début Okatea : si on as PAS accès en lecture à l'article ?>


		<?php # début Okatea : si on as accès en lecture à l'article
		if ($rsInsertPosts->isReadable()) : ?>

			<?php # début Okatea : affichage image
			$post_image = $rsInsertPosts->getFirstImageInfo();
			if (!empty($post_image) && isset($post_image['square_url'])) : ?>

			<div class="modal-box">
				<a href="<?php echo $post_image['img_url']?>"
				title="<?php echo util::escapeAttrHTML($rsInsertPosts->title) ?>"
				class="modal"><img src="<?php echo $post_image['square_url'] ?>"
				<?php echo $post_image['square_attr']?>
				alt="<?php echo util::escapeAttrHTML((isset($post_image['alt'][$okt->user->language]) ? $post_image['alt'][$okt->user->language] : $rsInsertPosts->title)) ?>" /></a>
			</div>
			<?php endif; # fin Okatea : affichage image ?>


			<?php # début Okatea : affichage texte tronqué
			if ($okt->news->config->insert_truncat_char > 0) : ?>

			<p><?php echo $rsInsertPosts->content ?>…</p>

			<p class="read-more-link-wrapper"><a href="<?php echo html::escapeHTML($rsInsertPosts->url) ?>"
			title="<?php echo util::escapeAttrHTML(sprintf(__('m_news_read_more_of_%s'),$rsInsertPosts->title)) ?>"
			class="read-more-link" rel="nofollow"><?php _e('m_news_read_more') ?></a></p>

			<?php endif; # fin Okatea : affichage texte tronqué ?>


			<?php # début Okatea : affichage texte pas tronqué
			if (!$okt->news->config->insert_truncat_char) : ?>

			<?php echo $rsInsertPosts->content ?>

			<?php endif; # fin Okatea : affichage texte pas tronqué ?>

		<?php endif; # début Okatea : si on as accès en lecture à l'article ?>

		</div><!-- .post-content -->
		<?php # fin Okatea : affichage du contenu ?>

	</div><!-- .post -->
	<?php # fin Okatea : affichage d'un article ?>

	<?php endwhile; # début Okatea : boucle sur la liste des actualités ?>

</div><!-- #newsInsert -->

<?php endif; # fin Okatea : si il y a des actualités on affiche la liste ?>
