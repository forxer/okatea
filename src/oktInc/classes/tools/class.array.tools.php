<?php
/**
 * @class imageTools
 * @ingroup okt_classes_tools
 * @brief Outil pour le redimensionnement des images.
 *
 */


class arrayTools
{
	public static $aElem = array();

	public static function array2object($object, array $array) {
		foreach($array as $key => $value) {
				$object->{$key} = $value;
		}
		return $object;
	}

	public static function array_search_property($needle, $haystack, $property, $strict=false){
		for($i=0; $i<count($haystack);$i++){
			if($strict){
				if($haystack[$i][$property]===$needle) return $i;
			}else{
				if($haystack[$i][$property]==$needle) return $i;
			}
		}
		return false;
	}
	
	private static function kcmp($a, $b){
        return strcmp(array_search($a, self::$aElem), array_search($b, self::$aElem));
    }
    
    public static function multi_ksort($arr1, $arr2){
		self::$aElem = array_keys($arr2);
        uksort($arr1, array("self", "kcmp"));
        return $arr1; 
    }
}
?>