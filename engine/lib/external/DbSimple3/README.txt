DbSimple: Simplest but powerful interface to work with various relational databases.
(C) Dmitry Koterov, http://en.dklab.ru/lib/DbSimple/


ABSTRACT

There are a lot of PHP libraries which bring us unified database access 
interfaces: PEAR DB, ADOdb and PDO. Code written using these libraries is 
known to be very verbalize and is excessive overloaded by useless details. 
DbSimple introduces the interface much more simple and handy than above 
(and many other popular) abstraction libraries.


MAIN FEATURES

* Supports PHP 5, DBMS: MySQL, PostgreSQL, InterBase/FireBird.
* Simple and laconic interface (see samples below).
* Conditional macro-blocks in SQL body ({}-blocks), which allow to 
  dynamically generate even very complex queries without detriment to 
  code readability.
* Caching of query results (if necessary).
* Supports various placeholder (query arguments) types: list-based, 
  associative, identifier-based etc.
* Supports operation "select + count total number of resulting rows" 
  (for data displayed page-by-page).
* Functions for direct fetching: all result rows, one row, one column, 
  one cell, associative array, multi-dimensional associative array, 
  linked tree etc.
* Very handy interface to watch and debug query errors.
* Supports enhanced query logging (including query results and caller 
  line number).
* Supports "native" database placeholders and automatic optimization 
  "single prepare, multiple execute".
* Object-based BLOB access interface (if necessary).
* Library code is quite compact: one file is the base class, one file - 
  specific database driver. 

  
IDEOLOGY

* License LGPL (open-source).
* Library should not wrap differences in SQL language between different DBMS.
* Interface must be extremely laconic and handy for practical usage.
* "Query execution" and "result fetching" must be joined together into 
  single operation.
* If a query is built "piece by piece" (dynamically by PHP code), it must be 
  done without detriment to readability.
* Optimization "single prepare, multiple execute" must be performed transparently 
  and automatically. 

  
WHY NOT TO USE OTHER LIBRARY?

* PEAR DB, ADOdb: these libraries do not simplify DBMS access, they 
  simply provide us single (and heavy overloaded) access interface; debug 
  functions are too poor.
* PDO: requires PHP 5; not quite handy work with placeholders and query 
  results provided.
* Standart PHP functions for DBMS access: poor code readability, large 
  debugging discomfort, amenability to SQL Injections. 


LIBRARY INTERFACE (BRIEF)

mixed connect(string $dsn)
    Static function to connect ANY database using DSN syntax. 
    
mixed select(string $query [,$arg1...])
    Executes the query and returns the result as 2D-array. 

hash selectRow(string $query [,$arg1...])
    Fetches the result of single-row query. 
    
array selectCol(string $query [,$arg1...])
    Fetches one column. 
    
scalar selectCell(string $query [,$arg1...])
    Fetches one query result cell. 
    
mixed selectPage(int &$total, string $query [,$arg1...)
    Fetches 2D-array with total number of found rows calculation. 
    
mixed query(string $query [,$arg1...])
    Executes non-SELECT query; for auto-increment fields and INSERT 
    queries - returns last inserted ID. 
    
mixed transaction([mixed $parameters])
    Starts the new transaction. 
    
mixed commit() / mixed rollback()
    Commits/rollbacks the current transaction. 

In addition, to modify the format of result representation you may use 
reserved column aliases (ARRAY_KEY* etc.) and attributed SQL comments 
(e.g. for turning on caching). See usage synopsis below.


SYNOPSIS

Listing 1: Connect to DBMS

require_once "DbSimple/Generic.php";
$DB = DbSimple_Generic::connect("pgsql://login:password@host/database");

OR

require_once "DbSimple/Connect.php";
$DB = new DbSimple_Connect("pgsql://login:password@host/database");

Listing 2: Fetch all resulting rows

$rows = $DB->select('SELECT * FROM ?_user LIMIT 10');
foreach ($rows as $numRow => $row) {
    echo $row['user_name'];
}


Listing 3: Fetch one page

// Variable $totalNumberOfUsers will hold total number of found rows.
$rows = $DB->selectPage(
    $totalNumberOfUsers,
    'SELECT * FROM ?_user LIMIT ?d, ?d',
    $pageOffset, $numUsersOnPage
);


Listing 4: Macro-substitutions in SQL queries

$rows = $DB->select('
        SELECT *
        FROM goods
        WHERE 
            category_id = ?
          { AND activated_at > ? }
        LIMIT ?d
    ',
    $categoryId,
    (empty($_POST['activated_at'])? DBSIMPLE_SKIP : $_POST['activated_at']),
    $pageSize
);


Listing 5: Macro-substitutions in SQL queries #2

$rows = $DB->select('
        SELECT *
        FROM 
            goods g
          { JOIN category c ON c.id = g.category_id AND 1 = ? }
        WHERE 
            1 = 1
          { AND c.name = ? }
        LIMIT ?d
    ',
    (empty($_POST['cat_name'])? DBSIMPLE_SKIP : 1),
    (empty($_POST['cat_name'])? DBSIMPLE_SKIP : $_POST['cat_name']),
    $pageSize
);


Listing 6: Macro-substitutions in SQL queries #3

$rows = $DB->select('
        SELECT * FROM user
        WHERE 
            1=0 
          { OR user_id IN(?a) }
    ',
    $listOfUserIdsMayBeEmpty
    // If empty, resulted to 1=0 which means false.
);


Listing 7: Query result caching by time

$DB->setCacher('myCacher');
$row = $DB->select('
    -- CACHE: 10h 20m 30s
    SELECT * FROM table WHERE id=123
');

// Define caching function.
function myCacher($key, $value)
{
    // If $value !== null then we must store it to the cache with key $key.
    // If $value === null then we must return the value stored in the cache with key $key.
}


Listing 8: Query result caching with dependence on table modification

// Here forum.modified and topic.modified are TIMESTAMPs.
$row = $DB->select('
    -- CACHE: 10h 20m 30s, forum.modified, topic.modified
    SELECT * 
    FROM forum JOIN topic ON topic.forum_id=forum.id 
    WHERE id=123
');


Listing 9: Fetching of associative array

$rows = $DB->select('SELECT user_id AS ARRAY_KEY, ?_user.* FROM ?_user');
foreach ($rows as $userId => $userData) {
    echo $userData['user_name'];
}


Listing 10: List-based placeholder

$ids = array(1, 101, 303);
$DB->select('SELECT name FROM tbl WHERE id IN(?a)', $ids);
// SELECT name FROM tbl WHERE id IN(1, 101, 303)


Listing 11: Associative placeholder

$row = array(
  'id'   => 10,
  'date' => "2006-03-02"
);
$DB->query('UPDATE tbl SET ?a', $row);
// MySQL: UPDATE tbl SET `id`='10', `date`='2006-03-02'


Listing 12: Identifier-based placeholder

$DB->select('SELECT ?# FROM tbl', 'date');
// MySQL: SELECT `date` FROM tbl
// FireBird: SELECT "date" FROM tbl


Listing 13: Identifier-list-based placeholder

$user = array('user_id' => 101, 'user_name' => 'Rabbit', 'user_age' => 30);
$newUserId = $DB->query(
    'INSERT INTO user(?#) VALUES(?a)', 
    array_keys($row), 
    array_values($row)
);


Listing 14: {Prefix-based placeholder}

$DB->setIdentPrefix('phpbb_'); 
$DB->select('SELECT * FROM ?_user');
// SELECT * FROM phpbb_users


Listing 15: One row fetching

$row = $DB->selectRow('SELECT * FROM ?_user WHERE user_id=?', $uid);


Listing 16: One cell fetching

$userName = $DB->selectCell(
    'SELECT user_name FROM ?_user WHERE user_id=?', 
    $uid
);


Listing 17: One column fetching

$cities = $DB->selectCol('SELECT city_name FROM ?_cities');
$citiesById = $DB->selectCol(
    'SELECT city_id AS ARRAY_KEY, city_name FROM ?_cities'
);


Listing 18: Multi-dimensional associative array fetching

$messagesByTopics = $DB->select('
    SELECT 
        message_topic_id AS ARRAY_KEY_1,
        message_id AS ARRAY_KEY_2,
        message_subject, message_text
    FROM 
        ?_message
');
// $messagesByForumsAndTopics[topicId][messageId] = messageData


Listing 19: Linked tree fetching

$forest = $DB->select('
  SELECT 
    person_id        AS ARRAY_KEY, 
    person_father_id AS PARENT_KEY,
    * 
  FROM ?_person
');


Listing 20: prepare ... execute optimization

foreach ($array as $item) {
  // DbSimple underatands that it should execure "prepare" only once!
  $DB->query('INSERT INTO tbl(field) VALUES(?)', $item);
}


Listing 21: Error handling

// File connect.php
$DB = DbSimple_Generic::connect('mysql://test:test@localhost1/non-existed-db');
$DB->setErrorHandler('databaseErrorHandler');

function databaseErrorHandler($message, $info)
{
    if (!error_reporting()) return;
    echo "SQL Error: $message<br><pre>"; print_r($info); echo "</pre>";
    exit();
}

// As a result we will get:
SQL Error: Unknown MySQL Server Host 'localhost1' (11001) at .../connect.php line 17
Array
(
    [code] => 2005
    [message] => Unknown MySQL Server Host 'localhost1' (11001)
    [query] => mysql_connect()
    [context] => .../connect.php line 17
)


Listing 22: Temporary error disabling

// Please note the "@" operator usage!
// Also an unique index must be created for id field.
if (!@$DB->query('INSERT INTO tbl(id, field) VALUES(1, ?)', $field)) {
  // Here goes the reaction on the query error.
  // You may fetch error context using $DB->error property.
  $DB->query('UPDATE tbl SET field=? WHERE id=1', $field);
}


Listing 23: Query logging

$DB->setLogger('myLogger');
$rows = $DB->select('SELECT * FROM U_GET_PARAM_LIST');

function myLogger($db, $sql)
{
  $caller = $db->findLibraryCaller();
  $tip = "at ".@$caller['file'].' line '.@$caller['line'];
  // Print the query (of course it's better to use Debug_HackerConsole).
  echo "<xmp title=\"$tip\">"; print_r($sql); echo "</xmp>";
}

// Will be printed something like:
SELECT * FROM U_GET_PARAM_LIST;
  --- 13 ms = 4+3+6; returned 30 row(s);
