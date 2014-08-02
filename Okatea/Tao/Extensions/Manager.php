<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Extensions;

use Okatea\Tao\Database\Recordset;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Okatea\Tao\Application;

class Manager
{
	/**
	 * Okatea application instance.
	 *
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * The name of the extensions table.
	 *
	 * @var string
	 */
	protected $t_extensions;

	/**
	 * The type of extensions.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The directory path extensions.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * List of all extensions in the file system.
	 *
	 * @var array
	 */
	protected $aAll;

	/**
	 * Extensions collection instance.
	 *
	 * @var Okatea\Tao\Extensions\Collection
	 */
	protected $collection;

	public function __construct(Application $okt, $sType, $sPath)
	{
		$this->okt = $okt;

		$this->db = $okt->db;

		$this->t_extensions = $okt['config']->database_prefix . 'core_extensions';

		$this->type = $sType;

		$this->path = $sPath;
	}

	/**
	 * Returns a list of extensions from the file system.
	 *
	 * @return array
	 */
	public function getFromFileSystem()
	{
		$finder = (new Finder())->files()
			->in($this->path)
			->depth('== 1')
			->name('_define.php');

		$aExstensions = array();

		foreach ($finder as $file)
		{
			$sId = $file->getRelativePath();

			$aInfos = require $file->getRealpath();

			$aExstensions[$sId] = array(
				'id' => $sId,
				'root' => $this->path . '/' . $sId,
				'name' => (! empty($aInfos['name']) ? $aInfos['name'] : $sId),
				'desc' => (! empty($aInfos['desc']) ? $aInfos['desc'] : null),
				'version' => (! empty($aInfos['version']) ? $aInfos['version'] : null),
				'author' => (! empty($aInfos['author']) ? $aInfos['author'] : null),
				'priority' => (! empty($aInfos['priority']) ? (integer) $aInfos['priority'] : 1000),
				'updatable' => (! empty($aInfos['updatable']) ? (boolean) $aInfos['updatable'] : true)
			);
		}

		return $aExstensions;
	}

	/**
	 * Returns a list of all the extensions in the file system.
	 *
	 * @return array
	 */
	public function getAll()
	{
		if (null === $this->aAll)
		{
			$this->aAll = $this->getFromFileSystem();
		}

		return $this->aAll;
	}

	/**
	 * Resets the list of all the extensions in the file system.
	 *
	 * @return void
	 */
	public function resetAll()
	{
		$this->aAll = array();
	}

	/**
	 * Returns a list of extensions registered in the database.
	 *
	 * @param array $aParams
	 * @return object Recordset
	 */
	public function getFromDatabase(array $aParams = array())
	{
		$reqPlus = 'WHERE type=\'' . $this->db->escapeStr($this->type) . '\' ';

		if (! empty($aParams['id']))
		{
			$reqPlus .= 'AND id=\'' . $this->db->escapeStr($aParams['id']) . '\' ';
		}

		if (! empty($aParams['status']))
		{
			$reqPlus .= 'AND status=' . (integer) $aParams['status'] . ' ';
		}

		$strReq = 'SELECT id, name, description, author, version, priority, updatable, status, type ' . 'FROM ' . $this->t_extensions . ' ' . $reqPlus . 'ORDER BY priority ASC, id ASC ';

		if (($rs = $this->db->select($strReq)) === false)
		{
			return new Recordset(array());
		}

		return $rs;
	}

	/**
	 * Returns the list of installed extensions.
	 *
	 * @return array
	 */
	public function getInstalled()
	{
		$rsInstalled = $this->getFromDatabase();

		$aInstalled = array();

		while ($rsInstalled->fetch())
		{
			$aInstalled[$rsInstalled->id] = array(
				'id' => $rsInstalled->id,
				'root' => $this->path . '/' . $rsInstalled->id,
				'name' => $rsInstalled->name,
				'name_l10n' => __($rsInstalled->name),
				'desc' => $rsInstalled->description,
				'desc_l10n' => __($rsInstalled->description),
				'author' => $rsInstalled->author,
				'version' => $rsInstalled->version,
				'priority' => $rsInstalled->priority,
				'status' => $rsInstalled->status,
				'updatable' => $rsInstalled->updatable
			);
		}

		return $aInstalled;
	}

	/**
	 * Add an extension to the database.
	 *
	 * @param string $id
	 * @param string $version
	 * @param string $name
	 * @param string $desc
	 * @param string $author
	 * @param integer $priority
	 * @param integer $status
	 * @return booolean
	 */
	public function addExtension($id, $version, $name = '', $desc = '', $author = '', $priority = 1000, $status = 0)
	{
		$query = 'INSERT INTO ' . $this->t_extensions . ' (' . 'id, name, description, author, ' . 'version, priority, status, type' . ') VALUES (' . '\'' . $this->db->escapeStr($id) . '\', ' . '\'' . $this->db->escapeStr($name) . '\', ' . '\'' . $this->db->escapeStr($desc) . '\', ' . '\'' . $this->db->escapeStr($author) . '\', ' . '\'' . $this->db->escapeStr($version) . '\', ' . (integer) $priority . ', ' . (integer) $status . ', ' . '\'' . $this->db->escapeStr($this->type) . '\' ' . ') ';

		if ($this->db->execute($query) === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * Update an extension into the database.
	 *
	 * @param string $id
	 * @param string $version
	 * @param string $name
	 * @param string $desc
	 * @param string $author
	 * @param integer $priority
	 * @param integer $status
	 * @return boolean
	 */
	public function updateExtension($id, $version, $name = '', $desc = '', $author = '', $priority = 1000, $status = null)
	{
		$query = 'UPDATE ' . $this->t_extensions . ' SET ' . 'name=\'' . $this->db->escapeStr($name) . '\', ' . 'description=\'' . $this->db->escapeStr($desc) . '\', ' . 'author=\'' . $this->db->escapeStr($author) . '\', ' . 'version=\'' . $this->db->escapeStr($version) . '\', ' . 'priority=' . (integer) $priority . ', ' . 'status=' . ($status === null ? 'status' : (integer) $status) . ' ' . 'WHERE id=\'' . $this->db->escapeStr($id) . '\' ';

		if ($this->db->execute($query) === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * Enable an extension.
	 *
	 * @param string $sExtensionId
	 * @return boolean
	 */
	public function enableExtension($sExtensionId)
	{
		$query = 'UPDATE ' . $this->t_extensions . ' SET ' . 'status=1 ' . 'WHERE id=\'' . $this->db->escapeStr($sExtensionId) . '\' ';

		if ($this->db->execute($query) === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * Disable an extension.
	 *
	 * @param string $sExtensionId
	 * @return boolean
	 */
	public function disableExtension($sExtensionId)
	{
		$query = 'UPDATE ' . $this->t_extensions . ' SET ' . 'status=0 ' . 'WHERE id=\'' . $this->db->escapeStr($sExtensionId) . '\' ';

		if ($this->db->execute($query) === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * Delete an extension from the database.
	 *
	 * @param string $sExtensionId
	 * @return boolean
	 */
	public function deleteExtension($sExtensionId)
	{
		$query = 'DELETE FROM ' . $this->t_extensions . ' ' . 'WHERE id=\'' . $this->db->escapeStr($sExtensionId) . '\' ';

		if ($this->db->execute($query) === false)
		{
			return false;
		}

		$this->db->optimize($this->t_extensions);

		return true;
	}

	/**
	 * Install an extension from a zip file.
	 *
	 * @param string $zip_file
	 * @param Collection $extensions
	 */
	public function installPackage($zip_file, $extensions)
	{
		$zip = new \fileUnzip($zip_file);
		$zip->getList(false, '#(^|/)(__MACOSX|\.svn|\.DS_Store|Thumbs\.db)(/|$)#');

		$zip_root_dir = $zip->getRootDir();

		if ($zip_root_dir !== false)
		{
			$target = dirname($zip_file);
			$destination = $target . '/' . $zip_root_dir;
			$define = $zip_root_dir . '/_define.php';
			$has_define = $zip->hasFile($define);
		}
		else
		{
			$target = dirname($zip_file) . '/' . preg_replace('/\.([^.]+)$/', '', basename($zip_file));
			$destination = $target;
			$define = '_define.php';
			$has_define = $zip->hasFile($define);
		}

		if ($zip->isEmpty())
		{
			$zip->close();
			unlink($zip_file);
			throw new \Exception(__('Empty module zip file.'));
		}

		if (! $has_define)
		{
			$zip->close();
			unlink($zip_file);
			throw new \Exception(__('The zip file does not appear to be a valid module.'));
		}

		$ret_code = 1;

		if (is_dir($destination))
		{
			copy($target . '/_define.php', $target . '/_define.php.bak');

			# test for update
			$sandbox = clone $extensions;
			$zip->unzip($define, $target . '/_define.php');

			$sandbox->resetCompleteList();
			$sandbox->requireDefine($target, basename($destination));
			unlink($target . '/_define.php');
			$new_modules = $sandbox->getCompleteList();
			$old_modules = $extensions->getModulesFromFileSystem();

			$extensions->disableModule(basename($destination));
			$extensions->generateCacheList();

			if (! empty($new_modules))
			{
				$tmp = array_keys($new_modules);
				$id = $tmp[0];
				$cur_module = $old_modules[$id];

				if (! empty($cur_module) && $new_modules[$id]['version'] != $cur_module['version'])
				{
					$fs = (new Filesystem())->remove($destination);

					$ret_code = 2;
				}
				else
				{
					$zip->close();
					unlink($zip_file);

					if (file_exists($target . '/_define.php.bak'))
					{
						rename($target . '/_define.php.bak', $target . '/_define.php');
					}

					throw new \Exception(sprintf(__('Unable to upgrade "%s". (same version)'), basename($destination)));
				}
			}
			else
			{
				$zip->close();
				unlink($zip_file);

				if (file_exists($target . '/_define.php.bak'))
				{
					rename($target . '/_define.php.bak', $target . '/_define.php');
				}

				throw new \Exception(sprintf(__('Unable to read new _define.php file')));
			}
		}

		$zip->unzipAll($target);
		$zip->close();
		unlink($zip_file);

		return $ret_code;
	}
}
