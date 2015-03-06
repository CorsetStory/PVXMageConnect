<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/PVXMageConnect/classes/pvx.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/PVXMageConnect/helpers.inc.php';


print('<H1>PVX Set up Event Capture for Stock Changes</H1>');

$myPVX = new PVX_API();

print ('<BR>Logged In: ');
if ($myPVX->LoggedIn() == false) { print 'NO'; } else 
{ 

	print 'YES<br>';
	print 'Set Subscribed Event....<br>';

	$callbackUrl = 'http://www.trsoft.co.uk/PVXMageConnect/?ItemCode={ItemCode}&Available={Available}';
	$eventType = 'AvailabilityChanges';

	if ($myPVX->subscribeEventSOAP($eventType, $callbackUrl))
	{
		echo '<br>Callback set successfully';
	}
	else
	{
		echo '<br>Callback set FAILED';
	}
}


