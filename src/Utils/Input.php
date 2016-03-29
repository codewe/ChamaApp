<?php
namespace Chama\Utils;

/**
 * Input
 * A wrapper class to get input values
 *
 * @author    James Ngugi <ngugi823@gmail.com>
 */
class Input {
	/**
	 * This loks for the data in the GET array, then the POST array
	 */
	public static function get($value, $default = null) {
		$out = "";
		if (isset($_POST[$value])) {
			$out = $_POST[$value];
		} elseif (isset($_GET[$value])) {
			$out = $_GET[$value];
		} else {
			$out = $default;
		}
		return $out;
	}
}