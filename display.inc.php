<?php
//include_once 'helpers.inc.php';

function display_stock($result) {

	$body = "<table border=4 cellspacing=4 cellpadding=4>
			<tr>
				<th>SKU</th>
				<th>Qty</th>
				<th>Timestamp</th>
				<th>Status</th>
			</tr>";
	foreach ($result as $row) {
		$body .= "<tr>
					  <td>".$row['sku']."</td>
					  <td>".$row['qty']."</td>
					  <td>".$row['time_s']."</td>
					  <td>".$row['status']."</td>
				</tr>";
	}
	$body .= "</table>";
	return $body;
}
 
 function display_shipment($result) {

	$body = '<table border=4 cellspacing=4 cellpadding=4>
			<tr>
				<th>Order No</th>
				<th>Despatch No</th>
				<th>Tracking No</th>
				<th>SKU</th>
				<th>Qty</th>
				<th>Status</th>
			</tr>';
	foreach ($result as $row) {
		$body .= '<tr>
					  <td>'.$row['order_number'].'</td>
					  <td>'.$row['despatch_number'].'></td>
					  <td>'.$row['tracking_number'].'</td>
					  <td>'.$row['sku'].'</td>
					  <td>'.$row['qty'].'</td>
					  <td>'.$row['status'].'</td>
				</tr>';
	}
	$body .= '</table>';
	return $body;
}
 