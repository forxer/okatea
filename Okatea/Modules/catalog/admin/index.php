<?php
/**
 * @ingroup okt_module_catalog
 * @brief Liste des produits
 *
 */

use Okatea\Admin\Page;
use Okatea\Admin\Pager;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# loaded by AJAX ?
$bAjaxLoaded = !empty($_REQUEST['oktAjaxLoad']) ? true : false;

# initialisation des filtres
$okt->catalog->filtersStart('admin');

/* Traitements
----------------------------------------------------------*/

# Switch product statut
if (!empty($_GET['switch_status']))
{
	$okt->catalog->switchProdStatus($_GET['switch_status']);
	http::redirect('module.php?m=catalog&action=index&switched=1');
}

# ré-initialisation filtres
if (!empty($_GET['catalog_init_filters']))
{
	$okt->catalog->filters->initFilters();
	http::redirect('module.php?m=catalog&action=index');
}


/* Affichage
----------------------------------------------------------*/

$sSearch = null;

# initialisation des filtres
$params = array();

if (!empty($_REQUEST['search']))
{
	$sSearch = trim($_REQUEST['search']);
	$params['search'] = $sSearch;
}
$okt->catalog->filters->setCatalogParams($params);

# création des filtres
$okt->catalog->filters->getFilters();

# initialisation de la pagination
$num_filtered_posts = $okt->catalog->getProds($params,true);

$pager = new Pager($okt, $okt->catalog->filters->params->page, $num_filtered_posts, $okt->catalog->filters->params->nb_per_page);

$num_pages = $pager->getNbPages();

$okt->catalog->filters->normalizePage($num_pages);

$params['limit'] = (($okt->catalog->filters->params->page-1)*$okt->catalog->filters->params->nb_per_page).','.$okt->catalog->filters->params->nb_per_page;


# récupération des produits
$list = $okt->catalog->getProds($params);

# Recherche AJAX
$okt->page->js->addReady('
	var delay = (function(){
		var timer = 0;
		return function(callback, ms){
			clearTimeout(timer);
			timer = setTimeout(callback, ms);
		};
	})();

	$("#search_submit").hide();

	$("#search").keyup(function(){

		var field = $(this);

		delay(function(){

			if (field.val().length == 0 || field.val().length > 1)
			{
				$("#ajax-loader").remove();

				$.ajax({
					type: "POST",
					url: "module.php?m=catalog&action=index",
					data: {
						oktAjaxLoad: 1,
						csrf_token: "'.$okt->user->csrf_token.'",
						search: field.val()
					},
					beforeSend: function() {
						field.prev().before(\'<img src="'.$okt->options->public_url.'/img/ajax-loader/arrow.gif" alt="Chargement, veuillez patienter…" id="ajax-loader" />\');
					},
					success: function(data) {
						$("#ajax-loader").fadeOut("slow",function(){
							$(this).remove();
						});
						$("#productsList").fadeOut(300).html(data).fadeIn(300);

						'.$okt->page->getLblJsLoader($okt->catalog->config->lightbox_type).'
						'.Page::getCommonJs().'
					}
				});
			}
		}, 300);
	});
');

$okt->page->css->addCss('
.search_form p {
	margin: 0;
}
#search {
	background: transparent url('.$okt->options->public_url.'/img/admin/preview.png) no-repeat center right;
}
');

# boutons style d’affichage
$display_style = !empty($_SESSION['sess_catalog_admin_dysplay_style']) ? $_SESSION['sess_catalog_admin_dysplay_style'] : $okt->catalog->config->admin_dysplay_style;

if (!empty($_GET['display']) && in_array($_GET['display'],array('list','mosaic'))) {
	$display_style = $_SESSION['sess_catalog_admin_dysplay_style'] = $_GET['display'];
}

# ajout de boutons
$okt->page->addButton('catalogBtSt',array(
	'permission' 	=> true,
	'title' 		=> 'Filtres d’affichage',
	'url' 			=> '#',
	'ui-icon' 		=> 'search',
	'active' 		=> $okt->catalog->filters->params->show_filters,
	'id'			=> 'filter-control',
	'class'			=> 'button-toggleable'
));
$okt->page->addButton('catalogBtSt',array(
	'permission' => true,
	'title' => 'Liste',
	'url' => 'module.php?m=catalog&amp;action=index&amp;display=list',
	'sprite-icon' => 'application_view_list',
	'active' => ($display_style == 'list')
));
$okt->page->addButton('catalogBtSt',array(
	'permission' => true,
	'title' => 'Mosaïque',
	'url' => 'module.php?m=catalog&amp;action=index&amp;display=mosaic',
	'sprite-icon' => 'application_view_tile',
	'active' => ($display_style == 'mosaic')
));
$okt->page->addButton('catalogBtSt',array(
	'permission' 	=> true,
	'title' 		=> 'Voir',
	'url' 			=> html::escapeHTML(CatalogHelpers::getCatalogUrl()),
	'sprite-icon' 	=> 'page_world'
));


# Filters control
if ($okt->catalog->config->admin_filters_style == 'slide')
{
	# Slide down
	$okt->page->filterControl($okt->catalog->filters->params->show_filters);
}
elseif ($okt->catalog->config->admin_filters_style == 'dialog')
{
	# Display a UI dialog box
	$okt->page->js->addReady("
		$('#filters-form').dialog({
			title: \"Filtres d’affichage des produits\",
			autoOpen: false,
			modal: true,
			width: 700,
			height: 350
		});

		$('#filter-control').click(function() {
			$('#filters-form').dialog('open');
		})
	");
}


# Modal
$okt->page->applyLbl($okt->catalog->config->lightbox_type);

# Affichage du résultat AJAX
if ($bAjaxLoaded)  :
	require __DIR__.'/productsList/productsList.php';

else :

# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<div class="double-buttonset">
	<div class="buttonsetA"><?php echo $okt->page->getButtonSet('catalogBtSt'); ?></div>
	<div class="buttonsetB">
		<form action="module.php" method="get" id="search_form" class="search_form">
			<p><label for="search">Recherche</label>
			<?php echo form::text('search',20,255,html::escapeHTML($sSearch)); ?>

			<?php echo form::hidden('m','catalog') ?>
			<?php echo form::hidden('action','index') ?>
			<input type="submit" name="search_submit" id="search_submit" value="ok" /></p>
		</form>
	</div>
</div>

<?php # formulaire des filtres ?>
<form action="module.php" method="get" id="filters-form">
	<fieldset>
		<legend>Filtres d’affichage des produits</legend>

		<?php echo $okt->catalog->filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><?php echo form::hidden('m','catalog') ?>
		<?php echo form::hidden('action','index') ?>
		<input type="submit" name="<?php echo $okt->catalog->filters->getFilterSubmitName() ?>" value="afficher" />
		<a href="module.php?m=catalog&amp;action=index&amp;catalog_init_filters=1">Ré-initialiser les filtres</a>
		</p>
	</fieldset>
</form>

<div id="productsList">
<?php require __DIR__.'/productsList/productsList.php'; ?>
</div><!-- #productsList -->


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

<?php endif;?>
