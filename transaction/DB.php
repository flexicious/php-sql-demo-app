<?php

/**
 * Created by PhpStorm.
 * User: 19.06.2013-7pm
 * Date: 06-Nov-15
 * Time: 2:55 PM
 */
class DB
{
    /**
     * strictly force you to close the connection externally
     * @return mysqli
     */

    public static $tableName = "server_records";

    public static function getConnection(){
        $serverName = "localhost";
        $username = "root";
        $password = "sa";
        $dbName = "sal_php";
        $conn = new mysqli($serverName, $username, $password, $dbName);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
}