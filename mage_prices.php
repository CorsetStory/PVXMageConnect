<?php

// This script resets special price for simples to blank.
// It relies on a table named 'price' containing a download copy of the sku, parent_sku and special price.
// This table was populated using uRapidFlow export.


define("mage_use_live", true);

//print_r($_COOKIE);

if ((isset($_REQUEST['login']) && $_POST['password']=="timlaurieonly"))
{
	//echo 'Correct Password!';
	setcookie("csltd_choc_cookie", "mint_choc_chip_with_banana_and_marmite", time()+360000);
	header("Refresh:0; url=mage_prices.php");

}
else
{

if(isset($_COOKIE['csltd_choc_cookie']) && $_COOKIE['csltd_choc_cookie']=="mint_choc_chip_with_banana_and_marmite")

{


header("Refresh:5; url=mage_prices.php");
echo 'Set simple prices to zero for these items....</br>';

include_once 'helpers.inc.php';
include_once  'db.inc.php';

if (mage_use_live) {	
	define("MAGE_USER_NAME", "tim_api_user");
	define("MAGE_PASSWORD", "have_a_banana_14");
	define("MAGE_URL", "http://admin.corset-story.eu/api/v2_soap/?wsdl");
	}
else
{	
	define("MAGE_USER_NAME", "tim_api_user");
	define("MAGE_PASSWORD", "have_a_banana_14");
	define("MAGE_URL", "http://stageadmin.corset-story.eu/api/v2_soap/?wsdl");
}

try {
	
	$myMage = new SoapClient(MAGE_URL); 
	$sessionId = $myMage->login(MAGE_USER_NAME, MAGE_PASSWORD); 

	
	$store_ids = array(6);
		
		foreach ($store_ids as $store_id)
		{
			
			$sql = "select s.sku from price s 
					inner join price_google_feed pgf on pgf.sku = s.sku and pgf.store_id = s.store_id
					where s.special_price > 0 and s.parent_sku <> '' and s.store_id = ".$store_id."  and s.sku not in 
					(select pu.sku from price_updates pu where pu.sku = s.sku and pu.store_id = ".$store_id.")
					limit 10;";
			
			
			//$sql = "select s.sku from price s
			//		where s.special_price > 0 and s.parent_sku <> '' and s.store_id = ".$store_id." and s.sku not in 
			//		(select pu.sku from price_updates pu where pu.sku = s.sku and pu.store_id =".$store_id.")
			//		limit 100";
			$result = $pdo->query($sql);
			foreach ($result as $row)
			{
			// 13 - GBP, 14 - USD, 15 - AUD, 16 - BRL, 17 - JPY, 18 - GBP wholesale, UR stores: 1 - EN, 2 - FI, 3 - IT, 4 - NL, 5 - PT, 6 FR, 7 -ES, 8 - DE
				echo 'update store_id: '. $store_id. '; sku: '.$row['sku'].'....';
		
		
				$mage_result = $myMage->catalogProductSetSpecialPrice($sessionId, $row['sku'], '', null, null,  $store_id, 'sku');
				echo ($mage_result ? 'success; ' : 'fail; ').'</br>';	
				if ($mage_result) {
					$sql2 = "insert into price_updates (sku, store_id) values ('". $row['sku'] ."', " . $store_id . ")";
					$result2 = $pdo->query($sql2);
				}
				
			}
		}
		echo '</br>Next set will be loaded automatically...</br>';
}
catch (Exception $e)
{
	$error = 'There a silly old error......' . $e->getMessage();
	include 'error.html.php';
  	exit();
}

	
?>


<?php }
else
{
?>
	<H1>Private System</H1>
	<body>
	<H2>Enter password to continue</H2>
	<form action="login" method="post">
		<input type=text name="password" value="" size="100">
		<input type=submit value="Subscribe">
	</form>	
	</body>
</html>
<?php
}
}
?>

