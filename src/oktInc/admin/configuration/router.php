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
use Tao\Html\Stack;
use Tao\Routing\ConfigHelpers;


# Accès direct interdit
if (!defined('ON_CONFIGURATION_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Locales
$okt->l10n->loadFile(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.router');

$oConfigHelpers = new ConfigHelpers($okt, OKT_CONFIG_PATH.'/routes');

# Liste des routes chargées
$aRouteInfoss = $oConfigHelpers->getRoutesInfos();


/* Traitements
----------------------------------------------------------*/



/* Affichage
----------------------------------------------------------*/

# Infos page
$okt->page->addGlobalTitle(__('c_a_config_router_internal_router'));


$okt->page->css->addCss('
	.routes_list {}
	.routes_list ul { list-style: none; margin: 0; }
	.routes_list li { line-height: 1.7; }

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

		<p>.....</p>

		<h4><?php _e('c_a_config_router_routes_list') ?></h4>

		<?php if (empty($aRouteInfoss)) : ?>
		<p><?php _e('c_a_config_router_no_route') ?></p>
		<?php else : ?>
		<table class="common routes_list">
			<caption><?php _e('c_a_config_router_routes_list') ?></caption>
			<thead><tr>
				<th scope="col" colspan="2"><?php _e('c_a_config_router_route_name') ?></th>
				<th scope="col"><?php _e('c_a_config_router_route_path') ?></th>
				<th scope="col"><?php _e('c_a_config_router_route_defaults') ?></th>
				<th scope="col"><?php _e('c_a_config_router_route_requirements') ?></th>
				<th scope="col"><?php _e('c_a_config_router_route_options') ?></th>
				<th scope="col"><?php _e('c_a_config_router_route_host') ?></th>
				<th scope="col"><?php _e('c_a_config_router_route_schemes') ?></th>
				<th scope="col"><?php _e('c_a_config_router_route_methods') ?></th>
				<th scope="col"><?php _e('c_a_config_router_route_loaded') ?></th>
			</tr></thead>
			<tbody>
			<?php # boucle sur les routes

			$count_line = 0;
			foreach ($aRouteInfoss as $sRouteName=>$aRouteInfos) :

			$td_class = $count_line%2 == 0 ? 'even' : 'odd';
			$count_line++;

			if (!$aRouteInfos['loaded']) {
				$td_class = ' disabled';
			}

			$sShortName = strstr($sRouteName, '-', true);
			?>
			<tr id="route_<?php echo $sRouteName ?>">
				<th class="<?php echo $td_class ?> fake-td" scope="row"><h5><?php echo $sRouteName ?></h5></th>
				<td class="<?php echo $td_class ?>">
					<?php echo (isset($GLOBALS['__l10n']['c_a_route_name_'.$sShortName]) ? '<p><strong>'.$GLOBALS['__l10n']['c_a_route_name_'.$sShortName].'</strong></p>'  : '') ?>
					<?php echo (isset($GLOBALS['__l10n']['c_a_route_desc_'.$sShortName]) ? '<p>'.$GLOBALS['__l10n']['c_a_route_desc_'.$sShortName].'</p>' : '') ?>
				</td>
				<td class="<?php echo $td_class ?>"><?php echo $aRouteInfos['path'] ?></td>
				<td class="<?php echo $td_class ?>"><?php $oList = new Stack($aRouteInfos['defaults']); echo $oList; ?></td>
				<td class="<?php echo $td_class ?>"><?php $oList = new Stack($aRouteInfos['requirements']); echo $oList; ?></td>
				<td class="<?php echo $td_class ?>"><?php $oList = new Stack($aRouteInfos['options']); echo $oList; ?></td>
				<td class="<?php echo $td_class ?>"><?php echo $aRouteInfos['host'] ?></td>
				<td class="<?php echo $td_class ?>"><?php $oList = new Stack($aRouteInfos['schemes']); echo $oList; ?></td>
				<td class="<?php echo $td_class ?>"><?php $oList = new Stack($aRouteInfos['methods']); echo $oList; ?></td>
				<td class="<?php echo $td_class ?> center"><?php echo $aRouteInfos['loaded'] ? '<span class="icon tick" title="'.__('c_c_yes').'"></span>' : '<span class="icon cross" title="'.__('c_c_no').'"></span>'; ?></td>
			</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div><!-- #tab_routes_system -->
</div><!-- #tabered -->

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
