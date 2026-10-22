<?php
$db_host     = "localhost:3306";
$db_username = "api";
$db_password = "api_password";
$db_name     = "pluralpaldb";

$db_conn = new mysqli($db_host, $db_username, $db_password, $db_name);
if ($db_conn->connect_error) { die("Connection failed: " . $db_conn->connect_error); }