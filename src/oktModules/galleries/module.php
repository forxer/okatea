<?php
/**
 * @ingroup okt_module_galleries
 * @brief La classe principale du Module Galleries.
 *
 */

use Tao\Admin\Page;
use Tao\Admin\Menu as AdminMenu;
use Tao\Modules\Module;
use Tao\Routing\Route;
use Tao\Core\Triggers;

class module_galleries extends Module
{
	protected $t_galleries;
	protected $t_galleries_locales;
	protected $t_items;
	protected $t_items_locales;

	public $config = null;
	public $tree;

	public $upload_dir;
	public $upload_url;


	protected function prepend()
	{
		# autoload
		$this->okt->autoloader->addClassMap(array(
			'GalleriesController' => __DIR__.'/inc/GalleriesController.php',
			'GalleriesHelpers' => __DIR__.'/inc/GalleriesHelpers.php',
			'GalleriesItems' => __DIR__.'/inc/GalleriesItems.php',
			'GalleriesItemsRecordset' => __DIR__.'/inc/GalleriesItemsRecordset.php',
			'GalleriesRecordset' => __DIR__.'/inc/GalleriesRecordset.php',
			'GalleriesTree' => __DIR__.'/inc/GalleriesTree.php'
		));

		# permissions
		$this->okt->addPermGroup('galleries', __('m_galleries_perm_group'));
			$this->okt->addPerm('galleries', __('m_galleries_perm_global'), 'galleries');
			$this->okt->addPerm('galleries_manage', __('m_galleries_perm_manage'), 'galleries');
			$this->okt->addPerm('galleries_add', __('m_galleries_perm_add'), 'galleries');
			$this->okt->addPerm('galleries_remove', __('m_galleries_perm_remove'), 'galleries');
			$this->okt->addPerm('galleries_display', __('m_galleries_perm_display'), 'galleries');
			$this->okt->addPerm('galleries_config', __('m_galleries_perm_config'), 'galleries');

		# tables
		$this->t_galleries = $this->db->prefix.'mod_galleries';
		$this->t_galleries_locales = $this->db->prefix.'mod_galleries_locales';
		$this->t_items = $this->db->prefix.'mod_galleries_items';
		$this->t_items_locales = $this->db->prefix.'mod_galleries_items_locales';

		# répertoire upload
		$this->upload_dir = $this->okt->options->get('upload_dir').'/galleries/';
		$this->upload_url = $this->okt->options->upload_url.'/galleries/';

		# déclencheurs
		$this->triggers = new Triggers();

		# config
		$this->config = $this->okt->newConfig('conf_galleries');

		# galleries tree
		$this->tree = new GalleriesTree(
			$this->okt,
			$this->t_items,
			$this->t_items_locales,
			$this->t_galleries,
			$this->t_galleries_locales,
			'id',
			'parent_id',
			'ord',
			'gallery_id',
			'language',
			array(
				'active',
				'ord',
				'locked',
				'password',
				'tpl',
				'items_tpl'
			),
			array(
				'title',
				'title_tag',
				'title_seo',
				'slug',
				'content',
				'meta_description',
				'meta_keywords'
			),
			$this->upload_dir,
			$this->upload_url
		);

		/* THE OLD ONE (remenber the fields names)
		$this->tree = new nestedTree(
			$this->okt,
			$this->t_galleries,
			'id',
			'parent_id',
			'ord',
			array(
				'active',
				'name',
				'slug',
				'ord',
				'image',
				'description',
				'password'
			)
		);*/



		# galleries items
		$this->items = new GalleriesItems(
			$this->okt,
			$this->t_items,
			$this->t_items_locales,
			$this->t_galleries,
			$this->t_galleries_locales
		);
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->galleriesSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add(
				$this->getName(),
				'module.php?m=galleries',
				$this->bCurrentlyInUse,
				20,
				$this->okt->checkPerm('galleries'),
				null,
				$this->okt->page->galleriesSubMenu,
				$this->url().'/icon.png'
			);
				$this->okt->page->galleriesSubMenu->add(
					__('c_a_menu_management'),
					'module.php?m=galleries&amp;action=index',
					$this->bCurrentlyInUse && (!$this->okt->page->action || $this->okt->page->action === 'index' || $this->okt->page->action === 'gallery' || $this->okt->page->action === 'items' || $this->okt->page->action === 'edit'),
					1
				);
				$this->okt->page->galleriesSubMenu->add(
					__('m_galleries_menu_add_item'),
					'module.php?m=galleries&amp;action=add',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'add'),
					2,
					$this->okt->checkPerm('galleries_add')
				);
				$this->okt->page->galleriesSubMenu->add(
					__('m_galleries_menu_add_items'),
					'module.php?m=galleries&amp;action=add_multiples',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'add_multiples'),
					3,
					$this->config->enable_multiple_upload && $this->okt->checkPerm('galleries_add')
				);
				$this->okt->page->galleriesSubMenu->add(
					__('m_galleries_menu_add_zip'),
					'module.php?m=galleries&amp;action=add_zip',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'add_zip'),
					4,
					$this->config->enable_zip_upload && $this->okt->checkPerm('galleries_add')
				);
				$this->okt->page->galleriesSubMenu->add(
					__('c_a_menu_display'),
					'module.php?m=galleries&amp;action=display',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'display'),
					10,
					$this->okt->checkPerm('galleries_display')
				);
				$this->okt->page->galleriesSubMenu->add(
					__('c_a_menu_configuration'),
					'module.php?m=galleries&amp;action=config',
					$this->bCurrentlyInUse && ($this->okt->page->action === 'config'),
					20,
					$this->okt->checkPerm('galleries_config')
				);
		}
	}


	/* Divers...
	----------------------------------------------------------*/

	/**
	 * Retourne la liste des types d'upload multiples disponibles.
	 *
	 * @param boolean $bFlip
	 * @return array
	 */
	public function getMultipleUploadTypes($bFlip=false)
	{
		$aUploadTypes = array(
			'Plupload' => 'plupload',
		);

		if ($bFlip) {
			$aUploadTypes = array_flip($aUploadTypes);
		}

		return $aUploadTypes;
	}

}
