<?php
/**
 * @class form
 * @ingroup okt_classes_libs
 * @brief Création d'éléments de formulaire HTML.
 *
 */

class form
{
	/**
	 * Retourne l'identifiant et le nom du champ en
	 * fonction des paramètres passés en argument.
	 */
	private static function getNameAndId($nid,&$name,&$id)
	{
		if (is_array($nid))
		{
			$name = $nid[0];
			$id = !empty($nid[1]) ? $nid[1] : null;
		}
		else {
			$name = $id = $nid;
		}
	}

	/**
	 * Retourne un champ de formulaire de type select.
	 *
	 * @param 	mixed	$nid			Le nom et l'identifiant du champ
	 * @param	array	$data			Le tableau contenant les lignes d'option du select
	 * @param	mixed	$default		La valeur sélectionnée par défaut
	 * @param	string	$class			La classe CSS de l'élément
	 * @param	integer	$tabindex		Le tabindex de l'élément
	 * @param	boolean	$disable		Désactiver ou non le champ
	 * @param	string	$extra_html		Du HTML en plus à mettre dans l'élément
	 * @return string
	 */
	public static function select($nid, $data ,$default='', $class='', $tabindex='', $disabled=false, $extra_html='')
	{
		self::getNameAndId($nid,$name,$id);

		$res = '<select name="'.$name.'"';
		$res .= $id ? ' id="'.$id.'"' : '';
		$res .= ' class="select'.($class ? ' '.$class : '').'"';
		$res .= $tabindex ? ' tabindex="'.$tabindex.'"' : '';
		$res .= $disabled ? ' disabled="disabled"' : '';
		$res .= $extra_html;
		$res .= '>'.PHP_EOL;

		$res .= self::selectOptions($data,$default);

		$res .= '</select>'.PHP_EOL;

		return $res;
	}

	/**
	 * Retourne les options d'un élément select.
	 *
	 * @param	array	$data			Le tableau contenant les lignes d'option du select
	 * @param	mixed	$default		La valeur sélectionnée par défaut
	 * @return string
	 */
	public static function selectOptions($data,$default)
	{
		$res = '';
		$option = '<option value="%1$s"%3$s>%2$s</option>'.PHP_EOL;
		$optgroup = '<optgroup label="%1$s">'.PHP_EOL.'%2$s'."</optgroup>\n";

		foreach ($data as $k => $v)
		{
			if (is_array($v)) {
				$res .= sprintf($optgroup,$k,self::selectOptions($v,$default));
			}
			elseif ($v instanceof formSelectOption) {
				$res .= $v->render($default);
			}
			else {
				$s = ($v == $default) ? ' selected="selected"' : '';
				$res .= sprintf($option,$v,$k,$s);
			}
		}

		return $res;
	}

	/**
	 * Retourne un champ de formulaire de type radio.
	 *
	 * @param 	mixed	$nid			Le nom et l'identifiant du champ
	 * @param	mixed	$value			La valeur de l'élément
	 * @param	boolean	$checked		L'état par défaut de l'élément
	 * @param	string	$class			La classe CSS de l'élément
	 * @param	integer	$tabindex		Le tabindex de l'élément
	 * @param	boolean	$disable		Désactiver ou non le champ
	 * @param	string	$extra_html		Du HTML en plus à mettre dans l'élément
	 * @return string
	 */
	public static function radio($nid, $value, $checked='', $class='', $tabindex='', $disabled=false, $extra_html='')
	{
		self::getNameAndId($nid,$name,$id);

		$res = '<input type="radio" name="'.$name.'" value="'.$value.'"';
		$res .= $id ? ' id="'.$id.'"' : '';
		$res .= $checked ? ' checked="checked"' : '';
		$res .= ' class="radio'.($class ? ' '.$class : '').'"';
		$res .= $tabindex ? ' tabindex="'.$tabindex.'"' : '';
		$res .= $disabled ? ' disabled="disabled"' : '';
		$res .= $extra_html;
		$res .= ' />'.PHP_EOL;

		return $res;
	}

	/**
	 * Retourne un champ de formulaire de type checkbox.
	 *
	 * @param 	mixed	$nid			Le nom et l'identifiant du champ
	 * @param	mixed	$value			La valeur de l'élément
	 * @param	boolean	$checked		L'état par défaut de l'élément
	 * @param	string	$class			La classe CSS de l'élément
	 * @param	integer	$tabindex		Le tabindex de l'élément
	 * @param	boolean	$disable		Désactiver ou non le champ
	 * @param	string	$extra_html		Du HTML en plus à mettre dans l'élément
	 * @return string
	 */
	public static function checkbox($nid, $value, $checked='', $class='', $tabindex='', $disabled=false, $extra_html='')
	{
		self::getNameAndId($nid,$name,$id);

		$res = '<input type="checkbox" name="'.$name.'" value="'.$value.'"';
		$res .= $id ? ' id="'.$id.'"' : '';
		$res .= $checked ? ' checked="checked"' : '';
		$res .= ' class="checkbox'.($class ? ' '.$class : '').'"';
		$res .= $tabindex ? ' tabindex="'.$tabindex.'"' : '';
		$res .= $disabled ? ' disabled="disabled"' : '';
		$res .= $extra_html;
		$res .= ' />'.PHP_EOL;

		return $res;
	}

	/**
	 * Retourne un champ de formulaire de type text.
	 *
	 * @param 	mixed	$nid			Le nom et l'identifiant du champ
	 * @param	integer	$size			La taille de l'élément en nombre de caractères
	 * @param	integer	$max			Le nombre maximum de caractères
	 * @param	string	$default		La valeur par défaut de lélément
	 * @param	string	$class			La classe CSS de l'élément
	 * @param	integer	$tabindex		Le tabindex de l'élément
	 * @param	boolean	$disable		Désactiver ou non le champ
	 * @param	string	$extra_html		Du HTML en plus à mettre dans l'élément
	 * @return string
	 */
	public static function text($nid, $size, $max, $default='', $class='', $tabindex='', $disabled=false, $extra_html='')
	{
		self::getNameAndId($nid,$name,$id);

		$res = '<input type="text" size="'.$size.'" name="'.$name.'"';
		$res .= $id ? ' id="'.$id.'"' : '';
		$res .= $max ? ' maxlength="'.$max.'"' : '';
		$res .= $default || $default === '0' ? ' value="'.$default.'"' : '';
		$res .= ' class="text'.($class ? ' '.$class : '').'"';
		$res .= $tabindex ? ' tabindex="'.$tabindex.'"' : '';
		$res .= $disabled ? ' disabled="disabled"' : '';
		$res .= $extra_html;
		$res .= ' />';

		return $res;
	}

	/**
	 * Retourne un champ de formulaire de type file.
	 *
	 * @param 	mixed	$nid			Le nom et l'identifiant du champ
	 * @param	string	$default		La valeur par défaut de lélément
	 * @param	string	$class			La classe CSS de l'élément
	 * @param	integer	$tabindex		Le tabindex de l'élément
	 * @param	boolean	$disable		Désactiver ou non le champ
	 * @param	string	$extra_html		Du HTML en plus à mettre dans l'élément
	 * @return string
	 */
	public static function file($nid, $default='', $class='', $tabindex='', $disabled=false, $extra_html='')
	{
		self::getNameAndId($nid,$name,$id);

		$res = '<input type="file" name="'.$name.'"';
		$res .= $id ? ' id="'.$id.'"' : '';
		$res .= $default || $default === '0' ? ' value="'.$default.'"' : '';
		$res .= ' class="file'.($class ? ' '.$class : '').'"';
		$res .= $tabindex ? ' tabindex="'.$tabindex.'"' : '';
		$res .= $disabled ? ' disabled="disabled"' : '';
		$res .= $extra_html;
		$res .= ' />';

		return $res;
	}

	/**
	 * Retourne un champ de formulaire de type password.
	 *
	 * @param 	mixed	$nid			Le nom et l'identifiant du champ
	 * @param	integer	$size			La taille de l'élément en nombre de caractères
	 * @param	integer	$max			Le nombre maximum de caractères
	 * @param	string	$default		La valeur par défaut de lélément
	 * @param	string	$class			La classe CSS de l'élément
	 * @param	integer	$tabindex		Le tabindex de l'élément
	 * @param	boolean	$disable		Désactiver ou non le champ
	 * @param	string	$extra_html		Du HTML en plus à mettre dans l'élément
	 * @return string
	 */
	public static function password($nid, $size, $max, $default='', $class='', $tabindex='', $disabled=false, $extra_html='')
	{
		self::getNameAndId($nid,$name,$id);

		$res = '<input type="password" size="'.$size.'" name="'.$name.'"';
		$res .= $id ? ' id="'.$id.'"' : '';
		$res .= $max ? ' maxlength="'.$max.'"' : '';
		$res .= $default || $default === '0' ? ' value="'.$default.'"' : '';
		$res .= ' class="password'.($class ? ' '.$class : '').'" ';
		$res .= $tabindex ? ' tabindex="'.$tabindex.'"' : '';
		$res .= $disabled ? ' disabled="disabled"' : '';
		$res .= $extra_html;
		$res .= ' />';

		return $res;
	}

	/**
	 * Retourne un champ de formulaire de type textarea.
	 *
	 * @param 	mixed	$nid			Le nom et l'identifiant du champ
	 * @param	integer	$cols			Le nombre de colonnes
	 * @param	integer	$rows			Le nombre de lignes
	 * @param	string	$default		La valeur par défaut de lélément
	 * @param	string	$class			La classe CSS de l'élément
	 * @param	integer	$tabindex		Le tabindex de l'élément
	 * @param	boolean	$disable		Désactiver ou non le champ
	 * @param	string	$extra_html		Du HTML en plus à mettre dans l'élément
	 * @return string
	 */
	public static function textarea($nid, $cols='', $rows='', $default='', $class='', $tabindex='', $disabled=false, $extra_html='')
	{
		self::getNameAndId($nid,$name,$id);

		$res = '<textarea';
		$res .= ($cols != '') ? ' cols="'.$cols.'"' : '';
		$res .= ($rows != '') ? ' rows="'.$rows.'"' : '';
		$res .= ' name="'.$name.'"';
		$res .= $id ? ' id="'.$id.'"' : '';
		$res .= ($tabindex != '') ? ' tabindex="'.$tabindex.'"' : '';
		$res .= ' class="textArea'.($class ? ' '.$class : '').'"';
		$res .= $disabled ? ' disabled="disabled"' : '';
		$res .= $extra_html.'>';
		$res .= $default;
		$res .= '</textarea>';

		return $res;
	}

	/**
	 * Retourne un champ de formulaire de type textarea.
	 *
	 * @param 	mixed	$nid		Le nom et l'identifiant du champ
	 * @param	string	$value		La valeur par de lélément
	 * @return string
	 */
	public static function hidden($nid,$value)
	{
		self::getNameAndId($nid,$name,$id);

		$res = '<input type="hidden" name="'.$name.'" value="'.$value.'" ';
		$res .= $id ? ' id="'.$id.'"' : '';
		$res .= ' />';

		return $res;
	}
}

class formSelectOption
{
	public $name;
	public $value;
	public $class_name;
	public $html;

	private $option = '<option value="%1$s"%3$s>%2$s</option>';

	public function __construct($name,$value,$class_name='',$html='')
	{
		$this->name = $name;
		$this->value = $value;
		$this->class_name = $class_name;
		$this->html = $html;
	}

	public function render($default)
	{
		$attr = $this->html;
		$attr .= $this->class_name ? ' class="'.$this->class_name.'"' : '';

		if ($this->value == $default) {
			$attr .= ' selected="selected"';
		}

		return sprintf($this->option,$this->value,$this->name,$attr).PHP_EOL;
	}
}
?>