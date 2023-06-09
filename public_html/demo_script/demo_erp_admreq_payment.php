<?php 
include_once "../includes/config.php";

$updatedrows = $db->query("UPDATE erp_admreq_payment SET credit_card_no = '', credit_card_exp = '', postal_code = '', bank_acc_no = '', routing_no = ''");

echo "<h2>Rows updated ".$updatedrows."</h2>";
?>
