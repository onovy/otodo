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

class Todo {
	public $id;
	public $done = false;
	public $doneDate;
	public $priority;
	public $creationDate;
	private $text = '';
	public $projects = array();
	public $contexts = array();
	public $addons = array();
	protected $todos;
	protected $validExtensions = array();

	public function __construct($todos, $str = null) {
		$this->todos = $todos;
		if (!is_null($str)) {
			$this->fillFromString($str);
		}
	}

	protected function parseDate($string) {
		$dt = DateTime::createFromFormat('Y-m-d', $string);
		if ($dt === false ){
			return null;
		}
		$dt->modify('00.00.00');
		return $dt;
	}

	protected function clean() {
		$this->done = false;
		$this->doneDate = null;
		$this->priority = null;
		$this->creationDate = null;
		$this->text = '';
		$this->projects = array();
		$this->contexts = array();
		$this->addons = array();
	}

	protected function parseAddons() {
	}

	protected function parseText() {
		$cols = explode(' ', $this->text);
		$output = '';
		$this->contexts = array();
		$this->projects = array();
		foreach ($cols as $col) {
			if (empty($col)) {
				continue;
			}
			if ($col[0] == '@') {
				// Context
				$this->contexts[] = substr($col, 1);
				$output .= $col . ' ';
			} elseif ($col[0] == '+') {
				// Project
				$this->projects[] = substr($col, 1);
				$output .= $col . ' ';
			} elseif (strpos($col, ':') !== false) {
				list($k, $v) = explode(':' , $col);
				if (in_array($k, $this->validExtensions)) {
					$this->addons[$k] = $v;
				} else {
					$output .= $col . ' ';
				}
			} else {
				$output .= $col . ' ';
			}
		}
		$this->text = rtrim($output);
		$this->parseAddons();
	}

	public function __set($name, $value) {
		switch ($name) {
			case 'text':
				$this->text = $value;
				$this->parseText();
			break;
			default:
				$trace = debug_backtrace();
				trigger_error (
					'Undefined property via __set(): ' . $name .
					' in ' . $trace[0]['file'] .
					' on line ' . $trace[0]['line'],
					E_USER_ERROR
				);
			break; // @codeCoverageIgnore
		}
	}

	public function __get($name) {
		switch ($name) {
			case 'text':
				return $this->text;
			break;
			default:
				$trace = debug_backtrace();
				trigger_error(
					'Undefined property via __get(): ' . $name .
					' in ' . $trace[0]['file'] .
					' on line ' . $trace[0]['line'],
					E_USER_ERROR
				);
			break; // @codeCoverageIgnore
		}
	} // @codeCoverageIgnore

	public function fillFromString($line) {
		$line = rtrim($line);

		$this->clean();

		// Finished task
		if (preg_match('/^x /', $line)) {
			$this->done = true;
			$line = substr($line, 2);

			$pos = strpos($line, ' ');
			$this->doneDate = $this->parseDate(substr($line, 0, $pos));
			if ($this->doneDate != null) {
				$line = substr($line, $pos + 1);
			}
		}

		// Priority
		if (preg_match('/^\([A-Z]\) /', $line)) {
			$this->priority = $line[1];
			$line = substr($line, 4);
		}

		// Creation date
		$pos = strpos($line, ' ');
		$this->creationDate = $this->parseDate(substr($line, 0, $pos));
		if ($this->creationDate != null) {
			$line = substr($line, $pos + 1);
		}

		$this->text = $line;
		$this->parseText();
	}

	public function generateString() {
		$out = '';
		if ($this->done) {
			$out .= 'x ';
			if ($this->doneDate !== null) {
				$out .= $this->doneDate->format('Y-m-d') . ' ';
			}
		}
		if ($this->priority != null) {
			$out .= '(' . $this->priority . ') ';
		}
		if ($this->creationDate !== null) {
			$out .= $this->creationDate->format('Y-m-d') . ' ';
		}
		$out .= $this->text . ' ';
		ksort($this->addons);
		foreach ($this->addons as $k=>$v) {
			$out .= $k . ':' . $v . ' ';
		}
		return rtrim($out);
	}

	public function markDone() {
		$this->done = true;
		$this->doneDate = new DateTime('today');
	}

	public function unmarkDone() {
		$this->done = false;
		$this->doneDate = null;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public function debug() {
		echo '=========' . PHP_EOL;
		echo 'Done: ' . ($this->done ? 't' : 'f') . PHP_EOL;
		if ($this->doneDate !== null) {
			echo 'Done date: ' . $this->doneDate->format('Y-m-d') . PHP_EOL;
		}
		if ($this->creationDate !== null) {
			echo 'Creation date: ' . $this->creationDate->format('Y-m-d') . PHP_EOL;
		}
		echo 'Text: ' . $this->text . PHP_EOL;
		if ($this->priority !== null) {
			echo 'Priority: ' . $this->priority . PHP_EOL;
		}
		echo 'Projects: ' . implode(',', $this->projects) . PHP_EOL;
		echo 'Contexts: ' . implode(',', $this->contexts) . PHP_EOL;
		echo 'Addons: ' . implode(',', $this->addons) . PHP_EOL;
		echo '=========' . PHP_EOL;
	}
}
