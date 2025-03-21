<form name="myform" role="form" data-parsley-validate="" novalidate="" enctype="multipart/form-data" id="form"
      action="<?php echo base_url(); ?>admin/invoice/export_invoice" method="post" class="form-horizontal  ">
    
    <script src="<?php echo base_url(); ?>assets/plugins/bootstrap-tagsinput/fm.tagator.jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.16.0/jquery.validate.js"></script>
    <?php include_once 'assets/admin-ajax.php'; ?>
    <?php echo message_box('success'); ?>
    <?php echo message_box('error'); ?>
    
    <div id="invoice_state_report_div">
    
    
    </div>
    


    <?php
    $h_s = config_item('invoice_state');
    if ($this->session->userdata('user_type') == 1) {
        if ($h_s == 'block') { ?>
            <!--<script>
            $(document).ready(function () { ins_data(base_url+'admin/invoice/invoice_state_report'); });
            </script>-->
            <?php
            
            $title = lang('hide_quick_state');
            $url = 'hide';
            $icon = 'fa fa-eye-slash';
        } else {
            $title = lang('view_quick_state');
            $url = 'show';
            $icon = 'fa fa-eye';
            ?>
            <!--    <script>
                    $(document).ready(function () {  $("#quick_state").on("click", function(){
                        if($('#state_report').length){ } else{
                        ins_data(base_url+'admin/invoice/invoice_state_report');}
                    }); });
                </script>-->
            <?php
        }
        ?>
        <div onclick="slideToggle('#state_report')" id="quick_state" data-toggle="tooltip" data-placement="top"
             title="<?= $title ?>" class="btn-xs btn btn-purple pull-left">
            <i class="fa fa-bar-chart"></i>
        </div>
        <div class="btn-xs btn btn-white pull-left ml ">
            <a class="text-dark" id="change_report" href="<?= base_url() ?>admin/dashboard/change_report/<?= $url ?>"><i
                        class="<?= $icon ?>"></i>
                <span><?= ' ' . lang('quick_state') . ' ' . lang($url) . ' ' . lang('always') ?></span></a>
        </div>
        <?php
    }
    $created = can_action('13', 'created');
    $edited = can_action('13', 'edited');
    $deleted = can_action('13', 'deleted');
    if (!empty($created) || !empty($edited)) {
    ?>

    
    <div class="row">
        <div class="col-sm-12">

            <div class="nav-tabs-custom">
                <!-- Tabs within a box -->
                <ul class="nav nav-tabs">
                    <li class=""><a
                                href="<?= base_url('admin/invoice/manage_invoice') ?>"><?= lang('all_invoices') ?></a>
                    </li>
                    <li class=""><a
                                href="<?= base_url('admin/invoice/createinvoice') ?>"><?= lang('create_invoice') ?></a>
                    </li>
                     <li class=""><a href="<?= base_url('admin/invoice/invoice_export') ?>">GSTR1</a>
                    </li>
                    <li class="active"><a href="<?= base_url('admin/invoice/hsn_summary') ?>">HSN Summary</a>
                    </li>
                </ul>
                <div class="tab-content bg-white">
                    <!-- ************** general *************-->
                    <?php } ?>
                    
                    
                    
                    <?php if (!empty($created) || !empty($edited)) { ?>
                    <div class="tab-pane active" id="create">
                        <div class="row mb-lg invoice accounting-template">
                            <div class="col-sm-6 col-xs-12 pv">
                                <div class="row">
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('start_date') ?><span
                                                        class="text-danger">*</span></label>
                                        <div class="col-lg-7">
                                            <div class="input-group">
                                                <input type="text" name="start_date" class="form-control datepicker"
                                                       value="<?php echo date('Y-m-d'); ?>" data-date-format="<?= config_item('date_picker_format'); ?>" required>
                                                <div class="input-group-addon">
                                                    <a href="#"><i class="fa fa-calendar"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-3 control-label"><?= lang('end_date') ?><span
                                                        class="text-danger">*</span></label>
                                        <div class="col-lg-7">
                                            <div class="input-group">
                                                <input type="text" name="end_date" class="form-control datepicker"
                                                       value="<?php echo date('Y-m-d', strtotime('+1 day'));
                                                       ?>" data-date-format="<?= config_item('date_picker_format'); ?>" required>
                                                <div class="input-group-addon">
                                                    <a href="#"><i class="fa fa-calendar"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn-bottom-toolbar text-right">
                            <input type="submit" value="<?= lang('submit') ?>" name="export" class="btn btn-primary">
                            <button type="button" onclick="goBack()" class="btn btn-sm btn-danger"><?= lang('cancel') ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>
<?php } else { ?>
    </div>
<?php } ?>
</div>

<script type="text/javascript">
    function slideToggle($id) {
        $('#quick_state').attr('data-original-title', '<?= lang('view_quick_state') ?>');
        $($id).slideToggle("slow");
    }

    $(document).ready(function () {
        $("#select_all_tasks").on("click", function () {
            $(".tasks_list").prop('checked', $(this).prop('checked'));
        });
        $("#select_all_expense").on("click", function () {
            $(".expense_list").prop('checked', $(this).prop('checked'));
        });
        $('[data-toggle="popover"]').popover();

        $('#start_recurring').on("click", function () {
            if ($('#show_recurring').is(":visible")) {
                $('#recuring_frequency').prop('disabled', true);
            } else {
                $('#recuring_frequency').prop('disabled', false);
            }
            $('#show_recurring').slideToggle("fast");
            $('#show_recurring').removeClass("hide");
        });
    });
</script>

<?php
if ($this->session->userdata('user_type') == 1) {
    if ($h_s == 'block') { ?>
        <script>
            $(document).ready(function () {
                ins_data(base_url + 'admin/invoice/invoice_state_report');
            });
        </script>
        <?php
        
    } else { ?>
        <script>
            $(document).ready(function () {
                $("#quick_state").on("click", function () {
                    if ($('#state_report').length) {
                    } else {
                        ins_data(base_url + 'admin/invoice/invoice_state_report');
                    }
                });
            });
        </script>
        <?php
    }
    ?>
<?php } ?>
<script>
    $(document).ready(function () {
        ins_data(base_url + 'admin/invoice/invo_data');
    });
</script>