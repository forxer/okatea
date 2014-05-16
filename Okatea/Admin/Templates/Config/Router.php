<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Html\Stack;

$view->extend('layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_config_router_internal_router'));

$okt->page->css->addCss('
	.routes_list {}
	.routes_list ul { list-style: none; margin: 0; }
	.routes_list li { line-height: 1.7; }
');

# Tabs
$okt->page->tabs();

?>

<h3><?php _e('c_a_config_router_routes_list') ?></h3>

<div id="tabered">
	<ul>
		<li><a href="#tab_public"><span><?php _e('c_a_config_router_website_routes') ?></span></a></li>
		<li><a href="#tab_admin"><span><?php _e('c_a_config_router_admin_routes') ?></span></a></li>
	</ul>

	<div id="tab_public">
		<?php if (empty($aWebsiteRoutesInfos)) : ?>
		<p><?php _e('c_a_config_router_no_route') ?></p>

		<?php else : ?>
		<table class="common routes_list">
			<caption><?php _e('c_a_config_router_routes_list') ?></caption>
			<thead>
				<tr>
					<th scope="col" colspan="2"><?php _e('c_a_config_router_route_name') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_path') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_controller') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_defaults') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_requirements') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_language') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_file') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_others_options') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_loaded') ?></th>
				</tr>
			</thead>
			<tbody>
			<?php 
# boucle sur les routes
			

			$count_line = 0;
			foreach ($aWebsiteRoutesInfos as $sRouteName => $aWebsiteRouteInfos)
			:
				
				$td_class = $count_line % 2 == 0 ? 'even' : 'odd';
				$count_line ++;
				
				if (! $aWebsiteRouteInfos['loaded'])
				{
					$td_class = ' disabled';
				}
				
				$aOthersOptions = array();
				
				if (! empty($aWebsiteRouteInfos['options']))
				{
					$oOptions = new Stack($aWebsiteRouteInfos['options']);
					$aOthersOptions[__('c_a_config_router_route_options')] = $oOptions->getHTML();
				}
				
				if (! empty($aWebsiteRouteInfos['host']))
				{
					$aOthersOptions[__('c_a_config_router_route_host')] = $aWebsiteRouteInfos['host'];
				}
				
				if (! empty($aWebsiteRouteInfos['schemes']))
				{
					$oSchemes = new Stack($aWebsiteRouteInfos['schemes']);
					$aOthersOptions[__('c_a_config_router_route_schemes')] = $oSchemes->getHTML();
				}
				
				if (! empty($aWebsiteRouteInfos['methods']))
				{
					$oMethods = new Stack($aWebsiteRouteInfos['methods']);
					$aOthersOptions[__('c_a_config_router_route_methods')] = $oMethods->getHTML();
				}
				
				$sShortName = strstr($sRouteName, '-', true);
				
				?>
			<tr id="route_<?php echo $sRouteName ?>">
					<th class="<?php echo $td_class ?> fake-td" scope="row"><p
							class="title"><?php echo $okt->languages->unique ? $aWebsiteRouteInfos['basename'] : $sRouteName; ?></p></th>
					<td class="<?php echo $td_class ?>">
					<?php echo (isset($GLOBALS['okt_l10n']['c_a_route_name_'.$sShortName]) ? '<p><strong>'.$GLOBALS['okt_l10n']['c_a_route_name_'.$sShortName].'</strong></p>'  : '')?>
					<?php echo (isset($GLOBALS['okt_l10n']['c_a_route_desc_'.$sShortName]) ? '<p>'.$GLOBALS['okt_l10n']['c_a_route_desc_'.$sShortName].'</p>' : '')?>
				</td>
					<td class="<?php echo $td_class ?>"><?php echo $okt->languages->unique ? $aWebsiteRouteInfos['basepath'] : $aWebsiteRouteInfos['path']; ?></td>
					<td class="<?php echo $td_class ?>"><?php echo $aWebsiteRouteInfos['controller']; ?></td>
					<td class="<?php echo $td_class ?>"><?php $oDefaults = new Stack($aWebsiteRouteInfos['defaults']); echo $oDefaults; ?></td>
					<td class="<?php echo $td_class ?>"><?php $oRequirements = new Stack($aWebsiteRouteInfos['requirements']); echo $oRequirements; ?></td>
					<td class="<?php echo $td_class ?> center"><?php echo $aWebsiteRouteInfos['language'] ?></td>
					<td class="<?php echo $td_class ?>"><?php echo basename($aWebsiteRouteInfos['file']) ?></td>
					<td class="<?php echo $td_class ?>"><?php $oOthersOptions = new Stack($aOthersOptions); echo $oOthersOptions; ?></td>
					<td class="<?php echo $td_class ?> center"><?php echo $aWebsiteRouteInfos['loaded'] ? '<span class="icon tick" title="'.__('c_c_yes').'"></span>' : '<span class="icon cross" title="'.__('c_c_no').'"></span>'; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<!-- #tab_public -->

	<div id="tab_admin">
		<?php if (empty($aAdminRoutesInfos)) : ?>
		<p><?php _e('c_a_config_router_no_route') ?></p>

		<?php else : ?>
		<table class="common routes_list">
			<caption><?php _e('c_a_config_router_routes_list') ?></caption>
			<thead>
				<tr>
					<th scope="col" colspan="2"><?php _e('c_a_config_router_route_name') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_path') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_controller') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_defaults') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_requirements') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_file') ?></th>
					<th scope="col"><?php _e('c_a_config_router_route_others_options') ?></th>
				</tr>
			</thead>
			<tbody>
			<?php 
# boucle sur les routes
			

			$count_line = 0;
			foreach ($aAdminRoutesInfos as $sRouteName => $aAdminRouteInfos)
			:
				
				$td_class = $count_line % 2 == 0 ? 'even' : 'odd';
				$count_line ++;
				
				$aOthersOptions = array();
				
				if (! empty($aAdminRouteInfos['options']))
				{
					$oOptions = new Stack($aAdminRouteInfos['options']);
					$aOthersOptions[__('c_a_config_router_route_options')] = $oOptions->getHTML();
				}
				
				if (! empty($aAdminRouteInfos['host']))
				{
					$aOthersOptions[__('c_a_config_router_route_host')] = $aAdminRouteInfos['host'];
				}
				
				if (! empty($aAdminRouteInfos['schemes']))
				{
					$oSchemes = new Stack($aAdminRouteInfos['schemes']);
					$aOthersOptions[__('c_a_config_router_route_schemes')] = $oSchemes->getHTML();
				}
				
				if (! empty($aAdminRouteInfos['methods']))
				{
					$oMethods = new Stack($aAdminRouteInfos['methods']);
					$aOthersOptions[__('c_a_config_router_route_methods')] = $oMethods->getHTML();
				}
				
				$sShortName = strstr($sRouteName, '-', true);
				
				?>
			<tr id="route_<?php echo $sRouteName ?>">
					<th class="<?php echo $td_class ?> fake-td" scope="row"><p
							class="title"><?php echo $okt->languages->unique ? $aAdminRouteInfos['basename'] : $sRouteName; ?></p></th>
					<td class="<?php echo $td_class ?>">
					<?php echo (isset($GLOBALS['okt_l10n']['c_a_route_name_'.$sShortName]) ? '<p><strong>'.$GLOBALS['okt_l10n']['c_a_route_name_'.$sShortName].'</strong></p>'  : '')?>
					<?php echo (isset($GLOBALS['okt_l10n']['c_a_route_desc_'.$sShortName]) ? '<p>'.$GLOBALS['okt_l10n']['c_a_route_desc_'.$sShortName].'</p>' : '')?>
				</td>
					<td class="<?php echo $td_class ?>"><?php echo $aAdminRouteInfos['path']; ?></td>
					<td class="<?php echo $td_class ?>"><?php echo $aAdminRouteInfos['controller']; ?></td>
					<td class="<?php echo $td_class ?>"><?php $oDefaults = new Stack($aAdminRouteInfos['defaults']); echo $oDefaults; ?></td>
					<td class="<?php echo $td_class ?>"><?php $oRequirements = new Stack($aAdminRouteInfos['requirements']); echo $oRequirements; ?></td>
					<td class="<?php echo $td_class ?>"><?php echo basename($aAdminRouteInfos['file']) ?></td>
					<td class="<?php echo $td_class ?>"><?php $oOthersOptions = new Stack($aOthersOptions); echo $oOthersOptions; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<!-- #tab_admin -->
</div>
<!-- #tabered -->
