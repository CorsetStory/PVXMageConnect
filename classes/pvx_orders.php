<?php

define("PVXO_DEBUGMODE", True);
define("PVXO_USE_WORKAROUND", True);  // PVX 'Despatch summary' doesn't return 'No of items' - use this flagged to use OutStanding sales orders to correct!! 

include_once 'db.inc.php';
include_once 'db_helpers.inc.php';
include_once 'helpers.inc.php';
include_once 'display.inc.php';
include_once 'classes/pvx.php';


if (defined('TESTMODE') && TESTMODE == True)
{
// Test


}
else
{
// Live 


}

class PVX_Order
{
	
	private $PVX;
	private $debugmode;
	private $pdo;
	private $do_ok;
		
	function __construct()
	{
		$this->debugmode = (defined('PVXO_DEBUGMODE') && PVXO_DEBUGMODE == True);
		$this->PVX = new PVX_API();
		try
		{
		  $this->pdo = new PDO('mysql:host=localhost;dbname=adhoc', 'adhoc', 'nGtE4t2Q');
  
		  $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		  $this->pdo->exec('SET NAMES "latin1"');
		  $this->pdo->exec('use adhoc');
		  $this->db_ok = true;
		}
		catch (PDOException $e)
		{
		  $this->db_ok = false;
		  exit();
		}

	}
	
	public function getDespatchedOrders($sinceDateTime)
	{
		// sinceDateTime gets only despatches since that time - if not setup return all;
		if ($this->PVX->LoggedIn())  { 
		
			$template_name='Despatch summary';
			$columns = '[Salesorder number],[Despatch number],[Tracking number],[No of items]';
			$order_by = '[Despatch number]';
			$result = null;

			if($sinceDateTime) 
				{ $search="[Despatch date] > '".$sinceDateTime."'"; } 
			else 
				{ $search = null;}
			
			$page_no = 1;
			
			if($this->debugmode) { echo '<br><br>getDespatchedOrders: '.$search.'<br>'; }
			
			while ($response = $this->PVX->GetReportData($template_name, $page_no, $search, $columns, $order_by)) 
			{
				//echo 'hello...response:'.$response; 
				
				foreach($response as $row) {
					$fields = explode(",", $row);
					DB_Shipment($fields[0], $fields[1], $fields[2], $fields[3], 0);
				}
				//$result .= $response;
				$page_no += 1;		
				if(($this->debugmode) && ($page_no > 1)) { return $result; }
			} 
		}	
	} 
	
	function DB_Shipment( $order_number, $despatch_number, $tracking_number, $sku, $qty)
	{
	
	$sql = "insert into pvx_shipment (order_number, despatch_number, tracking_number, sku, qty, status) values ('".$order_number."','".$despatch_number."','".$tracking_number."','".$sku."',".$qty.", 'Pending');";
	echo $sql;
	
	try
	{
		$result = $this->pdo->query($sql);
		
	}
	catch (PDOException $e)
	{
	  return false;
	}
	
 	return $result;
}
}

