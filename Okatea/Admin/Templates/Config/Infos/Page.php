<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_infos'));

# js
$okt->page->tabs();

?>

<div id="tabered">
	<ul>
		<?php foreach ($aPageData['tabs'] as $aTabInfos) : ?>
		<li><a href="#<?php
			
			echo $aTabInfos['id']?>"><span><?php echo $aTabInfos['title'] ?></span></a></li>
		<?php endforeach; ?>
	</ul>

	<?php foreach ($aPageData['tabs'] as $sTabUrl=>$aTabInfos) : ?>
	<div id="<?php echo $aTabInfos['id'] ?>">
		<?php echo $aTabInfos['content']?>
	</div>
	<!-- #<?php echo $aTabInfos['id'] ?> -->
	<?php endforeach; ?>
</div>
<!-- #tabered -->
