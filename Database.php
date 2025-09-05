<?php

class Database {
    private static $conn;

    public static function getConnection(): PDO {
        if (self::$conn === null) {
            $host = 'localhost';
            $dbname = 'pessoa';
            $user = 'root';
            $pass = 'root';
            $port = 3306;

            try {
                $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
                self::$conn = new PDO($dsn, $user, $pass);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
}