<?php
$mob_title = "Holidays";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
 if($_SESSION['icksumm_uat_login_usertypecode'] !== 'UT05'){ 
     include "../includes/unauthorized_msg.php";
    return;
}
?>

<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Holidays</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "parents/dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Holidays</li>
    </ul>
  </div>
 
</div>
<!-- /page header --> 
<!-- Content area -->
<div class="content">
  <div class="msg"></div>
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-flat">
        <div class="panel-body">
          <table class="table datatable-basic table-bordered">
            <thead>
              <tr>
                <th>Date</th>
        				<th>Reason</th>
        				<th>Group</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
$( document ).ready(function() {
	//FILL TABLE
	fillTable();
});

function fillTable(){
	var table = $('.datatable-basic').DataTable({
    autoWidth: false,
		destroy: true,
    pageLength: <?php echo TABLE_LIST_SHOW ?>,
		responsive: true,
		sProcessing:'',
		language: {
		   loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
		},
		ajax: '<?php echo SITEURL ?>ajax/ajss-holidays?action=list_all_holidays',
		'columns': [
			{ 'data': 'holiday_date',searchable: true,orderable: true },
			{ 'data': 'reason',searchable: true,orderable: true },
			{ 'data': 'for_group',searchable: true,orderable: true },
		],
		
    });
}
</script>
<?php include "../footer.php"?>
