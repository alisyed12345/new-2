<?php 
$mob_title = "Add Page";
include "../header.php";

if(!in_array("su_pages_add", $_SESSION['login_user_permissions'])){ 
  include "../includes/unauthorized_msg.php";
  return;
  }

?>
  <!-- Page header -->
  <div class="page-header page-header-default">
  <div class="page-header-content">
  <div class="page-title">
    <h4>Create New Page</h4>
  </div>
  </div>
  <div class="breadcrumb-line">
  <ul class="breadcrumb">
    <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
    <li><a href="<?php echo SITEURL."page/page_list" ?>"> Manage Pages</a></li>
    <li class="active">Create New Page</li>
  </ul>
  </div>
  </div>
  <!-- /page header --> 
  <!-- Content area -->
  <div class="content">
  <div class="row">
  <div class="col-lg-12">
    <form id="frmICK" class="form-validate-jquery" method="post">
      <div class="panel panel-flat">
        <div class="panel-body">
          <div class="ajaxMsg"></div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Page Name:<span class="mandatory">*</span></label>
                <input placeholder="Page Name" required name="page_name" id="page_name" required class="form-control" type="text">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Status:<span class="mandatory">*</span></label>
                <select name="active" id="active" required class="select form-control status">
                  <option value="">Select</option>
                  <option value="1">Active</option>
                  <option value="0">Inactive</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label>Content:<span class="mandatory">*</span></label>
                <p id="statusMsgcomm" style="color:red"></p>
                <textarea id="contents" name="contents" placeholder="Contents"  class="form-control "></textarea>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-10 text-right">
              <div class="ajaxMsgBot"></div>
            </div>
            <div class="col-md-2 text-right">
              <input type="hidden" name="action" value="add_page">
              <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner hide marR10 insidebtn"></i> Submit</button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  </div>
  </div>
  <!-- /Content area --> 
  <script>
  $(document).ready(function() {

    CKEDITOR.replace('contents', {
      height: 300,
      filebrowserUploadUrl: "../ajax/ckeditor_upload"
    });

  $('#frmICK').submit(function(e) {
      e.preventDefault();
      var getcode = CKEDITOR.instances.contents.getData();
      var ckvalue = getcode.replace(/<[^>]*>/gi, '').trim();
      ckvalue = ckvalue.replace(/&nbsp;/g, '');

      CKEDITOR.instances.contents.updateElement();
   

      if ($('#frmICK').valid() && ckvalue.length > 0) {
        $('#statusMsgcomm').html('');
        $('.spinner').removeClass('hide');
        var formData = new FormData(this);
        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-page';
        $.ajax({
          url: targetUrl,
          data: formData,
          type: 'POST',
          cache: false,
          contentType: false,
          processData: false,
          dataType: 'json',
          success: function(data) {
            $('.spinner').addClass('hide');
            if (data.code == 1) {
              displayAjaxMsg(data.msg, data.code);
              $("#frmICK")[0].reset();
              CKEDITOR.instances.contents.setData('');
              $('.status').change();


            } else {
              displayAjaxMsg(data.msg, data.code);
            }
          },
          error: function(data) {
            $('.spinner').addClass('hide');
            displayAjaxMsg(data.msg, data.code);
          }
        }, 'json');
      }else{
       if (ckvalue.length === 0) {
        $('#statusMsgcomm').html('Required Field');
        return false;
      } else {
        $('#statusMsgcomm').html('');
      }

      }
    });

    // CKEDITOR.instances.contents.on('change', function() { 
    //     var msgLength = CKEDITOR.instances.contents.getData();
    //     let tmp = document.createElement("DIV");
    //     tmp.innerHTML = msgLength;
    //     msgLength = tmp.textContent || tmp.innerText || "";
    //     msgLength = $.trim(msgLength.replace(/\&nbsp;/g, ''));
    //     if (msgLength !== ''){
    //       $('#contents-error').addClass('validation-valid-label');
    //     }
    //     else{
    //         $('#contents-error').removeClass('validation-valid-label');
    //     }
    //     });
  });

//  $('.select').on('change',function(){
//   if($(this).val()){
//     $('#active-error').addClass('validation-valid-label');
//   }
//   else{
//     $('#active-error').removeClass('validation-valid-label');
//   }
//  })
  
  function CKupdates(){
  for ( instance in CKEDITOR.instances )
      CKEDITOR.instances[instance].updateElement();
  }


  function CKupdate(){
  for ( instance in CKEDITOR.instances ){
      CKEDITOR.instances[instance].updateElement();
      CKEDITOR.instances[instance].setData('');
  }
  }

  </script>
  <?php include "../footer.php" ?>
