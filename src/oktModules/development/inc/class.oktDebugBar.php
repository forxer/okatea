<?php
/**
 * @ingroup okt_module_development
 * @brief La classe de la debug barre.
 */

use Tao\Misc\Utilities as util;

class oktDebugBar
{
	/**
	 * L'objet core.
	 * @var object oktCore
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
		if (!OKT_DEBUG || !$this->aConfig['admin']) {
			return false;
		}

		$this->okt->triggers->registerTrigger('adminBeforeHtmlBodyEndTag',
			array('oktDebugBar','addHtmlByBehavior'));

		$this->okt->page->css->addFile(OKT_PUBLIC_URL.'/ui-themes/'.$this->okt->config->admin_theme.'/jquery-ui.css');

		$this->addFiles();
	}

	/**
	 * Ajout de la debug barre côté public.
	 *
	 * @return void
	 */
	public function loadInPublicPart()
	{
		if (!OKT_DEBUG || !$this->aConfig['public']) {
			return false;
		}

		$this->okt->triggers->registerTrigger('publicBeforeHtmlBodyEndTag',
			array('oktDebugBar','addHtmlByBehavior'));

		$this->okt->page->css->addFile(OKT_PUBLIC_URL.'/ui-themes/'.$this->okt->config->public_theme.'/jquery-ui.css');

		$this->addFiles();

		$this->okt->page->css->addCss('
			#debugTabs {font-size: 0.75em; }
			#debugPanel {font-size: 0.9em; }
		');
	}

	/**
	 * Le behavior qui affiche le HTML de la debug barre.
	 *
	 * @param oktCore $okt
	 */
	public static function addHtmlByBehavior($okt)
	{
		echo $okt->development->debugBar->getHtml();
	}

	/**
	 * Ajout des fichiers JS et CSS de la debug barre.
	 *
	 * @return void
	 */
	public function addFiles()
	{
		$this->okt->page->js->addFile(OKT_PUBLIC_URL .'/js/jquery/jquery.min.js');
		$this->okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/ui/jquery-ui.min.js');

		$this->okt->page->js->addFile(OKT_PUBLIC_URL.'/plugins/syntaxhighlighter/scripts/shCore.js');
		$this->okt->page->js->addFile(OKT_PUBLIC_URL.'/plugins/syntaxhighlighter/scripts/shBrushSql.js');
		$this->okt->page->js->addFile(OKT_PUBLIC_URL.'/plugins/syntaxhighlighter/scripts/shBrushPhp.js');

		$this->okt->page->css->addFile(OKT_PUBLIC_URL.'/plugins/syntaxhighlighter/styles/shCore.css');
		$this->okt->page->css->addFile(OKT_PUBLIC_URL.'/plugins/syntaxhighlighter/styles/shThemeEclipse.css');

		$this->okt->page->js->addScript('SyntaxHighlighter.all();');

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
			$this->okt->page->css->addFile(OKT_PUBLIC_URL.'/css/holmes/holmes.min.css');

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
			$this->aDebugBarData['num_data']['get'] = count($_GET);
			$this->aDebugBarData['num_data']['post'] = count($_POST);
			$this->aDebugBarData['num_data']['cookie'] = count($_COOKIE);
			$this->aDebugBarData['num_data']['files'] = count($_FILES);
			$this->aDebugBarData['num_data']['session'] = count($_SESSION);
			$this->aDebugBarData['num_data']['server'] = count($_SERVER);
			$this->aDebugBarData['num_data']['env'] = count($_ENV);
			$this->aDebugBarData['num_data']['request'] = count($_REQUEST);
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
			$this->aDebugBarData['execTime'] = util::getExecutionTime();

			if (OKT_XDEBUG)
			{
				$this->aDebugBarData['memUsage'] = util::l10nFileSize(xdebug_memory_usage());
				$this->aDebugBarData['peakUsage'] = util::l10nFileSize(xdebug_peak_memory_usage());
			}
			else {

				$this->aDebugBarData['memUsage'] = util::l10nFileSize(memory_get_usage());
				$this->aDebugBarData['peakUsage'] = util::l10nFileSize(memory_get_peak_usage());
			}
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
			$sListitems .= '<li><a href="#superglobal_get">_GET ('.
				$this->aDebugBarData['num_data']['get'].')</a></li>';

			$sTabContent .= '<h3 id="superglobal_get">_GET</h3>'.
				'<div><pre>'.var_export($_GET,true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['post'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_post">_POST ('.
				$this->aDebugBarData['num_data']['post'].')</a></li>';

			$sTabContent .= '<h3 id="superglobal_post">_POST</h3>'.
				'<div><pre>'.var_export($_POST,true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['cookie'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_cookie">_COOKIE ('.
				$this->aDebugBarData['num_data']['cookie'].')</a></li>';

			$sTabContent .= '<h3 id="superglobal_cookie">_COOKIE</h3>'.
				'<div><pre>'.var_export($_COOKIE,true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['files'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_files">_FILES ('.
				$this->aDebugBarData['num_data']['files'].')</a></li>';

			$sTabContent .= '<h3 id="superglobal_files">_FILES</h3>'.
				'<div><pre>'.var_export($_FILES,true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['session'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_session">_SESSION ('.
				$this->aDebugBarData['num_data']['session'].')</a></li>';

			$sTabContent .= '<h3 id="superglobal_session">_SESSION</h3>'.
				'<div><pre>'.var_export($_SESSION,true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['server'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_server">_SERVER ('.
				$this->aDebugBarData['num_data']['server'].')</a></li>';

			$sTabContent .= '<h3 id="superglobal_server">_SERVER</h3>'.
				'<div><pre>'.var_export($_SERVER,true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['env'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_env">_ENV ('.
				$this->aDebugBarData['num_data']['env'].')</a></li>';

			$sTabContent .= '<h3 id="superglobal_env">_ENV</h3>'.
				'<div><pre>'.var_export($_ENV,true).'</pre></div>';
		}

		if ($this->aDebugBarData['num_data']['request'] > 0)
		{
			$sListitems .= '<li><a href="#superglobal_request">_REQUEST ('.
				$this->aDebugBarData['num_data']['request'].')</a></li>';

			$sTabContent .= '<h3 id="superglobal_request">_REQUEST</h3>'.
				'<div><pre>'.var_export($_REQUEST,true).'</pre></div>';
		}

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
						<td><pre class="brush: sql; gutter: false; toolbar: false;">'.wordwrap($query[1],60).'</pre></td>
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


		$str =
		'<div id="debugTools">
			<ul>
				<li>Mémoire utilisée par PHP&nbsp;: '.$this->aDebugBarData['memUsage'].'</li>
				<li>Pic mémoire allouée par PHP&nbsp;: '.$this->aDebugBarData['peakUsage'].'</li>
				<li>Temps d\'execution du script&nbsp;: '.$this->aDebugBarData['execTime'].' s</li>
			</ul>
			<ul>
				<li>Lang&nbsp;: '.$this->okt->router->getLanguage().'</li>
				<li>Path&nbsp;: '.$this->okt->router->getPath().'</li>
				<li>Route&nbsp;: '.$this->okt->router->getFindedRouteId().'</li>
			</ul>
			<ul>
				<li>$okt->page->module&nbsp;: '.(!empty($this->okt->page->module) ? $this->okt->page->module : '').'</li>
				<li>$okt->page->action&nbsp;: '.(!empty($this->okt->page->action) ? $this->okt->page->action : '').'</li>
			</ul>
			<ul>
				<li><a href="'.OKT_PUBLIC_URL.'/img/ico/sprites.html" id="sprites_link">Sprites</a></li>
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
