<?php
/**
 * @file Nano/Log.php
 *
 * Trivial wrapper around error_log that pretty-prints your print_r
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
 * @category   Nano
 * @package    Nano_Gd
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 */

//logs go to /tmp/pico-error.log by default
if( ! defined('PICO_ERROR_LOG') ){
    define( 'PICO_ERROR_LOG',  '/tmp/pico-error.log' );
}

/**
 * @class Nano_Log
 *
 * Trivial wrapper around error_log
 */
class Nano_Log {
    /**
     * Print an error to error_log
     *
     * @param mixed $str    Variable to print
     * @param string $label Optional label
     */
    public static function error( $str, $key = 'E' ){
        $date  = date('d-M-Y H:s:i');
        $lines = explode("\n",  print_r($str, true) );

        foreach( $lines as $value ){
            error_log( "[$date] $key: $value\n", 3, PICO_ERROR_LOG );
        }
    }
}
