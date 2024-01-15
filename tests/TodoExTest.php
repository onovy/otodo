<?php
/*
Copyright 2014-2019 Ondrej Novy

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

class TodoExTest extends \PHPUnit\Framework\TestCase {
	public function testInOut() {
		$tests = array(
			'dummy due:2014-02-01',
			'dummy rec:1d',
			'dummy due:2014-02-01 rec:1d',
		);

		foreach ($tests as $test) {
			$t = new TodoEx(null, $test);
			$this->assertEquals($t->generateString(), $test);
		}
	}

	public function testParser() {
		$t = new TodoEx(null, 'dummy due:2014-02-01');
		$this->assertEquals($t->due->format('Y-m-d'), '2014-02-01');
		$this->assertEquals($t->text, 'dummy');
		$this->assertArrayHasKey('due', $t->addons);
		$this->assertEquals($t->addons['due'], '2014-02-01');

		$t = new TodoEx(null, 'dummy rec:1d');
		$this->assertEquals($t->rec->toString(), '1d');
		$this->assertEquals($t->text, 'dummy');
		$this->assertArrayHasKey('rec', $t->addons);
		$this->assertEquals($t->addons['rec'], '1d');

		$t = new TodoEx(null, 'dummy due:2014-02-01 rec:1d');
		$this->assertEquals($t->rec->toString(), '1d');
		$this->assertEquals($t->due->format('Y-m-d'), '2014-02-01');
		$this->assertEquals($t->text, 'dummy');
		$this->assertArrayHasKey('rec', $t->addons);
		$this->assertEquals($t->addons['rec'], '1d');
		$this->assertArrayHasKey('due', $t->addons);
		$this->assertEquals($t->addons['due'], '2014-02-01');
	}

	public function testGenerator() {
		$t = new TodoEx(null);
		$t->due = new DateTime('2014-02-01');
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), 'dummy due:2014-02-01');
		$this->assertArrayHasKey('due', $t->addons);
		$this->assertEquals($t->addons['due'], '2014-02-01');

		$t = new TodoEx(null);
		$t->rec = new Recurrent('1d');
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), 'dummy rec:1d');
		$this->assertArrayHasKey('rec', $t->addons);
		$this->assertEquals($t->addons['rec'], '1d');

		$t = new TodoEx(null);
		$t->rec = new Recurrent('1d');
		$t->due = new DateTime('2014-02-01');
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), 'dummy due:2014-02-01 rec:1d');
		$this->assertArrayHasKey('rec', $t->addons);
		$this->assertEquals($t->addons['rec'], '1d');
		$this->assertArrayHasKey('due', $t->addons);
		$this->assertEquals($t->addons['due'], '2014-02-01');
	}

	public function testMarkDone() {
		$ts = new TodosEx();
		$t = new TodoEx($ts);
		$t->text = 'dummy';
		$t->rec = new Recurrent('+1d');
		$t->due = new DateTime('2014-03-01');
		$t->markDone();
		$this->assertTrue($ts->count() == 1);
		$keys = $ts->array_keys();
		$t = $ts[array_pop($keys)];
		$this->assertFalse($t->done);
		$this->assertEquals($t->text, 'dummy');
		$this->assertEquals($t->rec->toString(), '+1d');
		$this->assertEquals($t->due->format('Y-m-d'), '2014-03-02');

		$t->unmarkDone();
		$this->assertFalse($t->done);
		$this->assertNull($t->doneDate);
	}

	public function testChange() {
		$t = new TodoEx(null, 'x 2014-02-01 (D) 2014-03-01 dummy due:2014-02-01 rec:1d');

		$t->text = 'dummy2';
		$this->assertEquals($t->text, 'dummy2');

		$t->priority = 'B';
		$this->assertEquals($t->priority, 'B');

		$t->priority = null;
		$this->assertEquals($t->priority, null);

		$t->rec = new Recurrent('2d');
		$this->assertEquals($t->rec->toString(), '2d');

		$t->rec = null;
		$this->assertEquals($t->rec, null);

		$t->due = new DateTime('2014-03-01');
		$this->assertEquals($t->due->format('Y-m-d'), '2014-03-01');

		$t->due = null;
		$this->assertEquals($t->due, null);
	}

	public function testWrongSet() {
		$this->expectError();

		$t = new Todo(null);
		$t->notExists = '';
	}

	public function testWrongGet() {
		$this->expectError();

		$t = new Todo(null);
		$t->notExists;
	}

	public function testRecurrentConversion() {
		$t = new TodoEx(null, 'dummy recurrent:1d');
		$this->assertEquals($t->rec->toString(), '+1d');
		$this->assertArrayHasKey('rec', $t->addons);
		$this->assertArrayNotHasKey('recurrent', $t->addons);
		$this->assertEquals($t->generateString(), 'dummy rec:+1d');

		$t = new TodoEx(null, 'dummy recurrent:+1d');
		$this->assertEquals($t->rec->toString(), '1d');
		$this->assertArrayHasKey('rec', $t->addons);
		$this->assertArrayNotHasKey('recurrent', $t->addons);
		$this->assertEquals($t->generateString(), 'dummy rec:1d');
	}
}
