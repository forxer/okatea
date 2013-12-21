<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page de gestion du routeur interne
 *
 * @addtogroup Okatea
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;


# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;


/* Initialisations
----------------------------------------------------------*/

# Locales
$okt->l10n->loadFile(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.router');

# Liste des routes chargées
$aRoutes = $okt->router->getRoutes();

# Default route data
$p_default_route = array(
	'class' => $okt->config->default_route['class'],
	'method' => $okt->config->default_route['method'],
	'args' => $okt->config->default_route['args']
);

# Default custom route data
$p_add_custom_route = array(
	'rep' => '',
	'class' => '',
	'method' => '',
	'args' => ''
);


/* Traitements
----------------------------------------------------------*/

# modification route par défaut
if (!empty($_POST['edit_default_route']))
{
	$p_default_route = array(
		'class' => !empty($_POST['p_default_route_class']) ? $_POST['p_default_route_class'] : '',
		'method' => !empty($_POST['p_default_route_method']) ? $_POST['p_default_route_method'] : '',
		'args' => !empty($_POST['p_default_route_args']) ? $_POST['p_default_route_args'] : '',
	);

	if ((empty($p_default_route['class']) || empty($p_default_route['method'])) && !empty($_POST['p_default_route_dest']) && isset($aRoutes[$_POST['p_default_route_dest']]))
	{
		$p_default_route['class'] = $aRoutes[$_POST['p_default_route_dest']]->getClassHandler();
		$p_default_route['method'] = $aRoutes[$_POST['p_default_route_dest']]->getMethodHandler();
	}

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'default_route' => $p_default_route,
		);

		try
		{
			$okt->config->write($new_conf);
			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));
			http::redirect('configuration.php?action=router');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}

# modification des routes personnalisées
if (!empty($_POST['edit_custom_routes']))
{
	$p_custom_routes = !empty($_POST['p_custom_routes']) && is_array($_POST['p_custom_routes'])  ? $_POST['p_custom_routes'] : array();

	foreach ($p_custom_routes as $i=>$aCustomRoute)
	{
		if (empty($aCustomRoute['rep']) || empty($aCustomRoute['class']) || empty($aCustomRoute['method'])) {
			unset($p_custom_routes[$i]);
		}
	}

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'custom_routes' => $p_custom_routes,
		);

		try
		{
			$okt->config->write($new_conf);
			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));
			http::redirect('configuration.php?action=router');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}


# ajout d'une route personnalisée
if (!empty($_POST['add_custom_route']))
{
	$p_add_custom_route = array(
		'rep' => !empty($_POST['p_add_custom_route_rep']) ? $_POST['p_add_custom_route_rep'] : '',
		'class' => !empty($_POST['p_add_custom_route_class']) ? $_POST['p_add_custom_route_class'] : '',
		'method' => !empty($_POST['p_add_custom_route_method']) ? $_POST['p_add_custom_route_method'] : '',
		'args' => !empty($_POST['p_add_custom_route_args']) ? $_POST['p_add_custom_route_args'] : '',
	);

	if (empty($p_add_custom_route['rep'])) {
		$okt->error->set(__('c_a_config_router_need_rep'));
	}

	if (empty($p_add_custom_route['class']) || empty($p_add_custom_route['method']) )
	{
		if (!empty($_POST['p_add_custom_route_dest']) && isset($aRoutes[$_POST['p_add_custom_route_dest']])) {
			$p_add_custom_route['class'] = $aRoutes[$_POST['p_add_custom_route_dest']]->getClassHandler();
			$p_add_custom_route['method'] = $aRoutes[$_POST['p_add_custom_route_dest']]->getMethodHandler();
		}
		else {
			$okt->error->set(__('c_a_config_router_need_controller'));
		}
	}


	if ($okt->error->isEmpty())
	{
		$aCurrentCustomsRoutes = $okt->config->custom_routes;
		$aCurrentCustomsRoutes[] = $p_add_custom_route;

		$new_conf = array(
			'custom_routes' => $aCurrentCustomsRoutes,
		);

		try
		{
			$okt->config->write($new_conf);
			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));
			http::redirect('configuration.php?action=router');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}


# enregistrement configuration
if (!empty($_POST['save_config']))
{
	$p_app_file = !empty($_POST['p_app_file']) ? $_POST['p_app_file'] : 'index.php';

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'app_file' => $p_app_file
		);

		try
		{
			$okt->config->write($new_conf);
			$okt->page->flashMessages->addSuccess(__('c_c_confirm_configuration_updated'));
			http::redirect('configuration.php?action=router');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}

}


/* Affichage
----------------------------------------------------------*/

$aRoutesDestsChoice = array('&nbsp;'=>null);
foreach ($aRoutes as $sRoute=>$oRoute) {
	$aRoutesDestsChoice[(isset($GLOBALS['__l10n']['c_a_route_name_'.$sRoute]) ? $GLOBALS['__l10n']['c_a_route_name_'.$sRoute] : $sRoute)] = $sRoute;
}


# Infos page
$okt->page->addGlobalTitle(__('c_a_config_router_internal_router'));


# Tabs
$okt->page->tabs();


# JS pour l'aide à la saisie des controllers (class/method) à partir d'une liste de destinations
$aRoutesToBeJsonEncoded = array();

foreach ($aRoutes as $sRoute=>$oRoute)
{
	$aRoutesToBeJsonEncoded[$sRoute] = array(
		'class' => $oRoute->getClassHandler(),
		'method' => $oRoute->getMethodHandler()
	);
}

$okt->page->js->addScript('
	var routes = '.json_encode($aRoutesToBeJsonEncoded).';

	function handleRouteDest(e_dest,e_class,e_method) {
		var route = $(e_dest).val();

		if (routes[route] != undefined) {
			$(e_class).val(routes[route].class);
			$(e_method).val(routes[route].method);
		}
	}

	function handleRouteController(e_dest,e_class,e_method) {
		var className = $(e_class).val();
		var methodName = $(e_method).val();

		for (var route in routes) {
			if (routes[route].class == className && routes[route].method == methodName) {
				$(e_dest).val(route);
			}
		}
	}
');

$okt->page->js->addReady('
	// default route
	handleRouteController("#p_default_route_dest","#p_default_route_class","#p_default_route_method");

	$("#p_default_route_dest").change(function(){
		handleRouteDest("#p_default_route_dest","#p_default_route_class","#p_default_route_method");
	});

	$("#p_default_route_class,#p_default_route_method").change(function(){
		handleRouteController("#p_default_route_dest","#p_default_route_class","#p_default_route_method");
	});

	// custom routes
	handleRouteController("#p_add_custom_route_dest","#p_add_custom_route_class","#p_add_custom_route_method");

	$("#p_add_custom_route_dest").change(function(){
		handleRouteDest("#p_add_custom_route_dest","#p_add_custom_route_class","#p_add_custom_route_method");
	});

	$("#p_add_custom_route_class,#p_add_custom_route_method").change(function(){
		handleRouteController("#p_add_custom_route_dest","#p_add_custom_route_class","#p_add_custom_route_method");
	});
');



# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<div id="tabered">
	<ul>
		<li><a href="#tab_routes_system"><span><?php _e('c_a_config_router_tab_routes_system') ?></span></a></li>
		<li><a href="#tab_custom_routes"><span><?php _e('c_a_config_router_tab_custom_routes') ?></span></a></li>
		<li><a href="#tab_config"><span><?php _e('c_a_config_router_tab_config') ?></span></a></li>
	</ul>

	<div id="tab_routes_system">
		<h3><?php _e('c_a_config_router_tab_routes_system') ?></h3>

		<p class="note"><?php _e('c_a_config_router_routes_info') ?></p>

		<h4><?php _e('c_a_config_router_default_route') ?></h4>
		<form action="configuration.php" method="post">

			<div class="four-cols">
				<p class="field col"><label for="p_default_route_dest"><?php _e('c_a_config_router_route_dest') ?></label>
				<?php echo form::select('p_default_route_dest', $aRoutesDestsChoice) ?></p>

				<p class="field col"><label for="p_default_route_class"><?php _e('c_a_config_router_route_class') ?></label>
				<?php echo form::text('p_default_route_class', 40, 255, $p_default_route['class']) ?></p>

				<p class="field col"><label for="p_default_route_method"><?php _e('c_a_config_router_route_method') ?></label>
				<?php echo form::text('p_default_route_method', 40, 255, $p_default_route['method']) ?></p>

				<p class="field col"><label for="p_default_route_args"><?php _e('c_a_config_router_route_args') ?></label>
				<?php echo form::text('p_default_route_args', 40, 255, $p_default_route['args']) ?></p>
			</div><!-- .two-cols -->

			<p><?php echo form::hidden(array('edit_default_route'), 1); ?>
			<?php echo form::hidden(array('action'), 'router'); ?>
			<?php echo Page::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_edit') ?>" /></p>
		</form>

		<h4><?php _e('c_a_config_router_routes_list') ?></h4>

		<?php if (empty($aRoutes)) : ?>
		<p><?php _e('c_a_config_router_no_route') ?></p>
		<?php else : ?>
		<table class="common">
			<caption><?php _e('c_a_config_router_routes_list') ?></caption>
			<thead><tr>
				<th scope="col"><?php _e('c_a_config_router_route_name') ?></th>
				<th scope="col"><?php _e('c_a_config_router_route_desc') ?></th>
				<th scope="col"><?php _e('c_a_config_router_route_rep') ?></th>
				<th scope="col"><?php _e('c_a_config_router_route_class') ?></th>
				<th scope="col"><?php _e('c_a_config_router_route_method') ?></th>
			</tr></thead>
			<tbody>
			<?php foreach ($aRoutes as $sRoute=>$oRoute) : ?>
			<tr id="route_<?php echo $sRoute ?>">
				<th scope="row" class="route_name"><?php echo (isset($GLOBALS['__l10n']['c_a_route_name_'.$sRoute]) ? $GLOBALS['__l10n']['c_a_route_name_'.$sRoute] : $sRoute) ?></th>
				<td class="route_desc"><?php echo (isset($GLOBALS['__l10n']['c_a_route_desc_'.$sRoute]) ? $GLOBALS['__l10n']['c_a_route_desc_'.$sRoute] : '') ?></td>
				<td class="route_rep"><?php echo $oRoute->getPathRepresentation() ?></td>
				<td class="route_class"><?php echo $oRoute->getClassHandler() ?></td>
				<td class="route_method"><?php echo $oRoute->getMethodHandler() ?></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>

	</div><!-- #tab_routes_system -->

	<div id="tab_custom_routes">
		<h3><?php _e('c_a_config_router_tab_custom_routes') ?></h3>

		<?php if (empty($okt->config->custom_routes)) : ?>
		<p><?php _e('c_a_config_router_no_custom_route') ?></p>
		<?php else : ?>
		<form action="configuration.php" method="post">
			<table class="common">
			<caption><?php _e('c_a_config_router_custom_routes_list') ?></caption>
				<thead><tr>
					<th scope="col"><?php _e('c_a_config_router_route_rep') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_class') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_method') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_args') ?></th>
				</tr></thead>
				<tbody>
				<?php $iCustomRoutesCount = 0;
				foreach ($okt->config->custom_routes as $aCustomRoute) : ?>
				<tr>
					<td><?php echo form::text('p_custom_routes['.$iCustomRoutesCount.'][rep]', 40, 255, $aCustomRoute['rep']) ?></td>
					<td><?php echo form::text('p_custom_routes['.$iCustomRoutesCount.'][class]', 40, 255, $aCustomRoute['class']) ?></td>
					<td><?php echo form::text('p_custom_routes['.$iCustomRoutesCount.'][method]', 40, 255, $aCustomRoute['method']) ?></td>
					<td><?php echo form::text('p_custom_routes['.$iCustomRoutesCount.'][args]', 40, 255, $aCustomRoute['args']) ?></td>
				</tr>
				<?php $iCustomRoutesCount++; endforeach; ?>
				</tbody>
			</table>

			<p><?php echo form::hidden(array('edit_custom_routes'), 1); ?>
			<?php echo form::hidden(array('action'), 'router'); ?>
			<?php echo Page::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_edit') ?>" /></p>
		</form>
		<?php endif; ?>

		<form action="configuration.php" method="post">
			<h4><?php _e('c_a_config_router_add_custom_route') ?></h4>

			<p class="note"><?php _e('c_a_config_router_add_custom_route_info') ?></p>

			<div class="two-cols">
				<p class="field col"><label for="p_add_custom_route_rep"><?php _e('c_a_config_router_route_rep') ?></label>
				<?php echo form::text('p_add_custom_route_rep', 40, 255, $p_add_custom_route['rep']) ?></p>

				<p class="field col"><label for="p_add_custom_route_dest"><?php _e('c_a_config_router_route_dest') ?></label>
				<?php echo form::select('p_add_custom_route_dest', $aRoutesDestsChoice) ?></p>
			</div><!-- .two-cols -->

			<div class="two-cols">
				<p class="field col"><label for="p_add_custom_route_class"><?php _e('c_a_config_router_route_class') ?></label>
				<?php echo form::text('p_add_custom_route_class', 40, 255, $p_add_custom_route['class']) ?></p>

				<p class="field col"><label for="p_add_custom_route_method"><?php _e('c_a_config_router_route_method') ?></label>
				<?php echo form::text('p_add_custom_route_method', 40, 255, $p_add_custom_route['method']) ?></p>
			</div><!-- .two-cols -->

			<p class="field"><label for="p_add_custom_route_args"><?php _e('c_a_config_router_route_args') ?></label>
			<?php echo form::text('p_add_custom_route_args', 40, 255, $p_add_custom_route['args']) ?></p>

			<p><?php echo form::hidden(array('add_custom_route'), 1); ?>
			<?php echo form::hidden(array('action'), 'router'); ?>
			<?php echo Page::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_add') ?>" /></p>
		</form>

	</div><!-- #tab_custom_routes -->

	<div id="tab_config">
		<h3><?php _e('c_a_config_router_tab_config') ?></h3>

		<form action="configuration.php" method="post">
			<p class="field"><label for="p_app_file"><?php _e('c_a_config_router_file_path') ?></label>
			<?php echo form::text('p_app_file', 40, 255, html::escapeHTML($okt->config->app_file)) ?></p>

			<h4><?php _e('c_c_seo_rewrite_rules') ?></h4>
<pre>
# start Okatea internal router
RewriteRule ^(.+)$ <?php echo html::escapeHTML($okt->config->app_file) ?>?$1 [QSA,L]
RewriteRule ^$ <?php echo html::escapeHTML($okt->config->app_file) ?> [QSA,L]
# end Okatea internal router
</pre>
				<?php if ($okt->checkPerm('tools')) : ?>
			<p><?php printf(__('c_c_seo_go_to_htaccess_modification_tool'), 'configuration.php?action=tools#tab-htaccess') ?></p>
			<?php endif; ?>

			<p><?php echo form::hidden(array('save_config'), 1); ?>
			<?php echo form::hidden(array('action'), 'router'); ?>
			<?php echo Page::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
		</form>
	</div><!-- #tab_config -->

</div><!-- #tabered -->


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
