<?php


// insert a string into a database table - assumes header rows.
function InsertStringIntoDatabase($pdo, $sku, $qty)
{
	
	$sql = "insert into pvx_stockchange (sku, qty, time_s) values ('".$sku."',".$qty.", NOW());";
	//echo $sql;
	
	try
	{
		$result = $pdo->query($sql);
		
	}
	catch (PDOException $e)
	{
	  //echo $e;
	  exit();
	}
 
}
