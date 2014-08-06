<?php
/*

Utilisation de ce template d'encart :
------------------------------------------------------------

Vous devez sélectionner ce template dans la configuration.

Puis coller le code pour effectuer le rendu de ce template,
là où vous souhaitez afficher l'encart.

Exemples :

- Pour afficher la page identifiant 4 :

	<?php # début Okatea : affichage encart page id 4
	echo $okt['tpl']->render($okt->module('Pages')->getInsertTplPath(), array(
		'mPageIdentifier' => 4
	)); # fin Okatea : affichage encart page id 4 ?>


- Pour afficher la page ayant pour slug 'ma-page' :

	<?php # début Okatea : affichage encart page slug 'ma-page'
	echo $okt['tpl']->render($okt->module('Pages')->getInsertTplPath(), array(
		'mPageIdentifier' => 'ma-page'
	)); # fin Okatea : affichage encart page slug 'ma-page' ?>


*/
use Okatea\Tao\Html\Modifiers;

?>


<?php
# début Okatea : traitements avant affichage


# vérification de la présence d'un identifiant, sinon warning et fin
if (empty($mPageIdentifier))
{
	trigger_error('You must assign a mPageIdentifier variable for this template', E_USER_WARNING);
	return;
}

# récupération de la page pour l'encart
$rsInsertPage = $okt->module('Pages')->pages->getPage($mPageIdentifier);

# troncature du contenu ?
if ($okt->module('Pages')->config->insert_truncat_char > 0)
{
	$rsInsertPage->content = Modifiers::truncate(strip_tags($rsInsertPage->content), $okt->module('Pages')->config->insert_truncat_char);
}

# fin Okatea : traitements avant affichage ?>


<?php
# début Okatea : ajout du CHEMIN du fichier LESS
$okt->page->css->addLessFile(__DIR__ . '/styles.less');
# fin Okatea : ajout du CHEMIN du fichier LESS ?>


<?php
# début Okatea : ajout de jQuery
$okt->page->js->addFile($okt['public_url'] . '/components/jquery/dist/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php
# début Okatea : ajout du modal
$okt->page->applyLbl($okt->module('Pages')->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<?php
# début Okatea : si il y a une page à afficher
if (! $rsInsertPage->isEmpty())
:
	?>
<div id="page_insert">

	<?php # début Okatea : affichage du titre ?>
	<h2 class="page-title">
		<a href="<?php echo $view->escape($rsInsertPage->url) ?>"><?php echo $view->escape($rsInsertPage->title) ?></a>
	</h2>
	<?php # fin Okatea : affichage du titre ?>


	<?php
	# début Okatea : affichage du sous-titre
	if ($rsInsertPage->subtitle != '')
	:
		?>
	<p class="page-subtitle">
		<strong><?php echo $view->escape($rsInsertPage->subtitle) ?></strong>
	</p>
	<?php endif; # fin Okatea : affichage du sous-titre ?>


	<?php
	# début Okatea : si on as PAS accès en lecture à la page
	if (! $rsInsertPage->isReadable())
	:
		?>

		<p><?php _e('m_pages_restricted_access') ?></p>

	<?php endif; # début Okatea : si on as PAS accès en lecture à la page ?>


	<?php
	# début Okatea : si on as accès en lecture à la page
	if ($rsInsertPage->isReadable())
	:
		?>

	<div class="page-content">

		<?php
		# début Okatea : si les images sont activées
		if ($okt->module('Pages')->config->images['enable'] && ! empty($rsInsertPage->images))
		:
			?>
		<p class="page-images modal-box">

			<?php
			# début Okatea : boucle sur les images
			foreach ($rsInsertPage->images as $i => $image)
			:
				?>

				<?php
				# début Okatea : affichage de la première image uniquement, et ce au format square
				if ($i == 1 && isset($image['min_url']))
				:
					?>

				<a href="<?php echo $image['img_url'] ?>"
				title="<?php echo $view->escapeHtmlAttr((isset($image['title']) && isset($image['title'][$okt['visitor']->language]) ? $image['title'][$okt['visitor']->language] : $rsInsertPage->title)) ?>"
				class="modal center" rel="page-images"> <img
				src="<?php echo $image['square_url'] ?>"
				<?php echo $image['square_attr']?>
				alt="<?php echo $view->escapeHtmlAttr((isset($image['alt']) && isset($image['alt'][$okt['visitor']->language]) ? $image['alt'][$okt['visitor']->language] : $rsInsertPage->title)) ?>" /></a>

				<?php endif; # fin Okatea : affichage de la première image uniquement, et ce au format square ?>

			<?php endforeach; # fin Okatea : boucle sur les images ?>

		</p>
		<!-- .page-images -->
		<?php endif; # fin Okatea : si les images sont activées ?>


		<?php
		# début Okatea : affichage texte tronqué
		if ($okt->module('Pages')->config->insert_truncat_char > 0)
		:
			?>

		<p><?php echo $rsInsertPage->content ?>…</p>

		<p class="read-more-link-wrapper">
			<a href="<?php echo $view->escape($rsInsertPage->url) ?>"
				title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_pages_read_more_of_%s'), $rsInsertPage->title)) ?>"
				class="read-more-link" rel="nofollow"><?php _e('m_pages_read_more') ?></a>
		</p>

		<?php endif; # fin Okatea : affichage texte tronqué ?>


		<?php
		# début Okatea : affichage texte pas tronqué
		if (! $okt->module('Pages')->config->insert_truncat_char)
		:
			?>

		<?php echo $rsInsertPage->content?>

		<?php endif; # fin Okatea : affichage texte pas tronqué ?>

	</div>
	<!-- .page-content -->

	<?php endif; # fin Okatea : si on as accès en lecture à la page ?>

</div>
<!-- #page_insert -->
<?php endif; # fin Okatea : si il y a une page à afficher ?>
