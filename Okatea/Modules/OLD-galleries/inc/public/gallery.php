<?php
/**
 * @ingroup okt_module_galleries
 * @brief "controller" pour l'affichage public d'une galerie
 *
 */
use Okatea\Tao\Html\Modifiers;

# inclusion du preprend public général
require_once __DIR__ . '/../../../../oktInc/public/prepend.php';

# récupération de la galerie en fonction du slug
$slug = !empty($_GET['slug']) ? $_GET['slug'] : null;

$rsGallery = $okt->galleries->tree->getGalleries(array(
	'slug' => $slug,
	'visibility' => 1
));

if ($rsGallery->isEmpty())
{
	$okt->page->serve404();
}

# formatage description
if (!$okt->galleries->config->enable_gal_rte && !empty($rsGallery->description))
{
	$rsGallery->description = Modifiers::nlToP($rsGallery->description);
}

# un mot de passe ?
$bGalleryRequirePassword = false;
if ($rsGallery->password != '')
{
	# il y a un mot de passe en session
	if (!empty($_SESSION['okt_gallery_password_' . $rsGallery->id]))
	{
		if ($_SESSION['okt_gallery_password_' . $rsGallery->id] != $rsGallery->password)
		{
			$okt->error->set('Le mot de passe ne correspond pas à celui de la galerie.');
			$bGalleryRequirePassword = true;
		}
	}
	
	# ou il y a un mot de passe venant du formulaire
	elseif (!empty($_POST['okt_gallery_password']))
	{
		$p_password = trim($_POST['okt_gallery_password']);
		
		if ($p_password != $rsGallery->password)
		{
			$okt->error->set('Le mot de passe ne correspond pas à celui de la galerie.');
			$bGalleryRequirePassword = true;
		}
		else
		{
			$_SESSION['okt_gallery_password_' . $rsGallery->id] = $p_password;
			http::redirect(html::escapeHTML($rsGallery->getGalleryUrl()));
		}
	}
	
	# sinon on doit afficher le formulaire
	else
	{
		$bGalleryRequirePassword = true;
	}
}

# Récupération de la liste des sous-galeries
$subGalleriesList = $okt->galleries->tree->getGalleries(array(
	'active' => 1,
	'parent_id' => $rsGallery->id
));

# Récupération des éléments de la galerie
$rsItems = $okt->galleries->getItems(array(
	'gallery_id' => $rsGallery->id,
	'visibility' => 1
));

# module actuel
$okt->page->module = 'galleries';
$okt->page->action = 'gallery';

# meta description
if ($okt->galleries->config->meta_description != '')
{
	$okt->page->meta_description = $okt->galleries->config->meta_description;
}
else
{
	$okt->page->meta_description = Utilities::getSiteMetaDesc();
}

# meta keywords
if ($okt->galleries->config->meta_keywords != '')
{
	$okt->page->meta_keywords = $okt->galleries->config->meta_keywords;
}
else
{
	$okt->page->meta_keywords = Utilities::getSiteMetaKeywords();
}

# début du fil d'ariane
$okt->page->breadcrumb->add($okt->galleries->getName(), $okt->galleries->config->url);

# title tag du module
$okt->page->addTitleTag($okt->galleries->getTitle());

# Ajout de la hiérarchie des rubriques au fil d'ariane et au title tag
$rsPath = $okt->galleries->getPath($rsGallery->id, true);
while ($rsPath->fetch())
{
	$okt->page->addTitleTag($rsPath->name);
	
	$okt->page->breadcrumb->add($rsPath->name, $okt->page->getBaseUrl() . $okt->galleries->config->public_gallery_url . '/' . $rsPath->slug);
}
unset($rsPath);
