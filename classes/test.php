<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/stock/classes/pvx.php';

print('<H1>PVX Class Test Script</H1>');

$myPVX = new PVX_API();

print ('<BR>Logged In: ');
if ($myPVX->LoggedIn() == false) { print 'NO'; } else { print 'YES';}

print ('<BR>');
print($myPVX->GetData('Purchase Orders', 1, ''));

