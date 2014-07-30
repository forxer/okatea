<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao;

use Okatea\Tao\Database\Recordset;

/**
 * The Okatea language manager.
 */
class Languages
{
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
	public $list;

	/**
	 * Number of languages.
	 *
	 * @var integer
	 */
	public $num;

	/**
	 * Single language.
	 *
	 * @var boolean
	 */
	public $unique;

	/**
	 * The name of the language table.
	 *
	 * @var string
	 */
	protected $t_languages;

	/**
	 * The cache manager object.
	 *
	 * @var object
	 */
	protected $cache;

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

		$this->cache = $okt->cacheConfig;
		$this->cache_id = 'languages';

		$this->t_languages = $okt->config->database_prefix . 'core_languages';

		$this->load();
	}

	/**
	 * Load the list of active languages.
	 *
	 * @return void
	 */
	public function load()
	{
		if (! $this->cache->contains($this->cache_id)) {
			$this->generateCacheList();
		}

		$this->list = $this->cache->fetch($this->cache_id);

		$this->num = count($this->list);
		$this->unique = (boolean) ($this->num == 1);
	}

	/**
	 * Indicates whether a given language is active.
	 *
	 * @param string $sLanguage
	 * @return boolean
	 */
	public function isActive($sLanguage)
	{
		return array_key_exists($sLanguage, $this->list);
	}

	/**
	 * Returns the identifier of a language according to its code.
	 *
	 * @param string $code
	 * @return integer
	 */
	public function getIdByCode($code)
	{
		return isset($this->list[$code]) ? $this->list[$code]['id'] : false;
	}

	/**
	 * Returns the language code according to its identifier.
	 *
	 * @param string $iLanguageId
	 * @return integer
	 */
	public function getCodeById($iLanguageId)
	{
		foreach ($this->list as $lang)
		{
			if ($lang['id'] == $iLanguageId) {
				return $lang['code'];
			}
		}
	}

	/**
	 * Generates cache list of active languages.
	 *
	 * @return boolean
	 */
	public function generateCacheList()
	{
		$aLanguagesList = [];

		$list = $this->getLanguages([
			'active' => 1
		]);

		foreach ($list as $language)
		{
			$aLanguagesList[$language['code']] = [
				'id'        => (integer) $language['id'],
				'title'     => $language['title'],
				'code'      => $language['code'],
				'img'       => $language['img']
			];
		}

		return $this->cache->save($this->cache_id, $aLanguagesList);
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
			$this->flash->error(__('c_a_config_l10n_error_need_title'));
		}

		if (empty($aData['code'])) {
			$this->flash->error(__('c_a_config_l10n_error_need_code'));
		}

		return !$this->flash->hasError();
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
	 * Regenerate cache and touch router ressources.
	 *
	 * @return void
	 */
	protected function afterProcess()
	{
		$this->generateCacheList();

		$this->okt->router->touchResources();
	}
}
