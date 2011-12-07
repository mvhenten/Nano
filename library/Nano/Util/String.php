<?php
/**
 * library/Nano/Util/String.php
 *
 * Copyright (C) <2011>  <Matthijs van Henten>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @category   Nano/Util
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 * @package    Nano
 */


/**
 * String utilities, for the sole purpose of keeping them around
 *
 * @class Nano_Util_String
 */
class Nano_Util_String {

	/**
	 * Attempt to "slugify" (nice urls) a string - e.g. attempt to replace/strip
	 * unicode characters outside the good-old ascii range, and replace spaces with dashes
	 *
	 * Borrowed from Symphony, and other sources
	 *
	 * @param string  $text Input string
	 * @param string  $trim (optional) Optionally trim string
	 * @return string $string
	 */
	public static function slugify( $text, $trim=64 ) {
		$text = preg_replace('/[^\\pL\d]+/u', '-', $text);

		$text = trim($text, '-');

		if (function_exists('iconv')) {
			$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		}

		$text = strtolower($text);
		$text = preg_replace('/[^-\w]+/', '', $text);

		if (empty($text)) {
			return 'n-a';
		}

		return substr( $text, 0, $trim);
	}


	/**
	 * Camelize - e.g.
	 * i_know_what_you_did_last_summer => iKnowWhatYouDidLastSummer
	 *
	 * @param string  $string   Your underscores string
	 * @param unknown $extended (optional) Do an "extended" replace, e.g. also work with spaces and dashes
	 * @return string $camelized
	 */
	public function camelize( $string, $extended = false ) {
		if ( $extended ) {
			$pieces = preg_split( '/[\s\-\_]/', $string );
		}
		else {
			$pieces = explode( '_', $string );
		}

		$camels = join( '', array_map( 'ucfirst', $pieces ));

		if ( ! function_exists( 'lcfirst' ) ) {
			strtolower($camels{0}); //deprecated...
		}
		else {
			lcfirst($camels);
		}

		return $camels;
	}


	/**
	 * Dasherize - e.g. from camelized back to dashes (underscores, really)
	 * iKnowWhatYouDidLastSummer => i_know_what_you_did_last_summer
	 *
	 * @param string  $string Your camelized string
	 * @param string  $dash   (optional) Char to use - defaults to underscore
	 * @return string $dashes
	 */
	public static function dasherize( $string, $dash='_' ) {
		$string = preg_replace( '/([[:upper:]])/', '_$1', $string );

		return strtolower( trim( $string, $dash ) );
	}


}
