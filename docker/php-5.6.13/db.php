<?php
/**
 * Database configuration
 *
 * @link https://github.com/bluzphp/framework/wiki/Db
 * @return array
 */
return array(
    "connect" => array(
        "type" => "mysql",
        "host" => "database",
        "name" => "bluz",
        "user" => "bluz",
        "pass" => "bluz",
        "options" => array(
            \PDO::ATTR_PERSISTENT => true,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET utf8"
        )
    )
);
