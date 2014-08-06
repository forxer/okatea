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
use Okatea\Tao\Html\Escaper;

class Module extends BaseModule
{

	public $config = null;

	public $fields;

	protected function prepend()
	{
		# permissions
		$this->okt['permissions']->addPermGroup('contact', __('m_contact_perm_group'));
		$this->okt['permissions']->addPerm('contact_usage', __('m_contact_perm_global'), 'contact');
		$this->okt['permissions']->addPerm('contact_recipients', __('m_contact_perm_recipients'), 'contact');
		$this->okt['permissions']->addPerm('contact_fields', __('m_contact_perm_fields'), 'contact');
		$this->okt['permissions']->addPerm('contact_config', __('m_contact_perm_config'), 'contact');
		
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
			$this->okt->page->mainMenu->add($this->getName(), $this->okt['adminRouter']->generate('Contact_index'), $this->okt['request']->attributes->get('_route') === 'Contact_index', 2000, $this->okt['visitor']->checkPerm('contact_usage'), null, $this->okt->page->contactSubMenu, $this->okt['public_url'] . '/modules/' . $this->id() . '/module_icon.png');
			$this->okt->page->contactSubMenu->add(__('m_contact_menu_recipients'), $this->okt['adminRouter']->generate('Contact_index'), $this->okt['request']->attributes->get('_route') === 'Contact_index', 10, $this->okt['visitor']->checkPerm('contact_usage') && $this->okt['visitor']->checkPerm('contact_recipients'));
			$this->okt->page->contactSubMenu->add(__('m_contact_menu_fields'), $this->okt['adminRouter']->generate('Contact_fields'), in_array($this->okt['request']->attributes->get('_route'), array(
				'Contact_fields',
				'Contact_field_add',
				'Contact_field_values',
				'Contact_field'
			)), 20, $this->okt['visitor']->checkPerm('contact_usage') && $this->okt['visitor']->checkPerm('contact_fields'));
			$this->okt->page->contactSubMenu->add(__('m_contact_menu_configuration'), $this->okt['adminRouter']->generate('Contact_config'), $this->okt['request']->attributes->get('_route') === 'Contact_config', 30, $this->okt['visitor']->checkPerm('contact_usage') && $this->okt['visitor']->checkPerm('contact_config'));
		}
	}

	protected function prepend_public()
	{
		$this->okt->page->loadCaptcha($this->config->captcha);
	}

	/**
	 * Retourne les destinataires To.
	 *
	 * @return array
	 */
	public function getRecipientsTo()
	{
		if (empty($this->aRecipientsTo))
		{
			if (! empty($this->config->recipients_to))
			{
				$this->aRecipientsTo = (array) $this->config->recipients_to;
			}
			
			if (empty($this->aRecipientsTo))
			{
				if (! empty($this->okt['config']->email['name']))
				{
					$this->aRecipientsTo = array(
						$this->okt['config']->email['to'] => Escaper::html($this->okt['config']->email['name'])
					);
				}
				else
				{
					$this->aRecipientsTo = array(
						$this->okt['config']->email['to']
					);
				}
			}
		}
		
		return $this->aRecipientsTo;
	}

	/**
	 * Définit les destinataires To.
	 *
	 * @param array $aRecipientsTo        	
	 * @return void
	 */
	public function setRecipientsTo($aRecipientsTo)
	{
		$this->aRecipientsTo = $aRecipientsTo;
	}

	/**
	 * Retourne les destinataires Cc.
	 *
	 * @return array
	 */
	public function getRecipientsCc()
	{
		if (empty($this->aRecipientsCc))
		{
			$this->aRecipientsCc = ! empty($this->config->recipients_cc) ? (array) $this->config->recipients_cc : array();
		}
		
		return $this->aRecipientsCc;
	}

	/**
	 * Définit les destinataires Cc.
	 *
	 * @param array $aRecipientsCc        	
	 * @return void
	 */
	public function setRecipientsCc($aRecipientsCc)
	{
		$this->aRecipientsCc = $aRecipientsCc;
	}

	/**
	 * Retourne les destinataires Bcc.
	 *
	 * @return array
	 */
	public function getRecipientsBcc()
	{
		if (empty($this->aRecipientsBcc))
		{
			$this->aRecipientsBcc = ! empty($this->config->recipients_bcc) ? (array) $this->config->recipients_bcc : array();
		}
		
		return $this->aRecipientsBcc;
	}

	/**
	 * Définit les destinataires Bcc.
	 *
	 * @param array $aRecipientsBcc        	
	 * @return void
	 */
	public function setRecipientsBcc($aRecipientsBcc)
	{
		$this->aRecipientsBcc = $aRecipientsBcc;
	}

	/**
	 * Retourne la valeur de FromTo.
	 *
	 * @return mixed
	 */
	public function getFromTo()
	{
		if (empty($this->mFromTo))
		{
			$this->setFromToFromPostedData();
		}
		
		return $this->mFromTo;
	}

	/**
	 * Définit le FromTO.
	 *
	 * @param mixed $mFromTo        	
	 * @return void
	 */
	public function setFromTo($mFromTo)
	{
		$this->mFromTo = $mFromTo;
	}

	/**
	 * Définit le FromTO en fonction des données saisies dans le formulaire.
	 *
	 * @return void
	 */
	public function setFromToFromPostedData()
	{
		$this->mFromTo = $this->aPostedData[4];
		
		if (! empty($this->aPostedData[2]))
		{
			if (! empty($this->aPostedData[3]))
			{
				$this->mFromTo = array(
					$this->aPostedData[4] => $this->aPostedData[3] . ' ' . $this->aPostedData[2]
				);
			}
			else
			{
				$this->mFromTo = array(
					$this->aPostedData[4] => $this->aPostedData[2]
				);
			}
		}
	}

	/**
	 * Retourne la valeur de ReplyTo.
	 *
	 * @return mixed
	 */
	public function getReplyTo()
	{
		if (empty($this->mReplyTo))
		{
			$this->setReplyToFromPostedData();
		}
		
		return $this->mReplyTo;
	}

	/**
	 * Définit le ReplyTo.
	 *
	 * @param mixed $mReplyTo        	
	 * @return void
	 */
	public function setReplyTo($mReplyTo)
	{
		$this->mReplyTo = $mReplyTo;
	}

	/**
	 * Définit le ReplyTo en fonction des données saisies dans le formulaire.
	 *
	 * @return void
	 */
	public function setReplyToFromPostedData()
	{
		$this->mReplyTo = $this->aPostedData[4];
		
		if (! empty($this->aPostedData[2]))
		{
			if (! empty($this->aPostedData[3]))
			{
				$this->mReplyTo = array(
					$this->aPostedData[4] => $this->aPostedData[3] . ' ' . $this->aPostedData[2]
				);
			}
			else
			{
				$this->mReplyTo = array(
					$this->aPostedData[4] => $this->aPostedData[2]
				);
			}
		}
	}

	/**
	 * Retourne le nom de l'expediteur.
	 *
	 * @return string
	 */
	public function getSenderName()
	{
		if (empty($this->sSenderName))
		{
			$this->setSenderNameFromPostedData();
		}
		
		return (string) $this->sSenderName;
	}

	/**
	 * Définit le nom de l'expediteur.
	 *
	 * @param string $sSenderName        	
	 * @return void
	 */
	public function setSenderName($sSenderName)
	{
		$this->sSenderName = (string) $sSenderName;
	}

	/**
	 * Définit le nom de l'expediteur en fonction des données saisies dans le formulaire.
	 *
	 * @return void
	 */
	public function setSenderNameFromPostedData()
	{
		$this->sSenderName = '';
		
		if (isset($this->aPostedData[1]))
		{
			switch ($this->aPostedData[1])
			{
				case 0:
					$this->sSenderName .= 'Madame ';
					break;
				
				case 1:
					$this->sSenderName .= 'Mademoiselle ';
					break;
				
				case 2:
					$this->sSenderName .= 'Monsieur ';
					break;
			}
		}
		
		if (! empty($this->aPostedData[2]))
		{
			$this->sSenderName .= $this->aPostedData[2] . ' ';
		}
		
		if (! empty($this->aPostedData[3]))
		{
			$this->sSenderName .= $this->aPostedData[3];
		}
	}

	/**
	 * Retourne le sujet du mail.
	 *
	 * @return string
	 */
	public function getSubject()
	{
		if (empty($this->sSubject))
		{
			$this->setSubjectFromPostedData();
		}
		
		return (string) $this->sSubject;
	}

	/**
	 * Définit le sujet du mail.
	 *
	 * @param string $sSubject        	
	 * @return void
	 */
	public function setSubject($sSubject)
	{
		$this->sSubject = (string) $sSubject;
	}

	/**
	 * Définit le sujet du mail en fonction des données saisies dans le formulaire.
	 *
	 * @return void
	 */
	public function setSubjectFromPostedData()
	{
		if (! empty($this->aPostedData[6]))
		{
			$this->sSubject = Escaper::html($this->aPostedData[6]);
		}
		else
		{
			$this->sSubject = 'Contact depuis le site internet ' . Escaper::html($this->okt->page->getSiteTitle());
		}
	}

	/**
	 * Retourne le corps du mail.
	 *
	 * @return string
	 */
	public function getBody()
	{
		if (empty($this->sBody))
		{
			$this->setBodyFromPostedData();
		}
		
		return (string) $this->sBody;
	}

	/**
	 * Définit le corps du mail.
	 *
	 * @param string $sSubject        	
	 * @return void
	 */
	public function setBody($sBody)
	{
		$this->sBody = (string) $sBody;
	}

	/**
	 * Définit le corps du mail en fonction des données saisies dans le formulaire.
	 *
	 * @return void
	 */
	public function setBodyFromPostedData()
	{
		$this->sBody = 'Contact depuis le site internet ' . Escaper::html($this->okt->page->getSiteTitle()) . ' [' . $this->okt['request']->getSchemeAndHttpHost() . $this->okt['config']->app_url . ']' . PHP_EOL . PHP_EOL;
		
		$sSenderName = $this->getSenderName();
		if (! empty($sSenderName))
		{
			$this->sBody .= 'Nom : ' . $sSenderName . PHP_EOL;
		}
		
		$this->sBody .= 'E-mail : ' . $this->aPostedData[4] . PHP_EOL;
		
		if (! empty($this->aPostedData[5]))
		{
			$this->sBody .= 'Téléphone : ' . $this->aPostedData[5] . PHP_EOL;
		}
		
		$this->sBody .= PHP_EOL . 'Sujet : ' . $this->getSubject() . PHP_EOL;
		
		$this->sBody .= 'Message : ' . PHP_EOL . PHP_EOL;
		$this->sBody .= $this->aPostedData[7] . PHP_EOL . PHP_EOL;
		
		# ajout des autres champs
		while ($this->rsFields->fetch())
		{
			if ($this->isDefaultField($this->rsFields->id))
			{
				continue;
			}
			
			if (! empty($this->aPostedData[$this->rsFields->id]))
			{
				$sFieldValue = null;
				
				switch ($this->rsFields->type)
				{
					default:
					case 1: # Champ texte
					case 2: # Zone de texte
						$sFieldValue = $this->aPostedData[$this->rsFields->id];
						break;
					
					case 3: # Menu déroulant
					case 4: # Boutons radio
					case 5: # Cases à cocher
						$aValues = array_filter((array) unserialize($this->rsFields->value));
						
						if (is_array($this->aPostedData[$this->rsFields->id]))
						{
							$aFieldValue = array();
							foreach ($this->aPostedData[$this->rsFields->id] as $value)
							{
								if (isset($aValues[$value]))
								{
									$aFieldValue[] = $aValues[$value];
								}
							}
							$sFieldValue = implode(', ', $aFieldValue);
						}
						else
						{
							$sFieldValue = (isset($aValues[$this->aPostedData[$this->rsFields->id]]) ? $aValues[$this->aPostedData[$this->rsFields->id]] : '');
						}
						break;
				}
				
				$this->sBody .= Escaper::html($this->rsFields->title) . ' : ' . Escaper::html($sFieldValue) . PHP_EOL;
			}
		}
	}

	/**
	 * Retourne l'adresse de la société pour le plan Google Map.
	 * Si les coordonnées GPS sont remplies, elles prennent le pas sur l'adresse complète.
	 *
	 * @return string
	 */
	public function getAdressForGmap()
	{
		if ($this->okt['config']->gps['lat'] != '' && $this->okt['config']->gps['long'] != '')
		{
			return $this->okt['config']->gps['lat'] . ', ' . $this->okt['config']->gps['long'];
		}
		else
		{
			$sAdressForGmap = $this->okt['config']->address['street'] . ' ' . (! empty($this->okt['config']->address['street_2']) ? $this->okt['config']->address['street_2'] . ' ' : '') . $this->okt['config']->address['code'] . ' ' . $this->okt['config']->address['city'] . ' ' . $this->okt['config']->address['country'];
			
			return str_replace(',', '', $sAdressForGmap);
		}
	}

	public function genImgMail()
	{
		$font = $this->okt['public_path'] . '/fonts/OpenSans/OpenSans-Regular.ttf';
		$size = ($this->config->email_size * 72) / 96;
		$image_src = $this->okt['public_path'] . '/img/misc/empty.png';
		
		# Génération de l'image de base
		list ($width_orig, $height_orig) = getimagesize($image_src);
		$image_in = imagecreatefrompng($image_src);
		imagealphablending($image_in, false);
		imagesavealpha($image_in, true);
		
		# Calcul de l'espace que prendra le texte
		$aParam = imageftbbox($size, 0, $font, $this->okt['config']->email['to']);
		$dest_w = $aParam[4] - $aParam[6] + 2;
		$dest_h = $aParam[1] - $aParam[7] + 2;
		
		# Génération de l'image final
		$image_out = imagecreatetruecolor($dest_w, $dest_h);
		imagealphablending($image_out, false);
		imagesavealpha($image_out, true);
		imagecopyresampled($image_out, $image_in, 0, 0, 0, 0, $dest_w, $dest_h, $width_orig, $height_orig);
		
		# Ajout du texte dans l'image
		$email_color = $this->config->email_color;
		if ($email_color[0] === '#')
		{
			$email_color = substr($email_color, 1);
		}
		
		$txt_color = imagecolorallocate($image_out, hexdec(substr($email_color, 0, 2)), hexdec(substr($email_color, 2, 2)), hexdec(substr($email_color, 4, 2)));
		imagettftext($image_out, $size, 0, 0, 12, $txt_color, $font, $this->okt['config']->email['to']);
		
		# Génération du src de l'image et destruction des ressources
		ob_start();
		imagepng($image_out, null, 9);
		$contenu_image = ob_get_contents();
		ob_end_clean();
		
		imagedestroy($image_in);
		imagedestroy($image_out);
		
		return "data:image/png;base64," . base64_encode($contenu_image);
	}
}
