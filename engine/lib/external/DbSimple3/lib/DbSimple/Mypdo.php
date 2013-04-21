<?php
/**
 * DbSimple_Mypdo: PDO MySQL database.
 * (C) Dk Lab, http://en.dklab.ru
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
 * Database class for MySQL.
 */
class DbSimple_Mypdo extends DbSimple_Database
{
	private $link;

	public function DbSimple_Mypdo($dsn)
	{
		$base = preg_replace('{^/}s', '', $dsn['path']);
		if (!class_exists('PDO'))
			return $this->_setLastError("-1", "PDO extension is not loaded", "PDO");

		try {
			$this->link = new PDO('mysql:host='.$dsn['host'].(empty($dsn['port'])?'':';port='.$dsn['port']).';dbname='.$base,
				$dsn['user'], isset($dsn['pass'])?$dsn['pass']:'', array(
					PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
					PDO::ATTR_PERSISTENT => isset($dsn['persist']) && $dsn['persist'],
					PDO::ATTR_TIMEOUT => isset($dsn['timeout']) && $dsn['timeout'] ? $dsn['timeout'] : 0,
					PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.(isset($dsn['enc']) ? $dsn['enc'] : 'UTF8'),
				));
		} catch (PDOException $e) {
			$this->_setLastError($e->getCode() , $e->getMessage(), 'new PDO');
		}
	}

	protected function _performGetPlaceholderIgnoreRe()
	{
		return '
			"   (?> [^"\\\\]+|\\\\"|\\\\)*    "   |
			\'  (?> [^\'\\\\]+|\\\\\'|\\\\)* \'   |
			`   (?> [^`]+ | ``)*              `   |   # backticks
			/\* .*?                          \*/      # comments
		';
	}

	protected function _performEscape($s, $isIdent=false)
	{
		if (!$isIdent) {
			return $this->link->quote($s);
		} else {
			return "`" . str_replace('`', '``', $s) . "`";
		}
	}

	protected function _performTransaction($parameters=null)
	{
		return $this->link->beginTransaction();
	}

	protected function _performCommit()
	{
		return $this->link->commit();
	}

	protected function _performRollback()
	{
		return $this->link->rollBack();
	}

	protected function _performQuery($queryMain)
	{
		$this->_lastQuery = $queryMain;
		$this->_expandPlaceholders($queryMain, false);
		$p = $this->link->query($queryMain[0]);
		if (!$p)
			return $this->_setDbError($p,$queryMain[0]);
		if ($p->errorCode()!=0)
			return $this->_setDbError($p,$queryMain[0]);
		if (preg_match('/^\s* INSERT \s+/six', $queryMain[0]))
			return $this->link->lastInsertId();
		if ($p->columnCount()==0)
			return $p->rowCount();
		//Если у нас в запросе есть хотя-бы одна колонка - это по любому будет select
		$p->setFetchMode(PDO::FETCH_ASSOC);
		$res = $p->fetchAll();
		$p->closeCursor();
		return $res;
	}

	protected function _performTransformQuery(&$queryMain, $how)
	{
		// If we also need to calculate total number of found rows...
		switch ($how)
		{
			// Prepare total calculation (if possible)
			case 'CALC_TOTAL':
				$m = null;
				if (preg_match('/^(\s* SELECT)(.*)/six', $queryMain[0], $m))
					$queryMain[0] = $m[1] . ' SQL_CALC_FOUND_ROWS' . $m[2];
				return true;

			// Perform total calculation.
			case 'GET_TOTAL':
				// Built-in calculation available?
				$queryMain = array('SELECT FOUND_ROWS()');
				return true;
		}

		return false;
	}

	protected function _setDbError($obj,$q)
	{
		$info=$obj?$obj->errorInfo():$this->link->errorInfo();
		return $this->_setLastError($info[1], $info[2], $q);
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