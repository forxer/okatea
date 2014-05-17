<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Modules\Pages;

use ArrayObject;
use Okatea\Tao\Database\Recordset;
use Okatea\Tao\Html\Escaper;
use Okatea\Tao\Html\Modifiers;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Misc\FileUpload;
use Okatea\Tao\Themes\SimpleReplacements;
use Okatea\Tao\Users\Groups;
use RuntimeException;

class Pages
{
	/**
	 * Okatea application instance.
	 *
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The database manager instance.
	 *
	 * @var object
	 */
	protected $db;

	/**
	 * The errors manager instance.
	 *
	 * @var object
	 */
	protected $error;

	protected $t_pages;

	protected $t_pages_locales;

	protected $t_categories;

	protected $t_categories_locales;

	/**
	 *
	 * @param object $okt
	 *        	Okatea application instance.
	 * @param string $t_pages
	 * @param string $t_pages_locales
	 * @param string $t_categories
	 * @param string $t_categories_locales
	 */
	public function __construct($okt, $t_pages, $t_pages_locales, $t_categories, $t_categories_locales)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;

		$this->t_pages = $t_pages;
		$this->t_pages_locales = $t_pages_locales;
		$this->t_categories = $t_categories;
		$this->t_categories_locales = $t_categories_locales;
	}

	/**
	 * Retourne une liste de pages sous forme de recordset selon des paramètres donnés.
	 *
	 * @param array $aParams
	 *        	Paramètres de requete
	 * @param boolean $bCountOnly
	 *        	Ne renvoi qu'un nombre de pages
	 * @return integer|Okatea\Modules\Pages\PagesRecordset
	 */
	public function getPagesRecordset(array $aParams = array(), $bCountOnly = false)
	{
		$sReqPlus = '';

		if (! empty($aParams['id']))
		{
			$sReqPlus .= ' AND p.id=' . (integer) $aParams['id'] . ' ';
		}

		if (! empty($aParams['category_id']))
		{
			$sReqPlus .= ' AND p.category_id=' . (integer) $aParams['category_id'] . ' ';
		}

		if (! empty($aParams['slug']))
		{
			$sReqPlus .= ' AND pl.slug=\'' . $this->db->escapeStr($aParams['slug']) . '\' ';
		}

		if (isset($aParams['active']))
		{
			if ($aParams['active'] == 0)
			{
				$sReqPlus .= 'AND p.active=0 ';
			}
			elseif ($aParams['active'] == 1)
			{
				$sReqPlus .= 'AND p.active=1 ';
			}
			elseif ($aParams['active'] == 2)
			{
				$sReqPlus .= '';
			}
		}
		else
		{
			$sReqPlus .= 'AND p.active=1 ';
		}

		if (! empty($aParams['search']))
		{
			$aWords = Modifiers::splitWords($aParams['search']);

			if (! empty($aWords))
			{
				foreach ($aWords as $i => $w)
				{
					$aWords[$i] = 'pl.words LIKE \'%' . $this->db->escapeStr($w) . '%\' ';
				}
				$sReqPlus .= ' AND ' . implode(' AND ', $aWords) . ' ';
			}
		}

		if ($bCountOnly)
		{
			$sQuery = 'SELECT COUNT(p.id) AS num_pages ' . $this->getSqlFrom($aParams) . 'WHERE 1 ' . $sReqPlus;
		}
		else
		{
			$sQuery = 'SELECT ' . $this->getSelectFields($aParams) . ' ' . $this->getSqlFrom($aParams) . 'WHERE 1 ' . $sReqPlus;

			$sDirection = 'DESC';
			if (! empty($aParams['order_direction']) && strtoupper($aParams['order_direction']) == 'ASC')
			{
				$sDirection = 'ASC';
			}

			if (! empty($aParams['order']))
			{
				$sQuery .= 'ORDER BY ' . $aParams['order'] . ' ' . $sDirection . ' ';
			}
			else
			{
				$sQuery .= 'ORDER BY p.created_at ' . $sDirection . ' ';
			}

			if (! empty($aParams['limit']))
			{
				$sQuery .= 'LIMIT ' . $aParams['limit'] . ' ';
			}
		}

		if (($rs = $this->db->select($sQuery, 'Okatea\Modules\Pages\PagesRecordset')) === false)
		{
			if ($bCountOnly)
			{
				return 0;
			}
			else
			{
				$rs = new PagesRecordset(array());
				$rs->setCore($this->okt);
				return $rs;
			}
		}

		if ($bCountOnly)
		{
			return (integer) $rs->num_pages;
		}
		else
		{
			$rs->setCore($this->okt);
			return $rs;
		}
	}

	/**
	 * Retourne la chaine des champs pour le SELECT.
	 *
	 * @param array $aParams
	 *        	Paramètres de requete
	 * @return string
	 */
	protected function getSelectFields(array $aParams = array())
	{
		$aFields = array(
			'p.id',
			'p.user_id',
			'p.category_id',
			'p.active',
			'p.created_at',
			'p.updated_at',
			'p.images',
			'p.files',
			'p.tpl',
			'pl.language',
			'pl.title',
			'pl.subtitle',
			'pl.title_tag',
			'pl.title_seo',
			'pl.slug',
			'pl.content',
			'pl.meta_description',
			'pl.meta_keywords',
			'pl.words',
			'rl.title AS category_title',
			'rl.slug AS category_slug',
			'r.items_tpl AS category_items_tpl'
		);

		$oFields = new ArrayObject($aFields);

		# -- TRIGGER MODULE PAGES : getPagesSelectFields
		$this->okt->module('Pages')->triggers->callTrigger('getPagesSelectFields', $oFields);

		return implode(', ', (array) $oFields);
	}

	/**
	 * Retourne la chaine FROM en fonction de paramètres.
	 *
	 * @param array $aParams
	 *        	Paramètres de requete
	 * @return string
	 */
	protected function getSqlFrom(array $aParams = array())
	{
		if (empty($aParams['language']))
		{
			$aFrom = array(
				'FROM ' . $this->t_pages . ' AS p ',
				'LEFT OUTER JOIN ' . $this->t_pages_locales . ' AS pl ON p.id=pl.page_id ',
				'LEFT OUTER JOIN ' . $this->t_categories . ' AS r ON r.id=p.category_id ',
				'LEFT OUTER JOIN ' . $this->t_categories_locales . ' AS rl ON r.id=rl.category_id '
			);
		}
		else
		{
			$aFrom = array(
				'FROM ' . $this->t_pages . ' AS p ',
				'INNER JOIN ' . $this->t_pages_locales . ' AS pl ON p.id=pl.page_id ' . 'AND pl.language=\'' . $this->db->escapeStr($aParams['language']) . '\' ',
				'LEFT OUTER JOIN ' . $this->t_categories . ' AS r ON r.id=p.category_id ',
				'LEFT OUTER JOIN ' . $this->t_categories_locales . ' AS rl ON r.id=rl.category_id ' . 'AND rl.language=\'' . $this->db->escapeStr($aParams['language']) . '\' '
			);
		}

		$oFrom = new ArrayObject($aFrom);

		# -- TRIGGER MODULE PAGES : getPagesSqlFrom
		$this->okt->module('Pages')->triggers->callTrigger('getPagesSqlFrom', $oFrom);

		return implode(' ', (array) $oFrom);
	}

	/**
	 * Retourne une liste de pages sous forme de recordset selon des paramètres donnés
	 * et les prépares en vue d'un affichage.
	 *
	 * @param array $aParams
	 *        	Paramètres de requete
	 * @param integer $iTruncatChar
	 *        	(null) Nombre de caractère avant troncature du contenu
	 * @return object Okatea\Modules\Pages\PagesRecordset
	 */
	public function getPages(array $aParams = array(), $iTruncatChar = null)
	{
		$rs = $this->getPagesRecordset($aParams);

		$this->preparePages($rs, $iTruncatChar);

		return $rs;
	}

	/**
	 * Retourne un compte du nombre de pages selon des paramètres donnés.
	 *
	 * @param array $aParams
	 *        	Paramètres de requete
	 * @return integer
	 */
	public function getPagesCount(array $aParams = array())
	{
		return $this->getPagesRecordset($aParams, true);
	}

	/**
	 * Retourne une page donnée sous forme de recordset.
	 *
	 * @param integer $mPageId
	 *        	Identifiant numérique ou slug de la page.
	 * @param integer $iActive
	 *        	Statut requis de la page
	 * @return object Okatea\Modules\Pages\PagesRecordset
	 */
	public function getPage($mPageId, $iActive = 2)
	{
		$aParams = array(
			'language' => $this->okt->user->language,
			'active' => $iActive
		);

		if (Utilities::isInt($mPageId))
		{
			$aParams['id'] = $mPageId;
		}
		else
		{
			$aParams['slug'] = $mPageId;
		}

		$rs = $this->getPagesRecordset($aParams);

		$this->preparePage($rs);

		return $rs;
	}

	/**
	 * Indique si une page donnée existe.
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function pageExists($iPageId)
	{
		if (empty($iPageId) || $this->getPagesRecordset(array(
			'id' => $iPageId,
			'active' => 2
		))->isEmpty())
		{
			return false;
		}

		return true;
	}

	/**
	 * Retourne les localisations d'une page donnée.
	 *
	 * @param integer $iPageId
	 * @return recordset
	 */
	public function getPageL10n($iPageId)
	{
		$query = 'SELECT * FROM ' . $this->t_pages_locales . ' ' . 'WHERE page_id=' . (integer) $iPageId;

		if (($rs = $this->db->select($query)) === false)
		{
			$rs = new Recordset(array());
			return $rs;
		}

		return $rs;
	}

	/**
	 * Formatage des données d'un Recordset en vue d'un affichage d'une liste.
	 *
	 * @param Okatea\Modules\Pages\PagesRecordset $rs
	 * @param integer $iTruncatChar
	 *        	(null)
	 * @return void
	 */
	public function preparePages(PagesRecordset $rs, $iTruncatChar = null)
	{
		# on utilise une troncature personnalisée à cette préparation
		if (! is_null($iTruncatChar))
		{
			$iNumCharBeforeTruncate = (integer) $iTruncatChar;
		}
		# on utilise la troncature de la configuration
		elseif ($this->okt->module('Pages')->config->public_truncat_char > 0)
		{
			$iNumCharBeforeTruncate = $this->okt->module('Pages')->config->public_truncat_char;
		}
		# on n'utilisent pas de troncature
		else
		{
			$iNumCharBeforeTruncate = 0;
		}

		$iCountLine = 0;
		while ($rs->fetch())
		{
			# odd/even
			$rs->odd_even = ($iCountLine % 2 == 0 ? 'even' : 'odd');
			$iCountLine ++;

			# formatages génériques
			$this->commonPreparation($rs);

			# troncature
			if ($iNumCharBeforeTruncate > 0)
			{
				$rs->content = strip_tags($rs->content);
				$rs->content = Modifiers::truncate($rs->content, $iNumCharBeforeTruncate);
			}
		}
	}

	/**
	 * Formatage des données d'un recordset en vue d'un affichage d'une page.
	 *
	 * @param PagesRecordset $rs
	 * @return void
	 */
	public function preparePage(PagesRecordset $rs)
	{
		# formatages génériques
		$this->commonPreparation($rs);
	}

	/**
	 * Formatages des données d'un recordset communs aux listes et aux éléments.
	 *
	 * @param PagesRecordset $rs
	 * @return void
	 */
	protected function commonPreparation(PagesRecordset $rs)
	{
		# url page
		$rs->url = $rs->getPageUrl();

		# url rubrique
		$rs->category_url = $rs->getCategoryUrl();

		# récupération des images
		$rs->images = $rs->getImagesInfo();

		# récupération des fichiers
		$rs->files = $rs->getFilesInfo();

		# contenu
		if (! $this->okt->module('Pages')->config->enable_rte)
		{
			$rs->content = Modifiers::nlToP($rs->content);
		}

		# perform content replacements
		SimpleReplacements::setStartString('');
		SimpleReplacements::setEndString('');

		$aReplacements = array_merge($this->okt->getCommonContentReplacementsVariables(), $this->okt->getImagesReplacementsVariables($rs->images));

		$rs->content = SimpleReplacements::parse($rs->content, $aReplacements);
	}

	/**
	 * Créer une instance de cursor pour une page et la retourne.
	 *
	 * @param array $aPageData
	 * @return object cursor
	 */
	public function openPageCursor($aPageData = null)
	{
		$oCursor = $this->db->openCursor($this->t_pages);

		if (! empty($aPageData))
		{
			foreach ($aPageData as $k => $v)
			{
				$oCursor->{$k} = $v;
			}
		}

		return $oCursor;
	}

	/**
	 * Ajout/modification des textes internationnalisés de la page.
	 *
	 * @param integer $iPageId
	 * @param array $aPageLocalesData
	 */
	protected function setPageL10n($iPageId, $aPageLocalesData)
	{
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (empty($aPageLocalesData[$aLanguage['code']]['title']))
			{
				continue;
			}

			$oCursor = $this->db->openCursor($this->t_pages_locales);

			$oCursor->page_id = $iPageId;

			$oCursor->language = $aLanguage['code'];

			foreach ($aPageLocalesData[$aLanguage['code']] as $k => $v)
			{
				$oCursor->{$k} = $v;
			}

			$oCursor->content = $this->okt->HTMLfilter($oCursor->content);

			$oCursor->words = implode(' ', array_unique(Modifiers::splitWords($oCursor->title . ' ' . $oCursor->subtitle . ' ' . $oCursor->content)));

			$oCursor->meta_description = strip_tags($oCursor->meta_description);

			$oCursor->meta_keywords = strip_tags($oCursor->meta_keywords);

			if (! $oCursor->insertUpdate())
			{
				throw new RuntimeException('Unable to insert/update page locales into database');
			}

			$this->setPageSlug($iPageId, $aLanguage['code']);
		}
	}

	/**
	 * Création du slug d'une page donnée dans une langue donnée.
	 *
	 * @param integer $iPageId
	 * @param string $sLanguage
	 * @return boolean
	 */
	protected function setPageSlug($iPageId, $sLanguage)
	{
		$rsPage = $this->getPagesRecordset(array(
			'id' => $iPageId,
			'language' => $sLanguage,
			'active' => 2
		));

		if ($rsPage->isEmpty())
		{
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		if (empty($rsPage->slug))
		{
			$sUrl = $rsPage->title;
		}
		else
		{
			$sUrl = $rsPage->slug;
		}

		$sUrl = Modifiers::strToSlug($sUrl, false);

		# Let's check if URL is taken…
		$rsTakenSlugs = $this->db->select('SELECT slug FROM ' . $this->t_pages_locales . ' ' . 'WHERE slug=\'' . $this->db->escapeStr($sUrl) . '\' ' . 'AND page_id <> ' . (integer) $iPageId . ' ' . 'AND language=\'' . $this->db->escapeStr($sLanguage) . '\' ' . 'ORDER BY slug DESC');

		if (! $rsTakenSlugs->isEmpty())
		{
			$rsCurrentSlugs = $this->db->select('SELECT slug FROM ' . $this->t_pages_locales . ' ' . 'WHERE slug LIKE \'' . $this->db->escapeStr($sUrl) . '%\' ' . 'AND page_id <> ' . (integer) $iPageId . ' ' . 'AND language=\'' . $this->db->escapeStr($sLanguage) . '\' ' . 'ORDER BY slug DESC ');

			$a = array();
			while ($rsCurrentSlugs->fetch())
			{
				$a[] = $rsCurrentSlugs->slug;
			}

			$sUrl = Utilities::getIncrementedString($a, $sUrl, '-');
		}

		$sQuery = 'UPDATE ' . $this->t_pages_locales . ' SET ' . 'slug=\'' . $this->db->escapeStr($sUrl) . '\' ' . 'WHERE page_id=' . (integer) $iPageId . ' ' . 'AND language=\'' . $this->db->escapeStr($sLanguage) . '\' ';

		if (! $this->db->execute($sQuery))
		{
			return false;
		}

		return true;
	}

	/**
	 * Ajout d'une page.
	 *
	 * @param cursor $oCursor
	 * @param array $aPageLocalesData
	 * @param array $aPagePermsData
	 * @return integer
	 */
	public function addPage($oCursor, array $aPageLocalesData, array $aPagePermsData = array())
	{
		$sDate = date('Y-m-d H:i:s');
		$oCursor->created_at = $sDate;
		$oCursor->updated_at = $sDate;

		if (! $oCursor->insert())
		{
			throw new RuntimeException('Unable to insert page into database');
		}

		# récupération de l'ID
		$iNewId = $this->db->getLastID();

		# ajout des textes internationnalisés
		$this->setPageL10n($iNewId, $aPageLocalesData);

		# ajout des images
		if ($this->addImages($iNewId) === false)
		{
			throw new RuntimeException('Unable to insert images page');
		}

		# ajout des fichiers
		if ($this->addFiles($iNewId) === false)
		{
			throw new RuntimeException('Unable to insert files page');
		}

		# ajout permissions
		if (! $this->setPagePermissions($iNewId, $aPagePermsData))
		{
			throw new RuntimeException('Unable to set page permissions');
		}

		return $iNewId;
	}

	/**
	 * Mise à jour d'une page.
	 *
	 * @param cursor $oCursor
	 * @param array $aPageLocalesData
	 * @param array $aPagePermsData
	 * @return boolean
	 */
	public function updPage($oCursor, array $aPageLocalesData, array $aPagePermsData = array())
	{
		if (! $this->pageExists($oCursor->id))
		{
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $oCursor->id));
			return false;
		}

		# modification dans la DB
		$oCursor->updated_at = $this->db->now();

		if (! $oCursor->update('WHERE id=' . (integer) $oCursor->id . ' '))
		{
			throw new RuntimeException('Unable to update page into database');
		}

		# modification des images
		if ($this->updImages($oCursor->id) === false)
		{
			throw new RuntimeException('Unable to update images page');
		}

		# modification des fichiers
		if ($this->updFiles($oCursor->id) === false)
		{
			throw new RuntimeException('Unable to update files page');
		}

		# modification permissions
		if (! $this->setPagePermissions($oCursor->id, (! empty($aPagePermsData) ? $aPagePermsData : array())))
		{
			throw new RuntimeException('Unable to set page permissions');
		}

		# modification des textes internationnalisés
		$this->setPageL10n($oCursor->id, $aPageLocalesData);

		return true;
	}

	/**
	 * Vérifie les données envoyées par formulaire.
	 *
	 * @param array $aPageData
	 *        	Le tableau de données de la page.
	 * @return boolean
	 */
	public function checkPostData($aPageData)
	{
		$bHasAtLeastOneTitle = false;
		foreach ($this->okt->languages->list as $aLanguage)
		{
			if (empty($aPageData['locales'][$aLanguage['code']]['title']))
			{
				continue;
			}
			else
			{
				$bHasAtLeastOneTitle = true;
				break;
			}
		}

		if (! $bHasAtLeastOneTitle)
		{
			if ($this->okt->languages->unique)
			{
				$this->error->set(__('m_pages_page_must_enter_title'));
			}
			else
			{
				$this->error->set(__('m_pages_page_must_enter_at_least_one_title'));
			}
		}

		if ($this->okt->module('Pages')->config->enable_group_perms && empty($aPageData['perms']))
		{
			$this->error->set(__('m_pages_page_must_set_perms'));
		}

		# -- TRIGGER MODULE PAGES : checkPostData
		$this->okt->module('Pages')->triggers->callTrigger('checkPostData', $aPageData);

		return $this->error->isEmpty();
	}

	/**
	 * Switch le statut de visibilité d'une page donnée
	 *
	 * @param integer $iPageId
	 * @return boolean
	 */
	public function switchPageStatus($iPageId)
	{
		if (! $this->pageExists($iPageId))
		{
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		$sQuery = 'UPDATE ' . $this->t_pages . ' SET ' . 'updated_at=NOW(), ' . 'active = 1-active ' . 'WHERE id=' . (integer) $iPageId;

		if (! $this->db->execute($sQuery))
		{
			throw new RuntimeException('Unable to update page in database.');
		}

		return true;
	}

	/**
	 * Définit le statut de visibilité d'une page donnée
	 *
	 * @param integer $iPageId
	 * @param integer $status
	 * @return boolean
	 */
	public function setPageStatus($iPageId, $status)
	{
		if (! $this->pageExists($iPageId))
		{
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		$sQuery = 'UPDATE ' . $this->t_pages . ' SET ' . 'updated_at=NOW(), ' . 'active = ' . ($status == 1 ? 1 : 0) . ' ' . 'WHERE id=' . (integer) $iPageId;

		if (! $this->db->execute($sQuery))
		{
			throw new RuntimeException('Unable to update page in database.');
		}

		return true;
	}

	/**
	 * Suppression d'une page.
	 *
	 * @param integer $iPageId
	 * @return boolean
	 */
	public function deletePage($iPageId)
	{
		if (! $this->pageExists($iPageId))
		{
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		if ($this->deleteImages($iPageId) === false)
		{
			throw new RuntimeException('Unable to delete images page.');
		}

		if ($this->deleteFiles($iPageId) === false)
		{
			throw new RuntimeException('Unable to delete files page.');
		}

		$sQuery = 'DELETE FROM ' . $this->t_pages . ' ' . 'WHERE id=' . (integer) $iPageId;

		if (! $this->db->execute($sQuery))
		{
			throw new RuntimeException('Unable to remove page from database.');
		}

		$this->db->optimize($this->t_pages);

		$sQuery = 'DELETE FROM ' . $this->t_pages_locales . ' ' . 'WHERE page_id=' . (integer) $iPageId;

		if (! $this->db->execute($sQuery))
		{
			throw new RuntimeException('Unable to remove page locales from database.');
		}

		$this->db->optimize($this->t_pages_locales);

		$this->deletePagePermissions($iPageId);

		return true;
	}

	/* Gestion des permissions de pages
	----------------------------------------------------------*/

	/**
	 * Retourne la liste des groupes pour les permissions.
	 *
	 * @param
	 *        	$bWithAdmin
	 * @param
	 *        	$bWithAll
	 * @return array
	 */
	public function getUsersGroupsForPerms($bWithAdmin = false, $bWithAll = false)
	{
		$aParams = array(
			'language' => $this->okt->user->language,
			'group_id_not' => array(
				Groups::GUEST,
				Groups::SUPERADMIN
			)
		);

		if (! $this->okt->user->is_admin && ! $bWithAdmin)
		{
			$aParams['group_id_not'][] = Groups::ADMIN;
		}

		$rsGroups = $this->okt->getGroups()->getGroups($aParams);

		$aGroups = array();

		if ($bWithAll)
		{
			$aGroups[] = __('c_c_All');
		}

		while ($rsGroups->fetch())
		{
			$aGroups[$rsGroups->group_id] = Escaper::html($rsGroups->title);
		}

		return $aGroups;
	}

	/**
	 * Retourne les permissions d'une page donnée sous forme de tableau.
	 *
	 * @param integer $iPageId
	 * @return array
	 */
	public function getPagePermissions($iPageId)
	{
		if (! $this->okt->module('Pages')->config->enable_group_perms)
		{
			return array();
		}

		$sQuery = 'SELECT page_id, group_id ' . 'FROM ' . $this->t_permissions . ' ' . 'WHERE page_id=' . (integer) $iPageId . ' ';

		if (($rs = $this->db->select($sQuery)) === false)
		{
			return array();
		}

		$aPerms = array();
		while ($rs->fetch())
		{
			$aPerms[] = $rs->group_id;
		}

		return $aPerms;
	}

	/**
	 * Met à jour les permissions d'une page donnée.
	 *
	 * @param integer $iPageId
	 * @param array $aGroupsIds
	 * @return boolean
	 */
	protected function setPagePermissions($iPageId, $aGroupsIds)
	{
		if (! $this->okt->module('Pages')->config->enable_group_perms || empty($aGroupsIds))
		{
			return $this->setDefaultPagePermissions($iPageId);
		}

		if (! $this->pageExists($iPageId))
		{
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		# si l'utilisateur qui définit les permissions n'est pas un admin
		# alors on force la permission à ce groupe admin
		if (! $this->okt->user->is_admin)
		{
			$aGroupsIds[] = Groups::ADMIN;
		}

		# qu'une seule ligne par groupe pleaz
		$aGroupsIds = array_unique((array) $aGroupsIds);

		# liste des groupes existants réellement dans la base de données
		# (sauf invités et superadmin)
		$rsGroups = $this->okt->getGroups()->getGroups(array(
			'language' => $this->okt->user->language,
			'group_id_not' => array(
				Groups::GUEST,
				Groups::SUPERADMIN
			)
		));

		$aGroups = array();
		while ($rsGroups->fetch())
		{
			$aGroups[] = $rsGroups->group_id;
		}
		unset($rsGroups);

		# suppression de toutes les permissions éventuellement existantes
		$this->deletePagePermissions($iPageId);

		# mise en base de données
		$return = true;
		foreach ($aGroupsIds as $iGroupId)
		{
			if ($iGroupId == 0 || in_array($iGroupId, $aGroups))
			{
				$return = $return && $this->setPagePermission($iPageId, $iGroupId);
			}
		}

		return $return;
	}

	/**
	 * Met les permissions par défaut d'une page donnée.
	 *
	 * @param integer $iPageId
	 * @return boolean
	 */
	protected function setDefaultPagePermissions($iPageId)
	{
		if (! $this->pageExists($iPageId))
		{
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		# suppression de toutes les permissions éventuellement existantes
		$this->deletePagePermissions($iPageId);

		# mise en base de données de la permission "tous" (0)
		return $this->setPagePermission($iPageId, 0);
	}

	/**
	 * Insertion d'une permission donnée pour une page donnée.
	 *
	 * @param
	 *        	$iPageId
	 * @param
	 *        	$iGroupId
	 * @return boolean
	 */
	protected function setPagePermission($iPageId, $iGroupId)
	{
		$sQuery = 'INSERT INTO ' . $this->t_permissions . ' ' . '(page_id, group_id) ' . 'VALUES (' . (integer) $iPageId . ', ' . (integer) $iGroupId . ' ' . ') ';

		if (! $this->db->execute($sQuery))
		{
			throw new RuntimeException('Unable to insert page permissions into database');
		}

		return true;
	}

	/**
	 * Supprime les permissions d'une page donnée.
	 *
	 * @param integer $iPageId
	 * @return boolean
	 */
	public function deletePagePermissions($iPageId)
	{
		$sQuery = 'DELETE FROM ' . $this->t_permissions . ' ' . 'WHERE page_id=' . (integer) $iPageId;

		if (! $this->db->execute($sQuery))
		{
			throw new RuntimeException('Unable to delete page permissions from database');
		}

		$this->db->optimize($this->t_permissions);

		return true;
	}

	/* Gestion des fichiers des pages
						----------------------------------------------------------*/

	/**
	 * Retourne une instance de la classe fileUpload
	 *
	 * @return object
	 */
	protected function getFileUpload()
	{
		return new FileUpload($this->okt, $this->okt->module('Pages')->config->files, $this->upload_dir . '/files', $this->upload_url . '/files');
	}

	/**
	 * Ajout de fichier(s) à une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function addFiles($iPageId)
	{
		if (! $this->okt->module('Pages')->config->files['enable'])
		{
			return null;
		}

		$aFiles = $this->getFileUpload()->addFiles($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		return $this->updPageFiles($iPageId, $aFiles);
	}

	/**
	 * Modification de fichier(s) d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function updFiles($iPageId)
	{
		if (! $this->okt->module('Pages')->config->files['enable'])
		{
			return null;
		}

		$aCurrentFiles = $this->getPageFiles($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		$aFiles = $this->getFileUpload()->updFiles($iPageId, $aCurrentFiles);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		return $this->updPageFiles($iPageId, $aFiles);
	}

	/**
	 * Suppression d'un fichier donné d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @param
	 *        	$file_id
	 * @return boolean
	 */
	public function deleteFile($iPageId, $file_id)
	{
		$aCurrentFiles = $this->getPageFiles($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		$aNewFiles = $this->getFileUpload()->deleteFile($iPageId, $aCurrentFiles, $file_id);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		return $this->updPageFiles($iPageId, $aNewFiles);
	}

	/**
	 * Suppression des fichiers d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return boolean
	 */
	public function deleteFiles($iPageId)
	{
		$aCurrentFiles = $this->getPageFiles($iPageId);

		if (! $this->error->isEmpty())
		{
			return false;
		}

		$this->getFileUpload()->deleteAllFiles($aCurrentFiles);

		return $this->updPageFiles($iPageId);
	}

	/**
	 * Récupère la liste des fichiers d'une page donnée
	 *
	 * @param
	 *        	$iPageId
	 * @return array
	 */
	public function getPageFiles($iPageId)
	{
		if (! $this->pageExists($iPageId))
		{
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		$rsPage = $this->getPagesRecordset(array(
			'id' => $iPageId
		));

		$aFiles = $rsPage->files ? unserialize($rsPage->files) : array();

		return $aFiles;
	}

	/**
	 * Met à jours la liste des fichiers d'une page donnée
	 *
	 * @param integer $iPageId
	 * @param array $aFiles
	 * @return boolean
	 */
	public function updPageFiles($iPageId, $aFiles = array())
	{
		if (! $this->pageExists($iPageId))
		{
			$this->error->set(sprintf(__('m_pages_page_%s_not_exists'), $iPageId));
			return false;
		}

		$aFiles = ! empty($aFiles) ? serialize($aFiles) : NULL;

		$sQuery = 'UPDATE ' . $this->t_pages . ' SET ' . 'files=' . (! is_null($aFiles) ? '\'' . $this->db->escapeStr($aFiles) . '\'' : 'NULL') . ' ' . 'WHERE id=' . (integer) $iPageId;

		if (! $this->db->execute($sQuery))
		{
			return false;
		}

		return true;
	}
}
