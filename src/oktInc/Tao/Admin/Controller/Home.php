<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Admin\Controller;

use Tao\Admin\Controller;
use Tao\Core\Update as Updater;
use Tao\Misc\Utilities;

class Home extends Controller
{
	protected $sNewVersion;

	protected $aRoundAboutItems;

	protected $bFeedSuccess = false;

	protected $feed;

	public function homePage()
	{
		$this->roundAbout();

		$this->konami();

		$this->newsFeed();

		$this->updateNotification();

		if ($this->okt->options->get('debug')) {
			$this->page->flash->warning(__('c_a_public_debug_mode_enabled'));
		}

		return $this->render('home', array(
			'sNewVersion' => $this->sNewVersion,
			'bFeedSuccess' => $this->bFeedSuccess,
			'feed' => $this->feed,
			'aRoundAboutItems' => (array)$this->aRoundAboutItems
		));
	}

	protected function roundAbout()
	{
		$roundAboutOptions = new \ArrayObject;
		$roundAboutOptions['tilt'] = 4;
		$roundAboutOptions['easing'] = 'easeOutElastic';
		$roundAboutOptions['duration'] = 1400;

		$this->page->css->addCss('
			#roundabout img {
				display: block;
				margin: 0 auto;
			}
			.roundabout-holder {
				list-style: none;
				width: 75%;
				height: 15em;
				margin: 1em auto;
			}
			.roundabout-moveable-item {
				height: 4em;
				width: 8em;
				font-size: 2em;
				text-align: center;
				cursor: pointer;
			}
			.roundabout-moveable-item a {
				text-decoration: none;
			}
			.roundabout-moveable-item a:focus {
				outline: none;
			}
			.roundabout-in-focus {
				cursor: auto;
			}
		');

		# -- CORE TRIGGER : adminIndexRoundaboutOptions
		$this->okt->triggers->callTrigger('adminIndexRoundaboutOptions', $this->okt, $roundAboutOptions);

		$this->page->roundabout($roundAboutOptions,'#roundabout');

		# RoundAbout defaults Items
		$this->aRoundAboutItems = new \ArrayObject;

		$sRoundAboutItemFormat = '<a href="%2$s">%3$s<span>%1$s</span></a>';

		foreach ($this->page->mainMenu->getItems() as $item)
		{
			$this->aRoundAboutItems[] = sprintf($sRoundAboutItemFormat, $item['title'], $item['url'],
				($item['icon'] ? '<img src="'.$item['icon'].'" alt="" />' : ''));
		}

		if ($this->okt->modules->moduleExists('users'))
		{
			$this->aRoundAboutItems[] = sprintf($sRoundAboutItemFormat, __('c_c_user_profile'), 'module.php?m=users&amp;action=profil&amp;id='.$this->okt->user->id,
				'<img src="'.$this->okt->options->public_url.'/img/admin/contact-new.png" alt="" />');
		}

		$this->aRoundAboutItems[] = sprintf($sRoundAboutItemFormat, __('c_c_user_Log_off_action'), 'index.php?logout=1',
			'<img src="'.$this->okt->options->public_url.'/img/admin/system-log-out.png" alt="" />');


		# -- CORE TRIGGER : adminIndexaRoundAboutItems
		$this->okt->triggers->callTrigger('adminIndexaRoundAboutItems', $this->okt, $this->aRoundAboutItems);
	}

	protected function konami()
	{
		$this->page->js->addScript('
			if (window.addEventListener) {
				var kkeys = [], konami = "38,38,40,40,37,39,37,39,66,65";
				window.addEventListener("keydown", function(e){
					kkeys.push(e.keyCode);
					if (kkeys.toString().indexOf( konami ) >= 0) {
						window.location = "http://okatea.org/";
					}
				}, true);
			}
		');
	}

	protected function newsFeed()
	{
		if (!$this->okt->config->news_feed['enabled'] || empty($this->okt->config->news_feed['url'][$this->okt->user->language])) {
			return null;
		}

		// We'll process this feed with all of the default options.
		$this->feed = new \SimplePie();

		# set cache directory
		$sCacheDir = $this->okt->options->get('cache_dir').'/feeds/';

		if (!is_dir($sCacheDir)) {
			\files::makeDir($sCacheDir, true);
		}

		$this->feed->set_cache_location($sCacheDir);

		// Set which feed to process.
		$this->feed->set_feed_url($this->okt->config->news_feed['url'][$this->okt->user->language]);

		// Run SimplePie.
		$this->bFeedSuccess = $this->feed->init();

		// This makes sure that the content is sent to the browser as text/html and the UTF-8 character set (since we didn't change it).
		$this->feed->handle_content_type();

		$this->page->css->addCss('
			#news_feed_list {
				height: 13em;
				width: 28%;
				overflow-y: scroll;
				overflow-x: hidden;
				padding-right: 0.8em;
				float: right;
			}
			#news_feed_list .ui-widget-header a {
				text-decoration: none;
			}
			#news_feed_list .ui-widget-header {
				margin-bottom: 0;
				padding: 0.3em 0.5em;
			}
			#news_feed_list .ui-widget-content {
				padding: 0.5em;
			}

			#roundabout-wrapper {
				float: left;
				width: 70%;
			}
		');
	}

	protected function updateNotification()
	{
		if ($this->okt->config->update_enabled && $this->okt->checkPerm('is_superadmin') && is_readable($this->okt->options->get('digests')))
		{
			$updater = new Updater($this->okt->config->update_url, 'okatea', $this->okt->config->update_type, $this->okt->options->get('cache_dir').'/versions');
			$this->sNewVersion = $updater->check(Utilities::getVersion());

			if ($updater->getNotify() && $this->sNewVersion) {
				$this->okt->l10n->loadFile($this->okt->options->locales_dir.'/'.$this->okt->user->language.'/admin.update');
			}
		}
	}
}
