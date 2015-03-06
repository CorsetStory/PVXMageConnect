Hello

<?php

include_once 'db.inc.php';
include_once 'db_helpers.inc.php';
include_once 'helpers.inc.php';

if (isset($_REQUEST['ItemCode'], $_REQUEST['Available']))
	
{
	//echo 'Itemcode & Availble Set';
	$result = InsertStringIntoDatabase($pdo, $_REQUEST['ItemCode'], $_REQUEST['Available']);
} 
else
{
		


		try {
			//echo '<br>Itemcode not set';
			$sql = 'select sku, qty, time_s from pvx_stockchange;';
			//echo '<br>'.$sql;
			
			$result = $pdo->query($sql);
			
			echo '<br>Result:<br>';
			//echo var_dump($result);
			include 'display.html.php';
			//exit();
		}  		
  		catch (PDOException $e)
		{	
		  echo 'Error fetching stock status: ' . $e->getMessage();
		  //include 'error.html.php';
		  exit();
		}
}