<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env');
$dotenv->Load();
class DBConnection
{
    private $conn;
    function __construct()
    {

    }
    public function mConnect()
    {
        $this->conn = mysqli_connect($_ENV['DB_CONNECTIONSTRING'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']);
        if (mysqli_connect_errno()) {
            echo "Error" . mysqli_connect_error();
        }
        return $this->conn;
    }
}