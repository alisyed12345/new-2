<script>
  $('input').on('keypress', function(e) {
    if (this.value.length === 0 && e.which === 32) {
      return false;
    }
  });
  jQuery.validator.addMethod("spacenotallow", function(value, element) {
    var val = value.replace(/[\W_]/g, "");
    var val = jQuery.trim(val.replace(/\s/g, ""));

    if (!val) {
      return false;
    } else {
      return true;
    }


  }, "Space not allow");
</script>

<div id="overlay">
  <div id="overlay_text">
    <i class="icon-spinner9 spinner"></i><br />
    Loading<br />
    Please Wait
  </div>
</div>
<style type="text/css">
  label.error {
    color: red;
  }
</style>
<script>
  $(document).ready(function(e) {
    $(document).on('click', 'a.overlay_link, a.last_page, ul.breadcrumb a', function() {
      $('#overlay').css('display', 'block');
    });

    $('ul.navigation li a.navlink, a.logoutlink').click(function() {
      $('a.sidebar-mobile-main-toggle').trigger('click');
      $('#overlay').css('display', 'block');
    });
  });
</script>
</div>
<!-- /main content -->

</div>
<!-- /page content -->
</div>
<!-- /page container -->
<?php if ($_SESSION['icksumm_uat_login_total_roles_alloted'] > 1) { ?>
  <!-- Add Modal -->
  <div id="modal_switch_account" class="modal fade">
    <div class="modal-dialog">
      <div class="modal-content">
        <form name="frmSwitchAccount" id="frmSwitchAccount" method="post">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h5 class="modal-title">Switch Account</h5>
          </div>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <div class="form-group ">
                    <?php
                    // $role_ary = [];
                    // $user_types = $db->get_results("SELECT distinct t.id, t.user_type_group, t.user_type_subgroup FROM ss_usertypeusermap m 
                    // INNER JOIN ss_usertype t ON t.id = m.user_type_id WHERE m.user_id = '" . $_SESSION['icksumm_uat_login_userid'] . "' ");
                    // foreach ($user_types as $utype) {
                    //   if (!in_array($utype->user_type_group, $role_ary)) {
                    //     //$role_ary[$utype->id] = $utype->user_type_group; 
                    //     $role_ary[$utype->id] = ucwords(str_replace('_', ' ', $utype->user_type_subgroup));
                    //   }
                    // }
                    ?>
                    <select class="form-control select" style="width:100%" id="user_type" name="user_type" required>
                      <?php foreach ($user_types_sidebar as  $val) { ?>
                        <option value="<?php echo $val->id ?>" <?php echo $_SESSION['icksumm_uat_login_usertypegroup'] == $val->user_type_subgroup ? 'selected="selected"' : '' ?>><?php echo ucwords($val->user_type) ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-12 text-right">
                <div class="ajaxMsgBot_Switch"></div>
              </div>
              <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-success"><i class="icon-spinner2 spinner spinner_switch hide marR10 insidebtn"></i> Submit</button>
                <button type="button" class="btn btn-link" data-dismiss="modal">Close</button>
                <input type="hidden" name="action" value="switch_account">
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- /Add modal -->

  <script>
    $(document).ready(function() {
      $('#frmSwitchAccount').submit(function(e) {
        e.preventDefault();

        var targetUrl = '<?php echo SITEURL ?>ajax/ajss-authenticate';
        $('.spinner_switch').removeClass('hide');

        var formDate = $(this).serialize();
        $.post(targetUrl, formDate, function(data, status) {
          if (status == 'success') {
            if (data.code == 1) {
              window.location = data.url;
            } else {
              $('.ajaxMsgBot_Switch').html('Please try later');
              $('.spinner_switch').addClass('hide');
            }
          } else {
            $('.ajaxMsgBot_Switch').html('Please try later');
            $('.spinner_switch').addClass('hide');
          }
        }, 'json');
      });
    });
  </script>
<?php } ?>

<div class="modal fade" id="modal_version_notification" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                    <div class="modal-body">
					 <h5 class="modal-title">Software Update</h5>
                   <hr style="margin-top: 5px;margin-bottom: 5px;">
					 <div class="row">
					 <div class="col-md-12 col-lg-12">
					 <p><b>A New Version of <span id="version_product_name"><?php echo CENTER_SHORTNAME.' '.SCHOOL_NAME ; ?><span> is available!</b></p>
					<p><b>Release Notes:</b></p>
					<div style="border: solid 2px #ddd;padding:5px;">
					<p><b>Version <span id="current_version_name"></span> <span id="current_version_date"></span></b></p>
				 <div id='version_notification_details'></div>
					</div>	
						</div>  
					</div>
				</div>
                    <div class="modal-footer">
                        <div class="row">
                            <div class="col-md-12 col-xs-12 text-right">
                               <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                             </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>

<!-- Footer -->
<footer class="page-footer font-small blue"> 

  <!-- Copyright -->
  <div class="copyright-area ftdiv ftcontent">
    <div class="row">
      <div class="col-xl-3 col-lg-3 text-center">
        <div class="copyright-text">
          Copyright &copy <?php echo date('Y') ?> - All Rights Reserved
        </div>
      </div>
      <div class="col-xl-3 col-lg-3 text-center">
        <div class="copyright-text">
          <!-- Support Email <a href="mailto:support@bayyan.org">support@bayyan.org</a> -->
          Support Email <a href="javascript:void(0);" data-toggle="modal" data-target="#modalAddHelp"> <?php echo SUPPORT_EMAIL ?></a>
        </div>
      </div>
      <div class="col-xl-3 col-lg-3 text-center">
        <div class="copyright-text">
          Developed &amp; Maintained by <a href="https://www.bayyan.org/" target="_blank">Bayyan</a>
        </div>
      </div>
      <div class="col-xl-3 col-lg-3 text-center">
          <?php
            $VersionShow = $db->get_row("SELECT * FROM `ss_software_version` WHERE `status`='1' LIMIT 1");
            if (!empty($VersionShow->major)) {
                ?>
          <div class="copyright-text"><?php echo "Version " . $VersionShow->major . '.' . $VersionShow->minor . '.' . $VersionShow->patch;
                if ($VersionShow->notification=='1') {
                    echo ' <a href="javascript:void(0)" class="show_version_notification text-yellow" data-notification_date="'.date('m/d/Y', strtotime($VersionShow->created_on)).'" data-current_version_name="'.$VersionShow->major . "." . $VersionShow->minor . "." . $VersionShow->patch.'" data-notification_details="'.str_replace('"','',$VersionShow->describtion).'" ><i class="icon-info22"></i></a>' ;
                } ?> </div>
           <?php } ?>
        </div>
    </div>
  </div>
  <!-- Copyright -->

</footer>
<!-- Footer -->

<!-- Help MODEL START -->
<div class="modal fade" id="modalAddHelp" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form class="ibox-body" id="frmAddHelp" name="frmAddHelp" method="POST" action="javascript:;">

        <div class="modal-title" style="text-align: center;margin-top: 25px;">
          <div>
            <h5 id="curActiontitile" class="mb-0">Support Email</h5>
          </div>
        </div>

        <div class="modal-body">

          <div class="row">
            <div class="form-group col-12">
              <label for="name"> Name<span class="text-danger">*</span></label>
              <input id="name" maxlength="25" class="form-control required namealpha" type="text" data-msg-required="Required" lettersonly="true" name="name" value="" spacenotallow=true>
            </div>
          </div>
          <div class="row">

            <div class="form-group col-12">
              <label for="phone_no">Phone Number<span class="text-danger">*</span></label>
              <input type="text" maxlength="10" name="phone_no" id="phone_no" phoneUS="true" phonenocheck="true" class="form-control required phone" value="">
            </div>
          </div>
          <div class="row">
            <div class="form-group col-12">
              <label for="email">Email<span class="text-danger">*</span></label>
              <input type="email" maxlength="50" name="email" id="email" class=" email form-control required" value="">
            </div>
          </div>
          <div class="row">
            <div class="form-group col-12">
              <label for="message">Message<span class="text-danger">*</span></label>
              <textarea name="message" id="message" class="form-control required" spacenotallow=true></textarea>
            </div>
          </div>

          <div class="row">
            <div class="form-group text-right">
              <p id="sinlingMsg" style="color: green;"></p>
            </div>
            <div class="form-group col-12">
              <input type="hidden" name="action" value="email_support">
              <button type="submit" class="btn btn-primary app_btn_sm "><span>Submit</span></button>
              <button type="button" class="btn btn-defult gray x-small" data-dismiss="modal">Close</button>
            </div>
          </div>

        </div>
      </form>
    </div>
  </div>
</div>
<!-- Help MODEL END -->

<script>
  $(document).ready(function() {
    $('#phone_no').mask('000-000-0000');
    
    // jQuery.validator.addMethod("phonenocheck", function(value, element) {
    //     return this.optional(element) || /^([0-12]{1,12})$/i.test(value.length);
    // }, "Enter valid phone number");

    $.validator.addMethod('phonenocheck', function (value, element) {
    return this.optional(element) || /^\d{3}-\d{3}-\d{4}$/.test(value);
}, "Enter valid phone number");

    $('#modalAddHelp').on('show.bs.modal', function(e) {
      $('#sinlingMsg').html('');
      $('#frmAddHelp').trigger('reset');
      var validator = $("#frmAddHelp").validate();
      validator.resetForm();
    });
  
    //HELP SUBMIT EVENT
    $('#frmAddHelp').submit(function(e) {
      e.preventDefault();
      

      if ($('#frmAddHelp').valid()) {
        $('#sinlingMsg').html('Processing...');
        $('.spinner').removeClass('d-none');
        var formDate = $(this).serialize();
        $.post('<?php echo SITEURL ?>ajax/ajss-footer', formDate, function(data, status) {
          $('#sinlingMsg').html('');
          if (status == 'success') {
            if (data.code == 1) {
              $('#frmAddHelp').trigger('reset');
              //$('#modalAddHelp').modal('hide');
              $("#sinlingMsg").html(data.msg);
              setTimeout(function () {
                     $('#sinlingMsg').html(' ');
                     $('#modalAddHelp').modal('hide');
              }, 2000);
            } else {
              $("#sinlingMsg").html(data.msg);
            }
          } else {
            $("#sinlingMsg").html(data.msg);
          }
        }, 'json');

        //grecaptcha.reset();
      }
    });


    $('#btnBack').on('click', function(e) {
      $("#varify_code").val('');
      $("#frmSendText").hide();
      $("#frmMy").show();
      $("#data").hide();
      $("#sendTextMsg").hide();
    });

 $.fn.modal.prototype.constructor.Constructor.DEFAULTS.backdrop = 'static';
$.fn.modal.prototype.constructor.Constructor.DEFAULTS.keyboard =  false;

$(document).on('click', '.show_version_notification', function() {
  $('#version_notification_details').html($(this).data('notification_details'));
  $('#current_version_name').html($(this).data('current_version_name'));
  $('#current_version_date').html(' ('+$(this).data('notification_date')+')');
  $('#modal_version_notification').modal('show');
});

  });

  $(function() {

    $('body').on('keypress', '#phone_no', function(e) {
      var inputValue = event.which;

      if (inputValue > 31 && (inputValue < 48 || inputValue > 57))
        return false;

      return true;
    });
  });


    jQuery.validator.addMethod("spacenotallow", function (value, element) {
    var val = value.replace(/[\W_]/g, "");
    var val = jQuery.trim(val.replace(/\s/g, ""));

    if(!val){
        return false;
    }else{
        return true;
    }
  }, "Space not Allowed");
  get_notification_for_header();
  //GET NOTIFICATION UPDATES FOR HEADER
  setInterval(function() {
  get_notification_for_header();
  }, 300000);
  function get_notification_for_header() {

  var path = "<?php echo SITEURL ?>"; //local
  //var path = window.location.protocol + '//' + window.location.hostname;//Live

  $.post(path + '/ajax/ajss-admission-request?action=get_header_notif', function(data, status) {
  if (status == 'success') {

  $('#notification_count').html(data.notif_count);
  $('#notification_summary').html(data.notif_summary);

  }
  }, 'json');
  }
</script>

</body>

</html>