<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Module\News;

use Tao\Core\Authentification;
use Tao\Database\Recordset as BaseRecordset;
use Tao\Misc\Utilities;

/**
 * Extension du recordset de base pour l'affichage des articles.
 */
class Recordset extends BaseRecordset
{
	/**
	 * L'objet oktCore
	 * @access private
	 * @var object
	 */
	private $okt;

	/**
	 * Défini l'objet de type oktCore qui sera passé à la classe après
	 * qu'elle ait été instanciée.
	 *
	 * @param oktCore okt 	Objet de type core
	 * @return void
	 */
	public function setCore($okt)
	{
		$this->okt = $okt;
	}

	/**
	 * Returns whether post is "showable".
	 *
	 * @return boolean
	 */
	public function isShowable()
	{
		# If user is admin or contentadmin, true
		if ($this->okt->checkPerm('news_contentadmin') || $this->okt->checkPerm('m_news_perm_show_all')) {
			return true;
		}

		# No user id in result ? false
		if (!$this->exists('user_id')) {
			return false;
		}

		# If user is owner of the entrie
		if ($this->user_id == $this->okt->user->id) {
			return true;
		}

		return false;
	}

	/**
	 * Returns whether post is editable.
	 *
	 * @return boolean
	 */
	public function isEditable()
	{
		# If user is admin or contentadmin, true
		if ($this->okt->checkPerm('news_contentadmin')) {
			return true;
		}

		# No user id in result ? false
		if (!$this->exists('user_id')) {
			return false;
		}

		# If user is usage and owner of the entrie
		if ($this->okt->checkPerm('news_usage')
		&& $this->user_id == $this->okt->user->id) {
			return true;
		}

		return false;
	}

	/**
	 * Returns whether post is publishable
	 *
	 * @return boolean
	 */
	public function isPublishable()
	{
		# If user is admin or contentadmin, true
		if ($this->okt->checkPerm('news_contentadmin')) {
			return true;
		}

		# No user id in result ? false
		if (!$this->exists('user_id')) {
			return false;
		}

		# If user is usage and owner of the entrie
		if ($this->okt->checkPerm('news_publish')
		&& $this->user_id == $this->okt->user->id) {
			return true;
		}

		return false;
	}

	/**
	 * Returns whether post is deletable
	 *
	 * @return boolean
	 */
	public function isDeletable()
	{
		# If user is admin, or contentadmin, true
		if ($this->okt->checkPerm('news_contentadmin')) {
			return true;
		}

		# No user id in result ? false
		if (!$this->exists('user_id')) {
			return false;
		}

		# If user has delete rights and is owner of the entrie
		if ($this->okt->checkPerm('news_delete')
		&& $this->user_id == $this->okt->user->id) {
			return true;
		}

		return false;
	}

	/**
	 * Returns whether post is readable
	 *
	 * @return boolean
	 */
	public function isReadable()
	{
		static $perms = array();

		# si on as un "cache" on l'utilisent
		if (isset($perms[$this->id])) {
			return $perms[$this->id];
		}

		# si les permissions sont désactivées alors on as le droit
		if (!$this->okt->News->canUsePerms())
		{
			$perms[$this->id] = true;
			return true;
		}

		# si on est superadmin on as droit à tout
		if ($this->okt->user->is_superadmin)
		{
			$perms[$this->id] = true;
			return true;
		}

		# récupération des permissions de l'actualité
		$aPerms = $this->okt->News->getPostPermissions($this->id);

		# si on a le groupe id 0 (zero) alors tous le monde a droit
		# sinon il faut etre dans le bon groupe
		if (in_array(0,$aPerms) || in_array($this->okt->user->group_id,$aPerms))
		{
			$perms[$this->id] = true;
			return true;
		}

		# toutes éventualités testées, on as pas le droit
		$perms[$this->id] = false;
		return false;
	}

	/**
	 * Retourne l'auteur d'un article
	 *
	 * @return string
	 */
	public function getPostAuthor()
	{
		return Authentification::getUserCN($this->username, $this->lastname, $this->firstname);
	}

	/**
	 * Retourne l'URL publique d'un article
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public function getPostUrl($sLanguage=null)
	{
		if (empty($this->slug)) {
			return null;
		}

		return $this->okt->router->generate('newsItem', array('slug' => $this->slug), $sLanguage);
	}

	/**
	 * Retourne l'URL publique d'une rubrique
	 *
	 * @param string $sLanguage
	 * @return string
	 */
	public function getCategoryUrl($sLanguage=null)
	{
		if (empty($this->category_slug)) {
			return null;
		}

		return $this->okt->router->generate('newsCategory', array('slug' => $this->category_slug), $sLanguage);
	}

	/**
	 * Retourne les informations des fichiers d'un article
	 *
	 * @return array
	 */
	public function getFilesInfo()
	{
		$files = array();

		if (!$this->okt->News->config->files['enable']) {
			return $files;
		}

		$files_array = array_filter((array)unserialize($this->files));

		$j=1;
		for ($i=1; $i<=$this->okt->News->config->files['number']; $i++)
		{
			if (!isset($files_array[$i]) || empty($files_array[$i]['filename'])
				|| !file_exists($this->okt->News->upload_dir.'files/'.$files_array[$i]['filename']))
			{
				continue;
			}

			$mime_type = \files::getMimeType($this->okt->News->upload_dir.'files/'.$files_array[$i]['filename']);

			$files[$j++] = array_merge(
				stat($this->okt->News->upload_dir.'files/'.$files_array[$i]['filename']),
				array(
					'url' => $this->okt->News->upload_url.'files/'.$files_array[$i]['filename'],
					'filename' => $files_array[$i]['filename'],
					'title' => $files_array[$i]['title'],
					'mime' => $mime_type,
					'type' => Utilities::getMediaType($mime_type),
					'ext' => pathinfo($this->okt->News->upload_dir.'files/'.$files_array[$i]['filename'],PATHINFO_EXTENSION)
				)
			);
		}

		return $files;
	}

	/**
	 * Retourne les informations des images d'un article en fonction des données de la BDD
	 *
	 * @return 	array
	 */
	public function getImagesInfo()
	{
		if (!$this->okt->News->config->images['enable']) {
			return array();
		}

		return $this->getImagesArray();
	}

	/**
	 * Retourne les informations de la première image d'un article
	 * en fonction des données de la BDD
	 *
	 * @return 	array
	 */
	public function getFirstImageInfo()
	{
		if (!$this->okt->News->config->images['enable']) {
			return array();
		}

		$a = $this->getImagesArray();

		return isset($a[1]) ? $a[1] : array();
	}

	public function getImagesArray()
	{
		return is_array($this->images) ? $this->images : array_filter((array)unserialize($this->images));
	}

	public function getCurrentImagesDir()
	{
		return $this->okt->News->upload_dir.'img/'.$this->id.'/';
	}

	public function getCurrentImagesUrl()
	{
		return $this->okt->News->upload_url.'img/'.$this->id.'/';
	}

}
