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

		$this->_current = count($this->_runs);
		$this->_runs[$this->_current] = array(
			'desc' => $desc,
			'memory' => 0,
			'time' => 0.0,
		);

		// Avoids polluting memory usage as much as possible.
		unset($desc);

		$this->_runs[$this->_current]['memory'] = memory_get_usage();
		$this->_runs[$this->_current]['time']   = microtime(true);
	}

	function stop()
	{
		$memory = memory_get_usage();
		$time   = microtime(true);

		assert('$this->_current !== null');

		$c = &$this->_runs[$this->_current];
		$c['time']   = $time - $c['time'];
		$c['memory'] = $memory - $c['memory'];

		if (($this->_min === null) || ($c['time'] < $this->_min))
		{
			$this->_min = $c['time'];
		}

		$this->_current = null;
	}

	function present()
	{
		assert('$this->_current === null');

		$header = array(
			'desc' => '',
			'memory' => 'Memory (bytes)',
			'time' => 'Time (% / fastest)'
		);
		$paddings = array(
			'desc' => STR_PAD_RIGHT,
			'memory' => STR_PAD_LEFT,
			'time' => STR_PAD_LEFT
		);

		$lengths = array_map('strlen', $header);;
		foreach ($this->_runs as &$run)
		{
			$run['time'] = round($run['time'] * 100 / $this->_min);

			foreach ($lengths as $key => &$value)
			{
				if (($len = strlen($run[$key])) > $value)
				{
					$value = $len;
				}
			}
		}
		unset($value, $run);

		$col_sep = '│';
		$row_sep = '─';
		$cross   = '┼';
		$padding = ' ';

		$cols    = count($lengths);
		$col_sep = $padding.$col_sep.$padding;
		$cross   = str_repeat($row_sep, strlen($padding)).$cross.str_repeat($row_sep, strlen($padding));
		$row_len = array_sum($lengths) + ($cols - 1) * strlen($col_sep);
		$row_sep = implode($cross, array_map('str_repeat', array_fill(0, $cols, $row_sep), $lengths));

		$keys = array_keys($header);

		echo
			implode($col_sep, array_map('str_pad', $header, $lengths)), PHP_EOL,
			implode('━┿━', array_map('str_repeat', array_fill(0, $cols, '━'), $lengths)), PHP_EOL;
		foreach ($this->_runs as $run)
		{
			echo
				implode(
					$col_sep,
					array_map(
						'str_pad',
						$run,
						$lengths,
						array_fill(0, $cols,' '),
						$paddings
					)
				), PHP_EOL,
				$row_sep, PHP_EOL;
		}
	}

	private $_current = null;

	private $_min = null;

	private $_runs = array();
}
