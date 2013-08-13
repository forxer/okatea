<?php
/**
 * @ingroup okt_module_users
 * @brief Custom fields recordset.
 *
 */

class usersFieldRecordset extends recordset
{
	/**
	 * L'objet oktCore
	 * @access private
	 * @var object
	 */
	protected $okt;

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

	public function getOptions()
	{
		return (array)unserialize($this->options);
	}

	public function isSimpleField()
	{
		if ($this->type == 1 || $this->type == 2) {
			return true;
		}

		return false;
	}

	public function getHtmlField($aPostedData)
	{
		$return = '';

		switch($this->type)
		{
			# Champ texte
			default:
			case 1 :
				$return = '<p class="field" id="'.$this->html_id.'-wrapper">'.
				'<label for="'.$this->html_id.'"'.
				($this->status == 2 ? ' class="required" title="'.__('c_c_required_field').'"' : '').'>'.
				html::escapeHTML($this->title).'</label>'.
				form::text($this->html_id, 60, 255, $aPostedData[$this->id]).'</p>';
			break;

			# Zone de texte
			case 2 :
				$return = '<p class="field" id="'.$this->html_id.'-wrapper">'.
				'<label for="'.$this->html_id.'"'.
				($this->status == 2 ? ' class="required" title="'.__('c_c_required_field').'"' : '').'>'.
				html::escapeHTML($this->title).'</label>'.
				form::textarea($this->html_id, 58, 10, $aPostedData[$this->id]).'</p>';
			break;

			# Menu déroulant
			case 3 :
				$values = array_filter((array)unserialize($this->value));

				$return = '<p class="field" id="'.$this->html_id.'-wrapper">'.
				'<label for="'.$this->html_id.'"'.
				($this->status == 2 ? ' class="required" title="'.__('c_c_required_field').'"' : '').'>'.
				html::escapeHTML($this->title).'</label>'.
				form::select($this->html_id, array_flip($values), $aPostedData[$this->id]).'</p>';
			break;

			# Boutons radio
			case 4 :
				$values = array_filter((array)unserialize($this->value));

				$str = '';
				foreach ($values as $k=>$v)
				{
					$str .= '<li><label>'.form::radio(array($this->html_id,$this->html_id.'_'.$k), $k, ($k == $aPostedData[$this->id])).
					html::escapeHTML($v).'</label></li>';
				}

				$return = '<p class="field" id="'.$this->html_id.'-wrapper">'.
				'<span class="fake-label">'.html::escapeHTML($this->title).'</span></p>'.
				'<ul class="checklist">'.$str.'</ul>';
			break;

			# Cases à cocher
			case 5 :
				$values = array_filter((array)unserialize($this->value));

				$str = '';
				foreach ($values as $k=>$v) {
					$str .= '<li><label>'.form::checkbox(array($this->html_id.'['.$k.']',$this->html_id.'_'.$k), $k, in_array($k,$aPostedData[$this->id])).
					html::escapeHTML($v).'</label></li>';
				}

				$return = '<p class="field" id="'.$this->html_id.'-wrapper">'.
				'<span class="fake-label">'.html::escapeHTML($this->title).'</span></p>'.
			'<ul class="checklist">'.$str.'</ul>';
			break;
		}

		return $return;
	}

} # class
