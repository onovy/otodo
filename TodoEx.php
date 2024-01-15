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

class TodoEx extends Todo {
	private $due;
	private $rec;
	protected $validExtensions = array('due', 'rec', 'recurrent');

	protected function clean() {
		parent::clean();
		$this->due = null;
		$this->rec = null;
	}

	protected function parseAddons() {
		parent::parseAddons();
		if (isset($this->addons['due'])) {
			$this->due = $this->parseDate($this->addons['due']);
		}
		if (isset($this->addons['rec'])) {
			$this->rec = new Recurrent($this->addons['rec']);
		}
		if (isset($this->addons['recurrent'])) {
			$val = $this->addons['recurrent'];
			if ($val[0] === '+') {
				$val = substr($val, 1);
			} else {
				$val = '+' . $val;
			}
			$this->rec = new Recurrent($val);
			unset($this->addons['recurrent']);
			$this->addons['rec'] = $this->rec->toString();
		}
	}

	public function markDone() {
		if ($this->rec) {
			$t = clone $this;
			$t->creationDate = new DateTime('today');
			$t->due = $this->rec->recurr($this->due);
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
			case 'rec':
				$this->rec = $value;
				if ($value === null) {
					unset($this->addons['rec']);
				} else {
					assert($value instanceof Recurrent);
					$this->addons['rec'] = $value->toString();
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
			case 'rec':
				return $this->rec;
			break;
			default:
				return parent::__get($name);
			break;
		}
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function debug() {
		parent::debug();
		if ($this->due !== null) {
			echo 'Due: ' . $this->due->format('Y-m-d') . PHP_EOL;
		}
		if ($this->rec !== null) {
			echo 'Recurrent: ' . $this->rec->toString() . PHP_EOL;
		}
	}
}
