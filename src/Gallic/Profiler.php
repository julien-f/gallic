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
final class Gallic_Profiler
{
	function __construct()
	{}

	/**
	 * @param string $desc
	 */
	function start($desc)
	{
		assert('$this->_current === null');

		$this->_max_length = max($this->_max_length, strlen($desc));

		$this->_current = count($this->_runs);
		$this->_runs[$this->_current] = array(
			'desc' => $desc,
			'time' => microtime(true)
		);
	}

	function stop()
	{
		assert('$this->_current !== null');

		$time = &$this->_runs[$this->_current]['time'];
		$time = microtime(true) - $time;

		if (($this->_min === null) || ($time < $this->_min))
		{
			$this->_min = $time;
		}

		$this->_current = null;
	}

	function present()
	{
		assert('$this->_current === null');

		foreach ($this->_runs as $run)
		{
			echo
				str_pad($run['desc'], $this->_max_length), '  |  ',
				round($run['time']/$this->_min, 2), PHP_EOL;
		}
	}

	private $_current = null;

	private $_max_length = null;

	private $_min = null;

	private $_runs = array();
}
