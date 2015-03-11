<?php

define("PVXO_DEBUGMODE", True);
define("PVXO_USE_WORKAROUND", True);  // PVX 'Despatch summary' doesn't return 'No of items' - use this flagged to use OutStanding sales orders to correct!! 

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
	private $is_error;
	public $errormsg;
	public $db_ok;
	
		
	function __construct($clientID,$Username,$Password,$URL)
	{
		$this->debugmode = (defined('PVXO_DEBUGMODE') && PVXO_DEBUGMODE == True);
		$this->PVX = new PVX_API($clientID, $Username, $Password, $URL);

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
		$this->is_error = false;
		if ($this->PVX->LoggedIn())  { 
		
			$template_name='Despatch summary';
			$columns = '[Salesorder number],[Despatch number],[Despatch date],[Tracking number],[No of items]';
			$order_by = '[Despatch number]';
			$result = null;

			if($sinceDateTime) 
				{ $search="[Despatch date] > '".$sinceDateTime."'"; } 
			else 
				{ $search = null;}
			
			$page_no = 1;
			$more_pages = true;
			
			if($this->debugmode) { echo '<br><br>getDespatchedOrders: '.$search.'<br>'; }
			
			while ($more_pages && !$this->is_error)
			{
				$response = $this->PVX->GetReportData($template_name, $page_no, $search, $columns, $order_by);
				$explode = explode("\n", $response);
				
				if($this->debugmode) { echo "<BR>getDespatchedOrders: explode: <BR><PRE>".print_r($explode, true)."</PRE>"; }
				
				$first_row = true;
				$values = null;
				
				foreach($explode as $row) {
					//if($this->debugmode) { echo "<BR>getDespatchedOrders: row: <BR><PRE>".print_r($row, true)."</PRE>"; }
					$fields = explode(",", $row);
					//if($this->debugmode) { echo "<BR>getDespatchedOrders: fields: <BR><PRE>".print_r($fields, true)."</PRE>"; }
					if(!$first_row && count($fields) == 5) {
						
						$values .= "(".$fields[0].",".$fields[1].", STR_TO_DATE(".substr($fields[2],1,-1).",'%d/%m/%Y %H:%i'),".$fields[3].",".$fields[4].", 0),";
						//if($this->debugmode) { echo "<BR>getDespatchedOrders: values: <BR><PRE>".print_r($values, true)."</PRE>"; }
					}
					$first_row = false;
				}
				if($this->debugmode) { echo "<BR>getDespatchedOrders: FINAL values: <BR><PRE>".print_r($values, true)."</PRE>"; }
				$result = $this->DB_shipment($values);
				$page_no += 1;	
				$more_pages = $this->PVX->morePages;
				
				if(($this->debugmode) && ($page_no>2)) { 
					echo "<BR>getDespatchedOrders: ABORT - more than 2 pages in DebugMode<BR>";
					$more_pages = false;
				}
			} 
		}
		return (!$this->is_error) && (!$this->PVX->errorOccurred);
	} 
	
	private function DB_Shipment($values)
	{
	
	$sql = "insert into pvx_shipment (order_number, despatch_number, despatch_date,tracking_number, sku, qty ) values ".substr($values,0, -1).";";
	if($this->debugmode) { echo "<BR>getDespatchedOrders: FINAL values: <BR><PRE>".print_r($sql, true)."</pre>";}
	
	try
	{
		$result = $this->pdo->query($sql);
		
	}
	catch (PDOException $e)
	{
	  $this->errormsg = $e->getMessage();
	  $this->is_error = true;
	  return false;
	}
	
 	return $result;
}
}

