<?php 
$mob_title = "Message";
include "../header.php";

?>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Text Message Received</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Text Message Received</li>
    </ul>
  </div>
</div>
<!-- /page header --> 
<!-- Content area -->
<div class="content content-box">
<div class="row">
<div class="col-lg-12">
  <div class="panel panel-flat panel-flat-box">
    <div class="panel-body panel-body-box">
      <div class="row">
        <div class="col-lg-12">
          <div class="table-responsive">
            <table class="table table-bordered dataGrid">
              <thead>
                <tr>
                  <th>Bulk SMS Reply ID</th>
                  <th>Bulk SMS Reply ID Encrypted</th>
                  <th>Date</th>
                  <th>Sender Mobile</th>
                  <th>Message</th>
                  <th></th>
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
</div>
<!-- Modal - Mail Detail-->
<div id="modal_mail_detail" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h5 class="modal-title">Text Message Reply</h5>
      </div>
      <div class="modal-body viewonly" id="mail_detail"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- /Modal --> 
<!-- Modal - Reply Message-->
<div id="modal_mail_reply" class="modal fade">
  <div class="modal-dialog">
    <form name="frmReply" id="frmReply">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h5 class="modal-title" id="to_mobile_no">Send Reply</h5>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
                <label>Message <span id="charNum"></span></label>
                <textarea class="form-control required" id="message" name="message" onkeyup="countChar(this)" style="height:100px;" ></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
        	<div class="ajaxMsgBot"></div>
          <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i> Submit</button>
          <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
          <input type="hidden" id="rec_mobile_no" name="rec_mobile_no" value="" />
          <input type="hidden" name="action" value="send_reply_msg" />
        </div>
      </div>
    </form>
  </div>
</div>
<!-- /Modal --> 
</div>
</div>
<script>
$( document ).ready(function() {
	//FILL TABLE 
	fillTable();
	
	//FETCH MAIL DETAILS
	$(document).on('click','.viewdetail',function(){ 
		var msgid = $(this).data('msgid');
		var mobileno = $(this).data('mobileno');
		var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';

		$('#mail_detail').html('<h5>Data loading, please wait...</h5>');
		$('#modal_mail_detail').modal('show');
				
		$.post(targetUrl,{msgid:msgid,mobileno:mobileno,action:'view_sms_reply'},function(data,status){
			if(status == 'success'){
				$('#mail_detail').html(data);
			}
		});
	});
	
	//OPEN MODEL FOR REPLY
	$(document).on('click','.replymsg',function(){ 
		$('#rec_mobile_no').val($(this).data('mobileno'));
		$('#to_mobile_no').html('Send Reply to ' + $(this).data('mobileno'));
		$('#modal_mail_reply').modal('show');
	});

	//REQUEUE MAILS TO RESEND
	$('#frmReply').submit(function(e){
		e.preventDefault();
			
		if($('#frmReply').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';
			$('.spinner').removeClass('hide');
			
			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){					
				if(status == 'success'){
					if(data.code == 1){			
						$('#message').val('');
						
						displayAjaxMsg(data.msg,data.code);
					}else{
						displayAjaxMsg(data.msg,data.code);
					}
				}else{
					displayAjaxMsg('Error: Process failed');
				}
			},'json');
		}
	});
});

function fillTable(){ 
	var table = $('.dataGrid').DataTable({
        autoWidth: false,
		destroy: true,
    pageLength: <?php echo TABLE_LIST_SHOW ?>,
		sProcessing:'',		
		language: {
		   loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
		},
		responsive: true,
		ajax: '<?php echo SITEURL ?>ajax/ajss-message?action=mass_sms_reply',
		'columns': [
		{ 'data': 'id' },
		{ 'data': 'msgid' },
		{ 'data': 'created_on',searchable: true,orderable: true},
		{ 'data': 'sender_mobile_no',searchable: true,orderable: true},
		{ 'data': 'message',searchable: true,orderable: true},
		],
		"order": [[ 0, "desc" ]],
		"columnDefs": [
		{
			"render": function ( data, type, row ) { 
				var links = '';
       
				links = "<a href='javascript:void(0)' data-msgid='" + row["msgid"] + "' data-mobileno='" + row["sender_mobile_no"] + "' class='text-primary action_link replymsg' title='Send Reply Messages'>Send Reply Messages</a>";
  
        
        <?php  if(in_array("su_communicate_sent_text_view", $_SESSION['login_user_permissions'])){ ?>
				links = links + "<a href='javascript:void(0)' data-msgid='" + row["msgid"] + "' data-mobileno='" + row["sender_mobile_no"] + "' class='text-danger action_link viewdetail' title='View Text Message'>View Text Message</a>";
        <?php } ?>
				return links;
			}, "targets": 5 },
			{ "visible": false,  "targets": [ 0,1 ] }
        ]
    });
}
</script>
<?php include "../footer.php"?>
