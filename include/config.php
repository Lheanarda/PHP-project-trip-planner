<?php

$host = "localhost";
$username = "root";
$password = "";
$db = "pesonajawa2020";

try{
    $connection = new PDO("mysql:host=$host;dbname=$db", $username, $password);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $connection;
	
}catch (PDOException $exep){
    echo "Terjadi kesalah koneksi : " . $exep->getMessage();
}	

?>