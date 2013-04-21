<?php
/**
 * DbSimple_Sqlite: Sqlite2 database.
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * See http://www.gnu.org/copyleft/lesser.html
 *
 * Placeholders are emulated because of logging purposes.
 *
 * @author Ivan Borzenkov, http://forum.dklab.ru/users/Ivan1986/
 *
 * @version 2.x $Id$
 */
require_once dirname(__FILE__).'/Database.php';

/**
 * Database class for Sqlite.
 */
class DbSimple_Sqlite extends DbSimple_Database
{
	private $link;
	public function __construct($dsn)
	{
		$connect = 'sqlite_'.((isset($dsn['persist']) && $dsn['persist'])?'p':'').'open';
		if (!is_callable($connect))
			return $this->_setLastError("-1", "SQLite extension is not loaded", $connect);
		$err = '';
		try
		{
			$this->link = sqlite_factory($dsn['path'], 0666, $err);
		}
		catch (Exception $e)
		{
			$this->_setLastError($e->getCode() , $e->getMessage(), 'sqlite_factory');
		}
	}

	public function CreateFunction($function_name, $callback, $num_args)
	{	return $this->link->createFunction($function_name, $callback, $num_args); }
	public function CreateAggregate($function_name, $step_func, $finalize_func, $num_args)
	{	return $this->link->createAggregate($function_name, $step_func, $finalize_func, $num_args); }

	protected function _performGetPlaceholderIgnoreRe()
	{
		return '
			"   (?> [^"\\\\]+|\\\\"|\\\\)*    "   |
			\'  (?> [^\'\\\\]+|\\\\\'|\\\\)* \'   |
			`   (?> [^`]+ | ``)*              `   |   # backticks
			/\* .*?                          \*/      # comments
		/*';
	}

	protected function _performEscape($s, $isIdent=false)
	{
		if (!$isIdent) {
			return '\''.sqlite_escape_string($s).'\'';
		} else {
			return "`" . str_replace('`', '``', $s) . "`";
		}
	}

	protected function _performTransaction($parameters=null)
	{
		return $this->link->query('BEGIN TRANSACTION');
	}

	protected function _performCommit()
	{
		return $this->link->query('COMMIT TRANSACTION');
	}

	protected function _performRollback()
	{
		return $this->link->query('ROLLBACK TRANSACTION');
	}

	protected function _performQuery($queryMain)
	{
		$this->_lastQuery = $queryMain;
		$this->_expandPlaceholders($queryMain, false);
		$error_msg = '';
		$p = $this->link->query($queryMain[0], SQLITE_ASSOC, $error_msg);
		if (!$p || $error_msg)
			return $this->_setDbError($queryMain[0]);
		if (preg_match('/^\s* INSERT \s+/six', $queryMain[0]))
			return $this->link->lastInsertRowid();
		if ($p->numFields()==0)
			return $this->link->changes();
		//Если у нас в запросе есть хотя-бы одна колонка - это по любому будет select
		return $p->fetchAll(SQLITE_ASSOC);
	}

	protected function _performTransformQuery(&$queryMain, $how)
	{
		// If we also need to calculate total number of found rows...
		switch ($how)
		{
			// Prepare total calculation (if possible)
			case 'CALC_TOTAL':
				// Not possible
				return true;

			// Perform total calculation.
			case 'GET_TOTAL':
				// TODO: GROUP BY ... -> COUNT(DISTINCT ...)
				$re = '/^
					(?> -- [^\r\n]* | \s+)*
					(\s* SELECT \s+)                                             #1
					(.*?)                                                        #2
					(\s+ FROM \s+ .*?)                                           #3
						((?:\s+ ORDER \s+ BY \s+ .*?)?)                          #4
						((?:\s+ LIMIT \s+ \S+ \s* (?: , \s* \S+ \s*)? )?)  #5
				$/six';
				$m = null;
				if (preg_match($re, $queryMain[0], $m)) {
					$queryMain[0] = $m[1] . $this->_fieldList2Count($m[2]) . " AS C" . $m[3];
					$skipTail = substr_count($m[4] . $m[5], '?');
					if ($skipTail) array_splice($queryMain, -$skipTail);
				}
				return true;
		}

		return false;
	}

	protected function _setDbError($query)
	{
		return $this->_setLastError($this->link->lastError(), sqlite_error_string($this->link->lastError()), $query); 
	}

	protected function _performNewBlob($id=null)
	{
	}

	protected function _performGetBlobFieldNames($result)
	{
		return array();
	}

	protected function _performFetch($result)
	{
		return $result;
	}

}

?>