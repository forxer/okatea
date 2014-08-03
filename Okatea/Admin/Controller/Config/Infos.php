<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Admin\Controller\Config;

use ArrayObject;
use Okatea\Admin\Controller;
use Okatea\Tao\Requirements;

class Infos extends Controller
{
	protected $aPageData;

	protected $aNotes;

	protected $aPhpInfos;

	protected $aOkateaInfos;

	protected $aMysqlInfos;

	public function page()
	{
		if (! $this->okt->checkPerm('infos')) {
			return $this->serve401();
		}

		# locales
		$this->okt['l10n']->loadFile($this->okt['locales_dir'] . '/%s/admin/infos');

		# Données de la page
		$this->aPageData = new ArrayObject();

		$this->notesInit();

		$this->okateaInit();

		$this->mysqlInit();

		$this->phpInit();

		# -- TRIGGER CORE INFOS PAGE : adminInfosInit
		$this->okt['triggers']->callTrigger('adminInfosInit', $this->aPageData);

		if (($action = $this->notesHandleRequest()) !== false) {
			return $action;
		}

		if (($action = $this->okateaHandleRequest()) !== false) {
			return $action;
		}

		if (($action = $this->phpHandleRequest()) !== false) {
			return $action;
		}

		if (($action = $this->mysqlHandleRequest()) !== false) {
			return $action;
		}

		# -- TRIGGER CORE INFOS PAGE : adminInfosHandleRequest
		$this->okt['triggers']->callTrigger('adminInfosHandleRequest', $this->aPageData);

		# Construction des onglets
		$this->aPageData['tabs'] = new ArrayObject();

		# onglet notes
		$this->aPageData['tabs'][10] = array(
			'id' => 'tab-notes',
			'title' => __('c_a_infos_install_notes'),
			'content' => $this->renderView('Config/Infos/Tabs/Notes', array(
				'aPageData' => $this->aPageData,
				'aNotes' => $this->aNotes
			))
		);

		# onglet okatea
		$this->aPageData['tabs'][20] = array(
			'id' => 'tab-okatea',
			'title' => __('c_a_infos_okatea'),
			'content' => $this->renderView('Config/Infos/Tabs/Okatea', array(
				'aPageData' => $this->aPageData,
				'aOkateaInfos' => $this->aOkateaInfos
			))
		);

		# onglet php
		$this->aPageData['tabs'][30] = array(
			'id' => 'tab-php',
			'title' => __('c_a_infos_php'),
			'content' => $this->renderView('Config/Infos/Tabs/Php', array(
				'aPageData' => $this->aPageData,
				'aPhpInfos' => $this->aPhpInfos
			))
		);

		# onglet mysql
		$this->aPageData['tabs'][40] = array(
			'id' => 'tab-mysql',
			'title' => __('c_a_infos_mysql'),
			'content' => $this->renderView('Config/Infos/Tabs/Mysql', array(
				'aPageData' => $this->aPageData,
				'aMysqlInfos' => $this->aMysqlInfos
			))
		);

		# -- TRIGGER CORE INFOS PAGE : adminInfosBuildTabs
		$this->okt['triggers']->callTrigger('adminInfosBuildTabs', $this->aPageData);

		$this->aPageData['tabs']->ksort();

		return $this->render('Config/Infos/Page', array(
			'aPageData' => $this->aPageData
		));
	}

	protected function notesInit()
	{
		$this->aNotes = array(
			'file' => $this->okt['root_dir'] . '/notes.md',
			'has' => false,
			'edit' => false,
			'md' => null,
			'html' => null
		);

		if (file_exists($this->aNotes['file']))
		{
			$this->aNotes['has'] = true;

			$this->aNotes['md'] = file_get_contents($this->aNotes['file']);

			$this->aNotes['edit'] = $this->okt['request']->query->get('edit_notes');

			$this->aNotes['html'] = $this->okt->HTMLfilter(\Parsedown::instance()->parse($this->aNotes['md']));
		}
	}

	protected function okateaInit()
	{
		$this->aOkateaInfos = array(
			'version' => $this->okt->getVersion(),
			'pass_test' => true,
			'warning_empty' => true,
			'requirements' => null
		);

		$oRequirements = new Requirements($this->okt);

		$aResults = $oRequirements->getResultsFromHtmlCheckList();

		$this->aOkateaInfos['pass_test'] = $aResults['bCheckAll'];
		$this->aOkateaInfos['warning_empty'] = $aResults['bCheckWarning'];
		$this->aOkateaInfos['requirements'] = $oRequirements->getRequirements();
	}

	protected function mysqlInit()
	{
		$this->aMysqlInfos = array(
			'table' => $this->okt['request']->query->get('table')
		);

		$rs = $this->okt->db->select('SELECT VERSION() AS db_version');
		$this->aMysqlInfos['db_version'] = $rs->db_version;

		if ($this->aMysqlInfos['table'])
		{
			$this->aMysqlInfos['table_infos'] = $this->okt->db->select('SHOW FULL COLUMNS FROM `' . $this->okt->db->escapeStr($this->aMysqlInfos['table']) . '`');
		}

		$this->aMysqlInfos['db_infos'] = $this->okt->db->select('SHOW TABLE STATUS FROM `' . $this->okt->db->escapeStr($this->okt->db->db_name) . '`');

		$this->aMysqlInfos['num_tables'] = 0;
		$this->aMysqlInfos['num_rows'] = 0;
		$this->aMysqlInfos['db_size'] = 0;
		$this->aMysqlInfos['db_pertes'] = 0;

		while ($this->aMysqlInfos['db_infos']->fetch())
		{
			$this->aMysqlInfos['num_tables'] ++;
			$this->aMysqlInfos['num_rows'] += $this->aMysqlInfos['db_infos']->rows;
			$this->aMysqlInfos['db_size'] += $this->aMysqlInfos['db_infos']->data_length + $this->aMysqlInfos['db_infos']->index_length;
			$this->aMysqlInfos['db_pertes'] += $this->aMysqlInfos['db_infos']->data_free;
		}
	}

	protected function phpInit()
	{
		# PHP infos
		$this->aPhpInfos = array();
		$this->aPhpInfos['version'] = function_exists('phpversion') ? phpversion() : 'n/a';
		$this->aPhpInfos['zend_version'] = function_exists('zend_version') ? zend_version() : 'n/a';
		$this->aPhpInfos['sapi_type'] = function_exists('php_sapi_name') ? php_sapi_name() : 'n/a';
		$this->aPhpInfos['apache_version'] = function_exists('apache_get_version') ? apache_get_version() : 'n/a';
		$this->aPhpInfos['extensions'] = (function_exists('get_loaded_extensions') ? (array) get_loaded_extensions() : array());

		foreach ($this->aPhpInfos['extensions'] as $k => $e)
		{
			$this->aPhpInfos['extensions'][$k] .= ' ' . phpversion($e);
		}
	}

	protected function notesHandleRequest()
	{
		# création du fichier de notes
		if ($this->okt['request']->query->has('create_notes') && ! $this->aNotes['has'])
		{
			file_put_contents($this->aNotes['file'], '');

			return $this->redirect($this->generateUrl('config_infos') . '?edit_notes=1');
		}

		# enregistrement notes
		if ($this->okt['request']->request->has('save_notes'))
		{
			if ($this->aNotes['has'])
			{
				file_put_contents($this->aNotes['file'], $this->okt['request']->request->get('notes_content'));
			}

			return $this->redirect($this->generateUrl('config_infos'));
		}

		return false;
	}

	protected function okateaHandleRequest()
	{
		# affichage changelog Okatea
		$sChangelogFile = $this->okt['okt_dir'] . '/CHANGELOG';
		if ($this->okt['request']->query->has('show_changelog') && file_exists($sChangelogFile))
		{
			echo '<pre class="changelog">' . file_get_contents($sChangelogFile) . '</pre>';
			die();
		}

		return false;
	}

	protected function phpHandleRequest()
	{
		# affichage phpinfo()
		if ($this->okt['request']->query->has('phpinfo'))
		{
			phpinfo();
			exit();
		}

		return false;
	}

	protected function mysqlHandleRequest()
	{
		# optimisation d'une table
		$optimize = $this->okt['request']->query->get('optimize');

		if ($optimize)
		{
			if ($this->okt->db->optimize($optimize) === false) {
				$this->okt['flash']->error($this->okt->db->error());
			}

			$this->okt['flash']->success(__('c_a_infos_mysql_table_optimized'));

			return $this->redirect($this->generateUrl('config_infos'));
		}

		# vidange d'une table
		$truncate = $this->okt['request']->query->get('truncate');

		if ($truncate)
		{
			if ($this->okt->db->execute('TRUNCATE `' . $truncate . '`') === false) {
				$this->okt['flash']->error($this->okt->db->error());
			}

			$this->okt['flash']->success(__('c_a_infos_mysql_table_truncated'));

			return $this->redirect($this->generateUrl('config_infos'));
		}

		# suppression d'une table
		$drop = $this->okt['request']->query->get('drop');

		if ($drop)
		{
			if ($this->okt->db->execute('DROP TABLE `' . $drop . '`') === false)
			{
				$this->okt['flash']->error($this->okt->db->error());
			}

			$this->okt['flash']->success(__('c_a_infos_mysql_table_droped'));

			return $this->redirect($this->generateUrl('config_infos'));
		}

		return false;
	}
}
