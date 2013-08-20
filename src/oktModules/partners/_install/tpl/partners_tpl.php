
<?php # début Okatea : ce template étend le layout
$this->extend('layout');
# fin Okatea : ce template étend le layout ?>


<?php # début Okatea : ajout de la CSS spécifique au module
$okt->page->css->addFile(OKT_THEME.'/modules/partners/styles.css');
# fin Okatea : ajout de la CSS spécifique au module ?>


<?php # début Okatea : ajout de jQuery
$okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php # début Okatea : ajout du modal
$okt->page->applyLbl($okt->partners->config->lightbox_type);
# fin Okatea : ajout du modal ?>


<?php # début Okatea : si il n'y a PAS de partenaires à afficher on peut indiquer un message
if ($rsPartners->isEmpty()) : ?>

<p><em><?php _e('m_partners_there_is_no_partners') ?></em></p>

<?php endif; # fin Okatea : si il n'y a PAS de partenaires à afficher on peut indiquer un message ?>


<?php # début Okatea : si il y a des partenaires à afficher on affiche la liste
if (!$rsPartners->isEmpty()) : ?>

<div id="partners_list">

	<?php # début Okatea : boucle sur la liste des partenaires
	while ($rsPartners->fetch()) : ?>

	<?php # début Okatea : affichage d'un partenaire ?>
	<div class="partner">

		<?php # début Okatea : affichage logo
		if (!empty($rsPartners->logo)) : ?>

		<div class="logo">
			<?php $aPartnerLogoInfos = $rsPartners->getImagesInfo();

			# affichage square ou icon ?
			if (isset($aPartnerLogoInfos['min_url'])) {
				$logo_url = $aPartnerLogoInfos['min_url'];
				$logo_attr = $aPartnerLogoInfos['min_attr'];
			}
			else {
				$logo_url = OKT_PUBLIC_URL.'/img/media/image.png';
				$logo_attr = ' width="48" height="48" ';
			}
			?>
			<a href="<?php echo $aPartnerLogoInfos['img_url'] ?>"
			title="<?php echo util::escapeAttrHTML($rsPartners->name) ?>"
			class="modal" rel="partner-logo"><img src="<?php echo $logo_url ?>"
			<?php echo $logo_attr ?>
			alt="<?php echo util::escapeAttrHTML((isset($aPartnerLogoInfos['alt']) ? $aPartnerLogoInfos['alt'] : 'Logo '.$rsPartners->name)) ?>" /></a>
		</div>
		<?php endif; # fin Okatea : affichage logo  ?>

		<?php # début Okatea : affichage du nom ?>
		<h2 class="partner-name"><?php echo html::escapeHTML($rsPartners->name) ?></h2>
		<?php # fin Okatea : affichage du nom ?>

		<div class="partner-description">

			<?php # début Okatea : affichage description ?>
			<?php echo $rsPartners->description ?>
			<?php # fin Okatea : affichage du description ?>

			<?php # début Okatea : affichage URL
			if (!empty($rsPartners->url)) : ?>
			<p><a href="<?php echo html::escapeHTML($rsPartners->url) ?>" class="partner-url" title="<?php
			echo $rsPartners->name ?>"><?php echo (!empty($rsPartners->url_title) ? $rsPartners->url_title : __('m_partners_default_url_title')) ?></a></p>
			<?php endif; # fin Okatea : affichage URL ?>

		</div><!-- .partner-description -->

	</div><!-- .partner -->
	<?php # fin Okatea : affichage d'un partenaire ?>

	<?php endwhile; # fin Okatea : boucle sur la liste des partenaires ?>

</div><!-- #partners_list -->

<?php endif; # fin Okatea : si il y a des partenaires à afficher on affiche la liste ?>
