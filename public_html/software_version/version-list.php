<?php 
$mob_title = "List Software Versions";
include "../header.php";

  ?>
<!-- Page header -->
<style>
	html, body {margin: 0; height: 100%; overflow: hidden}
</style>

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Software Versions</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
  <?php  if (!empty($current_session->id) && !empty($get_info->school_name) && !empty($get_info->new_registration_session) && !empty($version->major)) {
             ?>
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i>Dashboard</a></li>
      <li class="active">List Software Versions</li>
    </ul>
	<?php }else{?>
		  <div class="breadcrumb-line">
		  <ul class="breadcrumb">
			  <li><a href="<?php echo SITEURL ?>check_data" ><i class="glyphicon glyphicon-check"></i> Check Mandatory Information</a></li>
		  </ul>
	  </div>
	  <?php } ?>
     <?php if(check_userrole_by_code('UT01')){ ?>
		  <div class="above-content"> <a href="<?php echo SITEURL ?>software_version/version-create" class="pull-right btn btn-primary"><span>Add New Software Version</span></a> </div>
		  <?php } ?>
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
                <th>Major</th>
                <th>Minor</th>
                <th>Patch</th>
                 <th>Notification</th>
                <th>Status</th>
                <th class="text-center action_col"></th>
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
	//REMOVE STAFF
	$(document).on('click','.remove_version',function(data,status){
		if(confirm('Do you want to delete version?')){
			$('.spinner').removeClass('hide');
			
			var version_id = $(this).data('versionid');
						
			$.post('<?php echo SITEURL ?>ajax/ajss-software-version',{version_id:version_id,action:'delete_version'},function(data,status){
				if(status == 'success'){
					fillTable();
					displayAjaxMsg(data.msg,data.code);
				}else{
					displayAjaxMsg(data.msg,data.code);
				}
			},'json');
		}
	});
});

function fillTable(){
	var table = $('.datatable-basic').DataTable({
        autoWidth: false,
		destroy: true,
		pageLength: <?php echo TABLE_LIST_SHOW ?>,
		responsive: true,
		ajax: '<?php echo SITEURL ?>ajax/ajss-software-version?action=list_all_version',
		sProcessing:'',		
		language: {
		   loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
		},
		'columns': [
		{ 'data': 'major' },
		{ 'data': 'minor' }, 
		{ 'data': 'patch'},
		{ 'data': 'notification'},
		{ 'data': 'status'}
		],
		"order": [[ 1, "asc" ]],
		"columnDefs": [
		{
			"render": function ( data, type, row ) { 
				var btn = '';

				btn += "<a href='<?php echo SITEURL ?>software_version/version-edit?id=" + row['id'] + "' class='text-primary action_link overlay_link' title='Edit'>Edit</a>";

				btn += "<a href='javascript:;' data-versionid="+ row['id'] +" class='text-danger action_link remove_version' title='Delete'>Delete</a>";


				

				return btn;

			}, "targets": 5 },
			{ "visible": true,  "targets": [ 0 ] }
        ]
    });
}

/*<a href='javascript:void(0)' data-staffid = " + row['user_id'] + " class = 'text-danger remove_staff action_link'><i class = 'icon-trash'></i></a>*/
</script>
<?php include "../footer.php"?>
