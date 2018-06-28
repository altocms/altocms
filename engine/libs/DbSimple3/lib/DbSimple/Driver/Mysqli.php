<?php
/**
 * DbSimple_Mysqli: MySQLi database.
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
 * @author Andrey Stavitsky
 *
 * @version 2.x $Id$
 */


/**
 * Database class for MySQL.
 */
class DbSimple_Driver_Mysqli extends DbSimple_Database
{
	private $link;
	private $isMySQLnd;

    /**
     * DbSimple_Driver_Mysqli constructor.
     *
     * @param $dsn
     */
	public function __construct($dsn)
	{
        $dsn = DbSimple_Database::parseDSN($dsn);
		$base = preg_replace('{^/}s', '', $dsn['path']);
		if (!class_exists('mysqli')) {
            $this->_setLastError(-1, 'mysqli extension is not loaded', 'mysqli');
            return;
        }

		try {
            $this->link = mysqli_init();
            if (!$this->link) {
                $this->_setLastError(-1, 'mysqli_init failed', 'new mysqli');
            } else {
                if (!$this->link->options(
                    MYSQLI_OPT_CONNECT_TIMEOUT,
                    isset($dsn['timeout']) && $dsn['timeout'] ? $dsn['timeout'] : 0
                )) {
                    $this->_setLastError($this->link->connect_errno, $this->link->connect_error, 'new mysqli');
                } else {
                    if (!$this->link->real_connect(
                        (isset($dsn['persist']) && $dsn['persist']) ? 'p:' . $dsn['host'] : $dsn['host'],
                        $dsn['user'],
                        isset($dsn['pass']) ? $dsn['pass'] : '', $base,
                        empty($dsn['port']) ? NULL : $dsn['port'],
                        NULL,
                        (isset($dsn['compression']) && $dsn['compression']) ? MYSQLI_CLIENT_COMPRESS : NULL
                    )) {
                        $this->_setLastError($this->link->connect_errno, $this->link->connect_error, 'new mysqli');
                    } else {
                        if (!$this->link->set_charset((isset($dsn['enc']) ? $dsn['enc'] : 'UTF8'))) {
                            $this->_setLastError($this->link->connect_errno, $this->link->connect_error, 'new mysqli');
                        } else {
                            $this->isMySQLnd = method_exists('mysqli_result', 'fetch_all');
                        }
                    }
                }
            }

			$this->isMySQLnd = method_exists('mysqli_result', 'fetch_all');
		} catch (mysqli_sql_exception $e) {
			$this->_setLastError($e->getCode() , $e->getMessage(), 'new mysqli');
		}
	}

    /**
     * @return string
     */
	protected function _performGetPlaceholderIgnoreRe()
	{
		return '
			"   (?> [^"\\\\]+|\\\\"|\\\\)*    "   |
			\'  (?> [^\'\\\\]+|\\\\\'|\\\\)* \'   |
			`   (?> [^`]+ | ``)*              `   |   # backticks
			/\* .*?                          \*/      # comments
		';
	}

    /**
     * @param $s
     * @param bool $isIdent
     *
     * @return string
     */
	protected function _performEscape($s, $isIdent = false)
	{
		if (!$isIdent) {
			return "'" . $this->link->escape_string($s) . "'";
		}
        return '`' . str_replace('`', '``', $s) . '`';
	}

    /**
     * @param null $parameters
     * @return bool|mysqli_result
     */
	protected function _performTransaction($parameters = null)
	{
		return $this->link->query('BEGIN');
	}

    /**
     * @return bool|mysqli_result
     */
	protected function _performCommit()
	{
		return $this->link->query('COMMIT');
	}

    /**
     * @return bool|mysqli_result
     */
	protected function _performRollback()
	{
		return $this->link->query('ROLLBACK');
	}

    /**
     * @param $queryMain
     *
     * @return bool|int|mixed|mysqli_result
     */
	protected function _performQuery($queryMain)
	{
		$this->_lastQuery = $queryMain;

		$this->_expandPlaceholders($queryMain, false);

		$result = $this->link->query($queryMain[0]);

		if (!$result) {
            return $this->_setDbError($this->link, $queryMain[0]);
        }

		if ($this->link->errno) {
            return $this->_setDbError($this->link, $queryMain[0]);
        }

        if (preg_match('/^\s* INSERT \s+/six', $queryMain[0])) {
            return $this->link->insert_id;
        }

		if ($this->link->field_count == 0) {
            return $this->link->affected_rows;
        }

		if ($this->isMySQLnd) {
			$res = $result->fetch_all(MYSQLI_ASSOC);
			$result->close();
		} else {
			$res = $result;
		}

        return $res;
	}

    /**
     * @param $queryMain
     * @param $how
     *
     * @return bool
     */
	protected function _performTransformQuery(&$queryMain, $how)
	{
		// If we also need to calculate total number of found rows...
		switch ($how)
		{
			// Prepare total calculation (if possible)
			case 'CALC_TOTAL':
				$m = null;
				if (preg_match('/^(\s* SELECT)(.*)/six', $queryMain[0], $m)) {
                    $queryMain[0] = $m[1] . ' SQL_CALC_FOUND_ROWS' . $m[2];
                }
				return true;

			// Perform total calculation.
			case 'GET_TOTAL':
				// Built-in calculation available?
				$queryMain = array('SELECT FOUND_ROWS()');
				return true;
		}
		return false;
	}

    /**
     * @param $obj
     * @param $q
     *
     * @return bool
     */
	protected function _setDbError($obj, $q)
	{
		$info = $obj ? $obj : $this->link;
		return $this->_setLastError($info->errno, $info->error, $q);
	}

    /**
     * @param $id
     */
	protected function _performNewBlob($id = null)
	{
	}

    /**
     * @param $result
     *
     * @return array
     */
	protected function _performGetBlobFieldNames($result)
	{
		return [];
	}

    /**
     * @param $result
     *
     * @return bool|null|array
     */
	protected function _performFetch($result)
	{
		if ($this->isMySQLnd) {
            return $result;
        }

		$row = $result->fetch_assoc();

        if ($this->link->error)
			return $this->_setDbError($this->link, $this->_lastQuery);

        if ($row === false) {
			$result->close();
			return null;
		}
		
        return $row;
	}
}

