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
 * Various type checking.
 */
final class Gallic_Type
{
	/**
	 * Checks whether a value is of a given type.
	 *
	 * @param mixed  $value
	 * @param string $type  Can be a basic  the name of a PHP  type, a meta-type
	 *                      (callback,  callable,   class,  interface,  numeric,
	 *                      object, scalar) or the name of a class/interface.
	 *
	 * @return boolean
	 */
	static function is($value, $type)
	{
		static $validators = array(
			'array'     => '',
			'bool'      => '',
			'boolean'   => 'is_bool',
			'callback'  => 'is_callable',
			'callable'  => '',
			'class'     => 'class_exists',
			'double'    => 'is_float',
			'float'     => '',
			'int'       => '',
			'interface' => 'interface_exists',
			'integer'   => 'is_int',
			'long'      => '',
			'null'      => '',
			'numeric'   => '',
			'object'    => '',
			'real'      => 'is_float',
			'resource'  => '',
			'scalar'    => '',
			'string'    => '',
		);

		if ($type === 'mixed')
		{
			return true;
		}

		if (isset($validators[$type]))
		{
			$function = $validators[$type];

			if ($function === '' ) // Unlikely
			{
				$function = 'is_'.$type;
				$validators[$type] = $function;
			}

			return $function($value);
		}

		return ($value instanceof $type);
	}

	/**
	 * Checks whether a class looks like an interface.
	 *
	 * Duck Typing:
	 *
	 *   When I see a  bird that walks like a duck and swims  like a duck and quacks
	 *   like a duck, I call that bird a duck.
	 *
	 * Note: This method uses a cache system.
	 *
	 * @param string|object $class
	 * @param string        $interface
	 *
	 * @return boolean
	 */
	static function looksLike($class, $interface)
	{
		static $cache = array();

		if (!is_string($class))
		{
			$class = get_class($class);
		}

		$key = $class.' '.$interface;

		if (isset($cache[$key]))
		{
			return $cache[$key];
		}

		if ($class instanceof $interface)
		{
			return ($cache[$key] = true);
		}

		try
		{
			$result = self::_looksLike($class, $interface);
		}
		catch (ReflectionException $e)
		{
			$result = false;
		}

		return ($cache[$key] = $result);
	}

	/**
	 * Checks whether a class looks like an interface.
	 *
	 * @param string $class
	 * @param string $interface
	 *
	 * @return boolean
	 *
	 * @throws ReflectionException Consider that the result is false.
	 */
	private static function _looksLike($class, $interface)
	{
		$class = new ReflectionClass($class);
		$interface = new ReflectionClass($interface);

		$i_methods = $interface->getMethods(ReflectionMethod::IS_PUBLIC);

		foreach ($i_methods as $i_method)
		{
			$c_method = $class->getMethod($i_method->getName());

			/*
			 * - The  class  method   MUST  be  public.
			 *
			 * - If the interface  method is static, the class  method also MUST
			 *   be and vice-versa.
			 *
			 * - The class method MUST have  at most the same number of required
			 *   parameters.
			 */
			if (!(
				$c_method->isPublic()
				&&
				($i_method->isStatic() === $c_method->isStatic())
				&&
				(
					$c_method->getNumberOfRequiredParameters() <=
					$i_method->getNumberOfRequiredParameters()
				)
			))
			{
				return false;
			}
		}

		return true;
	}

	private function __construct()
	{}
}
