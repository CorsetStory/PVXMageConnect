<?php


// insert a string into a database table - assumes header rows.
function DB_AvailabilityChange($pdo, $sku, $qty)
{
	
	$sql = "insert into pvx_stockchange (sku, qty, time_s, status) values ('".$sku."',".$qty.", NOW(), 'Pending');";
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

function DB_Shipment($order_number, $despatch_number, $tracking_number, $sku, $qty)
{
	
	$sql = "insert into pvx_shipment (order_number, despatch_number, tracking_number, sku, qty, status)) values ('".$order_number."','".$despatch_number."','".$tracking_number."','".$sku."',".$qty.", 'Pending';)";
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
