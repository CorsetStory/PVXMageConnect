<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PVXMageConnect/classes/pvx.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/PVXMageConnect/helpers.inc.php';


if (isset($_REQUEST['subscribe']))
{


	$myPVX = new PVX_API();

	if ($myPVX->LoggedIn())  
	{ 

		$callbackUrl = $_REQUEST['subscribe_text'];
		$eventType = 'AvailabilityChanges';

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


	$myPVX = new PVX_API();

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


	$myPVX = new PVX_API();

	if ($myPVX->LoggedIn())  
	{ 
		print_r($myPVX->GetData($_REQUEST['template_name'], 1, ''), true);
	}
	
}

if (isset($_REQUEST['get_reportdata']))
{


	$myPVX = new PVX_API();

	if ($myPVX->LoggedIn())  
	{ 
		print_r($myPVX->GetReportData($_REQUEST['template_name'], 1, $_REQUEST['search'], $_REQUEST['columns'], ''), true);
	}
	
}

?>

<html lang="en">
<H1>PVX Set up Event Capture for Stock Changes</H1>
<body>
	<form action="?subscribe" method="post">
		Subscribe Text:<br>
		<input type=text name="subscribe_text" value="http://www.trsoft.co.uk/PVXMageConnect/?ItemCode={ItemCode}&Available={Available}" size="100"><br> 
		<input type=submit value="Subscribe">
	</form>
	<p></p>
	<form action="?unsubscribe" method="post">ID to unsubscribe:<br>
		<input type=text name="unsubID", value="1"><br> 
		<input type=submit value="Unsubscribe">
	</form>
	<p></p>
	<form action="?get_data" method="post">
		GetData Template Name:<br>
		<input type=text name="template_name" value="Despatches" size="100"><br> 
		<input type=submit value="Get Data">
	</form>
	<form action="?get_reportdata" method="post">
		Template Name:<br>
		<input type=text name="template_name" value="Despatch Summary" size="100"><br> 
		Columns:<br>
		<input type=text name="columns" value="[Salesorder number], [Despatch Number], [Tracking number], [Item]" size="100"><br>
		SearchClause:<br>
		<input type=text name="search" size="100"><br>
		<input type=submit value="Get Report Data">
	</form>
</body>
</html>
		


