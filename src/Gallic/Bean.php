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
 *
 */
abstract class Gallic_Bean
{
	/**
	 *
	 */
	final function __construct(Gallic_Manager $manager)
	{
		$class = get_class($this);
		if (!isset(self::$_props[$class]))
		{
			self::_init($class);
		}

		$this->_manager = $manager;
	}

	/**
	 *
	 */
	final function __get($name)
	{
		$class = get_class($this);

		if (!isset(self::$_props[$class][$name]['get']))
		{
			trigger_error(
				'no such readable property '.$class.'->'.$name,
				E_USER_ERROR
			);
		}

		if (!isset($this->_attrs[$name])
		    && !array_key_exists($name, $this->_attrs))
		{
			trigger_error(
				'undefined value for '.$class.'->'.$name,
				E_USER_ERROR
			);
		}

		return $this->_attrs[$name];
	}

	/**
	 *
	 */
	final function __set($name, $value)
	{
		
	}

    /**
     * Note: Do not forget to call the parent class method to declare its
     *     properties!
     *
     * For each property the returned array has an entry for which the property
     * name is the key and which has one or more of the following entries:
     * - “get”: boolean indicating whether or not this property is readable;
     * - “getter”: callable called when the property is read (implies “get”);
     * - “set”: boolean indicating whether or not this property is writable;
     * - “setter”: callable called when the property is written (implies “set”);
     * - “attr”: attribute to read/write if not getter/setter defined.
     *
     * @param string $class The name of the class from wich this method was
     *     called (≈ late static binding).
     *
     * @return array
     */
    protected static function _getProperties($class)
    {
        return array();
    }

	/**
	 * @var array
	 */
	static private $_props;

	/**
	 *
	 */
	private static function _init($class)
	{
		$props = array();

		foreach (
			call_user_func($class, '_getProperties')
			as
			$name => $prop
		)
		{
			if (isset($prop['getter']))
			{
				if (!is_callable(array($this, $prop['getter'])))
				{
					trigger_error(
						'invalid getter for '.$class.'->'.$name,
						E_USER_ERROR
					);
				}

				$prop['get'] = true; // Implied
			}

			if (isset($prop['setter']))
			{
				if (!is_callable(array($this, $prop['setter'])))
				{
					trigger_error(
						'invalid setter for '.$class.'->'.$name,
						E_USER_ERROR
					);
				}

				$prop['set'] = true; // Implied
			}

			if (!isset($prop['get']) && !isset($prop['set']))
			{
				trigger_error(
					'property neither readable nor writable '.$class.'->'.$name,
					E_USER_ERROR
				);
			}

			$props[$name] = $prop;
		}

		self::$_props[$class] = $props;
	}

	/**
	 *
	 */
	private $_attrs;

	/**
	 * @var Gallic_Manager
	 */
	private $_manager;
}