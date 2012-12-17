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
 * @experimental
 *
 * Inspired by http://www.pluf.org/doc/template.html
 */
abstract class Gallic_Template
{
	public $variables = array();
	public $filters   = array();
	public $functions = array();

	final function __construct()
	{}

	final function render(array $variables = null)
	{
		($variables === null)
			and $variables = $this->variables;

		return $this->_render($variables, $this->filters, $this->functions);
	}

	abstract protected function _render(array $variables, array $filters, array $functions);
}
