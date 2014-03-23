<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Modules\Contact;

use Okatea\Admin\Menu as AdminMenu;
use Okatea\Admin\Page;
use Okatea\Tao\Extensions\Modules\Module as BaseModule;

class Module extends BaseModule
{
	public $config = null;

	public $fields;

	protected function prepend()
	{
		# permissions
		$this->okt->addPermGroup('contact', __('m_contact_perm_group'));
			$this->okt->addPerm('contact_usage', 		__('m_contact_perm_global'), 'contact');
			$this->okt->addPerm('contact_recipients', 	__('m_contact_perm_recipients'), 'contact');
			$this->okt->addPerm('contact_fields', 		__('m_contact_perm_fields'), 'contact');
			$this->okt->addPerm('contact_config', 		__('m_contact_perm_config'), 'contact');

		# config
		$this->config = $this->okt->newConfig('conf_contact');

		# custom fields
		$this->fields = new Fields($this->okt);
	}

	protected function prepend_admin()
	{
		# on ajoutent un item au menu admin
		if ($this->okt->page->display_menu)
		{
			$this->okt->page->contactSubMenu = new AdminMenu(null, Page::$formatHtmlSubMenu);
			$this->okt->page->mainMenu->add(
				$this->getName(),
				$this->okt->adminRouter->generate('Contact_index'),
				$this->okt->request->attributes->get('_route') === 'Contact_index',
				2000,
				$this->okt->checkPerm('contact_usage'),
				null,
				$this->okt->page->contactSubMenu,
				$this->okt->options->public_url.'/modules/'.$this->id().'/module_icon.png'
			);
				$this->okt->page->contactSubMenu->add(
					__('m_contact_menu_recipients'),
					$this->okt->adminRouter->generate('Contact_index'),
					$this->okt->request->attributes->get('_route') === 'Contact_index',
					10,
					$this->okt->checkPerm('contact_usage') && $this->okt->checkPerm('contact_recipients')
				);
				$this->okt->page->contactSubMenu->add(
					__('m_contact_menu_fields'),
					$this->okt->adminRouter->generate('Contact_fields'),
					in_array($this->okt->request->attributes->get('_route'), array('Contact_fields', 'Contact_field')),
					20,
					$this->okt->checkPerm('contact_usage') && $this->okt->checkPerm('contact_fields')
				);
				$this->okt->page->contactSubMenu->add(
					__('m_contact_menu_configuration'),
					$this->okt->adminRouter->generate('Contact_config'),
					$this->okt->request->attributes->get('_route') === 'Contact_config',
					30,
					$this->okt->checkPerm('contact_usage') && $this->okt->checkPerm('contact_config')
				);
		}
	}

	protected function prepend_public()
	{
		$this->okt->page->loadCaptcha($this->config->captcha);
	}

	/**
	 * Retourne l'adresse de la société pour le plan Google Map.
	 * Si les coordonnées GPS  sont remplies, elles prennent le pas sur l'adresse complète.
	 *
	 * @return string
	 */
	public function getAdressForGmap()
	{
		if ($this->okt->config->gps['lat'] != '' && $this->okt->config->gps['long'] != '')
		{
			return $this->okt->config->gps['lat'].', '.$this->okt->config->gps['long'];
		}
		else
		{
			$sAdressForGmap =
			$this->okt->config->address['street'].' '.
			(!empty($this->okt->config->address['street_2']) ? $this->okt->config->address['street_2'].' ' : '').
			$this->okt->config->address['code'].' '.
			$this->okt->config->address['city'].' '.
			$this->okt->config->address['country'];

			return str_replace(',', '', $sAdressForGmap);
		}
	}

	public function genImgMail()
	{
		$font = $this->okt->options->get('public_dir').'/fonts/OpenSans/OpenSans-Regular.ttf';
		$size = ($this->config->email_size * 72) / 96;
		$image_src = $this->okt->options->get('public_dir').'/img/misc/empty.png';

		# Génération de l'image de base
		list($width_orig, $height_orig) = getimagesize($image_src);
		$image_in = imagecreatefrompng($image_src);
		imagealphablending($image_in, false);
		imagesavealpha($image_in, true);

		# Calcul de l'espace que prendra le texte
		$aParam = imageftbbox($size, 0, $font, $this->okt->config->email['to']);
		$dest_w = $aParam[4] - $aParam[6] + 2;
		$dest_h = $aParam[1] - $aParam[7] + 2;

		# Génération de l'image final
		$image_out = imagecreatetruecolor($dest_w, $dest_h);
		imagealphablending($image_out, false);
		imagesavealpha($image_out, true);
		imagecopyresampled($image_out, $image_in, 0, 0, 0, 0, $dest_w, $dest_h, $width_orig, $height_orig);

		# Ajout du texte dans l'image
		$email_color = $this->config->email_color;
		if ($email_color[0] === '#') {
			$email_color = substr($email_color, 1);
		}

		$txt_color = imagecolorallocate($image_out, hexdec(substr($email_color, 0, 2)), hexdec(substr($email_color, 2, 2)), hexdec(substr($email_color, 4, 2)));
		imagettftext($image_out, $size, 0, 0, 12, $txt_color, $font, $this->okt->config->email['to']);

		# Génération du src de l'image et destruction des ressources
		ob_start();
		imagepng($image_out, null, 9);
		$contenu_image = ob_get_contents();
		ob_end_clean();

		imagedestroy($image_in);
		imagedestroy($image_out);

		return "data:image/png;base64,".base64_encode($contenu_image);
	}
}
