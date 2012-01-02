<?php
/**
 * This file is a part of Gallic.
 *
 * Gallic is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Gallic is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gallic. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Julien Fontanet <julien.fontanet@isonoe.net>
 * @license http://www.gnu.org/licenses/gpl-3.0-standalone.html GPLv3
 *
 * @package Gallic
 */

/**
 * This base class permits you to easily creates lazily generated properties.
 *
 * All you have to do is inherit from this class and define protected initializer functions.
 *
 * For  instance,  the initializer  for  the  “my_property”  property is  called
 * “_initMyProperty()” (case is non-meaningful).
 */
abstract class Gallic_LazyLoader
{
	/**
	 * @param string $name
	 *
	 * @return mixed
	 *
	 * @throws Gallic_Exception If the property does not exists and no matching
	 *                           loader is found.
	 */
	function __get($name)
	{
		if (!array_key_exists($name, $this->_entries))
		{
			$initializer = self::_getPropertyInitializer($name);

			if (!method_exists($this, $initializer))
			{
				throw new Gallic_Exception('no initializer for: '.$name);
			}

			$this->_entries[$name] = $this->$initializer();
		}

		return $this->_entries[$name];
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	private static function _getPropertyInitializer($name)
	{
		return '_init'.str_replace('_', '', $name);
	}

	/**
	 * @var array
	 */
	private $_entries = array();
}
