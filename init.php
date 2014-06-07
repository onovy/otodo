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

mb_internal_encoding('UTF-8');

function mb_str_pad ($input, $pad_length, $pad_string, $pad_type, $encoding = null) {
	if (is_null($encoding)) {
		$encoding = mb_internal_encoding();
	}
	return str_pad($input, strlen($input) - mb_strlen($input, $encoding) + $pad_length, $pad_string, $pad_type);
}

require_once dirname(__FILE__) . '/Exception.php';
require_once dirname(__FILE__) . '/Todos.php';
require_once dirname(__FILE__) . '/Todo.php';
require_once dirname(__FILE__) . '/Recurrent.php';
require_once dirname(__FILE__) . '/Gui.php';
require_once dirname(__FILE__) . '/ReadLine.php';
require_once dirname(__FILE__) . '/Config.php';

// My addons
require_once dirname(__FILE__) . '/TodosEx.php';
require_once dirname(__FILE__) . '/TodoEx.php';
