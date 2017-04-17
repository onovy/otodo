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

class TodosTest extends PHPUnit_Framework_TestCase {
	public function testInOut() {
		$ts = new Todos();
		$ts->loadFromFile('todo.txt');
		$file = tempnam(sys_get_temp_dir(), 'otodo');
		$ts->saveToFile($file);
		$this->assertEquals(sha1_file('todo.txt'), sha1_file($file));
		unlink($file);
	}

	/**
	 * @expectedException TodosLoadException
	 */
	public function testLoadFail() {
		$ts = new Todos();
		$ts->loadFromFile('dummy file not exists');
	}

	public function testSort() {
		$ts = new Todos();
		$ts->loadFromFile('todo.txt');
		$ts->sort(array(
			'done' => true,
			'doneDate' => true,
			'priority' => false,
			'text' => false,
		));
		$ids = array();
		foreach ($ts as $t) {
			$ids[] = $t->id;
		}
		$this->assertEquals($ids, array(4, 1, 3, 10, 9, 0, 2, 8, 7, 6, 5));
	}

	public function testSortDefault() {
		$ts = new Todos();
		$ts->loadFromFile('todo.txt');
		$ts->sort(array());
		$ids = array();
		foreach ($ts as $t) {
			$ids[] = $t->id;
		}
		$this->assertEquals($ids, array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10));
	}

	public function testSortPersistent() {
		$ts = new Todos();
		$ts->loadFromFile('todo.txt');
		$file = tempnam(sys_get_temp_dir(), 'otodo');
		$ts->sort(array(
			'done' => true,
			'priority' => false,
			'text' => false,
		));
		$ts->saveToFile($file);
		$this->assertEquals(sha1_file('todo.txt'), sha1_file($file));
		unlink($file);
	}

	/**
	 * @expectedException UnknownSortingParamException
	 */
	public function testSortWrong() {
		$ts = new Todos();
		$ts->loadFromFile('todo.txt');
		$ts->sort(array(
			'wrong' => true,
		));
	}

	public function testASortPersistent() {
		$ts = new Todos();
		$ts->loadFromFile('todo.txt');
		$file = tempnam(sys_get_temp_dir(), 'otodo');
		$ts->asort(array(
			'done' => true,
			'priority' => false,
			'text' => false,
		));
		$ts->saveToFile($file);
		$this->assertEquals(sha1_file('todo.txt'), sha1_file($file));
		unlink($file);
	}

	public function testASortDefault() {
		$ts = new Todos();
		$ts->loadFromFile('todo.txt');
		$ts->asort(array());
		$ids = array();
		foreach ($ts as $t) {
			$ids[] = $t->id;
		}
		$this->assertEquals($ids, array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10));
	}

	public function testArchive() {
		$ts = new Todos();
		$ts->loadFromFile('todo.txt');

		$tmpArchive = tempnam(sys_get_temp_dir(), 'otodo');
		$count = $ts->archive($tmpArchive);
		$this->assertEquals($count, 8);

		$tsArchive = new Todos();
		$tsArchive->loadFromFile($tmpArchive);

		$this->assertEquals($ts->count(), 3);
		$this->assertEquals($tsArchive->count(), 8);

		unlink($tmpArchive);

		$count = $ts->archive($tmpArchive);
		$this->assertEquals($count, 0);

		unlink($tmpArchive);
	}

	public function testSpl() {
		$ts = new Todos();
		$ts->loadFromFile('todo.txt');

		// ArrayAccess
		$this->assertEquals($ts[0]->generateString(), 'x 2014-01-01 (C) dummy8');
		$this->assertTrue(isset($ts[0]));
		unset($ts[0]);
		$this->assertFalse(isset($ts[0]));
		$t = new Todo(null);
		$ts[0] = $t;
		$this->assertEquals($ts[0], $t);

		// Iterator
		$count = 0;
		foreach ($ts as $k=>$t) {
			$count++;
			$this->assertTrue($t instanceof Todo);
		}
		$this->assertEquals($count, 11);

		// Countable
		$this->assertEquals(count($ts), 11);
	}

	public function testSearch() {
		Config::loadFile('../config.ini');

		$ts = new Todos();
		$ts->loadFromFile('todo.txt');

		$t = new Todo(null, 'dummy');
		$this->assertNotNull($ts->searchSimilar($t));

		$t = new Todo(null, 'different');
		$this->assertNull($ts->searchSimilar($t));

		$t = new Todo(null, '+dummy');
		$this->assertNotNull($ts->searchSimilar($t));
	}

	public function testArrayFilter() {
		$ts = new Todos();
		$ts->loadFromFile('todo.txt');

		$filtered = $ts->array_filter(function($todo) {
			return $todo->text == 'dummy1';
		});
		$this->assertEquals(count($filtered), 1);
	}
}
