<?php

$mob_title = "List Basic Fees";

include "header.php";



//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN

if(!$_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin' || !$_SESSION['icksumm_uat_login_usertypesubgroup'] == 'principal' ){

include "../includes/unauthorized_msg.php";

return;

}



if(check_userrole_by_subgroup('admin') ){

	$config_data = $db->get_results("select * from ss_config order by `key`");

}else{

	$config_data = $db->get_results("select * from ss_config where private = 0 order by `key`");

}





?>

<!-- Page header -->



<div class="page-header page-header-default">

<div class="page-header-content">

	<div class="page-title">

		<h4>Configuration</h4>

	</div>

</div>

<div class="breadcrumb-line">

	<ul class="breadcrumb">

		<li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>

		<li class="active">Configuration</li>

	</ul>

</div>

</div>



<!-- /page header -->

<!-- Content area -->

<div class="content">

<div class="row">

	<div class="col-lg-12">

		<div class="panel panel-flat">

			<div class="panel-body">

				<div class="ajaxMsg"></div>

				<table class="table datatable-basic table-bordered">

					<thead>

						<tr>

							<th>KEY</th>

							<th>VALUE</th>

							<?php  if($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin'){ ?>

							<th>PRIVATE</th>

							<th>CREATE DATE - TIME</th>

								<?php } ?>

						</tr>

						</tr>

					</thead>

					<tbody>

						<?php foreach ($config_data as $row) { ?>

						<tr>

							<td><?php echo $row->key; ?></td>

							<td><?php echo $row->value; ?></td>

							<?php  if($_SESSION['icksumm_uat_login_usertypesubgroup'] == 'admin'){ ?>

								<td><?php echo ($row->private == 0)? 'No':'Yes'; ?></td>

							<td><?php echo date('m/d/Y - h:i A', strtotime($row->created_on)); ?></td>

							<?php } ?>

						</tr>

					<?php } ?>

					</tbody>

				</table>

			</div>

		</div>

	</div>

</div>

</div>





<!-- START SCHEDULE MODEL START -->



<?php include "../footer.php" ?>