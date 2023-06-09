<?php
$mob_title = "Payment Credentials";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if (!check_userrole_by_code('UT05')) {
    include "../includes/unauthorized_msg.php";
    return;
}

$paymentcred = $db->get_row("select * from ss_paymentcredentials where family_id='" . $_SESSION['icksumm_uat_login_familyid'] . "'");
$paymentcred_id = $paymentcred->id;

$credit_card_type = base64_decode($paymentcred->credit_card_type);
$credit_card_no = str_replace(' ', '', base64_decode($paymentcred->credit_card_no));
$credit_card_exp = base64_decode($paymentcred->credit_card_exp);
$credit_card_cvv = base64_decode($paymentcred->credit_card_cvv);

$credit_card_expAry = explode('-', $credit_card_exp);
$credit_card_exp_month = $credit_card_expAry[0];
$credit_card_exp_year = $credit_card_expAry[1];

//$postal_code = $paymentcred->postal_code;
$bank_acc_no = base64_decode($paymentcred->bank_acc_no);
$routing_no = base64_decode($paymentcred->routing_no);

?>

<!-- Page header -->
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4>Edit Payment Information</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Edit Payment Information</li>
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
            <legend class="text-semibold"><i class="icon-coin-dollar position-left"></i> Payment Credentials</legend>
            <div class="row hide">
              <div class="col-md-12">
                <div class="form-group">
                  <label class="radio-inline">
                    <input type="radio" name="payment_method" id="credit_card" <?php echo $credit_card_type == '' ? '' : 'checked="checked"' ?> value="credit_card">
                    Credit Card </label>
                  <?php /* <label class="radio-inline">
                    <input type="radio" name="payment_method" id="auto_deduction_acc" <?php echo $bank_acc_no == '' ? '' : 'checked="checked"' ?> value="auto_deduction_acc">
                    Automatic Dedecution From Checking Account </label> */ ?>
                </div>
              </div>
            </div>
            <div class="row <?php echo $credit_card_type == '' ? 'hide' : '' ?>" id="row_credit_card_info">

              <div class="col-md-3">
                <div class="form-group">
                  <label>Last 4 Digits of Credit Card</label>
                  <input id="cc_last4digits" class="form-control" readonly value="************<?php echo substr($credit_card_no,-4) ?>" type="text">
                 </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Expiry</label>
                  <input id="expiry" class="form-control" readonly value="<?php echo $credit_card_exp_month.'/'.$credit_card_exp_year ?>" type="text">
                  </div>
              </div>
            </div>
            <div class="row <?php echo $credit_card_type == '' ? 'hide' : '' ?>" id="row_credit_card">


                 <input type="hidden" name="credit_card_type" id="credit_card_type">

              <div class="col-md-3">
                <div class="form-group">
                  <label>Credit Card No <span class="mandatory">*</span></label>
                  <div class="input-group">
                   <input placeholder="Credit Card No" maxlength="16" creditCardNoCheck="true" value="" id="credit_card_no"
                    name="credit_card_no" class="form-control required" type="password">
										<span class="input-group-addon cceye cursor_default"><i class="icon-eye"></i></span>
											</div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Expiration Month <span class="mandatory">*</span></label>
                  <select name="credit_card_exp_month" creditCardExpMonthCheck="true" id="credit_card_exp_month" class="form-control">
                    <option value="">Select Month</option>
                    <?php for ($i = 1; $i <= 12; $i++) {?>
                    <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?>"><?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?></option>
                    <?php }?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Expiration Year <span class="mandatory">*</span></label>
                  <select name="credit_card_exp_year" creditCardExpYearCheck="true" id="credit_card_exp_year" class="form-control">
                    <option value="">Select Year</option>
                    <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++) {?>
                    <option value="<?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?>"><?php echo str_pad($i, 2, "0", STR_PAD_LEFT) ?></option>
                    <?php }?>
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>CVV <span class="mandatory">*</span></label>
                   <div class="input-group">
                   <input placeholder="Credit Card No" maxlength="4" name="credit_card_cvv" id="cvv" checkCreditCardCVV="credit_card_type" value="" class="form-control required" type="password">
                    <span class="input-group-addon cceye1 cursor_default"><i class="icon-eye"></i></span>
                   </div>
                </div>
              </div>
            </div>
            <div class="row <?php echo $bank_acc_no == '' ? 'hide' : '' ?>" id="row_auto_deduction_acc">
              <div class="col-md-3">
                <div class="form-group">
                  <label>Bank Account Number <span class="mandatory">*</span></label>
                  <input placeholder="Bank Account Number" maxlength="22" 
            value="<?php echo $bank_acc_no ?>" name="bank_acc_no" id="bank_acc_no" class="form-control" type="text">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label>Routing Number <span class="mandatory">*</span></label>
                  <input placeholder="Routing Number" maxlength="16" NewRoutingNoCheck="true" value="<?php echo $routing_no ?>" name="routing_no" id="routing_no" class="form-control" type="text">
                </div>
              </div>
              <div class="col-md-3 col-xs-6 pt-20" id="td_bankname"></div>
            </div>
            <div class="row">
              <div class="col-md-10 text-right">
                <div class="ajaxMsgBot"></div>
              </div>
              <div class="col-md-2 text-right">
                <input type="hidden" name="action" value="save_pay_info">
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
     cardFormValidate();

    //VALIDATION - CVV
    jQuery.validator.addMethod("checkCreditCardCVV", function(value, element, params) {
        if($('#'+params).val() == "Amex"){ 
            return this.optional(element) || /^[0-9]{4}$/i.test(value);
        }else{
            return this.optional(element) || /^[0-9]{3}$/i.test(value);
        }
    }, "Enter valid CVV number");

    //VALIDATION - CREDIT CARD NUMBER
    jQuery.validator.addMethod("creditCardNoCheck", function(value, element) {
        if (cardFormValidate()) {
            return true;
        } else {
            return false;
        }
    }, "Invalid credit card");


    //VALIDATION ROUTING NO
    jQuery.validator.addMethod("NewRoutingNoCheck", function(value, element) {
        return this.optional(element) ||
            /^((0[0-9])|(1[0-2])|(2[1-9])|(3[0-2])|(6[1-9])|(7[0-2])|80)([0-9]{7})$/i.test(value);
    }, "Enter valid routung number");



$('#routing_no').blur(function() {
        if ($.trim($('#routing_no').val()) != '') {
            //$('#td_bankname').html('Processing...');

            $.get('https://www.usbanklocations.com/crn.php?q=' + $.trim($('#routing_no').val()),
                function(data, status) {
                    if (status == 'success') {
                        try {
                            if (data.indexOf('ublcrnright') != -1 || data.indexOf(
                                    'is valid bank routing number') != -1) {

                                var nameStartsWith = "<tr><td><b>Name:</b></td><td>";
                                var nameEndsWith = "</td>";
                                bankName = data.substring(data.indexOf(nameStartsWith) +
                                    nameStartsWith.length);
                                bankName = bankName.substring(0, bankName.indexOf(nameEndsWith));
                                bankName = bankName.replace("</a>", "");
                                if (bankName.indexOf(">") != -1) {
                                    bankName = bankName.substring(bankName.indexOf(">") + 1);
                                }
                            } else {
                                bankName = '';
                            }
                        } catch (ex) {
                            bankName = '';
                        }

                        if (bankName == '') {
                            $('#td_bankname').html("");
                        } else {
                            $('#td_bankname').html(
                                "<h4 class='text-success mt-0 mb-0'>Bank Name: " + bankName +
                                "</h4>");
                        }
                    }
                });
        }
    });



    function cardFormValidate() {
        var cardValid = false;

        // Card number validation
        $('#credit_card_no').validateCreditCard(function(result) {
            var cardType = (result.card_type == null) ? '' : result.card_type.name;
            if (cardType == 'Visa') {
                var backPosition = result.valid ? '2px -163px, 260px -87px' : '2px -163px, 260px -61px';
            } else if (cardType == 'MasterCard') {
                var backPosition = result.valid ? '2px -247px, 260px -87px' : '2px -247px, 260px -61px';
            } else if (cardType == 'Maestro') {
                var backPosition = result.valid ? '2px -289px, 260px -87px' : '2px -289px, 260px -61px';
            } else if (cardType == 'Discover') {
                var backPosition = result.valid ? '2px -331px, 260px -87px' : '2px -331px, 260px -61px';
            } else if (cardType == 'Amex') {
                var backPosition = result.valid ? '2px -121px, 260px -87px' : '2px -121px, 260px -61px';
            } else {
                var backPosition = result.valid ? '2px -121px, 260px -87px' : '2px -121px, 260px -61px';
            }

            $('#credit_card_no').css("background-position", backPosition);

            if (result.valid) {
                $("#credit_card_type").val(cardType);
                $("#credit_card_no").removeClass('required');
                cardValid = true;
            } else {
                $("#credit_card_type").val('');
                $("#credit_card_no").addClass('required');
                cardValid = false;
            }
        });

        return cardValid;

        // Card details validation
        var expMonth = $("#credit_card_exp_month").val();
        var expYear = $("#credit_card_exp_year").val();
        var cvv = $("#cvv").val();
        var regName = /^[a-z ,.'-]+$/i;
        var regMonth = /^01|02|03|04|05|06|07|08|09|10|11|12$/;
        var regYear = /^2017|2018|2019|2020|2021|2022|2023|2024|2025|2026|2027|2028|2029|2030|2031$/;
        var regCVV = /^[0-9]{3,3}$/;
        if (cardValid == 0) {
            $("#credit_card_no").addClass('required');
            $("#credit_card_no").focus();
            return false;
        } else if (!regMonth.test(expMonth)) {
            $("#credit_card_no").removeClass('required');
            $("#credit_card_exp_month").addClass('required');
            $("#credit_card_exp_month").focus();
            return false;
        } else if (!regYear.test(expYear)) {
            $("#credit_card_no").removeClass('required');
            $("#credit_card_exp_month").removeClass('required');
            $("#credit_card_exp_year").addClass('required');
            $("#credit_card_exp_year").focus();
            return false;
        } else if (!regCVV.test(cvv)) {
            $("#credit_card_no").removeClass('required');
            $("#credit_card_exp_month").removeClass('required');
            $("#credit_card_exp_month").removeClass('required');
            $("#cvv").addClass('required');
            $("#cvv").focus();
            return false;
        } else if (!regName.test(cardName)) {
            $("#credit_card_no").removeClass('required');
            $("#credit_card_exp_month").removeClass('required');
            $("#credit_card_exp_year").removeClass('required');
            $("#cvv").removeClass('required');
            return false;
        } else {
            $("#credit_card_no").removeClass('required');
            $("#credit_card_exp_month").removeClass('required');
            $("#credit_card_exp_year").removeClass('required');
            $("#cvv").removeClass('required');
            $('#cardSubmitBtn').prop('disabled', false);
            return true;
        }
    }



  //HIDE/SHOW CREDIT CARD NUMBER
  $('.cceye').click(function(){
    if($('#credit_card_no').hasClass('viewable')){
      $('#credit_card_no').removeClass('viewable');
      $(this).find('i').removeClass('icon-eye-blocked').addClass('icon-eye');
      $('#credit_card_no').attr('type','password');
    }else{
      $('#credit_card_no').addClass('viewable');
      $(this).find('i').addClass('icon-eye-blocked').removeClass('icon-eye');
      $('#credit_card_no').attr('type','text');
    }
  });



  //HIDE/SHOW CREDIT CARD NUMBER
  $('.cceye1').click(function(){
    if($('#cvv').hasClass('viewable')){
      $('#cvv').removeClass('viewable');
      $(this).find('i').removeClass('icon-eye-blocked').addClass('icon-eye');
      $('#cvv').attr('type','password');
    }else{
      $('#cvv').addClass('viewable');
      $(this).find('i').addClass('icon-eye-blocked').removeClass('icon-eye');
      $('#cvv').attr('type','text');
    }
  });

/*  $('#credit_card_no').keyup(function(){
    $('#credit_card_no').val(addDashInCreditCard($('#credit_card_no').val()));
  });*/



	/*$('#frmICK').submit(function(e){
		e.preventDefault();

		if($('#frmICK').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student.php';
			$('.spinner').removeClass('hide');

			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){
				if(status == 'success'){
					if(data.code == 1){
						displayAjaxMsg(data.msg,data.code);
					}else{
						displayAjaxMsg(data.msg,data.code);
					}
				}else{
					displayAjaxMsg(data.msg);
				}
			},'json');
		}
  });*/
  
  $('#frmICK').submit(function(e){
    e.preventDefault();

		if($('#frmICK').valid()){
			var targetUrl = '<?php echo SITEURL ?>ajax/ajss-bank-cc-info';
			$('.spinner').removeClass('hide');

			var formDate = $(this).serialize();
			$.post(targetUrl,formDate,function(data,status){
				if(status == 'success'){
					if(data.code == 1){
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

	$('#same_as_billing_ad').change(function(){
		if($(this).is(':checked')){
			$('#shipping_address_1').val($('#billing_address_1').val());
			$('#shipping_address_2').val($('#billing_address_2').val());
			$('#shipping_city').val($('#billing_city').val());
			$('#shipping_state_id').val($('#billing_state_id').val());
			$('#shipping_entered_state').val($('#billing_entered_state').val());
			$('#shipping_country_id').val($('#billing_country_id').val());
			$('#shipping_post_code').val($('#billing_post_code').val());
		}else{
			$('#shipping_address_1').val('');
			$('#shipping_address_2').val('');
			$('#shipping_city').val('');
			$('#shipping_state_id').val('');
			$('#shipping_entered_state').val('');
			$('#shipping_country_id').val('');
			$('#shipping_post_code').val('');
		}

		$('#frmICK').valid();
	});

	$("input:radio[name=payment_method]").click(function() {
		if($(this).val() == 'credit_card'){
			$('#row_auto_deduction_acc').addClass('hide');
			$('#row_credit_card').removeClass('hide');
		}else{
			$('#row_credit_card').addClass('hide');
			$('#row_auto_deduction_acc').removeClass('hide');
		}

		//$('#frmICK').valid();
	});
});
</script>
<?php include "../footer.php"?>