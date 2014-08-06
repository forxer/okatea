<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Install\Controller;

use Okatea\Install\Controller;
use Okatea\Tao\Database\MySqli;
use Okatea\Tao\Html\Checklister;

class DatabaseConfiguration extends Controller
{
	protected $checklist;

	public function page()
	{
		$aDatabaseParams = [
			'env' => $this->okt['env'],
			'prod' => [
				'host' => '',
				'name' => '',
				'user' => '',
				'password' => '',
				'prefix' => 'okt_'
			],
			'dev' => [
				'host' => 'localhost',
				'name' => 'okatea',
				'user' => 'root',
				'password' => '',
				'prefix' => 'okt_'
			]
		];

		if ($this->okt['request']->request->has('sended'))
		{
			$this->checklist = new Checklister();

			# Données environnement de production
			$aDatabaseParams = [
				'env' => $this->okt['request']->request->get('connect'),
				'prod' => [
					'host' => $this->okt['request']->request->get('prod_host'),
					'name' => $this->okt['request']->request->get('prod_name'),
					'user' => $this->okt['request']->request->get('prod_user'),
					'password' => $this->okt['request']->request->get('prod_password'),
					'prefix' => $this->okt['request']->request->get('prod_prefix')
				],
				'dev' => [
					'host' => $this->okt['request']->request->get('dev_host'),
					'name' => $this->okt['request']->request->get('dev_name'),
					'user' => $this->okt['request']->request->get('dev_user'),
					'password' => $this->okt['request']->request->get('dev_password'),
					'prefix' => $this->okt['request']->request->get('dev_prefix')
				]
			];

			if ($aDatabaseParams['env'] != 'dev' && $aDatabaseParams['env'] != 'prod')
			{
				$aDatabaseParams['env'] == 'dev';
			}

			if ($aDatabaseParams['env'] == 'prod')
			{
				if (empty($aDatabaseParams['prod']['prefix']))
				{
					$this->okt->error->set(__('i_db_conf_db_error_prod_must_prefix'));
				}
				elseif (! preg_match('/^[A-Za-z0-9_]+$/', $aDatabaseParams['prod']['prefix']))
				{
					$this->okt->error->set(__('i_db_conf_db_error_prod_prefix_form'));
				}

				if (empty($aDatabaseParams['prod']['host']))
				{
					$this->okt->error->set(__('i_db_conf_db_error_prod_must_host'));
				}

				if (empty($aDatabaseParams['prod']['name']))
				{
					$this->okt->error->set(__('i_db_conf_db_error_prod_must_name'));
				}

				if (empty($aDatabaseParams['prod']['user']))
				{
					$this->okt->error->set(__('i_db_conf_db_error_prod_must_username'));
				}
			}
			else
			{
				if (empty($aDatabaseParams['dev']['prefix']))
				{
					$this->okt->error->set(__('i_db_conf_db_error_dev_must_prefix'));
				}
				elseif (! preg_match('/^[A-Za-z0-9_]+$/', $aDatabaseParams['prod']['prefix']))
				{
					$this->okt->error->set(__('i_db_conf_db_error_dev_prefix_form'));
				}

				if (empty($aDatabaseParams['dev']['host']))
				{
					$this->okt->error->set(__('i_db_conf_db_error_dev_must_host'));
				}

				if (empty($aDatabaseParams['dev']['name']))
				{
					$this->okt->error->set(__('i_db_conf_db_error_dev_must_name'));
				}

				if (empty($aDatabaseParams['dev']['user']))
				{
					$this->okt->error->set(__('i_db_conf_db_error_dev_must_username'));
				}
			}

			$aParamsToTest = $aDatabaseParams[$aDatabaseParams['env']];

			# Tentative de connexion à la base de données
			if (! $this->okt['flashMessages']->hasError())
			{
				$con_id = mysqli_connect($aParamsToTest['host'], $aParamsToTest['user'], $aParamsToTest['password']);

				if (! $con_id)
				{
					$this->okt->error->set('MySQL: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
				}
				else
				{
					$result = mysqli_query($con_id, 'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \'' . mysqli_real_escape_string($con_id, $aParamsToTest['name']) . '\'');

					if (mysqli_num_rows($result) < 1)
					{
						$this->checklist->addItem(
							'create_database',
							mysqli_query($con_id, 'CREATE DATABASE IF NOT EXISTS `' . $aParamsToTest['name'] . '`'),
							__('i_db_conf_create_db_ok'),
							__('i_db_conf_create_db_ko')
						);
					}

					$db = mysqli_select_db($con_id, $aParamsToTest['name']);

					if (! $db)
					{
						$this->okt->error->set('MySQL: ' . mysqli_errno($con_id) . ' ' . mysqli_error($con_id));
					}
					else
					{
						mysqli_close($con_id);
					}
				}
			}

			# Nouvelle tentative de connexion à la base de données en utilisant la class interne
			if (! $this->okt['flashMessages']->hasError())
			{
				$db = new MySqli($aParamsToTest['user'], $aParamsToTest['password'], $aParamsToTest['host'], $aParamsToTest['name'], $aParamsToTest['prefix']);

				if ($db->hasError())
				{
					$this->okt->error->set('Unable to connect to database', $db->error());
				}
				else
				{
					# Création du fichier des paramètres de connexion
					$sConnectionFile = $this->okt['config_path'] . '/connection.php';
					$config = file_get_contents($this->okt['config_path'] . '/connection.dist.php');

					$config = str_replace([
						'%%DB_PROD_HOST%%',
						'%%DB_PROD_BASE%%',
						'%%DB_PROD_USER%%',
						'%%DB_PROD_PASS%%',
						'%%DB_PROD_PREFIX%%'
					], $aDatabaseParams['prod'], $config);

					$config = str_replace([
						'%%DB_DEV_HOST%%',
						'%%DB_DEV_BASE%%',
						'%%DB_DEV_USER%%',
						'%%DB_DEV_PASS%%',
						'%%DB_DEV_PREFIX%%'
					], $aDatabaseParams['dev'], $config);

					$this->checklist->addItem(
						'connection_file',
						file_put_contents($sConnectionFile, $config),
						__('i_db_conf_connection_file_ok'),
						__('i_db_conf_connection_file_ko')
					);

					# aller, dernière tentative en utilisant le fichier
					if (! file_exists($sConnectionFile))
					{
						$this->okt->error->set('Unable to find database connection file.');
					}
					else
					{
						$env = $aDatabaseParams['env'];
						require $sConnectionFile;

						$db = new MySqli($sDbUser, $sDbPassword, $sDbHost, $sDbName, $sDbPrefix);

						if ($db->hasError())
						{
							$this->okt->error->set('Unable to connect to database', $db->error());
						}
						else
						{
							$this->checklist->addItem(
								'connection_attempt',
								file_put_contents($sConnectionFile, $config),
								__('i_db_conf_conn_ok'),
								__('i_db_conf_conn_ko')
							);
						}
					}
				}
			}
		}

		return $this->render('DatabaseConfiguration', [
			'title' => __('i_db_conf_title'),
			'aDatabaseParams' => $aDatabaseParams,
			'oChecklist' => $this->checklist
		]);
	}
}
