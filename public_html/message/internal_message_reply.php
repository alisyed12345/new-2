<?php 
$mob_title = "Reply Message";
include "../header.php";

if ($_SESSION['icksumm_uat_login_usertypecode'] != 'UT05' && !in_array("su_internal_msg_reply", $_SESSION['login_user_permissions'])) {
  include "../includes/unauthorized_msg.php";
  return;
}
$message = $db->get_row("select * from ss_message where md5(id) = '".$_GET['id']."'");
?>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Reply Message</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo $SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li><a href="<?php echo $SITEURL."inertnal_message_list" ?>">Received Internal Message</a></li>
      <li class="active">Reply Message</li>
    </ul>
  </div>
</div>
<!-- /page header --> 
<!-- Content area -->
<div class="content content-box">
  <div class="panel panel-flat panel-flat-box">
    <div class="panel-body panel-body-box">
      <div class="row">
        <div class="col-lg-12">
          <table class="table table-bordered dataGrid message_list">
            <thead>
              <tr>
                <th>Message ID</th>
                <th>Message</th>
              </tr>
            </thead>
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
      <?php 
      if($message->rec_user_id == $_SESSION['icksumm_uat_login_userid'] || $message->created_by_user_id == $_SESSION['icksumm_uat_login_userid']){ ?>
      <form id="frmICK" class="form-validate-jquery" method="post">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <textarea class="form-control required" id="message" placeholder="Enter your message here" name="message"></textarea>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-10 text-right">
            <div class="form-group">
              <div class="ajaxMsgBot"></div>
            </div>
          </div>
          <div class="col-md-2 text-right">
            <div class="form-group">
              <input type="hidden" name="user_type_id" value="<?php echo $_SESSION['icksumm_uat_login_userid'] ?>">
              <input type="hidden" name="user_type_code" value="<?php echo $_SESSION['icksumm_uat_login_usertypecode'] ?>">
              <input type="hidden" name="action" value="reply_message">
              <input type="hidden" name="mid" value="<?php echo $_GET['id'] ?>">
              <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Send</button>
            </div>
          </div>
        </div>
      </form>
      <?php } ?>  
    </div>
  </div>
</div>
<!-- /Content area --> 
<script>
$(document).ready(function(e) {
	//FILL TABLE 
	fillTable();
	
	$('#frmICK').submit(function(e){
        e.preventDefault();
		
		if($('#frmICK').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';
			$('.spinner').removeClass('hide');
			
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){					
				if(status == 'success'){
					if(data.code == 1){			
						$('#message').val('');
						displayAjaxMsg(data.msg,data.code);
						fillTable();
					}else{
						displayAjaxMsg(data.msg,data.code);
					}
				}else{
					displayAjaxMsg('Error: Process failed');
				}
			},'json');
		}
	});
	
	//$('.bootstrap-select').selectpicker();
});

function fillTable(){ 
	var table = $('.dataGrid').DataTable({
        autoWidth: false,
		destroy: true,
		sProcessing:'',		
		language: {
		   loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
		},
		responsive: false,
		ajax: '<?php echo SITEURL ?>ajax/ajss-message?action=list_rec_messages&mid=<?php echo $_GET['id'] ?>',
		'columns': [
		{ 'data': 'id',orderable: true},
		{ 'data': 'message',searchable: true,orderable: true},
		],	
		"order": [[0, "desc" ]],
		"columnDefs": [
			{ "visible": false,  "targets": [ 0 ] }
        ]	
    });
}
</script>
<?php include "../footer.php" ?>
