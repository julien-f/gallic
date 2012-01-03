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

final class Gallic_TypeCheckerTest_Countable
{
	function count()
	{}
}

////////////////////////////////////////////////////////////////////////////////

final class Gallic_TypeCheckerTest extends GallicTest_Base
{
	private function _factory($expression)
	{
		return Gallic_TypeChecker::compile($expression);
	}

	////////////////////////////////////////

	function evaluateProvider()
	{
		$tmp = array(

			'int[]' =>
			array(
				array(1, false),
				array(array(), false),
				array(array(1), true),
				array(array(1, 'string'), false),
				array(range(1, 10), true),
			),

			'int[1]' =>
			array(
				array(1, false),
				array(array(), false),
				array(array(1), true),
				array(array(1, 1), false),
			),

			'int[0..]' =>
			array(
				array(1, false),
				array(array(), true),
				array(array(1), true),
				array(array(1, 1), true),
			),

			'int[0..2]' =>
			array(
				array(1, false),
				array(array(), true),
				array(array(1), true),
				array(array(1, 1), true),
				array(array(1, 1, 1), false),
			),

			'int[..2]' =>
			array(
				array(1, false),
				array(array(), false),
				array(array(1), true),
				array(array(1, 1), true),
				array(array(1, 1, 1), false),
			),
		);

		$data = array();
		foreach ($tmp as $pattern => $entries)
		{
			$tc = self::_factory($pattern);
			$i = 0;
			foreach ($entries as $entry)
			{
				$data[$pattern.' - '.($i++)] = array($tc, $entry[0], $entry[1]);
			}
		}

		return $data;
	}

	/**
	 * @covers Gallic_TypeChecker::_list
	 * @covers Gallic_TypeChecker_List
	 * @covers Gallic_TypeChecker::_or
	 * @covers Gallic_TypeChecker_Or
	 * @covers Gallic_TypeChecker::_array
	 * @covers Gallic_TypeChecker_Array
	 * @covers Gallic_TypeChecker::_term
	 * @covers Gallic_TypeChecker::_type
	 * @covers Gallic_TypeChecker_Type
	 *
	 * @dataProvider evaluateProvider
	 */
	function testEvaluate(Gallic_TypeChecker $tc, $value, $result)
	{
		$this->assertSame($result, $tc->evaluate($value));
	}
}
