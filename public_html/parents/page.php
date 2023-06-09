<?php $mob_title = ucwords(str_replace('-',' ',$_GET['slug'])); ?>
<?php include "../header.php";

//AUTHARISATION CHECK - UT05 MEANS PARENTS
if(!check_userrole_by_code('UT05') && !check_userrole_by_code('UT01') && !check_userrole_by_code('UT02') && !check_userrole_by_code('UT04')){
	include "../includes/unauthorized_msg.php";
	return;
}

$page = $db->get_row("select * from ss_page where slug='".$_GET['slug']."'");
?>
<style>
.page_contents li{
  margin-bottom:10px;
}
</style>
<!-- Page header -->
<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block"><?php echo $page->page_name ?></h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active"><?php echo $page->page_name ?></li>
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
           <div class="row">
              <div class="col-lg-12 page_contents">
                <?php echo $page->contents ?>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
$( document ).ready(function() {	
});
</script>
<?php include "../footer.php"?>
