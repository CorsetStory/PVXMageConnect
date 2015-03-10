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
		
	function __construct()
	{
		$this->debugmode = (defined('PVXO_DEBUGMODE') && PVXO_DEBUGMODE == True);
		$this->PVX = new PVX_API();
		
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
				$result .= $response;
				$page_no += 1;		
				if(($this->debugmode) && ($page_no > 1)) { return $result; }
			} 
		}
	return $result;	
	} 
}

