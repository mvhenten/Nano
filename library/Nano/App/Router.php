<?php
/**
 * @file Nano/App/Router.php
 *
 * A simple request router that matches a series of regexes agains uri patterns
 * and returns the results of the first match.
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
 * @package    Nano_Log
 * @copyright  Copyright (c) 2011 Ischen (http://ischen.nl)
 * @license    GPL v3
 */
/**
 * @class Nano_App_Router
 * Routing pattenrs can be defined by a subset of regexes.
 * Currently only \w and \d are supported (whitelisted)
 *
 * <code>
 * my $router = new Nano_App_Router(array(
 *      '/url/pattern/to' => 'Handler_One',
 *      '/url/\w+/\d+/?'  => 'Handler_Tow',
 * ));
 *
 * $router->addRoute( '/foo/bar/\d+/', 'Handler_Class' );
 *
 * list( $handler, $matches, $pattern ) = $router->getRoute( $url );
 * </code>
 */
class Nano_App_Router {

    private $_routes = array();
    private $_whitelist = array(
        '\w' => '\\\w', '\d' => '\\\d',
        '+'  => '\+', '?'  => '\?',
        '('  => '\(',  ')'  => '\)',
        '.' => '\\.'
    );

    public function __construct( $routes ){
        foreach( $routes as $key => $value ){
            $this->addRoute( $key, $value );
        }
    }

    public function addRoute( $pattern, $handler ){
        $pattern = preg_quote($pattern, '/');
        $this->_routes[$pattern] = $handler;
    }

    public function getRoute( $url ){
        foreach( $this->_routes as $pattern => $handler ){
            $pattern = str_replace(
                array_values( $this->_whitelist ),
                array_keys( $this->_whitelist ),
                $pattern
            );

            if( preg_match( "/^$pattern$/", $url, $matches ) ){
                $match = array_shift($matches);
                return array( $handler, $matches, $match );
            }
        }
    }
}
