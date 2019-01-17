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

class TodosExTest extends \PHPUnit\Framework\TestCase {
	public function testInOut() {
		$ts = new Todos();
		$ts->loadFromFile('todo.txt');
		$file = tempnam(sys_get_temp_dir(), 'otodo');
		$ts->saveToFile($file);
		$this->assertEquals(sha1_file('todo.txt'), sha1_file($file));
		unlink($file);
	}

	public function testSort() {
		$ts = new TodosEx();
		$ts->loadFromFile('todo.txt');
		$ts->sort(array(
			'due' => true,
		));
		$ids = array();
		foreach ($ts as $t) {
			$ids[] = $t->id;
		}
		$this->assertEquals($ids, array(9, 3, 8, 10, 0, 1, 2, 4, 5, 6, 7));
	}

	public function testSortPersistent() {
		$ts = new TodosEx();
		$ts->loadFromFile('todo.txt');
		$file = tempnam(sys_get_temp_dir(), 'otodo');
		$ts->sort(array(
			'done' => true,
			'due' => true,
			'priority' => false,
			'text' => false,
		));
		$ts->saveToFile($file);
		$this->assertEquals(sha1_file('todo.txt'), sha1_file($file));
		unlink($file);
	}

	public function testASortPersistent() {
		$ts = new TodosEx();
		$ts->loadFromFile('todo.txt');
		$file = tempnam(sys_get_temp_dir(), 'otodo');
		$ts->asort(array(
			'done' => true,
			'due' => true,
			'priority' => false,
			'text' => false,
		));
		$ts->saveToFile($file);
		$this->assertEquals(sha1_file('todo.txt'), sha1_file($file));
		unlink($file);
	}
}
