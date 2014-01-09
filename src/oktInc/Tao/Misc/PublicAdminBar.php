<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Misc;

use Tao\Core\Authentification;
use Tao\Core\Update as Updater;
use Tao\Misc\Utilities;
use Tao\Routing\AdminRouter;

/**
 * La classe pour afficher la barre admin côté publique
 *
 */
class PublicAdminBar
{
	/**
	 * L'objet core.
	 * @var object oktCore
	 */
	protected $okt;

	public function __construct($okt)
	{
		$this->okt = $okt;

		$this->okt->triggers->registerTrigger('publicBeforeHtmlBodyEndTag',
			'Tao\Misc\PublicAdminBar::displayPublicAdminBar');

		$this->okt->page->css->addFile($this->okt->options->public_url.'/css/admin-bar.css');
		$this->okt->page->js->addFile($this->okt->options->public_url.'/js/admin-bar.js');

		$this->okt->adminRouter = new AdminRouter(
			$this->okt,
			$this->okt->options->get('config_dir').'/routes_admin',
			$this->okt->options->get('cache_dir').'/routing/admin',
			$this->okt->debug
		);
	}

	public static function displayPublicAdminBar($okt)
	{
		$aBasesUrl = new \ArrayObject;
		$aPrimaryAdminBar = new \ArrayObject;
		$aSecondaryAdminBar = new \ArrayObject;

		$aBasesUrl['admin'] = $okt->config->app_path.'admin/';
		$aBasesUrl['logout'] = $aBasesUrl['admin'].'/index.php?logout=1';
		$aBasesUrl['profil'] = $aBasesUrl['admin'];


		# -- CORE TRIGGER : publicAdminBarBeforeDefaultsItems
		$okt->triggers->callTrigger('publicAdminBarBeforeDefaultsItems', $okt, $aPrimaryAdminBar, $aSecondaryAdminBar, $aBasesUrl);


		# éléments première barre
		$aPrimaryAdminBar[10] = array(
			'intitle' => '<img src="'.$okt->options->public_url.'/img/notify/error.png" width="22" height="22" alt="'.__('c_c_warning').'" />',
			'items' => array()
		);

		$aPrimaryAdminBar[100] = array(
			'href' => $aBasesUrl['admin'],
			'intitle' => __('c_c_administration')
		);

		$aPrimaryAdminBar[200] = array(
			'intitle' => __('c_c_action_Add'),
			'items' => array()
		);

		# éléments seconde barre
		$aSecondaryAdminBar[100] = array(
			'href' => $aBasesUrl['profil'],
			'intitle' => sprintf(__('c_c_user_hello_%s'), \html::escapeHTML(Authentification::getUserCN($okt->user->username, $okt->user->lastname, $okt->user->firstname)))
		);

		if (!$okt->languages->unique)
		{
			$iStartIdx = 150;
			foreach ($okt->languages->list as $aLanguage)
			{
				if ($aLanguage['code'] == $okt->user->language) {
					continue;
				}

				$aSecondaryAdminBar[$iStartIdx++] = array(
					'href' => \html::escapeHTML($okt->config->app_path.$aLanguage['code'].'/'),
					'title' => \html::escapeHTML($aLanguage['title']),
					'intitle' => '<img src="'.$okt->options->public_url.'/img/flags/'.$aLanguage['img'].'" alt="'.\html::escapeHTML($aLanguage['title']).'" />'
				);
			}
		}

		$aSecondaryAdminBar[200] = array(
			'href' => $aBasesUrl['logout'],
			'intitle' => __('c_c_user_log_off_action')
		);


		# infos super-admin
		if ($okt->checkPerm('is_superadmin'))
		{
			# avertissement mode debug activé
			if ($okt->debug)
			{
				$aPrimaryAdminBar[10]['items'][300] = array(
					'intitle' => __('c_a_public_debug_mode_enabled')
				);
			}

			# avertissement nouvelle version disponible
			if ($okt->config->update_enabled && is_readable($okt->options->get('digests')))
			{
				$updater = new Updater($okt->config->update_url, 'okatea', $okt->config->update_type, $okt->options->get('cache_dir').'/versions');
				$new_v = $updater->check(Utilities::getVersion());

				if ($updater->getNotify() && $new_v)
				{
					# locales
					$okt->l10n->loadFile($okt->options->locales_dir.'/'.$okt->user->language.'/admin.update');

					$aPrimaryAdminBar[10]['items'][100] = array(
						'href' => $aBasesUrl['admin'].'/configuration.php?action=update',
						'intitle' => sprintf(__('c_a_update_okatea_%s_available'), $new_v)
					);
				}
			}

			# avertissement mode maintenance est activé sur la partie publique
			if ($okt->config->public_maintenance_mode)
			{
				$aPrimaryAdminBar[10]['items'][300] = array(
					'href' => $aBasesUrl['admin'].'/configuration.php?action=advanced#tab_others',
					'intitle' => __('c_a_public_maintenance_mode_enabled')
				);
			}

			# avertissement mode maintenance est activé sur l'admin
			if ($okt->config->admin_maintenance_mode)
			{
				$aPrimaryAdminBar[10]['items'][400] = array(
					'href' => $aBasesUrl['admin'].'/configuration.php?action=advanced#tab_others',
					'intitle' => __('c_a_admin_maintenance_mode_enabled')
				);
			}

			# info execution
			$aExecInfos = array();
			$aExecInfos['execTime'] = Utilities::getExecutionTime();
			$aExecInfos['memUsage'] = Utilities::l10nFileSize(memory_get_usage());
			$aExecInfos['peakUsage'] = Utilities::l10nFileSize(memory_get_peak_usage());

			$aSecondaryAdminBar[1000] = array(
				'intitle' => '<img src="'.$okt->options->public_url.'/img/ico/terminal.gif" width="16" height="16" alt="" />',
				'items' => array(
					array(
						'intitle' => 'Temps d\'execution du script&nbsp;: '.$aExecInfos['execTime'].' s'
					),
					array(
						'intitle' => 'Mémoire Utilities::isée par PHP&nbsp;: '.$aExecInfos['memUsage']
					),
					array(
						'intitle' => 'Pic mémoire allouée par PHP&nbsp;: '.$aExecInfos['peakUsage']
					)
				)
			);

			$aRequestAttributes = $okt->request->attributes->all();

			if (!empty($aRequestAttributes['_route']))
			{
				$aSecondaryAdminBar[1000]['items'][] = array(
					'intitle' => 'Route&nbsp;: '.$aRequestAttributes['_route']
				);
				unset($aRequestAttributes['_route']);
			}

			if (!empty($aRequestAttributes['_controller']))
			{
				$aSecondaryAdminBar[1000]['items'][] = array(
					'intitle' => 'Controller&nbsp;: '.$aRequestAttributes['_controller']
				);
				unset($aRequestAttributes['_controller']);
			}

			if (!empty($aRequestAttributes))
			{
				foreach ($aRequestAttributes as $k=>$v)
				{
					$aSecondaryAdminBar[1000]['items'][] = array(
						'intitle' => $k.'&nbsp;: '.(is_array($v) ? implode($v) : $v)
					);
				}
			}

			if (!empty($okt->page->module))
			{
				$aSecondaryAdminBar[1000]['items'][] = array(
					'intitle' => '$okt->page->module&nbsp;: '.$okt->page->module
				);
			}

			if (!empty($okt->page->action))
			{
				$aSecondaryAdminBar[1000]['items'][] = array(
					'intitle' => '$okt->page->action&nbsp;: '.$okt->page->action
				);
			}
		}


		# -- CORE TRIGGER : publicAdminBarItems
		$okt->triggers->callTrigger('publicAdminBarItems', $okt, $aPrimaryAdminBar, $aSecondaryAdminBar, $aBasesUrl);


		# sort items of by keys
		$aPrimaryAdminBar->ksort();
		$aSecondaryAdminBar->ksort();

		# remove empty values of admins bars
		$aPrimaryAdminBar = array_filter((array)$aPrimaryAdminBar);
		$aSecondaryAdminBar = array_filter((array)$aSecondaryAdminBar);

		# reverse sedond bar items
		$aSecondaryAdminBar = array_reverse($aSecondaryAdminBar);


		$class = '';
		?>
		<div id="oktadminbar" class="<?php echo $class; ?>" role="navigation">
			<a class="screen-reader-shortcut" href="#okt-toolbar" tabindex="1"><?php _e('Skip to toolbar'); ?>
			</a>
			<div class="quicklinks" id="okt-toolbar" role="navigation"
				aria-label="<?php echo Utilities::escapeAttrHTML(__('Top navigation toolbar.')); ?>"
				tabindex="0">
				<ul class="ab-top-menu">
					<?php foreach ($aPrimaryAdminBar as $aPrimaryItem) {
						echo self::getItems($aPrimaryItem);
					} ?>
				</ul>
				<ul class="ab-top-secondary ab-top-menu">
					<?php foreach ($aSecondaryAdminBar as $aSecondaryItem) {
						echo self::getItems($aSecondaryItem);
					} ?>
				</ul>
			</div>
			<a class="screen-reader-shortcut"
				href="<?php echo $aBasesUrl['logout'] ?>"><?php _e('c_c_user_log_off_action'); ?>
			</a>
		</div>
		<?php
	}

	protected static function getItems($aItem)
	{
		$sReturn = '';

		if (isset($aItem['items']))
		{
			ksort($aItem['items']);

			$aItem['items'] = array_filter($aItem['items']);

			if (empty($aItem['items'])) {
				return null;
			}

			$sReturn = '<li class="menupop">'.self::getItem($aItem, true);

			$sReturn .=
			'<div class="ab-sub-wrapper">
				<ul class="ab-submenu">';

			foreach ($aItem['items'] as $aSubItem) {
				$sReturn .= '<li>'.self::getItem($aSubItem).'</li>';
			}

			$sReturn .=
			'</ul>
				</div>
				</li>';
		}
		else {
			$sReturn = '<li>'.self::getItem($aItem).'</li>';
		}

		return $sReturn;
	}

	protected static function getItem($aItem, $haspopup=false)
	{
		if (empty($aItem['href']))
		{
			return
				'<div class="ab-item ab-empty-item"'.
				(!empty($aItem['title']) ? ' title="'.Utilities::escapeAttrHTML($aItem['title']).'"' : '').'>'.
				$aItem['intitle'].'</div>';
		}
		else
		{
			return
				'<a class="ab-item" href="'.$aItem['href'].'"'.
				($haspopup ? ' aria-haspopup="true"' : '').
				(!empty($aItem['title']) ? ' title="'.Utilities::escapeAttrHTML($aItem['title']).'"' : '').'>'.
				$aItem['intitle'].'</a>';
		}
	}
}
