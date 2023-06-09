<?php 
$mob_title = "Message";
include "../header.php";
//AUTHARISATION CHECK 
if (!in_array("su_communicate_sent_text_list", $_SESSION['login_user_permissions'])) { 
	include "../includes/unauthorized_msg.php";
	return;
}
?>
<!-- Page header -->
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Text Message Sent</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Text Message Sent</li>
    </ul>
  </div>
  <?php if (in_array("su_communicate_sent_text_create", $_SESSION['login_user_permissions'])) {   ?>
  <div class="above-content"> <a href="<?php echo SITEURL ?>message/mass_text_msg" class="pull-right"><span class="label label-danger">New Text Message</span></a> </div>
  <?php  } ?>
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
                <th>Bulk SMS ID</th>
                <th>Bulk SMS ID Encrypted</th>
                <th>Date</th>
                <th>Message</th>
                <th>To</th>
                <th>Msg Type</th>
                <th>Sent</th>
                <th>In Queue</th>
                <th>Failed</th>
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
      <h5 class="modal-title">Text Message</h5>
    </div>
    <div class="modal-body viewonly" id="mail_detail"></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
    </div>
  </div>
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
		var to = $(this).data('to');
		var msgtype = $(this).data('msgtype');
		var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';

		$('#mail_detail').html('<h5>Data loading, please wait...</h5>');
		$('#modal_mail_detail').modal('show');
				
		$.post(targetUrl,{msgid:msgid,to:to,msgtype:msgtype,action:'view_sms_detail'},function(data,status){
			if(status == 'success'){
				$('#mail_detail').html(data);
			}
		});
	});

	//REQUEUE MAILS TO RESEND
	$(document).on('click','.resend',function(){ 
		if(confirm('Are you sure you want to re-queue failed deliveries?')){
			var msgid = $(this).data('msgid');
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';
	
			$(this).parent().append(' Wait...');				
			$.post(targetUrl,{msgid:msgid,action:'resend_mass_sms'},function(data,status){
				if(status == 'success'){
					fillTable();
				}
			});
		}
	});
});

  $.extend( jQuery.fn.dataTableExt.oSort, {
"date-uk-pre": function ( a ) {
    var ukDatea = a.split('/');
    return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
},

"date-uk-asc": function ( a, b ) {
    return ((a < b) ? -1 : ((a > b) ? 1 : 0));
},

"date-uk-desc": function ( a, b ) {
    return ((a < b) ? 1 : ((a > b) ? -1 : 0));
}
} );

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
		ajax: '<?php echo SITEURL ?>ajax/ajss-message?action=mass_sms_history',
		'columns': [
		{ 'data': 'id' },
		{ 'data': 'msgid' },
		{ 'data': 'created_on',searchable: true,orderable: true},
		{ 'data': 'message',searchable: true,orderable: false},
		{ 'data': 'to',searchable: true,orderable: false},
		{ 'data': 'msg_type',orderable: false},
		{ 'data': 'sent',orderable: true,width: "8%"},
		{ 'data': 'in_queue',orderable: true,width: "8%"},
		{ 'data': 'failed',orderable: true,width: "8%"},
		
		],
		"order": [[ 0, "desc" ]],

		"columnDefs": [
		{
			"render": function ( data, type, row ) { 
				var links = '';

				if(row['failed'] > 0){
				<?php  if(in_array("su_communicate_sent_text_create", $_SESSION['login_user_permissions'])){ ?>
					links = "<a href='javascript:void(0)' data-msgid='" + row["msgid"] + "' class='text-primary action_link resend' title='Re-send Failed Messages'>Re-send Failed Messages</a>";
					<?php } ?>
				}else{
					links = '';
				}
                 
                <?php  if(in_array("su_communicate_send_text_view", $_SESSION['login_user_permissions'])){ ?>
				links = links + "<a href='javascript:void(0)' data-msgid='" + row["msgid"] + "' data-to='" + row["to"] + "' data-msgtype='" + row["msg_type"] + "' class='text-danger action_link viewdetail' title='View Text Message'>View Text Message</a>";
                <?php } ?>
				return links;
			}, "targets": 9 },
			{"targets":2, "type":"date-eu"}, 
			{ "visible": false,  "targets": [ 0,1 ] }
        ]
    });
}
</script>
<?php include "../footer.php"?>
