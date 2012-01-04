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
 * This class provides a way to delay an object construction until the necessary
 * constructor's arguments are known.
 *
 * This is usefull  when using PDO (instead of  the plain PDO::FETCH_CLASS which
 * ignore the constructor) Soap.
 *
 * PDO example:
 *
 *     $dc = Gallic_DelayedConstructor::create('MyClass');
 *     $stmt = $pdo->query('SELECT * FROM table', PDO_FETCH_INTO, $dc);
 *     $results = array();
 *     while ($stmt->fetch())
 *     {
 *       $results[] = $dc->build();
 *       $dc->reset();
 *     }
 */
final class Gallic_DelayedConstructor
{
	static function create($class, $only_required = true)
	{
		$ctor_params = array();
		$c = new ReflectionClass($class);
		$ctor = $c->getConstructor();
		foreach ($ctor->getParameters() as $param)
		{
			if ($only_required && $param->isOptional())
			{
				// No more required parameters.
				break;
			}

			$ctor_params[] = $param->getName();
		}

		return new self($class, $ctor_params);
	}


	function __construct($class, array $ctor_params)
	{
		$this->_class = $class;
		$this->_ctor_params = array_flip($ctor_params);
		$this->reset();
	}

	function __set($name, $value)
	{
		if (isset($this->_ctor_params[$name]))
		{
			$this->_ctor_args[$this->_ctor_params[$name]] = $value;
			return;
		}

		$this->_props[$name] = $value;
	}

	function build()
	{
		$n = count($this->_ctor_args);

		if ($n < count($this->_ctor_params))
		{
			// Not enough constructor arguments.
			return false;
		}

		if ($n === 0)
		{
			$class = $this->_class;
			$o = new $class;
		}
		else
		{
			$class = new ReflectionClass($this->_class);
			ksort($this->_ctor_args);
			$o = $class->newInstanceArgs($this->_ctor_args);
		}

		foreach ($this->_props as $name => $value)
		{
			$o->$name = $value;
		}

		return $o;
	}

	function reset()
	{
		$this->_ctor_args = $this->_props = array();
	}

	function set(array $properties)
	{
		foreach ($properties as $name => $value)
		{
			$this->__set($name, $value);
		}
	}

	/**
	 * Name of the class of the object to construct.
	 *
	 * @var string
	 */
	private $_class;

	/**
	 * Constructor arguments.
	 *
	 * @var array
	 */
	private $_ctor_args;

	/**
	 * Indexes of the constructor parameters indexed by their names.
	 *
	 * @var integer[string]
	 */
	private $_ctor_params;

	/**
	 * Additional properties.
	 *
	 * @var mixed[string]
	 */
	private $_props;
}
