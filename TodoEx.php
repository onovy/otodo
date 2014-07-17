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

class TodoEx extends Todo {
	private $due;
	private $recurrent;
	protected $validExtensions = array('due', 'recurrent');

	protected function clean() {
		parent::clean();
		$this->due = null;
		$this->recurrent = null;
	}

	protected function parseAddons() {
		parent::parseAddons();
		if (isset($this->addons['due'])) {
			$this->due = $this->parseDate($this->addons['due']);
		}
		if (isset($this->addons['recurrent'])) {
			$this->recurrent = new Recurrent($this->addons['recurrent']);
		}
	}

	public function markDone() {
		if ($this->recurrent) {
			$t = clone $this;
			$t->due = $this->recurrent->recurr($this->due);
			$t->addons['due'] = $t->due->format('Y-m-d');
			$this->todos[] = $t;
		}
		parent::markDone();
	}

	public function __set($name, $value) {
		switch ($name) {
			case 'due':
				$this->due = $value;
				if ($value === null) {
					unset($this->addons['due']);
				} else {
					assert($value instanceof DateTime);
					$this->addons['due'] = $value->format('Y-m-d');
				}
			break;
			case 'recurrent':
				$this->recurrent = $value;
				if ($value === null) {
					unset($this->addons['recurrent']);
				} else {
					assert($value instanceof Recurrent);
					$this->addons['recurrent'] = $value->toString();
				}
			break;
			default:
				parent::__set($name, $value);
			break;
		}
	}

	public function __get($name) {
		switch ($name) {
			case 'due':
				return $this->due;
			break;
			case 'recurrent':
				return $this->recurrent;
			break;
			default:
				return parent::__get($name);
			break;
		}
	}

	public function debug() {
		parent::debug();
		if ($this->due !== null) {
			echo 'Due: ' . $this->due->format('Y-m-d') . PHP_EOL;
		}
		if ($this->recurrent !== null) {
			echo 'Recurrent: ' . $this->recurrent->toString() . PHP_EOL;
		}
	}
}
