<?php
/**
 * DbSimple_Generic: universal database connected by DSN.
 * (C) Dk Lab, http://en.dklab.ru
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 * See http://www.gnu.org/copyleft/lesser.html
 *
 * Use static DbSimple_Generic::connect($dsn) call if you don't know
 * database type and parameters, but have its DSN.
 *
 * Additional keys can be added by appending a URI query string to the
 * end of the DSN.
 *
 * The format of the supplied DSN is in its fullest form:
 *   phptype(dbsyntax)://username:password@protocol+hostspec/database?option=8&another=true
 *
 * Most variations are allowed:
 *   phptype://username:password@protocol+hostspec:110//usr/db_file.db?mode=0644
 *   phptype://username:password@hostspec/database_name
 *   phptype://username:password@hostspec
 *   phptype://username@hostspec
 *   phptype://hostspec/database
 *   phptype://hostspec
 *   phptype(dbsyntax)
 *   phptype
 *
 * Parsing code is partially grabbed from PEAR DB class,
 * initial author: Tomas V.V.Cox <cox@idecnet.com>.
 *
 * Contains 3 classes:
 * - DbSimple_Generic: database factory class
 * - DbSimple_Generic_Database: common database methods
 * - DbSimple_Generic_Blob: common BLOB support
 * - DbSimple_Generic_LastError: error reporting and tracking
 *
 * Special result-set fields:
 * - ARRAY_KEY* ("*" means "anything")
 * - PARENT_KEY
 *
 * Transforms:
 * - GET_ATTRIBUTES
 * - CALC_TOTAL
 * - GET_TOTAL
 * - UNIQ_KEY
 *
 * Query attributes:
 * - BLOB_OBJ
 * - CACHE
 *
 * @author Dmitry Koterov, http://forum.dklab.ru/users/DmitryKoterov/
 * @author Konstantin Zhinko, http://forum.dklab.ru/users/KonstantinGinkoTit/
 *
 * @version 2.x $Id$
 */

/**
 * Use this constant as placeholder value to skip optional SQL block [...].
 */
if (!defined('DBSIMPLE_SKIP'))
	define('DBSIMPLE_SKIP', log(0));

/**
 * Names of special columns in result-set which is used
 * as array key (or karent key in forest-based resultsets) in
 * resulting hash.
 */
if (!defined('DBSIMPLE_ARRAY_KEY'))
	define('DBSIMPLE_ARRAY_KEY', 'ARRAY_KEY');   // hash-based resultset support
if (!defined('DBSIMPLE_PARENT_KEY'))
	define('DBSIMPLE_PARENT_KEY', 'PARENT_KEY'); // forrest-based resultset support

require_once __DIR__.'/Database.php';

/**
 * DbSimple factory.
 */
class DbSimple_Generic
{
    /**
     * DbSimple_Generic connect(mixed $dsn)
     *
     * Universal static function to connect ANY database using DSN syntax.
     * Choose database driver according to DSN. Return new instance
     * of this driver.
     */
    public static function connect($dsn)
    {
        // Load database driver and create its instance.
        $parsed = self::parseDSN($dsn);
        if (!$parsed) {
            return null;
        }
        $class = 'DbSimple_Driver_'.ucfirst($parsed['scheme']);
        if (!class_exists($class)) {
            $file = __DIR__ . '/Driver/'.ucfirst($parsed['scheme']). '.php';
            if (is_file($file)) {
                require_once($file);
            } else {
                trigger_error("Error loading database driver: no file $file", E_USER_ERROR);
                return null;
            }
        }
        $object = new $class($parsed);
        if (isset($parsed['ident_prefix'])) {
            $object->setIdentPrefix($parsed['ident_prefix']);
        }
        $object->setCachePrefix(md5(serialize($parsed['dsn'])));
        return $object;
    }


    /**
     * array parseDSN(mixed $dsn)
     * Parse a data source name.
     * See parse_url() for details.
     */
    public static function parseDSN($dsn)
    {
        if (is_array($dsn)) {
            return $dsn;
        }
        $parsed = parse_url($dsn);
        if (!$parsed) {
            return null;
        }
        $params = null;
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $params);
            $parsed += $params;
        }
        $parsed['dsn'] = $dsn;

        return $parsed;
    }
}


