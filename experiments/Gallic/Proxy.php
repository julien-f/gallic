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
class Gallic_Proxy implements ArrayAccess, Countable, IteratorAggregate
{
	/**
	 * @param callback $cb
	 */
	function __construct($cb)
	{
		$this->_cb = $cb;
	}

	function __call($name, $arguments)
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	function __clone()
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	function __get($name)
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	function __invoke()
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	function __isset($name)
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	function __set($name, $value)
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	function __toString()
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	function __unset($name)
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	////////////////////////////////////////
	// Countable

	function count()
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	////////////////////////////////////////
	// IteratorAggregate

	function getIterator()
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	////////////////////////////////////////
	// ArrayAccess

	function offsetExists($name)
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	function offsetGet($name)
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	function offsetSet($name, $value)
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	function offsetUnset($name)
	{
		$args = func_get_args();
		return call_user_func($this->_cb, __FUNCTION__, $args);
	}

	private $_cb;
}
