<?php
//echo 'hello from db';
try
{
  $pdo = new PDO('mysql:host=localhost;dbname=adhoc', 'adhoc', 'nGtE4t2Q');
  
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->exec('SET NAMES "latin1"');
  $pdo->exec('use adhoc');
  //echo 'connected to db!';
}
catch (PDOException $e)
{
  $error = 'Ha! Unable to connect to the database server.' . $e->getMessage();
  include 'error.html.php';
  exit();
}



