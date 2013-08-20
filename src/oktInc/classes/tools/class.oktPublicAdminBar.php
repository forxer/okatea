<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class oktPublicAdminBar
 * @ingroup okt_classes_tools
 * @brief La classe pour afficher la barre admin côté publique
 *
 */
class oktPublicAdminBar
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
			array('oktPublicAdminBar', 'displayPublicAdminBar'));

		$this->okt->page->css->addFile(OKT_PUBLIC_URL.'/css/admin-bar.css');
		$this->okt->page->js->addFile(OKT_PUBLIC_URL.'/js/admin-bar.js');
	}

	public static function displayPublicAdminBar($okt)
	{
		$aBasesUrl = new ArrayObject;
		$aPrimaryAdminBar = new ArrayObject;
		$aSecondaryAdminBar = new ArrayObject;

		$aBasesUrl['admin'] = $okt->config->app_path.OKT_ADMIN_DIR;
		$aBasesUrl['logout'] = $aBasesUrl['admin'].'/index.php?logout=1';
		$aBasesUrl['profil'] = $aBasesUrl['admin'];


		# -- CORE TRIGGER : publicAdminBarBeforeDefaultsItems
		$okt->triggers->callTrigger('publicAdminBarBeforeDefaultsItems', $okt, $aPrimaryAdminBar, $aSecondaryAdminBar, $aBasesUrl);


		# éléments première barre
		$aPrimaryAdminBar[10] = array(
			'intitle' => '<img src="'.OKT_PUBLIC_URL.'/img/notify/error.png" width="22" height="22" alt="'.__('c_c_warning').'" />',
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
			'intitle' => sprintf(__('c_c_user_hello_%s'), html::escapeHTML(oktAuth::getUserCN($okt->user->username, $okt->user->lastname, $okt->user->firstname)))
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
					'href' => html::escapeHTML($okt->config->app_path.$aLanguage['code'].'/'),
					'title' => html::escapeHTML($aLanguage['title']),
					'intitle' => '<img src="'.OKT_PUBLIC_URL.'/img/flags/'.$aLanguage['img'].'" alt="'.html::escapeHTML($aLanguage['title']).'" />'
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
			# avertissement nouvelle version disponible
			if ($okt->config->update_enabled && is_readable(OKT_DIGESTS))
			{
				$updater = new oktUpdate($okt->config->update_url, 'okatea', $okt->config->update_type, OKT_CACHE_PATH.'/versions');
				$new_v = $updater->check(util::getVersion());

				if ($updater->getNotify() && $new_v)
				{
					# locales
					l10n::set(OKT_LOCALES_PATH.'/'.$okt->user->language.'/admin.update');

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
					'intitle' => sprintf(__('c_a_public_maintenance_mode_enabled'), $new_v)
				);
			}

			# avertissement mode maintenance est activé sur l'admin
			if ($okt->config->admin_maintenance_mode)
			{
				$aPrimaryAdminBar[10]['items'][400] = array(
					'href' => $aBasesUrl['admin'].'/configuration.php?action=advanced#tab_others',
					'intitle' => sprintf(__('c_a_admin_maintenance_mode_enabled'), $new_v)
				);
			}

			# info execution
			$aExecInfos = array();
			$aExecInfos['execTime'] = util::getExecutionTime();

			if (OKT_XDEBUG)
			{
				$aExecInfos['memUsage'] = util::l10nFileSize(xdebug_memory_usage());
				$aExecInfos['peakUsage'] = util::l10nFileSize(xdebug_peak_memory_usage());
			}
			else {

				$aExecInfos['memUsage'] = util::l10nFileSize(memory_get_usage());
				$aExecInfos['peakUsage'] = util::l10nFileSize(memory_get_peak_usage());
			}

			$aSecondaryAdminBar[1000] = array(
				'title' => $aExecInfos['execTime'].' s - '.$aExecInfos['memUsage'],
				'intitle' => '<img src="'.OKT_PUBLIC_URL.'/img/ico/terminal.gif" with="16" height="16" alt="" />',
				'items' => array(
					array(
						'intitle' => 'Temps d\'execution du script&nbsp;: '.$aExecInfos['execTime'].' s'
					),
					array(
						'intitle' => 'Mémoire utilisée par PHP&nbsp;: '.$aExecInfos['memUsage']
					),
					array(
						'intitle' => 'Pic mémoire allouée par PHP&nbsp;: '.$aExecInfos['peakUsage']
					),
					array(
						'intitle' => 'Router lang&nbsp;: '.$okt->router->getLanguage()
					),
					array(
						'intitle' => 'Router path&nbsp;: '.$okt->router->getPath()
					),
					array(
						'intitle' => 'Router route ID&nbsp;: '.$okt->router->getFindedRouteId()
					)
				)
			);

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
				aria-label="<?php echo util::escapeAttrHTML(__('Top navigation toolbar.')); ?>"
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
				(!empty($aItem['title']) ? ' title="'.util::escapeAttrHTML($aItem['title']).'"' : '').'>'.
				$aItem['intitle'].'</div>';
		}
		else
		{
			return
				'<a class="ab-item" href="'.$aItem['href'].'"'.
				($haspopup ? ' aria-haspopup="true"' : '').
				(!empty($aItem['title']) ? ' title="'.util::escapeAttrHTML($aItem['title']).'"' : '').'>'.
				$aItem['intitle'].'</a>';
		}
	}

}
