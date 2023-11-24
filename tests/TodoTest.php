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

class TodoTest extends \PHPUnit\Framework\TestCase {
	public function testInOut() {
		$tests = array(
			'(A) dummy',
			'2014-02-01 dummy',
			'(B) 2014-02-01 dummy',
			'x dummy',
			'x (C) dummy',
			'x (D) 2014-03-01 dummy',
			'x 2014-02-01 dummy',
			'x 2014-02-01 (C) dummy',
			'x 2014-02-01 2014-03-01 dummy',
			'x 2014-02-01 (D) 2014-03-01 dummy',
			'',
		);

		foreach ($tests as $test) {
			$t = new Todo(null, $test);
			$this->assertEquals($t->generateString(), $test);
		}
	}

	public function testParser() {
		$t = new Todo(null, '(A) dummy');
		$this->assertEquals($t->priority, 'A');
		$this->assertEquals($t->text, 'dummy');

		$t = new Todo(null, '2014-02-01 dummy');
		$this->assertEquals($t->creationDate->format('Y-m-d'), '2014-02-01');
		$this->assertEquals($t->text, 'dummy');

		$t = new Todo(null, '(B) 2014-02-01 dummy');
		$this->assertEquals($t->priority, 'B');
		$this->assertEquals($t->creationDate->format('Y-m-d'), '2014-02-01');
		$this->assertEquals($t->text, 'dummy');

		$t = new Todo(null, 'x dummy');
		$this->assertTrue($t->done);
		$this->assertEquals($t->text, 'dummy');

		$t = new Todo(null, 'x (C) dummy');
		$this->assertTrue($t->done);
		$this->assertEquals($t->priority, 'C');
		$this->assertEquals($t->text, 'dummy');

		$t = new Todo(null, 'x (D) 2014-03-01 dummy');
		$this->assertTrue($t->done);
		$this->assertEquals($t->priority, 'D');
		$this->assertEquals($t->creationDate->format('Y-m-d'), '2014-03-01');
		$this->assertEquals($t->text, 'dummy');

		$t = new Todo(null, 'x 2014-02-01 dummy');
		$this->assertTrue($t->done);
		$this->assertEquals($t->doneDate->format('Y-m-d'), '2014-02-01');
		$this->assertEquals($t->text, 'dummy');

		$t = new Todo(null, 'x 2014-02-01 (C) dummy');
		$this->assertTrue($t->done);
		$this->assertEquals($t->doneDate->format('Y-m-d'), '2014-02-01');
		$this->assertEquals($t->priority, 'C');
		$this->assertEquals($t->text, 'dummy');

		$t = new Todo(null, 'x 2014-02-01 2014-03-01 dummy');
		$this->assertTrue($t->done);
		$this->assertEquals($t->doneDate->format('Y-m-d'), '2014-02-01');
		$this->assertEquals($t->creationDate->format('Y-m-d'), '2014-03-01');
		$this->assertEquals($t->text, 'dummy');

		$t = new Todo(null, 'x 2014-02-01 (D) 2014-03-01 dummy');
		$this->assertTrue($t->done);
		$this->assertEquals($t->doneDate->format('Y-m-d'), '2014-02-01');
		$this->assertEquals($t->priority, 'D');
		$this->assertEquals($t->creationDate->format('Y-m-d'), '2014-03-01');
		$this->assertEquals($t->text, 'dummy');

		$t = new Todo(null, 'dummy @context1 +project1 @context2 +project2');
		$this->assertTrue(count($t->projects) == 2);
		$this->assertArrayHasKey('project1', array_flip($t->projects));
		$this->assertArrayHasKey('project2', array_flip($t->projects));
		$this->assertTrue(count($t->contexts) == 2);
		$this->assertArrayHasKey('context1', array_flip($t->contexts));
		$this->assertArrayHasKey('context2', array_flip($t->contexts));
		$this->assertEquals($t->text, 'dummy @context1 +project1 @context2 +project2');

		$t = new Todo(null, 'dummy not_addon:value');
		$this->assertEquals($t->text, 'dummy not_addon:value');
	}

	public function testGenerator() {
		$date1 = new DateTime('2014-02-01');
		$date2 = new DateTime('2014-03-01');

		$t = new Todo(null);
		$t->priority = 'A';
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), '(A) dummy');

		$t = new Todo(null);
		$t->creationDate = $date1;
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), '2014-02-01 dummy');

		$t = new Todo(null);
		$t->priority = 'B';
		$t->creationDate = $date1;
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), '(B) 2014-02-01 dummy');

		$t = new Todo(null);
		$t->done = true;
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), 'x dummy');

		$t = new Todo(null);
		$t->priority = 'C';
		$t->done = true;
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), 'x (C) dummy');

		$t = new Todo(null);
		$t->done = true;
		$t->priority = 'D';
		$t->creationDate = $date1;
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), 'x (D) 2014-02-01 dummy');

		$t = new Todo(null);
		$t->done = true;
		$t->doneDate = $date1;
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), 'x 2014-02-01 dummy');

		$t = new Todo(null);
		$t->done = true;
		$t->doneDate = $date1;
		$t->priority = 'C';
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), 'x 2014-02-01 (C) dummy');

		$t = new Todo(null);
		$t->done = true;
		$t->doneDate = $date1;
		$t->creationDate = $date2;
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), 'x 2014-02-01 2014-03-01 dummy');

		$t = new Todo(null);
		$t->done = true;
		$t->doneDate = $date1;
		$t->priority = 'D';
		$t->creationDate = $date2;
		$t->text = 'dummy';
		$this->assertEquals($t->generateString(), 'x 2014-02-01 (D) 2014-03-01 dummy');
	}

	public function testMarkDone() {
		$t = new Todo(null);
		$t->markDone();
		$this->assertTrue($t->done);
		$this->assertEquals($t->doneDate->format('Y-m-d'), (new DateTime())->format('Y-m-d'));

		$t->unmarkDone();
		$this->assertFalse($t->done);
		$this->assertNull($t->doneDate);
	}

	public function testChange() {
		$t = new Todo(null, 'x 2014-02-01 (D) 2014-03-01 dummy');

		$t->text = 'dummy2';
		$this->assertEquals($t->text, 'dummy2');

		$t->priority = 'B';
		$this->assertEquals($t->priority, 'B');

		$t->priority = null;
		$this->assertEquals($t->priority, null);
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
}
