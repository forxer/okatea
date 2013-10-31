
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


<div id="news-insert">

	<?php # début Okatea : si il n'y a PAS d'actualité à afficher on peut indiquer un message
	if ($rsInsertPosts->isEmpty()) : ?>

	<p><em><?php _e('m_news_there_is_no_post') ?></em></p>

	<?php endif; # fin Okatea : si il n'y a PAS d'actualité à afficher on peut indiquer un message ?>


	<?php # début Okatea : si il y a des actualités on affiche la liste
	if (!$rsInsertPosts->isEmpty()) : ?>

	<ul id="news-list-insert">

		<?php # début Okatea : boucle sur la liste des actualités
		while ($rsInsertPosts->fetch()) : ?>

		<?php # début Okatea : si on as accès en lecture à l'article, affichage du titre
		if ($rsInsertPosts->isReadable()) : ?>
		<li class="post <?php echo $rsInsertPosts->odd_even ?>">

			<?php # début Okatea : affichage du titre et du lien ?>
			<a href="<?php echo html::escapeHTML($rsInsertPosts->url) ?>"><?php echo html::escapeHTML($rsInsertPosts->title) ?></a>
			<?php # fin Okatea : affichage du titre et du lien  ?>

		</li><!-- .post -->
		<?php endif; # fin Okatea : si on as accès en lecture à l'article, affichage du titre ?>

		<?php endwhile; # début Okatea : boucle sur la liste des actualités ?>

	</ul><!-- #news-list-insert -->

	<?php endif; # fin Okatea : si il y a des actualités on affiche la liste ?>

</div><!-- #news-insert -->
