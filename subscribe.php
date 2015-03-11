<?php
define("subscribe_use_live", true);

//print_r($_COOKIE);

if ((isset($_REQUEST['login']) && $_POST['password']=="timlaurieonly"))
{
	//echo 'Correct Password!';
	setcookie("csltd_choc_cookie", "mint_choc_chip_with_banana_and_marmite", time()+360000);
	header("Refresh:0; url=subscribe.php");

}
else
{

if(isset($_COOKIE['csltd_choc_cookie']) && $_COOKIE['csltd_choc_cookie']=="mint_choc_chip_with_banana_and_marmite")

{

include_once 'classes/pvx.php';
include_once 'classes/pvx_orders.php';
include_once 'helpers.inc.php';

if (subscribe_use_live) {	
	define("SUBSCRIBE_CLIENT_ID", "corsetsuk2600");
	define("SUBSCRIBE_USER_NAME", "ReadOnly");
	define("SUBSCRIBE_PASSWORD", "r0enaldy14");
	define("SUBSCRIBE_URL", "http://emea.peoplevox.net/corsetsuk2600/resources/integrationservicev4.asmx");
	}
else
{	
	define("SUBSCRIBE_CLIENT_ID", "corsets2661Qa");
	define("SUBSCRIBE_USER_NAME", "ReadOnly");
	define("SUBSCRIBE_PASSWORD", "r0enaldy14");
	define("SUBSCRIBE_URL", "http://qa1.peoplevox.net/corsets2661Qa/resources/integrationservicev4.asmx");
}


if (isset($_REQUEST['GetDespatchedOrders']))
{


	$myPVX = new PVX_Order(SUBSCRIBE_CLIENT_ID, SUBSCRIBE_USER_NAME, SUBSCRIBE_PASSWORD,SUBSCRIBE_URL);

	$response = $myPVX->getDespatchedOrders($_REQUEST['despatch_date']);
	
	echo "<BR>GetDespatchedOrders RESULT: <BR><PRE>".print_r($response, true)."</PRE>";
}

if (isset($_REQUEST['subscribe']))
{


	$myPVX = new PVX_API(SUBSCRIBE_CLIENT_ID, SUBSCRIBE_USER_NAME, SUBSCRIBE_PASSWORD,SUBSCRIBE_URL);

	if ($myPVX->LoggedIn())  
	{ 

		$callbackUrl = $_REQUEST['subscribe_text'];
		$eventType = $_REQUEST['event_type'];

		if ($myPVX->subscribeEvent($eventType, $callbackUrl))
		{
			echo '<br>Callback set successfully';
		}
		else
		{
			echo '<br>Callback set FAILED';
		}
	}
}

if (isset($_REQUEST['unsubscribe']))
{


	$myPVX = new PVX_API(SUBSCRIBE_CLIENT_ID, SUBSCRIBE_USER_NAME, SUBSCRIBE_PASSWORD,SUBSCRIBE_URL);
	if ($myPVX->LoggedIn())  
	{ 

		$subscriptionID = $_REQUEST['unsubID'];

		
		if ($myPVX->unsubscribeEvent($subscriptionID))
		{
			echo '<br>unsubscribeEvent successful';
		}
		else
		{
			echo '<br>unsubscribeEvent failed';
		}
	}
}
	
if (isset($_REQUEST['get_data']))
{


	$myPVX = new PVX_API(SUBSCRIBE_CLIENT_ID, SUBSCRIBE_USER_NAME, SUBSCRIBE_PASSWORD,SUBSCRIBE_URL);
	
	if ($myPVX->LoggedIn())  
	{ 
		print_r($myPVX->GetData($_REQUEST['template_name'], 1, ''), true);
	}
	
}

if (isset($_REQUEST['get_reportdata']))
{


	$myPVX = new PVX_API(SUBSCRIBE_CLIENT_ID, SUBSCRIBE_USER_NAME, SUBSCRIBE_PASSWORD,SUBSCRIBE_URL);
	
	if ($myPVX->LoggedIn())  
	{ 
		$response = $myPVX->GetReportData($_REQUEST['template_name'], 1, $_REQUEST['search'], $_REQUEST['columns'], '');
		//echo $response;
		$explode = explode("\n", $response);
		echo "<PRE>".print_r($explode, true)."</PRE>";
	}
	
}

if (isset($_REQUEST['save_data']))
{


	$myPVX = new PVX_API(SUBSCRIBE_CLIENT_ID, SUBSCRIBE_USER_NAME, SUBSCRIBE_PASSWORD,SUBSCRIBE_URL);
	
	if ($myPVX->LoggedIn())  
	{ 
		$response = $myPVX->SaveData($_REQUEST['template_name'], $_REQUEST['csv_data']);
	
		echo "<PRE>SaveData Output - Response:".(($response)?$response:'FALSE')."</PRE>";
		echo "<PRE>SaveData Output - SaveDataDetail:".print_r($myPVX->SaveDataDetail, true)."</PRE>";
		echo "<PRE>SaveData Output - Partial Import?:".(($myPVX->partial_import) ? 'True' :'False')."</PRE>";
	
	}
	
}
?>

<H1>PVX Set up Event Capture for Stock Changes</H1>
<body>
	<H2>Set up a subscription in PVX</H2>
	<form action="?subscribe" method="post">
		Subscribe Text:<br>
		<input type=radio name="event_type" value="AvailabilityChanges" checked>AvailabilityChanges
		<input type=radio name="event_type" value="SalesOrderStatusChanges">SalesOrderStatusChanges
		<input type=radio name="event_type" value="TrackingNumberReceived">TrackingNumberReceived
		<BR>
		<input type=text name="subscribe_text" value="http://adhoc.corset-story.eu/?Event=Availability&ItemCode={ItemCode}&Available={Available}" size="100"><br> 
		<input type=submit value="Subscribe">
	</form>
	<H2>Remove a subscription in PVX</H2>
	<form action="?unsubscribe" method="post">ID to unsubscribe:<br>
		<input type=text name="unsubID", value="1"><br> 
		<input type=submit value="Unsubscribe">
	</form>
	<H2>Get Data Using the GetData method</H2>
	<form action="?get_data" method="post">
		GetData Template Name:<br>
		<input type=text name="template_name" value="Despatches" size="100"><br> 
		<input type=submit value="Get Data">
	</form>
	<H2>Get Data Using the GetReportData method</H2>
	<form action="?get_reportdata" method="post">
		Template Name:<br>
		<input type=text name="template_name" value="Despatch Summary" size="100"><br> 
		Columns:<br>
		<input type=text name="columns" value="[Salesorder number],[Requested delivery date],[Carrier],[Service],[Despatch date],[Despatch number],[Tracking number],[Item],[No of items],[Destination country]" size="100"><br>
		SearchClause:<br>
		<input type=text name="search" size="100"><br>
		<input type=submit value="Get Report Data">
	</form>
	<H2>Retrieve despatched items and put into the database</H2>
	<form action="?GetDespatchedOrders" method="post">Get Despatched Orders:<br>
		<br>Enter date to retrieve order after in format 'yyyy-mm-dd hh:mm'<br>
		 
		<input type=text name="despatch_date" value="2015-03-01 10:00" size="50"><br> 
		<input type=submit value="Get Despatched Orders">
	</form>
	<H2>Import Data Into PVX - use with care! especially LIVE!</H2>
	<form action="?save_data" method="post">
		Template Name to import into [PVX table]:<br>
		<input type=text name="template_name" value="Sales orders" size="150"><br> 
		CSV Data:<br>
		<textarea name="csv_data" cols=150 rows=10>"SalesOrderNumber","RequestedDeliveryDate","TelephoneNumber","ShipAdd1","ShipAdd2","ShipCity","ShipAddRegion","ShipAddPostcode","ShipAddCtry","InvAdd1","InvAdd2","InvAddCity","InvAddRegion","InvAddPostcode","InvAddCtry","IsPartial","ShipCost","Email","ContactName","TotalSale","Discount","TaxPaid","CreatedDate","ServiceType","PaymentMethod","OnHold"
"TIMTEST84112","11 Mar 2015 11:54:51","0555555","120 CHEMIN DES BURGER","","GRENOBLE","Isert","99999","France","marie baggy","120 CHEMIN DES BURGER","GRENOBLE","Isèrt","99999","France","FALSE","7.50","tim.rance@corsets-uk","tim tim","59.50","-13.00","0.00","11 Mar 2015 11:54:51","RM_AIRMAIL_EU_EU_1","paypal_express","TRUE"
</textarea>
		<br><input type=submit value="Import Data to PVX">
	</form>
</body>
</html>

<?php }
else
{
?>
	<H1>Private System</H1>
	<body>
	<H2>Enter password to continue</H2>
	<form action="?login" method="post">
		<input type=text name="password" value="" size="100">
		<input type=submit value="Subscribe">
	</form>	
	</body>
</html>
<?php
}
}
?>

