<?php

    /**
     * Function for connection to the MySQL Database
     */
    function databaseConnection() {
        
        try {    
            $dsn = 'mysql:host='. getenv('MYSQL_HOST') .';dbname='. getenv('MYSQL_DATABASE') .';charset=utf8';
            $mysql_connection = new PDO($dsn, getenv('MYSQL_USER'), getenv('MYSQL_PASSWORD'));
        } catch (Exception $err) {
            error_log($err->getMessage());
            $mysql_connection = false;
        }

        return $mysql_connection;
    }
?>