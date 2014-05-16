
<div id="catalog_categories_lists">

<?php
$iRefLevel = $iLevel = $rsCategories->level - 1;

while ($rsCategories->fetch())
{
	# ouverture niveau
	if ($rsCategories->level > $iLevel)
	{
		echo str_repeat('<ul><li id="cat-' . $rsCategories->id . '">', $rsCategories->level - $iLevel);
	}
	# fermeture niveau
	elseif ($rsCategories->level < $iLevel)
	{
		echo str_repeat('</li></ul>', - ($rsCategories->level - $iLevel));
	}
	
	# nouvelle ligne
	if ($rsCategories->level <= $iLevel)
	{
		echo '</li><li id="rub' . $rsCategories->id . '">';
	}
	
	echo '<a href="' . $okt->page->getBaseUrl() . $okt->catalog->config->public_catalog_url . '/' . $rsCategories->slug . '">' . $view->escape($rsCategories->name) . '</a>';
	
	$iLevel = $rsCategories->level;
}

if ($iRefLevel - $iLevel < 0)
{
	echo str_repeat('</li></ul>', - ($iRefLevel - $iLevel));
}

?>

</div>
<!-- #categories_lists  -->
