<?php

include_once 'db.inc.php';
include_once 'db_helpers.inc.php';
include_once 'helpers.inc.php';
include_once 'display.inc.php';

if (isset($_REQUEST['Event'], $_REQUEST['ItemCode'], $_REQUEST['Available']) && $_REQUEST['Event'] == 'Availability' )
{
	//echo 'Itemcode & Availble Set';
	$result =  DB_AvailabilityChange($pdo, $_REQUEST['ItemCode'], $_REQUEST['Available']);
} 
elseif (isset($_REQUEST['Event']) && $_REQUEST['Event'] == 'Shipment' )
{
	$result =  DB_Shipment($pdo, $_REQUEST['OrderNumber'], $_REQUEST['DespatchNumber'],$_REQUEST['TrackingNumber'], $_REQUEST['SKU'],$_REQUEST['Qty'] );
}
else
{
	try {
		
		$sql = 'select sku, qty, time_s, status from pvx_stockchange;';
		$result = $pdo->query($sql);
		$body = display_stock($result);

		$sql = 'select order_number, despatch_number, tracking_number, sku, qty, status from pvx_shipment;';
		$result = $pdo->query($sql);
		$body .= display_shipment($result);
		
		include 'display.html.php';
	}  		
	catch (PDOException $e)
	{	
	  echo 'Error fetching stock status: ' . $e->getMessage();
	  exit();
	}	
}
