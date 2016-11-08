<?php
/**
 * Генератор классов символов символов для Qevix
 */

require 'qevix.php';

function addChClass(&$table, $chars, $class) {
	foreach($chars as $ch) {
		$ord = Qevix::ord($ch);
		$table[$ord] = (isset($table[$ord]) ? $table[$ord] : 0) | $class;
	}
}

function addChRangeClass(&$table, $from, $to, $class) {
	for($i = $from; $i <= $to; $i++) {
		$table[$i] = (isset($table[$i]) ? $table[$i] : 0) | $class;
	}
}

$table = array();

addChRangeClass($table, 0, 0x20, Qevix::NOPRINT);
addChRangeClass($table, Qevix::ord('a'), Qevix::ord('z'), Qevix::ALPHA | Qevix::PRINATABLE | Qevix::TAG_NAME | Qevix::TAG_PARAM_NAME);
addChRangeClass($table, Qevix::ord('A'), Qevix::ord('Z'), Qevix::ALPHA |  Qevix::PRINATABLE | Qevix::TAG_NAME | Qevix::TAG_PARAM_NAME);
addChRangeClass($table, Qevix::ord('0'), Qevix::ord('9'), Qevix::NUMERIC | Qevix::PRINATABLE | Qevix::TAG_NAME | Qevix::TAG_PARAM_NAME);

addChClass($table, array('-'), Qevix::TAG_PARAM_NAME | Qevix::PRINATABLE);

addChClass($table, array(' ', "\t"), Qevix::SPACE);
addChClass($table, array("\r", "\n"), Qevix::NL);
addChClass($table, array('"'), Qevix::TAG_QUOTE | Qevix::TEXT_QUOTE | Qevix::PRINATABLE);
addChClass($table, array("'"), Qevix::TAG_QUOTE | Qevix::PRINATABLE);
addChClass($table, array('.', ',', '!', '?', ':', ';'), Qevix::PUNCTUATUON | Qevix::PRINATABLE);

addChClass($table, array('<', '>', '[', ']', '{', '}', '(', ')'),  Qevix::TEXT_BRACKET | Qevix::PRINATABLE);

addChClass($table, array('@', '#', '$'),  Qevix::SPECIAL_CHAR | Qevix::PRINATABLE);

ob_start();
var_export($table);
$res = ob_get_clean();
echo str_replace(array("\n", ' '), '', $res).';';
?>
