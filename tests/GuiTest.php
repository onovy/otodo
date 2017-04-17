<?php
/*
Copyright 2014-2017 Ondrej Novy

This file is part of otodo.

otodo is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

otodo is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with otodo.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once 'init.php';

class GuiTest extends PHPUnit_Framework_TestCase {
	public function testParseDate() {
		Config::loadFile('../config.ini');

		$gui = new Gui();

		$ret = invokeMethod($gui, 'parseDate', array('1.2.2014'))->format('Y-m-d');
		$this->assertEquals($ret, '2014-02-01');

		$ret = invokeMethod($gui, 'parseDate', array('1. 2. 2014'))->format('Y-m-d');
		$this->assertEquals($ret, '2014-02-01');

		$ret = invokeMethod($gui, 'parseDate', array('2014-02-01'))->format('Y-m-d');
		$this->assertEquals($ret, '2014-02-01');

		$ret = invokeMethod($gui, 'parseDate', array(''))->format('Y-m-d');
		$this->assertEquals($ret, date('Y-m-d'));

		$dt = new DateTime('today');
		$dt->modify('+2 day');
		$ret = invokeMethod($gui, 'parseDate', array('+2'))->format('Y-m-d');
		$this->assertEquals($ret, $dt->format('Y-m-d'));

		$dt = new DateTime('today');
		$dt->modify('+2 day');
		$ret = invokeMethod($gui, 'parseDate', array('+2d'))->format('Y-m-d');
		$this->assertEquals($ret, $dt->format('Y-m-d'));

		$dt = new DateTime('today');
		$dt->modify('+2 month');
		$ret = invokeMethod($gui, 'parseDate', array('+2m'))->format('Y-m-d');
		$this->assertEquals($ret, $dt->format('Y-m-d'));

		$dt = new DateTime('today');
		$dt->modify('+2 week');
		$ret = invokeMethod($gui, 'parseDate', array('+2w'))->format('Y-m-d');
		$this->assertEquals($ret, $dt->format('Y-m-d'));

		$dt = new DateTime('today');
		$dt->modify('+2 year');
		$ret = invokeMethod($gui, 'parseDate', array('+2y'))->format('Y-m-d');
		$this->assertEquals($ret, $dt->format('Y-m-d'));
	}

	public function testParseSortString() {
		Config::loadFile('../config.ini');

		$gui = new Gui();

		$ret = invokeMethod($gui, 'parseSortString', array('a'));
		$this->assertEquals($ret, array(
			'a' => true,
		));

		$ret = invokeMethod($gui, 'parseSortString', array('!a'));
		$this->assertEquals($ret, array(
			'a' => false,
		));

		$ret = invokeMethod($gui, 'parseSortString', array('a,!b,c'));
		$this->assertEquals($ret, array(
			'a' => true,
			'b' => false,
			'c' => true
		));
	}
}
