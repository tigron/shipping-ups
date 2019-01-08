<?php
/**
 * Util class
 *
 * @author Lionel Laffineur <lionel@tigron.be>
 */
namespace Tigron\Ups;

class Util {

	/**
	 * Replace characters not supported by UPS for labels printing
	 *
	 * @access public
	 * @param $in
	 * @return String
	 */
	public static function replace_unsupported_characters($in) {
		$patterns[ 0] = '/Ć|ć|Č|č/';		$replacements[ 0] = 'C';
		$patterns[ 1] = '/Ę|ę|Ē|ē/';		$replacements[ 1] = 'E';
		$patterns[ 2] = '/Ł|ł|Ļ|ļ/';		$replacements[ 2] = 'L';
		$patterns[ 3] = '/Ń|ń|Ņ|ņ/';		$replacements[ 3] = 'N';
		$patterns[ 4] = '/Ó|ó]/';			$replacements[ 4] = 'O';
		$patterns[ 5] = '/Ś|ś|Š|š/';		$replacements[ 5] = 'S';
		$patterns[ 6] = '/Ź|ź|Ż|ż|Ž|ž/';	$replacements[ 6] = 'Z';
		$patterns[ 7] = '/Ā|ā/';			$replacements[ 7] = 'A';
		$patterns[ 8] = '/Ģ|ģ/';			$replacements[ 8] = 'G';
		$patterns[ 9] = '/Ī|ī/';			$replacements[ 9] = 'I';
		$patterns[10] = '/Ķ|ķ/';			$replacements[10] = 'K';
		$patterns[11] = '/Ū|ū/';			$replacements[11] = 'U';
		return preg_replace($patterns, $replacements, $in);
	}
}
