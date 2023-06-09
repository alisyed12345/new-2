<?php
$mob_title = "New Message";
include "../header.php";

//AUTHARISATION CHECK - UT01 MEANS SUPER ADMIN
if ($_SESSION['icksumm_uat_login_usertypecode'] != 'UT05' && !in_array("su_internal_msg_send", $_SESSION['login_user_permissions'])) {
    include "../includes/unauthorized_msg.php";
    return;
}
?>
<style>
    .bootstrap-select {
        width: 100% !important;
        padding-top: 2px !important;
        font-size: 15px !important;

    }
</style>
<!-- Page header -->

<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>New Internal Message</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL ?>dashboard"><i class="icon-home2 position-left"></i>Dashboard</a></li>
            <li><a href="<?php echo $SITEURL . "inertnal_message_list" ?>">Received Internal Message</a></li>
            <li class="active">New Internal Message</li>
        </ul>
    </div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content content-box">
    <form id="frmICK" class="form-validate-jquery" method="post">
        <div class="panel panel-flat panel-flat-box">
            <div class="panel-body panel-body-box">
                <div class="ajaxMsg"></div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Message To</label>
                            <br />
                            <label class="radio-inline" style="padding-left: 18px;">
                                <input type="radio" name="message_to" class="recipient" value="staff" checked="checked">Staff </label>
                            <label class="radio-inline" style="padding-left: 18px;">
                                <input type="radio" name="message_to" class="recipient" value="student">
                                Group / Parents </label>
                        </div>
                    </div>
                    <div class="col-md-3 multi-select-full rec_staff">
                        <div class="form-group">
                            <label>Staff<span class="mandatory">*</span></label>
                            <?php
                            // $staff = $db->get_results("SELECT 2 AS row_order, u.id, s.first_name, s.middle_name, s.last_name, 
                            // (SELECT CONCAT(' (',GROUP_CONCAT(ut.user_type),')') FROM `ss_usertypeusermap` utm 
                            // INNER JOIN ss_usertype ut ON utm.user_type_id = ut.id WHERE user_id = s.user_id) AS user_type FROM ss_user u 
                            // INNER JOIN ss_staff s ON u.id = s.user_id 
                            // INNER JOIN ss_staff_session_map ssm ON u.id = ssm.staff_user_id 
                            // WHERE u.is_active = 1 AND u.is_deleted = 0 AND s.is_deleted = 0 AND ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                            // UNION
                            // SELECT 1 AS row_order, ss_user.id, 'Principal', '', '', '' FROM ss_user
                            // INNER JOIN ss_usertypeusermap ON ss_usertypeusermap.user_id = ss_user.id
                            // INNER JOIN ss_usertype ON ss_usertypeusermap.user_type_id = ss_usertype.id
                            //  WHERE ss_user.is_active = 1 AND ss_user.is_deleted = 0 AND ss_user.username <> 'admin31' 
                            // AND ss_usertype.id = (SELECT id FROM ss_usertype WHERE user_type_code = 'UT01' AND user_type_subgroup = 'principal' AND is_default = 1) 
                            // GROUP BY row_order ORDER BY row_order ASC, first_name ASC");

        
                            $staffs = $db->get_results("SELECT 2 AS row_order, u.id, s.first_name, s.middle_name, s.last_name, 
                            (SELECT CONCAT(' (',GROUP_CONCAT(ut.user_type),')') FROM `ss_usertypeusermap` utm INNER JOIN ss_usertype ut 
                            ON utm.user_type_id = ut.id WHERE user_id = s.user_id) as user_type FROM ss_user u 
                            INNER JOIN ss_staff s ON u.id = s.user_id INNER JOIN ss_staff_session_map ssm ON u.id = ssm.staff_user_id 
                            WHERE u.is_active = 1 AND u.is_deleted = 0 AND s.is_deleted = 0 and  ssm.session_id = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                            UNION
                            SELECT 1 AS row_order, id, 'Principal', '', '', '' FROM ss_user WHERE is_active = 1 AND is_deleted = 0 AND username <> 'admin31' 
                            AND user_type_id = (SELECT id FROM ss_usertype WHERE user_type_code = 'UT01' AND user_type_group = 'principal' AND is_default = 1) GROUP BY row_order
                            ORDER BY row_order ASC, first_name asc ") 
                            ?>
                            <select class="form-control" id="staff" multiple="multiple" name="staff[]" checkMsgToStaff="true">
                                <?php if (count((array)$staffs) > 0) { ?>
                                    <option value="whole_group">Select All</option>

                                    <?php foreach ($staffs as $sta) {
                                        if ($sta->id != $_SESSION['icksumm_uat_login_userid']) {
                                    ?>
                                            <option value="<?php echo $sta->id ?>">
                                                <?php echo $sta->first_name . ' ' . $sta->middle_name . ' ' . $sta->last_name . ' ' . $sta->user_type ?></option>
                                    <?php }
                                    } ?>
                                <?php } else { ?>
                                    <option value="">No data found</option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 multi-select-full hide rec_student">
                        <div class="form-group">
                            <label>Group<span class="mandatory">*</span></label>
                            <?php
                            if (check_userrole_by_subgroup('admin') || check_userrole_by_subgroup('principal')) {
                                $groups = $db->get_results("SELECT * from ss_groups where session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "' 
                AND is_active = 1 and is_deleted = 0 order by group_name asc");
                            } else {
                                $groups = $db->get_results("select distinct g.* from ss_groups g INNER JOIN ss_classtime d ON g.id = d.group_id 
                WHERE g.is_active=1 
                AND g.session = '" . $_SESSION['icksumm_uat_CURRENT_SESSION'] . "'
                and g.is_deleted=0 and d.id in (select classtime_id from ss_staffclasstimemap 
                where staff_user_id='" . $_SESSION['icksumm_uat_login_userid'] . "' 
                and active=1) order by group_name asc");
                            }
                            ?>
                            <select class="form-control" id="group" name="group" checkMsgToGroup="true">
                                <option value="">Select</option>
                                <?php foreach ($groups as $gr) { ?>
                                    <option value="<?php echo $gr->id ?>"><?php echo $gr->group_name ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2 multi-select-full hide rec_student">
                        <div class="form-group">
                            <label for="group">Subject:<span class="mandatory">*</span></label>
                            <select class="form-control" name="subject" id="subject">
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                    </div>


                    <div class="col-md-2 multi-select-full hide rec_student">
                        <div class="form-group">
                            <label>Parents Of<span class="mandatory">*</span></label><br>
                            <select class="bootstrap-select" multiple="multiple" id="student" name="student[]" style="float: none;">

                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Message<span class="mandatory">*</span></label>
                            <label class="error" id="statusMsgcomm"></label>
                            <!-- <textarea class="form-control messagecontent required" id="message" name="message"></textarea> -->
                            <textarea class="form-control messagecontent" id="message" name="message" style="height:200px"></textarea>


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
                            <input type="hidden" name="action" value="save_message">
                            <button type="submit" class="btn btn-success btnsubmit"><i class="icon-spinner2 spinner hide marR10 insidebtn" id="get_spinner"></i>
                                Send</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- /Content area -->
<!-- <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script> -->

<script>
    $(document).ready(function(e) {
        CKEDITOR.replace('message', {
            height: 300,
            filebrowserUploadUrl: "../ajax/ckeditor_upload"
        });

        $('.recipient').click(function() {
            $('#staff').selectpicker('refresh');
            $('#student').selectpicker('refresh');
            if ($(this).val() == 'student') {
                $('.rec_student').removeClass('hide');
                $('.rec_staff').addClass('hide');
                $('#staff').val('');
               
                $('#subject').addClass('required');
                $('#student').addClass('required');

            } else {
                $('.rec_student').addClass('hide');
                $('.rec_staff').removeClass('hide');
                $('#group').val('');
                $('#subject').val('');
                $('#student').html('<option value="">Select</option>');
                $('#subject').removeClass('required');
                $('#student').removeClass('required');
            }
        });



        $('#group').change(function() {
            $('#group_id').val($(this).val());

            if ($('#group').val() == '') {
                $('#subject').html('<option value="">Select Subject</option>');
            } else {
                //SUBJECT
                $('#subject').html('<option value="">Loading...</option>');

                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-classes';
                $.post(targetUrl, {
                    group_id: $('#group').val(),
                    action: 'fetch_assigned_group_class_for_select'
                }, function(data, status) {
                    if (status == 'success' && data != '') {
                        $('#subject').html('<option value="">Select Subject</option>');
                        $('#subject').append(data);
                    } else {
                        $('#subject').html('<option value="">Subject not found</option>');
                    }
                });

            }
        });


        $('#subject').change(function() {
            $('#subject').val($(this).val());

            if ($('#subject').val() == '') {
                $('#student').html('<option value="">Select</option>');
                $('#student').selectpicker('refresh');
            } else {

                //STUDENT
                $('#student').html('<option value="">Loading...</option>');
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-student';
                $.post(targetUrl, {
                    group_id: $('#group').val(),
                    class_id: $('#subject').val(),
                    action: 'student_of_group'
                }, function(data, status) {
                    if (status == 'success' && data.code == 1) {
                        $('#student').html(data.optionVal);
                    } else {
                        $('#student').html('<option value="">Select</option>');
                    }
                    $('#student').selectpicker('refresh');
                }, 'json');
            }
        });




        /*	$('#group').change(function(){

            $('#student').html('<option value="">Loading...</option>');
            
            var targetUrl = '<?php echo $SITEURL ?>ajax/ajss-student';
                $.post(targetUrl,{group_id:$('#group').val(),action:'student_of_group'},function(data,status){
                    if(status == 'success' && data.code == 1){
                        $('#student').html(data.optionVal);
                    }else{
                        $('#student').html('<option value="">Select</option>');
                    }
                },'json');


            });*/

        $('#frmICK').submit(function(e) {
            e.preventDefault();
            var getcode = CKEDITOR.instances.message.getData();
            var ckvalue = getcode.replace(/<[^>]*>/gi, '').trim();
            ckvalue = ckvalue.replace(/&nbsp;/g, '');

            CKEDITOR.instances.message.updateElement();


            if ($('#frmICK').valid() && ckvalue.length > 0) {
                $('#statusMsgcomm').html('');
                $('.spinner').removeClass('hide');
                var formData = new FormData(this);
                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';
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
                            CKEDITOR.instances.message.setData('');
                            //---------------------------------//
                            $("#staff").selectpicker("refresh");
                            $('#student').selectpicker('refresh');
                            $('.rec_student').addClass('hide');
                            $('.rec_staff').removeClass('hide');
                            $('#group').val('');
                            $('#student').html('<option value="">Select</option>');
                            $('#subject').removeClass('required');
                            $('#student').removeClass('required');
                            //------------------------------------------//

                        } else {
                            displayAjaxMsg(data.msg, data.code);
                        }
                    },
                    error: function(data) {
                        $('.spinner').addClass('hide');
                        displayAjaxMsg(data.msg, data.code);
                    }
                }, 'json');
            } else {
                if (ckvalue.length === 0) {
                    $('#statusMsgcomm').html('Required Field');
                    return false;
                } else {
                    $('#statusMsgcomm').html('');
                }

            }
        });

        $('.email_template_title').change(function() {
            var id = $('.email_template_title').val();
            $('.datacontent').html('Processing...');
            if (id.length == 0) {
                $('.datacontent').html('');
                $('.note-editable').html("");
            } else {

                var targetUrl = '<?php echo SITEURL ?>ajax/ajss-message';
                $.post(targetUrl, {
                    id: id,
                    action: 'get_email_template_data'
                }, function(data, status) {
                    if (status == 'success' && data.code == 1) {
                        $('.datacontent').html('');
                        $('.messagecontent').summernote('code', data.inputVal.email_template);
                    } else {
                        //$('.messagecontent').summernote('destroy');
                        $('.messagecontent').val();
                    }
                }, 'json');
            }
        });

        //$('.bootstrap-select').selectpicker();


        $('#student').change(function() {
            var checkele = $('#student').val();
            if (checkele !== '') {
                $("#student-error").empty();
            }
        })

        $('#student').selectpicker().change(function() {
            toggleSelectAll($(this));
        }).trigger('change');

        $('#staff').change(function() {
            var checkele = $('#staff').val();
            if (checkele !== '') {
                $("#staff-error").empty();
            }
        })

        $('#staff').selectpicker().change(function() {
            toggleSelectAll($(this));
        }).trigger('change');
    });



    function toggleSelectAll(control) {
        var allOptionIsSelected = (control.val() || []).indexOf("whole_group") > -1;

        function valuesOf(elements) {
            return $.map(elements, function(element) {
                return element.value;
            });
        }

        if (control.data('allOptionIsSelected') != allOptionIsSelected) {
            // User clicked 'All' option
            if (allOptionIsSelected) {
                // Can't use .selectpicker('selectAll') because multiple "change" events will be triggered
                control.selectpicker('val', valuesOf(control.find('option')));
            } else {
                control.selectpicker('val', []);
            }
        } else {
            // User clicked other option
            if (allOptionIsSelected && control.val().length != control.find('option').length) {
                // All options were selected, user deselected one option
                // => unselect 'All' option
                control.selectpicker('val', valuesOf(control.find('option:selected[value!=whole_group]')));
                allOptionIsSelected = false;
            } else if (!allOptionIsSelected && control.val().length == control.find('option').length - 1) {
                // Not all options were selected, user selected all options except 'All' option
                // => select 'All' option too
                control.selectpicker('val', valuesOf(control.find('option')));
                allOptionIsSelected = true;
            }
        }
        control.data('allOptionIsSelected', allOptionIsSelected);
    }

    // $(".messagecontent").on("summernote.change", function(e) { // callback as jquery custom event 
    //     var getcode = $('.messagecontent').summernote('code');
    //     getcode = $.trim(getcode.replace(/\&nbsp;/g, ''));
    //     var div = document.createElement("div");
    //     div.innerHTML = getcode;
    //     var text = div.textContent || div.innerText || "";
    //     if ($.trim(text).length == 0) {
    //         $('#statusMsgcomm').css("color", "red");
    //         $('#statusMsgcomm').html("Message cannot be empty ");
    //         return false;
    //     } else {
    //         $('#statusMsgcomm').html("");
    //     }
    // });
</script>
<?php include "../footer.php" ?>