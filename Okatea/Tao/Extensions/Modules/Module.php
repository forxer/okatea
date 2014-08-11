<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Extensions\Modules;

use Okatea\Tao\Extensions\Extension;

class Module extends Extension
{
	/**
	 * Chemin du répertoire upload du module.
	 *
	 * @var string
	 */
	public $upload_dir;

	/**
	 * URL du répertoire upload du module.
	 *
	 * @var string
	 */
	public $upload_url;

	final public function init()
	{
		parent::init();

		$this->upload_dir = $this->okt['upload_path'] . '/' . $this->getInfo('id');
		$this->upload_url = $this->okt['upload_url'] . '/' . $this->getInfo('id');
	}

	final public function initNs($ns)
	{
		parent::initNs($ns);
	}

	/**
	 * Retourne le nom internationnalisé en provenance de la config.
	 *
	 * @return string
	 */
	final public function getName()
	{
		static $sName = false;

		if ($sName !== false) {
			return $sName;
		}

		if (!isset($this->config) || !isset($this->config->name)) {
			$sName = null;
		}
		elseif (is_array($this->config->name))
		{
			if (isset($this->config->name[$this->okt['visitor']->language])) {
				$sName = $this->config->name[$this->okt['visitor']->language];
			}
			elseif ($this->config->name[$this->okt['config']->language]) {
				$sName = $this->config->name[$this->okt['config']->language];
			}
		}
		else {
			$sName = $this->config->name;
		}

		return $sName;
	}

	/**
	 * Retourne le title internationnalisé en provenance de la config.
	 *
	 * @return string
	 */
	final public function getTitle()
	{
		static $sTitle = false;

		if ($sTitle !== false) {
			return $sTitle;
		}

		if (!isset($this->config) || !isset($this->config->title)) {
			$sTitle = null;
		}
		elseif (is_array($this->config->title))
		{
			if (isset($this->config->title[$this->okt['visitor']->language])) {
				$sTitle = $this->config->title[$this->okt['visitor']->language];
			}
			elseif ($this->config->title[$this->okt['config']->language]) {
				$sTitle = $this->config->title[$this->okt['config']->language];
			}
		}
		else {
			$sTitle = $this->config->title;
		}

		return $sTitle;
	}

	/**
	 * Retourne le titre SEO internationnalisé en provenance de la config.
	 *
	 * @return string
	 */
	final public function getNameSeo()
	{
		static $sNameSeo = false;

		if ($sNameSeo !== false) {
			return $sNameSeo;
		}

		if (!isset($this->config) || !isset($this->config->title)) {
			$sNameSeo = null;
		}
		elseif (is_array($this->config->name_seo))
		{
			if (isset($this->config->name_seo[$this->okt['visitor']->language])) {
				$sNameSeo = $this->config->name_seo[$this->okt['visitor']->language];
			}
			elseif ($this->config->name_seo[$this->okt['config']->language]) {
				$sNameSeo = $this->config->name_seo[$this->okt['config']->language];
			}
		}
		else {
			$sNameSeo = $this->config->name_seo;
		}

		return $sNameSeo;
	}
}
