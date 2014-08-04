<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Logger;

use Okatea\Admin\Filters\LogAdmin as LogAdminFilters;
use Okatea\Tao\Application;
use Okatea\Tao\Database\Recordset;
use Okatea\Tao\L10n\Date;

/**
 * Administration log management.
 */
class LogAdmin
{
	const DEFAULT_COMPONENT = 'core';
	const TYPE_INFO = 0;
	const TYPE_WARNING = 10;
	const TYPE_CRITICAL = 20;
	const TYPE_ERROR = 30;

	const CODE_UNKNOWN = 0;
	const CODE_LOGIN = 10;
	const CODE_LOGOUT = 11;
	const CODE_INSTALL = 20;
	const CODE_UPDATE = 21;
	const CODE_UNINSTALL = 22;
	const CODE_REINSTALL = 23;
	const CODE_ACTIVATION = 30;
	const CODE_DEACTIVATION = 31;
	const CODE_SWITCH_STATUS = 32;
	const CODE_ADD = 40;
	const CODE_CHANGE = 41;
	const CODE_DELETE = 42;

	/**
	 * Okatea application instance.
	 *
	 * @var Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The log admin table name.
	 *
	 * @var string
	 */
	protected $t_log;

	/**
	 * Log admin filters.
	 *
	 * @var Okatea\Admin\Filters\LogAdmin
	 */
	public $filters = null;

	/**
	 * Constructeur.
	 *
	 * @param Okatea\Tao\Application $okt
	 * @return void
	 */
	public function __construct(Application $okt)
	{
		$this->okt = $okt;

		$this->t_log = $okt['config']->database_prefix . 'core_log_admin';
	}

	/**
	 * Filters initialization.
	 *
	 * @return void
	 */
	public function filtersStart()
	{
		if (null === $this->filters || !($this->filters instanceof logAdminFilters))
		{
			$this->filters = new LogAdminFilters($this->okt, $this);
		}
	}

	/**
	 * Returns a list of admin log ​​according to given parameters.
	 *
	 * @param array $aParams
	 * @param boolean $bCountOnly
	 * @return array|integer
	 */
	public function getLogs(array $aParams = [], $bCountOnly = false)
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder
			->where('true = true');

		if (!empty($aParams['id']))
		{
			$queryBuilder
				->andWhere('id = :id')
				->setParameter('id', $aParams['id']);
		}

		if (!empty($aParams['user_id']))
		{
			$queryBuilder
				->andWhere('user_id = :user_id')
				->setParameter('id', $aParams['user_id']);
		}

		if (!empty($aParams['username']))
		{
			$queryBuilder
				->andWhere('username = :username')
				->setParameter('username', $aParams['username']);
		}

		if (!empty($aParams['component']))
		{
			$queryBuilder
				->andWhere('component = :component')
				->setParameter('component', $aParams['component']);
		}

		if (!empty($aParams['ip']))
		{
			$queryBuilder
				->andWhere('ip = :ip')
				->setParameter('ip', $aParams['ip']);
		}

		if (isset($aParams['type']) && array_key_exists($aParams['type'], self::getTypes()))
		{
			$queryBuilder
				->andWhere('type = :type')
				->setParameter('type', $aParams['type']);
		}

		if (isset($aParams['code']) && array_key_exists($aParams['code'], self::getCodes()))
		{
			$queryBuilder
				->andWhere('code = :type')
				->setParameter('code', $aParams['code']);
		}

		if (!empty($aParams['date_min']) && !empty($aParams['date_max']))
		{
			$queryBuilder
				->andWhere(
					$queryBuilder->expr()->andX(
						$queryBuilder->expr()->gte('date', ':date_min'),
						$queryBuilder->expr()->lte('date', ':date_max')
					)
				)
				->setParameter('date_min', Date::parse($aParams['date_min']), 'datetime')
				->setParameter('date_max', Date::parse($aParams['date_max']), 'datetime');
		}
		elseif (!empty($aParams['date_min']))
		{
			$queryBuilder
				->andWhere(
					$queryBuilder->expr()->gte('date', ':date')
				)
				->setParameter('date', Date::parse($aParams['date_min']), 'datetime');
		}
		elseif (!empty($aParams['date_max']))
		{
			$queryBuilder
				->andWhere(
					$queryBuilder->expr()->lte('date', ':date')
				)
				->setParameter('date', Date::parse($aParams['date_max']), 'datetime');
		}

		if ($bCountOnly)
		{
			$queryBuilder
				->select('COUNT(id) AS num_logs_admin')
				->from($this->t_log);
		}
		else
		{
			$queryBuilder
				->select('id', 'user_id', 'username', 'ip', 'date', 'type', 'component', 'code', 'message')
				->from($this->t_log);

			if (!empty($aParams['order'])) {
				$queryBuilder->orderBy($aParams['order'], $aParams['order_direction']);
			}
			else {
				$queryBuilder->orderBy(LogAdminFilters::DEFAULT_ORDER_BY, LogAdminFilters::DEFAULT_ORDER_DIRECTION);
			}

			if (!isset($aParams['first'])) {
				$aParams['first'] = 0;
			}

			if (!isset($aParams['max'])) {
				$aParams['max'] = LogAdminFilters::DEFAULT_NB_PER_PAGE;
			}

			$queryBuilder
				->setFirstResult($aParams['first'])
				->setMaxResults($aParams['max']);
		}

		if ($bCountOnly) {
			return (integer) $queryBuilder->execute()->fetchColumn();
		}

		return $queryBuilder->execute()->fetchAll();
	}

	/**
	 * Returns information of a given admin log.
	 *
	 * @param integer $iLanguageId
	 * @return array
	 */
	public function getLog($idLog)
	{
		$aLog = $this->okt['db']->fetchAssoc(
			'SELECT * FROM '.$this->t_log.' WHERE id = ?',
			array($idLog)
		);

		return $aLog;
	}

	/**
	 * Indicates whether a given admin log exists.
	 *
	 * @param integer $idLog
	 * @return boolean
	 */
	public function logExist($idLog)
	{
		$aLog = $this->getLog($idLog);

		return $aLog ? true : false;
	}

	/**
	 * Add an admin log.
	 *
	 * @param array $aParams
	 * @return boolean
	 */
	public function add(array $aParams = [])
	{
		if (empty($aParams['user_id'])) {
			$aParams['user_id'] = $this->okt['visitor']->infos['id'];
		}

		if (empty($aParams['username'])) {
			$aParams['username'] = $this->okt['visitor']->infos['username'];
		}

		if (empty($aParams['component'])) {
			$aParams['component'] = self::DEFAULT_COMPONENT;
		}

		if (empty($aParams['ip'])) {
			$aParams['ip'] = $this->okt['request']->getClientIp();
		}

		if (empty($aParams['type'])) {
			$aParams['type'] = self::TYPE_INFO;
		}

		if (empty($aParams['code'])) {
			$aParams['code'] = self::CODE_UNKNOWN;
		}

		if (empty($aParams['message'])) {
			$aParams['message'] = '';
		}

		$aParams['date'] = Date::now()->toMysqlString();

		return $this->okt['db']->insert($this->t_log, $aParams);
	}

	/**
	 * Adding an admin log type info.
	 *
	 * @param array $aParams
	 * @return boolean
	 */
	public function info(array $aParams = [])
	{
		$aParams['type'] = self::TYPE_INFO;

		return $this->add($aParams);
	}

	/**
	 * Adding an admin log type warning.
	 *
	 * @param array $aParams
	 * @return boolean
	 */
	public function warning(array $aParams = [])
	{
		$aParams['type'] = self::TYPE_WARNING;

		return $this->add($aParams);
	}

	/**
	 * Adding an admin log type critical.
	 *
	 * @param array $aParams
	 * @return boolean
	 */
	public function critical(array $aParams = [])
	{
		$aParams['type'] = self::TYPE_CRITICAL;

		return $this->add($aParams);
	}

	/**
	 * Adding an admin log type error.
	 *
	 * @param array $aParams
	 * @return boolean
	 */
	public function error(array $aParams = [])
	{
		$aParams['type'] = self::TYPE_ERROR;

		return $this->add($aParams);
	}

	/**
	 * Delete all logs.
	 *
	 * @return boolean
	 */
	public function deleteLogs($iNnumMonths = null)
	{
		$queryBuilder = $this->okt['db']->createQueryBuilder();

		$queryBuilder->delete($this->t_log);

		$iNnumMonths = intval($iNnumMonths);

		if ($iNnumMonths > 0)
		{
			$queryBuilder
				->where(
					$queryBuilder->expr()->lte('date', ':date')
				)
				->setParameter('date', Date::now()->subMonths($iNnumMonths), 'datetime');
		}

		return $queryBuilder->execute();
	}

	/**
	 * Suppression des logs trop anciens.
	 *
	 * @param integer $iNnumMonths
	 * @return boolean
	 */
	public function deleteLogsDate($iNnumMonths)
	{
		return $this->deleteLogs($iNnumMonths);
	}

	/**
	 * Returns a list of the types of logs.
	 *
	 * @param boolean $bFlip
	 * @return array
	 */
	public static function getTypes($bFlip = false)
	{
		$aTypes = [
			self::TYPE_INFO      => __('c_c_log_admin_type_0'),
			self::TYPE_WARNING   => __('c_c_log_admin_type_10'),
			self::TYPE_CRITICAL  => __('c_c_log_admin_type_20'),
			self::TYPE_ERROR     => __('c_c_log_admin_type_30')
		];

		if ($bFlip) {
			$aTypes = array_flip($aTypes);
		}

		return $aTypes;
	}

	/**
	 * Returns a list of the code of logs.
	 *
	 * @param boolean $bFlip
	 * @return array
	 */
	public static function getCodes($bFlip = false)
	{
		$aCodes = [
			self::CODE_UNKNOWN           => __('c_c_log_admin_code_0'), # autre

			self::CODE_LOGIN             => __('c_c_log_admin_code_10'), # connexion
			self::CODE_LOGOUT            => __('c_c_log_admin_code_11'), # déconnexion

			self::CODE_INSTALL           => __('c_c_log_admin_code_20'), # installation
			self::CODE_UPDATE            => __('c_c_log_admin_code_21'), # mise à jour
			self::CODE_UNINSTALL         => __('c_c_log_admin_code_22'), # désinstallation
			self::CODE_REINSTALL         => __('c_c_log_admin_code_23'), # ré-installation

			self::CODE_ACTIVATION        => __('c_c_log_admin_code_30'), # activation
			self::CODE_DEACTIVATION      => __('c_c_log_admin_code_31'), # désactivation
			self::CODE_SWITCH_STATUS     => __('c_c_log_admin_code_32'), # switch status

			self::CODE_ADD               => __('c_c_log_admin_code_40'), # ajout
			self::CODE_CHANGE            => __('c_c_log_admin_code_41'), # modification
			self::CODE_DELETE            => __('c_c_log_admin_code_42') # suppression
		];

		if ($bFlip) {
			$aCodes = array_flip($aCodes);
		}

		return $aCodes;
	}

	/**
	 * Retourne le HTML associé à un type donné.
	 *
	 * @param integer $iType
	 * @return string
	 */
	public static function getHtmlType($iType)
	{
		static $aLogAdminTypes = null;

		if (null === $aLogAdminTypes) {
			$aLogAdminTypes = self::getTypes();
		}

		$sReturn = '';

		switch ($iType)
		{
			default:
			case self::TYPE_INFO:
				$sReturn .= '<span class="icon information"></span>';
				break;

			case self::TYPE_WARNING:
				$sReturn .= '<span class="icon error"></span>';
				break;

			case self::TYPE_CRITICAL:
			case self::TYPE_ERROR:
				$sReturn .= '<span class="icon exclamation"></span>';
				break;
		}

		return $sReturn . $aLogAdminTypes[$iType];
	}
}
