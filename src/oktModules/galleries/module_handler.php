<?php
/**
 * @ingroup okt_module_galleries
 * @brief La classe principale du Module Galleries.
 *
 */

class module_galleries extends oktModule
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
		# chargement des principales locales
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/main');

		# autoload
		$this->okt->autoloader->addClassMap(array(
			'galleriesController' => __DIR__.'/inc/class.galleries.controller.php',
			'galleriesHelpers' => __DIR__.'/inc/class.galleries.helpers.php',
			'galleriesItems' => __DIR__.'/inc/class.galleries.items.php',
			'galleriesItemsRecordset' => __DIR__.'/inc/class.galleries.items.recordset.php',
			'galleriesRecordset' => __DIR__.'/inc/class.galleries.recordset.php',
			'galleriesTree' => __DIR__.'/inc/class.galleries.tree.php'
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
		$this->upload_dir = OKT_UPLOAD_PATH.'/galleries/';
		$this->upload_url = OKT_UPLOAD_URL.'/galleries/';

		# déclencheurs
		$this->triggers = new oktTriggers();

		# config
		$this->config = $this->okt->newConfig('conf_galleries');

		$this->config->url = $this->okt->page->getBaseUrl().$this->config->public_list_url[$this->okt->user->language];
		$this->config->feed_url = $this->okt->config->app_path.$this->config->public_feed_url[$this->okt->user->language];

		# définition des routes
		$this->okt->router->addRoute('galleriesList', new oktRoute(
			'^('.html::escapeHTML(implode('|',$this->config->public_list_url)).')$',
			'galleriesController', 'galleriesList'
		));

		$this->okt->router->addRoute('galleriesGallery', new oktRoute(
			'^(?:'.html::escapeHTML(implode('|',$this->config->public_gallery_url)).')/(.*)$',
			'galleriesController', 'galleriesGallery'
		));

		$this->okt->router->addRoute('galleriesItem', new oktRoute(
			'^(?:'.html::escapeHTML(implode('|',$this->config->public_item_url)).')/(.*)$',
			'galleriesController', 'galleriesItem'
		));

		# galleries tree
		$this->tree = new galleriesTree(
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
		$this->items = new galleriesItems(
			$this->okt,
			$this->t_items,
			$this->t_items_locales,
			$this->t_galleries,
			$this->t_galleries_locales
		);
	}

	protected function prepend_admin()
	{
		# on détermine si on est actuellement sur ce module
		$this->onThisModule();

		# chargement des locales admin
		l10n::set(__DIR__.'/locales/'.$this->okt->user->language.'/admin');

		# on ajoutent un item au menu admin
		if (!defined('OKT_DISABLE_MENU'))
		{
			$this->okt->page->galleriesSubMenu = new htmlBlockList(null,adminPage::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add(
				$this->getName(),
				'module.php?m=galleries',
				ON_GALLERIES_MODULE,
				20,
				$this->okt->checkPerm('galleries'),
				null,
				$this->okt->page->galleriesSubMenu,
				$this->url().'/icon.png'
			);
				$this->okt->page->galleriesSubMenu->add(
					__('c_a_menu_management'),
					'module.php?m=galleries&amp;action=index',
					ON_GALLERIES_MODULE && (!$this->okt->page->action || $this->okt->page->action === 'index' || $this->okt->page->action === 'gallery' || $this->okt->page->action === 'items' || $this->okt->page->action === 'edit'),
					1
				);
				$this->okt->page->galleriesSubMenu->add(
					__('m_galleries_menu_add_item'),
					'module.php?m=galleries&amp;action=add',
					ON_GALLERIES_MODULE && ($this->okt->page->action === 'add'),
					2,
					$this->okt->checkPerm('galleries_add')
				);
				$this->okt->page->galleriesSubMenu->add(
					__('m_galleries_menu_add_items'),
					'module.php?m=galleries&amp;action=add_multiples',
					ON_GALLERIES_MODULE && ($this->okt->page->action === 'add_multiples'),
					3,
					$this->config->enable_multiple_upload && $this->okt->checkPerm('galleries_add')
				);
				$this->okt->page->galleriesSubMenu->add(
					__('m_galleries_menu_add_zip'),
					'module.php?m=galleries&amp;action=add_zip',
					ON_GALLERIES_MODULE && ($this->okt->page->action === 'add_zip'),
					4,
					$this->config->enable_zip_upload && $this->okt->checkPerm('galleries_add')
				);
				$this->okt->page->galleriesSubMenu->add(
					__('c_a_menu_display'),
					'module.php?m=galleries&amp;action=display',
					ON_GALLERIES_MODULE && ($this->okt->page->action === 'display'),
					10,
					$this->okt->checkPerm('galleries_display')
				);
				$this->okt->page->galleriesSubMenu->add(
					__('c_a_menu_configuration'),
					'module.php?m=galleries&amp;action=config',
					ON_GALLERIES_MODULE && ($this->okt->page->action === 'config'),
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

} # class
