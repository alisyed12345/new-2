<?php
$mob_title = "Page";
include "../header.php";
//AUTHARISATION CHECK 
if (!in_array("su_report_registration_payment", $_SESSION['login_user_permissions'])) {
  include "../includes/unauthorized_msg.php";
  return;
}
if (!empty(get_country()->currency)) {
  $currency = get_country()->currency;
} else {
  $currency = '';
}
?>
<style>
  .dataGrid.table>tbody>tr>td {
    padding-top: 13px !important;
    padding-bottom: 13px !important;
  }

  table,
  td {
    border-bottom: 1px solid black !important;
  }

  legend {
    font-size: 12px;
    padding-top: 10px;
    padding-bottom: 0px;
    text-transform: uppercase;
    margin-top: 10px;
  }

  /*.btn {
  border-radius: 0px !important;
  padding: 0.5rem 1rem;
  float: right;
  font-weight: 500 !important;
}*/
  .datatable-btn {
    position: relative;
    display: inline-block;
    box-sizing: border-box;
    margin-right: 0.333em;
    margin-bottom: 0.333em;
    padding: 0.5em 1em;
    border: 1px solid #999;
    border-radius: 2px;
    cursor: pointer;
    font-size: 0.88em;
    line-height: 1.6em;
    color: black;
    white-space: nowrap;
    overflow: hidden;
    background-color: #e9e9e9;
    background-image: linear-gradient(to bottom, #fff 0%, #e9e9e9 100%);

  }

  .datatable-btn:focus,
  .datatable-btn:hover {
    border: 1px solid #666;
    background-color: #e0e0e0;
    background-image: linear-gradient(to bottom, #f9f9f9 0%, #e0e0e0 100%);
    color: black;
  }
</style>
<!-- Page header -->

<div class="page-header page-header-default">
  <div class="page-header-content">
    <div class="page-title">
      <h4 style="display:inline-block">Registration Payment Report</h4>
    </div>
  </div>
  <div class="breadcrumb-line">
    <ul class="breadcrumb">
      <li><a href="<?php echo SITEURL . "dashboard" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
      <li class="active">Registration Payment Report</li>
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
            <div class="col-md-12">
              <div class="row">
                <div class="col-md-3">
                  <label for="from_date">From Date:</label>
                  <input type="text" name="from_date" id="from_date" placeholder="From Date" class="form-control uniquedate">
                </div>
                <div class="col-md-3">
                  <label for="to_date">To Date:</label>
                  <input type="text" name="to_date" id="to_date" placeholder="To Date" class="form-control uniquedate">
                </div>
                <div class="col-md-2">
                  <label for="status">Status:</label>
                  <select class="form-control " name="status" id="status">
                    <option value="">Select</option>
                    <option value="Success">Success</option>
                    <option value="Decline">Failed</option>
                  </select>
                </div>
                <div class="col-md-1">
                  <a href="javascript:void(0)" class="btn btn-primary text-left" id="btnSurveyStatus" style="margin-right:30px;margin-top:30px;">Filter</a>
                </div>
                <div class="col-md-1">
                  <a href="javascript:void(0)" class="btn btn-primary text-left" id="btnReset" style="margin-right:70px;margin-top:30px;background-color: #f2af58 !important; border-color: #f2af58 !important;">Reset</a>
                </div>


                <div class="col-md-1" style="float: right;">
                  <span style="font-weight: 700;">Total: <span id="grand_total">0</span></span>
                  <!-- <a href="javascript:void(0)" class="datatable-btn" onclick="exportpdf()" style="margin-top:10px;">Export PDF</a> -->
                </div>
                <!-- <div class="col-md-1" style="float: right;width: 6.94%;">
                <a href="javascript:void(0)" onclick="exportcsv()" class="datatable-btn" 
                style="margin-top:30px;">Export CSV</a>
            </div> -->
              </div>
            </div>
          </div>
          <div class="row" style="margin-top: 10px;">
            <div class="col-lg-12">
              <table class="table table-bordered data-table dataGrid">
                <thead>
                  <tr>
                    <th>1st Parent's Name</th>
                    <th>2nd Parent's Name</th>
                    <th>Student Name</th>
                    <th>Date</th>
                    <th>(<?php echo $currency; ?>) Amount</th>
                    <th>(<?php echo $currency; ?>) Refunded Amount</th>
                    <th>Transaction ID</th>
                    <th>Status</th>
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

<script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.11.5/api/sum().js"></script>
<script>
  $(document).ready(function() {
    $.cookie('setPagelimit', 25);
    //FILL TABLE
    var getter = $.cookie("setPagelimit");
    fillTable('', '', '', getter);

    $('#to_date').pickadate({
      labelMonthNext: 'Go to the next month',
      labelMonthPrev: 'Go to the previous month',
      labelMonthSelect: 'Pick a month from the dropdown',
      labelYearSelect: 'Pick a year from the dropdown',
      selectMonths: true,
      selectYears: true,
      //  min: new Date(2022,3,12),
      max: [<?php echo date('Y') ?>, <?php echo date('m') ?>, <?php echo date('d') ?>],
      formatSubmit: 'yyyy-mm-dd'
    });


    $('#from_date').pickadate({
      labelMonthNext: 'Go to the next month',
      labelMonthPrev: 'Go to the previous month',
      labelMonthSelect: 'Pick a month from the dropdown',
      labelYearSelect: 'Pick a year from the dropdown',
      selectMonths: true,
      selectYears: true,
      max: [<?php echo date('Y') ?>, <?php echo date('m') ?>, <?php echo date('d') ?>],
      formatSubmit: 'yyyy-mm-dd',
      onOpen: function(context) {
        var picker = $("#to_date").pickadate('picker');
        picker.clear();
      },
      onSet: function(context) {
        var date = new Date(context.select);
        var picker = $("#to_date").pickadate('picker');
        picker.set('min', date);
      }
    })




    // var date =  $('#from_date').val();
    //  picker.set('select', date);
    $('#btnSurveyStatus').on('click', function() {
      var getter = $.cookie("setPagelimit");
      var currency = '<?php echo $currency ?>';
      document.getElementById("grand_total").innerHTML = currency + 0;
      var fromdate = $('#from_date').val();
      var todate = $('#to_date').val();
      var status = $('#status').val();
      $('.datatable-basic').DataTable().destroy();
      fillTable(fromdate, todate, status, getter);
    });
    $('#btnReset').on('click', function() {
      var getter = $.cookie("setPagelimit");
      $('#from_date').val('');
      $('#to_date').val('');
      $('#status').val('');
      $('.datatable-basic').DataTable().destroy();
      fillTable('', '', '', getter);
    });
  });

  function exportcsv() {

    var fromdate = $('#from_date').val();
    var todate = $('#to_date').val();
    var status = $('#status').val();
    var url = "<?php echo SITEURL ?>report/excel/registration_payment_report" + "?fromdate=" + fromdate + "&todate=" + todate + "&status=" + status;
    window.location.href = url;
  }

  function exportpdf() {
    var fromdate = $('#from_date').val();
    var todate = $('#to_date').val();
    var status = $('#status').val();
    var url = "<?php echo SITEURL ?>report/pdf/registration_payment_report_pdf" + "?fromdate=" + fromdate + "&todate=" + todate + "&status=" + status;
    window.open(url, '_blank');
  }

  function fillTable(fromdate = '', todate = '', status = '', getter) {
    var total = 0;
    var table = $('.dataGrid').DataTable({
      autoWidth: false,
      destroy: true,
      iDisplayLength: getter,
      sProcessing: '',
      language: {
        loadingRecords: "<img src='<?php echo SITEURL ?>assets/images/ajax-loader.gif'> <h5>Please wait...</h5>"
      },
      responsive: true,
      ajax: {
        url: "<?php echo SITEURL ?>ajax/ajss-report?action=registration_payment_report",
        data: {
          fromdate: fromdate,
          todate: todate,
          status: status
        }
      },
      'columns': [{
          'data': 'parent_first',
          searchable: true,
          orderable: true,
          width: '15%'
        },
        {
          'data': 'parent_second',
          searchable: true,
          orderable: true,
          width: '15%'
        },
        {
          'data': 'child_name',
          searchable: true,
          orderable: true
        },
        {
          'data': 'date',
          searchable: true,
          orderable: true
        },
        {
          'data': 'amount',
          searchable: true,
          orderable: true,
          width: '10%'
        },
        {
          'data': 'refund_amount',
          searchable: true,
          orderable: true,
          width: '15%'
        },
        {
          'data': 'transaction_id',
          searchable: true,
          orderable: true,
          visible: false
        },
        {
          'data': 'status',
          searchable: true,
          orderable: true,
          width: '10%'
        },
      ],
      "order": [
        [3, "desc"]
      ],
      "columnDefs": [{
        "visible": true,
        "targets": [0]
      }],
      dom: 'Bfltip',
      buttons: [{
          // extend: 'print',
          // // exportOptions: {
          // //     columns: [ 1, 2, 5, 6, 7, 8, 9, 10, 11, 12 ]
          // // },
          // //Scale: '137',
          // Destination: 'Microsoft Print to PDF',

          extend: 'pdfHtml5',
          // title : function() {
          //     return "ABCDE List";
          // },
          orientation: 'Potrait',
          pageSize: 'A3',
          text: '<i class="fa fa-file-pdf-o"> PDF</i>',
          titleAttr: 'PDF',
          title: 'Registration Payment Report',
        },
        {
          extend: 'excel'
        }
      ],
      "fnRowCallback": function(nRow, aData, ) {
        var total = table.column(4).data().sum();
        var currency = '<?php echo $currency ?>';
        total.toFixed(2);
        document.getElementById("grand_total").innerHTML = currency + total;
        // console.log(aData["final_amount"]);
        // total = parseFloat(total) + parseFloat(aData["final_amount"]);
        // total.toFixed(2); document.getElementById("grand_total").innerHTML = '$'+total;   
      },
      "initComplete": function(settings, json) {
        if (json.data.length > 0) {
          table.buttons().enable();
        } else {
          table.buttons().disable();
        }
      }
    });
  }

  $('.dataGrid').on('length.dt', function(e, settings, len) {
    $.cookie("setPagelimit", len);
    var table = $('.dataGrid').DataTable();
    var total = table.column(4).data().sum();
    var currency = '<?php echo $currency ?>';
    total.toFixed(2);
    document.getElementById("grand_total").innerHTML = currency + total;
    //console.log(e);

  });
</script>

<?php include "../footer.php" ?>