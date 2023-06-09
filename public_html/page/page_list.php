<?php 
$mob_title = "Page";
include "../header.php";

if(!in_array("su_pages_list", $_SESSION['login_user_permissions'])){ 
  include "../includes/unauthorized_msg.php";
  return;
  }

?>
<style>
.attendance {}
</style>
<!-- Page header -->

<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4 style="display:inline-block">Manage Pages</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL."dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li class="active">Manage Pages</li>
        </ul>
    </div>
    <?php  if(in_array("su_pages_add", $_SESSION['login_user_permissions'])){  ?>
      <div class="above-content"> <a href="<?php echo SITEURL.'page/page_add' ?>"  class="pull-right"><span class="label label-primary"> Add New Page</span></a>  </div>
      <?php } ?>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-flat">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <table class="table table-bordered dataGrid">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Page Name</th>
                                        <!-- <th>Slug</th> -->
                                        <th>Status</th>
                                        <th class="text-center"></th>
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
</div>

<script>
$(document).ready(function() {
    //FILL TABLE
    fillTable();
});

function fillTable() {
    var table = $('.dataGrid').DataTable({
        autoWidth: false,
        destroy: true,
        pageLength: <?php echo TABLE_LIST_SHOW ?>,
        sProcessing: '',
        language: {
            loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
        },
        responsive: true,
        ajax: '<?php echo SITEURL ?>ajax/ajss-page?action=list_pages',
        'columns': [{
                'data': 'id'
            },
            {
                'data': 'page_name',
                searchable: true,
                orderable: true
            },
            // {
            //     'data': 'slug',
            //     searchable: true,
            //     orderable: true
            // },
            {
                'data': 'status',
                searchable: true,
                orderable: true
            },
        ],
        "columnDefs": [{
                "render": function(data, type, row) {
                    var btnLinks = '';
                    <?php if (in_array("su_pages_edit", $_SESSION['login_user_permissions'])) { ?>
                        btnLinks= "<a href='<?php echo SITEURL ?>page/page_edit?id=" + row['id'] +
                        "' class='text-primary action_link overlay_link' title='Edit Page'>Edit</a>";
                        <?php } ?>
                        return btnLinks;
                },
                "targets": 3
            },
            {
                "visible": false,
                "targets": [0]
            }
        ]
    });
}
</script>
<?php include "../footer.php"?>