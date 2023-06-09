<?php include "../header.php" ?>
<!-- Page header -->
<div class="page-header page-header-default">
    <div class="page-header-content">
        <div class="page-title">
            <h4>Student</h4>
        </div>
    </div>
    <div class="breadcrumb-line">
        <ul class="breadcrumb">
            <li><a href="<?php echo SITEURL."dashboard.php" ?>"><i class="icon-home2 position-left"></i> Dashboard</a></li>
            <li class="active">Add Student</li>
        </ul>

    </div>
</div>
<!-- /page header -->
<!-- Content area -->
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <form action="#" class="form-validate" method="post">
                <div class="panel panel-flat">
                    <div class="panel-body">
                        <legend class="text-semibold"><i class="icon-user position-left"></i> Personal Information</legend>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Admission Number:</label>
                                    <input placeholder="Admission Number" name="admission_no" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Admission Date:</label>
                                    <input placeholder="Admission Date" name="admission_date" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Group:</label>
                                    <select data-placeholder="Select your country" class="select form-control">
                                        <option>Select</option>
                                        <option value="Cambodia">Group A</option>
                                        <option value="Cameroon">Group B</option>
                                        <option value="Canada">Group C</option>
                                        <option value="Cape Verde">Group D</option>
                                        <option value="Cambodia">Group E</option>
                                        
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>First Name:</label>
                                    <input placeholder="First Name" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Middle Name:</label>
                                    <input placeholder="Middle Name" name="middle_name" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Last Name:</label>
                                    <input placeholder="Last Name" name="last_name" class="form-control" type="text">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Date of Birth:</label>
                                    <input placeholder="Date of Birth" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Birth Place:</label>
                                    <input placeholder="Birth Place" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Blood Group:</label>
                                    <select data-placeholder="Select your country" class="select form-control">
                                        <option>Select</option>
                                        <option value="Cambodia">A+</option>
                                        <option value="Cameroon">A-</option>
                                        <option value="Canada">B+</option>
                                        <option value="Cape Verde">B-</option>
                                        <option value="Cambodia">AB+</option>
                                        <option value="Cameroon">AB-</option>
                                        <option value="Canada">O+</option>
                                        <option value="Cape Verde">O-</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Password:</label>
                                    <input placeholder="Password" name="password" id="password" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Confirm Password:</label>
                                    <input placeholder="Confirm Password" name="confirm_password" id="confirm_password" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Gender:</label>
                                    <div class="col-md-12">
                                        <label class="radio-inline">
                                            <input type="radio" id="inlineCheckbox1" name="gender" value="option1"> Male
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" id="inlineCheckbox1" name="gender" value="option1"> Female
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <legend class="text-semibold"><i class="icon-envelop position-left"></i>Contact Information</legend>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Email:</label>
                                    <input placeholder="Email" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Telephone:</label>
                                    <input placeholder="Telephone" class="form-control" type="text">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Mobile:</label>
                                    <input placeholder="Mobile" class="form-control" type="text">
                                </div>
                            </div>
                        </div>
                        <legend class="text-semibold"><i class="icon-camera position-left"></i>Photo</legend>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Photo:</label>
                                    <input type="file">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="button" class="btn btn-success">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- /Content area -->
<?php include "../footer.php" ?>