<?php
define("pvx_use_live", false);
define("mage_use_live", false);

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

if (pvx_use_live) {	
	define("SUBSCRIBE_CLIENT_ID", "corsetsuk2600");
	define("SUBSCRIBE_USER_NAME", "ReadOnly");
	define("SUBSCRIBE_PASSWORD", "r0enaldy14");
	define("SUBSCRIBE_URL", "http://emea.peoplevox.net/corsetsuk2600/resources/integrationservicev4.asmx");
	}
else
{	
	define("SUBSCRIBE_CLIENT_ID", "corsetsQa2661");
	define("SUBSCRIBE_USER_NAME", "ReadOnly");
	define("SUBSCRIBE_PASSWORD", "r0enaldy14");
	define("SUBSCRIBE_URL", "http://peoplevox.net/corsetsQa2661/resources/integrationservicev4.asmx");
}


if (mage_use_live) {	
	define("MAGE_USER_NAME", "");
	define("MAGE_PASSWORD", "");
	define("MAGE_URL", "http://");
	}
else
{	
	define("MAGE_USER_NAME", "TimRance");
	define("MAGE_PASSWORD", "have_a_banana");
	define("MAGE_URL", "http://dev.corset-story.eu/api/v2_soap/?wsdl");
}

$myPVX = new PVX_API(SUBSCRIBE_CLIENT_ID, SUBSCRIBE_USER_NAME, SUBSCRIBE_PASSWORD,SUBSCRIBE_URL);

try {
	$myMage = new SoapClient(MAGE_URL); // TODO : change url
	$sessionId = $myMage->login(MAGE_USER_NAME, MAGE_PASSWORD); // TODO : change login and pwd if necessary
}
catch (Exception $e)
{
	echo 'Didn\'t login to Mage - sorry about that - message: \n'.$e->GetMessage();
}

$response = $myPVX->GetReportData('Item inventory summary', 1, '', '[Item code],[Available]', '');

//echo '<PRE>'.print_r($response, true).'</PRE>';

$explode = explode("\n", $response);  // split into rows.

//echo '<PRE>'.print_r($explode, true).'</PRE>';

foreach($explode as $row) {
	$row_explode = explode(",", $row);
	
	if (count($row_explode) == 2) {
		$data_result['sku'][] = str_replace("\"", "", $row_explode[0]);
		$data_result['qty'][] = str_replace("\"", "", $row_explode[1]);
		}
	}
	
	$pvx_sku_qty = array_combine($data_result['sku'], $data_result['qty']);
	
//echo '<PRE>'.print_r($pvx_sku_qty, true).'</PRE>'; // check it dumps the correct thing...
//echo '<PRE>'.print_r(array('12AW007;2X','12AW007;M'), true).'</PRE>';

$mage_result = $myMage->catalogInventoryStockItemList($sessionId, $data_result['sku']);

//echo '<PRE>'.print_r($mage_result, true).'</PRE>';

foreach($mage_result as $inventory_item) {
	//echo 'Magento sku: '.$inventory_item->sku.'; Magento qty: '.$inventory_item->qty;
	//echo 'SKU ['.$inventory_item->sku.']: (PVX: '.$pvx_sku_qty[$inventory_item->sku].', Mage: '.$inventory_item->qty.')</BR>';
	if($pvx_sku_qty[$inventory_item->sku] != $inventory_item->qty) {
		echo '['.$inventory_item->sku.'] - (PVX: '.$pvx_sku_qty[$inventory_item->sku].', Mage: '.$inventory_item->qty.')</BR>';
		}
	}


	
?>


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

