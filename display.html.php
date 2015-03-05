<!DOCTYPE html>
<html lang="en">
  <head>
     <title>Corset Story - PVX Magento 1.9 Integration System (BETA)</title>
  </head>
  <body>
  	
    </p>
     
    <table border=4 cellspacing=4 cellpadding=4>
    <tr>
    	<th>SKU</th>
    	<th>Qty</th>
    	<th>Timestamp</th>
    </tr>
    <?php foreach ($result as $row): ?>
    	<tr>
          <td><?php htmlout($row['sku']); ?></td>
          <td><?php htmlout($row['qty']); ?></td>
          <td><?php htmlout($row['time_s']); ?></td>-->
        </tr>
    <?php endforeach; ?>
    </table>
  </body>
</html>
