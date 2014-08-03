<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

# début Okatea : ce template étend le template principal "main"
$this->extend('main');
# fin Okatea : ce template étend le template principal "main"


# Ajout de jQuery
$okt->page->js->addFile($okt['public_url'] . '/components/jquery/dist/jquery.min.js');

# CSS
$okt->page->css->addFile($okt['public_url'] . '/css/init.css');
$okt->page->css->addFile($okt->theme->public_url . '/css/styles.css');
?>


<div id="page">
	<header class="clearfix">

		<div id="head">
			<div class="title"><?php echo $view->escape($okt->page->getSiteTitle()) ?></div>
			<div class="description"><?php echo $view->escape($okt->page->getSiteDescription()) ?></div>
		</div>
		<!-- #head -->

		<div id="search">
			<?php 
# début Okatea : affichage de la boite de recherche de véhicules
			if ($okt['modules']->isLoaded('vehicles'))
			:
				echo $okt->tpl->render('vehicles_search_tpl');
			
			endif; # fin Okatea : affichage de la boite de recherche de véhicules 			?>

			<?php 
# début Okatea : affichage du switcher de langues
			if (! $okt['languages']->unique)
			:
				?>
			<ul id="lang_switcher">
				<?php foreach ($okt['languages']->list as $aLanguage) : ?>
				<li
					id="lang_switcher_<?php echo $view->escape($aLanguage['code']) ?>"><a
					href="<?php echo $view->escape($aLanguage['code']) ?>"
					title="<?php echo $view->escape($aLanguage['title']) ?>"><img
						src="<?php echo $okt['public_url'].'/img/flags/'.$aLanguage['img'] ?>"
						alt="<?php echo $view->escape($aLanguage['title']) ?>" /></a></li>
				<?php endforeach; ?>
			</ul>
			<?php endif; # fin Okatea : affichage du switcher de langues ?>
		</div>
		<!-- #search -->

		<?php 
# début Okatea : affichage menu haut
		echo $okt->navigation->render('MenuTop');
		# fin Okatea : affichage menu haut 		?>

	</header>

	<?php 
# début Okatea : titre SEO de la page (h1)
	if ($okt->page->hasTitleSeo())
	:
		?>
	<h1><?php echo $view->escape($okt->page->getTitleSeo()); ?></h1>
	<?php endif; # fin Okatea : titre SEO de la page (h1) ?>

	<?php 
# début Okatea : affichage de la barre utilisateur
	echo $okt->tpl->render('users/user_bar/' . $okt['config']->users['templates']['user_bar']['default'] . '/template');
	# fin Okatea : affichage de la barre utilisateur 	?>


	<div id="sidebar">
		<?php 
# début Okatea : affichage menu milieu
		echo $okt->navigation->render('MenuMiddle');
		# fin Okatea : affichage menu milieu 		?>

		<?php 
# début Okatea : si le module news est présent, affichage de l'encart
		if ($okt['modules']->isLoaded('news'))
		:
			echo $this->render($okt->module('News')
				->getInsertTplPath());
		
		endif; # fin Okatea : si le module news est présent, affichage de l'encart 		?>


		<?php 
# début Okatea : affichage des pages de la rubrique id 1
		if ($okt['modules']->isLoaded('pages'))
		:
			echo pagesHelpers::getPagesByCatId(1);
		
		endif; # fin Okatea : affichage des pages de la rubrique id 1 		?>

		<?php 
# début Okatea : affichage des sous-rubriques de la rubrique id 1
		if ($okt['modules']->isLoaded('pages'))
		:
			echo pagesHelpers::getSubCatsByCatId(1);
		
		endif; # fin Okatea : affichage des sous-rubriques de la rubrique id 1 		?>

		<?php 
# début Okatea : affichage de l'arbre des rubriques
		if ($okt['modules']->isLoaded('pages'))
		:
			echo pagesHelpers::getCategories();
		
		endif; # fin Okatea : affichage de l'arbre des rubriques 		?>


		<?php 
# début Okatea : affichage des news de la rubrique id 1
		if ($okt['modules']->isLoaded('news'))
		:
			echo newsHelpers::getPostsByCatId(1);
		
		endif; # fin Okatea : affichage des news de la rubrique id 1 		?>

		<?php 
# début Okatea : affichage des sous-rubriques de la rubrique id 1
		if ($okt['modules']->isLoaded('news'))
		:
			echo newsHelpers::getSubCatsByCatId(1);
		
		endif; # fin Okatea : affichage des sous-rubriques de la rubrique id 1 		?>

		<?php 
# début Okatea : affichage de l'arbre des rubriques
		if ($okt['modules']->isLoaded('news'))
		:
			echo newsHelpers::getCategories();
		
		endif; # fin Okatea : affichage de l'arbre des rubriques 		?>

	</div>
	<!-- #sidebar -->

	<div id="content">

		<?php 
# début Okatea : titre de la page
		if ($okt->page->hasTitle())
		:
			?>
		<h2 id="rubric_title"><?php echo $view->escape($okt->page->getTitle()); ?></h2>
		<?php endif; # fin Okatea : titre de la page ?>

		<?php 
# début Okatea : affichage du contenu de la page
		$view['slots']->output('_content');
		# fin Okatea : affichage du contenu de la page 		?>

	</div>
	<!-- #content -->

	<footer>
		<ul>
			<li><?php echo $view->escape($okt['config']->company['com_name']) ?></li>
			<li><?php echo $view->escape($okt['config']->address['street']) ?></li>
			<?php if (!empty($okt['config']->address['street_2'])) : ?><li><?php echo $view->escape($okt['config']->address['street_2']) ?></li><?php endif; ?>
			<li><?php echo $view->escape($okt['config']->address['code'].' '.$okt['config']->address['city']) ?></li>
			<?php if (!empty($okt['config']->address['tel'])) : ?><li><abbr
				title="<?php _e('c_c_Phone') ?>"><?php _e('c_c_Phone_abbr') ?></abbr> <?php echo $view->escape($okt['config']->address['tel']) ?></li><?php endif; ?>
			<?php if (!empty($okt['config']->address['fax'])) : ?><li><?php _e('c_c_Fax') ?> <?php echo $view->escape($okt['config']->address['fax']) ?></li><?php endif; ?>
		</ul>

		<?php 
# début Okatea : affichage menu milieu
		echo $okt->navigation->render('MenuBottom');
		# fin Okatea : affichage menu milieu ?>

	</footer>
</div>
<!-- #page -->
