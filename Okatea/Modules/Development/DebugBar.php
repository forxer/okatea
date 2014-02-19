<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Development;

use Okatea\Tao\Misc\Utilities;

class DebugBar
{
	/**
	 * Okatea application instance.
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * La config de la debug bar.
	 *
	 * @var array
	 */
	protected $aConfig;

	/**
	 * Les données de debug.
	 *
	 * @var array
	 */
	protected $aDebugBarData;


	public function __construct($okt, $aConfig)
	{
		$this->okt = $okt;
		$this->aConfig = $aConfig;
	}

	/**
	 * Ajout de la debug barre côté admin.
	 *
	 * @return void
	 */
	public function loadInAdminPart()
	{
		if (!$this->okt->options->get('debug') || !$this->aConfig['admin']) {
			return false;
		}

		$this->okt->triggers->registerTrigger('adminBeforeHtmlBodyEndTag',
			array($this, 'addHtmlByBehavior'));

		$this->okt->page->css->addFile($this->okt->options->public_url.'/plugins/jquery-ui/themes/'.$this->okt->config->jquery_ui['admin'].'/jquery-ui.css');

		$this->addFiles();
	}

	/**
	 * Ajout de la debug barre côté public.
	 *
	 * @return void
	 */
	public function loadInPublicPart()
	{
		if (!$this->okt->options->get('debug') || !$this->aConfig['public']) {
			return false;
		}

		$this->okt->triggers->registerTrigger('publicBeforeHtmlBodyEndTag',
			array($this, 'addHtmlByBehavior'));

		$this->okt->page->css->addFile($this->okt->options->public_url.'/plugins/jquery-ui/themes/'.$this->okt->config->jquery_ui['public'].'/jquery-ui.css');

		$this->addFiles();

		$this->okt->page->css->addCss('
			#debugTabs {font-size: 0.75em; }
			#debugPanel {font-size: 0.9em; }
		');
	}

	/**
	 * Le behavior qui affiche le HTML de la debug barre.
	 *
	 * @param Okatea\Tao\Application $okt
	 */
	public function addHtmlByBehavior()
	{
		echo $this->okt->Development->debugBar->getHtml();
	}

	/**
	 * Ajout des fichiers JS et CSS de la debug barre.
	 *
	 * @return void
	 */
	public function addFiles()
	{
		$this->okt->page->js->addFile($this->okt->options->public_url .'/components/jquery/dist/jquery.min.js');
		$this->okt->page->js->addFile($this->okt->options->public_url.'/plugins/jquery-ui/jquery-ui.min.js');

		$this->okt->page->js->addReady('

			var debugBar = $("#debugBar");

			debugBar
				.draggable({
					handle: "#debugTabs",
					cursor: "move"
				})
				.tabs({
					collapsible: true,
					active: false,
					heightStyleType: "auto"
				})
				.css({
					"display": "block",
					"width": "650px",
					"opacity": "0.5",
					"z-index": "9999",
					"position": "fixed",
					"top": "2em",
					"right": "3em"
				})
				.mouseenter(function() {
					$(this).css("opacity", "1");
				})
				.mouseleave(function() {
					$(this).css("opacity", "0.5");
				});

			$("#sprites_link").click(function(){

				var dialog = $("<div style=\"display:hidden\"></div>").appendTo("body");

				dialog.dialog({
					title: "Fam Fam Fam Sprites",
					width: 830,
					height: 630,
					autoOpen: false
				});

				dialog.load(this.href, {},
					function (responseText, textStatus, XMLHttpRequest) {
						if (status == "error") {
							dialog.remove(); // on retirent la boite
						}
						else {
							debugBar.tabs("option", "active", false);// on ferment les onglets
							dialog.dialog("open"); // et on ouvrent la boite
						}
					}
				);

				return false;
			});
		');

		$this->okt->page->css->addCss('
			#debugPanel {
				height: auto;
				max-height: 400px;
				overflow: auto;
			}
		');

		if ($this->aConfig['holmes'])
		{
			$this->okt->page->css->addFile($this->okt->options->public_url.'/css/holmes/holmes.min.css');

			$this->okt->page->js->addReady('
				$("body").addClass("holmes-debug");
			');
		}
	}

	/**
	 * Détermine les données de la debug bar.
	 *
	 * @return void
	 */
	public function setData()
	{
		$this->aDebugBarData = array();
		$this->aDebugBarData['num_data']= array();

		if ($this->aConfig['tabs']['super_globales'])
		{
			$this->aDebugBarData['num_data']['get'] = count($this->okt->request->query);
			$this->aDebugBarData['num_data']['post'] = count($this->okt->request->request);
			$this->aDebugBarData['num_data']['cookies'] = count($this->okt->request->cookies);
			$this->aDebugBarData['num_data']['attributes'] = count($this->okt->request->attributes);
			$this->aDebugBarData['num_data']['files'] = count($this->okt->request->files);
			$this->aDebugBarData['num_data']['session'] = count($this->okt->session);
			$this->aDebugBarData['num_data']['server'] = count($this->okt->request->server);
		}

		if ($this->aConfig['tabs']['app'])
		{
			$this->aDebugBarData['definedVars'] = self::getDefinedVars();
			$this->aDebugBarData['definedConstants'] = self::getDefinedConstants();
			$this->aDebugBarData['configVars'] = $this->okt->config->get();
			$this->aDebugBarData['userVars'] = $this->okt->user->getData(0);
			$this->aDebugBarData['l10nVars'] = (!empty($__l10n) ? $__l10n: array());

			$this->aDebugBarData['num_data']['definedVars'] = count($this->aDebugBarData['definedVars']);
			$this->aDebugBarData['num_data']['definedConstants'] = count($this->aDebugBarData['definedConstants']);
			$this->aDebugBarData['num_data']['configVars'] = count($this->aDebugBarData['configVars']);
			$this->aDebugBarData['num_data']['userVars'] = count($this->aDebugBarData['userVars']);
			$this->aDebugBarData['num_data']['l10nVars'] = count($this->aDebugBarData['l10nVars']);
		}

		if ($this->aConfig['tabs']['db'])
		{
			$this->aDebugBarData['num_data']['queries'] = $this->okt->db->nbQueries();
		}

		if ($this->aConfig['tabs']['tools'])
		{
			$this->aDebugBarData['execTime'] = Utilities::getExecutionTime();
			$this->aDebugBarData['memUsage'] = Utilities::l10nFileSize(memory_get_usage());
			$this->aDebugBarData['peakUsage'] = Utilities::l10nFileSize(memory_get_peak_usage());
		}

		$aRequestAttributes = $this->okt->request->attributes->all();

		$this->aDebugBarData['route'] = '';
		if (!empty($aRequestAttributes['_route']))
		{
			$this->aDebugBarData['route'] = $aRequestAttributes['_route'];
			unset($aRequestAttributes['_route']);
		}

		$this->aDebugBarData['controller'] = '';
		if (!empty($aRequestAttributes['controller']))
		{
			$this->aDebugBarData['controller'] = $aRequestAttributes['controller'];
			unset($aRequestAttributes['controller']);
		}

		$this->aDebugBarData['requestAttributes'] = array();
		if (!empty($aRequestAttributes)) {
			$this->aDebugBarData['requestAttributes'] = $aRequestAttributes;
		}
	}

	/**
	 * Retourne le HTML de la debug barre.
	 *
	 * @return string
	 */
	public function getHtml()
	{
		$this->setData();

		return sprintf($this->getHtmlBlock(),
			($this->aConfig['tabs']['super_globales'] ? $this->getSuperGlobalesPanel() : '').
			($this->aConfig['tabs']['app'] ? $this->getAppPanel() : '').
			($this->aConfig['tabs']['db'] ? $this->getDatabasePanel() : '').
			($this->aConfig['tabs']['tools'] ? $this->getToolsPanel() : '')
		);
	}

	protected function getHtmlBlock()
	{
		$aItems = array();

		$sBaseUrl = '';

//		if (isset($this->okt->router) && $this->okt->router->getFindedRoute() !== null) {
//			$sBaseUrl = $this->okt->page->getBaseUrl().$this->okt->router->getPath();
//		}

		$sBaseUrl = $_SERVER['REQUEST_URI'];

		if ($this->aConfig['tabs']['super_globales']) {
			$aItems[] = '<a href="'.$sBaseUrl.'#debugGlobales">Superglobales</a>';
		}

		if ($this->aConfig['tabs']['app']) {
			$aItems[] = '<a href="'.$sBaseUrl.'#debugApp">Application</a>';
		}

		if ($this->aConfig['tabs']['db']) {
			$aItems[] = '<a href="'.$sBaseUrl.'#debugDatabase">'.$this->aDebugBarData['num_data']['queries'].' requêtes</a>';
		}

		if ($this->aConfig['tabs']['tools']) {
			$aItems[] = '<a href="'.$sBaseUrl.'#debugTools">'.$this->aDebugBarData['execTime'].' s - '.$this->aDebugBarData['memUsage'].'</a>';
		}

		return
		'<div id="debugBar" style="display:none">
			<ul id="debugTabs">'.
			'<li>'.implode('</li><li>',$aItems).'</li>'.
			'</ul><!-- #debugTabs -->
			<div id="debugPanel">
			%s
			</div><!-- #debugPanel -->
		</div><!-- #debugBar -->';
	}

	protected function getSuperGlobalesPanel()
	{
		$sListitems = '';
		$sTabContent = '';

		if ($this->aDebugBarData['num_data']['get'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_get">Get - '.
				$this->aDebugBarData['num_data']['get'].'</a></li>';

			$sTabContent .= '<h3 id="superglobal_get">Get</h3>'.
				'<div><pre>'.var_export($this->okt->request->query->all(), true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['post'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_post">Post - '.
				$this->aDebugBarData['num_data']['post'].'</a></li>';

			$sTabContent .= '<h3 id="superglobal_post">Post</h3>'.
				'<div><pre>'.var_export($this->okt->request->request->all(), true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['cookies'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_cookies">Cookies - '.
				$this->aDebugBarData['num_data']['cookies'].'</a></li>';

			$sTabContent .= '<h3 id="superglobal_cookies">Cookies</h3>'.
				'<div><pre>'.var_export($this->okt->request->cookies->all(), true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['attributes'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_attributes">Attributes - '.
				$this->aDebugBarData['num_data']['attributes'].'</a></li>';

			$sTabContent .= '<h3 id="superglobal_attributes">Attributess</h3>'.
				'<div><pre>'.var_export($this->okt->request->attributes->all(), true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['files'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_files">Files - '.
				$this->aDebugBarData['num_data']['files'].'</a></li>';

			$sTabContent .= '<h3 id="superglobal_files">Files</h3>'.
				'<div><pre>'.var_export($this->okt->request->files->all(), true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['session'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_session">Session - '.
				$this->aDebugBarData['num_data']['session'].'</a></li>';

			$sTabContent .= '<h3 id="superglobal_session">Session</h3>'.
				'<div><pre>'.var_export($this->okt->session->all(), true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['server'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_server">Server - '.
				$this->aDebugBarData['num_data']['server'].'</a></li>';

			$sTabContent .= '<h3 id="superglobal_server">Server</h3>'.
				'<div><pre>'.var_export($this->okt->request->server->all(), true).'</pre></div>';
		}


		$sListitems .= '<li><a href="#superglobal_others">Others utilities values</a></li>';

		$sTabContent .= '<h3 id="superglobal_others">Others utilities values</h3>'.
			'<div>'.
			'<p><code>$okt->request->getClientIps()</code> : '.var_export($this->okt->request->getClientIps(), true).'</p>'.
			'<p><code>$okt->request->getClientIp()</code> : '.$this->okt->request->getClientIp().'</p>'.
			'<p><code>$okt->request->getScriptName()</code> : '.$this->okt->request->getScriptName().'</p>'.
			'<p><code>$okt->request->getPathInfo()</code>  : '.$this->okt->request->getPathInfo().'</p>'.
			'<p><code>$okt->request->getBasePath()</code> : '.$this->okt->request->getBasePath().'</p>'.
			'<p><code>$okt->request->getBaseUrl()</code> : '.$this->okt->request->getBaseUrl().'</p>'.
			'<p><code>$okt->request->getScheme()</code> : '.$this->okt->request->getScheme().'</p>'.
			'<p><code>$okt->request->getPort()</code> : '.$this->okt->request->getPort().'</p>'.
			'<p><code>$okt->request->getUser()</code> : '.$this->okt->request->getUser().'</p>'.
			'<p><code>$okt->request->getPassword()</code> : '.$this->okt->request->getPassword().'</p>'.
			'<p><code>$okt->request->getUserInfo()</code> : '.$this->okt->request->getUserInfo().'</p>'.
			'<p><code>$okt->request->getHttpHost()</code> : '.$this->okt->request->getHttpHost().'</p>'.
			'<p><code>$okt->request->getRequestUri()</code> : '.$this->okt->request->getRequestUri().'</p>'.
			'<p><code>$okt->request->getSchemeAndHttpHost()</code> : '.$this->okt->request->getSchemeAndHttpHost().'</p>'.
			'<p><code>$okt->request->getUri()</code> : '.$this->okt->request->getUri().'</p>'.
			'<p><code>$okt->request->getUriForPath(\'/un/chemin\')</code> : '.$this->okt->request->getUriForPath('/un/chemin').'</p>'.
			'<p><code>$okt->request->getQueryString()</code> : '.$this->okt->request->getQueryString().'</p>'.
			'<p><code>$okt->request->isSecure()</code> : '.var_export($this->okt->request->isSecure(), true).'</p>'.
			'<p><code>$okt->request->getHost()</code>  : '.$this->okt->request->getHost().'</p>'.
			'<p><code>$okt->request->getMethod()</code>  : '.$this->okt->request->getMethod().'</p>'.
			'<p><code>$okt->request->getRealMethod()</code>  : '.$this->okt->request->getRealMethod().'</p>'.
			'<p><code>$okt->request->getMimeType(\'html\')</code>  : '.$this->okt->request->getMimeType('html').'</p>'.
			'<p><code>$okt->request->getFormat(\'application/json\')</code>  : '.$this->okt->request->getFormat('application/json').'</p>'.
			'<p><code>$okt->request->getRequestFormat()</code>  : '.$this->okt->request->getRequestFormat().'</p>'.
			'<p><code>$okt->request->getContentType()</code>  : '.$this->okt->request->getContentType().'</p>'.
			'<p><code>$okt->request->getETags()</code>  : '.var_export($this->okt->request->getETags(), true).'</p>'.
			'<p><code>$okt->request->isNoCache()</code>  : '.var_export($this->okt->request->isNoCache(), true).'</p>'.
			'<p><code>$okt->request->getPreferredLanguage()</code>  : '.var_export($this->okt->request->getPreferredLanguage(), true).'</p>'.
			'<p><code>$okt->request->getLanguages()</code>  : '.var_export($this->okt->request->getLanguages(), true).'</p>'.
			'<p><code>$okt->request->getCharsets()</code>  : '.var_export($this->okt->request->getCharsets(), true).'</p>'.
			'<p><code>$okt->request->getEncodings()</code>  : '.var_export($this->okt->request->getEncodings(), true).'</p>'.
			'<p><code>$okt->request->getAcceptableContentTypes()</code>  : '.var_export($this->okt->request->getAcceptableContentTypes(), true).'</p>'.
			'<p><code>$okt->request->isXmlHttpRequest()</code>  : '.var_export($this->okt->request->isXmlHttpRequest(), true).'</p>'.
			'</div>';

		return
		'<div id="debugGlobales">'.
		'<ul>'.$sListitems.'</ul>'.
		$sTabContent.
		'</div><!-- #debugGlobales -->';
	}

	protected function getAppPanel()
	{
		$sListitems = '';
		$sTabContent = '';

		if ($this->aDebugBarData['num_data']['definedVars'] > 0)
		{
			$sListitems .= '<li><a href="#app_definedVars">Variables ('.
				$this->aDebugBarData['num_data']['definedVars'].')</a></li>';

			$sTabContent .= '<h3 id="app_definedVars">Variables dans le scope global</h3>'.
				'<div><pre>'.var_export($this->aDebugBarData['definedVars'],true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['definedConstants'] > 0)
		{
			$sListitems .= '<li><a href="#app_definedConstants">Constantes ('.
				$this->aDebugBarData['num_data']['definedConstants'].')</a></li>';

			$sTabContent .= '<h3 id="app_definedConstants">Constantes</h3>'.
				'<div><pre>'.var_export($this->aDebugBarData['definedConstants'],true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['configVars'] > 0)
		{
			$sListitems .= '<li><a href="#app_configVars">Configuration ('.
				$this->aDebugBarData['num_data']['configVars'].')</a></li>';

			$sTabContent .= '<h3 id="app_configVars">Configuration</h3>'.
				'<div><pre>'.var_export($this->aDebugBarData['configVars'],true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['userVars'] > 0)
		{
			$sListitems .= '<li><a href="#app_userVars">Utilisateur ('.
				$this->aDebugBarData['num_data']['userVars'].')</a></li>';

			$sTabContent .= '<h3 id="app_userVars">Variables utilisateur</h3>'.
				'<div><pre>'.var_export($this->aDebugBarData['userVars'],true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['l10nVars'] > 0)
		{
			$sListitems .= '<li><a href="#app_l10nVars">Localisation ('.
				$this->aDebugBarData['num_data']['l10nVars'].')</a></li>';

			$sTabContent .= '<h3 id="app_l10nVars">Variables de localisation</h3>'.
				'<div><pre>'.var_export($this->aDebugBarData['l10nVars'],true).'</pre></div>';
		}

		return
		'<div id="debugApp">'.
		'<ul>'.$sListitems.'</ul>'.
		$sTabContent.
		'</div><!-- #debugApp -->';
	}

	protected function getDatabasePanel()
	{
		$str =
		'<div id="debugDatabase">
			<table class="common">
				<thead>
				<tr>
					<th>ID</th>
					<th>Query</th>
					<th>Time</th>
				</tr>
				</thead>
				<tbody>';

				foreach ($this->okt->db->getLog() as $query)
				{
					$str .=
					'<tr>
						<td>'.$query[0].'</td>
						<td>'.\SqlFormatter::format($query[1]).'</td>
						<td>'.$query[2].'</td>
					</tr>';
				}

		$str .= '</tbody>
			</table>
		</div><!-- #debugDatabase -->';

		return $str;
	}

	protected function getToolsPanel()
	{
		$str =
		'<div id="debugTools">
			<ul>
				<li>Mémoire utilisée par PHP&nbsp;: '.$this->aDebugBarData['memUsage'].'</li>
				<li>Pic mémoire allouée par PHP&nbsp;: '.$this->aDebugBarData['peakUsage'].'</li>
				<li>Temps d\'execution du script&nbsp;: '.$this->aDebugBarData['execTime'].' s</li>
			</ul>
			<ul>
				<li>Route&nbsp;: '.$this->aDebugBarData['route'].'</li>
				<li>Controller&nbsp;: '.$this->aDebugBarData['controller'].'</li>
				<li>Autre(s) attribut(s)&nbsp;: <ul><li>'.implode('</li><li>', $this->aDebugBarData['requestAttributes']).'</li></ul></li></ul>
			<ul>
				<li>$okt->page->module&nbsp;: '.(!empty($this->okt->page->module) ? $this->okt->page->module : '').'</li>
				<li>$okt->page->action&nbsp;: '.(!empty($this->okt->page->action) ? $this->okt->page->action : '').'</li>
			</ul>
			<ul>
				<li><a href="'.$this->okt->options->public_url.'/img/ico/sprites.html" id="sprites_link">Sprites</a></li>
			</ul>
		</div><!-- #debugTools -->';

		return $str;
	}


	/* Méthodes utilitaires
	----------------------------------------------------------*/

	/**
	 * Get defined vars
	 *
	 * @param array $varList
	 * @return array
	 */
	public static function getDefinedVars()
	{
		return array_values(
			array_diff(array_keys($GLOBALS),array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_FILES', '_SERVER', '_ENV', '_SESSION'))
		);
	}

	/**
	 * Get defined constants
	 *
	 * @param array $varList
	 * @return array
	 */
	public static function getDefinedConstants()
	{
		$c = get_defined_constants(true);
		return $c['user'];
	}

}
