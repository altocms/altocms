<?php
/**
 * DbSimple_Mssql: Mssql database.
 * (C) Dk Lab, http://en.dklab.ru
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * See http://www.gnu.org/copyleft/lesser.html
 *
 * Placeholders end blobs are emulated.
 *
 * @author Dmitry Koterov, http://forum.dklab.ru/users/DmitryKoterov/
 * @author Konstantin Zhinko, http://forum.dklab.ru/users/KonstantinGinkoTit/
 *
 * @version 2.x $Id: Mssql.php 163 2007-01-10 09:47:49Z dk $
 */
require_once dirname(__FILE__) . '/Generic.php';


/**
 * Database class for Mssql.
 */
class DbSimple_Mssql extends DbSimple_Database
{
    var $link;

    /**
     * constructor(string $dsn)
     * Connect to Mssql.
     */
    function DbSimple_Mssql($dsn)
    {
        if (!is_callable('mssql_connect')) {
            return $this->_setLastError("-1", "Mssql extension is not loaded", "mssql_connect");
        }
        $ok = $this->link = @mssql_connect(
            $dsn['host'] . (empty($dsn['port'])? "" : ":".$dsn['port']),
            $dsn['user'],
            $dsn['pass'],
            true
        );
        $this->_resetLastError();
        if (!$ok) return $this->_setDbError('mssql_connect()');
        $ok = @mssql_select_db(preg_replace('{^/}s', '', $p['path']), $this->link);
        if (!$ok) return $this->_setDbError('mssql_select_db()');
    }


    function _performEscape($s, $isIdent=false)
    {
        if (!$isIdent) {
            return "'" . str_replace("'", "''", $s) . "'";
        } else {
            return str_replace(array('[',']'), '', $s);
        }
    }


    function _performTransaction($parameters=null)
    {
        return $this->query('BEGIN TRANSACTION');
    }


    function _performNewBlob($blobid=null)
    {
        $obj = new DbSimple_Mssql_Blob($this, $blobid);
        return $obj;
    }


    function _performGetBlobFieldNames($result)
    {
        $blobFields = array();
        for ($i=mssql_num_fields($result)-1; $i>=0; $i--) {
            $type = mssql_field_type($result, $i);
            if (strpos($type, "BLOB") !== false) $blobFields[] = mssql_field_name($result, $i);
        }
        return $blobFields;
    }


    function _performGetPlaceholderIgnoreRe()
    {
        return '
            "   (?> [^"\\\\]+|\\\\"|\\\\)*    "   |
            \'  (?> [^\'\\\\]+|\\\\\'|\\\\)* \'   |
            `   (?> [^`]+ | ``)*              `   |   # backticks
            /\* .*?                          \*/      # comments
        ';
    }


    function _performCommit()
    {
        return $this->query('COMMIT TRANSACTION');
    }


    function _performRollback()
    {
        return $this->query('ROLLBACK TRANSACTION');
    }


    function _performTransformQuery(&$queryMain, $how)
    {
        // If we also need to calculate total number of found rows...
        switch ($how) {
            // Prepare total calculation (if possible)
            case 'CALC_TOTAL':
                $m = null;
                if (preg_match('/^(\s* SELECT)(.*)/six', $queryMain[0], $m)) {
                    if ($this->_calcFoundRowsAvailable()) {
                        $queryMain[0] = $m[1] . ' SQL_CALC_FOUND_ROWS' . $m[2];
                    }
                }
                return true;

            // Perform total calculation.
            case 'GET_TOTAL':
                // Built-in calculation available?
                if ($this->_calcFoundRowsAvailable()) {
                    $queryMain = array('SELECT FOUND_ROWS()');
                }
                // Else use manual calculation.
                // TODO: GROUP BY ... -> COUNT(DISTINCT ...)
                $re = '/^
                    (?> -- [^\r\n]* | \s+)*
                    (\s* SELECT \s+)                                      #1
                    (.*?)                                                 #2
                    (\s+ FROM \s+ .*?)                                    #3
                        ((?:\s+ ORDER \s+ BY \s+ .*?)?)                   #4
                        ((?:\s+ LIMIT \s+ \S+ \s* (?:, \s* \S+ \s*)? )?)  #5
                $/six';
                $m = null;
                if (preg_match($re, $queryMain[0], $m)) {
                    $query[0] = $m[1] . $this->_fieldList2Count($m[2]) . " AS C" . $m[3];
                    $skipTail = substr_count($m[4] . $m[5], '?');
                    if ($skipTail) array_splice($query, -$skipTail);
                }
                return true;
        }

        return false;
    }


    function _performQuery($queryMain)
    {
        $this->_lastQuery = $queryMain;
        $this->_expandPlaceholders($queryMain, false);

        $result = mssql_query($queryMain[0], $this->link);

        if ($result === false) {
            return $this->_setDbError($queryMain[0]);
        }

        if (!is_resource($result)) {

            if (preg_match('/^\s* INSERT \s+/six', $queryMain[0])) {
                // INSERT queries return generated ID.
                $result = mssql_fetch_assoc(mssql_query("SELECT SCOPE_IDENTITY() AS insert_id", $this->link));
                return isset($result['insert_id']) ? $result['insert_id'] : true;
            }

            // Non-SELECT queries return number of affected rows, SELECT - resource.
            if (function_exists('mssql_affected_rows')) {
                return mssql_affected_rows($this->link);
            } elseif (function_exists('mssql_rows_affected')) {
                return mssql_rows_affected($this->link);
            }
        }
        return $result;
    }


    function _performFetch($result)
    {
        $row = mssql_fetch_assoc($result);
        //if (mssql_error()) return $this->_setDbError($this->_lastQuery);
        if ($row === false) return null;

        // mssql bugfix - replase ' ' to ''
        if (is_array($row)) {
            foreach ($row as $k => $v) {
                if ($v === ' ') $row[$k] = '';
            }
        }
        return $row;
    }


    function _setDbError($query, $errors = null)
    {
        return $this->_setLastError('Error! ', mssql_get_last_message() . strip_tags($errors), $query);
    }


    function _calcFoundRowsAvailable()
    {
        return false;
    }
}


class DbSimple_Mssql_Blob extends DbSimple_Generic_Blob
{
    // Mssql does not support separate BLOB fetching.
    var $blobdata = null;
    var $curSeek  = 0;

    function DbSimple_Mssql_Blob(&$database, $blobdata=null)
    {
        $this->blobdata = $blobdata;
        $this->curSeek = 0;
    }

    function read($len)
    {
        $p = $this->curSeek;
        $this->curSeek = min($this->curSeek + $len, strlen($this->blobdata));
        return substr($this->blobdata, $this->curSeek, $len);
    }

    function write($data)
    {
        $this->blobdata .= $data;
    }

    function close()
    {
        return $this->blobdata;
    }

    function length()
    {
        return strlen($this->blobdata);
    }
}
?>
