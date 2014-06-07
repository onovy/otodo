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

class Todos implements Iterator, ArrayAccess, Countable {
	private $todos = array();
	protected $CLASS = 'Todo';
	private $currentId = 0;

	public function loadFromFile($filename) {
		$f = @fopen($filename, 'r');
		if ($f === FALSE) {
			$phpError = error_get_last();
			throw new TodosLoadException('Can\'t load todo file: ' .
				$phpError['message']);
		}

		$this->todos = array();
		while (($line = fgets($f)) !== false) {
			$t = new $this->CLASS($this);
			$t->fillFromString($line);
			$this->offsetSet(null, $t);
		}

		fclose($f);
	}

	public function saveToFile($filename) {
		$f = fopen($filename, 'w');

		foreach ($this->todos as $todo) {
			fputs($f, $todo->generateString() . PHP_EOL);
		}

		fclose($f);
	}

	public function array_keys() {
		return array_keys($this->todos);
	}

	public function array_filter($callback) {
		return array_filter($this->todos, $callback);
	}

	public function current() {
		return current($this->todos);
	}

	public function key() {
		return key($this->todos);
	}

	public function next() {
		return next($this->todos);
	}

	public function rewind() {
		return reset($this->todos);
	}

	public function valid() {
		return (key($this->todos) !== NULL);
	}

	public function count() {
		return count($this->todos);
	}

	public function offsetExists($offset) {
		return array_key_exists($offset, $this->todos);
	}

	public function offsetGet($offset) {
		return $this->todos[$offset];
	}

	public function offsetSet($offset, $value) {
		$value->id = $this->currentId++;
		if ($offset === null) {
			$this->todos[] = $value;
		} else {
			$this->todos[$offset] = $value;
		}
	}

	public function offsetUnset($offset) {
		unset($this->todos[$offset]);
	}

	protected function sortCmp($col, $asc, $a, $b) {
		switch ($col) {
			case 'text':
				$ac = trim($a->$col);
				$bc = trim($b->$col);
				$cmp = strcasecmp($ac, $bc);
				if ($cmp == 0) {
					return 0;
				} elseif ($cmp > 0) {
					return $asc ? 1 : -1;
				} else {
					return $asc ? -1 : 1;
				}
			break;
			case 'priority':
				if ($a->$col === null) {
					if ($b->$col === null) {
						return 0;
					}
					return 1;
				} elseif ($b->$col === null) {
					return -1;
				}
				$ac = trim($a->$col);
				$bc = trim($b->$col);
				$cmp = strcasecmp($ac, $bc);
				if ($cmp == 0) {
					return 0;
				} elseif ($cmp > 0) {
					return $asc ? 1 : -1;
				} else {
					return $asc ? -1 : 1;
				}
			break;
			case 'done':
				if ($a->$col == $b->$col) {
					return 0;
				} elseif ($a->$col) {
					return $asc ? 1 : -1;
				} else {
					return $asc ? -1 : 1;
				}
			break;
			case 'creationDate':
			case 'doneDate':
				if ($a->$col === null) {
					if ($b->$col === null) {
						return 0;
					}
					return 1;
				} elseif ($b->$col === null) {
					return -1;
				}
				$diff = $a->$col->diff($b->$col);
				if ($diff->days == 0) {
					return 0;
				}
				if ($diff->invert) {
					return $asc ? 1 : -1;
				} else {
					return $asc ? -1 : 1;
				}
			break;
			default:
				$trace = debug_backtrace();
				trigger_error (
					'Unknown sorting param: ' . $col,
					E_USER_ERROR
				);
			break;
		}
	}

	public function sort($order) {
		usort($this->todos, function($a, $b) use ($order) {
			foreach ($order as $col => $asc) {
				$cmp = static::sortCmp($col, $asc, $a, $b);
				if ($cmp != 0) {
					return $cmp;
				}
			}
			return $a->id > $b->id;
		});
	}

	public function asort($order) {
		uasort($this->todos, function($a, $b) use ($order) {
			foreach ($order as $col => $asc) {
				$cmp = static::sortCmp($col, $asc, $a, $b);
				if ($cmp != 0) {
					return $cmp;
				}
			}
			return $a->id > $b->id;
		});
	}

	public function archive($archive) {
		if (!file_exists($archive)) {
			touch($archive);
		}
		$ta = new TodosEx();
		$ta->loadFromFile($archive);
		$unset = array();
		foreach ($this->todos as $k => $todo) {
			if ($todo->done) {
				$ta[] = $todo;
				$unset[] = $k;
			}
		}
		if (count($unset)) {
			foreach ($unset as $k) {
				unset($this->todos[$k]);
			}
			$ta->saveToFile($archive);
			return count($unset);
		} else {
			return 0;
		}
	}

	public function searchSimilar($todo) {
		assert($todo instanceof Todo);

		$limit = Config::$config['core']['similar_limit'];
		$max = 0;
		$sTodo = null;

		$text1 = '';
		$end = '';
		$tokens = explode(' ', $todo->text);
		foreach ($tokens as $token) {
			if ($token[0] == '+' || $token[0] == '@') {
				$end .= $token . ' ';
				continue;
			}
			$text1 .= $token . ' ';
		}
		$text1 .= $end;
		$text1 = trim($text1);
		$text1 = strtolower($text1);

		foreach ($this->todos as $todo2) {
			$text2 = '';
			$end = '';
			$tokens = explode(' ', $todo2->text);
			foreach ($tokens as $token) {
				if ($token[0] == '+' || $token[0] == '@') {
					$end .= $token . ' ';
					continue;
				}
				$text2 .= $token . ' ';
			}
			$text2 .= $end;
			$text2 = trim($text2);
			$text2 = strtolower($text2);

			$perc = 0;
			similar_text($text1, $text2, $perc);
			if ($perc >= $limit && $perc > $max) {
				$max = $perc;
				$sTodo = $todo2;
			}
		}

		return $sTodo;
	}
}
