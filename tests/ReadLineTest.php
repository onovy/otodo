<?php
/*
Copyright 2014 Ondrej Novy

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

class ReadLineTest extends PHPUnit_Framework_TestCase {
	public function testHistoryInOut() {
		$r = new ReadLine();
		$r->historyAdd('aa');

		$file = tempnam(sys_get_temp_dir(), 'otodo');
		$r->historySave($file);

		$r2 = new ReadLine();
		$r2->historyLoad($file);

		$h = getProperty($r2, 'history');
		$this->assertEquals($h, array('aa'));

		unlink($file);
	}

	public function testHistoryAdd() {
		$r = new ReadLine();

		$r->historyAdd('aa');
		$h = getProperty($r, 'history');
		$this->assertEquals($h, array('aa'));

		$r->historyAdd('bb');
		$h = getProperty($r, 'history');
		$this->assertEquals($h, array('aa', 'bb'));

		$r->historyAdd('bb');
		$h = getProperty($r, 'history');
		$this->assertEquals($h, array('aa', 'bb'));

		$r->historyAdd('');
		$h = getProperty($r, 'history');
		$this->assertEquals($h, array('aa', 'bb'));

		$r->historyAdd('aa');
		$h = getProperty($r, 'history');
		$this->assertEquals($h, array('aa', 'bb', 'aa'));
	}

	public function testHistoryLoadNotExists() {
		$r = new ReadLine();
		$r->historyLoad('not exists');

		$h = getProperty($r, 'history');
		$this->assertEquals($h, array());
	}

	/**
	 * @expectedException HistoryLoadException
	 */
	public function testHistoryLoadWrong() {
		$file = tempnam(sys_get_temp_dir(), 'otodo');

		$r = new ReadLine();
		$r->historyLoad($file);

		unlink($file);
	}

	/**
	 * @expectedException HistoryLoadException
	 */
	public function testHistoryLoadWrong2() {
		$file = tempnam(sys_get_temp_dir(), 'otodo');
		file_put_contents($file, serialize('a'));

		$r = new ReadLine();
		$r->historyLoad($file);

		unlink($file);
	}
}
