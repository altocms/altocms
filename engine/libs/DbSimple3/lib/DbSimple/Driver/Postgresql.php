<?php
/**
 * DbSimple_Postgreql: PostgreSQL database.
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
 * @author Dmitry Koterov, http://forum.dklab.ru/users/DmitryKoterov/
 * @author Konstantin Zhinko, http://forum.dklab.ru/users/KonstantinGinkoTit/
 *
 * @version 2.x $Id$
 */


/**
 * Database class for PostgreSQL.
 */
class DbSimple_Driver_Postgresql extends DbSimple_Database
{
    private $DbSimple_Postgresql_USE_NATIVE_PHOLDERS = null;
    private $prepareCache = array();
    private $link;

    /**
     * constructor(string $dsn)
     * Connect to PostgresSQL.
     */
    public function __construct($dsn)
    {
        $dsn = DbSimple_Database::parseDSN($dsn);
        if (!is_callable('pg_connect')) {
            $this->_setLastError(-1, 'PostgreSQL extension is not loaded', 'pg_connect');
            return;
        }

        // Prepare+execute works only in PHP 5.1+.
        $this->DbSimple_Postgresql_USE_NATIVE_PHOLDERS = function_exists('pg_prepare');
        
        $dsnWithoutPass = 
        	(!empty($dsn['host']) ? 'host='.$dsn['host'].' ' : '') .
            (!empty($dsn['port']) ? 'port=' . $dsn['port'] . ' ' : '') .
            'dbname=' . preg_replace('{^/}s', '', $dsn['path']) .' '.
            (!empty($dsn['user']) ? 'user='. $dsn['user'] : '');

        $ok = $this->link = @pg_connect(
            $dsnWithoutPass . ' ' . (!empty($dsn['pass']) ? 'password=' . $dsn['pass'] . ' ' : ''),
			PGSQL_CONNECT_FORCE_NEW
        );
        // We use PGSQL_CONNECT_FORCE_NEW, because in PHP 5.3 & PHPUnit
        // $this->prepareCache may be cleaned, but $this->link is still
        // not closed. So the next creation of DbSimple_Postgresql()
        // would use exactly the same connection as the previous, but with
        // empty $this->prepareCache, and it will generate "prepared statement 
        // xxx already exists" error each time we execute the same statement
        // as in the previous calls.
        $this->_resetLastError();
        if (!$ok) {
            $this->_setDbError('pg_connect("' . $dsnWithoutPass . '") error');
        }
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
            return "E'" . pg_escape_string($this->link, $s) . "'";
        }
        return '"' . str_replace('"', '_', $s) . '"';
    }

    /**
     * @param null $parameters
     *
     * @return array|null
     */
    protected function _performTransaction($parameters=null)
    {
        return $this->query('BEGIN');
    }

    /**
     * @param null $blobid
     *
     * @return DbSimple_Postgresql_Blob
     */
    protected function _performNewBlob($blobid = null)
    {
        return new DbSimple_Postgresql_Blob($this, $blobid);
    }


    protected function _performGetBlobFieldNames($result)
    {
        $blobFields = array();
        for ($i=pg_num_fields($result)-1; $i>=0; $i--) {
            $type = pg_field_type($result, $i);
            if (strpos($type, 'BLOB') !== false) $blobFields[] = pg_field_name($result, $i);
        }
        return $blobFields;
    }

    // TODO: Real PostgreSQL escape
    protected function _performGetPlaceholderIgnoreRe()
    {
        return '
            "   (?> [^"\\\\]+|\\\\"|\\\\)*    "   |
            \'  (?> [^\'\\\\]+|\\\\\'|\\\\)* \'   |
            /\* .*?                          \*/      # comments
        ';
    }

    protected function _performGetNativePlaceholderMarker($n)
    {
        // PostgreSQL uses specific placeholders such as $1, $2, etc.
        return '$' . ($n + 1);
    }

    protected function _performCommit()
    {
        return $this->query('COMMIT');
    }


    protected function _performRollback()
    {
        return $this->query('ROLLBACK');
    }


    protected function _performTransformQuery(&$queryMain, $how)
    {

        // If we also need to calculate total number of found rows...
        switch ($how) {
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
                        ((?:\s+ LIMIT \s+ \S+ \s* (?: OFFSET \s* \S+ \s*)? )?)  #5
                $/six';
                $m = null;
                if (preg_match($re, $queryMain[0], $m)) {
                    $queryMain[0] = $m[1] . $this->_fieldList2Count($m[2]) . " AS C" . $m[3];
                    $skipTail = substr_count($m[4] . $m[5], '?');
                    if ($skipTail) {
                        array_splice($queryMain, -$skipTail);
                    }
                }
                return true;
        }

        return false;
    }


    protected function _performQuery($queryMain)
    {
        $this->_lastQuery = $queryMain;
        $isInsert = preg_match('/^\s* INSERT \s+/six', $queryMain[0]);

        //
        // Note that in case of INSERT query we CANNOT work with prepare...execute
        // cache, because RULEs do not work after pg_execute(). This is a very strange
        // bug... To reproduce:
        //   $DB->query("CREATE TABLE test(id SERIAL, str VARCHAR(10)) WITH OIDS");
        //   $DB->query("CREATE RULE test_r AS ON INSERT TO test DO (SELECT 111 AS id)");
        //   print_r($DB->query("INSERT INTO test(str) VALUES ('test')"));
        // In case INSERT + pg_execute() it returns new row OID (numeric) instead
        // of result of RULE query. Strange, very strange...
        //

        if ($this->DbSimple_Postgresql_USE_NATIVE_PHOLDERS && !$isInsert) {
            // Use native placeholders only if PG supports them.
            $this->_expandPlaceholders($queryMain, true);
            $hash = md5($queryMain[0]);
            if (!isset($this->prepareCache[$hash])) {
                $prepared = @pg_prepare($this->link, $hash, $queryMain[0]);
                if ($prepared === false) {
                    return $this->_setDbError($queryMain[0]);
                }
                $this->prepareCache[$hash] = true;
            } else {
                // Prepare cache hit!
            }
            $result = pg_execute($this->link, $hash, array_slice($queryMain, 1));
        } else {
            // No support for native placeholders on INSERT query.
            $this->_expandPlaceholders($queryMain, false);
            $result = @pg_query($this->link, $queryMain[0]);
        }

        if ($result === false) {
            return $this->_setDbError($queryMain);
        }
        if (!pg_num_fields($result)) {
            if ($isInsert) {
                // INSERT queries return generated OID (if table is WITH OIDs).
                //
                // Please note that unfortunately we cannot use lastval() PostgreSQL
                // stored function because it generates fatal error if INSERT query
                // does not contain sequence-based field at all. This error terminates
                // the current transaction, and we cannot continue to work nor know
                // if table contains sequence-updateable field or not.
                //
                // To use auto-increment functionality you must invoke
                //   $insertedId = $DB->query("SELECT lastval()")
                // manually where it is really needed.
                //
                return @pg_last_oid($result);
            }
            // Non-SELECT queries return number of affected rows, SELECT - resource.
            return @pg_affected_rows($result);
        }
        return $result;
    }


    protected function _performFetch($result)
    {
        $row = @pg_fetch_assoc($result);
        if (pg_last_error($this->link)) {
            return $this->_setDbError($this->_lastQuery);
        }
        return $row;
    }


    protected function _setDbError($query)
    {
        return $this->_setLastError(null, $this->link? pg_last_error($this->link) : (is_array($query) ? 'Connection is not established' : $query), $query);
    }

    protected function _getVersion()
    {
    }
}


class DbSimple_Postgresql_Blob implements DbSimple_Blob
{
    private $blob; // resourse link
    private $id;
    private $database;

    public function __construct(&$database, $id = null)
    {
        $this->database =& $database;
        $this->database->transaction();
        $this->id = $id;
        $this->blob = null;
    }

    public function read($len)
    {
        if ($this->id === false) {
            return '';
        } // wr-only blob
        if (!($e=$this->_firstUse())) {
            return $e;
        }
        $data = @pg_lo_read($this->blob, $len);
        if ($data === false) {
            return $this->_setDbError('read');
        }
        return $data;
    }

    public function write($data)
    {
        if (!($e=$this->_firstUse())) {
            return $e;
        }
        $ok = @pg_lo_write($this->blob, $data);
        if ($ok === false) {
            return $this->_setDbError('add data to');
        }
        return true;
    }

    public function close()
    {
        if (!($e=$this->_firstUse())) {
            return $e;
        }
        if ($this->blob) {
            $id = @pg_lo_close($this->blob);
            if ($id === false) {
                return $this->_setDbError('close');
            }
            $this->blob = null;
        } else {
            $id = null;
        }
        $this->database->commit();

        return $this->id? $this->id : $id;
    }

    public function length()
    {
        if (!($e=$this->_firstUse())) {
            return $e;
        }

        @pg_lo_seek($this->blob, 0, PGSQL_SEEK_END);
        $len = @pg_lo_tell($this->blob);
        @pg_lo_seek($this->blob, 0, PGSQL_SEEK_SET);

        if (!$len) {
            return $this->_setDbError('get length of');
        }
        return $len;
    }

    protected function _setDbError($query)
    {
        $hId = $this->id === null? "null" : ($this->id === false? "false" : $this->id);
        $query = "-- $query BLOB $hId";
        $this->database->_setDbError($query);
    }

    // Called on each blob use (reading or writing).
    protected function _firstUse()
    {
        // BLOB opened - do nothing.
        if (is_resource($this->blob)) {
            return true;
        }

        // Open or create blob.
        if ($this->id !== null) {
            $this->blob = @pg_lo_open($this->database->link, $this->id, 'rw');
            if ($this->blob === false) {
                return $this->_setDbError('open');
            }
        } else {
            $this->id = @pg_lo_create($this->database->link);
            $this->blob = @pg_lo_open($this->database->link, $this->id, 'w');
            if ($this->blob === false) {
                return $this->_setDbError('create');
            }
        }
        return true;
    }
}
