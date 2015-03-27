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

class ReadLine {
	const INIT = 0;
	const READ = 1;
	const END = 2;
	const ESC_033 = 3;  // \033
	const ESC_033B = 5; // \033[
	const ESC_033O = 6; // \033O
	const ESC_033_TWOCHAR = 7; // \033??

	private $input = '';
	private $line = '';
	private $history = array();
	private $historyPos = null;
	private $historyNew = null;
	private $pos = 0;
	private $state = self::INIT;
	private $prefix = '';
	public $timeout = false;
	private $completitionCallback = null;
	private $completition = '';
	public $maxWidth = 80;
	private $leftOffset = 0;
	private $leftOffsetChange = 10;

	public function setCompletitionCallback(callable $callback) {
		$this->completitionCallback = $callback;
	}

	public function historyAdd($line) {
		$line = trim($line);
		if ($line === '') {
			return;
		}
		if (end($this->history) == $line) {
			return;
		}

		$this->history[] = $line;
	}

	public function historyLoad($file) {
		if (file_exists($file)) {
			$this->history = @unserialize(file_get_contents($file));
			if ($this->history === FALSE) {
				$phpError = error_get_last();
				throw new HistoryLoadException('Can\'t load history file: ' .
					$phpError['message']);
			} elseif (!is_array($this->history)) {
				throw new HistoryLoadException('Can\'t load history file: Wrong format');
			}
		} else {
			$this->history = array();
		}
	}

	public function historySave($file) {
		file_put_contents($file, serialize($this->history));
	}

	public function read($prefix = '', $prefill = '', $timeout = null) {
		if ($timeout && $this->timeout) {
			$this->timeout = false;
		} else {
			$this->historyPos = null;
			$this->historyNew = null;
			$this->line = $prefill;
			$this->pos = strlen($this->line);
			$this->completition = '';
			$this->leftOffset = 0;
		}

		$this->state = self::READ;
		$this->prefix = $prefix;
		$this->repaint();
		system('stty -icanon -echo');
		while (true) {
			$read = array(STDIN);
			$write = array();
			$except = array();
			$count = stream_select($read, $write, $except, $timeout ? $timeout : null);
			if (!$count) {
				if ($timeout) {
					$this->timeout = true;
					break;
				}
				continue;
			}
			do {
				$this->input .= fread(STDIN, 256);

				$read = array(STDIN);
				$write = array();
				$except = array();
			} while (stream_select($read, $write, $except, 0));
			$this->parseInput();
			$this->repaint();
			if ($this->state == self::END) {
				break;
			}
		}
		system('stty sane');
		echo PHP_EOL;
		echo "\033[K";
		return trim($this->line);
	}

	private function repaint() {
		$this->checkLeftOffset();

		echo PHP_EOL;
		echo "\033[K";
		if (mb_strlen($this->completition) > $this->maxWidth) {
			echo mb_substr($this->completition, 0, $this->maxWidth - 1) . '→';
		} else {
			echo $this->completition;
		}
		echo "\033[1A";
		echo "\033[9999D";
		echo "\033[K";

		echo $this->prefix;

		$maxLength = $this->maxWidth - mb_strlen($this->prefix);
		$length = mb_strlen($this->line) - $this->leftOffset;
		if ($this->leftOffset) {
			echo '←';
			$maxLength--;
		}
		echo mb_substr($this->line, $this->leftOffset, $maxLength - 1);
		if ($length >= $maxLength) {
			echo "→\033[1D";
			$length = $maxLength - 1;
		}

		$l = $length - $this->pos + $this->leftOffset;
		if ($l > 0) {
			echo "\033[" . $l . "D";
		}
	}

	private function checkLeftOffset() {
		$maxLength = $this->maxWidth - mb_strlen($this->prefix) - 2;
		if ($this->leftOffset) {
			$maxLength--;
		}
		if ($this->pos <= $this->leftOffset) {
			$this->leftOffset = floor($this->pos / $this->leftOffsetChange) * $this->leftOffsetChange;
		}
		if ($this->pos - $this->leftOffset >= $maxLength) {
			$this->leftOffset = ceil(($this->pos - $maxLength) / $this->leftOffsetChange) * $this->leftOffsetChange;
		}
	}

	private function parseInput() {
		for ($i = 0 ; $i < mb_strlen($this->input) ; $i++) {
			$ch = mb_substr($this->input, $i, 1);
			switch ($this->state) {
				case self::READ:
					switch ($ch) {
						case "\n":
							$this->state = self::END;
							break 3;
						break;
						case "\177": // Backspace
							if ($this->pos > 0) {
								if ($this->pos == mb_strlen($this->line)) {
									$this->line = mb_substr($this->line, 0, -1);
								} else {
									$this->line =
										mb_substr($this->line, 0, $this->pos - 1) .
										mb_substr($this->line, $this->pos);
								}
								$this->pos--;
							}
						break;
						case "\t": // Tab
							if (!is_null($this->completitionCallback)) {
								$search = mb_substr($this->line, 0, $this->pos);
								$pos = strrpos($search, ' ');
								if ($pos !== false) {
									$search = mb_substr($search, $pos + 1);
								}
								$ar = call_user_func($this->completitionCallback, $search);
								$this->completition = null;

								if (count($ar) == 1) {
									if ($pos === false) {
										$this->line =
											$ar[0] .
											mb_substr($this->line, mb_strlen($search));
									} else {
										$this->line =
											mb_substr($this->line, 0, $pos + 1) .
											$ar[0] .
											mb_substr($this->line, $pos + mb_strlen($search) + 1);
									}
									$this->pos += mb_strlen($ar[0]) - mb_strlen($search);
								} elseif (count($ar) > 1) {
									$this->completition = implode(' ', $ar);
								}
							}
						break;
						case "\033":
							$this->state = self::ESC_033;
						break;
						case "\001": // Ctrl+A
							$this->pos = 0;
						break;
						case "\005": // Ctrl+E
							$this->pos = mb_strlen($this->line);
						break;
						default:
							if (strlen($ch) == 1 && (ord($ch) < 32 || ord($ch) > 126)) {
								break;
							}
							if ($this->pos == mb_strlen($this->line)) {
								$this->line .= $ch;
							} else {
								$this->line =
									mb_substr($this->line, 0, $this->pos) .
									$ch .
									mb_substr($this->line, $this->pos);
							}
							$this->pos++;
						break;
					}
				break;
				case self::ESC_033:
					switch ($ch) {
						case '[':
							$this->state = self::ESC_033B;
						break;
						case 'O':
							$this->state = self::ESC_033O;
						break;
						default:
							if (ord($ch) >= 64 && ord($ch) <= 95) {
								$this->state = self::ESC_033_TWOCHAR;
							}
						break;
					}
				break;
				case self::ESC_033B:
					switch ($ch) {
						case 'A': // Up
							if ($this->historyPos === null) {
								if ($this->line === '' && $this->historyNew !== null) {
									$this->line = $this->historyNew;
									$this->pos = mb_strlen($this->line);
									continue;
								} else {
									if ($this->line != '') {
										$this->historyNew = $this->line;
									}
									$this->historyPos = count($this->history) - 1;
								}
							} elseif ($this->historyPos > 0) {
								$this->historyPos--;
							}
							$this->line = $this->history[$this->historyPos];
							$this->pos = mb_strlen($this->line);
						break;
						case 'B': // Down
							if ($this->historyPos === null) {
								$this->line = '';
							} elseif ($this->historyPos < count($this->history) - 1) {
								$this->historyPos++;
								$this->line = $this->history[$this->historyPos];
							} else {
								$this->historyPos = null;
								$this->line = $this->historyNew;
							}
							$this->pos = mb_strlen($this->line);
						break;
						case 'C': // Right
							if (mb_strlen($this->line) > $this->pos) {
								$this->pos++;
							}
						break;
						case 'D': // Left
							if ($this->pos > 0) {
								$this->pos--;
							}
						break;
						case '3': // Delete
							if ($this->pos + 1 < mb_strlen($this->line)) {
								$this->line =
									mb_substr($this->line, 0, $this->pos) .
									mb_substr($this->line, $this->pos + 1);
							} else if ($this->pos + 1 == mb_strlen($this->line)) {
								$this->line = mb_substr($this->line, 0, -1);
							}
						break;
					}
					if (ord($ch) >= 64 && ord($ch) <= 126) {
						$this->state = self::READ;
					}
				break;
				case self::ESC_033O:
					switch ($ch) {
						case 'H': // Home
							$this->pos = 0;
						break;
						case 'F': // End
							$this->pos = mb_strlen($this->line);
						break;
					}
					$this->state = self::READ;
				break;
				case self::ESC_033_TWOCHAR:
					$this->state = self::READ;
				break;
			}
		}
		$this->input = mb_substr($this->input, $i + 1);
	}
}
