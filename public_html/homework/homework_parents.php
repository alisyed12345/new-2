<?php 
$mob_title = "List Homework";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if(!check_userrole_by_code('UT05')){
	include "../includes/unauthorized_msg.php";
	return;
}
?>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Homework</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Homework</li>
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
                <th>ID</th>  
                <th>Date</th>
                <th>Student / Group</th>
                <th>Class</th>
                <th>Homework</th>
                <th>Attachment</th>                                
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
      responsive: true,
      ajax: '<?php echo SITEURL ?>ajax/ajss-homework?action=list_homework',
      sProcessing:'',		
      
      language: {
        loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>",
      },
      'columns': [
        { 'data': 'id' },
        { 'data': 'created_on',searchable: true,orderable: true },
        { 'data': 'group_name',searchable: true,orderable: true },
		    { 'data': 'class_name',searchable: true,orderable: true },
        { 'data': 'homework_text',searchable: true,orderable: true },
        { 'data': 'homework_attechment'},		
      ],
      "order": [[ 0, "desc" ]],
      "columnDefs": [
        { "visible": false,  "targets": [0] }
      ]
    });
}

/*<a href='javascript:void(0)' data-homeworkid = " + row['user_id'] + " class = 'text-danger remove_homework action_link'><i class = 'icon-trash'></i></a>*/
</script>
<?php include "../footer.php"?>
