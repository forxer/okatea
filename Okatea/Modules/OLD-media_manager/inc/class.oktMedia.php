<?php
/**
 * @ingroup okt_module_media_manager
 * @brief Media manager.
 *
 * Fork of dcMedia Dotclear media manage
 *
 */
use Okatea\Tao\Misc\ImageTools;
use Okatea\Tao\Users\Users;

class oktMedia extends filemanager
{

	protected $core; // oktCore instance

	protected $db; // database connection

	protected $t_media; //Media table name (string)

	protected $t_users; // Users table name (string)

	protected $type; // Media type filter (string)

	protected $file_sort = 'name-asc';

	protected $file_handler = array(); // Array of callbacks

	public $thumb_tp = '%s/.%s_%s.jpg'; // Thumbnail file pattern (string)

	
	/**
	 * Tubmnail sizes:
	 * - m: medium image
	 * - s: small image
	 * - t: thumbnail image
	 * - sq: square image
	 *
	 * @var array
	 */
	public $thumb_sizes = array(
		'm' => array(
			448,
			'ratio',
			'medium'
		),
		's' => array(
			240,
			'ratio',
			'small'
		),
		't' => array(
			100,
			'ratio',
			'thumbnail'
		),
		'sq' => array(
			48,
			'crop',
			'square'
		)
	);

	public $icon_img = 'images/media/%s.png'; // Icon file pattern (string)

	
	/**
	 * Object constructor.
	 *
	 * @param object $core
	 *        	oktCore instance
	 * @param string $type
	 *        	Media type filter
	 * @return void
	 */
	public function __construct($okt, $type = '')
	{
		$this->okt = $okt;
		$this->db = $okt->db;
		$this->config = $okt->media_manager->config;
		
		$this->t_media = $this->db->prefix . 'mod_media';
		$this->t_users = $this->db->prefix . 'core_users';
		
		$this->icon_img = $okt->options->public_url . '/img/media/%s.png';
		
		$root = $this->okt->options->get('upload_dir') . '/media_manager';
		
		if (! is_dir($root))
		{
			files::makeDir($root, true);
		}
		
		if (preg_match('#^http(s)?://#', $okt->options->upload_url . '/media_manager'))
		{
			$root_url = rawurldecode($okt->options->upload_url . '/media_manager');
		}
		else
		{
			$root_url = rawurldecode($this->okt->request->getSchemeAndHttpHost() . path::clean($okt->options->upload_url . '/media_manager'));
		}
		
		if (! is_dir($root))
		{
			throw new Exception(sprintf(__('Directory %s does not exist.'), $root));
		}
		
		$this->type = $type;
		
		parent::__construct($root, $root_url);
		$this->chdir('');
		
		$this->path = $okt->options->upload_url . '/media_manager/';
		
		//		$this->addExclusion(DC_RC_PATH);
		//		$this->addExclusion(__DIR__.'/../');
		

		$this->exclude_pattern = $this->config->media_exclusion;
		
		# Event handlers
		$this->addFileHandler('image/jpeg', 'create', array(
			$this,
			'imageThumbCreate'
		));
		$this->addFileHandler('image/png', 'create', array(
			$this,
			'imageThumbCreate'
		));
		$this->addFileHandler('image/gif', 'create', array(
			$this,
			'imageThumbCreate'
		));
		
		$this->addFileHandler('image/png', 'update', array(
			$this,
			'imageThumbUpdate'
		));
		$this->addFileHandler('image/jpeg', 'update', array(
			$this,
			'imageThumbUpdate'
		));
		$this->addFileHandler('image/gif', 'update', array(
			$this,
			'imageThumbUpdate'
		));
		
		$this->addFileHandler('image/png', 'remove', array(
			$this,
			'imageThumbRemove'
		));
		$this->addFileHandler('image/jpeg', 'remove', array(
			$this,
			'imageThumbRemove'
		));
		$this->addFileHandler('image/gif', 'remove', array(
			$this,
			'imageThumbRemove'
		));
		
		$this->addFileHandler('image/jpeg', 'create', array(
			$this,
			'imageMetaCreate'
		));
		
		# Thumbnails sizes
		$this->thumb_sizes['m'][0] = abs($this->config->media_img_m_size);
		$this->thumb_sizes['s'][0] = abs($this->config->media_img_s_size);
		$this->thumb_sizes['t'][0] = abs($this->config->media_img_t_size);
	}

	/**
	 * Changes working directory.
	 *
	 * @param
	 *        	dir		<b>string</b>		Directory name.
	 */
	public function chdir($dir)
	{
		parent::chdir($dir);
		$this->relpwd = preg_replace('/^' . preg_quote($this->root, '/') . '\/?/', '', $this->pwd);
	}

	/**
	 * Adds a new file handler for a given media type and event.
	 *
	 * Available events are:
	 * - create: file creation
	 * - update: file update
	 * - remove: file deletion
	 *
	 * @param
	 *        	type		<b>string</b>		Media type
	 * @param
	 *        	event	<b>string</b>		Event
	 * @param
	 *        	function	<b>callback</b>
	 */
	public function addFileHandler($type, $event, $function)
	{
		if (is_callable($function))
		{
			$this->file_handler[$type][$event][] = $function;
		}
	}

	protected function callFileHandler($type, $event)
	{
		if (! empty($this->file_handler[$type][$event]))
		{
			$args = func_get_args();
			array_shift($args);
			array_shift($args);
			
			foreach ($this->file_handler[$type][$event] as $f)
			{
				call_user_func_array($f, $args);
			}
		}
	}

	/**
	 * Returns HTML breadCrumb for media manager navigation.
	 *
	 * @param
	 *        	href		<b>string</b>		URL pattern
	 * @return <b>string</b> HTML code
	 */
	public function breadCrumb($href)
	{
		$res = '';
		if ($this->relpwd && $this->relpwd != '.')
		{
			$pwd = '';
			foreach (explode('/', $this->relpwd) as $v)
			{
				$pwd .= rawurlencode($v) . '/';
				$res .= '<a href="' . sprintf($href, $pwd) . '">' . $v . '</a> / ';
			}
		}
		return $res;
	}

	protected function fileRecord($rs)
	{
		if ($rs->isEmpty())
		{
			return null;
		}
		
		if (! $this->isFileExclude($this->root . '/' . $rs->media_file) && is_file($this->root . '/' . $rs->media_file))
		{
			$f = new fileItem($this->root . '/' . $rs->media_file, $this->root, $this->root_url);
			
			if ($this->type && $f->type_prefix != $this->type)
			{
				return null;
			}
			
			$meta = @simplexml_load_string($rs->media_meta);
			
			$f->editable = true;
			$f->media_id = $rs->media_id;
			$f->media_title = $rs->media_title;
			$f->media_meta = $meta instanceof SimpleXMLElement ? $meta : simplexml_load_string('<meta></meta>');
			$f->media_user_id = $rs->user_id;
			$f->media_user = Users::getUserDisplayName($rs->username, $rs->lastname, $rs->firstname, $rs->displayname);
			$f->media_priv = (boolean) $rs->media_private;
			$f->media_dt = strtotime($rs->media_dt);
			$f->media_dtstr = dt::str('%Y-%m-%d %H:%M', $f->media_dt);
			
			$f->media_image = false;
			
			if (! $this->okt->checkPerm('media_admin') && $this->okt->user->id != $f->media_user_id)
			{
				$f->del = false;
				$f->editable = false;
			}
			
			$type_prefix = explode('/', $f->type);
			$type_prefix = $type_prefix[0];
			
			switch ($type_prefix)
			{
				case 'image':
					$f->media_image = true;
					$f->media_icon = 'image';
					break;
				case 'audio':
					$f->media_icon = 'audio';
					break;
				case 'text':
					$f->media_icon = 'text';
					break;
				case 'video':
					$f->media_icon = 'video';
					break;
				default:
					$f->media_icon = 'blank';
			}
			switch ($f->type)
			{
				case 'application/msword':
				case 'application/vnd.oasis.opendocument.text':
				case 'application/vnd.sun.xml.writer':
				case 'application/pdf':
				case 'application/postscript':
					$f->media_icon = 'document';
					break;
				case 'application/msexcel':
				case 'application/vnd.oasis.opendocument.spreadsheet':
				case 'application/vnd.sun.xml.calc':
					$f->media_icon = 'spreadsheet';
					break;
				case 'application/mspowerpoint':
				case 'application/vnd.oasis.opendocument.presentation':
				case 'application/vnd.sun.xml.impress':
					$f->media_icon = 'presentation';
					break;
				case 'application/x-debian-package':
				case 'application/x-gzip':
				case 'application/x-java-archive':
				case 'application/rar':
				case 'application/x-redhat-package-manager':
				case 'application/x-tar':
				case 'application/x-gtar':
				case 'application/zip':
					$f->media_icon = 'package';
					break;
				case 'application/octet-stream':
					$f->media_icon = 'executable';
					break;
				case 'application/x-shockwave-flash':
					$f->media_icon = 'video';
					break;
				case 'application/ogg':
					$f->media_icon = 'audio';
					break;
				case 'text/html':
					$f->media_icon = 'html';
					break;
			}
			
			$f->media_type = $f->media_icon;
			$f->media_icon = sprintf($this->icon_img, $f->media_icon);
			
			# Thumbnails
			$f->media_thumb = array();
			$p = path::info($f->relname);
			$thumb = sprintf($this->thumb_tp, $this->root . '/' . $p['dirname'], $p['base'], '%s');
			$thumb_url = sprintf($this->thumb_tp, $this->root_url . $p['dirname'], $p['base'], '%s');
			
			# Cleaner URLs
			$thumb_url = preg_replace('#\./#', '/', $thumb_url);
			$thumb_url = preg_replace('#(?<!:)/+#', '/', $thumb_url);
			
			foreach ($this->thumb_sizes as $suffix => $s)
			{
				if (file_exists(sprintf($thumb, $suffix)))
				{
					$f->media_thumb[$suffix] = sprintf($thumb_url, $suffix);
				}
			}
			
			if (isset($f->media_thumb['sq']) && $f->media_type == 'image')
			{
				$f->media_icon = $f->media_thumb['sq'];
			}
			
			return $f;
		}
		
		return null;
	}

	public function setFileSort($type = 'name')
	{
		if (in_array($type, array(
			'name-asc',
			'name-desc',
			'date-asc',
			'date-desc'
		)))
		{
			$this->file_sort = $type;
		}
	}

	protected function sortFileHandler($a, $b)
	{
		switch ($this->file_sort)
		{
			case 'date-asc':
				if ($a->media_dt == $b->media_dt)
				{
					return 0;
				}
				return ($a->media_dt < $b->media_dt) ? - 1 : 1;
			case 'date-desc':
				if ($a->media_dt == $b->media_dt)
				{
					return 0;
				}
				return ($a->media_dt > $b->media_dt) ? - 1 : 1;
			case 'name-desc':
				return strcasecmp($b->basename, $a->basename);
			case 'name-asc':
			default:
				return strcasecmp($a->basename, $b->basename);
		}
	}

	/**
	 * Gets current working directory content.
	 *
	 * @param
	 *        	type		<b>string</b>		Media type filter
	 */
	public function getDir($type = null)
	{
		if ($type)
		{
			$this->type = $type;
		}
		
		$media_dir = $this->relpwd ? $this->relpwd : '.';
		
		$strReq = 'SELECT m.media_file, m.media_id, m.media_path, m.media_title, m.media_meta, m.media_dt, ' . 'm.media_creadt, m.media_upddt, m.media_private, m.user_id, ' . 'u.username, u.lastname, u.firstname ' . 'FROM ' . $this->t_media . ' AS m ' . 'LEFT JOIN ' . $this->t_users . ' AS u ON u.id=m.user_id ' . "WHERE media_path = '" . $this->path . "' " . "AND media_dir = '" . $this->db->escapeStr($media_dir) . "' ";
		
		if (! $this->okt->checkPerm('media_admin'))
		{
			$strReq .= 'AND (m.media_private <> 1 ';
			
			if ($this->okt->user->id != 1)
			{
				$strReq .= 'OR m.user_id = ' . (integer) $this->okt->user->id;
			}
			$strReq .= ') ';
		}
		
		$rs = $this->db->select($strReq);
		
		parent::getDir();
		
		$f_res = array();
		$p_dir = $this->dir;
		
		# If type is set, remove items from p_dir
		if ($this->type)
		{
			foreach ($p_dir['files'] as $k => $f)
			{
				if ($f->type_prefix != $this->type)
				{
					unset($p_dir['files'][$k]);
				}
			}
		}
		
		$f_reg = array();
		
		while ($rs->fetch())
		{
			# File in subdirectory, forget about it!
			if (dirname($rs->media_file) != '.' && dirname($rs->media_file) != $this->relpwd)
			{
				continue;
			}
			
			if ($this->inFiles($rs->media_file))
			{
				$f = $this->fileRecord($rs);
				if ($f !== null)
				{
					$f_res[] = $this->fileRecord($rs);
					$f_reg[$rs->media_file] = 1;
				}
			}
			elseif (! empty($p_dir['files']) && $this->relpwd == '')
			{
				# Physica file does not exist remove it from DB
				# Because we don't want to erase everything on
				# dotclear upgrade, do it only if there are files
				# in directory and directory is root
				$this->db->execute('DELETE FROM ' . $this->t_media . ' ' . "WHERE media_path = '" . $this->db->escapeStr($this->path) . "' " . "AND media_file = '" . $this->db->escapeStr($rs->media_file) . "' ");
				$this->callFileHandler(files::getMimeType($rs->media_file), 'remove', $this->pwd . '/' . $rs->media_file);
			}
		}
		
		$this->dir['files'] = $f_res;
		foreach ($this->dir['dirs'] as $k => $v)
		{
			$v->media_icon = sprintf($this->icon_img, 'folder');
		}
		
		# Check files that don't exist in database and create them
		if ($this->okt->checkPerm('media') || $this->okt->checkPerm('media_admin'))
		{
			foreach ($p_dir['files'] as $f)
			{
				if (! isset($f_reg[$f->relname]))
				{
					if (($id = $this->createFile($f->basename)) !== false)
					{
						$this->dir['files'][] = $this->getFile($id);
					}
				}
			}
		}
		usort($this->dir['files'], array(
			$this,
			'sortFileHandler'
		));
	}

	/**
	 * Gets file by its id.
	 * Returns a filteItem object.
	 *
	 * @param
	 *        	id		<b>integer</b>		File ID
	 * @return <b>fileItem</b>
	 */
	public function getFile($id)
	{
		$strReq = 'SELECT m.media_id, m.media_path, m.media_title, ' . 'm.media_file, m.media_meta, m.media_dt, m.media_creadt, ' . 'm.media_upddt, m.media_private, m.user_id, ' . 'u.username, u.lastname, u.firstname ' . 'FROM ' . $this->t_media . ' AS m ' . 'LEFT JOIN ' . $this->t_users . ' AS u ON u.id=m.user_id ' . "WHERE m.media_path = '" . $this->path . "' " . 'AND m.media_id = ' . (integer) $id . ' ';
		
		if (! $this->okt->checkPerm('media_admin'))
		{
			$strReq .= 'AND (m.media_private <> 1 ';
			
			if ($this->okt->user->id != 1)
			{
				$strReq .= 'OR m.user_id = ' . (integer) $this->okt->user->id . ' ';
			}
			$strReq .= ') ';
		}
		
		$rs = $this->db->select($strReq);
		return $this->fileRecord($rs);
	}

	/**
	 * Returns media items attached to a blog post.
	 * Result is an array containing
	 * fileItems objects.
	 *
	 * @param
	 *        	post_id	<b>integer</b>		Post ID
	 * @param
	 *        	media_id	<b>integer</b>		Optionnal media ID
	 * @return <b>array</b> Array of fileItems
	 */
	public function getPostMedia($post_id, $media_id = null)
	{
		$post_id = (integer) $post_id;
		
		$strReq = 'SELECT media_file, M.media_id, media_path, media_title, media_meta, media_dt, ' . 'media_creadt, media_upddt, media_private, user_id ' . 'FROM ' . $this->t_media . ' M ' . 'INNER JOIN ' . $this->t_media_ref . ' PM ON (M.media_id = PM.media_id) ' . "WHERE media_path = '" . $this->path . "' " . 'AND post_id = ' . $post_id . ' ';
		
		if ($media_id)
		{
			$strReq .= 'AND M.media_id = ' . (integer) $media_id . ' ';
		}
		
		$rs = $this->db->select($strReq);
		
		$res = array();
		
		while ($rs->fetch())
		{
			$f = $this->fileRecord($rs);
			if ($f !== null)
			{
				$res[] = $f;
			}
		}
		
		return $res;
	}

	/**
	 * Attaches a media to a post.
	 *
	 * @param
	 *        	post_id	<b>integer</b>		Post ID
	 * @param
	 *        	media_id	<b>integer</b>		Optionnal media ID
	 */
	public function addPostMedia($post_id, $media_id)
	{
		$post_id = (integer) $post_id;
		$media_id = (integer) $media_id;
		
		$f = $this->getPostMedia($post_id, $media_id);
		
		if (! empty($f))
		{
			return;
		}
		
		$cur = $this->db->openCursor($this->t_media_ref);
		$cur->post_id = $post_id;
		$cur->media_id = $media_id;
		
		$cur->insert();
	}

	/**
	 * Detaches a media from a post.
	 *
	 * @param
	 *        	post_id	<b>integer</b>		Post ID
	 * @param
	 *        	media_id	<b>integer</b>		Optionnal media ID
	 */
	public function removePostMedia($post_id, $media_id)
	{
		$post_id = (integer) $post_id;
		$media_id = (integer) $media_id;
		
		$strReq = 'DELETE FROM ' . $this->t_media_ref . ' ' . 'WHERE post_id = ' . $post_id . ' ' . 'AND media_id = ' . $media_id . ' ';
		
		$this->db->execute($strReq);
	}

	/**
	 * Rebuilds database items collection.
	 * Optional <var>$pwd</var> parameter is
	 * the path where to start rebuild.
	 *
	 * @param
	 *        	pwd		<b>string</b>		Directory to rebuild
	 */
	public function rebuild($pwd = '')
	{
		if (! $this->okt->user->is_superadmin)
		{
			throw new Exception(__('You are not a super administrator.'));
		}
		
		$this->chdir($pwd);
		parent::getDir();
		
		$dir = $this->dir;
		
		foreach ($dir['dirs'] as $d)
		{
			if (! $d->parent)
			{
				$this->rebuild($d->relname, false);
			}
		}
		
		foreach ($dir['files'] as $f)
		{
			$this->chdir(dirname($f->relname));
			$this->createFile($f->basename);
		}
		
		$this->rebuildDB($pwd);
	}

	protected function rebuildDB($pwd)
	{
		$media_dir = $pwd ? $pwd : '.';
		
		$strReq = 'SELECT media_file, media_id ' . 'FROM ' . $this->t_media . ' ' . "WHERE media_path = '" . $this->path . "' " . "AND media_dir = '" . $this->db->escapeStr($media_dir) . "' ";
		
		$rs = $this->db->select($strReq);
		
		$delReq = 'DELETE FROM ' . $this->t_media . ' ' . 'WHERE media_id IN (%s) ';
		
		$del_ids = array();
		
		while ($rs->fetch())
		{
			if (! is_file($this->root . '/' . $rs->media_file))
			{
				$del_ids[] = (integer) $rs->media_id;
			}
		}
		
		if (! empty($del_ids))
		{
			$this->db->execute(sprintf($delReq, implode(',', $del_ids)));
		}
	}

	public function makeDir($d)
	{
		$d = files::tidyFileName($d);
		parent::makeDir($d);
	}

	/**
	 * Creates or updates a file in database.
	 * Returns new media ID or false if
	 * file does not exist.
	 *
	 * @param
	 *        	name		<b>string</b>		File name (relative to working directory)
	 * @param
	 *        	title	<b>string</b>		File title
	 * @param
	 *        	private	<b>boolean</b>		File is private
	 * @param
	 *        	dt		<b>string</b>		File date
	 * @return <b>integer</b> New media ID
	 */
	public function createFile($name, $title = null, $private = false, $dt = null)
	{
		if (! $this->okt->checkPerm('media') && ! $this->okt->checkPerm('media_admin'))
		{
			throw new Exception(__('Permission denied.'));
		}
		
		$file = $this->pwd . '/' . $name;
		if (! file_exists($file))
		{
			return false;
		}
		
		$media_file = $this->relpwd ? path::clean($this->relpwd . '/' . $name) : path::clean($name);
		$media_type = files::getMimeType($name);
		
		$cur = $this->db->openCursor($this->t_media);
		
		$strReq = 'SELECT media_id ' . 'FROM ' . $this->t_media . ' ' . "WHERE media_path = '" . $this->db->escapeStr($this->path) . "' " . "AND media_file = '" . $this->db->escapeStr($media_file) . "' ";
		
		$rs = $this->db->select($strReq);
		
		if ($rs->isEmpty())
		{
			try
			{
				$cur->user_id = (string) $this->okt->user->id;
				$cur->media_path = (string) $this->path;
				$cur->media_file = (string) $media_file;
				$cur->media_dir = (string) dirname($media_file);
				$cur->media_creadt = array(
					'NOW()'
				);
				$cur->media_upddt = array(
					'NOW()'
				);
				
				$cur->media_title = ! $title ? (string) $name : (string) $title;
				$cur->media_private = (integer) (boolean) $private;
				
				if ($dt)
				{
					$cur->media_dt = (string) $dt;
				}
				else
				{
					$cur->media_dt = strftime('%Y-%m-%d %H:%M:%S', filemtime($file));
				}
				
				try
				{
					$cur->insert();
					$media_id = $this->db->getLastID();
				}
				catch (Exception $e)
				{
					@unlink($name);
					throw $e;
				}
			}
			catch (Exception $e)
			{
				throw $e;
			}
		}
		else
		{
			$media_id = (integer) $rs->media_id;
			
			$cur->media_upddt = array(
				'NOW()'
			);
			
			$cur->update('WHERE media_id = ' . $media_id);
		}
		
		$this->callFileHandler($media_type, 'create', $cur, $name, $media_id);
		
		return $media_id;
	}

	/**
	 * Updates a file in database.
	 *
	 * @param
	 *        	file		<b>fileItem</b>	Current fileItem object
	 * @param
	 *        	newFile	<b>fileItem</b>	New fileItem object
	 */
	public function updateFile($file, $newFile)
	{
		if (! $this->okt->checkPerm('media') && ! $this->okt->checkPerm('media_admin'))
		{
			throw new Exception(__('Permission denied.'));
		}
		
		$id = (integer) $file->media_id;
		
		if (! $id)
		{
			throw new Exception('No file ID');
		}
		
		if (! $this->okt->checkPerm('media_admin') && $this->okt->user->id != $file->media_user_id)
		{
			throw new Exception(__('You are not the file owner.'));
		}
		
		$cur = $this->db->openCursor($this->t_media);
		
		# We need to tidy newFile basename. If dir isn't empty, concat to basename
		$newFile->relname = files::tidyFileName($newFile->basename);
		if ($newFile->dir)
		{
			$newFile->relname = $newFile->dir . '/' . $newFile->relname;
		}
		
		if ($file->relname != $newFile->relname)
		{
			$newFile->file = $this->root . '/' . $newFile->relname;
			
			if (file_exists($newFile->file))
			{
				throw new Exception(__('New file already exists.'));
			}
			
			$this->moveFile($file->relname, $newFile->relname);
			
			$cur->media_file = (string) $newFile->relname;
			$cur->media_dir = (string) dirname($newFile->relname);
		}
		
		$cur->media_title = (string) $newFile->media_title;
		$cur->media_dt = (string) $newFile->media_dtstr;
		$cur->media_upddt = array(
			'NOW()'
		);
		$cur->media_private = (integer) $newFile->media_priv;
		
		$cur->update('WHERE media_id = ' . $id);
		
		$this->callFileHandler($file->type, 'update', $file, $newFile);
	}

	/**
	 * Uploads a file.
	 *
	 * @param
	 *        	tmp		<b>string</b>		Full path of temporary uploaded file
	 * @param
	 *        	name		<b>string</b>		File name (relative to working directory)
	 * @param
	 *        	title	<b>string</b>		File title
	 * @param
	 *        	private	<b>boolean</b>		File is private
	 */
	public function uploadFile($tmp, $name, $title = null, $private = false, $overwrite = false)
	{
		if (! $this->okt->checkPerm('media') && ! $this->okt->checkPerm('media_admin'))
		{
			throw new Exception(__('Permission denied.'));
		}
		
		$name = files::tidyFileName($name);
		
		parent::uploadFile($tmp, $name, $overwrite);
		
		return $this->createFile($name, $title, $private);
	}

	/**
	 * Creates a file from binary content.
	 *
	 * @param
	 *        	name		<b>string</b>		File name (relative to working directory)
	 * @param
	 *        	bits		<b>string</b>		Binary file content
	 */
	public function uploadBits($name, $bits)
	{
		if (! $this->okt->checkPerm('media') && ! $this->okt->checkPerm('media_admin'))
		{
			throw new Exception(__('Permission denied.'));
		}
		
		$name = files::tidyFileName($name);
		
		parent::uploadBits($name, $bits);
		
		return $this->createFile($name, null, null);
	}

	/**
	 * Removes a file.
	 *
	 * @param
	 *        	f		<b>fileItem</b>	fileItem object
	 */
	public function removeFile($f)
	{
		if (! $this->okt->checkPerm('media') && ! $this->okt->checkPerm('media_admin'))
		{
			throw new Exception(__('Permission denied.'));
		}
		
		$media_file = $this->relpwd ? path::clean($this->relpwd . '/' . $f) : path::clean($f);
		
		$strReq = 'DELETE FROM ' . $this->t_media . ' ' . "WHERE media_path = '" . $this->db->escapeStr($this->path) . "' " . "AND media_file = '" . $this->db->escapeStr($media_file) . "' ";
		
		if (! $this->okt->checkPerm('media_admin'))
		{
			$strReq .= 'AND user_id = ' . (integer) $this->okt->user->id . ' ';
		}
		
		$this->db->execute($strReq);
		
		if ($this->db->affectedRows() == 0)
		{
			throw new Exception(__('File does not exist in the database.'));
		}
		
		parent::removeFile($f);
		
		$this->callFileHandler(files::getMimeType($media_file), 'remove', $f);
	}

	/**
	 * Extract zip file in current location
	 *
	 * @param
	 *        	f		<b>fileRecord</b>	fileRecord object
	 */
	public function inflateZipFile($f, $create_dir = true)
	{
		$zip = new fileUnzip($f->file);
		$zip->getList(false, '#(^|/)(__MACOSX|\.svn|\.DS_Store|Thumbs\.db)(/|$)#');
		
		if ($create_dir)
		{
			$zip_root_dir = $zip->getRootDir();
			if ($zip_root_dir != false)
			{
				$destination = $zip_root_dir;
				$target = $f->dir;
			}
			else
			{
				$destination = preg_replace('/\.([^.]+)$/', '', $f->basename);
				$target = $f->dir . '/' . $destination;
			}
			
			if (is_dir($f->dir . '/' . $destination))
			{
				throw new Exception(sprintf(__('Extract destination directory %s already exists.'), dirname($f->relname) . '/' . $destination));
			}
		}
		else
		{
			$target = $f->dir;
			$destination = '';
		}
		
		$zip->unzipAll($target);
		$zip->close();
		return dirname($f->relname) . '/' . $destination;
	}

	/**
	 * Returns zip file content
	 *
	 * @param
	 *        	f		<b>fileRecord</b>	fileRecord object
	 * @return <b>array</b>
	 */
	public function getZipContent($f)
	{
		$zip = new fileUnzip($f->file);
		$list = $zip->getList(false, '#(^|/)(__MACOSX|\.svn|\.DS_Store|Thumbs\.db)(/|$)#');
		$zip->close();
		return $list;
	}
	
	/* Image handlers
	------------------------------------------------------- */
	public function imageThumbCreate($cur, $f, $force = true)
	{
		$file = $this->pwd . '/' . $f;
		
		if (! file_exists($file))
		{
			return false;
		}
		
		$p = path::info($file);
		$thumb = sprintf($this->thumb_tp, $p['dirname'], $p['base'], '%s');
		
		try
		{
			$img = new ImageTools();
			$img->loadImage($file);
			
			$w = $img->getW();
			$h = $img->getH();
			
			if ($force)
			{
				$this->imageThumbRemove($f);
			}
			
			foreach ($this->thumb_sizes as $suffix => $s)
			{
				$thumb_file = sprintf($thumb, $suffix);
				if (! file_exists($thumb_file) && $s[0] > 0 && ($suffix == 'sq' || $w > $s[0] || $h > $s[1]))
				{
					$img->resize($s[0], $s[0], $s[1]);
					$img->output('jpeg', $thumb_file, 80);
				}
			}
			$img->close();
		}
		catch (Exception $e)
		{
			if ($cur === null)
			{ # Called only if cursor is null (public call)
				throw $e;
			}
		}
	}

	protected function imageThumbUpdate($file, &$newFile)
	{
		if ($file->relname != $newFile->relname)
		{
			$p = path::info($file->relname);
			$thumb_old = sprintf($this->thumb_tp, $p['dirname'], $p['base'], '%s');
			
			$p = path::info($newFile->relname);
			$thumb_new = sprintf($this->thumb_tp, $p['dirname'], $p['base'], '%s');
			
			foreach ($this->thumb_sizes as $suffix => $s)
			{
				try
				{
					parent::moveFile(sprintf($thumb_old, $suffix), sprintf($thumb_new, $suffix));
				}
				catch (Exception $e)
				{
				}
			}
		}
	}

	protected function imageThumbRemove($f)
	{
		$p = path::info($f);
		$thumb = sprintf($this->thumb_tp, '', $p['base'], '%s');
		
		foreach ($this->thumb_sizes as $suffix => $s)
		{
			try
			{
				parent::removeFile(sprintf($thumb, $suffix));
			}
			catch (Exception $e)
			{
			}
		}
	}

	protected function imageMetaCreate($cur, $f, $id)
	{
		$file = $this->pwd . '/' . $f;
		
		if (! file_exists($file))
		{
			return false;
		}
		
		$xml = new xmlTag('meta');
		$meta = imageMeta::readMeta($file);
		$xml->insertNode($meta);
		
		$c = $this->db->openCursor($this->t_media);
		$c->media_meta = $xml->toXML();
		
		if ($cur->media_title !== null && $cur->media_title == basename($cur->media_file))
		{
			if ($meta['Title'])
			{
				$c->media_title = $meta['Title'];
			}
		}
		
		if ($meta['DateTimeOriginal'] && $cur->media_dt === '')
		{
			# We set picture time to user timezone
			$media_ts = strtotime($meta['DateTimeOriginal']);
			if ($media_ts !== false)
			{
				$o = dt::getTimeOffset($this->okt->user->timezone, $media_ts);
				$c->media_dt = dt::str('%Y-%m-%d %H:%M:%S', $media_ts + $o);
			}
		}
		
		$c->update('WHERE media_id = ' . $id);
	}

	/**
	 * Returns HTML code for MP3 player
	 *
	 * @param
	 *        	url		<b>string</b>		MP3 URL to play
	 * @param
	 *        	player	<b>string</b>		Player URL
	 * @param
	 *        	args		<b>array</b>		Player parameters
	 * @return <b>string</b>
	 */
	public static function mp3player($url, $player = null, $args = null)
	{
		if (! $player)
		{
			$player = 'player_mp3.swf';
		}
		
		if (! is_array($args))
		{
			$args = array(
				'showvolume' => 1,
				'loadingcolor' => 'ff9900',
				'bgcolor1' => 'eeeeee',
				'bgcolor2' => 'cccccc',
				'buttoncolor' => '0066cc',
				'buttonovercolor' => 'ff9900',
				'slidercolor1' => 'cccccc',
				'slidercolor2' => '999999',
				'sliderovercolor' => '0066cc'
			);
		}
		
		$args['mp3'] = $url;
		
		if (empty($args['width']))
		{
			$args['width'] = 200;
		}
		if (empty($args['height']))
		{
			$args['height'] = 20;
		}
		
		$vars = array();
		foreach ($args as $k => $v)
		{
			$vars[] = $k . '=' . $v;
		}
		
		return '<object type="application/x-shockwave-flash" ' . 'data="' . $player . '" ' . 'width="' . $args['width'] . '" height="' . $args['height'] . '">' . '<param name="movie" value="' . $player . '" />' . '<param name="wmode" value="transparent" />' . '<param name="FlashVars" value="' . implode('&amp;', $vars) . '" />' . '</object>';
	}

	public static function flvplayer($url, $player = null, $args = null)
	{
		if (! $player)
		{
			$player = 'player_flv.swf';
		}
		
		if (! is_array($args))
		{
			$args = array(
				'margin' => 1,
				'showvolume' => 1,
				'showtime' => 1,
				'showfullscreen' => 1,
				'buttonovercolor' => 'ff9900',
				'slidercolor1' => 'cccccc',
				'slidercolor2' => '999999',
				'sliderovercolor' => '0066cc'
			);
		}
		
		$args['flv'] = $url;
		
		if (empty($args['width']))
		{
			$args['width'] = 400;
		}
		if (empty($args['height']))
		{
			$args['height'] = 300;
		}
		
		$vars = array();
		foreach ($args as $k => $v)
		{
			$vars[] = $k . '=' . $v;
		}
		
		return '<object type="application/x-shockwave-flash" ' . 'data="' . $player . '" ' . 'width="' . $args['width'] . '" height="' . $args['height'] . '">' . '<param name="movie" value="' . $player . '" />' . '<param name="wmode" value="transparent" />' . '<param name="allowFullScreen" value="true" />' . '<param name="FlashVars" value="' . implode('&amp;', $vars) . '" />' . '</object>';
	}
}
