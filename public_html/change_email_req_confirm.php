<?php
$post_unique_id = $_GET['id'];
include_once "includes/config.php";
include "header_guest.php";
?>
<style>
body{margin-top:20px;}
.mail-seccess {
    text-align: center;
	/* background: #fff;
	border-top: 1px solid #eee; */
}
.mail-seccess .success-inner {
	display: inline-block;
}
.mail-seccess .success-inner h1 {
	font-size: 100px;
	text-shadow: 3px 5px 2px #3333;
	color: #006DFE;
	font-weight: 700;
}
.mail-seccess .success-inner h1 span {
	display: block;
	font-size: 40px;
	color: #333;
	font-weight: 600;
	text-shadow: none;
	margin-top: 65px;
  margin-left:22px;
}
.mail-seccess .success-inner p {
	padding: 20px 15px;
}
.mail-seccess .success-inner .btn{
	color:#fff;
}
.row{
  margin-left:-211px;
  margin-right:-190px;
}
.btn-lg{
    font-size: 23px;
}
</style>

<?php


$change_email=$db->get_row("SELECT * FROM ss_change_email_request where MD5(userid)='".$post_unique_id."' and status=0");


if(!empty($change_email)){

?>
<div class="container">

<div class="text-center " id="buffering_off" >
  <img src="<?php echo SITEURL ?>assets/images/processing.gif" class="rounded" alt="Picture">


<!-- <p id="show_warning"> </p> -->

</div>

<!-- <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" /> -->
<section class="mail-seccess section">
		<div class="row hide" id="show_message_div">
			<div class="col-lg-12 offset-lg-3 col-12">
				<!-- Error Inner -->
				<div class="success-inner">
					<h1><i class="fa fa-envelope"></i><span>Your Email Updated Successfully!</span></h1>
					<p style="font-size:18px;">Your email has been updated. Feel free to contact us. </p>
					<a href="<?php echo SITEURL ?>logout" class="btn btn-primary btn-lg"><i class="glyphicon glyphicon-log-in btn-sm" style="font-size: 28px;"></i> Login</a>
				</div>
                
				<!--/ End Error Inner -->
			</div>
		</div>
	
</section>

</div>



<?php 
}else{
header("Location:".SITEURL."errors/error404.php");
exit;
}
 ?>

<script>
 $(document).ready(function() {  
    myFunction(); 
function myFunction() {
var targetUrl = '<?php echo SITEURL ?>ajax/ajss-family';
data = {       
        id: <?php echo $change_email->id?> ,
        email:  '<?php echo $change_email->new_email?>',
        mainid: <?php echo $change_email->userid?>,
        user_type:<?php echo $change_email->user_type?>,
        action: 'approve_email'
    }

$.post(targetUrl,data , function(data, status) {
    if (status == 'success') {
        if (data.code == 1) {
            $('#show_warning').html(data.msg);
            $('#buffering_off').addClass('hide');
            $('#show_message_div').removeClass('hide')
            displayAjaxMsg(data.msg, data.code);
        } else {
  
            $('#show_warning').html(data.msg);
        }
    } else {
        $('#show_warning').html(data.msg);
    }

}, 'json');
    }
 });


</script>

