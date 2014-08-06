<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Website;

use ArrayObject;
use Okatea\Admin\Router as AdminRouter;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Update as Updater;
use Okatea\Tao\Users\Users;

/**
 * La classe pour afficher la barre admin côté site web.
 */
class AdminBar
{
	/**
	 * Okatea application instance.
	 *
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	public function __construct($okt)
	{
		$this->okt = $okt;

		$this->okt['triggers']->registerTrigger('publicBeforeHtmlBodyEndTag', array(
			$this,
			'displayWebsiteAdminBar'
		));

		$this->okt->page->css->addFile($this->okt['public_url'] . '/css/admin-bar.css');
		$this->okt->page->js->addFile($this->okt['public_url'] . '/js/admin-bar.js');

		$this->okt['adminRouter'] = new AdminRouter($this->okt, $this->okt['config_path'] . '/RoutesAdmin', $this->okt['cache_path'] . '/routing/admin', $this->okt['debug']);
	}

	public function displayWebsiteAdminBar()
	{
		$aBasesUrl = new ArrayObject();
		$aPrimaryAdminBar = new ArrayObject();
		$aSecondaryAdminBar = new ArrayObject();

		$aBasesUrl['admin'] = $this->okt['config']->app_url . 'admin/';
		$aBasesUrl['logout'] = $this->okt['router']->generate('usersLogout');
		$aBasesUrl['profil'] = $aBasesUrl['admin'];

		# -- CORE TRIGGER : websiteAdminBarBeforeDefaultsItems
		$this->okt['triggers']->callTrigger('websiteAdminBarBeforeDefaultsItems', $aPrimaryAdminBar, $aSecondaryAdminBar, $aBasesUrl);

		# éléments première barre
		$aPrimaryAdminBar[10] = array(
			'intitle' => '<img src="' . $this->okt['public_url'] . '/img/notify/error.png" width="22" height="22" alt="' . __('c_c_warning') . '" />',
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
			'intitle' => sprintf(__('c_c_user_hello_%s'), Escaper::html(Users::getUserDisplayName($this->okt['visitor']->username, $this->okt['visitor']->lastname, $this->okt['visitor']->firstname, $this->okt['visitor']->displayname)))
		);

		if (! $this->okt['languages']->hasUniqueLanguage())
		{
			$iStartIdx = 150;
			foreach ($this->okt['languages']->getList() as $aLanguage)
			{
				if ($aLanguage['code'] == $this->okt['visitor']->language)
				{
					continue;
				}

				$aSecondaryAdminBar[$iStartIdx ++] = array(
					'href' => Escaper::html($this->okt['config']->app_url . $aLanguage['code'] . '/'),
					'title' => Escaper::html($aLanguage['title']),
					'intitle' => '<img src="' . $this->okt['public_url'] . '/img/flags/' . $aLanguage['img'] . '" alt="' . Escaper::html($aLanguage['title']) . '" />'
				);
			}
		}

		$aSecondaryAdminBar[200] = array(
			'href' => $aBasesUrl['logout'],
			'intitle' => __('c_c_user_log_off_action')
		);

		# infos super-admin
		if ($this->okt['visitor']->checkPerm('is_superadmin'))
		{
			# avertissement mode debug activé
			if ($this->okt['debug'])
			{
				$aPrimaryAdminBar[10]['items'][110] = array(
					'intitle' => __('c_a_public_debug_mode_enabled')
				);
			}

			# avertissement nouvelle version disponible
			if ($this->okt['config']->updates['enabled'] && is_readable($this->okt['digests_path']))
			{
				$updater = new Updater($this->okt['config']->updates['url'], 'okatea', $this->okt['config']->updates['type'], $this->okt['cache_path'] . '/versions');
				$new_v = $updater->check($this->okt->getVersion());

				if ($updater->getNotify() && $new_v)
				{
					# locales
					$this->okt['l10n']->loadFile($this->okt['locales_path'] . '/%s/admin.update');

					$aPrimaryAdminBar[10]['items'][120] = array(
						'href' => $aBasesUrl['admin'] . '/configuration.php?action=update',
						'intitle' => sprintf(__('c_a_update_okatea_%s_available'), $new_v)
					);
				}
			}

			# avertissement mode maintenance est activé sur la partie publique
			if ($this->okt['config']->maintenance['public'])
			{
				$aPrimaryAdminBar[10]['items'][130] = array(
					'href' => $aBasesUrl['admin'] . '/configuration.php?action=advanced#tab_others',
					'intitle' => __('c_a_maintenance_public_enabled')
				);
			}

			# avertissement mode maintenance est activé sur l'admin
			if ($this->okt['config']->maintenance['admin'])
			{
				$aPrimaryAdminBar[10]['items'][140] = array(
					'href' => $aBasesUrl['admin'] . '/configuration.php?action=advanced#tab_others',
					'intitle' => __('c_a_maintenance_admin_enabled')
				);
			}

			# info execution
			$aExecInfos = array();
			$aExecInfos['execTime'] = Utilities::getExecutionTime();
			$aExecInfos['memUsage'] = Utilities::l10nFileSize(memory_get_usage());
			$aExecInfos['peakUsage'] = Utilities::l10nFileSize(memory_get_peak_usage());

			$aSecondaryAdminBar[1000] = array(
				'intitle' => '<img src="' . $this->okt['public_url'] . '/img/ico/terminal.gif" width="16" height="16" alt="" />',
				'items' => array(
					array(
						'intitle' => 'Temps d\'execution du script&nbsp;: ' . $aExecInfos['execTime'] . ' s'
					),
					array(
						'intitle' => 'Mémoire Utilisée par PHP&nbsp;: ' . $aExecInfos['memUsage']
					),
					array(
						'intitle' => 'Pic mémoire allouée par PHP&nbsp;: ' . $aExecInfos['peakUsage']
					)
				)
			);

			$aRequestAttributes = $this->okt['request']->attributes->all();

			if (! empty($aRequestAttributes['_route']))
			{
				$aSecondaryAdminBar[1000]['items'][] = array(
					'intitle' => 'Route&nbsp;: ' . $aRequestAttributes['_route']
				);
				unset($aRequestAttributes['_route']);
			}

			if (! empty($aRequestAttributes['controller']))
			{
				$aSecondaryAdminBar[1000]['items'][] = array(
					'intitle' => 'Controller&nbsp;: ' . $aRequestAttributes['controller']
				);
				unset($aRequestAttributes['controller']);
			}

			if (! empty($aRequestAttributes))
			{
				foreach ($aRequestAttributes as $k => $v)
				{
					$aSecondaryAdminBar[1000]['items'][] = array(
						'intitle' => $k . '&nbsp;: ' . (is_array($v) ? implode($v) : $v)
					);
				}
			}
		}

		# -- CORE TRIGGER : websiteAdminBarItems
		$this->okt['triggers']->callTrigger('websiteAdminBarItems', $aPrimaryAdminBar, $aSecondaryAdminBar, $aBasesUrl);

		# sort items of by keys
		$aPrimaryAdminBar->ksort();
		$aSecondaryAdminBar->ksort();

		# remove empty values of admins bars
		$aPrimaryAdminBar = array_filter((array) $aPrimaryAdminBar);
		$aSecondaryAdminBar = array_filter((array) $aSecondaryAdminBar);

		# reverse sedond bar items
		$aSecondaryAdminBar = array_reverse($aSecondaryAdminBar);

		$class = '';
		?>
<div id="oktadminbar" class="<?php echo $class; ?>" role="navigation">
	<a class="screen-reader-shortcut" href="#okt-toolbar" tabindex="1"><?php _e('Skip to toolbar'); ?>
			</a>
	<div class="quicklinks" id="okt-toolbar" role="navigation"
		aria-label="<?php echo Escaper::attribute(__('Top navigation toolbar.')); ?>"
		tabindex="0">
		<ul class="ab-top-menu">
					<?php

foreach ($aPrimaryAdminBar as $aPrimaryItem)
		{
			echo self::getItems($aPrimaryItem);
		}
		?>
				</ul>
		<ul class="ab-top-secondary ab-top-menu">
					<?php

foreach ($aSecondaryAdminBar as $aSecondaryItem)
		{
			echo self::getItems($aSecondaryItem);
		}
		?>
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

			if (empty($aItem['items']))
			{
				return null;
			}

			$sReturn = '<li class="menupop">' . self::getItem($aItem, true);

			$sReturn .= '<div class="ab-sub-wrapper">
				<ul class="ab-submenu">';

			foreach ($aItem['items'] as $aSubItem)
			{
				$sReturn .= '<li>' . self::getItem($aSubItem) . '</li>';
			}

			$sReturn .= '</ul>
				</div>
				</li>';
		}
		else
		{
			$sReturn = '<li>' . self::getItem($aItem) . '</li>';
		}

		return $sReturn;
	}

	protected static function getItem($aItem, $haspopup = false)
	{
		if (empty($aItem['href']))
		{
			return '<div class="ab-item ab-empty-item"' . (! empty($aItem['title']) ? ' title="' . Escaper::attribute($aItem['title']) . '"' : '') . '>' . $aItem['intitle'] . '</div>';
		}
		else
		{
			return '<a class="ab-item" href="' . $aItem['href'] . '"' . ($haspopup ? ' aria-haspopup="true"' : '') . (! empty($aItem['title']) ? ' title="' . Escaper::attribute($aItem['title']) . '"' : '') . '>' . $aItem['intitle'] . '</a>';
		}
	}
}
