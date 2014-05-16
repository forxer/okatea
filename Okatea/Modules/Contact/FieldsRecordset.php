<?php

/**
 * @ingroup okt_module_contact
 * @brief Extension du recordset pour les champs de la page contact
 *
 */
namespace Okatea\Modules\Contact;

use Okatea\Tao\Database\Recordset;
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Html\Escaper;

class FieldsRecordset extends Recordset
{

	/**
	 * Okatea application instance.
	 * 
	 * @var object Okatea\Tao\Application
	 */
	protected $okt;

	/**
	 * Défini l'instance de l'application qui sera passée à l'objet après
	 * qu'il ait été instancié.
	 *
	 * @param
	 *        	Okatea\Tao\Application okt Okatea application instance.
	 * @return void
	 */
	public function setCore($okt)
	{
		$this->okt = $okt;
	}

	public function getOptions()
	{
		return (array) unserialize($this->options);
	}

	public function isSimpleField()
	{
		return Fields::isSimpleType($this->type);
	}

	public function getHtmlField()
	{
		$return = '';
		switch ($this->type)
		{
			# Champ texte
			default:
			case 1:
				$return = '<p class="field" id="' . $this->html_id . '-wrapper">' . '<label for="' . $this->html_id . '"' . ($this->status == 2 ? ' class="required" title="' . __('c_c_required_field') . '"' : '') . '>' . Escaper::html($this->title) . '</label>' . form::text($this->html_id, 60, 255, $this->okt->module('Contact')->aPostedData[$this->id]) . '</p>';
				break;
			
			# Zone de texte
			case 2:
				$return = '<p class="field" id="' . $this->html_id . '-wrapper">' . '<label for="' . $this->html_id . '"' . ($this->status == 2 ? ' class="required" title="' . __('c_c_required_field') . '"' : '') . '>' . Escaper::html($this->title) . '</label>' . form::textarea($this->html_id, 58, 10, $this->okt->module('Contact')->aPostedData[$this->id]) . '</p>';
				break;
			
			# Menu déroulant
			case 3:
				$values = array_filter((array) unserialize($this->value));
				
				$return = '<p class="field" id="' . $this->html_id . '-wrapper">' . '<label for="' . $this->html_id . '"' . ($this->status == 2 ? ' class="required" title="' . __('c_c_required_field') . '"' : '') . '>' . Escaper::html($this->title) . '</label>' . form::select($this->html_id, array_flip($values), $this->okt->module('Contact')->aPostedData[$this->id]) . '</p>';
				break;
			
			# Boutons radio
			case 4:
				$values = array_filter((array) unserialize($this->value));
				
				$str = '';
				foreach ($values as $k => $v)
				{
					$str .= '<li><label>' . form::radio(array(
						$this->html_id,
						$this->html_id . '_' . $k
					), $k, ($k == $this->okt->module('Contact')->aPostedData[$this->id])) . Escaper::html($v) . '</label></li>';
				}
				
				$return = '<p class="field" id="' . $this->html_id . '-wrapper">' . '<span class="fake-label">' . Escaper::html($this->title) . '</span></p>' . '<ul class="radiolist">' . $str . '</ul>';
				break;
			
			# Cases à cocher
			case 5:
				$values = array_filter((array) unserialize($this->value));
				
				$str = '';
				foreach ($values as $k => $v)
				{
					$str .= '<li><label>' . form::checkbox(array(
						$this->html_id . '[' . $k . ']',
						$this->html_id . '_' . $k
					), $k, in_array($k, $this->okt->module('Contact')->aPostedData[$this->id])) . Escaper::html($v) . '</label></li>';
				}
				
				$return = '<p class="field" id="' . $this->html_id . '-wrapper">' . '<span class="fake-label">' . Escaper::html($this->title) . '</span></p>' . '<ul class="checkboxlist">' . $str . '</ul>';
				break;
		}
		
		return $return;
	}
}
