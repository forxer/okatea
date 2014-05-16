<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao;

use Okatea\Admin\Filters\LogAdmin as LogAdminFilters;
use Okatea\Tao\Database\Recordset;

/**
 * Le gestionnnaire de log administration.
 */
class LogAdmin
{

	/**
	 * L'objet core.
	 * 
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * L'objet gestionnaire de base de données.
	 * 
	 * @var object
	 */
	protected $db;

	/**
	 * L'objet gestionnaire d'erreurs.
	 * 
	 * @var object
	 */
	protected $error;

	/**
	 * Le nom de la table log admin.
	 * 
	 * @var string
	 */
	protected $t_log;

	public $filters = null;

	/**
	 * Constructeur.
	 *
	 * @param Okatea\Tao\Application $okt        	
	 * @return void
	 */
	public function __construct($okt)
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->error = $okt->error;
		
		$this->t_log = $this->db->prefix . 'core_log_admin';
	}

	/**
	 * Initialisation des filtres.
	 *
	 * @return void
	 */
	public function filtersStart()
	{
		if ($this->filters === null || ! ($this->filters instanceof logAdminFilters))
		{
			$this->filters = new LogAdminFilters($this->okt, $this);
		}
	}

	/**
	 * Retourne, sous forme de recordset, la liste des logs admin.
	 *
	 * @param array $aParams
	 *        	Paramètres de requete
	 * @return Okatea\Tao\Database\Recordset
	 */
	public function getLogs($aParams = array(), $bCountOnly = false)
	{
		$reqPlus = '';
		$reqWhere = '';
		
		if (! empty($aParams['id']))
		{
			$reqPlus .= ' AND id=' . (integer) $aParams['id'] . ' ';
		}
		
		if (! empty($aParams['user_id']))
		{
			$reqPlus .= ' AND user_id=' . (integer) $aParams['user_id'] . ' ';
		}
		
		if (! empty($aParams['username']))
		{
			$reqPlus .= ' AND username=\'' . $this->db->escapeStr($aParams['username']) . '\' ';
		}
		
		if (! empty($aParams['component']))
		{
			$reqPlus .= ' AND component=\'' . $this->db->escapeStr($aParams['component']) . '\' ';
		}
		
		if (! empty($aParams['ip']))
		{
			$reqPlus .= ' AND ip=\'' . $this->db->escapeStr($aParams['ip']) . '\' ';
		}
		
		if (isset($aParams['type']) && array_key_exists($aParams['type'], self::getTypes()))
		{
			$reqPlus .= ' AND type=' . (integer) $aParams['type'] . ' ';
		}
		else
		{
			$reqPlus .= ' ';
		}
		
		if (isset($aParams['code']) && array_key_exists($aParams['code'], self::getCodes()))
		{
			$reqPlus .= ' AND code=' . (integer) $aParams['code'] . ' ';
		}
		
		if (! empty($aParams['date_max']) && ! empty($aParams['date_min']))
		{
			$reqPlus .= ' AND date BETWEEN \'' . date('Y-m-d H:i:s', strtotime($aParams['date_min'])) . '\'' . ' AND \'' . date('Y-m-d H:i:s', strtotime($aParams['date_max'])) . '\' ';
		}
		elseif (! empty($aParams['date_min']))
		{
			$reqPlus .= ' AND date > \'' . date('Y-m-d H:i:s', strtotime($aParams['date_min'])) . '\' ';
		}
		elseif (! empty($aParams['date_max']))
		{
			$reqPlus .= ' AND date < \'' . date('Y-m-d H:i:s', strtotime($aParams['date_max'])) . '\' ';
		}
		
		if ($bCountOnly)
		{
			$query = 'SELECT COUNT(id) AS num_logs_admin ' . 'FROM ' . $this->t_log . ' ' . 'WHERE 1 ' . $reqPlus;
		}
		else
		{
			$query = 'SELECT id, user_id, username, ip, date, type, component, code, message ' . 'FROM ' . $this->t_log . ' ' . 'WHERE 1 ' . $reqPlus;
			
			if (! empty($aParams['order']))
			{
				$query .= 'ORDER BY ' . $aParams['order'] . ' ' . $aParams['order_direction'] . ' ';
			}
			else
			{
				$query .= 'ORDER BY date DESC ';
			}
			
			if (! empty($aParams['limit']))
			{
				$query .= 'LIMIT ' . $aParams['limit'] . ' ';
			}
		}
		
		if (($rs = $this->db->select($query)) === false)
		{
			return new Recordset(array());
		}
		
		if ($bCountOnly)
		{
			return (integer) $rs->num_logs_admin;
		}
		else
		{
			return $rs;
		}
	}

	/**
	 * Ajout d'un log admin.
	 *
	 * @param array $aParams        	
	 * @return boolean
	 */
	public function add($aParams = array())
	{
		if (empty($aParams['user_id']))
		{
			$aParams['user_id'] = $this->okt->user->infos->f('id');
		}
		
		if (empty($aParams['username']))
		{
			$aParams['username'] = $this->okt->user->infos->f('username');
		}
		
		if (empty($aParams['component']))
		{
			$aParams['component'] = 'core';
		}
		
		if (empty($aParams['ip']))
		{
			$aParams['ip'] = $this->okt->request->getClientIp();
		}
		
		if (empty($aParams['type']))
		{
			$aParams['type'] = 0;
		}
		
		if (empty($aParams['code']))
		{
			$aParams['code'] = 0;
		}
		
		if (empty($aParams['message']))
		{
			$aParams['message'] = '';
		}
		
		$query = 'INSERT INTO ' . $this->t_log . ' ( ' . 'user_id, username, ip, date, type, component, code, message ' . ' ) VALUES ( ' . (integer) $aParams['user_id'] . ', ' . '\'' . $this->db->escapeStr($aParams['username']) . '\', ' . '\'' . $this->db->escapeStr($aParams['ip']) . '\', ' . 'NOW(), ' . (integer) $aParams['type'] . ', ' . '\'' . $this->db->escapeStr($aParams['component']) . '\', ' . (integer) $aParams['code'] . ', ' . '\'' . $this->db->escapeStr($aParams['message']) . '\' ' . '); ';
		
		if (! $this->db->execute($query))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Ajout d'un log admin de type info.
	 *
	 * @param array $aParams        	
	 * @return boolean
	 */
	public function info($aParams = array())
	{
		$aParams['type'] = 0;
		return $this->add($aParams);
	}

	/**
	 * Ajout d'un log admin de type warning.
	 *
	 * @param array $aParams        	
	 * @return boolean
	 */
	public function warning($aParams = array())
	{
		$aParams['type'] = 10;
		return $this->add($aParams);
	}

	/**
	 * Ajout d'un log admin de type critical.
	 *
	 * @param array $aParams        	
	 * @return boolean
	 */
	public function critical($aParams = array())
	{
		$aParams['type'] = 20;
		return $this->add($aParams);
	}

	/**
	 * Ajout d'un log admin de type error.
	 *
	 * @param array $aParams        	
	 * @return boolean
	 */
	public function error($aParams = array())
	{
		$aParams['type'] = 30;
		return $this->add($aParams);
	}

	/**
	 * Suppression de tous les logs.
	 *
	 * @return boolean
	 */
	public function deleteLogs($iNnumMonths = null)
	{
		$sSqlQuery = 'DELETE FROM ' . $this->t_log;
		
		if ($iNnumMonths > 0)
		{
			$sSqlQuery .= ' WHERE date < \'' . date('Y-m-d H:i:s', strtotime('-' . $iNnumMonths . ' months')) . '\' ';
		}
		
		if (! $this->db->execute($sSqlQuery))
		{
			return false;
		}
		
		$this->db->optimize($this->t_log);
		
		return true;
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
	 * Teste l'existance d'un log.
	 *
	 * @param integer $idLog        	
	 * @return boolean
	 */
	public function logExist($idLog)
	{
		if (empty($idLog) || $this->getLogs($idLog)->isEmpty())
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Retourne la liste des types de logs.
	 *
	 * @param boolean $bFlip        	
	 * @return array
	 */
	public static function getTypes($bFlip = false)
	{
		$aTypes = array(
			0 => __('c_c_log_admin_type_0'), # info
			10 => __('c_c_log_admin_type_10'), # warning
			20 => __('c_c_log_admin_type_20'), # critique
			30 => __('c_c_log_admin_type_30') # error
		);
		
		if ($bFlip)
		{
			$aTypes = array_flip($aTypes);
		}
		
		return $aTypes;
	}

	/**
	 * Retourne la liste des codes de logs.
	 *
	 * @param boolean $bFlip        	
	 * @return array
	 */
	public static function getCodes($bFlip = false)
	{
		$aCodes = array(
			0 => __('c_c_log_admin_code_0'), # autre
			

			10 => __('c_c_log_admin_code_10'), # connexion
			11 => __('c_c_log_admin_code_11'), # déconnexion
			

			20 => __('c_c_log_admin_code_20'), # installation
			21 => __('c_c_log_admin_code_21'), # mise à jour
			22 => __('c_c_log_admin_code_22'), # désinstallation
			23 => __('c_c_log_admin_code_23'), # ré-installation
			

			30 => __('c_c_log_admin_code_30'), # activation
			31 => __('c_c_log_admin_code_31'), # désactivation
			32 => __('c_c_log_admin_code_32'), # switch status
			

			40 => __('c_c_log_admin_code_40'), # ajout
			41 => __('c_c_log_admin_code_41'), # modification
			42 => __('c_c_log_admin_code_42') # suppression
		);
		
		if ($bFlip)
		{
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
		
		if (is_null($aLogAdminTypes))
		{
			$aLogAdminTypes = self::getTypes();
		}
		
		$sReturn = '';
		
		switch ($iType)
		{
			default:
			case 0:
				$sReturn .= '<span class="icon information"></span>';
				break;
			
			case 10:
				$sReturn .= '<span class="icon error"></span>';
				break;
			
			case 20:
			case 30:
				$sReturn .= '<span class="icon exclamation"></span>';
				break;
		}
		
		return $sReturn . $aLogAdminTypes[$iType];
	}
}
