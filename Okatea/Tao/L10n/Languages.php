<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\L10n;

use Okatea\Tao\Application;
use Okatea\Tao\Database\Recordset;

/**
 * The Okatea language manager.
 */
class Languages
{
	const CACHE_ID = 'okt_languages';

	/**
	 * Okatea application instance.
	 *
	 * @var Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * List of languages.
	 *
	 * @var array
	 */
	protected $aList;

	/**
	 * Number of languages.
	 *
	 * @var integer
	 */
	protected $iNumberOfLanguages;

	/**
	 * Single language.
	 *
	 * @var boolean
	 */
	protected $bUnique;

	/**
	 * The name of the language table.
	 *
	 * @var string
	 */
	protected $t_languages;

	/**
	 * The cache identifier.
	 *
	 * @var string
	 */
	protected $cache_id;

	/**
	 * Constructor.
	 *
	 * @param Okatea\Tao\Application $okt
	 * @return void
	 */
	public function __construct(Application $okt)
	{
		$this->okt = $okt;

		$this->t_languages = $okt['config']->database_prefix . 'core_languages';

		$this->load();
	}

	/**
	 * Returns a list of active languages​​.
	 *
	 * @return array
	 */
	public function getList()
	{
		return $this->aList;
	}

	/**
	 * Returns the number of active languages​​.
	 *
	 * @return integer
	 */
	public function getNumberOfLanguages()
	{
		return $this->iNumberOfLanguages;
	}

	/**
	 * Specifies whether the system has only one active language.
	 *
	 * @return boolean
	 */
	public function hasUniqueLanguage()
	{
		return $this->bUnique;
	}

	/**
	 * Indicates whether a given language is active.
	 *
	 * @param string $sLanguage
	 * @return boolean
	 */
	public function isActive($sLanguage)
	{
		return array_key_exists($sLanguage, $this->aList);
	}

	/**
	 * Returns the identifier of a language according to its code.
	 *
	 * @param string $sLanguageCode
	 * @return integer
	 */
	public function getIdByCode($sLanguageCode)
	{
		return isset($this->aList[$sLanguageCode]) ? $this->aList[$sLanguageCode]['id'] : false;
	}

	/**
	 * Returns the language code according to its identifier.
	 *
	 * @param string $iLanguageId
	 * @return integer
	 */
	public function getCodeById($iLanguageId)
	{
		foreach ($this->aList as $aLanguage)
		{
			if ($aLanguage['id'] == $iLanguageId) {
				return $aLanguage['code'];
			}
		}
	}

	/**
	 * Returns a list of languages ​​according to given parameters.
	 *
	 * @param array	$aParams
	 * @param boolean $bCountOnly
	 * @return array|integer
	 */
	public function getLanguages(array $aParams = [], $bCountOnly = false)
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder
			->where('true = true');

		if (!empty($aParams['id']))
		{
			$queryBuilder
				->andWhere('l.id = :id')
				->setParameter('id', $aParams['id']);
		}

		if (!empty($aParams['title']))
		{
			$queryBuilder
				->andWhere('l.title = :title')
				->setParameter('title', $aParams['title']);
		}

		if (!empty($aParams['code']))
		{
			$queryBuilder
				->andWhere('l.code = :code')
				->setParameter('code', $aParams['code']);
		}

		if (!empty($aParams['active']))
		{
			$queryBuilder
				->andWhere('l.active = :active')
				->setParameter('active', $aParams['active']);
		}

		if ($bCountOnly)
		{
			$queryBuilder
				->select('COUNT(l.id) AS num_languages')
				->from($this->t_languages, 'l');
		}
		else
		{
			$queryBuilder
				->select('l.id', 'l.title', 'l.code', 'l.img', 'l.active', 'l.ord')
				->from($this->t_languages, 'l');

			if (!empty($aParams['order'])) {
				$queryBuilder->orderBy($aParams['order']);
			}
			else {
				$queryBuilder->orderBy('l.ord', 'ASC');
			}

			if (!empty($aParams['limit']))
			{
				$queryBuilder
					->setFirstResult(0)
					->setMaxResults($aParams['limit']);
			}
		}

		if ($bCountOnly) {
			return (integer) $queryBuilder->execute()->fetchColumn();
		}

		return $queryBuilder->execute()->fetchAll();
	}

	/**
	 * Returns information of a given language.
	 *
	 * @param integer $iLanguageId
	 * @return array
	 */
	public function getLanguage($iLanguageId)
	{
		$aLanguage = $this->okt['db']->fetchAssoc(
			'SELECT * FROM '.$this->t_languages.' WHERE id = ?',
			array($iLanguageId)
		);

		return $aLanguage;
	}

	/**
	 * Indicates whether a given language exists.
	 *
	 * @param integer $iLanguageId
	 * @param boolean
	 */
	public function languageExists($iLanguageId)
	{
		$aLanguage = $this->getLanguage($iLanguageId);

		return $aLanguage ? true : false;
	}

	/**
	 * Adding a language.
	 *
	 * @param array $aData
	 * @return integer
	 */
	public function addLanguage(array $aData = [])
	{
		$iMaxOrd = $this->okt['db']->fetchColumn('SELECT MAX(ord) FROM ' . $this->t_languages);

		$this->okt['db']->insert($this->t_languages, array(
			'title' 	=> $aData['title'],
			'code' 		=> $aData['code'],
			'img' 		=> $aData['img'],
			'active'	=> (integer) $aData['active'],
			'ord' 		=> (integer) ($iMaxOrd + 1)
		));

		$this->afterProcess();

		return $this->okt['db']->lastInsertId();
	}

	/**
	 * Update a language.
	 *
	 * @param array $aData
	 * @return boolean
	 */
	public function updLanguage(array $aData = [])
	{
		$this->okt['db']->update($this->t_languages,
			array(
				'title' 	=> $aData['title'],
				'code' 		=> $aData['code'],
				'img' 		=> $aData['img'],
				'active' 	=> (integer) $aData['active']
			),
			array(
				'id' => (integer) $aData['id']
			)
		);

		$this->afterProcess();

		return true;
	}

	/**
	 * Verifies the data sent in the forms.
	 *
	 * @param array $aData
	 * @return boolean
	 */
	public function checkPostData(array $aData = [])
	{
		if (empty($aData['title'])) {
			$this->okt['flashMessages']->error(__('c_a_config_l10n_error_need_title'));
		}

		if (empty($aData['code'])) {
			$this->okt['flashMessages']->error(__('c_a_config_l10n_error_need_code'));
		}

		return !$this->okt['flashMessages']->hasError();
	}

	/**
	 * Switch the status of a given language.
	 *
	 * @param integer $iLanguageId
	 * @return boolean
	 */
	public function switchLangStatus($iLanguageId)
	{
		if (! $this->languageExists($iLanguageId)) {
			return false;
		}

		$this->okt['db']->update(
			$this->t_languages,
			array('active' => '1-active'),
			array('id' => $iLanguageId)
		);

		$this->afterProcess();

		return true;
	}

	/**
	 * Sets the status of a given language.
	 *
	 * @param integer $iLanguageId
	 * @param integer $iStatus
	 * @return boolean
	 */
	public function setLangStatus($iLanguageId, $iStatus)
	{
		if (! $this->languageExists($iLanguageId)) {
			return false;
		}

		$iStatus = ($iStatus == 1) ? 1 : 0;

		$this->okt['db']->update(
			$this->t_languages,
			array('active' => $iStatus),
			array('id' => $iLanguageId)
		);

		$this->afterProcess();

		return true;
	}

	/**
	 * Updates the position of a given language.
	 *
	 * @param integer $iLanguageId
	 * @param integer $iOrd
	 * @return boolean
	 */
	public function updLanguageOrder($iLanguageId, $iOrd)
	{
		if (! $this->languageExists($iLanguageId)) {
			return false;
		}

		$this->okt['db']->update(
			$this->t_languages,
			array('ord' => $iOrd),
			array('id' => $iLanguageId)
		);

		$this->afterProcess();

		return true;
	}

	/**
	 * Deleting a given language.
	 *
	 * @param integer $iLanguageId
	 * @return boolean
	 */
	public function delLanguage($iLanguageId)
	{
		if (! $this->languageExists($iLanguageId)) {
			return false;
		}
		$this->okt['db']->delete($this->t_languages, array('id' => $iLanguageId));

		$this->afterProcess();

		return true;
	}

	/**
	 * Load the list of active languages.
	 *
	 * @return void
	 */
	protected function load()
	{
		if (!$this->okt['cacheConfig']->contains(self::CACHE_ID)) {
			$this->generateCacheList();
		}

		$this->aList = $this->okt['cacheConfig']->fetch(self::CACHE_ID);

		$this->iNumberOfLanguages = count($this->aList);
		$this->bUnique = (boolean) ($this->iNumberOfLanguages === 1);
	}

	/**
	 * Generates cache list of active languages.
	 *
	 * @return boolean
	 */
	protected function generateCacheList()
	{
		$aLanguagesList = [];

		$aList = $this->getLanguages([
			'active' => 1
		]);

		foreach ($aList as $aLanguage)
		{
			$aLanguagesList[$aLanguage['code']] = [
				'id'        => (integer) $aLanguage['id'],
				'title'     => $aLanguage['title'],
				'code'      => $aLanguage['code'],
				'img'       => $aLanguage['img']
			];
		}

		return $this->okt['cacheConfig']->save(self::CACHE_ID, $aLanguagesList);
	}

	/**
	 * Regenerate cache and touch router ressources.
	 *
	 * @return void
	 */
	protected function afterProcess()
	{
		$this->generateCacheList();

		$this->okt['router']->touchResources();
	}
}
