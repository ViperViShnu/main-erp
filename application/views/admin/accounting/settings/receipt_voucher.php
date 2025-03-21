<div class="row">
    <!-- Start Form -->
    <div class="col-lg-12">
        <form action="<?php echo base_url() ?>admin/accounting/save_receipt_voucher_settings"
              enctype="multipart/form-data"
              class="form-horizontal" method="post">
            <div class="panel panel-custom">
                <header class="panel-heading  "><?= lang('receipt_voucher') ?></header>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?= lang('receipt_voucher') . ' ' . lang('prefix') ?>
                            <span class="text-danger">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" name="receipt_voucher_prefix" class="form-control" style="width:260px"
                                       value="<?= config_item('receipt_voucher_prefix') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?= lang('receipt_voucher_prefix_start_no') ?> <span
                                    class="text-danger">*</span></label>
                        <div class="col-lg-7">
                            <input type="text" name="receipt_voucher_prefix_start_no" class="form-control" style="width:260px"
                                   value="<?= config_item('receipt_voucher_prefix_start_no') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?= lang('receipt_voucher') . ' ' . lang('number_format') ?></label>
                        <div class="col-lg-5">
                            <input type="text" name="receipt_voucher_format" class="form-control" style="width:260px"
                                   value="<?php
                                   if (empty(config_item('receipt_voucher_format'))) {
                                       echo '[' . config_item('receipt_voucher_format') . ']' . '[yyyy][mm][dd][number]';
                                   } else {
                                       echo config_item('receipt_voucher_format');
                                   } ?>">
                            <small>ex [<?= config_item('receipt_voucher_prefix') ?>] = <?= lang('receipt_voucher_prefix') ?>,[yyyy] =
                                'Current Year (<?= date('Y') ?>)'[yy] ='Current Year (<?= date('y') ?>)',[mm] =
                                'Current Month(<?= date('M') ?>)',[m] =
                                'Current Month(<?= date('m') ?>)',[dd] = 'Current Date (<?= date('d') ?>)',[number] =
                                'Invoice Number (<?= sprintf('%04d', config_item('estimate_start_no')) ?>)'
                            </small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-lg-3 control-label"><?= lang('increment_receipt_voucher') ?></label>
                        <div class="col-lg-6">
                            <div class="checkbox c-checkbox">
                                <label class="needsclick">
                                    <input type="hidden" value="off" name="increment_receipt_voucher"/>
                                    <input type="checkbox" <?php
                                    if (config_item('increment_receipt_voucher') == 'TRUE') {
                                        echo "checked=\"checked\"";
                                    }
                                    ?> name="increment_receipt_voucher">
                                    <span class="fa fa-check"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-lg-3 control-label"></div>
                    <div class="col-lg-6">
                        <button type="submit" class="btn btn-sm btn-primary"><?= lang('save_changes') ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- End Form -->
</div>