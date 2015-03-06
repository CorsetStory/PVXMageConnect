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
		<input type=text name="unsubID", value="13"><br> 
		<input type=submit value="Unsubscribe">
	</form>
</body>
</html>
		


