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

mb_internal_encoding('UTF-8');

/**
 * Pad a multi-byte string to a certain length with another multi-byte string
 *
 * @param string $input The input multi-byte string.
 * @param string $pad_length If the value of pad_length is negative, less than,
 *        or equal to the length of the input string, no padding takes place,
 *        and input will be returned.
 * @param string $pad_string Multi-byte string to be padded.
 * @param int $pad_type Optional argument pad_type can be STR_PAD_RIGHT,
 *        STR_PAD_LEFT, or STR_PAD_BOTH. If pad_type is not specified
 *        it is assumed to be STR_PAD_RIGHT.
 *
 * @return Returns the padded string.
 */
function mb_str_pad($input, $pad_length, $pad_string, $pad_type = STR_PAD_RIGHT, $encoding = null) {
	if (is_null($encoding)) {
		$encoding = mb_internal_encoding();
	}

	if (mb_strlen($input, $encoding) >= $pad_length) {
		return $input;
	}

	$pad = '';
	for ($i = 0 ; $i < $pad_length - mb_strlen($input, $encoding) ; $i++) {
		$pad .= mb_substr($pad_string, $i % mb_strlen($pad_string, $encoding), 1, $encoding);
	}

	switch ($pad_type) {
		case STR_PAD_LEFT:
			return $pad . $input;
		break;
		case STR_PAD_RIGHT:
			return $input . $pad;
		break;
		case STR_PAD_BOTH:
			$l = floor(mb_strlen($pad, $encoding) / 2);
			$r = ceil(mb_strlen($pad, $encoding) / 2);
			return mb_substr($pad, 0, $l, $encoding) . $input . mb_substr($pad, 0, $r, $encoding);
		break;
	}
}

require_once dirname(__FILE__) . '/Exception.php';
require_once dirname(__FILE__) . '/Todos.php';
require_once dirname(__FILE__) . '/Todo.php';
require_once dirname(__FILE__) . '/Recurrent.php';
require_once dirname(__FILE__) . '/Term.php';
require_once dirname(__FILE__) . '/Gui.php';
require_once dirname(__FILE__) . '/GuiDiff.php';
require_once dirname(__FILE__) . '/ReadLine.php';
require_once dirname(__FILE__) . '/Config.php';

// My addons
require_once dirname(__FILE__) . '/TodosEx.php';
require_once dirname(__FILE__) . '/TodoEx.php';
